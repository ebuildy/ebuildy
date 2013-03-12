<?php

namespace eBuildy\Component;

use eBuildy\Component\Response;

class Controller
{
    use ApplicationAware;
    
    protected $request;
    
    /**
     * The main container
     * 
     * @var \Container
     */
    protected $container;
    
    public function __construct()
    {
        $this->container = $this->getApplication()->container;
    }
    
    public function execute($request)
    {
        $this->request  = $request;
        
        $route = $request->route;
        
        if (isset($route['function']))
        {
            $method = $route['function'];
            
            $res = $this->$method($request);

            if (gettype($res) === 'object')
            {
                return $res;
            }
            else
            {
                return new Response($res);
            }
        }
    }
    
     /**
     * Generates a URL from the given parameters.
     *
     * @param string  $route      The name of the route
     * @param mixed   $parameters An array of parameters
     *
     * @return string The generated URL
     */
    public function generateUrl($route, $parameters = array())
    {
        return $this->get('router')->generate($route, $parameters);
    }
    
    /**
     * Returns a RedirectResponse to the given URL.
     *
     * @param string  $url    The URL to redirect to
     * @param integer $status The status code to use for the Response
     *
     * @return RedirectResponse
     */
    public function redirectWithHtml($url, $response = null)
    {
        if ($response === null)
        {
            $response = new Response();
        }
        
        $response->setContent(
            sprintf('<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta http-equiv="refresh" content="1;url=%1$s" />

        <title>Redirecting to %1$s</title>
    </head>
    <body>
        Redirecting to <a href="%1$s">%1$s</a>.
    </body>
</html>', htmlspecialchars($url, ENT_QUOTES, 'UTF-8')));
        
        return $response;
    }
        
   /**
     * Returns a RedirectResponse to the given URL.
     *
     * @param string  $url    The URL to redirect to
     * @param integer $status The status code to use for the Response
     *
     * @return RedirectResponse
     */
    public function redirect($url, $status = 302, $response = null)
    {
        if ($response === null)
        {
            $response = new Response();
        }
        
        $response = $this->redirectWithHtml($url, $response);
        
        $response->addHeader('location', $url);
        
        return $response;
    }
    
    protected function  renderJSON($data, $response = null)
    {
        if ($response === null)
        {
            $response = new Response();
        }
        
        $response->setContent(json_encode($data));
        
        return $response;
    }


    protected function renderDecoratedTemplate($templates, $data = array(), $response = null)
    {
        if ($response === null)
        {
            $response = new Response();
        }
        
        foreach($templates as &$template)
        {
            $template = \eBuildy\Helper\ResolverHelper::resolveTemplatePath($template, $this->request->route['module'], $this->request->route['controller']);
        }
        
        $response->setContent($this->get('templating')->renderDecoratedTemplate($templates, $data));
                
        return $response;
    }
        
    
}