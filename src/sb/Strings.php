<?php
/**
 * Used to parse strings
 * 
 * @author paul.visco@roswellpark.org
 * @package Strings
 */
namespace sb;

class Strings{
    
    /**
     * Strips punctuation from a string
     *
     * @author paul.visco@roswellpark.org
     * @param string $str  The string to strip punctuation from.  Allows space, underscore, numbers and letters both lowercase and capital
     * @return string
     * @version 2.1
     * 
     */
    public static function strip_punct($str)
    {
        return preg_replace("~[^ \w]~", "", $str);
    }
    
    
    /**
     * Converts underscore separated word strings into camel style e.g. to_camel becomes toCamel
     *
     * @author paul.visco@roswellpark.org
     * @param string $str
     * @return string
     */
    public static function to_camel($str)
    {
        return str_replace(' ', '', ucwords(preg_replace('/[^A-Z^a-z^0-9]+/', ' ', $str)));
    } 
    
    /**
     * Cleans up file names and removes extraneous spaces, symbols, etc
     *
     * @author paul.visco@roswellpark.org
     * @param string $str
     * @return string
     */
    public static function clean_file_name($str)
    {
        preg_match('~\.\w{1,4}$~', $str, $ext);
        
        $str = preg_replace('~\.\w{1,4}$~', '', $str);
        
        return str_replace(Array(' ', '.', '-'), "_", $str).$ext[0];
    }
    
    /**
     * Escapes html tags to make them safe to print to screen
     *
     * @author paul.visco@roswellpark.org
     * @param string $str
     * @return string
     */
    public static function html_escape_tags($str)
    {
         return preg_replace("/&amp;(#[0-9]+|[a-z]+);/i", "&$1;", htmlspecialchars($str));
    }
    
    /**
     * Removes microsoft characters and replace them with their ascii counterparts
     *
     * @author paul.visco@roswellpark.org
     * @param string $str
     * @return string
     */
    public static function stripMicrosoftChars($str)
    {
        
        $chars=array(
        
            chr(133) => '...', //dot dot dot elipsis
            chr(145) => "'", //curly left single quotes
            chr(146) => "'", //curly right single quotes
            chr(147) => '"', //curly left double quotes
            chr(148) => '"', //curly right double quotes
            chr(149) => '*', //bullet char
            chr(150) => '-', //en dash
            chr(151) => '-', //em dash,
            
            //mac word roman charset
            chr(165) => '*', //bullet char
            chr(201) => '...', //elipsis
            chr(208) => '-', //en-dash
            chr(209) => '-', //em-dash
            chr(210) => '"', //curly left double quotes
            chr(211) => '"', //curly right double quotes
            chr(212) => '\'', //curly left single quote
            chr(213) => '\'', //curly right single quotes
            
        );
        
        return strtr($str, $chars);
    }
    
    /**
     * replaces unicode characters with ascii in unicode encoded urls e.g.from ajax calls
     *
     * @author paul.visco@roswellpark.org
     * @param string $str
     * @return string
     */
    public static function unicodeUrldecode($str)
    {
       preg_match_all('/%u([[:alnum:]]{4})/', $str, $matches);
     
       foreach ($matches[1] as $uniord){
           $str = str_replace('%u'.$uniord, '&#x' . $uniord . ';', $str);
       }
     
       return urldecode($str);
    }
    
    /**
     * Add an s to a work if there is only one of them
     *
     * @author paul.visco@roswellpark.org
     * @param integer $quanitity
     * @param string $noun
     * @return string
     */
    public static function pluralize($quanitity, $noun)
    {
        $quanitity = intval($quanitity);
        return ($quanitity ===1) ? $noun : $noun.'s';
    }
    
    /**
     * Truncates a string to a certain length and right padswith ...
     *
     * @param string $str
     * @param integer $maxlength The maximum length, keep in mind that the ... counts as three chars
     * @return string
     */
    public static function truncate($str, $maxlength=20)
    {
        
        if(strlen($str) > $maxlength){
            $str = substr($str, 0, $maxlength-3).'...';
        }
        
        return $str;
    }
    
    /**
     * Creates a string that is safe to display as HTML by stripping all tags, using html entities and removing slashes
     *
     * @param string $str
     * @return string
     */
    public static function safe($str)
    {
        return htmlentities(strip_tags(stripslashes(($str))));
    }
}
