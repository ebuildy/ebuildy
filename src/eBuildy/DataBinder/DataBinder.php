<?php

namespace eBuildy\DataBinder;

use eBuildy\DataBinder\Validator\StringValidator;

abstract class DataBinder
{
    public $name;
    
    protected $data; 
    protected $dataNormed;
    protected $errors = array();
    
    protected $options;
    protected $preTransforms;
    
    public function __construct($name = '', $options = array())
    {
        $this->name = $name;
        $this->options = $options;
        
        $defaultPreTransforms = array('trim');

        if (isset($options['pre_transforms']))
        {
            $this->preTransforms = array_merge($defaultPreTransforms, $options['pre_transforms']);
        }
        else
        {
            $this->preTransforms = $defaultPreTransforms;
        }
        
        if (isset($options['required']) && $options['required'] === true)
        {
            if (!isset($options['validators']))
            {
                $options['validators'] = array();
            }

            $options['validators'] []= new StringValidator(array('required' => true));
        }
    }
    
    public function getOptions($field = null, $default = null)
    {
        if ($field === null)
        {
            return $this->options;
        }
        else
        {
            return isset($this->options[$field]) ? $this->options[$field] : $default;
        }
    }
    
    /**
     * Bind a request or an array.
     * 1. Transform input data
     * 2. Validate normed data
     * 
     * @return boolean
     */
    public function bind($value)
    {
        $this->errors = array();

        $this->setData($value);
        
        return $this->isValid();
    }
    
    /**
     * Return the validation state.
     * 
     * @return boolean
     */
     public function isValid()
    {
        return count($this->errors) == 0;
    }
    
    /**
     * Return the input data.
     * 
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }    
    
    /**
     * Set input data.
     * 
     * @param array $value
     */
    public function setData($value)
    {
        if ($this->preTransforms !== null)
        {
            foreach($this->preTransforms as $transformMethod)
            {
                $value = call_user_func($transformMethod, $value);
            }
        }
        
        $this->data = $value;
        
        $this->transform();
    }
    
    /**
     * Return the normed data.
     * 
     * @return array
     */
    public function getDataNormed()
    {
        return $this->dataNormed;
    }
    
    /**
     * Set normed data.
     * 
     * @param array $value
     */
    public function setDataNormed($value)
    {
        $this->dataNormed = $value;
        
        $this->reverseTransform();
    }
        
    /**
     * Return the validation errors.
     * 
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
    
    /**
     * Return a validation error by name.
     * 
     * @param string $key
     * 
     * @return string
     */
    public function getError($key)
    {
        if (is_numeric($key))
        {
            $buffer = array_values($this->errors);
            
            return $buffer[$key];
        }
        else
        {
            return isset($this->errors[$key]) ? $this->errors[$key] : null;
        }
    }
    
    /**
     * Validate data.
     * 
     * @param array $validators
     * 
     * @return boolean
     */
    public function validate($validators)
    {
        if (!is_array($validators))
        {
            $res = $validators($this->getData());

            if ($res !== true)
            {
                $this->errors[] = $res;

                return false;
            }
        }
	
        foreach($validators as $validator)
        {
            $res = $validator($this->getData());

            if ($res !== true)
            {
                $this->errors[] = $res;

                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Transform input data to norm data.
     * - Must validate the input data and add errors if any.
     */
    abstract protected function transform();
    
   /**
     * Transform norm data to input data.
    * - Must validate normed data and throw exception if any.
     */
    abstract protected function reverseTransform();
}