<?php

namespace eBuildy\Templating;

/**
 * @Service("templating", "templating")
 */
class Templating
{
    protected $templateDirectory = null;
    protected $variables = array();
    protected $exposeMethods = array();    
    
    public $currentCompiler;
    
    private $stackCompilers = array();
        
    public function initialize($configuration)
    {
        if (isset($configuration['exposes']))
        {
            $this->exposeMethods = $configuration['exposes'];
        }
    }
    
    public function addVariable($name, $value)
    {
        $this->variables[$name] = $value;
    }
    
    public function addVariables($vars)
    {
        $this->variables = array_merge($this->variables, $vars);
    }
    
    public function getContext()
    {
    //   return $this->currentCompiler->getContext();
        return $this->stackCompilers[0]->getContext();
    }
    
    public function render($template, $data = array())
    {
        $compiler = $this->getCompiler($template);

        return $compiler->render($template, array_merge($this->variables, $data));
    }
        
    public function renderDecoratedTemplate($templates, $data = array())
    {
        $templateContent = null;
        
        foreach($templates as $template)
        {
            $compiler = $this->getCompiler($template);
            
            array_push($this->stackCompilers, $compiler);
            
            if ($templateContent !== null)
            {
                $compiler->setBlock('content',  $templateContent);
            }
            
            $templateContent = $compiler->render($template, array_merge($this->variables, $data));
            
            array_pop($this->stackCompilers);
        }
        
        return $templateContent;
    }
    
    protected function getCompiler($templateName)
    {
        if (strpos($templateName, '.twig') !== false)
        {
            return new TwigCompiler($this->container, $this->exposeMethods);
        }
        else
        {
            return new Compiler($this->container, $this->exposeMethods);
        }
    }
}