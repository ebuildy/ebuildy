<?php

namespace eBuildy\Component;

/**
 * @Service("hook", "hook")
 */
class HookService
{
	private $hooks;
	
	public function initialize($configuration)
	{
		$this->hooks = $configuration['hooks'];
	}
	
	public function dispatch($name, $data = null)
    {
        if (!isset($this->hooks[$name])) 
        {
            return false;
        }
        
        foreach ($this->hooks[$name] as $callable) 
        {			
            $serviceInstance = $this->container->get($callable['service']);
            
            call_user_func(array($serviceInstance, $callable['method']), $data);
        }

        return true;
    }
    
    public function addCallable($name, $callable, $priority = 0)
    {
        if (!isset($this->hooks[$name]))
        {
            $this->hooks[$name] = array();
        }
        
        $this->hooks[$name] [] = array('priority' => $priority, 'callable' => $callable);
    }

    public function remove($name)
    {
        if (isset($this->hooks[$name]))
		{
            unset($this->hooks[$name]);
        }
    }
}