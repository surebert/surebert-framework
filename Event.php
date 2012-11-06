<?php

/**
 * Models an event
 * @author: paul.visco@roswellpark.org 
 */
namespace sb;

class Event{
    
    /**
     * The name of the event being processed
     * @var string 
     */
    protected $name = '';
    
    /**
     * The args to pass to the event, these can be changed by other listeners
     * @var array
     */
    protected $args = Array();
    
    /**
     * The subject of the event e.g. in a car.crash event it could be an instance of Car
     * @var mixed 
     */
    protected $subject;
    
    /**
     * Has this event stopped event propagation
     * @var type 
     */
    public $stopped_propagation = 0;
    
    /**
     * The event processor that dispatched the event
     * @var \sb\Event\Dispatcher
     */
    protected $dispatcher = null;
    
    /**
     * The last listener that was called either because dispatch reached the 
     * end or because of stopPropagation was called
     * @var Closure 
     */
    protected $last_listener = null;
    
    /**
     * Sets the data for the event
     * @param array $args 
     * @param mixed $subject The subject of the event
     */
    public function __construct($args=Array(), $subject=false)
    {
        
        $this->setArgs($args);
        $this->setSubject($subject);
    }
    
    /**
     * Stops propagation of the event by dispatcher 
     */
    public function stopPropagation()
    {
        $this->stopped_propagation = 1;
    }
    
    /**
     * Gets the nmae of the event being dispatched
     */
    public function get_name()
    {
        return $this->name;
    }
    
    /**
     * Sets the event name being dispatched
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    
    /**
     * Sets a specific _args key
     * @param type $key The key to set
     * @param type $val The value to set for the key
     */
    public function setArg($key, $val='')
    {
        $this->args[$key] = $val;
    }
    
    /**
     * Sets the events _args property
     * @param array $args
     */
    public function setArgs(Array $args)
    {
        $this->args = $args;
    }
    
    /**
     * Gets the event _args value for a specific key if passed
     * @param string $key the specific key to fetch
     * @return mixed Whatever value the key holds or the full array if no key is specified
     */
    public function getArg($key='')
    {
        
        if(isset($this->args[$key])){
            return $this->args[$key];
        } else {
            return null;
        }
    }
    
    /**
     * Gets the event _args as a whole array
     * @return mixed Whatever value the key holds or the full array if no key is specified
     */
    public function getArgs()
    {
        return $this->args;
    }
    
    /**
     * The subject of the event e.g. in a car.crash event it could be an instance of Car
     * @param mixed $subject 
     */
    public function setSubject($subject='')
    {
         $this->subject = $subject;
    }
    
    /**
     * Gets the subject of the event e.g. in a car.crash event it could be an instance of Car
     * @return type 
     */
    public function getSubject()
    {
        return $this->subject;
    }
    
    /**
     * Gets the event dispatcher that dispatched this event
     * @return \sb\Event\Dispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }
    
    /**
     * Sets the event dispatcher that dispatched this event
     * @param \sb\Event\Dispatcher $dispatcher 
     */
    public function setDispatcher(\sb\Event\Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }
    
    /**
     * Sets the last listener called before it reached the end of the cycle or 
     * stopPropagation was called
     * @param type $listener 
     */
    public function setLastListener($listener)
    {
        $this->last_listener = $listener;
    }
    
    /**
     * Gets the last listener called before it reached the end of the cycle or 
     * stopPropagation was called
     * @param type $listener 
     */
    public function getLastListener($listener)
    {
        return $this->last_listener;
    }
}

