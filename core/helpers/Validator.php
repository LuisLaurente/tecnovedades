<?php

namespace Core\Helpers;

class Validator
{
    public static function isEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function isRequired($value)
    {
        return trim($value) !== '';
    }

    public static function minLength($value, $min)
    {
        return mb_strlen(trim($value)) >= $min;
    }

    public static function isNumeric($value)
    {
        return is_numeric($value);
    }
}
