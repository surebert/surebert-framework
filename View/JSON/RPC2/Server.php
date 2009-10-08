<?php
/**
 * This view serves all the public methods of the view as a JSON service
 * @author visco
 * @version 1.0 02/24/09 02/24/09
 *
 */
class sb_View_JSON_RPC2_Server extends sb_View{
	
	/**
	 * The JSON server that serves rp_Directory_Mysql
	 * @var rp_JSON_RPC2_Server
	 */
	protected $server;
	 
	public function __construct(){
		
		$this->server = new sb_JSON_RPC2_Server();
		$this->server->surpress_http_status = true;
		$this->server->serve_instance($this);
		
		$this->server->remove_methods(Array('render', 'set_request', '__construct'));
	}
	
	/**
	 * Handles the requests by passing them to the JSON server when a .view view template is not found
	 * @see sb_View#template_not_found()
	 */
	protected function template_not_found($template=''){
		
		echo $this->server->handle();
	}
}
?>