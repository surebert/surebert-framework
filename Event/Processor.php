<?php 
class sb_Event_Processor{
	protected $events = array();
	
	protected $logger = true;
	
	public $function_root;
	public function __construct(){
		$this->function_root = ROOT.'/private/functions/';
	}
	
	public  function add_listener($evt, $callback) {
		if (!isset($this->events[$evt])) {
			$this->events[$evt] = array();
		}
		$this->events[$evt][] = $callback;
	}

	public  function remove_listener($evt, $callback) {
		if (isset($this->events[$evt])){
			array_splice($this->events[$evt], array_search($callback, $this->events[$evt]), 1);
		}
	}
	
	public  function clear_listeners($evt){
		$this->events[$evt] = Array();
	}
	
	public  function remove_event($evt){
		if(isset($this->events[$evt])){
			unset($this->events[$evt]);
			return true;
		}
		
		return false;
	}
	
	public  function remove_events($evt){
		return $this->events = Array();
	}
	
	public  function dispatch(){
		$args = func_get_args();
		$event_name = array_shift($args);
		if (isset($this->events[$event_name])) {
			foreach ($this->events[$event_name] as $listener) {
				if(!is_callable($listener)){
					if(method_exists($listener, $event_name)){
						$listener = array($listener, $event_name);
					} else if(is_string($listener) && !is_callable($listener)){
						require_once($this->function_root.$listener.'.php');
					}
				}
				
				if(call_user_func_array($listener, $args) === false){
					break;
				}
			}
		}
	}
}

?>