<?php

namespace eBuildy\Exception;

class NotFoundException extends \Exception
{
    public $data;
    
    public function __construct($type, $data = null)
    {
        parent::__construct($type.' not found !', 404);
        
        $this->data = $data;
    }
}