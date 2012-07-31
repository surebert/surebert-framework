<?php 
/**
 * Used to escape and unescape HTML for sanitation purposes
 * @author paul.visco@roswellpark.org 
 */
namespace sb;

class HTML{
    
    /**
     * Recursively htmlspecialchars string properties of objects and arrays 
     * @param mixed $mixed The object or array to convert
     * @param int $quote_style ent quote style from htmlspecialchars
     * @param string $charset The charset from  htmlspecialchars
     * @return type 
     */
    public static function escape($mixed, $quote_style = ENT_QUOTES, $charset = 'UTF-8') 
    {
        
        if (is_string($mixed)) {
            $mixed = htmlspecialchars($mixed, $quote_style, $charset);
        } elseif (is_object($mixed) || is_array($mixed)) {
            foreach ($mixed as $k => &$v) {
                if ($v) {
                    if (is_object($mixed)) {
                        $mixed->$k = self::escape($v, $quote_style, $charset);
                    } else {
                        $mixed[$k] = self::escape($v, $quote_style, $charset);
                    }
                }
            }
        }

        return $mixed;
    }

    /**
     * Recursively unhtmlspecialchars string properties of objects and arrays 
     * @param mixed $mixed The object or array to convert
     * @param int $quote_style ent quote style from htmlspecialchars
     * @param string $charset The charset from  htmlspecialchars
     * @return type 
     */
    public static function unescape($mixed, $quote_style = ENT_QUOTES, $charset = 'UTF-8') 
    {
        
        if (is_string($mixed)) {
            $mixed = str_replace('&amp;', '&', $mixed);
            $mixed = str_replace('&#039;', '\'', $mixed);
            $mixed = str_replace('&quot;', '"', $mixed);
            $mixed = str_replace('&lt;', '<', $mixed);
            $mixed = str_replace('&gt;', '>', $mixed);
        } elseif (is_object($mixed) || is_array($mixed)) {
            foreach ($mixed as $k => &$v) {
                if ($v) {
                    if (is_object($mixed)) {
                        $mixed->$k = self::unescape($v);
                    } else {
                        $mixed[$k] = self::unescape($v);
                    }
                }
            }
        }

        return $mixed;
    }
}
