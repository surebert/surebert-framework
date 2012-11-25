<?php
/**
 * Used to respond to javascript requests
 * @author paul.visco@roswellpark.org
 * @package JS
 */
namespace sb;

class JS{
    
    /**
     * The current on response headers
     * @var integer 
     */
    protected static $sb_on_response_headers = 0;
    
    /**
     * Uses jsexec_header to set the innerHTML of an element by id
     * @param integer $id the HTML element id
     * @param string $html The innerHTML to set
     * @return string
     */
    public static function setHTML($id, $html)
    {
        $js = '$("'.$id.'").html('.json_encode($html).');';
        return self::execHeader($js);
    }
    
    /**
     * Executes a script at a certain path
     * @param string $script_path full path
     */
    public static function execScript($script_path, $context=null)
    {
        header('Content-type: text/javascript');
        if(!is_null($context)){
            if(is_object($context)){
                $context = get_object_vars($context);
            }
            if(is_array($context)){
                extract($context);
            }
            
        }
        require($script_path);
    }
    
    /**
     *
     * @param string $message The message to notify
     * @param string $class The class to use for the notification 'error', 'success', etc
     * @return string
     */
    public static function notify($message, $class='success')
    {
        return self::execHeader("sb.notify(".json_encode($message).", ".json_encode($class).");");
    }
    
    /**
     * Executes the full response text, can be called more than once
     * @param type $js 
     */
    public static function exec_response($js)
    {
        header('Content-type: text/javascript');
        echo $js;
    }
    
    /**
     * Executes a response header
     * @param type $js 
     */
    public static function exec_header($js)
    {
        header("sb_on_response".(self::$sb_on_response_headers++).": ".$js);
        
    }
}
