<?php

namespace eBuildy\Component;

/**
 * @Service("translator", "translator")
 */
class TranslatorService
{
    protected $catalogues = array();
    
    protected $cataloguePathMask;
    
    public function initialize($configuration)
    {
        $this->cataloguePathMask = $configuration['catalogue'];
    }
    
    public function get($key, $data = array())
    {
        $a = strpos($key, '.');
        
        $type = substr($key, 0, $a);
        $name = substr($key, $a + 1);
        
        if (!isset($this->catalogues[$type]))
        {
            $cataloguePath = ROOT . str_replace(array('{lang}', '{catalogue}'), array('fr', $type), $this->cataloguePathMask);

            if (!file_exists($cataloguePath))
            {
               // return "<!> Translations missing for $key <!>";
                return $key;
            }
            
            include($cataloguePath);
            
            $this->catalogues[$type] = $languages;
        }
        
        $r = $this->catalogues[$type][$name];
        
        if ($r === null)
        {
            return $type . '.' . $name;
        }
        else
        {
            $content = $r;//['fr'];

            if (count($data) > 0)
            {
                foreach($data as $k => $v)
                {
                    $content = str_replace('%'.$k.'%', $v, $content);
                }
            }

            return $content;
        }
    }
}