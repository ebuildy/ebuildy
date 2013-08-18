<?php

namespace eBuildy\Templating;

class TwigCompiler extends Compiler
{    
    /**
     * 1. Set parent blocks
     * 2. Render template
     * 3. Get template blocks
     * 
     * @return string
     */
    protected function __render()
    {        
        $target = 'templates/' . basename($this->templatePath).'.php';
        
        $template = $this->_compile();
	
	$template->container = $this->container;        
	
	$template->setBlocks($this->getBlocks());
	
        $content = $template->render($this->data);
	
	$this->setBlocks(array());
		
	foreach($template->blocks as $blockId => $block)
	{
	    if (is_array($block))
	    {
		ob_start();

		call_user_func_array(array($template, $block[1]), array($this->data));

		$buffer = ob_get_clean();

		$this->setBlock($blockId, $buffer);
	    }
	}
	
	return $content;
    }
    
    protected function _compile()
    {
        $env = new \Twig_Environment(new \Twig_Loader_Filesystem(SOURCE_PATH), array('autoescape' => false, 'cache' => TMP_PATH . 'twig', 'debug' => true, 'base_template_class' => 'eBuildy\Templating\TwigBaseTemplate'));
        
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
        
        $template = $env->loadTemplate(str_replace(SOURCE_PATH, '', $this->templatePath)); 
        
        return $template;
    }
}