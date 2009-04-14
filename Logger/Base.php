<?php
/**
 * Abstract class base for logging
 *
 * @Author: Paul Visco
 * @Version: 1.32 4/17/2008 02/12/2009
 *
 */
abstract class sb_Logger_Base{
	
	/**
	 * If logging is enabled or not
	 * @var boolean true = is logging
	 */
	public $enabled = true;
	
	/**
	 * An array of accepted log files
	 * @var array
	 */
	protected $_enabled_logs = Array();
	
	/**
	 * The method used to convert non-string values for logging
	 * @var string json or print_r
	 */
	protected $_conversion_method = 'json';
	
	/**
	 * The log root ROOT.'/logs';
	 * @var string
	 */
	protected $_log_root = '';
	
	/**
	 * The string represting the agent/user that initiated the action
	 *
	 * @var string Set with $this->set_agent($str);
	 */
	protected $_agent_str = 'n/a';
	
	/**
	 * Sets the agent string representing the agent/user that initiated the action
	 *
	 * @param string $str It is a string instead of an object as it may require specific formating for your needs e.g. "\t".App::$user->uname."\t".App::$user->roswell_id."\t".App::$user->ip
	 */
	public function set_agent_string($str){
		$this->_agent_str = $str;
	}
	
	/**
	* Creates an sb_Logger instance
	* @param $log_types Array Sets the type of logging accepting, each one can be called as a method
	*/
	public function __construct($log_types = Array()){
	
		$this->add_log_types($log_types);
	}

	/**
	 * Adds additional logging methods
	 * @param string an unlimited number of string arguments representing the types of logs to enable
	 * <code>
	 * $logger->add_log_types(Array('jump', 'dance', 'run'));
	 * </code>
	 */
	public function add_log_types($log_types = Array()){

		foreach($log_types as $type){
			
			$this->_enabled_logs[$type] = true;
		}
	}
	
	/**
	 * When any accepted logging method is called that is not defined it runs this
	 * @param $log_type The type of log to produce
	 * @param $arguments The arguments passed to the missing method, of which [0] is the message or object
	 * @return boolean If the log is written or not
	 */
	abstract public function __call($log_type, $arguments);
}
?>