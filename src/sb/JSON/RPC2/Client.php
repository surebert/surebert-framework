<?php

/**
 * The JSON_RPC2_Client used to send the request
 *
 * @author paul.visco@roswellpark.org
 * @package JSON_RPC2
 */
namespace sb\JSON\RPC2;

class Client
{

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
    public $agent = '';

    /**
     * The \sb\_JSON\RPC2\Request to dispatch
     * @var \sb\JSON\RPC2\Request
     */
    protected $request;
    
    /**
     * Determines if CURL verifys host/ssl
     * @var boolean 
     */
    protected $verify_ssl = true;

    /**
     * Creates an instance of \sb\JSON\RPC2\Client
     *
     * <code>
     * $client = new \sb\JSON\RPC2\Client('http://service.roswellpark.org/my/service');
     *
     * $x = $client->add(1,2);
     *
     * var_dump($response);
     * </code>
     *
     * @param $url String The url of the server
     * @param $timeout The time to wait for a response in seconds for curl to open a connection. Default is 3 seconds
     * @param $port Integer The port to make the request on
     * @param $request_timeout integer The timeout of the curl request execution. Default is 30 seconds
     * @return \sb\JSON\RPC2\Response
     */
    public function __construct($url, $timeout = 3, $port = null, $request_timeout = 30)
    {

        $data = parse_url($url);

        if (!is_null($port)) {
            $this->port = $port;
        } else {
            $this->port = $data['scheme'] == 'https' ? 443 : 80;
        }

        $this->host = $data['host'];
        $this->uri = $data['path'];

        $this->timeout = $timeout;
        $this->request_timeout = $request_timeout;
    }

    /**
     * method you can use to log the json request
     * @param string $json_request The input json
     */
    protected function logRequest($json_request)
    {

    }

    /**
     * method you can use to log the json response
     * @param string $json_request The output json
     */
    protected function logResponse($json_response)
    {

    }

    /**
     * Sets the key that data is encrypted with and turns on encryption, the server must use the same key
     * @param $key String
     */
    public function useEncryption($key)
    {
        $this->encryptor = new \sb\Encryption\ForTransmission($key);
        $this->encryption_key = $key;
    }

    /**
     * Adds a cookie to send to the server
     * @param $cookie Array('key' => 'val');
     */
    public function addCookie($cookie = Array())
    {
        foreach ($cookie as $key => $val) {
            if (isset($this->encryption_key)) {
                $val = $this->encryptor->encrypt($val);
            }

            $this->cookies[$key] = $val;
        }
    }

    /**
     * Dispatches a \sb\JSON\RPC2\Request
     * @param $request \sb\JSON\RPC2\Request An object instance that models that request
     * @return \sb\JSON\RPC2\Response
     */
    public function dispatch(\sb\JSON\RPC2\Request $request)
    {

        if (!(is_array($request->params) || is_object($request->params))) {
            $response = new \sb\JSON\RPC2\Response();
            $response->error = new \sb\JSON\RPC2\Error('-32602');
            $response->message = 'Invalid params';
            $response->error->data = 'Invalid method parameters: ' . json_encode($request->params);
            return $response;
        }

        $host = $this->host;
        $port = $this->port;
        $timeout = $this->timeout;
        $request_timeout = $this->request_timeout;
        $uri = $this->uri;

        $json = json_encode($request);

        $this->logRequest($json);

        if ($this->debug == true) {
            echo "--> " . $json;
        }

        $headers = [];
        
        if ($this->php_serialize_response) {
            $headers[] = "php-serialize-response:".($this->php_serialize_response ? 1 : 0);
        }

        //if there are cookies add them
        if (!empty($this->cookies)) {
            $cookies = '';
            foreach ($this->cookies as $key => $val) {
                $cookies .= $key . '=' . urlencode($val) . ';';
            }

            $headers[] = "Cookie: " . $cookies;
        }

        $response_str = '';

        $ch = curl_init();
        if($headers){
            curl_setopt($ch,CURLOPT_HTTPHEADER, $headers); 
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->verify_ssl ? 2 : false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->verify_ssl ? 2 : false);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->agent ? $this->agent : \sb\Gateway::$http_host.' '.'\sb\JSON\RPC2\Client ');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout); 
        curl_setopt($ch, CURLOPT_TIMEOUT, $request_timeout); 
        curl_setopt($ch, CURLOPT_PORT, $port);
        
        $url = 'http'.($this->port == 443 ? 's' : '').'://'.$this->host.$uri;
        if($this->method == 'post'){
            if (isset($this->encryption_key)) {
                $json = $this->encryptor->encrypt($json);
            }
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        } else {
            
            $params = base64_encode(json_encode($request->params));
            $params = urlencode($params);

            $url .= (strstr($this->uri, '?') ? '&' : '?')
                . 'method=' . $request->method . '&params='
                . $params . '&id=' . $request->id;
    
        }
        
        curl_setopt ($ch, CURLOPT_URL, $url);  
       
        $response_str = curl_exec($ch);
        $error = curl_errno($ch);
        if($error){
            $response = new \sb\JSON\RPC2\Response();
            $response->error = new \sb\JSON\RPC2\Error('-32099');
            $response->error->message = 'Server error';
            $response->error->data = 'Could not reach: ' . $this->host . ": #$error - ". curl_error($ch);
            return $response;
        }
        
        return $this->processResponse($response_str);
    }

    /**
     * Break down the received data into headers and response and then handle gz encoding, encryption, utf, etc
     * @param $str  The data returned from the socket connection
     * @return string The body of the message
     */
    protected function processResponse($str)
    {

        if (!empty($this->encryption_key)) {
            $str = $this->encryptor->decrypt($str);
        }

        $this->logResponse($str);

        //check if response body is serialized json_response object and just unserialize and return if it is
        if ($this->php_serialize_response && !empty($str)) {

            try {
                $serialized = \unserialize($str);
                if ($serialized !== false) {
                    $response = $serialized;
                }
            } catch (\Exception $e) {

                if ($this->debug) {
                    echo $body;
                }
            }
        }

        //Not sure about this?
        $str = \utf8_encode($str);

        if (!isset($response)) {
            $response = new \sb\JSON\RPC2\Response($str);
        }

        return $response;
    }

    /**
     * Calls the remote procedure (method) as though it was a method of the client itself
     * @param $method
     * @param $args
     * @return mixed
     */
    public function __call($method, $args)
    {

        $request = new \sb\JSON\RPC2\Request();
        $request->method = $method;

        $request->params = isset($args) ? $args : Array();

        $request->id = \uniqid();

        if (isset($args['debug'])) {
            $this->debug = true;
        }

        $response = $this->dispatch($request);
        
        if (is_object($response)) {
            if (isset($response->error)) {
                throw(new \Exception($response->error->code . ': '
                    . $response->error->message . ".\nData Received: "
                    . (isset($response->error->data) ?
                        $response->error->data : 'NONE'
                    )));
            } else {
                return $response->result;
            }
        } else {
            return $response;
        }
    }

}