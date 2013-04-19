<?php

namespace eBuildy\DataBinder\Validator;

/**
 * @Service("validator.string")
 */
class StringValidator extends Validator
{    
    public function __construct($options = array())
    {
        parent::__construct();
        
        $this->mergeOptions(array('min' => 0, 'max' => 1000000, 'required' => false));
        $this->mergeOptions($options);
    }
    
    public function validate($value)
    {
        if ($this->options['required'] && strlen($value) == 0)
        {
            return self::$ERROR_REQUIRED;
        }
        
        return true;
    }
}