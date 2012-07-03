<?php

/**
 * A CURL based REST client for fetching data from REST services
 * @author Paul.Visco@roswellpark.org
 * 
 * <code>
 * $client = new sb_REST_Client('https://some_site/api_content', Array(
  'on_http_error' => function($status, $message){
  var_dump(func_get_args());
  }
  ));
 * $response = $x->get(Array('ticket' => '49d75185-e71b-42f1-9298-ac0382dd2e26'));
 * </code>
 *  
 */
class sb_REST_Client {

	/**
	 * The URL being fetched
	 * @var string
	 */
	protected $url;

	/**
	 * Should we follow redirects
	 * @var boolean true
	 */
	protected $follow_location = true;

	/**
	 * Should we verify SSL certs
	 * @var boolean false
	 */
	protected $verify_ssl = false;

	/**
	 * Should we return transfer
	 * @var true
	 */
	protected $return_transfer = true;

	/**
	 * debug curl output to stdout
	 * @var false
	 */
	protected $debug = false;

	/**
	 * The cookie file path to read from, write to if needed
	 * @var string
	 */
	protected $cookie_file = '';

	/**
	 * The user agent to send with the request
	 * @var string
	 */
	protected $user_agent = 'sb_REST_Client';

	/**
	 * The default timeout for connections
	 * @var int 
	 */
	protected $timeout = 30;

	/**
	 * An array of key value pairs that are sent as HTTP headers of the request
	 * @var array e.g. 'Content-Type' => 'application/xml' 
	 */
	protected $headers = Array();

	/**
	 * The callable to call when there is a non 100, 200 HTTP header
	 * Receives two arguments $status and $message
	 * @var callable
	 */
	protected $on_http_error = false;

	/**
	 * The callable that fires when headers arrive
	 * Receives one argument $data
	 * @var callable
	 */
	protected $on_headers = false;

	/**
	 * The callable that fires when the content body arrives
	 * 
	 * Receives one argument $data.
	 * @var callable
	 */
	protected $on_body = false;

	/**
	 * The callable that fires when their is a curl error such as no network connection, domain not found, etc
	 * 
	 * Receives two arguments $error_num, $error_str.  Errors are listed here http://curl.haxx.se/libcurl/c/libcurl-errors.html
	 * @var callable
	 */
	protected $on_error = false;

	/**
	 * Constructs a new client
	 * @param type $url The base url for the server e.g. http://something.com/api
	 * @param array $default_settings settings to override the default properties of 
	 * follow_location, verify_ssl, return_transfer, debug, cookie_file, user_agent, timeout,  on_http_error, on_headers, on_body
	 */
	public function __construct($url = '', $default_settings = Array()) {
		$this->url = $url;
		$this->set_default_settings($default_settings);
	}

	/**
	 * Fire get request to fetch data from the REST service
	 * @param type $data Query string vars to send
	 * @param array $settings settings to override the default properties of 
	 * follow_location, verify_ssl, return_transfer, debug, cookie_file, user_agent, timeout,  on_http_error, on_headers, on_body
	 * @return type 
	 */
	public function get($data, $settings = Array()) {
		return $this->process_curl('GET', $data, $settings);
	}

	/**
	 * Fire get request to fetch data from the REST service
	 * @param type $data POST data to send
	 * @param array $settings settings to override the default properties of 
	 * follow_location, verify_ssl, return_transfer, debug, cookie_file, user_agent, timeout,  on_http_error, on_headers, on_body
	 * @return type 
	 */
	public function post($data, $settings = Array()) {
		return $this->process_curl('POST', $data, $settings);
	}

	/**
	 * Fire get request to fetch data from the REST service
	 * @param type $data DELETE data to send
	 * @param array $settings settings to override the default properties of 
	 * follow_location, verify_ssl, return_transfer, debug, cookie_file, user_agent, timeout,  on_http_error, on_headers, on_body
	 * @return type 
	 */
	public function delete($data, $settings = Array()) {
		return $this->process_curl('DELETE', $data, $settings);
	}

	/**
	 * Fire get request to fetch data from the REST service
	 * @param type $data PUT data to send
	 * @param array $settings settings to override the default properties of 
	 * follow_location, verify_ssl, return_transfer, debug, cookie_file, user_agent, timeout,  on_http_error, on_headers, on_body
	 * @return type 
	 */
	public function put($data, $settings = Array()) {
		return $this->process_curl('PUT', $data, $settings);
	}

	/**
	 * Overrides the default settings for all requests
	 * @param array $default_settings settings to override the default properties of 
	 * follow_location, verify_ssl, return_transfer, debug, cookie_file, user_agent, timeout,  on_http_error, on_headers, on_body
	 */
	public function set_default_settings($default_settings) {
		foreach ($default_settings as $setting => $val) {
			if (property_exists(get_class(), $setting)) {
				$this->$setting = $val;
			}
		}
	}

	/**
	 * Passes the request of to CURL for processing
	 * @param string $method The type of method to send it with, POST, GET, PUT, DELETE
	 * @param array $data The data to send
	 * @param array $override_settings settings to override the default properties of 
	 * follow_location, verify_ssl, return_transfer, debug, cookie_file, user_agent, timeout, on_http_error, on_headers, on_body
	 */
	protected function process_curl($method, $data, $override_settings = Array()) {

		$settings = get_object_vars($this);
		foreach ($override_settings as $setting => $val) {
			$settings[$setting] = $val;
		}

		$url = $settings['url'];

		if ($method == 'GET' && is_array($data)) {
			$url .='?' . http_build_query($data);
		}

		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, $settings['return_transfer'] ? TRUE : FALSE);
		curl_setopt($ch, CURLOPT_USERAGENT, $settings['user_agent']);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $settings['follow_location'] ? TRUE : FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $settings['verify_ssl'] ? TRUE : FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $settings['verify_ssl'] ? TRUE : FALSE);
		curl_setopt($ch, CURLOPT_TIMEOUT, is_int($settings['debug']) ? $settings['debug'] : 30);

		if (is_array($settings['headers'])) {
			foreach ($settings['headers'] as $key => $val) {
				curl_setopt($ch, CURLOPT_HTTPHEADER, array($key . ": " . $val));
			}
		}

		if ($settings['debug']) {
			curl_setopt($ch, CURLOPT_VERBOSE, $settings['debug'] ? TRUE : FALSE);
		}

		if (!empty($settings['cookie_file'])) {
			curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
		}

		if ($method != 'GET') {
			if ($method == 'POST') {
				curl_setopt($ch, CURLOPT_POST, 1);
			} else {
				$data = is_array($data) ? http_build_query($data) : $data;
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Length: ' . strlen($data)));
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
			}
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}

		curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($ch, $data) use ($settings) {
			if (!is_null($data)) {

				if (is_callable($settings['on_headers'])) {
					if ($settings['on_headers']($data) === false) {
						return false;
					}
				}

				if (preg_match("~^HTTP/\d+\.\d+\s(\d+)\s(.*?)[\r\n]~", $data, $match) && !in_array($match[1], Array(100, 200))) {
					if (is_callable($settings['on_http_error'])) {
						return $settings['on_http_error']($match[1], $match[2]);
					} else {
						throw(new Exception($data));
						return false;
					}
				}

				return strlen($data);
			}
		});
				
		if (is_callable($settings['on_body'])) {
			curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $data) use($settings) {

				if (!is_null($data)) {
					$settings['on_body']($data);
				}

				return strlen($data);
			});
		}
		
		$response = curl_exec($ch);
		
		if ($response === false) {
			$error = curl_error($ch);
			$error_no = curl_errno($ch);
			if (is_callable($settings['on_error'])) {
				return $settings['on_error']($error_no, $error);
			}

			return $error_no . ' ' . $error;
		}
		
		return $response;
	}

}

?>