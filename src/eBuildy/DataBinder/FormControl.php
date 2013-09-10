<?php

namespace eBuildy\DataBinder;

class FormControl extends DataBinder
{
    protected $label;
    protected $template;

    public function __construct($name, $label, $options = array())
    {
        parent::__construct($name, $options);
        
        if (isset($options['template']))
        {
            $this->template = $options['template'];
        }
                 
        $this->label = $label;
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
            $this->validate($this->options['validators']);
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