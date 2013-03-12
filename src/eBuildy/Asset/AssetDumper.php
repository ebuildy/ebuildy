<?php

namespace eBuildy\Asset;

use eBuildy\Asset\AssetResolver;

class AssetDumper
{
    protected $path;
    
    public $assets;
    
    /**
     * @var \eBuildy\Asset\AssetHelper
     */
    public $assetHelper;
    
    public function __construct($path)
    {
        $this->path = $path;
        $this->assets = array();
    }
    
    public function dump()
    {
        $Iterator  = new \RecursiveIteratorIterator($iterator = new \RecursiveDirectoryIterator($this->path));
        $Regex    = new \RegexIterator($Iterator, '/^.+\.phtml/i', \RecursiveRegexIterator::GET_MATCH);

        foreach ($Regex as $file => $vv)
        {
            $this->parseFile($file);
        }
                        
        return $this->assets;
    }
    
    protected function parseFile($file)
    {
        $tokens = token_get_all(file_get_contents($file));
      //  var_dump($tokens);die();
        
        $count = count($tokens) - 1;
        
        for($i = 0; $i < $count; $i++)
        {
            $token = $tokens[$i];
            
            if ($token[0] == 307)
            {
                $method = $token[1];
                
                if ($method === 'getCss')
                {
                    $type = 'css';
                }
                elseif ($method === 'getJs')
                {
                    $type = 'js';
                }
                else
                {
                    $type = null;
                }
                
                if ($type !== null)
                {
                    $arg = $this->getArgument($tokens, $i);
                    
                    $sourcePath = AssetResolver::resolveSourcePath($arg, \eBuildy\Helper\ResolverHelper::getModulePathFromView($file));
                              
                    if ($sourcePath === null)
                    {
                        // var_dump(array('file' => $file, 'type' => $type, 'source' => $arg));
                    }
                    else
                    {
                        $this->assets[$sourcePath] = $type;
                    }
                }
            }
        }
    }
    
    protected function getArgument($tokens, &$index)
    {
        $buffer = "";
        $count = count($tokens) - 1;
        
        // 1. Move to open bracket
        while($tokens[$index] !== '(' && $index < $count)
        {
            $index++;
        }
        
        $index++;
                    
        // 2. Fetch until close bracket
        while($index < $count)
        {
            $token = $tokens[$index];
            
            if ($token === ')' || $token === ',')
            {
                break;
            }
            
            if (is_string($token))
            {
                $buffer .= $token;
            }
            else
            {
                $buffer .= $token[1];
            }

            $index++;
        }
        
       eval("\$res = $buffer;");
       
       return $res;
    }
}