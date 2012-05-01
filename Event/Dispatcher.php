<?php
/**
 * Processes events in a non linear fashion by creating and executing listeners
 * 
 * @author paul.visco@roswellpark.org
 * @package sb_Event
 */
class sb_Event_Dispatcher{
	/**
	 * The listeners array which holds all of the listeners
	 * @var type 
	 */
	protected $listeners = array();

	/**
	 * Adds a new listener for an event
	 * @param string $event_name e.g. car.crash, blog.load, user.profile.update
	 * @param Closure $callback Any callable function with sb_Event or decendent as only arg
	 * @return int The unique id of the listener, used to cancel it
	 */
	public function add_listener($event_name, $callback) {
		if (!isset($this->listeners[$event_name])) {
			$this->listeners[$event_name] = array();
		}
		$this->listeners[$event_name][] = $callback;
		
		return count($this->listeners[$event_name])-1;
	}

	/**
	 * Removes an active event listener
	 * @param string $event_name e.g. car.crash, blog.load, user.profile.update
	 * @param int $listener_id The int returned frmo the add_listener method
	 */
	public function remove_listener($event_name, $listener_id) {
		if (isset($this->listeners[$event_name])){
			$this->listeners[$event_name][$listener_id] = null;
		}
	}
	
	/**
	 * Clears all listeners for a specific event, or for all events if no arg is passed
	 * @param string $evt e.g. car.crash, blog.load, user.profile.update
	 * 
	 */
	public function clear_listeners($event_name=''){
		
		if($event_name){
			unset($this->listeners[$event_name]);
		} else {
			$this->listeners = Array();
		}
		
	}
	
	/**
	 * Dispatches the named event to the listeners
	 * @param string $event_name e.g. car.crash, blog.load, user.profile.update
	 * @param sb_Event $e The event to fire
	 * @return sb_Event The event that was past to the dispatcher, after it has 
	 * been passed through each listener where it can be altered
	 */
	public  function dispatch($event_name, sb_Event $e){
		$e->set_dispatcher($this);
		$e->set_name($event_name);
		if (isset($this->listeners[$event_name])) {
		
			foreach ($this->listeners[$event_name] as $listener) {
				$e->set_last_listener($listener);
				
				if(call_user_func($listener, $e) === false  || $e->has_stopped_propagation){
					break;
				}
			}
			
			return $e;
		}
	}
}

?>