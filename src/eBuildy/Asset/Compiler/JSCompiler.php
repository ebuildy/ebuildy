<?php

namespace eBuildy\Asset\Compiler;

class JSCompiler extends AssetCompiler
{
    public $content;

    protected function doCompile($source, $target)
    {
        $this->content = $this->compileFile($source);

        if ($target !== null)
        {
            $dir = dirname($target);
            
            if (!is_dir($dir))
            {
                mkdir($dir, 0777, true);
            }
            
            file_put_contents($target, $this->content);
        }
        
        return $this->content;
    }



}