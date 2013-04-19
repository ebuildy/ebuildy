<?php

namespace eBuildy\Container;

class BaseContainer 
{
    protected $application;

    public function __construct($application)
    {
        $this->application = $application;
    }

    public function getApplication()
    {
        return $this->application;
    }
    
   public function get($service) 
   {
	$method = \eBuildy\Helper\ResolverHelper::resolveServiceMethodName($service);
  
	return $this->$method(); 
    }
}