<?php

/**
 * Initializes a surebert framework project
 *
 * @author paul.visco@roswellpark.org
 * @package Gateway
 *
 */
#####################################################################
##### DO NOT TOUCH THIS FILE UNLESS YOU KNOW WHAT YOU ARE DOING #####
#####################################################################

namespace sb;

\ob_start();

/**
 * The main gateway
 * @author paul.visco@roswellpark.org
 * @package Gateway
 *
 */
class Gateway {

    /**
     * The main controller being served by the request
     * @var sb\Controller
     */
    public static $controller;

    /**
     * The main controller class being served by the request
     * @var string
     */
    public static $controller_class;

    /**
     * An array of command line options if provided
     * --request = the request to render
     * @var array
     */
    public static $cmd_options = null;

    /**
     * The request path being requested
     * @var sb\Request
     */
    public static $request;

    /**
     * The agent of the client from the HTTP_USER_AGENT or blank
     * @var string
     */
    public static $agent = '';

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
     * An instance of a logger used to log all gateway requests during debugging
     * @var \sb\Logger_Base
     */
    public static $logger;

    /**
     * An array of modules names loaded
     * @var type array
     */
    public static $mods = Array();

    /**
     * The start time in ms that the request was loaded
     * @var integer
     */
    public static $start_time = 0;

    /**
     * Allow direct rendering of .view templates matching request if not matching
     * controller method is found e.g. /chat/lines would render /chat/latest.view regardless of if
     * ChatController had lines method.
     * @var boolean
     */
    public static $allow_direct_view_rendering = true;

    /**
     * Determines if main request is rendered
     * @var boolean 
     */
    public static $render_main_request = true;

    /**
     * Indicates that Gateway was instantiated via phpunit unit test for the framework itself
     * rather than for code which uses the framework.  When testing the framework itself, files
     * such as App.php and the definitions files will not be present.
     *
     * @var boolean
     */
    public static $running_framework_unittests = false;

    /**
     * Converts underscore string to camel case
     * @param string $str
     * @return string
     */
    public static function toCamelCase($str) {
        return preg_replace_callback('/_([a-zA-Z0-9])/', function($v) {
            return strtoupper($v[1]);
        }, strtolower($str));
    }

    /**
     * Converts path to controller class name
     * @param string $str
     * @return string
     */
    public static function pathToController($str) {
        return preg_replace_callback('/_([a-z])/', function($v) {
            return "\\" . strtoupper($v[1]);
        }, $str);
    }

    /**
     * Sets the request
     * @param $request string
     */
    public static function setRequest($request) {

        self::$request = new Request($request);

        $put = [];
        $delete = [];
        $data = [];
        $request_method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

        if ($request_method == 'PUT') {
            \parse_str(file_get_contents("php://input"), $put);
        } else if ($request_method == 'DELETE') {
            \parse_str(file_get_contents("php://input"), $delete);
        } else if ($request_method != 'GET' && $request_method != 'POST') {
            \parse_str(file_get_contents("php://input"), $data);
        }

        self::$request->setInput($_POST, $_COOKIE, $_FILES, $put, $delete, $data);
        self::$request->setMethod($request_method);

        //empty the input data so as to prevent its use
        $_GET = $_POST = $_FILES = $_REQUEST = [];

        Gateway::$controller_class = self::getControllerClass(self::$request);
    }

    /**
     * Gets the controller class name from the request
     * @param \sb\Request $request
     * @return string
     */
    public static function getControllerClass(\sb\Request $request) {
        $controller = $request->path_array[0];
        if (Gateway::$command_line) {
            // If run from commandline, default the controller to the framework Command\Line base class
            $controller_class = 'sb\Controller\Command\Line';
        } else {
            // If not run from commandline, default the controller to the project's Index controller
            $controller_class = '\Controllers\Index';
        }
        $request_class = '\\Controllers\\' . ucwords(self::pathToController($controller));
        if (class_exists($request_class) && in_array('sb\Controller\Base', class_parents($request_class))) {
            $controller_class = $request_class;
        }

        return $controller_class;
    }

    protected static $routes = [];

    public static function addRoute($method, $pattern, $callable) {
        self::$routes[strtoupper($method)][$pattern] = $callable;
    }

    /**
     * Processes any routes when matching controller methods do not exist
     * @return boolean
     * @throws type
     */
    protected static function processRoutes(\sb\Request $request) {

        //loop through route options
        foreach (self::$routes as $http_methods => $routes) {

            //limit the routes to any HTML methods defined or any if *
            if ($http_methods != '*' && !in_array($request->method, explode(',', $http_methods))) {
                continue;
            }

            //for each one of the routes definedc check to see if the pattern matches the request
            foreach ($routes as $pattern => $callable) {

                //define the pattern to match
                $pattern = preg_replace("/:\w+/", "([^/]+)", $pattern);

                if (\preg_match('#' . $pattern . '#', $request->request, $matches)) {

                    //if the callable is a string
                    if (\is_string($callable) && strstr($callable, '@')) {

                        //check to see if the current controller has a matching method name
                        $parts = explode("@", $callable);

                        if (count($parts) > 1) {
                            //instantiate the class to be called if it is not already $this class
                            if ($parts[0] == '\\' . \get_called_class()) {
                                $class = $this;
                            } else if (class_exists($parts[0])) {
                                $class = new $parts[0];
                            }

                            //set the request equal to the current request
                            $class->request = $request;

                            if ($class->onBeforeRender($pattern) === false) {
                                return Array('exists' => true, 'data' => false);
                            }

                            //set the callable to the class and method defined
                            $callable = Array($class, $parts[1]);
                        }
                    }

                    if (is_callable($callable)) {
                        return ['exists' => true, 'data' => \call_user_func_array($callable, array_slice($matches, 1, count($matches)))];
                    } else {
                        throw(new \Exception("Routing callable for pattern '" . $pattern . "' using httpd methods " . $http_methods . " on " . get_called_class() . "->route() is not callable"));
                    }
                }
            }
        }

        return Array('exists' => false, 'data' => false);
    }

    /**
     * Returns the calling function through a backtrace
     * used for error reporting
     */
    public static function getCallingMethod() {

        $bt = debug_backtrace();
        $caller = $bt[2];

        $c = '';
        if (isset($caller['class'])) {
            $c = $caller['class'] . '::';
        }
        if (isset($caller['object'])) {
            $c = get_class($caller['object']) . '->';
        }

        return $c . $caller['function'] . '()';
    }

    /**
     * Loads a view for rendering
     * @param mixed $request Either an instance of Request or a string with the path to the view e.g. /user/run
     * @return string The rendered view data
     */
    public static function renderRequest($request, $included = true) {
        if ($request instanceof Request && \method_exists('\App', 'filterAllInput')) {
            \App::filterAllInput($request->get);
            \App::filterAllInput($request->post);
        } elseif (\is_string($request)) {
            $request = new Request($request);
        }

        if (!$request instanceof Request) {
            trigger_error('$request must be a \sb\Request instance');
        }

        if ($included) {
            $controller_class = self::getControllerClass($request);
        } else {
            $controller_class = Gateway::$controller_class;
        }

        $controller = new $controller_class();

        $controller->included = $included;
        if (!$included) {
            self::$controller = $controller;
        }
        if ($request != self::$request) {
            $request->get = array_merge(self::$request->get, $request->get);
        }

        if (!$controller instanceof \sb\Controller\Base) {
            throw new \Exception("Your custom controller " . $controller_class . " must extend \sb\Controller\Base");
        }

        $controller->setRequest($request);

        return $controller->render();
    }

    /**
     * Require a new mod.  Will load the init.php if it exists
     * @param type $mod_name
     */
    public static function requireMod($mod_name) {
        if (!\in_array($mod_name, self::$mods)) {
            self::$mods[] = $mod_name;
        }
        $init = ROOT . '/mod/' . $mod_name . '/init.php';
        if (\is_file($init)) {
            require($init);
        }
    }

    /**
     *
     * @param $path The path to the file from ROOT of the framework e.g. /public/surebert/sb.js
     */
    public static function fileRequire($path) {
        if (Gateway::$running_framework_unittests) {
            return;
        }
        require(ROOT . $path);
    }

    /**
     * Reads a file into a variable
     * @param $path The path to the file from ROOT
     * @return string or false if file not found
     */
    public static function fileRead($path) {
        $contents = '';
        if (\is_file(ROOT . $path)) {
            $fh = \fopen(ROOT . $path, "r");

            while (!feof($fh)) {
                $contents .= fgets($fh);
            }

            \fclose($fh);
        } else {
            $contents = false;
        }


        return $contents;
    }

    /**
     * Initializes the gateway by determining the
     */
    public static function init() {
        self::$remote_addr = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : self::$remote_addr;

        self::$agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : self::$agent;

        if (defined('REQUEST_URI')) {
            $request = REQUEST_URI;
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $request = $_SERVER['REQUEST_URI'];
        } else if (isset(self::$cmd_options['request'])) {
            $request = self::$cmd_options['request'];
        } else {
            $request = '/';
        }

        if (isset(self::$cmd_options['config'])) {
            require(self::$cmd_options['config']);
            if (isset($_GET)) {
                $request .= '?' . \http_build_query($_GET);
            }
        }

        if (isset(self::$cmd_options['http_host'])) {
            self::$http_host = self::$cmd_options['http_host'];
        } else {
            self::$http_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : self::$http_host;
        }

        self::setRequest($request);

        if (\method_exists('App', 'filterAllInput')) {
            \App::filterAllInput($_POST);
        }

        return function(\sb\Request $request) {
            return self::processRoutes($request);
        };
    }
    
    /**
     * Renders a PSR7 response
     * @param \GuzzleHttp\Psr7\Response $response
     */
    public static function renderResponse(\GuzzleHttp\Psr7\Response $response){
        $http_line = sprintf('HTTP/%s %s %s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        );
        header($http_line, true, $response->getStatusCode());
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header("$name: $value", false);
            }
        }
        $stream = $response->getBody();
        if ($stream->isSeekable()) {
            $stream->rewind();
        }
        while (!$stream->eof()) {
            echo $stream->read(1024 * 8);
        }
    }

}

Gateway::$start_time = \microtime(true);

if (!defined('ROOT')) {

    $cwd = \getcwd();
    $root = \dirname($cwd);

    if (\defined('STDIN')) {

        $root = isset($argv[0]) ? $argv[0] : $root;

        if (isset($_SERVER['argv']) && isset($_SERVER['argv'][0]) && preg_match("~phpunit(-skelgen)?~", $_SERVER['argv'][0])
        ) {
            Gateway::$render_main_request = false;

            foreach ($_SERVER['argv'] as $k => $v) {
                if ($v == '--bootstrap') {
                    $root = $_SERVER['argv'][$k + 1];

                    if (basename($_SERVER['argv'][$k+1]) === 'surebert_bootstrap.php') {
                        Gateway::$running_framework_unittests = true;
                    }
                    break;
                }
            }
        }

        Gateway::$cmd_options = getopt('', Array('request:', 'http_host:', 'config:'));

        if (is_file($root)) {
            $root = dirname(dirname(realpath($root)));
        }

        Gateway::$command_line = true;
    } elseif (isset($_SERVER['DOCUMENT_ROOT'])) {
        $root = preg_replace("~/public$~", "", $_SERVER['DOCUMENT_ROOT']);
    }

    $root = \str_replace("\\", "/", $root);

    define("ROOT", $root);

    unset($root);
}

//include composer autoload
require_once ROOT . '/vendor/autoload.php';

//require the App class for static global vars
Gateway::fileRequire('/private/config/App.php');

//initialize the gateway
$process_routes = Gateway::init();

$output = '';

//include site based definitions/global functions
Gateway::fileRequire('/private/config/definitions.php');

//render the main request
if (Gateway::$render_main_request) {

    $response = $process_routes(Gateway::$request);
    if ($response['exists']) {
        $output = $response['data'];
    }

    if (!$output) {
        $output = Gateway::renderRequest(Gateway::$request, false);
    }
    
    if($output instanceof \GuzzleHttp\Psr7\Response){
        Gateway::renderResponse($output);
    } else {
        //filter the output if required and display it
        if (\method_exists('\App', "filterAllOutput")) {
            echo \App::filterAllOutput($output);
        } else {
            echo $output;
        }
    }
    
}

if (ob_get_level()) {
    ob_flush();
}
