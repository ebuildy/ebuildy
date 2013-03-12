<?php

namespace eBuildy\Debug;

/**
 * @Service("ebuildy.debug")
 */
class Debug
{
    protected $logValues = array();
    
    public function log($name, $value = null)
    {        
        if ($value === null)
        {
             $this->logValues []= array('name' => '', 'value' => $name);
        }
        else
        {
            $this->logValues []= array('name' => $name, 'value' => $value);
        }
    }
    
    public function getLogs()
    {
        return $this->logValues;
    }
}