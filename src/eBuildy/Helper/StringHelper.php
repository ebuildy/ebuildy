<?php

namespace eBuildy\Helper;

class StringHelper
{
    public static function generateRandomString($length = 10) 
    {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    }
}