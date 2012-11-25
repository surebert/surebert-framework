<?php
/**
 * Validation results object returned from sb\Validate methods
 *
 * @author paul.visco@roswellpark.org
 * @package Validate
 *
 */
namespace sb\Validate;

class Results{

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
