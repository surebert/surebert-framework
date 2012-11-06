<?php
/**
 * Validates numbers
 * @author paul.visco@roswellpark.org
 * @package Validate
 */
namespace sb\Validate;

class Numbers{

    /**
     * Checks to see if str, float, or int type and represents whole number
     * @param mixed $int
     * @return boolean
     */
    public static function is_int($int)
    {
        return (is_string($int) || is_int($int) || is_float($int)) &&
            ctype_digit((string)$int);
    }
}
