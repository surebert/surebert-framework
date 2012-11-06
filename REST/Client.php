<?php

/**
 * A CURL based REST client for fetching data from REST services
 * @author paul.visco@roswellpark.org
 * 
 * <code>
 * $client = new \sb\REST\Client('https://some_site/api_content', Array(
  'on_http_error' => function($status, $message){
  var_dump(func_get_args());
  }
  ));
 * $response = $client->get(Array('ticket' => '49d75185-e71b-42f1-9298-ac0382dd2e26'));
 * </code>
 *  
 */
namespace sb\REST;

class Client
{

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
     * Auth credentials to pass to the request
     * @var array  ('type' => 'basic', 'uname' => 'somebody', 'pass' => 'somepass');
     */
    protected $authentication = Array();

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
    public function __construct($url = '', $default_settings = Array())
    {
        $this->url = $url;
        $this->setDefaultArguments($default_settings);
    }

    /**
     * Fire get request to fetch data from the REST service
     * @param type $data Query string vars to send
     * @param array $settings settings to override the default properties of 
     * follow_location, verify_ssl, return_transfer, debug, cookie_file, user_agent, timeout,  on_http_error, on_headers, on_body
     * @return type 
     */
    public function get($data = '', $settings = Array())
    {
        return $this->processCurl('GET', $data, $settings);
    }

    /**
     * Fire get request to fetch data from the REST service
     * @param type $data POST data to send
     * @param array $settings settings to override the default properties of 
     * follow_location, verify_ssl, return_transfer, debug, cookie_file, user_agent, timeout,  on_http_error, on_headers, on_body
     * @return type 
     */
    public function post($data = '', $settings = Array())
    {
        return $this->processCurl('POST', $data, $settings);
    }

    /**
     * Fire get request to fetch data from the REST service
     * @param type $data DELETE data to send
     * @param array $settings settings to override the default properties of 
     * follow_location, verify_ssl, return_transfer, debug, cookie_file, user_agent, timeout,  on_http_error, on_headers, on_body
     * @return type 
     */
    public function delete($data = '', $settings = Array())
    {
        return $this->processCurl('DELETE', $data, $settings);
    }

    /**
     * Fire get request to fetch data from the REST service
     * @param type $data PUT data to send
     * @param array $settings settings to override the default properties of 
     * follow_location, verify_ssl, return_transfer, debug, cookie_file, user_agent, timeout,  on_http_error, on_headers, on_body
     * @return type 
     */
    public function put($data = '', $settings = Array())
    {
        return $this->processCurl('PUT', $data, $settings);
    }

    /**
     * Overrides the default settings for all requests
     * @param array $default_settings settings to override the default properties of 
     * follow_location, verify_ssl, return_transfer, debug, cookie_file, user_agent, timeout,  on_http_error, on_headers, on_body
     */
    public function setDefaultArguments($default_settings)
    {
        foreach ($default_settings as $setting => $val) {
            if (property_exists(get_class(), $setting)) {
                $this->$setting = $val;
            }
        }
    }

    /**
     * Sets the authentication type used
     * @param string $uname The username
     * @param string $pass The password
     * @param string $type The auth type basic, ntlm, digest 
     */
    public function setAuthentication($uname = '', $pass = '', $type = 'basic')
    {
        $this->authentication['uname'] = $uname;
        $this->authentication['pass'] = $pass;
        $this->authentication['type'] = $type;
    }

    /**
     * Sets the timeout for the request
     * @param int $timeout defaults to 30 seconds
     */
    public function setTimeout($timeout = 30)
    {
        $this->timeout = is_int($timeout) ? $timeout : 30;
    }

    /**
     * Sets any headers to send with the request
     * @param array $headers
     */
    public function setHeaders($headers = Array())
    {
        $this->headers = is_array($headers) ? $headers : Array();
    }

    /**
     * Sets the user agent for the request
     * @param string $agent
     */
    public function setUserAgent($agent = 'sb_REST_Client')
    {
        $this->agent = $agent;
    }

    /**
     * File path to the cookie file
     * @param string $cookie_file
     */
    public function setCookieFile($cookie_file)
    {
        $this->cookie_file = $cookie_file;
    }

    /**
     * Sets the debug level to use
     * @param int $debug
     */
    public function setDebugState($debug = 2)
    {
        $this->debug = $debug;
    }

    /**
     * Sets callbacks for the events
     * @param string $event on_error, on_http_error, on_body and on_headers events
     * @param Callable $callable any function to parse the data
     */
    public function setCallback($event, Callable $callable)
    {
        $this->$event = $callable;
    }

    /**
     * Gets an array of all of the current settings
     * @return array 
     */
    public function getSettings()
    {
        return get_object_vars($this);
    }

    /**
     * Passes the request of to CURL for processing
     * 
     * @param string $method The type of method to send it with, POST, GET, PUT, DELETE
     * @param array $data The data to send
     * @param array $override_settings settings to override the default properties of 
     * follow_location, verify_ssl, return_transfer, debug, cookie_file, user_agent, timeout, on_http_error, on_headers, on_body
     */
    protected function processCurl($method, $data, $override_settings = Array())
    {

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

        if (is_array($settings['authentication']) && count($settings['authentication'])) {
            $auth_type = isset($settings['authentication']['type']) ? $settings['authentication']['type'] : 'basic';

            switch ($auth_type) {
                case 'ntlm':
                    $auth_type = CURLAUTH_NTLM;
                    break;
                case 'digest':
                    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_NTLM);
                    $auth_type = CURLAUTH_DIGEST;
                    break;
                case 'any':
                    $auth_type = CURLAUTH_ANY;
                case 'basic':
                default:
                    $auth_type = CURLAUTH_BASIC;
                    break;
            }

            curl_setopt($ch, CURLOPT_HTTPAUTH, $auth_type);
            $uname = isset($settings['authentication']['uname']) ? $settings['authentication']['uname'] : '';
            $pass = isset($settings['authentication']['pass']) ? $settings['authentication']['pass'] : '';
            curl_setopt($ch, CURLOPT_USERPWD, $uname . ':' . $pass);
        }

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
                        $settings['on_headers']($data);
                    }

                    if (preg_match("~^HTTP/\d+\.\d+\s(\d+)\s(.*?)[\r\n]~", $data, $match) && !in_array($match[1], Array(100, 200))) {
                        if (is_callable($settings['on_http_error'])) {
                            $settings['on_http_error']($match[1], $match[2]);
                        }
                    }
                }

                return strlen($data);
            });

        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $data) use($settings) {

                if (is_callable($settings['on_body'])) {
                    $settings['on_body']($data);
                }

                return strlen($data);
            });

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

