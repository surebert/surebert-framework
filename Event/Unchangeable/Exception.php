<?php

/**
 * The exception thrown when a programmers tries to changed the args value
 * of an Event past to a listener
 * @author paul.visco@roswellpark.org 
 */
namespace sb\Event\Unchangeable;

class Exception extends \Exception
{

    protected $message = 'Cannot change args of an \sb\Event\Unchangeable event';

    protected $code = 1;

}

