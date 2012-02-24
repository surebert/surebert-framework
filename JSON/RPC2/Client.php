<?php
/**
 * The JSON_RPC2_Client used to send the request
 *
 * @author visco
 * @package sb_JSON_RPC2
 */

class sb_JSON_RPC2_Client {

	/**
	 * Determines if data is debugged to the output
	 * @var boolean
	 */
	public $debug = false;

	/**
	 * The transport method, eithe rpost or get - post is preferred
	 * @var string
	 */
	public $method = 'post';

	/**
	 * Cookies to send with the client requests
	 * @var array
	 */
	public $cookies = Array();

	/**
	 * Determines if the responsed in serialized in php format to preserve data form.
	 * e.g. hashes stay hashes and are not converted to objects
	 * @var boolean
	 */
	public $php_serialize_response = true;

	/**
	 * The user agent to send with the request
	 * @var string
	 */
	public $agent = 'sb_JSON_RPC2_Client';

	/**
	 * The sb_JSON_RPC2_Request to dispatch
	 * @var sb_JSON_RPC2_Request
	 */
	protected $request;

	/**
	 * Creates an instance of sb_JSON_RPC2_Client
	 *
	 * <code>
	 * $client = new sb_JSON_RPC2_Client('http://service.roswellpark.org/my/service');
	 * 
	 * $x = $client->add(1,2);
	 *
	 * var_dump($response);
	 * </code>
	 * 
	 * @param $url String The url of the server
	 * @param $timeout The time to wait for a response in seconds
	 * @param $port Integer The port to make the request on
	 * @return sb_JSON_RPC2_Response
	 */
	public function __construct($url, $timeout=1, $port=null) {
		
		$data = parse_url($url);

		if(!is_null($port)){
			$this->port = $port;
		} else {
			$this->port = $data['scheme'] == 'https' ? 443 : 80;
		}
		
		$this->host = $data['host'];
		$this->uri = $data['path'];

		$this->timeout = $timeout;
	}

	/**
	 * method you can use to log the json request
	 * @param string $json_request The input json
	 */
	protected function log_request($json_request){}
	
	/**
	 * method you can use to log the json response
	 * @param string $json_request The output json
	 */
	protected function log_response($json_response){}

	/**
	 * Sets the key that data is encrypted with and turns on encryption, the server must use the same key
	 * @param $key String
	 */
	public function use_encryption($key) {
		$this->encryptor = new sb_Encryption_ForTransmission($key);
		$this->encryption_key = $key;
	}

	/**
	 * Adds a cookie to send to the server
	 * @param $cookie Array('key' => 'val');
	 */
	public function add_cookie($cookie = Array()) {
		foreach($cookie as $key=>$val) {
			if(isset($this->encryption_key)) {
				$val = $this->encryptor->encrypt($val);
			}

			$this->cookies[$key] = $val;
		}
	}

	/**
	 * Dispatches a sb_JSON_RPC2_Request
	 * @param $request sb_JSON_RPC2_Request An object instance that models that request
	 * @return sb_JSON_RPC2_Response
	 */
	public function dispatch(sb_JSON_RPC2_Request $request) {
	
			if(!(is_array($request->params) || is_object($request->params))) {
				$response = new sb_JSON_RPC2_Response();
				$response->error = new sb_JSON_RPC2_Error('-32602');
				$response->message = 'Invalid params';
				$response->error->data = 'Invalid method parameters: '.json_encode($request->params);
				return $response;
			}
			
			$host = $this->host;
			$port = $this->port;
			$timeout = $this->timeout;
			$uri = $this->uri;

			$json = json_encode($request);

			$this->log_request($json);

			if($this->debug == true) {
				echo "--> ".$json;
			}

			if($this->method == 'post') {
				if(isset($this->encryption_key)) {
					$json = $this->encryptor->encrypt($json);
				}
				$content_length = 'Content-Length: ' . strlen($json);
			} else {
				$params = base64_encode(json_encode($request->params));
				$params = urlencode($params);

				$uri  .= (strstr($this->uri, '?') ? '&' : '?').'method='.$request->method.'&params='.$params.'&id='.$request->id;
			}

			$out = Array();
			$out[] = strtoupper($this->method)." ".$uri." HTTP/1.1";
			$out[] = 'Host: '.$this->host;

			if(isset($content_length)) {
				$out[] = $content_length;
			}
			$out[] = "User Agent: ".$this->agent;

			if($this->php_serialize_response) {
				$out[] = "Php_Serialize_Response: ".$this->php_serialize_response;
			}

			//if there are cookies add them
			if(!empty($this->cookies)) {
				$cookies = '';
				foreach($this->cookies as $key=>$val) {
					$cookies  .= $key.'='.urlencode($val).';';
				}

				$out[] = "Cookie: ".$cookies;
			}

			$out[] = 'Connection: close';

			$response_str = '';

			if($this->port == 443){
				$host = 'ssl://'.$this->host;
			}
			$fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
			if (!$fp || !(get_resource_type($fp) == 'stream')) {
				$response = new sb_JSON_RPC2_Response();
				$response->error = new sb_JSON_RPC2_Error('-32099');
				$response->error->message = 'Server error';
				$response->error->data = 'Could not reach: '.$this->host.": #$errno - $errstr";
				return $response;
			}

			$data = implode("\r\n", $out) . "\r\n\r\n" . $json;

			fputs($fp, $data);

			while(!feof($fp)) {
				$response_str .= fread($fp, 8192);
			}
			fclose($fp);

			return $this->process_response($response_str);
	}

	/**
	 * Break down the received data into headers and response and then handle gz encoding, encryption, utf, etc
	 * @param $str  The data returned from the socket connection
	 * @return string The body of the message
	 */
	protected function process_response($str) {

		$marker = strpos($str, "\r\n\r\n")+4;
		$headers =  substr($str, 0, $marker);
		$body = substr($str, $marker);

		if($this->debug == true) {
			echo "\n<--".$body;
		}

		if(stristr($headers, "Transfer-Encoding: chunked")) {
			$body = $this->unchunk_data($body);
		}

		//ungzip the content
		if(substr($body,0,3)=="\x1f\x8b\x08") {
			$body = $this->gzdecode($body);
		}
		
		if(!empty($this->encryption_key)) {
			$body = $this->encryptor->decrypt($body);
		}

		$this->log_response($body);
		
		//check if response body is serialized json_response object and just unserialize and return if it is
		if($this->php_serialize_response && !empty($body)) {

			try{
				$serialized = @unserialize($body);
				if($serialized !== false){
					$response = $serialized;
				}
			} catch(Exception $e){

				if($this->debug){
					echo $body;
				}
				
			}
		}

		//Not sure about this?
		$body = utf8_encode($body);
		
		if(!isset($response)){
			$response = new sb_JSON_RPC2_Response($body);
		}

		return $response;
	}

	/**
	 * Calls the remote procedure (method) as though it was a method of the client itself
	 * @param $method
	 * @param $args
	 * @return mixed
	 */
	public function __call($method, $args) {

		$request = new sb_JSON_RPC2_Request();
		$request->method = $method;

		$request->params = isset($args) ? $args : Array();

		$request->id = uniqid();

		if(isset($args['debug'])){
			$this->debug = true;
		}

		$response = $this->dispatch($request);

		if($response instanceof sb_JSON_RPC2_Response){
			if(isset($response->error)) {
				throw(new Exception($response->error->code.': '.$response->error->message.".\nData Received: ".(isset($response->error->data) ? $response->error->data : 'NONE')));
			} else {
				return $response->result;
			}
		} else {
			return $response;
		}
	}

	/**
	 * gzdecodes the data, PHP 6 will have this natievly until then, taken from from http://www.tellinya.com/read/2007/08/28/83.html coming natively in php 6
	 * @param $data gzencoded string
	 * @return string
	 */
	protected function gzdecode ($data) {

		$flags = ord(substr($data, 3, 1));
		$headerlen = 10;
		$extralen = 0;
		$filenamelen = 0;

		if ($flags & 4) {
			$extralen = unpack('v' ,substr($data, 10, 2));
			$extralen = $extralen[1];
			$headerlen += 2 + $extralen;
		}
		// Filename
		if ($flags & 8) {
			$headerlen = strpos($data, chr(0), $headerlen) + 1;
		}

		// Comment
		if ($flags & 16) {
			$headerlen = strpos($data, chr(0), $headerlen) + 1;
		}

		// CRC at end of file
		if ($flags & 2) {
			$headerlen += 2;
		}

		$unpacked = gzinflate(substr($data, $headerlen));
		if ($unpacked === FALSE)
			$unpacked = $data;
		return $unpacked;
	}

	/**
	 * This handles content encoding chunked for HTTP 1.1, taken from php.net fsockopen manual
	 * @param $str
	 * @return string
	 */
	private function unchunk_data($str) {

		if (!is_string($str) or strlen($str) < 1) { return false; }

		$eol = "\r\n";
		$add = strlen($eol);
		$tmp = $str;
		$str = '';

		do {
			$tmp = ltrim($tmp);
			$pos = strpos($tmp, $eol);
			if ($pos === false) {
				return false;
			}
			$len = hexdec(substr($tmp,0,$pos));
			if (!is_numeric($len) or $len < 0) {
				return false;
			}
			$str .= substr($tmp, ($pos + $add), $len);
			$tmp  = substr($tmp, ($len + $pos + $add));
			$check = trim($tmp);
		} while(!empty($check));

		unset($tmp);
		return $str;
	}

}

?>