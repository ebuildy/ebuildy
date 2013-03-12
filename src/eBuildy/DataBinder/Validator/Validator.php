<?php

namespace eBuildy\DataBinder\Validator;

abstract class Validator
{
    static public $ERROR_REQUIRED = 'validator.string.required';
    
    protected $options = array();
    
    public function __construct($options = array())
    {
        $this->options = $options;
    }
    
    public function mergeOptions($options)
    {
        $this->options = array_merge($this->options, $options);
    }
    
   abstract public function validate($value);  
}