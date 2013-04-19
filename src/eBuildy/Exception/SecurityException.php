<?php

namespace eBuildy\Exception;

class SecurityException extends \Exception
{
    public $roleRequired;
    
    public function __construct($code = 0, $roleRequired = '')
    {
        parent::__construct('Access forbidden', $code);
        
        $this->roleRequired = $roleRequired;
    }
}