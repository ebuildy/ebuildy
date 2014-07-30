<?php

namespace eBuildy\Templating;

use eBuildy\Templating\Volt\VoltView;

class VoltCompiler extends Compiler
{
	protected $voltView;
	protected $voltCompiler;
	protected $voltEngine;

	public function __construct($container, $exposedMethods = array())
	{
		parent::__construct($container, $exposedMethods);
		
		if (!file_exists(TMP_PATH . '/volt/'))
		{
			mkdir(TMP_PATH . '/volt/', 0x755);
		}
		
		$this->voltView = new VoltView();
			
		$this->voltEngine = new \Phalcon\Mvc\View\Engine\Volt($this->voltView);
			
		$this->voltEngine->setOptions(array(
			'compileAlways' => true,
			'compiledPath' => function($templatePath) {
				$root = TMP_PATH . '/volt/';

				return $root . trim(str_replace("/", "_", str_replace(ROOT, '', $templatePath)), '_') . '.php';
			}
		));
		
		$this->voltCompiler = $this->voltEngine->getCompiler();
		
		foreach($this->exposedMethod as $methodName => $method)
		{
			if (isset($method['service']))
			{
				$this->voltCompiler->addFunction($methodName, '$container->' . \eBuildy\Helper\ResolverHelper::resolveServiceMethodName($method['service']) . '()->' . $method['method']);
			}
			else
			{
				//$env->addFunction($methodName, new \Twig_SimpleFunction($methodName, '$this->' . $methodName));
			}
		}
		
		$defaultFunctions = array('str_replace');
		
		foreach($defaultFunctions as $methodName)
		{
			$this->voltCompiler->addFunction($methodName, $methodName);
		}
	}
	
	protected function __render()
    {  
		$this->data['container'] = $this->container;
		
		ob_start();
		
        $this->voltEngine->render($this->templatePath, $this->data);
		
		$buffer = ob_get_clean();
		
		return $buffer;
    }
	
}
