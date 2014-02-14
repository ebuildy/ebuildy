<?php

class Twig_Node_Expression_GetAttr extends Twig_Node_Expression
{
     public function __construct(Twig_Node_Expression $node, Twig_Node_Expression $attribute, Twig_Node_Expression_Array $arguments, $type, $lineno)
    {
        parent::__construct(array('node' => $node, 'attribute' => $attribute, 'arguments' => $arguments), array('type' => $type, 'is_defined_test' => false, 'ignore_strict_check' => false, 'disable_c_ext' => false), $lineno);
    }
    
    public function compile(Twig_Compiler $compiler)
    {
		$this->getNode('node')->setAttribute('ignore_strict_check', true);
		$this->getNode('node')->setAttribute('is_defined_test', false);
				
		if ($this->getAttribute('is_defined_test'))
		{
			$compiler->raw('isset(')->subcompile($this->getNode('node'))->raw('[')->subcompile($this->getNode('attribute'))->raw('])');
		}
		else
		{
			$compiler->subcompile($this->getNode('node'))->raw('[')->subcompile($this->getNode('attribute'))->raw(']');
		}
    }
}