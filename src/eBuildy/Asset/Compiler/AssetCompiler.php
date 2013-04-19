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
        $this->options = $options;
    }
            
    public function import($pattern, $compile = false)
    {
        $importSources = array($this->currentFolder . DIRECTORY_SEPARATOR, $this->currentModule . DIRECTORY_SEPARATOR, SOURCE_PATH, VENDOR_PATH, ROOT);
        
        foreach($importSources as $importSource)
        {
            $files = glob($importSource . $pattern);
            
            if (count($files) > 0)
            {
                break;
            }
        }

        if (count($files) == 0)
        {
            throw new \Exception('No files match ' . $pattern . ' Search on ' . var_export($importSources, true));
        }

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
        return str_replace(array('/**', '**/'), array('<?php ', ' ?>'), $content);
    }
    
    protected function postCompile($content)
    {
        return $content;
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