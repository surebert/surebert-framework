<?php
/**
 * Used as the basis for a command line script
 * @author visco
 * @package sb_Command
 */
class sb_Command_Base {

	/**
	 * The begin time of the script in order to calculate the total time required
	 *
	 * @var integer
	 */
	protected $start_time;

	/**
	 * The number of errors that occurred based on the message type error
	 * @var integer
	 */
	protected $number_of_errors = 0;

	public function __construct(){
		if(!Gateway::$command_line){
			die('You can only use this command from the terminal');
		}
		
		$this->start_time = microtime(true);
		$this->log(date('Y/m/d H:i:s')." - Begin Process ".__CLASS__);
		$this->set_memory_limit();
		$this->set_max_execution_time();

		if(method_exists($this, 'on_start')){
			$this->on_start();
		}
	}

	/**
	 * Sets the memory limit for the command
	 * @param integer $memory_in_MB
	 */
	public function set_memory_limit($memory_in_MB=200){
		ini_set('memory_limit', $memory_in_MB.'M');
	}

	/**
	 * Sets the maximum execution time for the script
	 * @param integer $time_in_seconds
	 */
	public function set_max_execution_time($time_in_seconds=3600){
		ini_set('max_execution_time',$time_in_seconds);
	}

	/**
	 * Determines the peak memory usage
	 * @return string The value in b, KB, or MB depending on size
	 */
	protected function get_memory_usage() {
		$mem_usage = memory_get_peak_usage(true);
		$str = '';
		if ($mem_usage < 1024) {
			$str = $mem_usage." b";
		} elseif ($mem_usage < 1048576) {
			$str = round($mem_usage/1024,2)." KB";
		} else {
			$str = round($mem_usage/1048576,2)." MB";
		}
		return $str;
	}

	/**
	 * Logs to std out
	 * @param string $message
	 */
	protected function log($message, $type="MESSAGE"){

		$type = strtoupper($type);

		switch($type){

			case 'RAW':
				break;

			case 'ERROR':
				$this->number_of_errors++;

			default:
				$message = "\n".$type.': '.$message;
		}

		file_put_contents("php://stdout", $message);
	}

	/**
	 * Calculates time
	 */
	public function __destruct(){
		$milliseconds = round((microtime(true) - $this->start_time)*1000, 2);
		$this->log('PEAK MEMORY USAGE: '.$this->get_memory_usage(), 'MESSAGE');
		$this->log('TOTAL ERRORS: '.$this->number_of_errors, 'MESSAGE');
		$this->log('TOTAL TIME REQUIRED: '.$milliseconds."ms", 'MESSAGE');

		$this->log(date('Y/m/d H:i:s')." - End Log", 'MESSAGE');

	}

}
?>