<?php
/**
 * Used to response to JSON_RPC2 requests as per the spec proposal at http://groups.google.com/group/json-rpc/web/json-rpc-1-2-proposal
 * @version 1.21 02/06/09 05/12/09
 * @author visco
 * @package sb_JSON_RPC2
 * <code>
 *
 //a function to serve
 function add($x, $y){
 return $x+$y;
 }

 //create the server
 $server = new sb_JSON_RPC2_Server();

 //methods you are allowing the server to serve
 $server->serve_methods(Array(
 'add' => 'add'
 ));

 //debug one of the methods
 //echo $server->handle('{"method":"/","params":[1,2],"id":"abc123"}');

 //serve the methods requested in Gateway::$request->post[]; which is sent by a sb_JSON_RPC2_Request's dispatch method
 echo $server->handle();
 * </code>
 *
 */


class sb_JSON_RPC2_Server {

/**
 * The transport method to listen for data on
 * @var string post or get or both
 */
	public $method = 'post';

	/**
	 * Determines if objects passed as params are converted to hashes instead of objects
	 * @var boolean
	 */
	public $json_decode_assoc_array = true;

	/**
	 * If set to true then HTTP status headers 200, 400, 404, 500 are not send with response as some clients cannot parse 404 or 500 responses.
	 * @var boolean
	 */
	public $suppress_http_status = false;

	/**
	 * Determines if the response body is first serialized as php format before
	 * being json encoded to preserve hash array data that would otherwise
	 * be converted to objects.  This is set by the client and is set to true
	 * if the client passes a php_serialize_response HTTP header.
	 * @var boolean
	 */
	public $php_serialize_response = false;

	/**
	 * The sb_logger to write to
	 * @var sb_Logger
	 */
	protected $logger;

	/**
	 * An array of methods to serve, can be references to function or statically called class methods, can be from mixed origin.
	 * The key should match the method called by the remote sb_JSON_RPC2_Request
	 * @var array
	 */
	protected $methods = Array();

	/**
	 * The gz encoding level to use when transferring data
	 * @var integer
	 */
	protected $gz_encode_level = false;

	/**
	 * Create an instance
	 */
	public function __construct($methods=Array()) {
		if(isset($_SERVER) && isset($_SERVER['HTTP_PHP_SERIALIZE_RESPONSE'])) {
			$this->php_serialize_response = true;
		}

		$this->methods['get_methods'] = Array($this, 'get_methods');
		$this->methods['get_phpdoc'] = Array($this, 'get_phpdoc');

		if(!empty($methods)) {
			$this->serve_methods($methods);
		}
	}

	/**
	 * Determines is gzencoding is used
	 * @param $level Integer A number between 0-9, the compression level, higher takes longer
	 */
	public function use_gz_encoding($level) {
		$this->gz_encode_level = $level;
	}

	/**
	 * Sets the key that data is encrypted with and turns on encryption, the client must use the same key
	 * @param $key String
	 */
	public function use_encryption($key) {
		$this->encryptor = new sb_Encryption_ForTransmission($key);
		$this->encryption_key = $key;

		//decrypt cookies if sent
		foreach(Gateway::$cookie as $k=>$v) {
			Gateway::$cookie[$k] = $this->encryptor->decrypt($v);
		}
	}

	/**
	 * Sets the logger for the client
	 * @param $logger sb_Logger
	 */
	public function set_logger(sb_Logger_Base $logger) {
		$this->logger = $logger;
	}

	/**
	 * Removes multiple methods from service
	 * @param $methods Array An array of method names to remove from service
	 */
	public function remove_methods($methods=Array()) {
		if(is_array($methods)) {
			foreach($methods as $method) {
				$this->remove_method($method);
			}
		}
	}

	/**
	 * Removes a method from service
	 * @param $method string The name of the method to remove from service
	 */
	public function remove_method($method) {
		unset($this->methods[$method]);
	}

	/**
	 * Add methods to serve
	 * @param $methods Array An named hash of methods to serve, key is name to serve as, value is the user callable function name.  To serve method of objects pass the value as an array with the first value being the object and the second, the method to call.
	 *
	 * The example substract below would call a class named Math's substract method e.g. Math::subtract
	 *
	 * <code>
	 * //methods you are allowing the server to serve
	 * $server->serve_methods(Array(
	 * 	'subtract' => Array('Math', 'substract')
	 * 	'+' => 'add'
	 * ));
	 *
	 * </code>
	 */
	public function serve_methods($methods) {

		foreach($methods as $k=>$v) {
			$this->methods[$k] = $v;
		}
	}

	/**
	 * Serves a function
	 * @param $function String The name of the function to serve
	 */
	public function serve_function($function) {
		if(function_exists($function)) {
			$this->methods[$function] = $function;
		} else {
			throw(new Exception(__METHOD__."'s \$function argument must be a string, which is the name of an existing function"));
		}
	}

	/**
	 * Serves all the methods of a class
	 * @param $class
	 * <code>
	 * //methods you are allowing the server to serve
	 * $server->serve_class('Math');
	 *
	 * </code>
	 */
	public function serve_class($class) {

		if(class_exists($class)) {
			$methods = get_class_methods($class);
			foreach($methods as $method) {
				$this->methods[$method] = Array($class, $method);
			}
		} else {
			throw(new Exception(__METHOD__."'s \$class argument must be a class name"));
		}

	}

	/**
	 * Serves all the methods of an object instance
	 * @param $instance
	 *
	 *<code>
	 * //methods you are allowing the server to serve
	 * $instance = new SomeClass();
	 * $server->serve_instance($instance);
	 *
	 * </code>
	 */
	public function serve_instance($instance) {

		if(is_object($instance)) {
			$class = get_class($instance);
			if(class_exists($class)) {
				$methods = get_class_methods($class);
			}

			foreach($methods as $method) {

				$this->methods[$method] = Array($instance, $method);
			}

		} else {
			throw(new Exception(__METHOD__."'s \$obj argument must be an object instance"));
		}

	}

	/**
	 * Parses the request
	 * @param $json_request
	 */
	protected function parse_request($debug_request='') {


		$response = new sb_JSON_RPC2_Response();

		$request = null;

		//DEBUGGING
		if(!empty($debug_request)) {
			$request = new sb_JSON_RPC2_Request($debug_request);
			$input = $debug_request;

		//FROM POST DATA
		} else if($this->method == 'post' || $this->method == 'both') {
				$post = file_get_contents("php://input");

				if(isset($this->encryption_key)) {
					$post = $this->encryptor->decrypt($post);
				}

				$request = new sb_JSON_RPC2_Request($post);
				$input = $post;
			}

		$get = Gateway::$request->get;

		//FROM GET DATA
		if(is_null($request)
			&& ($this->method == 'get' || $this->method == 'both')
			&& isset($get['method']) && isset($get['params']) && isset($get['id'])
		) {

			$request = new sb_JSON_RPC2_Request();
			$request->method = $get['method'];

			$params =$get['params'];

			if(!preg_match("~[\[\{]~", substr($params, 0, 1))) {

				$params = base64_decode($params);
			}

			$request->params = json_decode($params);

			$request->id = $get['id'];
			$input = json_encode($request);
		}

		if(is_null($request)) {
			$response->error = new sb_JSON_RPC2_Error(-32700, 'Parse Error', "Data Received: ".$input);
		} else {
			$response->id = $request->id;
		}

		if($this->logger instanceof sb_Logger) {
			$this->logger->add_log_types(Array('sb_json_rpc2_server'));
			$this->logger->sb_json_rpc2_server("--> ". $input);
		}

		//check for requested remote procedure
		if(!isset($this->methods[$request->method]) || !is_callable($this->methods[$request->method])) {
			$response->error = new sb_JSON_RPC2_Error();
			$response->error->code = -32601;
			$response->error->message = "Procedure not found";
		}

		if(isset($this->methods[$request->method]) && is_callable($this->methods[$request->method])) {

			if(is_object($request->params)) {
				$answer = call_user_func($this->methods[$request->method], $request->params);
			} else if(is_array($request->params)) {
					$answer = call_user_func_array($this->methods[$request->method], $request->params);
				}

			//if they return an error from the method call, return that
			if($answer instanceof sb_JSON_RPC2_Error) {
				$response->error = $answer;

			} else {
			//otherwise return the answer
				$response->result = $answer;
			}
		}

		//remove unnecessary properties
		if($response->error instanceof sb_JSON_RPC2_Error) {

			unset($response->result);
			if(is_null($response->error->data)) {
				unset($response->error->data);
			}
		} else {
			unset($response->error);
		}

		//log the final response
		if($this->logger instanceof sb_Logger) {
			$this->logger->sb_json_rpc2_server('<-- '.json_encode($response));

		}

		return $response;
	}

	/**
	 * Serves data based on the json_request if set, otherwise based on Gateway::$request->post['sb_JSON_RPC2_Request']
	 * @param $json_request String This optional argument can be used for debugging the server.  A sb_JSON_RPC2_Request formatted JSON string e.g. {"method":"add","params":[1,2],"id":"abc123"}
	 * @return string JSON encoded sb_JSON_RPC2_Response
	 */
	public function handle($json_request = null) {

		$response = $this->parse_request($json_request);

		$message = 'OK';
		$status = 200;
		//headers from spec here http://json-rpc.googlegroups.com/web/json-rpc-over-http.html
		if(isset($response->error) && $response->error instanceof sb_JSON_RPC2_Error) {
			$code = $response->error->code;

			if(
			in_array($code, Array(-32700, -3260, -32603))
				|| ($code <= -32000 && $code >= -32099)) {
				$status = 500;
				$message = 'Internal Server Error';
			} else if($code == -32600) {
					$message = 'Bad Request';
					$status = 400;
				} else if ($code == -32601) {
						$message = 'Not Found';
						$status = 404;
					}

		}

		if(!$this->suppress_http_status) {
			header("Content-Type: application/json-rpc");
			header("HTTP/1.1 ".$status." ".$message);
		}

		//serialize PHP to preserve format of hashes if client requests it in headers
		if($this->php_serialize_response) {
			$json_response = serialize($response);
		} else {
			$json_response =  json_encode($response);
		}

		if(!empty($this->encryption_key)) {
			$json_response = $this->encryptor->encrypt($json_response);
		}

		if($this->gz_encode_level !== false) {
			$json_response = gzencode($json_response, $this->gz_encode_level);
		}

		return $json_response;

	}

	/**
	 * Get the methods available for this sb_JSON_RPC2_Server instance
	 * @return Array - Object once json_encoded
	 */
	protected function get_methods($html = true) {

		$arr = Array();

		foreach($this->methods as $name=>$func) {

			$reflect = $this->get_reflection($name);

			if($reflect) {
				$params = $reflect->getParameters();
				$ps = '';
				foreach($params as $param) {
					$ps[] = '$'.$param->getName();
				}

				$key = $name.'('.implode(', ', $ps).')';

				$arr[$key] = $reflect->getDocComment();
			}
		}

		if($html == false) {
			return $arr;
		} else {
			return $this->methods_to_html($arr);
		}
	}

	/**
	 * Coverts the methods served into an HTML string
	 * @param $arr
	 * @return string HTML
	 */
	protected function methods_to_html($methods) {

		$html = '<style type="text/css">li{background-color:#c8c8d4;}h1{font-size:1.0em;padding:3px 0 3px 10px;color:white;background-color:#8181bd;}pre{color:#1d1d4d;}</style><ol>';
		foreach($methods as $method=>$comments) {
			$html .= '<li><h1>$server->'.$method.';</h1><pre>'."\t".$comments.'</pre></li>';
		}

		$html .= '</ol>';

		return $html;
	}

	/**
	 * Get reflection from php doc of method
	 * @param string $name The name of the method to grab the docs for
	 * @return ReflectionMethod/ReflectionFunction depending which type of object was requested by
	 */
	protected function get_reflection($name) {

		if(isset($this->methods[$name])) {
			$func = $this->methods[$name];
		} else {
			return false;
		}

		if(is_array($func)) { //class

			$reflect = new ReflectionMethod($func[0], $func[1]);

		} else if(is_string($func)) { //function
				$reflect = new ReflectionFunction($name);
			}

		return $reflect;
	}

	/**
	 * Get php doc and return_type for method by name
	 * @param string $name The name of the method to grab the docs for
	 * @return Object with return_type and phpdoc property
	 */
	protected function get_phpdoc($name) {

		$reflect = $this->get_reflection($name);

		$response = new stdClass();
		$response->phpdoc = $reflect->getDocComment();

		if(preg_match("~@return (.*?) (.*?)\*/$~s", $response->phpdoc, $match)) {
			$response->return_type = $match[1];
		} else {
			$response->return_type = 'n/a';
		}

		return $response;
	}

}
?>