<?php

namespace eBuildy\Component;

class Controller
{
	/**
	 * @var Request
	 */
    protected $request;
	
	/**
	 * @var Response
	 */
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
    
    protected function getRequestData($name)
    {
	return new \eBuildy\DataBinder\DataBinderWorkflow($name, $this->request->get($name));
    }
    
    
    protected function getInput($value, $label = '', $transformers = null, $validators = null)
    {
        $value = $this->request->get($value);

        if (!empty($transformers) && !empty($validators))
        {
            $value = \eBuildy\DataBinder\DataBinderHelper::get($value, $label, $transformers, $validators);
        }
        
        return $value;
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
		
		if ($buffer === false)
		{
			$errors = array(
				JSON_ERROR_NONE =>  'Aucune erreur',
				JSON_ERROR_DEPTH => 'Profondeur maximale atteinte',
				JSON_ERROR_STATE_MISMATCH => 'Inadéquation des modes ou underflow',
				JSON_ERROR_CTRL_CHAR => 'Erreur lors du contrôle des caractères',
				JSON_ERROR_SYNTAX => 'Erreur de syntaxe ; JSON malformé',
				JSON_ERROR_UTF8 => 'Caractères UTF-8 malformés, probablement une erreur d\'encodage'
			);
			
			$errorMessage = isset($errors[json_last_error()]) ? $errors[json_last_error()] : 'Erreur inconnue';
				
			throw new \Exception("Cannot JSON encode data: " . $errorMessage, 500);
		}
		
        $this->response->setContent($buffer);
        
        $this->response->addHeader('Content-Length', mb_strlen($buffer));
        
        return $this->response;
    }
}
