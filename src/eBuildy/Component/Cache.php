<?php

namespace eBuildy\Component;

/**
 * @Service('cache')
 */
class Cache
{
    static public function needFresh($source, $target)
    {
        if (file_exists(TMP_PATH . $target) === false)
        {
            return true;
        }
        
        $sourceMTime = filemtime($source);
        $destMTime = filemtime(TMP_PATH . $target);
        
        if ($destMTime === false)
        {
            return true;
        }
        else
        {
            return $destMTime < $sourceMTime;
        }
    }
    
    static public function set($target, $content)
    {
        $buffer = '<?php '.PHP_EOL.'/* Auto generated at '.date(DATE_RSS).' */'.PHP_EOL.PHP_EOL;
        
        $buffer .= '$cache = ' . var_export($content, true). ';';
        
        return self::writeTempFile($target, $buffer);
    }
    
    static public function get($target)
    {         
        include(TMP_PATH . $target);
        
        return $cache;
    }
    
    static public function writeTempFile($target, $content)
    {
        $folder = dirname($target);
        
        if (!is_dir(TMP_PATH))
        {
            mkdir(TMP_PATH, 0777, true);
        }
        
        if (!is_dir(TMP_PATH . $folder))
        {
            if (!is_writable(TMP_PATH))
            {
                throw new \Exception(TMP_PATH . ' is not writable !');
            }
            else
            {            
                mkdir(TMP_PATH . $folder, 0777, true);
            
                chmod(TMP_PATH . $folder, 0777);
            }
        }
        
        if (is_writable(TMP_PATH . $folder) === false)
        {
            throw new \Exception(TMP_PATH . $folder.' is not writable ;-(');
        }
        else
        {
            file_put_contents(TMP_PATH . $target, $content);
            
            chmod(TMP_PATH . $target, 0644);
        }
    }
}