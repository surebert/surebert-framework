<?php
/**
 * Used to log application state to the log files.
 *
 * @author Paul Visco
 * @package sb_Logger
 * 
 */
namespace sb\Logger;

class CommandLine extends \sb\Logger\FileSystem{
	
	/**
	* Creates an sb_Logger instance
	* @param $agent String The agent string
	*/
	public function __construct($agent = '', $log_root=''){
		
		parent::__construct($agent);
		$log_root = !empty($log_root) ? $log_root : ROOT.'/private/logs';
		$this->set_log_root($log_root);
		
	}

	/**
	 * Allows the setting of the log root
	 * @param <type> $log_root
	 */
	public function set_log_root($log_root){
		$this->_log_root = $log_root;
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
		return file_put_contents($this->__get_log_path($log_type).date('Y_m_d').'.log', "\n".$data, FILE_APPEND);
	}
}
?>