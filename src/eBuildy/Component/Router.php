<?php

namespace eBuildy\Component;

use eBuildy\Component\ApplicationAware;
use eBuildy\Exception\SecurityException;
use eBuildy\Exception\NotFoundException;

/**
 * @Service("router", "router")
 */
class Router
{
    use ApplicationAware;
    
    public $routes;
    public $securityServiceName;
    
    private $request;
    private $routesNamed;        
    
    public function initialize($configuration)
    {
        $this->routes = $configuration['routes'];
        
        $this->routesNamed = array();
        
        foreach($this->routes as $route)
        {
            if ($route['name'] !== null)
            {
                $this->routesNamed[$route['name']] = $route;
            }
        }
    }
    
    public function matchRequest($request)
    {
        $uri = $request->getPathInfo();
        $method = $request->getMethod();
        
        $this->request = $request;
        
        foreach($this->routes as $route)
        {
            if ($this->matchRoute($route, $method, $uri) && $this->secureRoute($route))
            {                
                return $this->prepare($route);
            }
        }
        
        throw new NotFoundException('route', array('method' => $method, 'uri' =>$uri));
    }
    
    /**
     * @Expose("getUrl")
     */
    public function generate($name, $parameters = array())
    {
        return $this->bindRoute($this->routesNamed[$name], $parameters);
    }
    
    public function get($name)
    {
        return $this->routesNamed[$name];
    }
    
    protected function secureRoute($route)
    {
        if ($route['security'] !== null && isset($route['security']['role']))
        {
            if ($this->get($this->securityServiceName)->checkRole($route['security']['role']) === false)
            {            
                throw new SecurityException();
            }
        }
        
        return true;
    }
    
    protected function matchRoute(&$route, $method, $uri)
    {
        $routeMethod = $route['method'];
       
        if ($routeMethod === '' || $routeMethod === $method)
        {        
            if (isset($route['path']))
            {
                return $uri === $route['path'];
            }
            else
            {
                $res = preg_match_all('/^'.$route['pattern'].'$/i', $uri, $matches);

                if ($res !== false && $res != 0)
                {//ob_clean();var_dump($matches);die();
                
                    foreach($matches as $key => $value)
                    {                    
                        $this->request->routeData->set($key, $value[0]);
                    }
                    
                    return true;
                }
            }
        }
    }
    
    protected function prepare($route)
    {
        $controller = $route['controller'];
        $buffer = explode('\\', $controller);

        $module = '';

           foreach($buffer as $part)
           {
               if ($part !== 'Controller')
               {
                   $module .= $part.DIRECTORY_SEPARATOR;
               }
               else
               {
                   break;
               }
           }

       $route['module'] = $module;
       
       return $route;
    }
    
    protected function bindRoute($route, $parameters = null)
    {
        if (isset($route['path']))
        {
            if ($parameters !== null && count($parameters) > 0)
            {
                return trim($route['path'].'?'.http_build_query($parameters), '?');
            }
            else
            {
                return $route['path'];
            }
        }
        else
        {
            $pattern = $route['pattern_original'];

            foreach($parameters as $key => $value)
            {
                $pattern = str_replace('{'.$key.'}', $value, $pattern);
            }
            
            return $pattern;
        }
    }
}