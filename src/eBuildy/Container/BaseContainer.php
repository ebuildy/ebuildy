<?php

namespace eBuildy\Container;

class BaseContainer 
{    
   public function get($service) 
   {
	$method = \eBuildy\Helper\ResolverHelper::resolveServiceMethodName($service);
  
	return $this->$method(); 
    }
    
    /**
     * Instance $class and resolve dependencies-injection dynamically.
     *
     * @param string $class
     * @return mixed
     */
    public function newInstance($class)
    {
        $reflectedClass = new \ReflectionClass($class);

        $properties = $reflectedClass->getProperties(\ReflectionMethod::IS_PUBLIC);

        $instance = new $class();

        foreach ($properties as $property)
        {
            $currentProperty = $property->getName();

            foreach ($this->parseAnnotations($property->getDocComment()) as $annotation)
            {
                $annotationMethod = substr($annotation, 0, strpos($annotation, '('));

                if ($annotationMethod === 'Inject')
                {
                    $service = substr($annotation, 2 + strpos($annotation, '("'), strpos($annotation, '")') - strpos($annotation, '("') - 2);

                    $instance->$currentProperty = $this->get($service);
                }
            }
        }

        return $instance;
    }

    protected function parseAnnotations($annotations)
    {
        $buffer = array();

        preg_match_all('#@(.*?)\n#s', $annotations, $buffer);

        return $buffer[1];
    }
}
