<?php

namespace eBuildy\Container;

trait ContainerAwareTrait
{
    /**
     * @var \Container
     */
    protected $container;
        
    public function setContainer($container)
    {
        $this->container = $container;
    }
}