<?php

namespace eBuildy\Container;

class BaseContainer 
{    
   public function get($service) 
   {
	$method = \eBuildy\Helper\ResolverHelper::resolveServiceMethodName($service);
  
	return $this->$method(); 
    }
}