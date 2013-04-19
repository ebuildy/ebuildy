<?php

namespace eBuildy\Component;

class Session
{
    protected $started = false;
    protected $name = '';
    
    public function initialize($configuration)
    {
        $this->name = $configuration['name'];
    }
    
    public function start()
    {
        $this->started = true;
        
        session_name($this->name);

        session_start();        
    }
    
    public function set($key, $value)
    {
        if (!$this->started)
        {
            $this->start();
        }
        
        $_SESSION[$key] = $value;
    }
    
    public function get($key, $default = null)
    {
        if (!$this->started)
        {
            $this->start();
        }
        
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }
    
    public function all()
    {
        if (!$this->started)
        {
            $this->start();
        }
        
        return $_SESSION;
    }

    public function isStarted()
    {
        return $this->started;
    }
}