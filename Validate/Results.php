<?php
/**
 * Validation results object returned from sb_Validate methods
 *
 * @author Paul Visco
 * @version 1.2 03/17/2008
 * @package sb_Validate
 *
 */
class sb_Validate_Results{

	/**
	 * Is the string valid or not
	 *
	 * @var boolean
	 */
	public $valid;

	/**
	 * The reason it is valid or not
	 *
	 * @var string
	 */
	public $message;

	/**
	 * An object with additional data properties specific to the validation
	 *
	 * @var object
	 */
	public $data;

	public $value;

}
?>