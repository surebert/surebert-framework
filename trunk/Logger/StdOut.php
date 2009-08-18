<?php
/**
 * Used to log application state to the log files.
 *
 * @Author: Paul Visco
 * @Version: 1.0 05/12/2009 05/12/2009
 *<code>
App::$logger = new sb_Logger_FileSystem(Array('audit','files','debug'));
App::$logger->set_agent_string("\t".App::$user->uname."\t".App::$user->roswell_id."\t".Gateway::$remote_addr);
App::$logger->debug('Here is a message');
//If the argument is anything other than a string it is converted to json for logging as string
App::$logger->files($obj);
 *</code>
 *
 */
 
class sb_Logger_StdOut extends sb_Logger_Base{

	/**
	 * Writes the data to file
	 * @param string $data The data to be written
	 * @param string $log_type The log_type being written to
	 * @return boolean If the data was written or not
	 */
	protected function __write($data, $log_type){
		return fwrite(STDOUT, "\n\n".date('Y/m/d H:i:s')."\n".$data);
	}

}
?>