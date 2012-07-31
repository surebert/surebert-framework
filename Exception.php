<?php
/**
 * Used to throw custom exceptions
 * @author paul.visco@roswellpark.org
 * @package sb_Exception
 */
namespace sb;

class Exception extends \Exception
{

    private $context = null;

    public function __construct($code, $message, $file, $line, $context = null)
    {
        parent::__construct($message, $code);
        $this->file = $file;
        $this->line = $line;
        $this->context = $context;
    }
}
