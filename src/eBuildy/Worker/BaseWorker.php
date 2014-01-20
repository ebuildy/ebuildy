<?php

namespace eBuildy\Worker;

abstract class BaseWorker
{
    public $input;
    public $output;
    
	/**
	 * @var \Container 
	 */
    public $container;
    
    public function __construct($container)
    {
        $this->container = $container;
        
        $this->initializeErrorHandling();
    }
    
     protected function initializeErrorHandling()
    {
        set_error_handler(array($this, 'onError'));

        set_exception_handler(array($this, 'onException'));
    }
    
    abstract public function run();
    
    abstract public function onException(\Exception $e);

    abstract public function onError($errno, $errstr, $errfile, $errline, $errcontext);
}