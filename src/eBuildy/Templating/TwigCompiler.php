<?php

namespace eBuildy\Templating;

class TwigCompiler extends Compiler
{    
    protected function __render()
    {        
        $target = 'templates/' . basename($this->templatePath).'.php';
        
        $env = new \Twig_Environment(new \Twig_Loader_Filesystem(SOURCE_PATH), array('autoescape' => false, 'cache' => true, 'debug' => true, 'base_template_class' => 'eBuildy\Templating\TwigBaseTemplate'));
        
        $env->addGlobal('__template_name', basename($this->templatePath));
                
        foreach($this->exposedMethod as $methodName => $method)
        {
            if (isset($method['service']))
            {
                $env->addFunction($methodName, new \Twig_SimpleFunction($methodName, '$this->container->' . \eBuildy\Helper\ResolverHelper::resolveServiceMethodName($method['service']) . '()->' . $method['method']));
            }
            else
            {
                $env->addFunction($methodName, new \Twig_SimpleFunction($methodName, '$this->' . $methodName));
            }
        }
//        
//        $lexer = new \Twig_Lexer($env);
//        
//        $parser = new \Twig_Parser($env);
//        
//        $stream = $lexer->tokenize(file_get_contents($this->templatePath), $this->templatePath);
//       
//        $nodes = $parser->parse($stream);
//        
//        $compiler = new \Twig_Compiler($env);
//        
//        $compiler->compile($nodes);
//        
//        $content = $compiler->getSource();
//        
//        \eBuildy\Component\Cache::writeTempFile($target, $content);
//                
//        $this->templatePath = TMP_PATH . $target;
//        
//        include($this->templatePath);
        
        $template = $env->loadTemplate(str_replace(SOURCE_PATH, '', $this->templatePath));
        
        $template->setBlocks($this->blocks);
        $template->container = $this->container;

        return $template->render($this->data);
    }
}