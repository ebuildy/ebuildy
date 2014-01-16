<?php

namespace eBuildy\Asset\Compiler;

class CSSCompiler extends AssetCompiler
{
    protected function getBaseUrl()
    {
        return isset($this->options['base_url']) ? $this->options['base_url'] : null;
    }
    
    protected function doCompile($source, $target)
    {
        if ($target !== null && file_exists($target))
        {
            unlink($target);
        }

        if (strpos($source, '.less') !== false)
        {
			$errorLog = TMP_PATH . 'css_compiler_error.log';
			
            $this->currentSource = $this->resolveFilePath($source);

            $this->content = shell_exec('lessc ' . $this->currentSource . ' --include-path="' . SOURCE_PATH . ':' . VENDOR_PATH . ':' . ROOT . '" 2> ' . $errorLog);

            if ($this->content === null)
            {
			//	echo('lessc ' . $this->currentSource . ' --include-path="' . SOURCE_PATH . ':' . VENDOR_PATH . ':' . ROOT . '" 2> ' . $errorLog);
                throw new \Exception('Error occured compiling '. $source. ' : '. print_r(file($errorLog), true));
            }
        }
        else
        {
//            $this->content = file_get_contents($source);
//
//            copy($source, $target);
                $this->content = $this->compileFile($source);
        }

        //var_dump();

        if ($target !== null)
        {
            $this->saveCompiledFile($this->content, $target);
        }

        return $this->content;
    }

    protected function postCompile($content)
    {
        $content = parent::postCompile($content);
        
        if (($baseUrl = $this->getBaseUrl()) !== null)
        {
            $content = str_replace('url(', 'url(' . $baseUrl, $content);
        }
      
        return $content;
    }
}