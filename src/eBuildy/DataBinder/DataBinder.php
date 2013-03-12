<?php

namespace eBuildy\DataBinder;

abstract class DataBinder
{
    public $name;
    
    protected $data; 
    protected $dataNormed;
    protected $errors = array();
    
    public function __construct($name = '')
    {
        $this->name = $name;
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
        return isset($this->errors[$key]) ? $this->errors[$key] : null;
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