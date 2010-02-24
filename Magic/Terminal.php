<?php
/**
 * Used to handle sb.terminal server side requests
 *
 * @package sb_Magic
 */
class sb_Magic_Terminal implements sb_Magic_Model{

	/**
	 * Runs when a method that does not exist is called
	 * @param sting $method
	 * @return string
	 *
	 * @servable true
	 */
	public function __call($method, $args){
		return 'Command Not Found';
	}

	public function filter_output($out){
		return $out;
	}
}
?>