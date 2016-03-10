<?php

namespace eBuildy\Worker;

class CommandWorker extends BaseWorker
{
    protected $parameters;
    
    public function initialize($parameters)
    {
		$buffer = $this->container->getCommandConfiguration();
		
        $this->parameters = $parameters;
        $this->commands = $buffer['commands'];
    }
    
    protected function runAutoCompletion()
    {
        $parameters = $this->parameters;
        
        array_shift($parameters);
        array_shift($parameters);
        
        if (count($parameters) == 1)
        {
            foreach($this->commands as $name => $class)
            {
                print $name . PHP_EOL;
            }
        }
        else
        {
            $commandInput = $parameters[1];
            
            $command = $this->commands[$commandInput];

            $commandInstance = new $command($commandInput);
            
            die(implode(PHP_EOL, $commandInstance->autoCompletion()));
        }
        
        //var_dump($parameters);
    }

    public function run()
    {
        global $argv;
        
        $parameters = $this->parameters;
        $parametersCount = count($parameters);
      
        $input = $this->input = new \Symfony\Component\Console\Input\ArgvInput($parameters);
        $output = $this->output = new \Symfony\Component\Console\Output\ConsoleOutput();
        
        if ($this->commands === null)
        {
            throw new \Exception('There is no commands in your project!');
        }
        
        set_time_limit(0);

        if ($input->getFirstArgument() === '__autocomplete__')
        {
            return $this->runAutoCompletion();
        }
        
        if ($input->getFirstArgument() === null)
        {
            foreach($this->commands as $name => $class)
            {
                print $name . PHP_EOL;
            }
            
            print PHP_EOL;
            
            exit(1);
        }
        
        $commandInput = $input->getFirstArgument();
        
        if (!isset($this->commands[$commandInput]))
        {
            throw new \Exception('Command "'.$commandInput.'" is not found!');
        }
        
        $command = $this->commands[$commandInput];

        $commandInstance = new $command($this->container);
                
        return $commandInstance->run(new \Symfony\Component\Console\Input\ArgvInput($argv), $output);
    }
    
    public function onException($e)
    {
        $title = $e->getMessage();
        $code = $e->getCode();
        $file = $e->getFile();
        $line = $e->getLine();
        $trace = $e->getTrace();
        
        $debugLogs = $this->container->get("ebuildy.debug")->getLogs();
        
        $this->output->writeln("");
        $this->output->writeln("<error>".  str_repeat(" ", 100)."</error>");
        $this->output->writeln("<error> " . $title.' ['.str_replace(realpath(ROOT), '', $file).':'.$line."] </error>");
        $this->output->writeln("<error>".  str_repeat(" ", 100)."</error>");
        $this->output->writeln("");
    }

    public function onError($errno, $errstr, $errfile, $errline, $errcontext)
    {
        die("\033[1;31m<!> ".$errstr.' ['.str_replace(realpath(ROOT), '', $errfile).':'.$errline."] <!>\033[0m".PHP_EOL);
    }
}
