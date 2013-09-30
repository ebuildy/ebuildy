<?php

namespace eBuildy\DataBinder\Validator;

class CallbackValidator extends Validator
{    
    protected $callback;
    
    public function __construct($callback)
    {
        parent::__construct();
        
        $this->callback = $callback;
    }
    
    public function validate($value)
    {
        return call_user_func($this->callback, $value);
    }
}