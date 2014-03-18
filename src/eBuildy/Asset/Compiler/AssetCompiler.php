<?php

namespace eBuildy\Asset\Compiler;

use eBuildy\Component\Cache;

abstract class AssetCompiler
{
    protected $currentModule;
    protected $currentFolder;
    protected $currentSource;
    protected $options;
    
    public function __construct($options = null)
    {
        $this->options = array_merge(array(
			'compile' => true
		), $options);
    }
    
    /**
     * Import an asset resource.
     * Search on:
     * 1. Current folder
     * 2. Current module folder
     * 3. Application source folder
     * 4. Vendor folder
     * 5. Root folder
     * 6. Web folder
     * 
     * @param string $pattern
     * @param boolean $compile
     * 
     * @throws \Exception
     */            
    public function import($pattern, $compile = false)
    {
        $importSources = array($this->currentFolder . DIRECTORY_SEPARATOR, $this->currentModule . DIRECTORY_SEPARATOR, SOURCE_PATH, VENDOR_PATH, ROOT, WEB_PATH);
        
        foreach($importSources as $importSource)
        {
            $files = glob($importSource . $pattern);
			
            if (!empty($files) > 0)
            {
                break;
            }
        }
		
        if (empty($files))
        {
            throw new \Exception('No files match ' . $pattern . ' Search on ' . var_export($importSources, true));
        }
        
        echo '/** Generated at ' . date(DATE_ATOM) . ' **/';

        foreach ($files as $filePath)
        {
            echo PHP_EOL . PHP_EOL . '/** ' . str_replace(ROOT, '', $filePath) . ' **/' . PHP_EOL . PHP_EOL;

            if ($compile)
            {
                echo $this->compileFile($filePath);
            }
            else
            {
                $sourcePath = $this->resolveFilePath($filePath);
        
                echo file_get_contents($sourcePath);
            }
        }
    }
    
    public function compileGroup($sources, $target, $data = array())
    {
        $buffer = '';
        
        foreach($sources as $source)
        {
            $buffer .= $this->compile($source, null, $data);
        }
        
        $this->saveCompiledFile($buffer, $target);
    }
    
    public function compile($source, $target = null, $data = array())
    {
        $this->currentData   = $data;
        $this->currentFolder = dirname($source);        
        $this->currentModule = '';
        
        foreach(explode('/', $source) as $part)
        {
            if ($part === 'css' || $part === 'js' || $part === 'web')
            {
                break;
            }
            
            $this->currentModule .= '/' . $part;
        }
        
        return $this->doCompile($source, $target);
    }
    
    abstract protected function doCompile($source, $target);


    protected function preCompile($content)
    {
        return str_replace(array('/** ', ' **/'), array('<?php ', ' ?>'), $content);
    }
    
    protected function postCompile($content)
    {
        return $content;
    }
    
    protected function saveCompiledFile($content, $target)
    {
        if ($target !== null)
        {
            $dir = dirname($target);
            
            if (!is_dir($dir))
            {
                mkdir($dir, 0755, true);
            }
            
            file_put_contents($target, $content);
            
            if (isset($this->options['optimize']) && $this->options['optimize'])
            {
                exec('/usr/bin/yui-compressor ' . $target . ' -o ' . $target . ' --charset utf-8  2>&1');
            }
        }
    }
    
    protected function resolveFilePath($file)
    {
        $searchSources = array('', $this->currentModule . DIRECTORY_SEPARATOR, SOURCE_PATH, VENDOR_PATH, ROOT);
        
        foreach($searchSources as $searchSource)
        {
            $filePath = str_replace('//', '/', $searchSource . $file);
            
            if (file_exists($filePath))
            {
                return $filePath;
            }
        }
        
        throw new \Exception($file . ' is not found, search on ' . var_export($searchSources, true));
    }
    
    protected function compileFile($source)
    {
        $this->currentSource = $this->resolveFilePath($source);
        
        $sourceContent = file_get_contents($this->currentSource);
        
        $bufferExtension = explode('.', $source);
        $extension = $bufferExtension[count($bufferExtension) - 1];

        $sourceContentPreCompiled = $this->preCompile($sourceContent);

        $tmpFileKey = str_replace(array(ROOT, '.' . $extension, '/'), array('', '.php', '_'), $source);
        
        $sourceContentPostCompiled = $this->postCompile($sourceContentPreCompiled);

        Cache::writeTempFile($extension . '/' . $tmpFileKey, $sourceContentPostCompiled);
        
        $tmpFilePath = TMP_PATH . $extension . '/' . $tmpFileKey;

        ob_start();

        include($tmpFilePath);

        return ob_get_clean();
    }
}