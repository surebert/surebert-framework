<?php
/**
 * Base class for custom sessions
 * @author paul.visco@roswellpark.org
 * @package Session
 */
namespace sb;

class Session{

    public function __construct()
    {
        session_start();
    }

    /**
     * Sets a value in the session 
     * @param $key The key to store it by
     * @param $val The value to store
     */
    public function set($key, $val)
    {
        $_SESSION[$key] = $val;
    }
    
    /**
     * Gets a value from the session
     * @param $key The key it is stored by
     * @return * The value stored
     */
    public function get($key)
    {
        return $_SESSION[$key];
    }
    
    
    
}

