<?php
/**
 * Validation results object returned from sb_Validate methods
 *
 * @Author Paul Visco
 * @Version 1.2 03/17/2008
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
