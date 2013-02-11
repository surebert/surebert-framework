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

//script start time
$sb_start = \microtime(true);

/**
 * The main gateway
 * @author paul.visco@roswellpark.org
 * @package Gateway
 *
 */
class Gateway
{

    /**
     * The main controller being served by the request
     * @var sb\Controller
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
     * @var sb\Request
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
     * The type of controller to use by default - must extend sb\Controller
     * @var string
     */
    public static $default_controller_type = 'IndexController';

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
     * Converts underscore string to camel case
     * @param string $str
     * @return string
     */
    public static function toCamelCase($str)
    {
      return preg_replace_callback('/_([a-z])/', function($v){
          return strtoupper($v[1]);
      }, $str);
    }

    /**
     * Loads a view for rendering
     * @param mixed $request Either an instance of Request or a string with the path to the view e.g. /user/run
     * @return string The rendered view data
     */
    public static function renderRequest($request, $included = true)
    {

        if ($request instanceof Request && \method_exists('\App', 'filter_all_input')) {

            \App::filter_all_input($request->get);
            \App::filter_all_input($request->post);
        } elseif (\is_string($request)) {
            $request = new Request($request);
        }

        if (!$request instanceof Request) {
            trigger_error('$request must be a \sb\Request instance');
        }

        $controller = $request->path_array[0];

        if (empty($controller)) {
            $controller_class = self::$default_controller_type;
        } else {
            $controller_class = str_replace(' ', '_', ucwords(str_replace('_', ' ', $controller))) . 'Controller';

            $path = str_replace('_', '/', $controller_class) . '.php';

            if (!\is_file(ROOT . '/private/controllers/' . $path)) {

                if ($controller == 'surebert') {
                    $controller_class = '\sb\Controller\Toolkit';
                } else {
                    $found = false;
                    foreach (self::$mods as $mod) {
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
            $controller_class = self::$default_controller_type;
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
     * Autoloads classes from the _classes folder when they are instantiated
     * so that the defintions of the classes never need to be manually included
     *
     * @param string $class_name
     */
    public static function autoload($class_name)
    {

        if (preg_match('~Controller$~', $class_name)) {
                $f = ROOT . '/private/controllers/' . $class_name . '.php';
                if (is_file($f)) {
                        require($f);
                } else {
                        foreach (self::$mods as $mod) {
                                $f = ROOT . '/mod/' . $mod . '/controllers/' . $class_name . '.php';
                                if (is_file($f)) {
                                        require($f);
                                }
                        }
                }
        } elseif (substr($class_name, 0, 4) == 'mod/') {
                require(ROOT . '/' . $class_name . '.php');
        } elseif (file_exists(ROOT . '/private/models/' . $class_name . '.php')) {
                require(ROOT . '/private/models/' . $class_name . '.php');
        } elseif (file_exists(ROOT . '/private/resources/' . $class_name . '.php')) {
                require(ROOT . '/private/resources/' . $class_name . '.php');
        } else {
                foreach (self::$mods as $mod) {
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
    public static function requireMod($mod_name)
    {
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
    public static function fileRequire($path)
    {
        require(ROOT . $path);
    }

    /**
     * Reads a file into a variable
     * @param $path The path to the file from ROOT
     * @return string or false if file not found
     */
    public static function fileRead($path)
    {
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
     * @param $argv array Command line arguments
     */
    public static function init($argv = null)
    {

        spl_autoload_extensions('.php');
        spl_autoload_register("sb\Gateway::autoload");

        require_once ROOT . '/vendor/autoload.php';

        self::$remote_addr = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : self::$remote_addr;

        self::$agent = (isset($_SERVER['HTTP_USER_AGENT'])
                && $_SERVER['HTTP_USER_AGENT'] != 'command line')
                ? $_SERVER['HTTP_USER_AGENT'] : self::$agent;

        if (isset(self::$cmd_options['http_host'])) {
            self::$http_host = self::$cmd_options['http_host'];
        } else {
            self::$http_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : self::$http_host;
        }

        self::$request_method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : self::$request_method;

        if (\method_exists('App', 'filter_all_input')) {
            \App::filter_all_input($_POST);
        }

        //$_GET is handled by sb_Request constructor so to allow included views to use ? GET syntax
        self::$post = $_POST;
        self::$cookie = $_COOKIE;
        self::$files = $_FILES;

        if (self::$request_method == 'PUT' || self::$request_method == 'DELETE') {
            \parse_str(file_get_contents("php://input"), self::$data);
        } else {
            self::$data = self::$post;
        }

        //empty the input data so as to prevent its use
        $_GET = $_POST = $_FILES = $_REQUEST = Array();
    }
}

$request = null;

if (!defined('ROOT')) {

    $cwd = \getcwd();
    $root = \dirname($cwd);
    if (\defined('STDIN')) {
        if (isset($_SERVER['argv'])
                && isset($_SERVER['argv'][0]) && \basename($_SERVER['argv'][0]) == 'phpunit'
        ) {
            foreach ($_SERVER['argv'] as $k => $v) {
                if ($v == '--bootstrap') {
                    $root = $_SERVER['argv'][$k + 1];
                    break;
                }
            }
        } elseif (isset($argv)) {
            $root = $argv[0];
            if ($root == 'gateway.php') {
                $root = \dirname(getcwd());
            }

            self::$cmd_options = getopt('', Array('request:', 'http_host:', 'config:', 'install:', 'uninstall:'));
            if (self::$cmd_options) {
                if (isset(self::$cmd_options['request'])) {
                    $request = self::$cmd_options['request'];
                }

                if (isset(self::$cmd_options['config'])) {
                    require(self::$cmd_options['config']);
                    if (isset($_GET)) {
                        $request.='?' . \http_build_query($_GET);
                    }
                }
            }
        }
        self::$command_line = true;
    } elseif (isset($_SERVER['DOCUMENT_ROOT'])) {
        $root = $_SERVER['DOCUMENT_ROOT'];
    }

    $root = \str_replace("\\", "/", $root);
    $root = \preg_replace('~/public.*$~', '', $root);

    define("ROOT", $root);

    unset($root);
}

if (defined('REQUEST_URI')) {
    $request = REQUEST_URI;
} elseif (isset($_SERVER['REQUEST_URI'])) {
    $request = $_SERVER['REQUEST_URI'];
}

//require the App class for static global vars
Gateway::fileRequire('/private/config/App.php');

//initialize the gateway
Gateway::init();

Gateway::$start_time = $sb_start;

$output = '';
if ($request) {

    //set the main request and filter the input if required
    Gateway::$request = new Request($request);
}

//include site based definitions/global functions
Gateway::fileRequire('/private/config/definitions.php');

if ($request) {
    //render the main request
    $output = Gateway::renderRequest(Gateway::$request, false);
    unset($request);
}

if (isset(Gateway::$cmd_options)) {
    if (isset(Gateway::$cmd_options['install'])) {
        require_once(ROOT . '/mod/' . Gateway::$cmd_options['install'] . '/install.php');
    } elseif (isset(Gateway::$cmd_options['uninstall'])) {
        require_once(ROOT . '/mod/' . Gateway::$cmd_options['uninstall'] . '/uninstall.php');
    }
}

//filter the output if required and display it
if (\method_exists('\App', "filter_all_output")) {
    echo \App::filter_all_output($output);
} else {
    echo $output;
}

if (ob_get_level()) {
    ob_flush();
}