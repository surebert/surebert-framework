<?php
/**
 * Loads a URL and allows you to pass post, cookie, get, user agent, and other header information through a simple OOP interface
 *
 * @author Paul Visco
 * @Version 1.01 11/05/07 12/08/08
 * @Example: 
 * <code>
$loader = new sb_Load_URL();
$loader->set_user_agent("Mozilla/4.0 (compatible;MSIE 6.0; Windows NT 5.1)"); 
$loader->add_post_data($_POST); //here I am forwarding the current pages $_POST data, could be any array
$loader->add_get_data(Array("friend"=>"tim"));
$loader->add_cookies(Array("name"=> "Paul"));
$loader->add_additional_headers(Array('Accept-Language' => 'en-us,en;q=0.5'));
$page = $loader->fetch('http://200xx51/zhangyo/asp/ajax.validate.name.email.asp');

//wanna turn xml into json? try this
$xml = simplexml_load_string($page);
echo json_encode($xml);
 * </code>
 * 
 */

class sb_Load_URL{
	
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
	private $user_agent = '';
	
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
	 * Add an array of data and url encode it before sending
	 *
	 * @param array $data
	 * @return the string of urlencoded data
	 */
	public function add_post_data($data){
		$this->method="post";
		
		$this->content = http_build_query($data);
		
		return $this->content;
	}
	
	/**
	 * Add an array of data and url encode it before sending
	 *
	 * @param array $data
	 * @return the string of urlencoded data
	 */
	public function add_get_data($data){
	
		$this->query_string = http_build_query($data);
		
		return $this->query_string;
	}
	
	/**
	 * Add an array of data and url encode it before sending
	 *
	 * @param array $data
	 * @return the string of urlencoded data
	 */
	public function add_cookies($data){
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
	 * @param string $agent
	 */
	public function set_user_agent($agent){
		$this->additional_headers .= "User-Agent: ".$agent."\r\n";
	}
	
	/**
	 * Load the data from the url
	 *
	 * @param string $url
	 */
	public function fetch($url='', $debug=0){
		
		
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
		
		if($debug ==1){
			
			return "url: ".$url."\n\nParams: ".print_r($params, 1);
		}
	
		$response = @file_get_contents($url, false, $context);
		if ($response === false) {throw new Exception("Problem reading data from $this->url");}
		return $response;
		
	}
}

?>