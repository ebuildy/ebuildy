<?php

namespace eBuildy\Worker;

class WebWorker extends BaseWorker
{
    public function initialize($inputGet, $inputPost, $inputCookie, $inputFile, $inputServer)
    {
        $this->input = $this->container->getRequestService();
        
        $this->input->initialize($inputGet, $inputPost, array(), $inputCookie, $inputFile, $inputServer);
    }
    
    public function run()
    {
        $router  = $this->container->getRouterService();        
        
        $this->application->dispatchEvent(\eBuildy\Component\Application::EVENT_REQUEST_READY, $this->input);

        $this->input->route = $router->matchRequest($this->input);

        $controllerClass = $this->input->route['controller'];
        
        $controller= new $controllerClass();

        $this->output = $controller->execute($this->input);

        return $this->output->render();
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
        
         include(VENDOR_PATH.'ebuildy/ebuildy/view/exception.phtml');
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
        
        include(VENDOR_PATH.'ebuildy/ebuildy/view/error.phtml');
        
        exit(1);
    }
}