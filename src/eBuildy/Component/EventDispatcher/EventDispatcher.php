<?php

namespace eBuildy\Component\EventDispatcher;

use eBuildy\Component\ApplicationAware;
use eBuildy\Component\EventDispatcher\Event;

class EventDispatcher
{
    use ApplicationAware;
    
    private $listeners = array();
    
     public function dispatch($event, $data = null)
    {
        if (is_string($event)) 
        {
            $event = new Event($event, $data);
        }

        if (!isset($this->listeners[$event->name])) 
        {
            return false;
        }
        
        $eventListeners = $this->listeners[$event->name];

        foreach ($eventListeners as $listener) 
        {
            $serviceInstance = $this->get($listener['listener']['service']);
            
            call_user_func(array($serviceInstance, $listener['listener']['method']), $event);
            
            if ($event->propagationStopped) 
            {
                break;
            }
        }

        return true;
    }
    
    public function addListener($eventName, $listener, $priority = 0)
    {
        if (!isset($this->listeners[$eventName]))
        {
            $this->listeners[$eventName] = array();
        }
        
        $this->listeners[$eventName] [] = array('priority' => $priority, 'listener' => $listener);
    }

    public function removeListener($eventName, $listener)
    {
        if (!isset($this->listeners[$eventName])) {
            return;
        }

    }
}