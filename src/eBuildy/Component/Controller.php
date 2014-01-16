<?php

namespace eBuildy\Component;

use eBuildy\Container\ContainerAware;

class Controller extends ContainerAware
{
    protected $request;
    protected $response;
                
    public function execute($request, $response)
    {        
        $this->request = $request;
        $this->response = $response;               
        
        $route = $request->route;
        
        $this->preExecute($request, $response);
        
        if (isset($route['function']))
        {
            $method = $route['function'];
            
            $res = $this->$method($request, $response);
            
            if ($res !== null)
            {
                if (gettype($res) === 'string')
                {
                    $response->setContent($res);
                }
            }
        }
        else
        {
            $res = null;
        }
        
        $this->postExecute($request, $response);
        
        return $res;
    }
    
    protected function preExecute($request, $response)
    {
        
    }
    
    protected function postExecute($request, $response)
    {
        
    }
    
    
    protected function getInput($value, $label = '', $transformers = null, $validators = null)
    {
        $value = $this->request->get($value);
        
        if ($transformers !== null && $validators !== null)
        {
            $value = \eBuildy\DataBinder\DataBinderHelper::get($value, $label, $transformers, $validators);
        }
        
        return $value;
    }
    
     /**
     * Generates a URL from the given parameters.
     *
     * @param string  $route      The name of the route
     * @param mixed   $parameters An array of parameters
	 * @param boolean $absolute 
     *
     * @return string The generated URL
     */
    public function generateUrl($route, $parameters = array(), $absolute = false)
    {
        return $this->get('router')->generate($route, $parameters, $absolute);
    }
    
    /**
     * Returns a RedirectResponse to the given URL.
     *
     * @param string  $url    The URL to redirect to
     * @param integer $status The status code to use for the Response
     *
     * @return RedirectResponse
     */
    public function redirectWithHtml($url)
    {        
        $this->response->setContent(
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
        
	$this->response->addHeader('Content-Type', 'text/html');
	
        return $this->response->render();
    }
        
   /**
     * Returns a RedirectResponse to the given URL.
     *
     * @param string  $url    The URL to redirect to
     * @param integer $status The status code to use for the Response
     *
     * @return RedirectResponse
     */
    public function redirect($url, $status = 302)
    {
        $this->redirectWithHtml($url);
        
        $this->response->addHeader('location', $url);
        
        return $this->response->render();
    }
    
    protected function renderJSON($data)
    {
        $buffer = json_encode($data);
        
        $this->response->setContent($buffer);
        
        $this->response->addHeader('Content-Length', mb_strlen($buffer));
        
        return $this->response;
    }

    protected function renderDecoratedTemplate($templates, $data = array())
    {
        foreach($templates as &$template)
        {
            $template = \eBuildy\Helper\ResolverHelper::resolveTemplatePath($template, $this->request->route['module'], $this->request->route['controller']);
        }
        
        $this->response->setContent($this->get('templating')->renderDecoratedTemplate($templates, $data));
        
        return $this->response;
    }
        
    protected function renderTemplate($template, $data = array())
    {
        return $this->renderDecoratedTemplate(array($template), $data);
    }
}