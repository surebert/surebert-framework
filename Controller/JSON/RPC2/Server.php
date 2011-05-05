<?php

/**
 * Used to response to JSON_RPC2 requests as per the spec proposal at http://groups.google.com/group/json-rpc/web/json-rpc-1-2-proposal
 * @version 1.21 02/06/09 05/12/09
 * @author visco
 * @package sb_JSON_RPC2
 *
 */
class sb_Controller_JSON_RPC2_Server extends sb_Controller {

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
	 * The gz encoding level to use when transferring data
	 * @var integer
	 */
	protected $gz_encode_level = false;

	/**
	 * Create an instance
	 *
	 * <code>
	 *
	 * //a function to serve
	 * function add($x, $y){
	 * 	return $x+$y;
	 * }
	 *
	 * //create the server
	 * $server = new sb_JSON_RPC2_Server();
	 *
	 * //methods you are allowing the server to serve
	 * $server->serve_methods(Array(
	 * 	'add' => 'add'
	 * ));
	 *
	 * //debug one of the methods
	 * //echo $server->handle('{"method":"/","params":[1,2],"id":"abc123"}');
	 *
	 * //serve the methods requested in Gateway::$request->post[]; which is sent by a sb_JSON_RPC2_Request's dispatch method
	 * echo $server->handle();
	 * </code>
	 */
	public function __construct($methods=Array()) {
		if (isset($_SERVER) && isset($_SERVER['HTTP_PHP_SERIALIZE_RESPONSE'])) {
			$this->php_serialize_response = true;
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
		foreach (Gateway::$cookie as $k => $v) {
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
	 * Parses the request
	 * @param $json_request
	 */
	protected function parse_request($debug_request='') {

		$response = new sb_JSON_RPC2_Response();

		$request = null;

		$input = '';

		//DEBUGGING
		if (!empty($debug_request)) {
			$request = new sb_JSON_RPC2_Request($debug_request);
			$input = $debug_request;

			//FROM POST DATA
		} else if ($this->method == 'post' || $this->method == 'both') {
			$post = file_get_contents("php://input");

			if (isset($this->encryption_key)) {
				$post = $this->encryptor->decrypt($post);
			}

			$request = new sb_JSON_RPC2_Request($post);
			$input = $post;
		}

		$get = Gateway::$request->get;

		//FROM GET DATA
		if (is_null($request)
				&& ($this->method == 'get' || $this->method == 'both')
				&& isset($get['method']) && isset($get['params']) && isset($get['id'])
		) {

			$request = new sb_JSON_RPC2_Request();
			$request->method = $get['method'];

			$params = $get['params'];

			if (!preg_match("~[\[\{]~", substr($params, 0, 1))) {

				$params = base64_decode($params);
			}

			$request->params = json_decode($params);

			$request->id = $get['id'];
			$input = json_encode($request);
		}

		if (is_null($request)) {
			$response->error = new sb_JSON_RPC2_Error(-32700, 'Parse Error', "Data Received: " . $input);
		} else {
			$response->id = $request->id;
		}

		if ($this->logger instanceof sb_Logger) {
			$this->logger->add_log_types(Array('sb_json_rpc2_server'));
			$this->logger->sb_json_rpc2_server("--> " . $input);
		}
		$servable = false;
		if (method_exists($this, $request->method)) {
			$reflection = new ReflectionMethod($this, $request->method);

			//check for phpdocs
			$docs = $reflection->getDocComment();
			$servable = false;
			if (!empty($docs)) {
				if (preg_match("~@servable (true|false)~", $docs, $match)) {
					$servable = $match[1] == 'true' ? true : false;
				}
			}
		}
		//check for requested remote procedure
		if ($servable) {

			if (is_object($request->params)) {
				$answer = call_user_func(Array($this, $request->method), $request->params);
			} else if (is_array($request->params)) {
				$answer = call_user_func_array(Array($this, $request->method), $request->params);
			}

			//if they return an error from the method call, return that
			if ($answer instanceof sb_JSON_RPC2_Error) {
				$response->error = $answer;
			} else {
				//otherwise return the answer
				$response->result = $answer;
			}
		} else {

			$response->error = new sb_JSON_RPC2_Error();
			$response->error->code = -32601;
			$response->error->message = "Procedure not found";
		}

		//remove unnecessary properties
		if ($response->error instanceof sb_JSON_RPC2_Error) {

			unset($response->result);
			if (is_null($response->error->data)) {
				unset($response->error->data);
			}
		} else {
			unset($response->error);
		}

		//log the final response
		if ($this->logger instanceof sb_Logger) {
			$this->logger->sb_json_rpc2_server('<-- ' . json_encode($response));
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
		if (isset($response->error) && $response->error instanceof sb_JSON_RPC2_Error) {
			$code = $response->error->code;

			if (in_array($code, Array(-32700, -3260, -32603))
				|| ($code <= -32000 && $code >= -32099)) {
				$status = 500;
				$message = 'Internal Server Error';
			} else if ($code == -32600) {
				$message = 'Bad Request';
				$status = 400;
			} else if ($code == -32601) {

				$override = $this->not_found();
				if(!is_null($override)){
					return $override;
				}
				$message = 'Not Found';
				$status = 404;
			}
		}

		if (!$this->suppress_http_status) {
			header("Content-Type: application/json-rpc");
			header("HTTP/1.1 " . $status . " " . $message);
		}

		//serialize PHP to preserve format of hashes if client requests it in headers
		if ($this->php_serialize_response) {
			$json_response = serialize($response);
		} else {
			$json_response = json_encode($response);
		}

		if (!empty($this->encryption_key)) {
			$json_response = $this->encryptor->encrypt($json_response);
		}

		if ($this->gz_encode_level !== false) {
			$json_response = gzencode($json_response, $this->gz_encode_level);
		}

		return $json_response;
	}

	/**
	 * Get the methods available for this sb_JSON_RPC2_Server instance
	 * @return Array - Object once json_encoded
	 * @servable true
	 */
	protected function get_methods($html = true) {

		$arr = Array();

		foreach (get_class_methods($this) as $method) {

			$reflect = new ReflectionMethod($this, $method);

			if ($reflect) {
				$docs = $reflect->getDocComment();
				$servable = false;
				if (!empty($docs)) {
					if (preg_match("~@servable (true|false)~", $docs, $match)) {
						$servable = $match[1] == 'true' ? true : false;
					}
				}
				if (!$servable) {
					continue;
				}

				$params = $reflect->getParameters();
				$ps = Array();
				foreach ($params as $param) {
					$ps[] = '$' . $param->getName();
				}

				$key = $method . '(' . implode(', ', $ps) . ')';

				$arr[$key] = $reflect->getDocComment();
			}
		}

		if ($html == false) {
			return $arr;
		} else {
			return $this->methods_to_html($arr);
		}
	}

	/**
	 * Get php doc and return_type for method by name
	 * @param string $name The name of the method to grab the docs for
	 * @return Object with return_type and phpdoc property
	 * @servable true
	 */
	protected function get_phpdoc($method) {

		if (method_exists($this, $method)) {
			$reflect = new ReflectionMethod($this, $method);
		} else {
			$response->error = new sb_JSON_RPC2_Error();
			$response->error->code = -32602;
			$response->error->message = "Invalid method parameters";
			return $response;
		}

		$response = new stdClass();
		$response->phpdoc = $reflect->getDocComment();

		if (preg_match("~@return (.*?) (.*?)\*/$~s", $response->phpdoc, $match)) {
			$response->return_type = $match[1];
		} else {
			$response->return_type = 'n/a';
		}

		return $response;
	}

	/**
	 * Coverts the methods served into an HTML string
	 * @param $arr
	 * @return string HTML
	 */
	protected function methods_to_html($methods) {

		$html = '<style type="text/css">li{background-color:#c8c8d4;}h1{font-size:1.0em;padding:3px 0 3px 10px;color:white;background-color:#8181bd;}pre{color:#1d1d4d;}</style><ol>';
		foreach ($methods as $method => $comments) {
			$html .= '<li><h1>$server->' . $method . ';</h1><pre>' . "\t" . $comments . '</pre></li>';
		}

		$html .= '</ol>';

		return $html;
	}

	public function render() {
		if($this->on_before_render() !== false){
			return $this->filter_output($this->handle());
		}
	}

	/**
	 * If this returns null when method not found, then deault JSON error object
	 * is returned.  Otherwise, the string or object returned from not_found is returned.
	 * @return type 
	 */
	public function not_found() {
		return NULL;
	}

}

?>