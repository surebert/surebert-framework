<?php
/**
 * Logs to blackHole - nothing - so that you can estimate logging performance hit
 *
 * @Author: Paul Visco
 * @Version: 1.0 05/12/2009 05/12/2009
 *<code>
App::$logger = new sb_Logger_BlackHole(Array('audit','files','debug'));
App::$logger->audit('Here is a message');
 *</code>
 *
 */

class sb_Logger_BlackHole extends sb_Logger_Base{

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