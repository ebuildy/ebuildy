<?php

namespace eBuildy\Component;

use eBuildy\Container\ContainerAwareTrait;
use eBuildy\Exception\SecurityException;
use eBuildy\Exception\NotFoundException;

/**
 * @Service("router", "router")
 */
class RouterService
{
    use ContainerAwareTrait;

    /**
     * @var array
     */
    public $controllers;
    public $securityServiceName;
    
    private $request;
    private $baseUris;
    
    public function initialize($configuration)
    {
        $this->controllers = $configuration['controllers'];
        
        if (isset($configuration['base_uris']))
        {
            $this->baseUris = $configuration['base_uris'];
        }
    }


    /**
     * @param Request $request
     * @return mixed
     *
     * @throws \eBuildy\Exception\NotFoundException
     * @throws \eBuildy\Exception\SecurityException
     */
    public function matchRequest($request)
    {
        $uri    = $request->getPathInfo();
        $method = $request->getMethod();
        
        while (strlen($uri) > 1 && $uri[0] === '/' && $uri[1] === '/')
        {
            $uri = substr($uri, 1);
        }
        
        $this->request = $request;
        
        foreach($this->controllers as $controller)
        {
            $controllerPrefix = $controller['prefix'];

            if (empty($controllerPrefix) || strpos($uri, $controllerPrefix) === 0)
            {
                $prefixLength = strlen($controllerPrefix);

                if ($prefixLength > 1)
                {
                    $subURI = substr($uri, $prefixLength);
                }
                else
                {
                    $subURI = $uri;
                }

                foreach ($controller['routes'] as $route)
                {
                    if ($this->matchRoute($route, $method, $subURI) && $this->secureRoute($route))
                    {
                        return $this->prepare($controller, $route);
                    }
                }
            }
        }
        
        throw new NotFoundException('route', ['method' => $method, 'uri' =>$uri]);
    }
    
    /**
     * @Expose("getUrl")
     */
    public function generate($name, $parameters = [], $base = false)
    {
		if ($base === true)
		{
			$base = 'default';
		}
		
        foreach($this->controllers as $controllerName => $controller)
        {
            if (isset($controller['routes'][$name]))
            {
                return ($base !== false ? $this->baseUris[$base] : "") . $this->bindRoute($controller['routes'][$name], $parameters);
            }
        }

        throw new NotFoundException('Route ' . $name . ' not found!');
    }
    
    public function get($name)
    {
        return $this->controllers[$name];
    }
    
    protected function secureRoute($route)
    {
        if (isset($route['security']) && $route['security'] !== null && isset($route['security']['role']))
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
        $routeMethod = isset($route['method']) ? $route['method'] : null;

        if (empty($routeMethod) || $routeMethod === $method)
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

    protected function prepare($controller, $route)
    {
        $controllerInstance = $this->container->get('controller.' . $controller['name']);

        $route['controller'] = $controllerInstance;

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
