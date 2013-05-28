<?php

namespace eBuildy\Helper;

class CryptageHelper
{
    static public function crypt($message, $key) 
    {
        $result = '';
        
        if (!is_string($message))
        {
            $message = json_encode($message);
        }

        for($i=1; $i<=strlen($message); $i++)
        {
            $char = substr($message, $i-1, 1);
            $keychar = substr($key, ($i % strlen($key))-1, 1);
            $char = chr(ord($char)+ord($keychar));
            $result.=$char;
        }
        
        return self::base64UrlEncode($result);
    }
    
    static public function decrypt($message, $key)
    {
        $result = '';
        
        $message = self::base64UrlDecode($message);
        
        for($i=1; $i<=strlen($message); $i++)
        {
            $char = substr($message, $i-1, 1);
            $keychar = substr($key, ($i % strlen($key))-1, 1);
            $char = chr(ord($char)-ord($keychar));
            $result.=$char;
        }
        
        return $result;
    }


    static public function base64UrlEncode($input) {
        return trim(strtr(base64_encode($input), '+/', '-_'), '=');
    }

    static public function base64UrlDecode($input) {
        return base64_decode(strtr($input, '-_', '+/').'==');
    }
}