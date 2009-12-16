<?php

/**
 * Initializes a surebert framework project - do not edit
 *
 * @author Paul Visco
 * @version 3.12 10-01-2008 12-03-2009
 * @package sb_Application
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
interface sb_Magic_Model{

    /**
     * Filter the output before it is passed back to the Gateway
     * @param string $out
     */
    public function filter_output($out);

}

/**
 * Loads the .view file that corresponds to the request
 * @author paul.visco@roswellpark.org
 * @package sb_View
 */
class sb_View {

/**
 * Determines which file is loaded in the view directory if one is not specified.  When not set renders index.view.  To set just use template name minus the .view extension e.g. $this->default-file = index;
 *
 * @var string
 */
    protected $default_file = 'index';

    /**
     * The argument delimeter e.g. the comma in: /view/action/1,2,3
     * @var string
     */
    protected $input_args_delimiter = ',';

    /**
     * Set to true if the view is loaded from within another view via Gateway::render_view, otherwise false
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
     * Left in for backwards compat with v1.0
     * @todo depreciate this and use $this->request->args instead
     * @var array
     */
    protected $args;

    /**
     * Filters input arguments /page/read/1,2 e.g. 1,2 as array
     * @param $arr Array reference to $this->request->args array
     */
    protected function filter_args(&$arr) {}

    /**
     * Filters input arguments /page/read/1?one=two e.g. one=>two as array same as $_GET
     * @param $arr Array reference to $this->request->get array
     */
    protected function filter_get(&$arr) {}

    /**
     * Filters input arguments from $_POST
     * @param $arr Array reference to $this->request->post array
     */
    protected function filter_post(&$arr) {}

    /**
     * Filters the output after the view is rendered but before
     * it is displayed so that you can filter the output
     *
     * @param $output
     * @return string
     */
    protected function filter_output($output) {
        return $output;
    }

    /**
     * Fires before the .view template is rendered allowing you to make decisions, check input args, etc before the output is rendered.
     * If you return false from this method then no output is rendered.
     * @param $template The .view template requested.  e.g. for /user/dance $template would be dance
     * @return boolean determines if the view should render anything or not, false == no render
     */
    protected function on_before_render($template) {
        return true;
    }
    /**
     * Fires when view template is not found
     */
    protected function template_not_found($template) {

        header("HTTP/1.0 404 Not Found");
        if(is_file(ROOT.'/private/views/error/404.view')) {
            echo Gateway::render_view('/error/404');
        }
    }

    /**
     * Render the view from the template file based on the request
     *
     * @param String $template the template to use e.g. /dance
     *
     * @todo should template render be /template or template
     * @todo should on_before_fire for includes?
     * @todo should path and path arrray be temporaily reset
     * @todo are we still going with this->request->get or do we just want this get?;(
     */
     public function render($template='') {

        //set default path
        $path = $this->request->path;

        //if there is a template render that
        if(!empty($template)) {

            $this->included = true;
            if(isset($this->request->path_array[1])) {
                $path = preg_replace("~/".$this->request->path_array[1]."$~", $template, $path);

            } else {
                $path .= $template;
            }

        } else if(isset($this->request->path_array[1])) {

                $template = $this->request->path_array[1];
            } else {
                $path .='/'.$this->default_file;
            }

        $pwd = ROOT.'/private/views'.$path.'.view';

        $output = '';

        //capture view to buffer
        ob_start();

        $on_before_render = $this->on_before_render($template) !== false;

        $this->template = $template;

        //use template not found if view not found
        if(!is_file($pwd)) {
            $this->template_not_found(basename($path));

        } else if($on_before_render) {

            require($pwd);
        }

        $output = ob_get_clean();

        $output = $this->filter_output($output);

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

        $this->filter_args($request->args);

        $this->filter_get($request->get);

        $this->filter_post($request->post);

        /**
         * Hold over for backward compatibility
         * @var array
         * @todo depreciate this
         */
        $this->args = $request->args;
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
     * Creates a new request instance
     * @param $request The string request with args e.g. /_surebert/custom/strings.numPad
     */
    public function __construct($request) {

        if(preg_match("~\?(.*)$~", $request, $match)) {
            $request = preg_replace("~\?.*$~", '', $request);
            if(isset($match[1])) {
                parse_str($match[1], $this->get);
            }
        }

        if(method_exists('App', 'filter_all_input')) {
            App::filter_all_input($this->get);
        }

        $this->request = $request;

        $arr = explode("/", substr_replace($request, "", 0, 1));

        $this->path_array[0] = $arr[0];
        if(isset($arr[1]) && !empty($arr[1])) {
            $this->path_array[1] = $arr[1];
        }

        $this->path = "/".implode("/", $this->path_array);

        $this->set_input(Gateway::$post, Gateway::$cookie, Gateway::$files);
    }

    /**
     * Sets the input for the request
     * @param $post
     * @param $cookie
     * @param $files
     */
    public function set_input(&$post, &$cookie, &$files) {

        $this->post = $post;
        $this->cookie = $cookie;
        $this->files = $files;
    }

    /**
     * Sets the input argument delimeter and parses it
     * @param $input_args_delimiter
     */
    public function set_input_args_delimiter($input_args_delimiter) {

    //parse arguments by removing path
        $args = preg_replace("~^".$this->path."/?~", "", $this->request);

        //remove $_GET string
        $args = preg_replace("~\?.*?$~", "", $args);

        if($args !== '') {

            $this->args = explode($input_args_delimiter, $args);

            //decodes url encoding
            foreach($this->args as &$arg) {
                $arg = urldecode($arg);
            }

            if(method_exists('App', 'filter_all_input')) {
                App::filter_all_input($this->args);
            }

        }

    }

}

/**
 * The main gateway
 * @author paul.visco@roswellpark.org
 * @package sb_Application
 *
 */
class Gateway {

//Gateway::$remote_addr = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
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
	 * Should errors be formatted as HTML
	 * @var boolean
	 */
	public static $html_errors = true;

    /**
     * An instance of a logger used to log all gateway requests during debugging
     * @var sb_Logger_Base
     */
    public static $logger;

     /**
      * Loads a view for rendering
      * @param mixed $request Either an instance of sb_Request or a string with the path to the view e.g. /user/run
      * @param object $model Optional The model used in the view
      * @return string The rendered view data
      */
    public static function render_view($request, $model='') {

        if($request instanceof sb_Request && method_exists('App', 'filter_all_input')) {

            App::filter_all_input($request->get);
            App::filter_all_input($request->post);

        } else if(is_string($request)) {
            $request = new sb_Request($request);
        }

        if(!$request instanceof sb_Request) {
            trigger_error('$request must be a sb_Request instance');
        }

        $view = $request->path_array[0];

        if(!empty($view)) {
            $viewClass = ucwords($view).'View';
            $viewFile = ROOT.'/private/views/'.$view.'/'.$viewClass.'.php';
        } else {
            $viewClass = 'IndexView';
            $viewFile = ROOT.'/private/views/IndexView.php';
        }

        $viewClass = (is_file($viewFile)) ? $viewClass : 'sb_View';

        $view = new $viewClass();
        $view->model = $model;

        if($request != Gateway::$request) {

            $request->get = array_merge(Gateway::$request->get, $request->get);

            $view->included = true;
        }

        if(!$view instanceof sb_View) {
            trigger_error("Your custom view ".$viewClass." must extend sb_View");
        }

        $view->set_request($request);

        return $view->render();

    }

    /**
     * Loads the main request
     * @author visco
     *
     */
    public static function render_main_request() {

        $p = self::$request->path_array;

        //see if there is an model/action possibility
        if(isset($p[0]) && isset($p[1])){
            if(strstr($p[0], '_')){
				$p[0] = explode("_", $p[0]);
				$arr = Array();
				foreach($p[0] as $s){
					$arr[] = ucwords($s);

				}
				$model = implode("_", $arr);
				unset($arr);
			} else {
				$model = ucwords($p[0]);
			}

            $action = $p[1];

            if(class_exists($model)){

                if(method_exists($model, $action) && in_array('sb_Magic_Model', class_implements($model, true))){

                    $reflection = new ReflectionMethod($model, $action);

                    //check for phpdocs
                    $docs = $reflection->getDocComment();

                    //get class default method
                    $http_method = 'post';

                    //determine how args are passed to method
                    $input_as_array = false;

                    $servable = false;

                    if(!empty($docs)){
                        if(preg_match("~@http_method (get|post)~", $docs, $match)){
                            $http_method = $match[1];
                        }

                        if(preg_match("~@input_as_array (true|false)~", $docs, $match)){
                            $input_as_array = $match[1] == 'true' ? true : false;
                        }

                        if(preg_match("~@servable (true|false)~", $docs, $match)){

                            $servable = $match[1] == 'true' ? true : false;
                        }

                    }

                    if($servable){
                        //notify which model is being served
                        Gateway::$magic_model = $model;

                        //explode input args
                        self::$request->set_input_args_delimiter('/');

                        //create instance of model
                        $instance = new $model(self::$request->args);

                        //set up arguments to pass to function
                        $args = self::$request->{$http_method};

                        //pass thru input filter if it exists
                        if(method_exists($instance, 'filter_input')){
                            $args = $instance->filter_input($args);
                        }

                        if($input_as_array){
                            $data = $instance->$action($args);
                        } else {
                            $data = call_user_func_array(array($instance, $action), array_values($args));
                        }

                        if(isset($instance->logger) && $instance->logger instanceof sb_Logger_Base){
                            $instance->logger->add_log_types(Array($model));

                            if($input_as_array){
                                $args = json_encode($args);
                            } else {
                                $args = implode(",", $args);
                            }
                            $instance->logger->{$model}($action."(".$args.');');
                        }

                        return $instance->filter_output($data);
                    } else {
                        return Gateway::render_view('/error/404.view');
                    }
                }
            }

        }

        //otherwise assume view and render accordingly
        return self::render_view(self::$request);
    }

    /**
     * Autoloads classes from the _classes folder when they are instantiated so that the defintions of the classes never need to be manually included
     *
     * @param string $class_name
     */

    public static function sb_autoload($class_name) {

        $class_name = str_replace('_', '/', $class_name);

        if(substr($class_name, 0, 3) == 'sb/') {
            $class_name = substr_replace($class_name, "", 0, 3);
            require(SUREBERT_FRAMEWORK_SB_PATH.'/'.$class_name.'.php');

        } else if(substr($class_name, 0, 3) == 'rp/') {
            $class_name = substr_replace($class_name, "", 0, 3);
            require(SUREBERT_FRAMEWORK_RP_PATH.'/'.$class_name.'.php');

        } else if($class_name  == 'IndexView') {

            require(ROOT.'/private/views/IndexView.php');
        } else if(preg_match('~View$~', $class_name)) {
            $d = preg_replace("~[A-Z][a-z]+$~", "", $class_name);
            require(ROOT.'/private/views/'.strtolower($d).'/'.$class_name.'.php');
        } else if(file_exists(ROOT.'/private/models/'.$class_name.'.php')) {

            require(ROOT.'/private/models/'.$class_name.'.php');
        } else if(strstr($class_name, 'PHPUnit') && defined('PHPUNIT_PATH')) {

            require(PHPUNIT_PATH.$class_name.'.php');
        }
    }

    /**
     * Grabs the request from the REQUEST_URI or the command line argv
     * @param $argv array Command line arguments
     */
    public static function set_main_request($argv) {

    //Calculates the path based on REQUEST_URI or the command line args
        if(!empty($_SERVER['REQUEST_URI'])) {
            $request = $_SERVER['REQUEST_URI'];
        } else if(isset($argv)) {

            $request = $argv[1];

        } else {
            die("Path not found! Application cannot run in this context");
        }


        //requires variable $_GET, $_POST, $_COOKIE, $_FILES, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']
        if (isset($argv[2])){

            //if argv3 set POST OR GET based on argv2
            if(isset($argv[3])){
                if(strcasecmp($argv[2], 'post') == 0){
                    parse_str($argv[3], $_POST);
                } else {
                    $request.='?'.$argv[3];
                }

            //otherwise load file
            } else if(is_file($argv[2])) {

                require_once($argv[2]);
                $request.='?'.http_build_query($_GET);
            }

        }

        //allow user to override determination of remote_addr, e.g. using proxy X-FORWARDED-FOR etc
        if(method_exists('App', 'set_remote_addr')) {
            self::$remote_addr = App::set_remote_addr();
        } else {
            self::$remote_addr = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : self::$remote_addr;
        }

        self::$agent = (isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'] != 'command line') ? $_SERVER['HTTP_USER_AGENT'] : self::$agent;

        self::$http_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : self::$http_host;

        if(method_exists('App', 'filter_all_input')) {
            App::filter_all_input($_POST);
        }

        //$_GET is handled by sb_Request constructor so to allow included views to use ? GET syntax
        self::$post = $_POST;
        self::$cookie = $_COOKIE;
        self::$files = $_FILES;

        //empty the input data so as to prevent its use
        $_GET = $_POST = $_FILES = Array();

        //convert REQUEST into an array and define Controller which loads view
        self::$request = new sb_Request($request);
    }

    /**
     *
     * @param $path The path to the file from ROOT of the framework e.g. /public/surebert/sb.js
     */
    public static function file_require($path) {

        require(ROOT.$path);
    }

    /**
     * Reads a file into a variable
     * @param $path The path to the file from ROOT
     * @return string or false if file not found
     */
    public static function file_read($path) {
        $contents = '';
        if(is_file(ROOT.$path)) {
            $fh = fopen(ROOT.$path, "r");

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

    }

    /**
     * Converts errors into exceptions
     * @param integer $code The error code
     * @param string $message The error message
     * @param string $file The file the error occurred in
     * @param integer $line The line the error occurred on
     */
    public static function error_handler($code, $message, $file, $line) {
        throw new sb_Exception($code, $message, $file, $line);
    }

    /**
     * Handles acceptions and turns them into strings
     * @param Exception $e
     */
    public static function exception_handler(Exception $e){

        $s = Gateway::$html_errors ? '<br />' : "\n";
        $m = 'Code: '.$e->getCode()."\n".
            'Message: '.$e->getMessage()."\n".
            'Location: '.$e->getFile()."\n".
            'Line: '.$e->getLine()."\n".
            "Trace: \n\t".str_replace("\n", "\n\t",$e->getTraceAsString());

        if(Gateway::$html_errors){
			echo '<div style="background-color:red;padding:10px;color:#FFF;">'.nl2br(str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $m)).'</div>';
        } else if(Gateway::$command_line){
            file_put_contents('php://stderr', "\n".$m."\n");
        } else {
			echo "\n".$m."\n";
		}
    }
}

if(isset($argv)) {
    Gateway::$command_line = true;
	Gateway::$html_errors = false;
}

/**
 * Used to throw custom exceptions
 * @author paul.visco@roswellpark.org
 * @package sb_Exception
 */
class sb_Exception extends Exception{

    private $context = null;

    public function __construct($code, $message, $file, $line, $context = null){
        parent::__construct($message, $code);
        $this->file = $file;
        $this->line = $line;
        $this->context = $context;
    }
};

set_error_handler('Gateway::error_handler');
set_exception_handler('Gateway::exception_handler');

define("ROOT", str_replace('/public', '', str_replace("\\", "/", Gateway::$command_line ? $_ENV['PWD'] : $_SERVER['DOCUMENT_ROOT'])));

//initialize the gateway
Gateway::init();

//require the App class for static global vars
Gateway::file_require('/private/config/App.php');

//set the main request and filter the input if required
Gateway::set_main_request((isset($argv) ? $argv : null));

//include site based definitions/global functions
Gateway::file_require('/private/config/definitions.php');

//load the main request as view or magic model
$output = Gateway::render_main_request();

//filter the output if required and display it
if(method_exists('App', "filter_all_output")) {
    echo App::filter_all_output($output);
} else {
    echo $output;
}

ob_flush();

if(Gateway::$logger instanceof sb_Logger_Base){

    Gateway::$logger->add_log_types(Array('gateway'));
    Gateway::$logger->gateway(((microtime(true)-$sb_start)*1000)."ms\t".(memory_get_usage()/1024)."kb\n".print_r(Gateway::$request, 1));
}

?>