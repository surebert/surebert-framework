<?php
/**
 * Used to log application state to the log files.
 *
 * @author Paul Visco
 * @version 1.35 4/17/2008 05/12/2009
 * @package sb_Logger
 * 
 *<code>
App::$logger = new sb_Logger_FileSystem(Array('audit','files','debug'), "\tApp::$user->uname."\t".App::$user->roswell_id."\t".Gateway::$remote_addr);
App::$logger->debug('Here is a message');
//If the argument is anything other than a string it is converted to json for logging as string
App::$logger->files($obj);
 *</code>
 *
 */
class sb_Logger_FileSystem extends sb_Logger_Base{
	
	/**
	* Creates an sb_Logger instance
	* @param $log_types Array Sets the type of logging accepting, each one can be called as a method
	* @param $agent String The agent string
	*/
	public function __construct($log_types=Array(), $agent = ''){
		
		parent::__construct($log_types);
		$this->_log_root = ROOT.'/private/logs';
		
		$this->_agent_str = !empty($agent) ? $agent : Gateway::$remote_addr;
		
	}
	
	/**
	 * Grabs the log path based on the type of log
	 * @param $log Sting the log type.  Should be in the $enabled_logs array
	 * @return string The path to the log directory to be used
	 */
	private function __get_log_path($log){
		
		$dir = $this->_log_root.'/'.$log.'/';
	
		if(!is_dir($dir)){
			mkdir($dir, 0777, true);
		}
		
		return $dir;
	}

	/**
	 * Writes the data to file
	 * @param string $data The data to be written
	 * @param string $log_type The log_type being written to
	 * @return boolean If the data was written or not
	 */
	protected function __write($data, $log_type){
		return file_put_contents($this->__get_log_path($log_type).date('Y_m_d').'.log', "\n\n".date('Y/m/d H:i:s')."\t".$this->_agent_str."\n".$data, FILE_APPEND);
	}
}
?>