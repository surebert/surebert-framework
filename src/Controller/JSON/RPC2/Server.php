<?php

/**
 * Used to response to JSON_RPC2 requests as per the spec proposal at 
 * http://groups.google.com/group/json-rpc/web/json-rpc-1-2-proposal
 * @author paul.visco@roswellpark.org
 * @package JSON_RPC2
 *
 */
namespace sb\Controller\JSON\RPC2;

use \sb\Controller\Base;

class Server extends Base
{

    /**
     * The transport method to listen for data on
     * @var string post or get or both
     */
    public $method = 'both';

    /**
     * Determines if objects passed as params are converted to hashes instead of objects
     * @var boolean
     */
    public $json_decode_assoc_array = true;

    /**
     * If set to true then HTTP status headers 200, 400, 404, 500 are not send 
     * with response as some clients cannot parse 404 or 500 responses.
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
     * The gz encoding level to use when transferring data
     * @var integer
     */
    protected $gz_encode_level = false;

    /**
     * Create an instance, called by gateway
     */
    public function __construct()
    {

        if (isset($_SERVER) && isset($_SERVER['HTTP_PHP_SERIALIZE_RESPONSE'])) {
            $this->php_serialize_response = true;
        }
    }

    /**
     * Determines is gzencoding is used
     * @param $level Integer A number between 0-9, the compression level, higher takes longer
     */
    public function useGzEncoding($level)
    {
        $this->gz_encode_level = $level;
    }

    /**
     * Sets the key that data is encrypted with and turns on encryption, 
     * the client must use the same key
     * @param $key String
     */
    public function useEncryption($key)
    {
        $this->encryptor = new \sb\Encryption\ForTransmission($key);
        $this->encryption_key = $key;

        //decrypt cookies if sent
        foreach (\sb\Gateway::$cookie as $k => $v) {
            \sb\Gateway::$cookie[$k] = $this->encryptor->decrypt($v);
        }
    }

    /**
     * Get the methods available for this \sb\JSON\RPC2_Server instance
     * @return Array - Object once json_encoded
     * @servable true
     */
    public function getMethods($html = true)
    {

        $arr = Array();

        foreach (\get_class_methods($this) as $method) {

            $reflect = new \ReflectionMethod($this, $method);

            if ($reflect) {
                $docs = $reflect->getDocComment();
                $servable = false;
                if (!empty($docs)) {
                    if (\preg_match("~@servable (true|false)~", $docs, $match)) {
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
            return $this->methodsToHtml($arr);
        }
    }

    /**
     * Get php doc and return_type for method by name
     * @param string $name The name of the method to grab the docs for
     * @return Object with return_type and phpdoc property
     */
    protected function getPhpdoc($method)
    {

        if (\method_exists($this, $method)) {
            $reflect = new \ReflectionMethod($this, $method);
        } else {
            $response->error = new \sb\JSON\RPC2\Error();
            $response->error->code = -32602;
            $response->error->message = "Invalid method parameters";
            return $response;
        }

        $response = new \stdClass();
        $response->phpdoc = $reflect->getDocComment();

        if (\preg_match("~@return (.*?) (.*?)\*/$~s", $response->phpdoc, $match)) {
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
    protected function methodsToHtml($methods)
    {

        $html = '<style type="text/css">
            li{background-color:#c8c8d4;}
            
            h1{
            font-size:1.0em;
            padding:3px 0 3px 10px;
            color:white;
            background-color:#8181bd;
            }
            
            pre{
                color:#1d1d4d;
            }
            </style><ol>';
        foreach ($methods as $method => $comments) {
            $html .= '<li><h1>$server->' . $method . ';</h1><pre>' . "\t" . $comments . '</pre></li>';
        }

        $html .= '</ol>';

        return $html;
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
     * Parses the request
     * @param $json_request_str
     */
    protected function getResponse($json_request_str = '')
    {

        $response = new \sb\JSON\RPC2\Response();

        $request = null;

        if (empty($json_request_str)) {

            if (isset(Gateway::$cmd_options) && isset(Gateway::$cmd_options['json_request'])) {
                $json_request_str = Gateway::$cmd_options['json_request'];
            } elseif ($this->method == 'post' || $this->method == 'both') {
                $json_request_str = \file_get_contents("php://input");
            }

            if (is_null($request)
                    && ($this->method == 'get' || $this->method == 'both')
                    && (isset($this->request->get['method'])
                    && isset($this->request->get['params'])
                    && isset($this->request->get['id']))) {

                $request = new \sb\JSON\RPC2\Request();

                $request->id = $this->request->get['id'];
                $request->method = $this->request->get['method'];

                $params = $this->request->get['params'];

                if (!\preg_match("~[\[\{]~", \substr($params, 0, 1))) {

                    $params = \base64_decode($params);
                }

                $request->params = \json_decode($params);

                $json_request_str = \json_encode($request);
            }
        }

        if ($json_request_str) {
            if (isset($this->encryption_key)) {
                $json_request_str = $this->encryptor->decrypt($json_request_str);
            }
        }
        $request = new \sb\JSON\RPC2\Request($json_request_str);

        if (\is_null($request)) {
            $response->error = new \sb\JSON\RPC2\Error(-32700, 'Parse Error', "Data Received: " . $json_request_str);
        } else {
            $response->id = $request->id;
        }
        //log the incoming request
        $this->logRequest($json_request_str);

        $servable = false;

        if (\method_exists($this, $request->method)) {
            $reflection = new \ReflectionMethod($this, $request->method);

            //check for phpdocs
            $docs = $reflection->getDocComment();
            $servable = false;
            $non_rpc = false;
            if (!empty($docs)) {
                if (\preg_match("~@servable (true|false)~", $docs, $match)) {
                    $servable = $match[1] == 'true' ? true : false;
                }
            }
        }

        //check for requested remote procedure
        if ($servable) {

            if (\is_object($request->params)) {
                $answer = \call_user_func(Array($this, $request->method), $request->params);
            } else {
                if (!\is_array($request->params)) {
                    $request->params = Array();
                }
                $answer = \call_user_func_array(Array($this, $request->method), $request->params);
            }
            //if they return an error from the method call, return that
            if ($answer instanceof \sb\JSON\RPC2\Error) {
                $response->error = $answer;
            } else {
                //otherwise return the answer
                $response->result = $answer;
            }
        } else {
            if (isset($request->error) && $request->error instanceOf \sb\JSON\RPC2\Error) {
                $response->error = $request->error;
            } else {
                $response->error = new \sb\JSON\RPC2\Error();
                $response->error->code = -32601;
                $response->error->message = "Procedure not found";
            }
        }

        //remove unnecessary properties
        if ($response->error instanceof \sb\JSON\RPC2\Error) {

            unset($response->result);
            if (\is_null($response->error->data)) {
                unset($response->error->data);
            }
        } else {
            unset($response->error);
        }

        //log the final response
        $this->logResponse(json_encode($response));

        return $response;
    }

    /**
     * Serves data based on the json_request if set, otherwise based on
     *  Gateway::$cmd_options['json_request']
     * Had to remove default args being listed to prevent 
     * "should be compatible with that of" when using autoload, 
     * kept them in phpdoc to make it known which exist
     * 
     * @param $json_request_str String This optional argument can be used 
     * for debugging the server.  A \sb\JSON\RPC2_Request formatted JSON string 
     * e.g. {"method":"add","params":[1,2],"id":"abc123"}
     * @return string JSON encoded \sb\JSON\RPC2_Response
     */
    public function render()
    {
        $args = \func_get_args();

        $json_request_str = isset($args[0]) ? $args[0] : '';

        if ($this->onBeforeRender() !== false) {

            $response = $this->getResponse($json_request_str);

            $message = 'OK';
            $status = 200;
            //headers from spec here http://json-rpc.googlegroups.com/web/json-rpc-over-http.html
            if (isset($response->error) && $response->error instanceof \sb\JSON\RPC2\Error) {
                $code = $response->error->code;

                if (\in_array($code, Array(-32700, -3260, -32603))
                        || ($code <= -32000 && $code >= -32099)) {
                    $status = 500;
                    $message = 'Internal Server Error';
                } elseif ($code == -32600) {
                    $message = 'Bad Request';
                    $status = 400;
                } elseif ($code == -32601) {

                    $override = $this->not_found();
                    if (!\is_null($override)) {
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
                $json_response = \serialize($response);
            } else {
                $json_response = \json_encode($response);
            }

            if (!empty($this->encryption_key)) {
                $json_response = $this->encryptor->encrypt($json_response);
            }

            if ($this->gz_encode_level !== false) {
                $json_response = \gzencode($json_response, $this->gz_encode_level);
            }

            return $this->filter_output($json_response);
        }
    }

    /**
     * If this returns null when method not found, then deault JSON error object
     * is returned.  Otherwise, the string or object returned from not_found is returned.
     * Will also server get_methods/methods as HTML list of available calls
     * @return type 
     */
    public function notFound()
    {
        if (isset($this->request->path_array[1])) {
            switch ($this->request->path_array[1]) {
                case 'methods':
                    return $this->getMethods(true);
                    break;
            }
        }

        return null;
    }
}

