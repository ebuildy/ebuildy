<?php

namespace eBuildy\Component;

trait ApplicationAware
{
    protected $application = null;
    
    public function getApplication()
    {
        if ($this->application === null)
        {
            $this->application = Application::getInstance();
        }
        
        return $this->application;
    }
    
    public function get($service)
    {
        return $this->getApplication()->container->get($service);
    }
        
    public function getParameter($key, $default = null)
    {
        return $this->getApplication()->getParameter($key, $default);
    }
}