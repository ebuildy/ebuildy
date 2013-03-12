<?php

namespace eBuildy\Container;

class ContainerAware
{
    /**
     * @var \Container
     */
    protected $container;
    
    public function __construct($container)
    {
        $this->container = $container;
    }
    
    public function setContainer($container)
    {
        $this->container = $container;
    }
}