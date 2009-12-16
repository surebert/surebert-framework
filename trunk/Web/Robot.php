<?php
/**
 * Loads a URL and allows you to pass post, cookie, get, user agent, and other header information through a simple OOP interface
 *
 * @author Paul Visco
 * @version 1.01 11/05/07 12/08/08
 * @package sb_Web
 * 
 */

class sb_Web_Robot{
	
	/**
	 * The HTTP transport method, default is get, sets topost when you use the add_post_data emthod
	 *
	 * @var string
	 */
	private $method = 'get';
	
	/**
	 * The url to fetch, you can set this directly or pass it to the fetch method
	 *
	 * @var string
	 */
	private $url = '';
	
	/**
	 * The user agent to simulate
	 *
	 * @var string e.g. Mozilla/4.0 (compatible;MSIE 6.0; Windows NT 5.1)
	 */
	private $user_agent = 'sb_Web_Robot';
	
	/**
	 * Cookie string creayed from passing an array to the add_cookie_data_method
	 *
	 * @var string
	 */
	private $cookies = '';
	
	/**
	 * Additional headers converted from an array in add_additional_headers
	 *
	 * @var string
	 */
	private $additional_headers = '';
	
	/**
	 * Additional query string variables used in request set by passing an array to add_get_data
	 *
	 * @var string
	 */
	private $query_string ='';

	/**
	 * Creates a sb_Web_Robot instance
	 * @param string $user_agent The user agent to use
	 * <code>
	 *  $loader = new sb_Web_Robot();
	 *  $loader->set_user_agent("Mozilla/4.0 (compatible;MSIE 6.0; Windows NT 5.1)");
	 *  $loader->set_post_data($_POST); //here I am forwarding the current pages $_POST data, could be any array
	 *  $loader->set_get_data(Array("friend"=>"tim"));
	 *  $loader->set_cookies(Array("name"=> "Paul"));
	 *  $loader->set_additional_headers(Array('Accept-Language' => 'en-us,en;q=0.5'));
	 *  $page = $loader->dispatch('http://200xx51/zhangyo/asp/ajax.validate.name.email.asp');
	 *  
	 *  //wanna turn xml into json? try this
	 *  $xml = simplexml_load_string($page);
	 *  echo json_encode($xml);
	 *  </code>
	 */
	public function __construct($user_agent = ''){
		$user_agent = !empty($user_agent) ? $user_agent : $this->user_agent;
		$this->set_user_agent($user_agent);
	}
	
	/**
	 * Add an array of data and url encode it before sending
	 *
	 * @param array $data
	 * @return the string of urlencoded data
	 */
	public function set_post_data($data){
		
		$this->method="post";
		$this->content = http_build_query($data);
		
		return $this->content;
		
	}
	
	/**
	 * Sets the content to a raw string
	 *
	 * @param string $content the raw content to send with the request
	 */
	public function set_content($content){
		
		$this->method="post";
		$this->content = $content;
	}
	
	/**
	 * Add an array of data and url encode it before sending
	 *
	 * @param array $data
	 * @return the string of urlencoded data
	 */
	public function set_get_data($data){
	
		$this->query_string = http_build_query($data);
		
		return $this->query_string;
	}
	
	/**
	 * Add an array of data and url encode it before sending
	 *
	 * @param array $data
	 * @return the string of urlencoded data
	 */
	public function set_cookies($data){
		$cookies = '';
		foreach($data as $key=>$val){
			$cookies  .= $key.'='.urlencode($val).';';
		}
		
		$this->cookies = "Cookie: ".$cookies."\r\n";
		
		return $this->cookies;
	}
	
	/**
	 * Add other additional headers
	 *
	 * @param array $data
	 * @return string
	 */
	public function add_additional_headers($data){
		
		$additional_headers = '';
		
		foreach($data as $key=>$val){
			$additional_headers  .= $key.': '.$val."\r\n";
		}
		
		$this->additional_headers = $additional_headers;
		
		return $this->additional_headers;
	}
	
	/**
	 * Sets the user agent sent with the http request
	 *
	 * @param string $user_agent The user agent to send e.g. 'pauls_web_bot'
	 */
	public function set_user_agent($user_agent){
		$this->user_agent = $user_agent;
	}
	
	/**
	 * Load the data from the url
	 *
	 * @param string $url
	 */
	public function dispatch($url='', $debug=0){
		
		
		if(empty($url)){
			throw(New Exception("You need to specify a url"));
		}

		$params = array('http' => array(
			'method' => strtoupper($this->method)
		));
		
		$headers = '';
		if(!empty($this->additional_headers)){
			$headers .= $this->additional_headers;
		}
		
		if(!empty($this->user_agent)){
			$headers.= "User-Agent: ".$this->user_agent."\r\n";
		}
		
		if(!empty($this->cookies)){
			$headers .= $this->cookies;
		}
		
		if(!empty($this->content)){
			
			$headers .= "Content-type: application/x-www-form-urlencoded\r\n".'Content-Length: ' . strlen($this->content) . "\r\n";
			$params['http']['content'] = $this->content;
		}
	
		$params['http']['header'] = $headers;
		
		$context = @stream_context_create($params);
		if(!empty($this->query_string)){
			$url = $url.'?'.$this->query_string;
		} 
		
		if($debug == 1){
			var_dump(Array("URL" => $url, 
			"Params" =>$params
			));
		}
	
		$response = @file_get_contents($url, false, $context);
		$this->response_headers = $http_response_header;
		
		return $response;
		
	}
}

?>