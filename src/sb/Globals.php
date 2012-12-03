<?php

namespace sb;

/**
 * Stores global variable
 */
class Globals
    {

    /**
     * Stores all the data
     * @var array
     */
    protected static $hash = Array();

    /**
     * Gets a config variable
     * @param string $key The key to get the value for
     * @return string value
     */
    public static function get($key)
    {
        return isset(self::$hash[$key]) ? self::$hash[$key] : null;
    }

    /**
     * Stes a config variable
     * @param string $key The key to set
     * @param mixed $val The value to set
     */
    public static function set($key, $val)
    {
        self::$hash[$key] = $val;
    }

    /**
     * A dump of the entire config value array
     * @return array
     */
    public static function dump()
    {
        return print_r(self::$hash, 1);
    }
    
    /**
     * Persists data in the server file system
     * @param string $key The key to the file
     * @param mixed $expires_tstamp unixtimestamp or anything that strtotime takes
     * @return string The key that was set which can later be retrieved
     */
    public static function saveToFile($key=null, $expires_tstamp=null){
        
        $expires_tstamp = is_int($expires_tstamp) ? $expires_tstamp : strtotime($expires_tstamp);
        $key = is_null($key) ? uniqid() : $key;
        $key = preg_replace("~[^\w]~", "", $key);
        $file = ROOT.'/private/cache/globals/'.$key;
        $dir = dirname($file);
        if(!is_dir($dir)){
            mkdir($dir, 0775);
        }
        
        file_put_contents($file, serialize(Array('expires_tstamp' => $expires_tstamp, 'hash' => self::$hash)));
        return $key;
    }
    
    /**
     * Grabs persisted data from file
     * @param string $key The key being saved
     * @return boolean Was the data found and not expired
     */
    public static function loadFromFile($key){
        $key = preg_replace("~[^\w]~", "", $key);
        $key = $key ? $key : uniqid();
        $file = ROOT.'/private/cache/globals/'.$key;
        
        if(is_file($file)){
            $data = unserialize(file_get_contents(ROOT.'/private/cache/globals/'.$key));
            if(time() > $data['expires_tstamp']){
                unlink($file);
                self::$hash = Array();
                return false;
            } else {
                
                self::$hash = $data['hash'];
                return true;
            }
            
        } 
        
        self::$hash = Array();
        return false;
        
    }
    
    /**
     * Persist the global data to a session
     * @param string $key The key to use
     */
    public static function saveToSession($key=null){
        
        $key = $key ? $key : uniqid();
        $_SESSION[$key] = serialize(self::$hash);
        return true;
    }
    
    /**
     * Loads from a session
     * @param string $key The key to use from saveToSession
     * @return boolean was the data sucessfully found or not
     */
    public static function loadFromSession($key){
        if(isset($_SESSION[$key])){
            self::$hash = unserialize($_SESSION[$key]);
            return true;
        }
        
        self::$hash = Array();
        return false;
    }

}
