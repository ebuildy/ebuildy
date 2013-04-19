<?php

namespace eBuildy\Helper;

class ResolverHelper
{    
    static public function getModulePathFromView($file)
    {
        $module = '';
        
        foreach(explode('/', $file) as $part)
        {
            if ($part === 'view')
            {
                break;
            }
            
            $module .= '/' . $part;
        }
        
        return $module;
    }
    
    static public function resolveServiceMethodName($service)
    {
        if (strpos($service, '.') === false)
        {
            return 'get' . ucfirst($service).'Service';
        }
        else
        {
            $name = '';
            $buffer = explode('.', $service);
            
            foreach($buffer as $item)
            {
                $name .= ucfirst($item);
            }
                        
            return 'get' . $name.'Service';
        }
    }
    
    static public function resolveTemplatePath($templateName, $module = null, $controller = null)
    {
        if ($templateName === null)
        {
            return null;
        }
        elseif (strpos($templateName, '::') !== false)
        {
            $buffer = explode('::', $templateName);
            
            $applicationName = $buffer[0];
            $templateName = $buffer[1];
            
            if (strlen($applicationName) == 0)
            {
                $buffer = explode('\\', $controller);

                $applicationName = $buffer[0];
            }
            else
            {
                $applicationName = str_replace(':', '/', $applicationName);
            }
            
            return SOURCE_PATH . $applicationName.'/view/'.$templateName;
        }
        else
        {            
            return SOURCE_PATH . $module.'/view/'.$templateName;
        }
    }
    
    static public function getRelativeRootPath($path)
    {
        return str_replace(ROOT, '', $path);
    }
}