<?php

namespace eBuildy\DataBinder\Validator;

class EmailValidator extends StringValidator
{
    static public $ERROR_SYNTAX_INVALID = 'validator.email.syntax';
    static public $ERROR_HOST_VALID = 'validator.email.host';
    
    public function __construct($required = false, $checkMx = false, $checkHost = false)
    {        
         parent::__construct($required);
         
        $this->mergeOptions(array('checkMX' => $checkMx, 'checkHost' => $checkHost));
    }
    
    public function validate($value)
    {
        $parentValidation = parent::validate($value);
        
        if ($parentValidation === true)
        {        
            if (filter_var($value, FILTER_VALIDATE_EMAIL) === false)
            {
                return self::$ERROR_SYNTAX_INVALID;
            }

            $host = substr($value, strpos($value, '@') + 1);

            if ($this->options['checkMX'] && !$this->checkMX($host))
            {
                return self::$ERROR_HOST_VALID;
            } 

            if ($this->options['checkHost'] && $this->checkHost($host))
            {
                return self::$ERROR_HOST_VALID;
            }

            return true;
        }
        else
        {
            return $parentValidation;
        }
    }
    
    /**
     * Check DNS Records for MX type.
     *
     * @param string $host Hostname
     *
     * @return Boolean
     */
    public function checkMX($host)
    {
        return checkdnsrr($host, 'MX');
    }

    /**
     * Check if one of MX, A or AAAA DNS RR exists.
     *
     * @param string $host Hostname
     *
     * @return Boolean
     */
    public function checkHost($host)
    {
        return $this->checkMX($host) || (checkdnsrr($host, "A") || checkdnsrr($host, "AAAA"));
    }
}