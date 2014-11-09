<?php

namespace eBuildy\DataBinder;

use eBuildy\Exception\ValidationException;

class DataBinderWorkflow
{
	public $label;
	public $initialValue;
	
	public $value;
	
	public function __construct($label = '', $value = null)
	{
		$this->label = $label;
		$this->initialValue = $value;
		$this->value = $value;
	}
	
	public function setValue($value)
	{
		$this->value = $value;
		
		return $this;
	}
	
	public function getValue()
	{
		return $this->value;
	}
	
	public function transform($transformer)
	{
		$this->setValue(call_user_func($transformer, $this->value));
		
		return $this;
	}
	
	public function validate($validator)
	{
		$successOrError = call_user_func($validator, $this->value);
		
		if ($successOrError !== true)
		{
			throw new ValidationException($successOrError, $this->label, $this->value);
		}
		
		return $this;
	}
}
