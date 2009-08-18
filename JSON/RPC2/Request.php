<?php
/**
 * Models a JSONRPC 2 request as per the spec proposal at http://groups.google.com/group/json-rpc/web/json-rpc-1-2-proposal
 * @version 1.0 02/06/09
 * @author visco
 * 
 * <code>
$request = new sb_JSON_RPC2_Request();
$request->id = 'abc123';
$request->method = '+';
$request->params = Array(1,2);
$response = $request->dispatch('http://mysite.com/json/server', false);
print_r($response);
 * </code>
 * 
 *
 */
class sb_JSON_RPC2_Request{
	
	/**
	 * A String containing the name of the procedure to be invoked.
	 * @var string
	 */
	public $method;
	
	/**
	 * An Array or Object, that holds the actual parameter values for the invocation of the procedure. Can be omitted if empty.
	 * @var array/object
	 */
	public $params;
	
	/**
	 * A request identifier, will be returned with the respose
	 * @var string
	 */
	public $id;
	
	/**
	 * Popultes the properties from json recieved
	 * @param $json JSON encoded sb_JSON_RPC2_Response
	 * OR
	 * @param $method String The method to call
	 * @param $params Array/Object The params to send
	 * @param $id String The Id of the request
	 */
	public function __construct($json = null){
		
		$args = func_get_args();
		$count = count($args);
		//json
		if($count == 1 && is_string($args[0])){
		
			$o = json_decode($json);
		
			if(!is_object($o)){
				$this->error = new sb_JSON_RPC2_Error(-32700, "Parse error");
			}
			
			foreach(get_object_vars($this) as $k=>$v){
			
				if(isset($o->$k)){
					$this->$k = $o->$k;
				}
			}
			
		} else if($count > 0){
			$this->method = $args[0];
			$this->params = $args[1];
			$this->id = isset($args[2]) ? $args[2] : uniqid();
		}
		
	}

}

?>