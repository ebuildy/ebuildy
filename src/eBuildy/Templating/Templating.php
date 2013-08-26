<?php

namespace eBuildy\Templating;

/**
 * @Service("templating", "templating")
 */
class Templating
{
    protected $templateDirectory = null;
    protected $exposeMethods = array();    
    
    public $currentCompiler;
    public $variables;
    
    private $stackCompilers = array();
        
    public function initialize($configuration)
    {
        if (isset($configuration['exposes']))
        {
            $this->exposeMethods = $configuration['exposes'];
            $this->variables = new \eBuildy\Helper\ParameterBag();
        }
    }
    
    public function addVariable($name, $value)
    {
        $this->variables->set($name, $value);
    }
        
    public function addVariables($vars)
    {
        $this->variables->add($vars);
    }
    
    public function getContext()
    {
        return count($this->stackCompilers) === 0 ? null : $this->stackCompilers[0]->getContext();
    }
    
    public function render($template, $data = array())
    {
        $compiler = $this->getCompiler($template);

        return $compiler->render($template, array_merge($this->variables->all(), $data));
    }
        
    public function renderDecoratedTemplate($templates, $data = array())
    {
        $templateContent = null;
	$blockPreviousTemplate = null;
        
        foreach($templates as $template)
        {
            $compiler = $this->getCompiler($template);
            
            array_push($this->stackCompilers, $compiler);
	    
	    if ($blockPreviousTemplate !== null)
	    {
		$compiler->setBlocks($blockPreviousTemplate);
	    }
                        
            $templateContent = $compiler->render($template, array_merge($this->variables->all(), $data));
	    
	    $blockPreviousTemplate = $compiler->getBlocks();

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