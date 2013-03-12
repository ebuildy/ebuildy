<?php

namespace eBuildy\Configuration;

class AnnotationLoader
{
    private $sourceDir;
    private $routes = array();
    private $services = array();
    private $eventListeners = array();
    private $commands = array();
    private $exposes = array();
    
    private $currentModule;
    private $currentClass;
    private $currentMethod;
    private $currentService;
    private $currentProperty;
    private $currentSecurity;
    private $currentPrefix;
    
    public function load($path)
    {
        $this->sourceDir  = $path;
        $this->targetPath = TMP_PATH.'annotations_'.md5($path).'.php';
        
        $start = microtime(true);
        
        $iterator = new \DirectoryIterator($this->sourceDir);

        foreach ($iterator as $module)
        {
            if ($module->getFilename() !== '.' && $module->getFilename() !== '..')
            {
                $this->extractModule($module);
            }
        }
        
        return array('parameters' => array('router' => array('routes' => $this->routes), 'templating' => array('exposes' => $this->exposes)), 'services' => $this->services, 'commands' => $this->commands, 'event_listeners' => $this->eventListeners);
    }

    protected function extractModule($module)
    {
        $this->currentModule = $module->getFilename();

        $Iterator  = new \RecursiveIteratorIterator($iterator = new \RecursiveDirectoryIterator($module->getPathname()));
        $Regex     = new \RegexIterator($Iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);

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
            catch(\ReflectionException $e)
            {
                continue ;
            }

            foreach ($this->parseAnnotations($r->getDocComment()) as $annotation)
            {
                $method = substr($annotation, 0, strpos($annotation, '('));

                if (method_exists($this, $method))
                {
                    eval('$this->' . $annotation . ';');
                }
            }

            $methods = $r->getMethods(\ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $method)
            {                
                if ($this->currentClass === $method->getDeclaringClass()->getName())
                {                
                    $this->currentMethod = $method->getName();

                    foreach ($this->parseAnnotations($method->getDocComment()) as $annotation)
                    {
                        $annotationMethod = substr($annotation, 0, strpos($annotation, '('));

                        if (method_exists($this, $annotationMethod))
                        {
                            eval('$this->' . $annotation . ';');
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
        $buffer = array();
        
        preg_match_all('#@(.*?)\n#s', $annotations, $buffer);
        
        return $buffer[1];
    }
    
    protected function resolveClassName($path)
    {
        $pathResolved = trim(str_replace(array($this->sourceDir, '.php'), '', $path), '/\\');
        
        return str_replace('/', '\\', $pathResolved);
    }

    protected function Route($pattern, $name = null, $method = "")
    {
        if ($this->currentMethod === null)
        {
            $this->currentPrefix = $pattern;
            return ;
        }
        
        if ($name === 'null')
        {
            $name = str_replace(array('/', '\\'), '_', $pattern);
        }
       // echo $this->currentClass. ' : ' .$this->currentMethod . PHP_EOL;
        //echo $name .' : '.$this->currentPrefix . ' - ' . $pattern . PHP_EOL;
        
        $pattern = $this->currentPrefix . $pattern;
        
        $route = array( "method"     => $method, 'controller' => $this->currentClass, 'function'   => $this->currentMethod);
        
        if (strpos($pattern, '(') !== false || strpos($pattern, '[') !== false || strpos($pattern, '{') !== false)
        {
            $route['pattern_original'] = $pattern;
            
            $route['pattern'] =  preg_replace_callback('/\{([a-zA-Z]*)\}/', function($matches)
            {
                return '(?<'.$matches[1].'>([0-9a-zA-Z-]*))';
            }, str_replace('/', '\/', $pattern));
        }
        else
        {
            $route['path'] = $pattern;
        }
        
        $route['security'] = $this->currentSecurity === null ? '' : $this->currentSecurity;
                
        $this->routes [$name] = $route;
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
        
        $this->services[$name] = array('class' => $this->currentClass, 'configurationNode' => $configurationNode, 'dependencies' => array());
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
        $this->exposes[$name]  = array( 'service' => $this->currentService, 'method' => $this->currentMethod);
    }
    
    protected function Event($event, $priority = 0)
    {
        if ($this->currentService !== null)
        {
            if (!isset($this->eventListeners[$event]))
            {
                $this->eventListeners[$event] = array();
            }

            $this->eventListeners[$event] []= array('priority' => $priority, 'service' => $this->currentService, 'method' => $this->currentMethod);
        }
    }
}