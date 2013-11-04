<?php

namespace eBuildy\Worker;

class WebWorker extends BaseWorker
{
    public function initialize($inputGet, $inputPost, $inputCookie, $inputFile, $inputServer)
    {
        $this->input = new \eBuildy\Component\Request();
        $this->output = new \eBuildy\Component\Response('', array('Content-Type' => 'text/html; charset=utf-8'));
        
        $this->input->initialize($inputGet, $inputPost, array(), $inputCookie, $inputFile, $inputServer);
    }
    
    public function run()
    {
        $router  = $this->container->getRouterService();        
        
        $this->application->dispatchEvent(\eBuildy\Component\Application::EVENT_REQUEST_READY, $this->input);

        $this->input->route = $router->matchRequest($this->input);

        $controllerClass = $this->input->route['controller'];
        
        $controller= new $controllerClass($this->container);

        $controller->execute($this->input, $this->output);

        return $this->output->render();
    }
    
    public function onException(\Exception $e)
    {  
	$this->application->onException($e);
	
        $this->onError($e->getCode(), 'Uncaught exception: ' . $e->getMessage(), $e->getFile(), $e->getLine(), null, $e->getTrace());
    }

    public function onError($errno, $errstr, $errfile, $errline, $context, $trace = null)
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
        
        $__trace = array_values($__trace);    
      //  var_dump($__trace);die();
        for($i = 1; $i < count($__trace); $i++)
        {
            $current = $__trace[$i];
            $previous = $__trace[$i - 1];
            
            if (!isset($previous['file']))
            {
                continue ;
            }
            
            $item = array('file' => $previous['file'], 'line' => $previous['line'], 'function' => $current['function'], 'args' => $current['args']);
            
            if (isset($current['type']))
            {
                $item['caller'] = $current['class'] . $current['type'] . $current['function'];
            }
            elseif (isset($current['function']))
            {
                $item['caller'] = $current['function'];
            }
            else
            {
                $item['caller'] = '';
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
                    elseif ($type === 'array')
                    {
                        $buffer []= $type;
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
                $item['args'] = array();
            }
            
            $trace []= $item;
            
            //var_dump($item['file'], $item['line'], $item['function']);
        }        
        
        $debugLogs = $this->container->get("ebuildy.debug")->getLogs();
                
        include(VENDOR_PATH.'ebuildy/ebuildy/view/error.phtml');
        
        exit(1);
    }
}