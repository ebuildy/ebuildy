<?php

namespace eBuildy\DataBinder\Validator;

class DateValidator extends StringValidator
{
    static public $ERROR_WRONG_SEPARATOR = 'validator.date.separator';
    static public $ERROR_NOT_VALID = 'validator.date.notvalide';

    public function __construct($required = false)
    {
        parent::__construct($required, 10, 10);
    }

    public function validate($input)
    {
        $pre = parent::validate($input);

        if ($pre !== true)
        {
            return $pre;
        }

        if ($input[2] !== '/' || $input[5] !== '/')
        {
            return self::$ERROR_WRONG_SEPARATOR;
        }

        list($day, $month, $year) = explode('/', $input);

        if ($day <= 0 || $day > 31 || $month <= 0 || $month > 12 || $year <= 0 || $year >= 3000)
        {
            return self::$ERROR_NOT_VALID;
        }

        return true;
    }
}
