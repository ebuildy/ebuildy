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
        
        $Iterator  = new \RecursiveIteratorIterator($iterator = new \RecursiveDirectoryIterator($this->path));
        $Regex    = new \RegexIterator($Iterator, '/^.+\.twig/i', \RecursiveRegexIterator::GET_MATCH);

        foreach ($Regex as $file => $vv)
        {
            $this->parseTwigFile($file);
        }
                        
        return $this->assets;
    }
    
    protected function parseTwigFile($file)
    {
        $env = new \Twig_Environment(new \Twig_Loader_Filesystem(SOURCE_PATH), array('autoescape' => false, 'cache' => false, 'base_template_class' => 'eBuildy\Templating\TwigBaseTemplate'));
                
        $lexer = new \Twig_Lexer($env);
        
        $parser = new \Twig_Parser($env);
        
        $stream = $lexer->tokenize(file_get_contents($file), $file);
        
        while(!$stream->isEOF())
        {
            $node = $stream->next();
            
            $method = $node->getValue();
 
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
                $arg = $this->getTwigArgument($stream);

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
    
    protected function getTwigArgument(\Twig_TokenStream $stream)
    {
        $buffer = "";
        
        // 1. Move to open bracket
        while(!$stream->isEOF())
        {
            if ($stream->next()->getValue() === '(')
            {
                break;
            }
        }
                            
        // 2. Fetch until close bracket
        while(!$stream->isEOF())
        {
            $token = $stream->next()->getValue();
            
            if ($token === ')' || $token === ',')
            {
                break;
            }
            
            $buffer .= $token;
        }
    
        return $buffer;
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