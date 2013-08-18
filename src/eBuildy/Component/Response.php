<?php

namespace eBuildy\Component;

class Response
{
    protected $rawContent;
    protected $headers = array();
    protected $cookies = array();
    
    public function __construct($content = '', $headers = array())
    {
        $this->rawContent = $content;
        $this->headers = $headers;
    }
      
    public function addHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }
    
    public function setCookie($name, $value, $expire = 0, $path = '/', $domain = null, $secure = false, $httponly = false)
    {
        if ($domain === null)
        {
            $domain = $_SERVER['HTTP_HOST'];
        }
        
        if (is_string($expire))
        {
            $expire = strtotime($expire);
        }
        
        $this->cookies[$name] = array(
            'name' => $name, 
            'value' => $value,
            'expire' => $expire,
            'path' => $path, 
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httponly
        );
    }
    
    public function removeCookie($name)
    {	
        $this->setCookie($name, null, '-= 1 week');
	//$this->setCookie($name, null, '-= 1 week', '/', '');
    }

    public function setContent($content)
    {
        $this->rawContent = $content;
    }

    public function render($display = true)
    {
        if ($display)
        {
            foreach($this->headers as $name => $value)
            {
                header($name.': '.$value);
            }

            foreach($this->cookies as $name => $cookie)
            {
                setcookie($name, $cookie['value'], $cookie['expire'], $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
            }
            
            die($this->rawContent);
        }
        else
        {
            return $this->rawContent;
        }
    }
}