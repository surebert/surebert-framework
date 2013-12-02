<?php
/**
 * Models an incoming request's path and data e.g. /_surebert/custom
 * @author paul.visco@roswellpark.org
 * @package sb\Request
 *
 */

namespace sb;

class Request
    {

    /**
     * The path as a string eg. /surebert/load
     * @var string
     */
    public $path;

    /**
     * The path requested e.g. /user/dance
     * @var string
     */
    public $request;

    /**
     * The path as an array /view/action Array('view', 'action')
     * @var array
     */
    public $path_array = Array();

    /**
     * The framework path args in e.g. /view/action/1,2,3 it would be 1,2,3
     * @var array
     */
    public $args = Array();

    /**
     * The GET based input arguments from the request e.g. ?dog=cat as an array of key value pairs
     * @var array
     */
    public $get = Array();

    /**
     * The POST based input from the request - used to pass or similate $_POST
     * @var array
     */
    public $post = Array();

    /**
     * The PUT based input from the request
     * @var array
     */
    public $put = Array();
    
    /**
     * The DELETE based input from the request
     * @var array
     */
    public $delete = Array();
    
    /**
     * Any input from methods other than GET, POST, PUT, DELETE
     * @var array
     */
    public $data = Array();

    /**
     * The cookies with the request
     * @var array
     */
    public $cookie = Array();

    /**
     * A copy of the global $_FILES array, can be used to simulate file uploads
     * @var array
     */
    public $files = Array();

    /**
     * The method
     * @var string The request method
     */
    public $method = 'GET';

    /**
     * Creates a new request instance
     * @param $request The string request with args e.g. /_surebert/custom/strings.numPad
     */
    public function __construct($request)
    {

        if (preg_match("~\?(.*)$~", $request, $match)) {
            $request = preg_replace("~\?.*$~", '', $request);
            if (isset($match[1])) {
                parse_str($match[1], $this->get);
            }
        }

        if (method_exists('\App', 'filterAllInput')) {
            \App::filterAllInput($this->get);
        }

        $this->request = urldecode($request);

        $arr = explode("/", substr_replace($this->request , "", 0, 1));

        $this->path_array[0] = $arr[0];
        if (isset($arr[1]) && !empty($arr[1])) {
            $this->path_array[1] = $arr[1];
        }

        $this->path = "/" . implode("/", $this->path_array);

    }

    /**
     * Sets the input for the request
     * @param $post
     * @param $cookie
     * @param $files
     */
    public function setInput(&$post, &$cookie, &$files, &$put, &$delete, &$data)
    {

        $this->post = $post;
        $this->cookie = $cookie;
        $this->files = $files;
        $this->put = $put;
        $this->delete = $delete;
        $this->data = $data;
    }

    /**
     * Sets the input argument delimeter and parses it
     * @param $input_args_delimiter
     */
    public function setInputArgsDelimiter($input_args_delimiter)
    {

        //parse arguments by removing path
        $args = preg_replace("~^.{" . strlen($this->path) . "}/?~", "", $this->request);

        //remove $_GET string
        $args = preg_replace("~\?.*?$~", "", $args);

        if ($args !== '') {

            $this->args = explode($input_args_delimiter, $args);

            //decodes url encoding
            foreach ($this->args as &$arg) {
                $arg = urldecode($arg);
            }

            if (method_exists('\App', 'filterAllInput')) {
                \App::filterAllInput($this->args);
            }
        }
    }

    /**
     * Gets a GET variable value or returns the default value (null unless overridden)
     * @param string $key The $_GET var key to look for
     * @param mixed $default_val null by default
     * @return mixed string value or null
     */
    public function getGet($key, $default_val = null)
    {

        if (isset($this->get[$key])) {
            return $this->get[$key];
        }

        return $default_val;
    }

    /**
     * Gets a POST variable value or returns the default value (null unless overridden)
     * @param string $key The $_POST var key to look for
     * @param mixed $default_val null by default
     * @return mixed string value or null
     */
    public function getPost($key, $default_val = null)
    {
        if (isset($this->post[$key])) {
            return $this->post[$key];
        }

        return $default_val;
    }
    
    /**
     * Gets a non PUT, POST, DELETE, GET variable value or returns the default value (null unless overridden)
     * @param string $key The $_POST var key to look for
     * @param mixed $default_val null by default
     * @return mixed string value or null
     */
    public function getData($key, $default_val = null)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return $default_val;
    }
    
    /**
     * Gets a PUT variable value or returns the default value (null unless overridden)
     * @param string $key The $_POST var key to look for
     * @param mixed $default_val null by default
     * @return mixed string value or null
     */
    public function getPut($key, $default_val = null)
    {
        if (isset($this->put[$key])) {
            return $this->put[$key];
        }

        return $default_val;
    }
    
    /**
     * Gets a post variable value or returns the default value (null unless overridden)
     * @param string $key The $_POST var key to look for
     * @param mixed $default_val null by default
     * @return mixed string value or null
     */
    public function getDelete($key, $default_val = null)
    {
        if (isset($this->delete[$key])) {
            return $this->delete[$key];
        }

        return $default_val;
    }

    /**
     * Gets a cookie value if set, otherwise returns null
     *
     * @param string $key The key to look for
     * @return mixed the string value or null if not found
     */
    public function getCookie($key, $default_val = null)
    {
        if (isset($this->cookie[$key])) {
            return $this->cookie[$key];
        }

        return $default_val;
    }

    /**
     * Gets a get variable value or returns the default value (null unless overridden)
     * @param string $key The $_SESSION var key to look for
     * @param mixed $default_val null by default
     * @return mixed string value or null
     */
    public function getSession($key, $default_val = null)
    {

        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }

        return $default_val;
    }

    /**
     * Gets a args variable value or returns the default value (null unless overridden)
     * @param integer $arg_num The numeric arg value
     * @param mixed $default_val null by default
     * @return mixed string value or null
     */
    public function getArg($arg_num, $default_val = null)
    {
        if (isset($this->args[$arg_num])) {
            return $this->args[$arg_num];
        }

        return $default_val;
    }
    
    /**
     * Gets a uploaded file reference, otherwise returns null
     *
     * @param string $key The key to look for
     * @return array the file that was uploaded
     */
    public function getFile($key)
    {
        if (isset($this->files[$key])) {
            return $this->files[$key];
        }

        return null;
    }
    
    /**
     * Gets the method used to call the request
     * @return string e.g. GET, POST, PUT, DELETE
     */
    public function getMethod(){
        return $this->method;
    }
    
    /**
     * Sets the method used to call the request
     * @return string e.g. GET, POST, PUT, DELETE
     */
    public function setMethod($method){
        $this->method = $method;
    }

}
