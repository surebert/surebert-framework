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
ob_start();

//script start time
$sb_start = microtime(true);

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
	 * Turn data into an appropriate json AJAX response
	 * @param mixed $data
	 */
	public function json_encode($data) {
		$response = new Ajax\Response();
		$response->set_content($data);
		$response->dispatch();
	}

	/**
	 * Loads a view for rendering
	 * @param mixed $request Either an instance of Request or a string with the path to the view e.g. /user/run
	 * @return string The rendered view data
	 */
	public static function render_request($request, $included = true) {

		if ($request instanceof Request && method_exists('\App', 'filter_all_input')) {

			\App::filter_all_input($request->get);
			\App::filter_all_input($request->post);
		} else if (is_string($request)) {
			$request = new Request($request);
		}

		if (!$request instanceof Request) {
			trigger_error('$request must be a \sb\Request instance');
		}

		$controller = $request->path_array[0];

		if (empty($controller)) {
			$controller_class = Gateway::$default_controller_type;
		} else {
			$controller_class = str_replace(' ', '_', ucwords(str_replace('_', ' ', $controller))) . 'Controller';

			$path = str_replace('_', '/', $controller_class) . '.php';

			if (!is_file(ROOT . '/private/controllers/' . $path)) {

				if ($controller == 'surebert') {
					$controller_class = '\sb\Controller_Toolkit';
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

		//$controller = new $controller_class();

		$controller->included = $included;
		if (!$included) {
			Gateway::$controller = $controller;
		}
		if ($request != Gateway::$request) {
			$request->get = array_merge(Gateway::$request->get, $request->get);
		}

		if (!$controller instanceof Controller) {
			
			throw new \Exception("Your custom controller " . $controller_class . " must extend \sb\Controller");
		}

		$controller->set_request($request);
		return $controller->render();
	}

	public static function process_controller_method($class, $method) {

		$servable = false;
		$http_method = 'post';
		$input_as_array = false;
		$args = Array();

		if (method_exists($class, 'on_before_render')) {
			if ($class->on_before_render($method) === false) {
				return Array('exists' => true, 'data' => false);
			}
		}

		if (method_exists($class, $method)) {
			$reflection = new \ReflectionMethod($class, $method);

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
			return Array('exists' => true, 'data' => $class->filter_output($data));
		}

		return Array('exists' => false, 'data' => false);
	}

	/**
	 * Autoloads classes from the _classes folder when they are instantiated so that the defintions of the classes never need to be manually included
	 *
	 * @param string $class_name
	 */
	public static function sb_autoload($class_name) {

		if(strstr($class_name, "\\")){
			
			 $class_name = ltrim($class_name, '\\');
			$fileName  = '';
			$namespace = '';
			if ($lastNsPos = strripos($class_name, '\\')) {
				$namespace = substr($class_name, 0, $lastNsPos);
				$class_name = substr($class_name, $lastNsPos + 1);
				$fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
			}
			$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $class_name) . '.php';

			$fileName = ROOT.'/vendor/'.$fileName;
		
			require $fileName;
		} else if (preg_match('~Controller$~', $class_name)) {
			$f = ROOT . '/private/controllers/' . $class_name . '.php';
			if (is_file($f)) {
				require($f);
			} else {
				foreach (Gateway::$mods as $mod) {
					$f = ROOT . '/mod/' . $mod . '/controllers/' . $class_name . '.php';
					if (is_file($f)) {
						require($f);
					}
				}
			}
		}
		
	
	return;
		$class_name = str_replace('\\', '_', $class_name);
		
		$class_name = str_replace('_', '/', $class_name);
		echo $class_name;
		return;
		if (substr($class_name, 0, 3) == 'sb/') {
			$class_name = substr_replace($class_name, "", 0, 3);
			require(SUREBERT_FRAMEWORK_SB_PATH . '/' . $class_name . '.php');
		} else if (substr($class_name, 0, 3) == 'rp/') {
			$class_name = substr_replace($class_name, "", 0, 3);
			require(SUREBERT_FRAMEWORK_RP_PATH . '/' . $class_name . '.php');
		} else if (preg_match('~Controller$~', $class_name)) {
			$f = ROOT . '/private/controllers/' . $class_name . '.php';
			if (is_file($f)) {
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
		} else if (file_exists(ROOT . '/private/resources/' . $class_name . '.php')) {
			require(ROOT . '/private/resources/' . $class_name . '.php');
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
		spl_autoload_register("sb\Gateway::sb_autoload");

		self::$remote_addr = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : self::$remote_addr;

		self::$agent = (isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT'] != 'command line') ? $_SERVER['HTTP_USER_AGENT'] : self::$agent;

		if (isset(Gateway::$cmd_options['http_host'])) {
			self::$http_host = Gateway::$cmd_options['http_host'];
		} else {
			self::$http_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : self::$http_host;
		}

		self::$request_method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : self::$request_method;

		if (method_exists('App', 'filter_all_input')) {
			\App::filter_all_input($_POST);
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

if (defined('REQUEST_URI')) {
	$request = REQUEST_URI;
} else if (isset($_SERVER['REQUEST_URI'])) {
	$request = $_SERVER['REQUEST_URI'];
}

//require the App class for static global vars
Gateway::file_require('/private/config/App.php');

//initialize the gateway
Gateway::init();

Gateway::$start_time = $sb_start;

$output = '';
if ($request) {

	//set the main request and filter the input if required
	Gateway::$request = new Request($request);
}

//include site based definitions/global functions
Gateway::file_require('/private/config/definitions.php');

if ($request) {
	//render the main request
	$output = Gateway::render_request(Gateway::$request, false);
	unset($request);
}

if (isset(Gateway::$cmd_options)) {
	if (isset(Gateway::$cmd_options['install'])) {
		require_once(ROOT . '/mod/' . Gateway::$cmd_options['install'] . '/install.php');
	} else if (isset(Gateway::$cmd_options['uninstall'])) {
		require_once(ROOT . '/mod/' . Gateway::$cmd_options['uninstall'] . '/uninstall.php');
	}
}

//filter the output if required and display it
if (method_exists('\App', "filter_all_output")) {
	echo \App::filter_all_output($output);
} else {
	echo $output;
}

if (ob_get_level()) {
	ob_flush();
}

?>