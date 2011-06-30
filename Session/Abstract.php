<?php
/**
 * Interface for building custom sessions
 * @author visco
 * @version 1.0 01/24/2009 01/24/2009
 * @package sb_Session
 */

abstract class sb_Session_Abstract extends sb_Session{
	
	/**
	 * The session ma xlifetime
	 * @var integer
	 */
	private $session_life_time;
	
	/**
	 * 
	 * @param $db PDO the database conection to store the sessions in
	 * @param $session_life_time integer
	 * @return unknown_type
	 */
	public function __construct($db, $session_life_time=null){
		
		$this->db = $db;
		
		$this->token = md5(Gateway::$remote_addr.Gateway::$agent);
		
		// get session lifetime
        $this->session_life_time = !is_null($session_life_time) ? $session_life_time : ini_get("session.gc_maxlifetime");
        // register the new handler
        
        session_set_save_handler(
            array($this, 'open'),
            array($this, 'close'),
            array($this, 'read'),
            array($this, 'write'),
            array($this, 'destroy'),
            array($this, 'gc')
        );
        register_shutdown_function('session_write_close');
        
        session_start();
	}
	
	/**
	 * Opens the session, not needed for db based sessions
	 * @return boolean
	 */
	abstract public function open();
	
	/**
	 * Closes the session, not needed for db based sessions
	 * @return boolean
	 */
	abstract public function close();
	
	/**
	 * Closes the session, not needed for db based sessions
	 * @return boolean
	 */
	abstract public function read($session_id);
	
	/**
	 * updates session data in the mysql database
	 * @param $session_id
	 * @param $data The session data to write
	 * @return boolean
	 */
	abstract public function write($session_id, $data);
	
	/**
	 * Destroys a sessions by deleting it from the database
	 * @return unknown_type
	 */
	abstract public function destroy($session_id);
		
	/**
	 * Garbage collects any open sessions that are no longer valid
	 * @return boolean
	 */
	abstract public function gc();
	
	/**
	 * regenerate the session id
	 * @return boolean
	 */
	abstract public function regenerate_id();
	
}

?>