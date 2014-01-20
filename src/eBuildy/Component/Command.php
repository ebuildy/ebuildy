<?php

namespace eBuildy\Component;

abstract class Command
{    
    /**
     * The main container
     * 
     * @var \Container
     */
    protected $container;
    
    public function __construct($container)
    {
        $this->container = $container;
    }
    
    public function getArgument($index = 0)
    {
        global $argv;

        return $argv[$index + 1];
    }
    
    public function autoCompletion()
    {
        return array('nop', 'nop');
    }
    
    public function execute($input, $output)
    {
        
    }
}