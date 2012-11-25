<?php

/**
 * Extends \sb\Event to make an event for which the listeners cannot reset the original
 * data past to the event constructors 
 * @author paul.visco@roswellpark.org
 * @package Event
 * 
 */
namespace sb\Event;

use \sb\Event;

class Unchangeable extends Event
{

    /**
     * Sets the data for the event
     * @param array $data 
     * @param mixed $subject The subject of the event
     */
    public function __construct($data = Array(), $subject = false)
    {

        $this->args = $data;
        $this->set_subject($subject);
    }

    /**
     * Used to catch trying to override the args property
     * @param string $name The property being called to set
     * @param mixed $value The value to set it to
     * @throws \sb\Event\Unchangeable\Exception 
     */
    public function __set($name, $value)
    {
        if ($name == 'args') {
            throw(new \sb\Event\Unchangeable\Exception());
        }
    }

    /**
     * Stops programmer who wrote listener from overriding the args
     * @throws \sb\Event\Unchangeable\Exception 
     */
    public function setArgs()
    {
        throw(new \sb\Event\Unchangeable\Exception());
    }

    /**
     * Stops programmer who wrote listener from overriding the args
     * @throws \sb\Event_Unchangeable_Exception 
     */
    public function setArg()
    {
        throw(new \sb\Event\Unchangeable\sException());
    }

}

