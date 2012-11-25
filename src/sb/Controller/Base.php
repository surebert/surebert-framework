<?php
/**
 * Loads the .view file that corresponds to the request
 * @author paul.visco@roswellpark.org
 * @package Controller
 */
namespace sb\Controller;

class Base
{

    /**
     * Set to true if the view is loaded from within another view via \sb\Gateway::render_request, otherwise false
     *
     * @var boolean
     */
    public $included = false;

    /**
     * The requested data and input
     * @var \sb\Request
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
     * Determines which file is loaded in the view directory if one is not
     * specified.  When not set renders index.view.  To set just use template
     *  name minus the .view extension e.g. $this->default-file = index;
     *
     * @var string
     */
    protected $default_file = 'index';

    /**
     * An array of REGex to callable that routes requests that are not otherwise servable
     * @var array
     */
    public $routing_patterns = Array();

    /**
     * Filters the output after the view is rendered but before
     * it is displayed so that you can filter the output
     *
     * @param $output
     * @return string
     */
    public function filterOutput($output)
    {
        return $output;
    }

    /**
     * Sets the request paramter
     *
     * @param sb\Request $request The request instance fed to the view
     */
    final public function setRequest(\sb\Request $request)
    {

        $this->request = $request;

        $request->setInputArgsDelimiter($this->input_args_delimiter);
    }

    /**
     * Fires before any response is rendered by he controller allowing you to
     * make decisions, check input args, etc before the output is rendered.
     * If you return false from this method then no output is rendered.
     * @return boolean determines if anything should render anything or not,
     * false == no render
     */
    public function onBeforeRender($method = '')
    {
        return true;
    }

    /**
     * Render the response based on the request
     *
     * I had to remove default arguments in order to get around issue with incompatbile
     * child class methods when E_STRICT is enabled.  I am keeping them in phpdoc
     *
     * @param String $template    the template to use e.g. /dance
     * @param mixed  $extact_vars extracts the keys of an object or array into
     * local variables in the view
     *
     */
    public function render()
    {

        $args = func_get_args();
        $template = isset($args[0]) ? $args[0] : '';
        $extract_vars = isset($args[1]) ? $args[1] : null;

        $output = '';

        //capture view to buffer
        ob_start();
        //if no method is set, use index, for the IndexController that would be request path array 0

        if (get_class($this) == 'IndexController') {
            $method = !empty($this->request->path_array[0]) ? $this->request->path_array[0] : $this->default_file;
        } else {
            $method = isset($this->request->path_array[1]) ? $this->request->path_array[1] : $this->default_file;
        }

        //return the servable method
        $response = self::processControllerMethod($this, $method);

        if ($response['exists']) {
            return $response['data'];
        }

        //if no matching controller and direct view rendering is allowed
        if (\sb\Gateway::$allow_direct_view_rendering) {
            //set default path
            $path = $this->request->path;

            //if there is a template render that
            if (!empty($template)) {

                if (isset($this->request->path_array[1])) {
                    $path = preg_replace("~/" . $this->request->path_array[1] . "$~", $template, $path);
                } else {
                    $path .= $template;
                }
            } elseif (isset($this->request->path_array[1])) {

                $template = $this->request->path_array[1];
            } else {
                $path .= '/'.$this->default_file;
            }

            $this->template = $template;

            if ($this->getView($path, $extract_vars)) {
                $output = ob_get_clean();

                return $this->filterOutput($output);
            }
        }

        if (isset($this->routing_patterns)) {
            foreach ($this->routing_patterns as $pattern => $method) {
                if (preg_match($pattern, Gateway::$request->request)) {
                    if (is_callable($method)) {
                        return $this->filterOutput(call_user_func($method, $pattern));
                    } elseif (is_string($method) && is_callable(Array($this, $method))) {
                        return $this->filterOutput($this->$method($pattern));
                    }

                }
            }
        }

        $this->notFound();
    }

  
    /**
     * Processes the controller method being served
     * @param String $class  The class of the controller
     * @param string $method The method to be fired for service
     * @return array and array with properties showing if it was served and the data
     * that resulted from firing 
     */
    protected static function processControllerMethod($class, $method)
    {

        $servable = false;
        $http_method = 'post';
        $input_as_array = false;
        $args = Array();

        if (method_exists($class, 'onBeforeRender')) {
            if ($class->onBeforeRender($method) === false) {
                return Array('exists' => true, 'data' => false);
            }
        }

        $method = \sb\Gateway::toCamelCase($method);
        
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
        } elseif (method_exists($class, '__call')) {
            $servable = true;
        }

        if ($servable) {

            if ($input_as_array) {

                $data = $class->$method($args);
            } else {

                $data = call_user_func_array(array($class, $method), array_values($args));
            }

            return Array('exists' => true, 'data' => $class->filterOutput($data));
        }

        return Array('exists' => false, 'data' => false);
    }

    /**
     * Renders the actual .view template
     * @param string $view_path   The path to the template e.g. /blah/foo
     * @param mixed  $extact_vars extracts the keys of an object or array into
     * local variables in the view
     * @return string
     */
    protected function getView($_view_path, $extract_vars = null)
    {
        //extract class vars to local vars for view
        if ($this->extract) {
            extract(get_object_vars($this));
        }

        if (!is_null($extract_vars)) {
            if (is_object($extract_vars)) {
                $extract_vars = get_object_vars($extract_vars);
            }
            if (is_array($extract_vars)) {
                extract($extract_vars);
            }
        }

        $_pwd = ROOT . '/private/views/' . $_view_path . '.view';

        if (!is_file($_pwd)) {
            $_pwd = false;
            foreach (\sb\Gateway::$mods as $mod) {
                $m = ROOT . '/mod/' . $mod . '/views/' . $view_path . '.view';
                if (is_file($m)) {
                    $_pwd = $m;
                    break;
                }
            }
        }

        if ($_pwd) {
            require($_pwd);

            return true;
        }

        return false;
    }

    /**
     * Include an arbitrary .view template within the $this of the view
     * @param string $view_path   e.g. .interface/cp
     * @param mixed  $extact_vars extracts the keys of an object or array into
     * local variables in the view
     */
    public function renderView($path, $extract_vars = null)
    {

        //capture view to buffer
        ob_start();

        $this->getView($path, $extract_vars);

        return ob_get_clean();
    }

    /**
     * Default request not fullfilled
     */
    public function notFound()
    {

        $file = ROOT . '/private/views/errors/404.view';
        if (is_file($file)) {
            include_once($file);
        } else {
            header("HTTP/1.0 404 Not Found");
        }
    }
}

