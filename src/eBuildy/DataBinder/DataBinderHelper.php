<?php

namespace eBuildy\DataBinder;

use eBuildy\Exception\ValidationException;

class DataBinderHelper 
{
    static public function get($value, $label = '', $preTransforms = array(), $validators = array())
    {
        $control = new FormControl($label, $label, array('pre_transforms' => $preTransforms, 'validators' => $validators));

        if ($control->bind($value))
        {
            return $control->getDataNormed();
        }
        else
        {
            foreach($control->getErrors() as $error)
            {
                throw new ValidationException($error, $label, $control->getData());
            }
        }
    }
    
    static public function my_htmlentities($value)
    {
        return htmlentities($value, ENT_QUOTES);
    }
}