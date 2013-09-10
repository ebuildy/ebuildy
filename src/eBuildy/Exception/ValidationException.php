<?php

namespace eBuildy\Exception;

class ValidationException extends \Exception 
{
    public $binderLabel;
    public $binderValue;
    
    public function __construct($message, $label, $value)
    {
        parent::__construct($message);
        
        $this->binderLabel = $label;
        $this->binderValue = $value;
    }
}