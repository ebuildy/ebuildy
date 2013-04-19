<?php

namespace eBuildy\Component;

use eBuildy\Component\EventDispatcher\EventDispatcher;

abstract class Application
{
    public $env;
    public $debug;
    
    /**
     * @var \Container
     */
    public $container;
            
    protected $eventDispatcher;
    protected $parameters;
    protected $serviceInstances = array();
    
    static private $instance;
    
    const EVENT_REQUEST_READY = 'ebuildy.request.ready';
    const EVENT_EXCEPTION_OCCURED = 'ebuildy.exception';
    const EVENT_ERROR_OCCURED = 'ebuildy.error';
    
    static public function getInstance()
    {
        return self::$instance;
    }

    public function __construct($env = 'dev')
    {
        self::$instance = $this;

        $this->env   = $env;
    }

    public function run()
    {    
        $this->initializeEventDispatcher();
        
        if (PHP_SAPI === 'cli')
        {
            global $argv;
                        
            $worker = new \eBuildy\Worker\CommandWorker($this);
            
            $worker->initialize($argv);
            
            return $worker->run();
        }
        else
        {
            $worker = new \eBuildy\Worker\WebWorker($this);
            
            $worker->initialize($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER);
            
            return $worker->run();
        }
    }
        
    public function getParameter($key, $default = null)
    {
        return isset($this->parameters[$key]) ? $this->parameters[$key] : $default;
    }
    
    public function addEventListener($eventName, $listener)
    {
        return $this->eventDispatcher->addListener($eventName, $listener);
    }
    
    public function dispatchEvent($event, $data = null)
    {
        return $this->eventDispatcher->dispatch($event, $data);
    }

    protected function newInstance($class)
    {
        return new $class;
    }
    
    protected function initializeEventDispatcher()
    {
        $this->eventDispatcher = new EventDispatcher($this->container);
        
        $listeners = $this->container->eventListeners;
        
        if ($listeners !== null)
        {
            foreach($listeners as $eventName => $eventListeners)
            {
                foreach($eventListeners as $listener)
                {
                    $this->addEventListener($eventName, $listener);    
                }
            }
        }
    }
}