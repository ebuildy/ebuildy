<?php

namespace eBuildy\Configuration;

class AnnotationLoader
{
    private $sourceDir;
    private $controllers = [];
    private $services = [];
    private $hooks = [];
    private $commands = [];
    private $exposes = [];

    private $currentClass;
    private $currentMethod;
    private $currentService;
    private $currentProperty;
    private $currentSecurity;
    private $currentController;
    private $currentPrefix;

    public function load($path, $contextAutoLoad = null)
    {
        $this->sourceDir = $contextAutoLoad === null ? $path : $contextAutoLoad;
        $this->targetPath = TMP_PATH . 'annotations_' . md5($path) . '.php';

        $this->extractModule($path);

        return [
            'parameters' => [
                'router'     => [
                    'controllers' => $this->controllers
                ],
                'templating' => ['exposes' => $this->exposes],
                'command'    => ['commands' => $this->commands],
                'hook'       => ['hooks' => $this->hooks]
            ],
            'services'   => $this->services
        ];
    }

    protected function extractModule($module)
    {
        $Iterator = new \RecursiveIteratorIterator($iterator = new \RecursiveDirectoryIterator($module));
        $Regex = new \RegexIterator($Iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);

        foreach ($Regex as $file => $vv)
        {
            $this->currentService = null;
            $this->currentSecurity = null;
            $this->currentMethod = null;
            $this->currentClass = $this->resolveClassName($file);
            $this->currentPrefix = '';

            try
            {
                $r = new \ReflectionClass($this->currentClass);
            }
            catch (\ReflectionException $e)
            {
                var_dump($e->getMessage());

                continue;
            }

            foreach ($this->parseAnnotations($r->getDocComment()) as $annotation)
            {
                $method = substr($annotation, 0, strpos($annotation, '('));

                if (method_exists($this, $method))
                {
                    eval('$this->' . $annotation . ';');
                }
            }

            if ($r->isSubclassOf('eBuildy\Container\ContainerAware'))
            {
                $this->services[$this->currentService]['containerAware'] = true;
            }

            $methods = $r->getMethods(\ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $method)
            {
                if ($this->currentClass === $method->getDeclaringClass()->getName())
                {
                    $this->currentMethod = $method->getName();

                    foreach ($this->parseAnnotations($method->getDocComment()) as $annotation)
                    {
                        if (strpos($annotation, '(') !== false)
                        {
                            $annotationMethod = substr($annotation, 0, strpos($annotation, '('));

                            if (strpos($annotationMethod, ' ') === false && method_exists($this, $annotationMethod))
                            {
                                eval('$this->' . $annotation . ';');
                            }
                        }
                    }
                }
            }

            $properties = $r->getProperties(\ReflectionMethod::IS_PUBLIC);

            foreach ($properties as $property)
            {
                $this->currentProperty = $property->getName();

                foreach ($this->parseAnnotations($property->getDocComment()) as $annotation)
                {
                    $annotationMethod = substr($annotation, 0, strpos($annotation, '('));

                    if (method_exists($this, $annotationMethod))
                    {
                        eval('$this->' . $annotation . ';');
                    }
                }
            }
        }
    }

    protected function parseAnnotations($annotations)
    {
        $buffer = [];

        preg_match_all('#@(.*?)\n#s', $annotations, $buffer);

        return $buffer[1];
    }

    protected function resolveClassName($path)
    {
        $pathResolved = trim(str_replace([$this->sourceDir, '.php'], '', $path), '/\\');

        return str_replace('/', '\\', $pathResolved);
    }

    protected function Controller($name, $pattern = '')
    {
        $this->currentController = $name;

        $this->controllers[$name] = [
            'name'   => $name,
            'routes' => [],
            'prefix' => $pattern
        ];

        $controllerService = 'controller.' . $name;

        $this->currentService = $controllerService;

        $this->services[$controllerService] = [
            'class'             => $this->currentClass,
            'configurationNode' => null,
            'dependencies'      => []
        ];
    }

    protected function Route($pattern, $name = null, $method = "")
    {
        if ($name === 'null' || $name === null)
        {
            $name = \eBuildy\Helper\RandomHelper::generateRandomString(12);
        }
        // echo $this->currentClass. ' : ' .$this->currentMethod . PHP_EOL;
        //echo $name .' : '.$this->currentPrefix . ' - ' . $pattern . PHP_EOL;

        //$pattern = $this->currentPrefix . $pattern;

        $route = ['function' => $this->currentMethod];

        if (!empty($method) && $method !== '*')
        {
            $route['method'] = $method;
        }

        if (strpos($pattern, '(') !== false || strpos($pattern, '[') !== false || strpos($pattern, '{') !== false)
        {
            $route['pattern_original'] = $pattern;

            $route['pattern'] = preg_replace_callback('/\{([^\}]*)\}/', function ($matches)
            {
                $p = $matches[1];

                if (strpos($p, '|') === false)
                {
                    $regex = '([0-9a-zA-Z-]*)';
                }
                else
                {
                    $a = strpos($p, '|');

                    $regex = substr($p, $a + 1);
                    $p = substr($p, 0, $a);
                }

                return '(?<' . $p . '>' . $regex . ')';
            }, str_replace('/', '\/', $pattern));

            $route['pattern_original'] = preg_replace_callback('/\{([^\}]*)\}/', function ($matches)
            {
                $p = $matches[1];

                if (strpos($p, '|') !== false)
                {
                    $a = strpos($p, '|');

                    $regex = substr($p, $a + 1);
                    $p = substr($p, 0, $a);

                    $route['pattern_original'] = '{' . $p . '}';
                }

                return '{' . $p . '}';
            }, $pattern);
        }
        else
        {
            $route['path'] = $pattern;
        }
        //var_dump($route);

        if (isset($this->routes[$name]))
        {
            throw new \Exception("The route " . $name . " already exist: " . json_encode($this->routes[$name]));
        }

        $this->controllers[$this->currentController]['routes'][$name] = $route;
    }

    protected function Helper($name)
    {
        $this->helpers[$name] = $this->currentClass;
    }

    protected function Command($name)
    {
        $this->commands[$name] = $this->currentClass;
    }

    protected function Service($name, $configurationNode = null)
    {
        $this->currentService = $name;

        $this->services[$name] = [
            'class'             => $this->currentClass,
            'configurationNode' => $configurationNode,
            'dependencies'      => []
        ];
    }

    protected function Inject($serviceToInject)
    {
        if ($this->currentService !== null)
        {
            $service = &$this->services[$this->currentService];

            $service['dependencies'][$this->currentProperty] = $serviceToInject;
        }
    }

    protected function Expose($name)
    {
        $this->exposes[$name] = ['service' => $this->currentService, 'method' => $this->currentMethod];
    }

    protected function Hook($name, $priority = 0)
    {
        if ($this->currentService !== null)
        {
            if (!isset($this->hooks[$name]))
            {
                $this->hooks[$name] = [];
            }

            $this->hooks[$name] [] = [
                'priority' => $priority,
                'service'  => $this->currentService,
                'method'   => $this->currentMethod
            ];
        }
    }
}
