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
    public $routes = [];
    
    /**
     * Any rendering errors that are found
     * @var array
     */
    public $render_errors = [];

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
    public function onBeforeRender($request = '')
    {

        return true;
    }

    /**
     * Render the response based on the request
     *
     * I had to remove default arguments in order to get around issue with incompatbile
     * child class methods when E_STRICT is enabled.  I am keeping them in phpdoc
     *
     * @param String $template the template to use e.g. /dance
     * @param mixed $extact_vars extracts the keys of an object or array into
     * local variables in the view
     *
     */
    public function render()
    {

        $args = \func_get_args();
        $template = isset($args[0]) ? $args[0] : '';
        $extract_vars = isset($args[1]) ? $args[1] : null;

        $output = '';

        //capture view to buffer
        ob_start();
        
        $is_index_controller = \get_called_class() == 'Controllers\Index';
        
        //if routing is set for the controller and no matching method was found
        if ($is_index_controller && isset($this->routes)) {
            $routes_response = $this->processRoutes();
            if($routes_response['exists']){
                return $routes_response['data'] === false ? $this->notFound() : $routes_response['data'];
            }
        }
        
        //if no method is set, use index
        if ($is_index_controller) {
            $method = !empty($this->request->path_array[0]) ? $this->request->path_array[0] : $this->default_file;
        } else {
            $method = isset($this->request->path_array[1]) ? $this->request->path_array[1] : $this->default_file;
        }

        //return the servable method
        $response = $this->processControllerMethod($method);

        //if there is a response, return that
        if ($response['exists']) {
            return $response['data'];
        }

        //if direct view rendering is allowed
        //use controller from first part of URL or default index controller and 
        //render the view using it as the implied controller
        if (\sb\Gateway::$allow_direct_view_rendering) {
            $direct_view_rendering_output = $this->processDirectViewRendering($template, $extract_vars);
            if($direct_view_rendering_output !== false){
                return $direct_view_rendering_output;
            }
        }

        $this->notFound();
    }
    
    /**
     * If \sb\Gateway::$allow_direct_view_rendering is set to true
     * Then you can render /some/thing as /some/thing.view rendered through implied
     * controller of \Controllers\Index
     * @param String $template The template file to use
     * @param Array $extract_vars any extract variables to pass along
     * @return boolean
     */
    protected function processDirectViewRendering($template, $extract_vars=[]){
        //set default path
        $path = $this->request->path;

        //if there is a template render that
        if (!empty($template)) {

            if (isset($this->request->path_array[1])) {
                $path = \preg_replace("~/" . $this->request->path_array[1] . "$~", $template, $path);
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
            $output = \ob_get_clean();
            return $this->filterOutput($output);
        }
        
        return false;
    }
    
    /**
     * Returns the calling function through a backtrace
     * used for error reporting
     */
    public function getCallingMethod() {
        // a funciton x has called a function y which called this
        // see stackoverflow.com/questions/190421
        $bt = debug_backtrace();
        $caller = $bt[2];
        
        $c = '';
        if (isset($caller['class'])) {
            $c = $caller['class'] . '::';
        }
        if (isset($caller['object'])) {
            $c = get_class($caller['object']) . '->';
        }

        return $c.$caller['function'] . '()';
    }

    /**
     * Processes any routes when matching controller methods do not exist
     * @return boolean
     * @throws type
     */
    protected function processRoutes(){
        
        //determine if we should give feedback about routes not matching
        $debug_routes = isset($this->routes_debug) && $this->routes_debug == true;
        
        //loop through route options
        foreach ($this->routes as $http_methods => $routes) {

            //limit the routes to any HTML methods defined or any if *
            if($http_methods != '*' && !in_array($this->request->method, explode(',', $http_methods))){
                continue;
            }

            //for each one of the routes definedc check to see if the pattern matches the request
            foreach($routes as $pattern=>$callable){

                //define the pattern to match
                $pattern = preg_replace("/:\w+/", "([^".$this->input_args_delimiter."]+)", $pattern);

                if(\preg_match('#'.$pattern.'#', $this->request->request, $matches)){

                    //if the callable is a string
                    if (\is_string($callable) && strstr($callable, '@')){

                        //check to see if the current controller has a matching method name
                        $parts = explode("@", $callable);
                        if(count($parts) > 1){

                            //instantiate the class to be called if it is not already $this class
                            if($parts[0] == 'this'){
                                $class = $this;
                            } else if(class_exists($parts[0])){
                                $class = new $parts[0];
                            }
                            
                            //set the request equal to the current request
                            $class->request = $this->request;

                            if($class->onBeforeRender($pattern) === false){
                                return Array('exists' => true, 'data' => false);
                            }
                            
                            //set the callable to the class and method defined
                            $callable = Array($class, $parts[1]); 
                        }
                    }

                    if(is_callable($callable)){
                        $data = $this->filterOutput(\call_user_func_array($callable, array_slice($matches, 1, count($matches))));
                        return Array('exists' => true, 'data' => $data);
                    } else {
                        throw(new \Exception("Routing callable for pattern '".$pattern."' using httpd methods ".$http_methods." on ".  get_called_class()."->route() is not callable"));

                    }

                }
                
            }
        }
        
        return Array('exists' => false, 'data' => false);
    }

    /**
     * Excutes the controller method that matches the request
     * @param string $method The method that matches the request
     * @return type
     */
    protected function processControllerMethod($method)
    {

        $servable = false;
        $http_method = 'post';
        $input_as_array = false;
        $args = Array();

        if (method_exists($this, 'onBeforeRender')) {
            if ($this->onBeforeRender($method) === false) {
                return Array('exists' => true, 'data' => false);
            }
        }
        
        if (!method_exists($this, $method)) {
            $method = \sb\Gateway::toCamelCase($method);
        }

        if (method_exists($this, $method)) {

            $reflection = new \ReflectionMethod($this, $method);

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
            if (!isset($this->request)) {
                $this->request = \sb\Gateway::$request;
            }

            $args = $this->request->{$http_method};

            //pass thru input filter if it exists
            if (\method_exists($this, 'filterInput')) {
                $args = $this->filterInput($args);
            }
        } elseif (\method_exists($this, '__call')) {
            $servable = true;
        }

        if ($servable) {

            if ($input_as_array) {

                $data = $this->$method($args);
            } else {

                $data = \call_user_func_array(array($this, $method), array_values($args));
            }
            return Array('exists' => true, 'data' => $this->filterOutput($data));
        }

        return Array('exists' => false, 'data' => false);
    }

    /**
     * Renders the actual .view template
     * @param string $view_path The path to the template e.g. /blah/foo
     * @param mixed $extact_vars extracts the keys of an object or array into
     * local variables in the view
     * @return string
     */
    protected function getView($_view_path, $extract_vars = null, $from_render_view=false)
    {
        //extract class vars to local vars for view
        if ($this->extract) {
            \extract(\get_object_vars($this));
        }

        if (!\is_null($extract_vars)) {
            if (\is_object($extract_vars)) {
                $extract_vars = \get_object_vars($extract_vars);
            }
            if (\is_array($extract_vars)) {
                \extract($extract_vars);
            }
        }

        $_view_path = ltrim($_view_path, '/');
        $_pwd = ROOT . '/private/views/' . $_view_path . '.view';

        if (!\is_file($_pwd)) {
            foreach (\sb\Gateway::$mods as $mod) {
                $m = ROOT . '/mod/' . $mod . '/views/' . $_view_path . '.view';
                if (\is_file($m)) {
                    $_pwd = $m;
                    break;
                }
            }
        }

        if (is_file($_pwd)) {
            require($_pwd);
            return true;
        } else {
            $this->__sb_cannot_get_view = true;
        }
        return false;
    }

    /**
     * Include an arbitrary .view template within the $this of the view
     * @param string $view_path  e.g. .interface/cp
     * @param mixed $extact_vars extracts the keys of an object or array into
     * local variables in the view
     */
    public function renderView($path, $extract_vars = null)
    {

        //capture view to buffer
        ob_start();
        
        $this->getView($path, $extract_vars);
        if(isset($this->__sb_cannot_get_view)){
            unset($this->__sb_cannot_get_view);
            throw(new \Exception("Cannot find view to render in ".$this->getCallingMethod()." \$this->renderView('".$path."')"));
        }
        return \ob_get_clean();
    }

    /**
     * Default request not fullfilled
     */
    public function notFound()
    {

        $file = ROOT . '/private/views/errors/404.view';
        if (\is_file($file)) {
            include_once($file);
        } else {
            \header("HTTP/1.0 404 Not Found");
        }
    }
    
    /**
     * Grabs the part of the path referenced by index
     * e.g. if path was /image/of/dog $this->getPath(0) would return image
     * @param int $part optionally which part to return
     * @return mixed string or false if not set
     */
    public function getPath($part=null){
        
        return $this->request->getPath($part);
    }
}
