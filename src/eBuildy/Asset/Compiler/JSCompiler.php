<?php

namespace eBuildy\Asset\Compiler;

class JSCompiler extends AssetCompiler
{
    public $content;

    protected function doCompile($source, $target)
    {
        $this->content = $this->compileFile($source);
        
        $this->saveCompiledFile($this->content, $target);
        
        return $this->content;
    }



}