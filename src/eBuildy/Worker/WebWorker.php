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
        
        $controller= new $controllerClass($this->container);

        $this->output = $controller->execute($this->input);

        return $this->output->render();
    }
    
    public function onException(\Exception $e)
    {  
        $this->onError($e->getCode(), 'Uncaught exception: ' . $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace());
    }

    public function onError($errno, $errstr, $errfile, $errline, $trace = null)
    {
        if (ob_get_level() > 0)
        {
            ob_clean();
        }
        
        $title = $errstr;
        $code = $errno;
        $file = $errfile;
        $line = $errline;
        $__trace = $trace === null ? debug_backtrace() : $trace;
        $trace = array();
        
        array_shift($__trace);
        
        for($i = 2; $i < count($__trace); $i++)
        {
            $current = $__trace[$i];
            
            $item = array('file' => $__trace[$i - 1]['file'], 'line' => $__trace[$i - 1]['line'], 'function' => $__trace[$i]['function'], 'args' => $__trace[$i]['args']);
            
            if (isset($current['type']))
            {
                $item['caller'] = $current['class'] . $current['type'] . $current['function'];
            }
            elseif (isset($current['function']))
            {
                $item['caller'] = $current['function'];
            }
            
            if (isset($current['args']))
            {
                $args = $current['args'];
                $buffer = array();
                
                foreach($args as $arg)
                {
                    $type = gettype($arg);
                    
                    if ($type === 'object')
                    {
                        $buffer []= (string) get_class($arg);
                    }
                    else
                    {
                        $buffer []= var_export($arg, true);// $type;
                    }
                }
                                 
                $item['args'] = $buffer;
            }
            else
            {
                $item['args'] = '';
            }
            
            $trace []= $item;
            
            //var_dump($item['file'], $item['line'], $item['function']);
        }        
        
        $debugLogs = $this->container->get("ebuildy.debug")->getLogs();
                
        include(VENDOR_PATH.'ebuildy/ebuildy/view/error.phtml');
        
        exit(1);
    }
}