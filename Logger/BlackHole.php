<?php
/**
 * Logs to blackHole - nothing - so that you can estimate logging performance hit
 *
 * @author paul.visco@roswellpark.org
 * @package Logger
 *
 */
namespace sb;
class Logger_BlackHole extends Logger_Base{

	/**
	 * Writes the data to file
	 * @param string $data The data to be written
	 * @param string $log_type The log_type being written to
	 * @return boolean If the data was written or not
	 */
	protected function __write($data, $log_type){
		return true;
	}

}
?>