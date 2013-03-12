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
                
        $this->eventDispatcher = new EventDispatcher();
    }

    public function run()
    {    
        $this->initializeErrorHandling();
        $this->initializeEventDispatcher();
        
        if (PHP_SAPI === 'cli')
        {
            return $this->runCli();
        }
        else
        {
            return $this->runHttp();
        }
    }
    
    protected function runHttp()
    {  
        $request = $this->container->getRequestService();
        $router  = $this->container->getRouterService();

        $request->initialize($_GET, $_POST, array(), $_COOKIE, $_FILES, $_SERVER);
        
        $this->dispatchEvent(self::EVENT_REQUEST_READY, $request);

        $request->route = $router->matchRequest($request);

        $controller = $this->newInstance($request->route['controller']);

        $response = $controller->execute($request);

        return $response->render();
    }
    
    protected function runCli()
    {
        global $argv;
        
//        if ($_SERVER['USER'] !== 'www-data')
//        {
//            throw new \Exception('You must run this command as www-data user instead of "' . $_SERVER['USER'].'" !');
//        }
                
        $commands = $this->container->commands;
        
        if ($commands === null)
        {
            throw new \Exception('There is no commands in your project!');
        }
        
        set_time_limit(0);
        
        if (count($argv) == 1)
        {
            print "Welcome to eBuildy CLI, here your commands:".PHP_EOL;
            
            foreach($commands as $name => $class)
            {
                print "\t--> ".$name."  ".$class.PHP_EOL;
            }
            
            print PHP_EOL;
            
            exit(1);
        }
        
        $commandInput = $argv[1];
        
        if (!isset($commands[$commandInput]))
        {
            throw new \Exception('Command "'.$commandInput.'" is not found!');
        }
        
        $command = $commands[$commandInput];

        $commandInstance = new $command();

        $commandInstance->run();
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

    protected function initializeErrorHandling()
    {
        set_error_handler(array($this, 'onError'));
        
        set_exception_handler(array($this, 'onException'));
    }
    
    public function onException(\Exception $e)
    {
        if (ob_get_level() > 0)
        {
            ob_clean();
        }
        
        $title = $e->getMessage();
        $code = $e->getCode();
        $file = $e->getFile();
        $line = $e->getLine();
        $trace = $e->getTrace();
        
        $debugLogs = $this->container->get("ebuildy.debug")->getLogs();
        
        if (PHP_SAPI === 'cli')
        {
            var_dump($debugLogs);
            
            die("\033[1;31m<!> ".$title.' ['.str_replace(realpath(ROOT), '', $file).':'.$line."] <!>\033[0m".PHP_EOL);
            exit(1);
        }
        else
        {
            include(VENDOR_PATH.'ebuildy/ebuildy/view/exception.phtml');
        }
    }

    public function onError($errno, $errstr, $errfile, $errline, $errcontext)
    {
        if (ob_get_level() > 0)
        {
            ob_clean();
        }
        
        $title = $errstr;
        $code = $errno;
        $file = $errfile;
        $line = $errline;
        $trace = debug_backtrace();
        
        array_splice($trace, 0, 1);
        
        $debugLogs = $this->container->get("ebuildy.debug")->getLogs();
        
        if (PHP_SAPI === 'cli')
        {
            die("\033[1;31m<!> ".$title.' ['.str_replace(realpath(ROOT), '', $file).':'.$line."] <!>\033[0m".PHP_EOL);
            
        }
        else
        {
            include(VENDOR_PATH.'ebuildy/ebuildy/view/error.phtml');
        }
        
        exit(1);
    }
}