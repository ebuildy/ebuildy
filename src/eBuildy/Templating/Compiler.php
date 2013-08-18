<?php

namespace eBuildy\Templating;

use eBuildy\Helper\ResolverHelper;

class Compiler
{
    use BlockHelperTrait;
    
    protected $templatePath = null;
    protected $data = null;
    protected $container = null;
    protected $alias = null;
    protected $context = null;    
    protected $exposedMethod = null;
    
    public function __construct($container, $exposedMethods = array())
    {   
        $this->container = $container;
        $this->exposedMethod = $exposedMethods;
    }
    
    public function render($templatePath, $data = array())
    {
        $this->templatePath = $templatePath;
        $this->data = $data;

        $this->context = str_replace('//', '/', ResolverHelper::getModulePathFromView($templatePath) . '/');

//        $trace=debug_backtrace();
//        $caller=array_shift($trace);
//        
//        debug($caller);
        
        $buffer = $this->__render();

	if (!$this->hasBlock('content'))
	{
	    $this->setBlock('content', $buffer);
	}
	
	return $buffer;
    }
    
    protected function __render()
    {  
        ob_start();
        
        $__template_name = basename($this->templatePath);

        extract($this->data);

        include($this->templatePath);

        $templateContent = ob_get_clean();
        
        return $templateContent;
    }
    
    public function __call($name, $arguments)
    {
        if (isset($this->exposedMethod[$name]))
        {            
            $method = $this->exposedMethod[$name];
            
            $service = call_user_func(array($this->container, ResolverHelper::resolveServiceMethodName($method['service'])));

            return call_user_func_array(array($service, $method['method']), $arguments);
        }
        else
        {
            throw new \eBuildy\Exception\NotFoundException('Method " ' . $name . ' " ');
        }
    }

//    public function __get($name)
//    {
//        return $this->container->get(str_replace('_', '.', $name));
//    }
    
    public function getContext()
    {
        return $this->context;
    }
    
//    public function execute($routeName, $data = array())
//    {
//        $request = new \eBuildy\Component\Request();
//        
//        $request->initialize($data);
//        
//        $request->route = $this->container->getRouterService()->get($routeName);
//
//        $controller = new $request->route['controller']();
//
//        $response = $controller->execute($request);
//
//        return $response->render(false);
//    }
}