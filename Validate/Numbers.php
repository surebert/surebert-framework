<?php

class sb_Validate_Numbers{

    /**
     * Checks to see if str, float, or int type and represents whole number
     * @param mixed $int
     * @return boolean
     */
    public static function is_int($int){
        return (is_string($int) || is_int($int) || is_float($int)) &&
            ctype_digit((string)$int);
    }
}
?>