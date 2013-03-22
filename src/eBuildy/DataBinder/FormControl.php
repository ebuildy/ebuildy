<?php

namespace eBuildy\DataBinder;

use eBuildy\DataBinder\Validator\StringValidator;

class FormControl extends DataBinder
{
    protected $label;
    protected $template;
    protected $options;
    
    public function __construct($name, $label, $options = array())
    {
        parent::__construct($name);
        
        if (isset($options['template']))
        {
            $this->template = $options['template'];
        }
                
        if (isset($options['required']) && $options['required'] === true)
        {
            if (!isset($options['validators']))
            {
                $options['validators'] = array();
            }

            $options['validators'] []= new StringValidator(array('required' => true));
        }
        
        $this->label = $label;
        $this->options = $options;
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
    
    public function getRowTemplate()
    {
        return '__row.phtml';
    }
    
    public function getTemplate()
    {
        return $this->template;
    }
    
    public function getLabel()
    {
        return $this->label;
    }

    protected function reverseTransform()
    {
        $this->data = $this->dataNormed;
    }

    protected function transform()
    {
        if (isset($this->options['validators']))
        {
            $validators = $this->options['validators'];

            foreach($validators as $validator)
            {
                $res = $validator->validate($this->getData());
                
                if ($res !== true)
                {
                    $this->errors[] = $res;
                    
                    break;
                }
            }
        }
        
        if ($this->isValid())
        {
            $this->dataNormed = $this->data;
        }
        else
        {
            $this->dataNormed = null;
        }
    }
}