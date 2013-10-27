<?php

namespace eBuildy\Templating;

class TwigBaseTemplate  implements \Twig_TemplateInterface
{
    public $env;
    public $container;
    public $blocks;
    
    use BlockHelperTrait;
    
    public function __construct(\Twig_Environment $env)
    {
        $this->env = $env;
    }

    public function display(array $context, array $blocks = array())
    {
        return $this->doDisplay($context, $blocks);
    }

    public function getEnvironment()
    {
        return $this->env;
    }

    public function render(array $context)
    {
        ob_start();
        
        $this->doDisplay($context, array());
        
        return ob_get_clean();
    }
    
    public function displayBlock($name, array $context, array $blocks = array())
    {
        echo $this->block($name);
    }
        
    protected function getAttribute($object, $item, array $arguments = array(), $type = \Twig_TemplateInterface::ANY_CALL, $isDefinedTest = false, $ignoreStrictCheck = false)
    {
		$item = trim($item);
	
        if (array_key_exists($item, $object))
        {
            return $object[$item];
        }
        else 
        {
            throw new \Twig_Error('Twig error: variable ' . $item . ' doest not exist!');
        }
    }
}