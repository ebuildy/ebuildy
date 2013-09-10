<?php

namespace eBuildy\DataBinder\Validator;

/**
 * @Service("validator.string")
 */
class StringValidator extends Validator
{    
    static public $ERROR_TOO_MAX = 'validator.string.max';
    static public $ERROR_TOO_MIN = 'validator.string.min';
    
    public function __construct($required = true, $min = 0, $max = 100000)
    {
        parent::__construct();
        
        $this->mergeOptions(array('min' => $min, 'max' => $max, 'required' => $required));
    }
    
    public function validate($value)
    {
        $s = strlen($value);
        
        if ($this->options['required'] && ($value === null || $s == 0))
        {
            return self::$ERROR_REQUIRED;
        }
        
        $max = (int) $this->options['max'];
        $min = (int) $this->options['min'];
        
        if ($max > 0 && $s > $max)
        {
            return self::$ERROR_TOO_MAX;
        }
        elseif ($min > 0 && $s < $min)
        {
            return self::$ERROR_TOO_MIN;
        }
        
        return true;
    }
}