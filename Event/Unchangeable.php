<?php
/**
 * Extends sb_Event to make an event for which the listeners cannot reset the original
 * data past to the event constructors 
 * @author paul.visco@roswellpark.org
 * @package Event
 * 
 */
namespace sb;
class Event_Unchangeable extends _Event{
    /**
     * Sets the data for the event
     * @param array $data 
     * @param mixed $subject The subject of the event
     */
    public function __construct($data=Array(), $subject=false){
        
        $this->args = $data;
        $this->set_subject($subject);
    }
    
    /**
     * Used to catch trying to override the args property
     * @param string $name The property being called to set
     * @param mixed $value The value to set it to
     * @throws sb_Event_Unchangeable_Exception 
     */
    public function __set($name, $value){
        if($name == 'args'){
            throw(new sb_Event_Unchangeable_Exception());
        }
    }
    
    /**
     * Stops programmer who wrote listener from overriding the args
     * @throws \sb\Event_Unchangeable_Exception 
     */
    public function set_args(){
        throw(new \sb\Event_Unchangeable_Exception());
    }
    
    /**
     * Stops programmer who wrote listener from overriding the args
     * @throws \sb\Event_Unchangeable_Exception 
     */
    public function set_arg(){
        throw(new \sb\Event_Unchangeable_Exception());
    }
}

/**
 * The exception thrown when a programmers tries to changed the args value
 * of an Event past to a listener
 * @author paul.visco@roswellpark.org 
 */
class Event_Unchangeable_Exception extends \Exception{
    protected $message = 'Cannot change args of an \sb\Event_Unchangeable event';
    protected $code = 1;
}

?>