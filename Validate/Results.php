<?php
/**
 * Validation results object returned from sb_Validate methods
 *
 * @author paul.visco@roswellpark.org
 * @package Validate
 *
 */
class Validate_Results{

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