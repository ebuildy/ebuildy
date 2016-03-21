<?php

namespace eBuildy\Container;

use eBuildy\Component\Cache;
use eBuildy\Helper\ResolverHelper;
use Symfony\Component\Yaml\Yaml;

class ContainerBuilder
{
    private $configuration = array();

    public function addNode($name, $data)
    {
        if ($name === 'root')
        {
            $this->configuration = array_merge($data, $this->configuration);
        }
        else
        {
            if ($name === 'parameters')
            {
                if (!isset($this->configuration[$name]))
                {
                    $this->configuration[$name] = array();
                }

                foreach ($data as $k => $v)
                {
                    $this->__addNode($this->configuration[$name], $k, $v);
                }
            }
            else
            {
                $this->__addNode($this->configuration, $name, $data);
            }
        }
        
        return $this;
    }

    private function __addNode(&$node, $name, $data)
    {
        if (!isset($node[$name]))
        {
            $node[$name] = array();
        }

        if ($data !== null && is_array($data))
        {
            $node[$name] = array_merge_recursive($node[$name], $data);
        }
    }

    public function getAll()
    {
        return $this->configuration;
    }

    public function getNode($name, $default = null)
    {
        return isset($this->configuration[$name]) ? $this->configuration[$name] : $default;
    }

    public function get($name, $default = null)
    {
        return $this->getNode($name, $default);
    }

    public function loadAnnotations($path, $contextAutoload = null)
    {
        $loader = new AnnotationLoader();

        foreach ($loader->load($path, $contextAutoload) as $k => $v)
        {
            $this->addNode($k, $v);
        }
        
        return $this;
    }

    public function loadFile($source, $parameters = array())
    {
        if (!is_readable($source))
        {
            throw new \Exception('Configuration source "' . $source . '" is not readable!');
        }

        $cacheDest = 'config/' . md5($source) . '/' . md5(json_encode($parameters));

        if (Cache::needFresh($source, $cacheDest) || DEBUG)
        {
            $content = file_get_contents($source);

            foreach ($parameters as $k => $v)
            {
                $parameters['&' . $k] = $v;

                $content = str_replace('%' . $k . '%', '*' . $k, $content);

                unset($parameters[$k]);
            }

            $aliasYaml = Yaml::dump(array('__aliases' => $parameters), 3);

            foreach ($parameters as $k => $v)
            {
                $aliasYaml = str_replace('\'' . $k . '\':', '- ' . $k, $aliasYaml);
            }

            //die($aliasYaml.PHP_EOL.$content);

            $result = Yaml::parse($aliasYaml . PHP_EOL . $content);

            unset($result['__aliases']);

            Cache::set($cacheDest, $result);
        }
        else
        {
            $result = Cache::get($cacheDest);
        }

        foreach ($result as $k => $v)
        {
            $this->addNode($k, $v);
        }
        
        return $this;
    }

    public function build($dir, $name)
    {
        $phpContent = '<?php ' . PHP_EOL . PHP_EOL . 'class ' . $name . ' extends \eBuildy\Container\BaseContainer {' . PHP_EOL . PHP_EOL;
            
       // $frameworkDebug = $this->configuration['parameters']['ebuildy']['debug'];
        
        $services   = $this->get('services');

        foreach ($services as $serviceName => $service)
        {
            $phpContent .= 'protected $' . $this->resolveServicePropertyName($serviceName) . ' = null;' . PHP_EOL;
        }
        
        $phpContent .= PHP_EOL . PHP_EOL;

        foreach ($this->get('parameters') as $parameterName => $parameter)
        {
            $method = $this->resolveParameterMethod($parameterName);

            $phpContent .= 'public function ' . $method . '() {' . PHP_EOL;

            $phpContent .= "\t" . 'return ' . var_export($parameter, true) . ';' . PHP_EOL;

            $phpContent .= '}' . PHP_EOL . PHP_EOL;
        }

        foreach ($services as $serviceName => $service)
        {
            $method = ResolverHelper::resolveServiceMethodName($serviceName);
            
            if (!isset($service['class']))
            {
                var_dump($serviceName, $service);
                die('error configuration.php');
            }

            $args = '';

            if (isset($service['containerAware']))
            {
                $args .= '$this';
            }
            
            $phpContent .= '/**' . PHP_EOL . '* @public' . PHP_EOL . '* @return ' . $service['class'] . PHP_EOL . '*/' . PHP_EOL;

            $phpContent .= 'public function ' . $method . '() {' . PHP_EOL;

            $phpContent .= "\t" . 'if ($this->' . $this->resolveServicePropertyName($serviceName) . ' === null) {' . PHP_EOL;

            $phpContent .= "\t\t" . '$this->' . $this->resolveServicePropertyName($serviceName) . ' = new ' . $service['class'] . '(' . $args . ');' . PHP_EOL;

            if (isset($service['configurationNode']))
            {
                $phpContent .= "\t\t" . '$this->' . $this->resolveServicePropertyName($serviceName) . '->initialize($this->' . $this->resolveParameterMethod($service['configurationNode']) . '());' . PHP_EOL;
            }

            if (isset($service['dependencies']))
            {
                foreach ($service['dependencies'] as $property => $serviceToInject)
                {
                    $phpContent .= "\t\t" . '$this->' . $this->resolveServicePropertyName($serviceName) . '->' . $property . ' = $this->' . \eBuildy\Helper\ResolverHelper::resolveServiceMethodName($serviceToInject) . '();' . PHP_EOL;
                }
            }

            $phpContent .= "\t" . '}' . PHP_EOL;

            $phpContent .= "\t" . 'return $this->' . $this->resolveServicePropertyName($serviceName) . ';' . PHP_EOL;

            $phpContent .= '}' . PHP_EOL . PHP_EOL;
        }

        $phpContent.= '}';
        //         die($phpContent);
        file_put_contents($dir . DIRECTORY_SEPARATOR . $name . '.php', $phpContent);
        
        chmod($dir . DIRECTORY_SEPARATOR . $name . '.php', 0644);
        
        return $this;
    }

    protected function resolveServicePropertyName($service)
    {
        if (strpos($service, '.') === false)
        {
            return '__' . $service . 'Service';
        }
        else
        {
            $name   = '';
            $buffer = explode('.', $service);

            foreach ($buffer as $item)
            {
                $name .= ucfirst($item);
            }

            return '__' . lcfirst($name);
        }
    }

    protected function resolveParameterMethod($value)
    {
        if (strpos($value, '.') === false)
        {
            return 'get' . ucfirst($value) . 'Configuration';
        }
        else
        {
            $name   = '';
            $buffer = explode('.', $value);

            foreach ($buffer as $item)
            {
                $name .= ucfirst($item);
            }

            return 'get' . $name . 'Configuration';
        }
    }

}
