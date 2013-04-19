<?php

namespace eBuildy\DataBinder;

class Form extends DataBinder
{    
    protected $children = array();
    
    public function addChild(DataBinder $value)
    {
        $this->children[$value->name] = $value;
    }
    
    public function getChild($childName)
    {
        return $this->children[$childName];
    }
    
    public function getChildData($childName)
    {
        return $this->getChild($childName)->getData();
    }
    
    public function getChildDataNormed($childName)
    {
        return $this->getChild($childName)->getDataNormed();
    }
    
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * 1. Set data of children
     * 2. Merge children norm validation error
     * 3. Set form data
     * 
     * @param array $value
     */
    public function setData($value)
    {
        foreach($this->children as $child)
        {
            if (isset($value[$child->name]))
            {
                $child->setData($value[$child->name]);
            }
            else
            {
                $child->setData(null);
            }
        }
        
        foreach($this->children as $child)
        {
            if ($child->isValid() === false)
            {
                foreach($child->getErrors() as $errorName => $error)
                {
                    $this->errors[$child->name] = $error;
                }
            }
        }
        
        parent::setData($value);
    }
    
    public function setDataNormed($value)
    {
        foreach($this->children as $child)
        {
            if (isset($value[$child->name]))
            {
                $child->setDataNormed($value[$child->name]);
            }
        }
        
        parent::setDataNormed($value);
    }
                
    protected function reverseTransform()
    {

    }

    protected function transform()
    {
        $dataNormed = array();
        
        foreach($this->children as $child)
        {
            $dataNormed[$child->name] = $child->getDataNormed();
        }
        
        $this->dataNormed = $dataNormed;
    }
}