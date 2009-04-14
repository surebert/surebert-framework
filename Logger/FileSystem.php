<?php
/**
 * Used to log application state to the log files.
 *
 * @Author: Paul Visco
 * @Version: 1.32 4/17/2008 02/12/2009
 *<code>
App::$logger = new sb_Logger_FileSystem(Array('audit','files','debug'));
App::$logger->set_agent_string("\t".App::$user->uname."\t".App::$user->roswell_id."\t".Gateway::$remote_addr);
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
	private function get_log_path($log){
		
		$dir = $this->_log_root.'/'.$log.'/';
	
		if(!is_dir($dir)){
			mkdir($dir, 0777, true);
		}
		
		return $dir;
	}
	
	/**
	 * When any accepted logging method is called that is not defined it runs this
	 * @param $log_type The type of log to produce
	 * @param $arguments The arguments passed to the missing method, of which [0] is the message or object
	 * @return boolean If the log is written or not
	 */
	public function __call($log_type, $arguments){
		
		//if logging is not enabled, just return true
		if(!$this->enabled){
			return true;
		}
		
		if(array_key_exists($log_type, $this->_enabled_logs)){
			$data = $arguments[0];
			
			if(!is_string($data)){
				if($this->_conversion_method == 'print_r'){
					$data = print_r($data, 1);
				} else {
					$data = json_encode($data);
				}
			}
			return file_put_contents($this->get_log_path($log_type).date('Y_m_d').'.log', "\n\n".date('Y/m/d H:i:s')."\t".$this->_agent_str."\n".$data, FILE_APPEND);
		}
		
		return false;
	}
}
?>