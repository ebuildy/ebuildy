<?php

namespace eBuildy\Asset;

class AssetResolver
{
    static public function resolveSourcePath($source, $context)
    {
        $context = rtrim($context, '/');
        $path = null;
        
        if ($source[0] === '/')
        {
            $path = $source;
        }
        elseif ($source[0] === '@')
        {
            $path = SOURCE_PATH . substr($source, 1);
        }
        elseif ($source[0] === ':')
        {
            $path = VENDOR_PATH . substr($source, 1);
        }
        elseif (file_exists($context . '/' . $source))
        {
            $path = $context . '/' . $source;
        }
        else
        {
            $searchSources = array(SOURCE_PATH, VENDOR_PATH, ROOT);
        
            foreach($searchSources as $searchSource)
            {
                $filePath = str_replace('//', '/', $searchSource . $source);

                if (file_exists($filePath))
                {
                    $path = $filePath;
                    
                    break;
                }
            }
        }
        
        return $path === null ? null : realpath($path);
    }
    
    static public  function resolveRessourceName($source, $options = array())
    {
        if (is_string($options))
        {
            return $options;
        }
        elseif (isset($options['name']))
        {
            return $options['name'];
        }
        else
        {
            $context = \eBuildy\Helper\ResolverHelper::getRelativeRootPath(dirname($source));
              
            $buffer = explode('.', basename($source));
            $sourceName = $buffer[0];
            
            return strtolower(str_replace(array('src/', '/'), array('', '_'), $context) . '_' . $sourceName);
        }
    }
        
    static public  function resolveRouteData($source, $target)
    {  
         return base64_encode(json_encode(array($target, $sourceClear)));
    }
    
    static public  function resolveNameWithVersion($name, $extension, $version, $versionFormat)
    {
        return str_replace(array('{name}', '{extension}', '{version}'), array($name, $extension, $version), $versionFormat);
    }
    
    static public  function resolveNameForCompilation($name)
    {
        $a = strpos($name, '?');
        
        if ($a !== false)
        {
            return substr($name, 0, $a);
        }
        else
        {
            return $name;
        }
    }
}