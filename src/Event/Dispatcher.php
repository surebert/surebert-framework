<?php

/**
 * Processes events in a non linear fashion by creating and executing listeners
 * 
 * @author paul.visco@roswellpark.org
 * @package Event
 */

namespace sb\Event;

class Dispatcher {

    /**
     * The listeners array which holds all of the listeners
     * @var type 
     */
    protected $listeners = array();

    /**
     * If set the logger is used for event dispatch logging
     * @var \sb\Logger_Base 
     */
    protected $logger = null;

    /**
     * The name of the log to write to
     * @var string
     */
    protected $log_name = 'event_dispatcher';

    /**
     * Sets a logger for easy tracking of events firing and debugging
     * @param \sb\Logger_Base $logger 
     * @param string $log_name The name of the log to log to
     */
    public function setLogger(\sb\Logger\Base $logger, $log_name = 'event_dispatcher') {
        $this->log_name = $log_name;
        $this->logger = $logger;
    }

    /**
     * Adds a new listener for an event
     * @param string $event_name e.g. car.crash, blog.load, user.profile.update
     * @param Closure $callback Any callable function with \sb\Event or decendent as only arg
     * @return int The unique id of the listener, used to cancel it
     */
    public function addListener($event_name, $callback) {
        if (!isset($this->listeners[$event_name])) {
            $this->listeners[$event_name] = array();
        }
        $this->listeners[$event_name][] = $callback;

        return count($this->listeners[$event_name]) - 1;
    }

    /**
     * Removes an active event listener
     * @param string $event_name e.g. car.crash, blog.load, user.profile.update
     * @param int $listener_id The int returned frmo the addListener method
     */
    public function removeListener($event_name, $listener_id) {
        if (isset($this->listeners[$event_name])) {
            $this->listeners[$event_name][$listener_id] = null;
        }
    }

    /**
     * Clears all listeners for a specific event, or for all events if no arg is passed
     * @param string $evt e.g. car.crash, blog.load, user.profile.update
     * 
     */
    public function clearListeners($event_name = '') {

        if ($event_name) {
            unset($this->listeners[$event_name]);
        } else {
            $this->listeners = Array();
        }
    }

    /**
     * Grab an array of all currently listened for events.
     * @param string event_name The event name to check for or all if empty
     * @return array
     */
    public function getListeners($event_name = '') {
        if (empty($event_name)) {
            $i = array_keys($this->listeners);
            sort($i);
            return $i;
        }

        if (isset($this->listeners[$event_name])) {
            return $this->listeners[$event_name];
        }

        return Array();
    }

    /**
     * Count all currently listened for events.
     * @param string event_name The event name to check for or all if empty
     * @return integer
     */
    public function countListeners($event_name = '') {

        if (empty($event_name)) {
            return count($this->listeners);
        }

        if (isset($this->listeners[$event_name])) {
            return count($this->listeners[$event_name]);
        }

        return 0;
    }

    /**
     * Dispatches the named event to the listeners
     * @param string $event_name e.g. car.crash, blog.load, user.profile.update
     * @param \sb\Event $e The event to fire
     * @param boolean $allow_partial_match Allows partial match of listener to fire event.
     * e.g. event listener for event "blog" would fire when "blog.delete" or "blog.update" is fired
     * @return \sb\Event The event that was past to the dispatcher, after it has 
     * been passed through each listener where it can be altered
     */
    public function dispatch($event_name, \sb\Event $e, $allow_partial_match = false) {
        $e->setDispatcher($this);
        $e->setName($event_name);
        $listeners = Array();

        if ($allow_partial_match) {

            $arr = $this->listeners;

            foreach ($arr as $k => $a) {
                if (preg_match("~^" . $k . "~", $event_name)) {
                    $listeners = array_merge($listeners, $a);
                }
            }
        } elseif (isset($this->listeners[$event_name])) {
            $listeners = $this->listeners[$event_name];
        }

        $x = 0;
        foreach ($listeners as $listener) {
            $e->setLastListener($listener);
            if (!is_callable($listener)) {

                continue;
            }
            $call = call_user_func($listener, $e);

            if ($this->logger) {
                if (is_array($listener)) {
                    $reflection = new \ReflectionMethod($listener[0], $listener[1]);
                    $name = $reflection->getName();
                    if (is_string($listener[0])) {
                        $name = $reflection->getDeclaringClass()->getName() . '::' . $name . '($e)';
                    } else {
                        $name = 'new ' . $reflection->getDeclaringClass()->getName() . '()->' . $name . '($e)';
                    }
                } else {
                    $reflection = new \ReflectionFunction($listener);
                    $name = $reflection->getName() . '($e)';
                }

                $this->logger->{$this->log_name . '.' . $event_name}(Array(
                    'listener' => Array(
                        'func' => $name,
                        'file' => str_replace(ROOT, '', $reflection->getFileName()),
                        'line' => $reflection->getStartLine()
                    ),
                    'event' => Array(
                        'args' => $e->getArgs(),
                        'stopped_propagation' => $e->stopped_propagation
                    )
                ));
            }

            if ($call === false || $e->stopped_propagation) {
                if ($this->logger) {
                    
                }
                break;
            }
            $x++;
        }

        return $e;
    }

}

