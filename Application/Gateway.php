<?php
/**
 * Initializes a surebert framework project - do not edit
 *
 * @author Paul Visco
 * @version 10-01-2008 11-21-2011
 * @package Gateway
 *
 */
#####################################################################
##### DO NOT TOUCH THIS FILE UNLESS YOU KNOW WHAT YOU ARE DOING #####
#####################################################################

ob_start();

//script start time
$sb_start = microtime(true);

/**
 * A special type of model that takes in requests and returns data directly
 */
interface sb_Magic_Model {

    /**
     * Filter the output before it is passed back to the Gateway
     * @param string $out
     */
    public function filter_output($out);
}

/**
 * Loads the .view file that corresponds to the request
 * @author paul.visco@roswellpark.org
 * @package sb_Controller
 */
class sb_Controller {

	/**
     * Set to true if the view is loaded from within another view via Gateway::render_request, otherwise false
     *
     * @var boolean
     */
    public $included = false;
    /**
     * The requested data and input
     * @var sb_Request
     */
    public $request;
	
    /**
     * The argument delimeter e.g. the comma in: /view/action/1,2,3
     * @var string
     */
    protected $input_args_delimiter = '/';
	
	/**
	 * Determines if the controller's public properties become
	 * local vars in the views or not
	 * @var boolean 
	 */
    protected $extract = false;
	
    /**
     * Left in for backwards compat with v1.0
     * @todo depreciate this and use $this->request->args instead
     * @var array
     */
    protected $args;
    /**
     * Determines which file is loaded in the view directory if one is not specified.  When not set renders index.view.  To set just use template name minus the .view extension e.g. $this->default-file = index;
     *
     * @var string
     */
    protected $default_file = 'index';

    /**
     * Filters the output after the view is rendered but before
     * it is displayed so that you can filter the output
     *
     * @param $output
     * @return string
     */
    public function filter_output($output) {
        return $output;
    }

    /**
     * Sets the request paramter
     *
     * @param sb_Request $request The request instance fed to the view
     */
    final public function set_request(sb_Request $request) {

        $this->request = $request;

        $request->set_input_args_delimiter($this->input_args_delimiter);

        /**
         * Hold over for backward compatibility
         * @var array
         * @todo depreciate this
         */
        $this->args = $request->args;
    }

    /**
     * Fires before the .view template is rendered allowing you to make decisions, check input args, etc before the output is rendered.
     * If you return false from this method then no output is rendered.
     * @return boolean determines if the view should render anything or not, false == no render
     */
    public function on_before_render($method='') {
        return true;
    }

    /**
     * Render the view from the template file based on the request
     *
     * @param String $template the template to use e.g. /dance
     * @param mixed $extact_vars extracts the keys of an object or array into
	 * local variables in the view
	 */
    public function render($template='', $extract_vars=null) {

        $output = '';

        //capture view to buffer
        ob_start();

        if (get_class($this) == 'IndexController' && isset($this->request->path_array[0])) {
            $method = $this->request->path_array[0];
        } else {
            $method = isset($this->request->path_array[1]) ? $this->request->path_array[1] : 'index';
        }

        $data = Gateway::process_class_method($this, $method);
        if ($data !== false) {
            return $data;
        }

        //set default path
        $path = $this->request->path;

        //if there is a template render that
        if (!empty($template)) {

            if (isset($this->request->path_array[1])) {
                $path = preg_replace("~/" . $this->request->path_array[1] . "$~", $template, $path);
            } else {
                $path .= $template;
            }
        } else if (isset($this->request->path_array[1])) {

            $template = $this->request->path_array[1];
        } else {
            $path .='/' . $this->default_file;
        }

        $this->template = $template;

        $this->get_view($path, $extract_vars);
        $output = ob_get_clean();

        return $this->filter_output($output);
    }

	/**
	 * Renders the actual .view template
	 * @param string $view_path The path to the template e.g. /blah/foo
	 * @param mixed $extact_vars extracts the keys of an object or array into
	 * local variables in the view
	 * @return string 
	 */
    protected function get_view($_view_path, $extract_vars=null) {
		
		//extract class vars to local vars for view
		if($this->extract){
			extract(get_object_vars($this));
		}
		
		if(!is_null($extract_vars)){
			if(is_object($extract_vars)){
				$extract_vars = get_object_vars($extract_vars);
			}
			if(is_array($extract_vars)){
				extract($extract_vars);
			}
		}
		
        $_pwd = ROOT . '/private/views/' . $_view_path . '.view';
		
        if (!is_file($_pwd)) {
            $_pwd = false;
            foreach (Gateway::$mods as $mod) {
                $m = ROOT . '/mod/' . $mod . '/views/' . $_view_path . '.view';
                if (is_file($m)) {
                    $_pwd = $m;
                    break;
                }
            }
        }

        if ($_pwd) {
            require($_pwd);
            return;
        }

        return $this->not_found($_view_path);
    }

    /**
     * Include an arbitrary .view template within the $this of the view
     * @param string $view_path  e.g. .interface/cp
	 * @param mixed $extact_vars extracts the keys of an object or array into
	 * local variables in the view
     */
    public function render_view($path, $extract_vars=null) {

        //capture view to buffer
        ob_start();
	
        $this->get_view($path, $extract_vars);
        return ob_get_clean();
    }

    /**
     * Default request not fullfilled
     */
    public function not_found() {
		
        $file = ROOT . '/private/views/errors/404.view';
        if (is_file($file)) {
            include_once($file);
        } else {
            header("HTTP/1.0 404 Not Found");
        }
    }

}

/**
 * Models an incoming request's path and data e.g. /_surebert/custom
 * @author paul.visco@roswellpark.org
 * @package sb_Request
 *
 */
class sb_Request {

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
     * The "get" based input arguments from the request e.g. ?dog=cat as an array of key value pairs
     * @var array
     */
    public $get = Array();
    /**
     * The post based input from the request - used to pass or similate $_POST
     * @var array
     */
    public $post = Array();
    /**
     * Any incoming data not specified in post e.g. PUT, DELETE, command line etc
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
    public function __construct($request) {

        if (preg_match("~\?(.*)$~", $request, $match)) {
            $request = preg_replace("~\?.*$~", '', $request);
            if (isset($match[1])) {
                parse_str($match[1], $this->get);
            }
        }

        if (method_exists('App', 'filter_all_input')) {
            App::filter_all_input($this->get);
        }

        $this->request = $request;

        $arr = explode("/", substr_replace($request, "", 0, 1));

        $this->path_array[0] = $arr[0];
        if (isset($arr[1]) && !empty($arr[1])) {
            $this->path_array[1] = $arr[1];
        }

        $this->path = "/" . implode("/", $this->path_array);

        $this->set_input(Gateway::$post, Gateway::$cookie, Gateway::$files, Gateway::$data);

        $this->method = Gateway::$request_method;
    }

    /**
     * Sets the input for the request
     * @param $post
     * @param $cookie
     * @param $files
     */
    public function set_input(&$post, &$cookie, &$files, &$data) {

        $this->post = $post;
        $this->cookie = $cookie;
        $this->files = $files;
        $this->data = $data;
    }

    /**
     * Sets the input argument delimeter and parses it
     * @param $input_args_delimiter
     */
    public function set_input_args_delimiter($input_args_delimiter) {

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

            if (method_exists('App', 'filter_all_input')) {
                App::filter_all_input($this->args);
            }
        }
    }
    
     /**
     * Gets a get variable value or returns the default value (null unless overridden)
     * @param string $key The $_GET var key to look for
     * @param mixed $default_val null by default
     * @return mixed string value or null 
     */
    public function get_get($key, $default_val=null){
        
        if(isset($this->get[$key])){
            return $this->get[$key];
        }
        
        return $default_val;
    }
    
    /**
     * Gets a post variable value or returns the default value (null unless overridden)
     * @param string $key The $_POST var key to look for
     * @param mixed $default_val null by default
     * @return mixed string value or null 
     */
    public function get_post($key, $default_val=null){
        if(isset($this->post[$key])){
            return $this->post[$key];
        }
        
        return $default_val;
    }
    
     /**
     * Gets a cookie value if set, otherwise returns null
     * 
     * @param string $key The key to look for
     * @return mixed the string value or null if not found
     */
    public function get_cookie($key, $default_val=null){
        if(isset($this->cookie[$key])){
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
    public function get_session($key, $default_val=null){
        
        if(isset($_SESSION[$key])){
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
    public function get_arg($arg_num, $default_val=null){
        if(isset($this->args[$arg_num])){
            return $this->args[$arg_num];
        }
        
        return $default_val;
    }

}

/**
 * Stores configuration variables
 */
class sb_Config {

    /**
     * Stores all the data
     * @var <type>
     */
    protected static $hash = Array();

    /**
     * Gets a config variable
     * @param string $key The key to get the value for
     * @return string value
     */
    public static function get($key) {
        return isset(self::$hash[$key]) ? self::$hash[$key] : null;
    }

    /**
     * Stes a config variable
     * @param string $key The key to set
     * @param mixed $val The value to set
     */
    public static function set($key, $val) {
        self::$hash[$key] = $val;
    }

    /**
     * A dump of the entire config value array
     * @return array
     */
    public static function dump() {
        return print_r(self::$hash, 1);
    }

}

/**
 * The main gateway
 * @author paul.visco@roswellpark.org
 * @package Gateway
 *
 */
class Gateway {

    /**
     * The main controller being served by the request
     * @var sb_Controller
     */
    public static $controller;
    /**
     * An array of command line options if provided
     * --request = the request to render
     * @var array
     */
    public static $cmd_options = null;
    /**
     * The request path being requested
     * @var sb_Request
     */
    public static $request;
    /**
     * The post data sent to the gateway
     *
     * @var array
     */
    public static $post = Array();
    /**
     * The put data sent to the gateway
     *
     * @var array
     */
    public static $data = Array();
    /**
     * The cookie data sent to the gateway
     *
     * @var array
     */
    public static $cookie = Array();
    /**
     * The files data sent to the gateway
     *
     * @var array
     */
    public static $files = Array();
    /**
     * The agent of the client from the HTTP_USER_AGENT or command line if from the command line
     *
     * @var string
     */
    public static $agent = 'command line';
    /**
     * The http_host if it exists
     * @var string
     */
    public static $http_host = '';
    /**
     * The request method GET, PUT, DELETE, POST, etc
     * @var string
     */
    public static $request_method = 'GET';
    /**
     * The remote addr of the client
     *
     * @var string
     */
    public static $remote_addr = '127.0.0.1';
    /**
     * If the request comes from the command line
     *
     * @var boolean
     */
    public static $command_line = false;
    /**
     * Is the gateway serving a magic model
     * @var mixed boolean false or string of the class being served
     */
    public static $magic_model = false;
    /**
     * The type of controller to use by default - must extend sb_Controller
     * @var string
     */
    public static $default_controller_type = 'IndexController';
    
    /**
     * An instance of a logger used to log all gateway requests during debugging
     * @var sb_Logger_Base
     */
    public static $logger;
    /**
     * An array of modules names loaded
     * @var type array
     */
    public static $mods = Array();

    /**
     * Turn data into an appropriate json AJAX response
     * @param mixed $data
     */
    public function json_encode($data) {
        $response = new sb_Ajax_Response();
        $response->set_content($data);
        $response->dispatch();
    }

    /**
     * Loads a view for rendering
     * @param mixed $request Either an instance of sb_Request or a string with the path to the view e.g. /user/run
     * @return string The rendered view data
     */
    public static function render_request($request, $included=true) {

        if ($request instanceof sb_Request && method_exists('App', 'filter_all_input')) {

            App::filter_all_input($request->get);
            App::filter_all_input($request->post);
        } else if (is_string($request)) {
            $request = new sb_Request($request);
        }

        if (!$request instanceof sb_Request) {
            trigger_error('$request must be a sb_Request instance');
        }

        $p = $request->path_array;

        //see if there is an model/action possibility
        if (isset($p[0]) && isset($p[1])) {

            if (strstr($p[0], '_')) {
                $p[0] = explode("_", $p[0]);
                $arr = Array();
                foreach ($p[0] as $s) {
                    $arr[] = ucwords($s);
                }
                $model = implode("_", $arr);
                unset($arr);
            } else {
                $model = ucwords($p[0]);
            }

            $action = $p[1];

            if (class_exists($model)
                    && in_array('sb_Magic_Model', class_implements($model, true))
                    && (method_exists($model, $action) || method_exists($model, '__call'))
            ) {
                //notify which model is being served
                Gateway::$magic_model = $model;

                //explode input args
                $request->set_input_args_delimiter('/');

                //create instance of model
                $instance = new $model($request->args);
                $data = self::process_class_method($instance, $action);
                if ($data !== false) {
                    return $data;
                }
            }
        }

        $controller = $request->path_array[0];
		
        if (empty($controller)) {
            $controller_class = Gateway::$default_controller_type;
        } else {
           $controller_class = str_replace(' ', '_', ucwords(str_replace('_', ' ', $controller))) . 'Controller';
		   
		   $path = str_replace('_', '/', $controller_class).'.php';
		   
		   if (!is_file(ROOT . '/private/controllers/' . $path)) {

                if ($controller == 'surebert') {
                    $controller_class = 'sb_Controller_Toolkit';
                } else {
                    $found = false;
                    foreach (Gateway::$mods as $mod) {
                        $p = ROOT . '/mod/' . $mod . '/controllers/' . $path;
                        if (is_file($p)) {
                            require_once($p);
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $controller_class = null;
                    }
                }
            }
        }

        if (!isset($controller_class)) {
            $controller_class = Gateway::$default_controller_type;
        }

        $controller = new $controller_class();
		
		$controller->included = $included;
		if(!$included){
			Gateway::$controller = $controller;
		}
        if ($request != Gateway::$request) {
            $request->get = array_merge(Gateway::$request->get, $request->get);
        }

        if (!$controller instanceof sb_Controller) {
            trigger_error("Your custom controller " . $controller_class . " must extend sb_Controller");
        }

        $controller->set_request($request);
        return $controller->render();
    }

    public static function process_class_method($class, $method) {

        $servable = false;
        $http_method = 'post';
        $input_as_array = false;
        $args = Array();

        if (method_exists($class, 'on_before_render')) {
            if ($class->on_before_render($method) === false) {
                return false;
            }
        }

        if (method_exists($class, $method)) {
            $reflection = new ReflectionMethod($class, $method);

            //check for phpdocs
            $docs = $reflection->getDocComment();
            if (!empty($docs)) {

                if (preg_match("~@http_method (get|post)~", $docs, $match)) {
                    $http_method = $match[1];
                }

                if (preg_match("~@input_as_array (true|false)~", $docs, $match)) {
                    $input_as_array = $match[1] == 'true' ? true : false;
                }

                if (preg_match("~@servable (true|false)~", $docs, $match)) {
                    $servable = $match[1] == 'true' ? true : false;
                }
            }

            //set up arguments to pass to function
            if (!isset($class->request)) {
                $class->request = Gateway::$request;
            }

            $args = $class->request->{$http_method};

            //pass thru input filter if it exists
            if (method_exists($class, 'filter_input')) {
                $args = $class->filter_input($args);
            }
        } else if (method_exists($class, '__call')) {
            $servable = true;
        }

        if ($servable) {

            if ($input_as_array) {

                $data = $class->$method($args);
            } else {

                $data = call_user_func_array(array($class, $method), array_values($args));
            }

            return $class->filter_output($data);
        }

        return false;
    }

    /**
     * Autoloads classes from the _classes folder when they are instantiated so that the defintions of the classes never need to be manually included
     *
     * @param string $class_name
     */
    public static function sb_autoload($class_name) {

        $class_name = str_replace('_', '/', $class_name);

        if (substr($class_name, 0, 3) == 'sb/') {
            $class_name = substr_replace($class_name, "", 0, 3);
            require(SUREBERT_FRAMEWORK_SB_PATH . '/' . $class_name . '.php');
        } else if (substr($class_name, 0, 3) == 'rp/') {
            $class_name = substr_replace($class_name, "", 0, 3);
            require(SUREBERT_FRAMEWORK_RP_PATH . '/' . $class_name . '.php');
        } else if (preg_match('~Controller$~', $class_name)) {
			$f = ROOT . '/private/controllers/' . $class_name . '.php';
			if(is_file($f)){
				require($f);
			} else {
				foreach (Gateway::$mods as $mod) {
                $f = ROOT . '/mod/' . $mod . '/controllers/' . $class_name . '.php';
                if (is_file($f)) {
                    require($f);
                }
            }
			}
			
		} else if (substr($class_name, 0, 4) == 'mod/') {
            require(ROOT . '/' . $class_name . '.php');
        } else if (file_exists(ROOT . '/private/models/' . $class_name . '.php')) {
            require(ROOT . '/private/models/' . $class_name . '.php');
        } else {
            foreach (Gateway::$mods as $mod) {
                $m = ROOT . '/mod/' . $mod . '/models/' . $class_name . '.php';
                if (is_file($m)) {
                    require($m);
                    break;
                }
            }
        }
		
	
    }

    /**
     * Require a new mod.  Will load the init.php if it exists
     * @param type $mod_name 
     */
    public static function require_mod($mod_name) {
        if (!in_array($mod_name, Gateway::$mods)) {
            Gateway::$mods[] = $mod_name;
        }
        $init = ROOT . '/mod/' . $mod_name . '/init.php';
        if (is_file($init)) {
            require($init);
        }
    }

    /**
     *
     * @param $path The path to the file from ROOT of the framework e.g. /public/surebert/sb.js
     */
    public static function file_require($path) {

        require(ROOT . $path);
    }

    /**
     * Reads a file into a variable
     * @param $path The path to the file from ROOT
     * @return string or false if file not found
     */
    public static function file_read($path) {
        $contents = '';
        if (is_file(ROOT . $path)) {
            $fh = fopen(ROOT . $path, "r");

            while (!feof($fh)) {
                $contents .= fgets($fh);
            }

            fclose($fh);
        } else {
            $contents = false;
        }


        return $contents;
    }

    /**
     * Initializes the gateway by determining the
     * @param $argv array Command line arguments
     */
    public static function init($argv = null) {
        spl_autoload_extensions('.php');
        spl_autoload_register("Gateway::sb_autoload");
        
        self::$remote_addr = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : self::$remote_addr;

        self::$agent = (isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'] != 'command line') ? $_SERVER['HTTP_USER_AGENT'] : self::$agent;

        if(isset(Gateway::$cmd_options['http_host'])){
            self::$http_host = Gateway::$cmd_options['http_host'];
        } else {
           self::$http_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : self::$http_host;

        }
        
        self::$request_method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : self::$request_method;

        if (method_exists('App', 'filter_all_input')) {
            App::filter_all_input($_POST);
        }

        //$_GET is handled by sb_Request constructor so to allow included views to use ? GET syntax
        self::$post = $_POST;
        self::$cookie = $_COOKIE;
        self::$files = $_FILES;

        if (self::$request_method == 'PUT' || self::$request_method == 'DELETE') {
            parse_str(file_get_contents("php://input"), self::$data);
        } else {
            self::$data = self::$post;
        }

        //empty the input data so as to prevent its use
        $_GET = $_POST = $_FILES = $_REQUEST = Array();

    }

}

$request = null;

if (!defined('ROOT')) {

    $cwd = getcwd();
    $root = dirname($cwd);
    if (defined('STDIN')) {
        if (isset($_SERVER['argv'])
                && isset($_SERVER['argv'][0]) && basename($_SERVER['argv'][0]) == 'phpunit'
        ) {
            foreach ($_SERVER['argv'] as $k => $v) {
                if ($v == '--bootstrap') {
                    $root = $_SERVER['argv'][$k + 1];
                    break;
                }
            }
        } else if (isset($argv)) {
            $root = $argv[0];
            if ($root == 'gateway.php') {
                $root = dirname(getcwd());
            }
            
            Gateway::$cmd_options = getopt('', Array('request:', 'http_host:', 'config:', 'install:', 'uninstall:'));
            if (Gateway::$cmd_options) {
                if (isset(Gateway::$cmd_options['request'])) {
                    $request = Gateway::$cmd_options['request'];
                }

                if (isset(Gateway::$cmd_options['config'])) {
                    require(Gateway::$cmd_options['config']);
                    if (isset($_GET)) {
                        $request.='?' . http_build_query($_GET);
                    }
                }
            }
        }
        Gateway::$command_line = true;
    } else if (isset($_SERVER['DOCUMENT_ROOT'])) {
        $root = $_SERVER['DOCUMENT_ROOT'];
    }

    $root = str_replace("\\", "/", $root);
    $root = preg_replace('~/public.*$~', '', $root);

    define("ROOT", $root);

    unset($root);
}

if (isset($_SERVER['REQUEST_URI'])) {
    $request = $_SERVER['REQUEST_URI'];
}

//require the App class for static global vars
Gateway::file_require('/private/config/App.php');

//initialize the gateway
Gateway::init();

$output = '';
if ($request) {

    //set the main request and filter the input if required
    Gateway::$request = new sb_Request($request);
}

//include site based definitions/global functions
Gateway::file_require('/private/config/definitions.php');

if ($request) {
    //load the main request as view or magic model
    $output = Gateway::render_request(Gateway::$request, false);

    unset($request);
}

if(isset(Gateway::$cmd_options)){
    if(isset(Gateway::$cmd_options['install'])){
        require_once(ROOT.'/mod/'.Gateway::$cmd_options['install'].'/install.php');
    } else if(isset(Gateway::$cmd_options['uninstall'])){
        require_once(ROOT.'/mod/'.Gateway::$cmd_options['uninstall'].'/uninstall.php');
    }
}

//filter the output if required and display it
if (method_exists('App', "filter_all_output")) {
    echo App::filter_all_output($output);
} else {
    echo $output;
}

if (ob_get_level()) {
    ob_flush();
}

if (Gateway::$logger instanceof sb_Logger_Base) {
    $stats = ((microtime(true) - $sb_start) * 1000) . "ms\t" . (memory_get_usage() / 1024) . "kb\n" . print_r(Gateway::$request->request, 1);
    Gateway::$logger->gateway($stats);
}
?>