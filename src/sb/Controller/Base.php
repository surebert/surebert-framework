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
     * The parsed docblock tags
     * @var stdClass
     */
    protected $docblock = null;

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
        
        //return whatever notFound has as long as it is a string
        $result = $this->notFound();
        
        if(!is_null($result) && !is_string($result) && !is_numeric($result)){
            throw(new \Exception(ucfirst(gettype($result))." returned where string expected. You must return a string from \\".get_called_class().'->notFound().'));
        }
        return $result;
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

            // Parse docblock
            $reflection = new \ReflectionMethod($this, $method);
            $fully_qualified_method_name = implode('::', [$reflection->class, $reflection->name]);
            $this->docblock = $this->parseDocblock($reflection->getDocComment(), $fully_qualified_method_name);

            // Override defaults with docblock values if present
            $servable       = $this->docblock->servable       ?? $servable;
            $input_as_array = $this->docblock->input_as_array ?? $input_as_array;
            $http_method    = $this->docblock->http_method    ?? $http_method;

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
     * Checks to see if a view files exists by path
     * @param string $view_path e.g. /user/profile
     * @return mixed Path to view file or false
     */
    protected function viewExists($view){
        $view_path = ltrim($view, '/');
        $view_file = ROOT . '/private/views/' . $view_path . '.view';
        $exists = is_file($view_file);
        
        if(!$exists){
            foreach (\sb\Gateway::$mods as $mod) {
                $m = ROOT . '/mod/' . $mod . '/views/' . $view_path . '.view';
                if (\is_file($m)) {
                    $exists = true;
                    $view_file = $m;
                    break;
                }
            }
        }
        
        return $exists ? $view_file : false;
        
    }
    
    /**
     * Renders the actual .view template
     * @param string $view_path The path to the template e.g. /blah/foo
     * @param mixed $extact_vars extracts the keys of an object or array into
     * local variables in the view
     * @return string
     */
    protected function getView($view_path, $extract_vars = null)
    {
        //putting vars out of the way to not conflict with extracted vars
        $___view_file = $this->viewExists($view_path);
        unset($view_path);
        if(!is_file($___view_file)){
            return false;
        }
        
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

        include($___view_file);
        return true;
    }

    /**
     * Include an arbitrary .view template within the $this of the view
     * @param string $view_path  e.g. /interface/cp
     * @param mixed $extact_vars extracts the keys of an object or array into
     * local variables in the view
     */
    public function renderView($view_path, $extract_vars = null)
    {

        //capture view to buffer
        ob_start();
        
        if(!$this->viewExists($view_path)){
            throw(new \Exception("Cannot find view to render in ".\sb\Gateway::getCallingMethod()." \$this->renderView('".$view_path."')"));
        }
        
        $this->getView($view_path, $extract_vars);
        
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
    
    /**
     * Gets a args variable value or returns the default value (null unless overridden)
     * @param integer $arg_num The numeric arg value
     * @param mixed $default_val null by default
     * @return mixed string value or null
     */
    public function getArg($arg_num, $default_val = null)
    {
        return $this->request->getArg($arg_num, $default_val);
    }
    
    /**
     * Gets a get variable value or returns the default value (null unless overridden)
     * @param string $key The $_GET var key to look for
     * @param mixed $default_val null by default
     * @return mixed string value or null
     */
    public function getGet($key, $default_val = null)
    {
        return $this->request->getGet($key, $default_val);
    }

    /**
     * Parses docblock tags.  Each known docblock tag '@tagname <tagval>'
     * becomes a property of $this->docblock such that the following holds:
     *     $this->docblock->tagname == <tagval>
     *
     * @var string $docblock_text
     * @var string $method_name  Name of method whose docblock is being parsed
     */
    public function parseDocblock(string $docblock_text, string $method_name)
    {
        $docblock_obj = new \stdClass();

        $docblock_obj->method_name = $method_name;

        // Return empty object if given empty input
        if (empty($docblock_text)) {
            return $docblock_obj;
        }

        // Note that the tag name must be the first capture
        $tag_handlers = [
            "~@(http_method) (get|post)~" => function ($match) {
                return [ 'tagname' => $match[1], 'tagval' => $match[2] ];
            },
            "~@(input_as_array) (true|false)~" => function ($match) {
                return [ 'tagname' => $match[1], 'tagval' => $match[2] == 'true' ? true : false ];
            },
            "~@(servable) (true|false)~" => function ($match) {
                return [ 'tagname' => $match[1], 'tagval' => $match[2] == 'true' ? true : false ];
            },
            "~@(triggers) (.*)~" => function ($match) {
                return [ 'tagname' => $match[1], 'tagval' => trim($match[2]) ];
            },
        ];

        // For each tag regex, invoke its corresponding handler
        foreach ($tag_handlers as $pattern => $handler)
        {
            if (preg_match($pattern, $docblock_text, $match))
            {
                // Invoke handler
                $parsed_tag = $handler($match);

                // Set dynamic field to handler return val
                $docblock_obj->{$parsed_tag['tagname']} = $parsed_tag['tagval'];
            }
        }

        return $docblock_obj;
    }

}
