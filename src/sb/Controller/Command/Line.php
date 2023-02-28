<?php

/**
 * Used to create command line utilities
 *
 * @author paul.visco@roswellpark.org
 * @package Controller
 */
namespace sb\Controller\Command;

use \sb\Controller\Base as Base;

/**
 * To create you own command line logger create a controller that extends this and this you can use the $this->log method to log
 */
class Line extends Base
{

    /**
     * Background colors
     * @var array
     */
    protected  $bgcolors = [
        'black' => '40',
        'red' => '41',
        'green' => '42',
        'yellow' => '43',
        'blue' => '44',
        'magenta' => '45',
        'cyan' => '46',
        'light_gray' => '47'
    ];

    /**
     * Foreground colors
     * @var array
     */
    protected $fgcolors = [
        'black' => '0;30',
        'dark_gray' => '1;30',
        'blue' => '0;34',
        'light_blue' => '1;34',
        'green' => '0;32',
        'light_green' => '1;32',
        'cyan' => '0;36',
        'light_cyan' => '1;36',
        'red' => '0;31',
        'light_red' => '1;31',
        'purple' => '0;35',
        'light_purple' => '1;35',
        'brown' => '0;33',
        'yellow' => '1;33',
        'light_gray' => '0;37',
        'white' => '1;37'
    ];

    /**
     * The begin time of the script in order to calculate the total time required
     *
     * @var integer
     */
    protected $start_time;

    /**
     * The number of errors that occurred based on the message type error
     * @var integer
     */
    protected $number_of_errors = 0;

    /**
     * Determines if it is allowed to run from anywhere or only command line
     * @var boolean false default
     */
    protected $allow_from_anywhere = false;

    /**
     * Determines if logs get written to file on __destruct
     * @var boolean
     */
    protected $log_to_file = false;

    /**
     * If true then log messages are written to file if $log_to_file is enabled
     * @var mixed
     */
    protected $logger = null;

    /**
     * The name of the destruct log to write to
     * @var string
     */
    protected $log_name = 'commandLine';

    /**
     * If a subclass defines this attribute, it will be appended to the log_name
     */
    protected $log_suffix = null;

    /**
     * The default memory limit in mb, default 200
     * @var int
     */
    protected $memory_limit = 200;

    /**
     * The default max execution time in seconds, default 3600 - 1 hr
     * @var int
     */
    protected $max_execution_time = 3600;

    /**
     * Determines whether the process specified in a @triggers docblock tag
     * can be fired.  Defaults to false.  If the method in question has fulfilled
     * its responsibilities, it should set this to true.
     *
     * @var boolean
     */
    protected $trigger_next_process = false;


    /**
     * Blocks non command line calls unless overridden
     * @param boolean $allow_from_anywhere Allows connections from elsewhere if true
     */
    public function __construct($allow_from_anywhere = false)
    {
        if($this->log_to_file){
            $this->setLogger(new \sb\Logger\CommandLine());
        }

        $this->allow_from_anywhere = $allow_from_anywhere;
        if (!\sb\Gateway::$command_line && !$allow_from_anywhere) {
            die('You can only use this command from the terminal');
        }

        $this->start_time = microtime(true);
        $this->request_call = get_called_class().''.\sb\Gateway::$request->request;
        $this->log(date('Y/m/d H:i:s'), 'START');
        $this->log(get_called_class(), "CLASS");
        $this->log(\sb\Gateway::$request->request, 'REQUEST');
        if(count(\sb\Gateway::$request->get)){
           $this->log(http_build_query(\sb\Gateway::$request->get), 'PARAMS');
        }

        $this->setMemoryLimit($this->memory_limit);
        $this->setMaxExecutionTime($this->max_execution_time);

    }

    /**
     * Set the logger that logs the full log message stream
     * when the destructor runs
     * @param \sb\Logger\Base $logger
     */
    public function setLogger(\sb\Logger\Base $logger, $logname=''){
        $this->logger = $logger;
        $this->log_name = $logname ? $logname : get_called_class();
        if (isset($this->log_suffix)) {
            $this->log_name = $this->log_name . '_' . $this->log_suffix;
        }
        $this->log_name = preg_replace("~[^\w+]~", "_", $this->log_name);
    }

    /**
     * Sets the memory limit for the command
     * @param integer $memory_in_MB
     */
    public function setMemoryLimit($memory_in_MB = 200)
    {
        $size = $memory_in_MB . 'M';
        ini_set('memory_limit', $size);
        $this->log("limited to ". $size, "MEMORY");
    }

    /**
     * Sets the maximum execution time for the script
     * @param integer $time_in_seconds
     */
    public function setMaxExecutionTime($time_in_seconds = 3600)
    {
        ini_set('max_execution_time', $time_in_seconds);
        $this->log("limited to ". $time_in_seconds." seconds", "TIME");
    }

    /**
     * Determines the peak memory usage
     * @param boolean $peak return peak or total memory used
     * @return string The value in b, KB, or MB depending on size
     */
    protected function getMemoryUsage($peak)
    {
        if($peak){
            $mem_usage = memory_get_peak_usage(true);
        } else {
            $mem_usage = memory_get_usage(true);
        }

        $str = '';
        if ($mem_usage < 1024) {
            $str = $mem_usage . " b";
        } elseif ($mem_usage < 1048576) {
            $str = round($mem_usage / 1024, 2) . " KB";
        } else {
            $str = round($mem_usage / 1048576, 2) . " MB";
        }
        return $str;
    }

    /**
     * Logs to std out
     * @param string $message The message of the line
     * @param string $type The prefix of the line, if ERROR, encrements error count
     * @param array|string $text_attributes, used to describe how output should look
     * e.g. ['fgcolor' => 'red', 'bgcolor' => 'yellow', 'bold' => true, 'underline' => true, 'keep' => false]
     * if keep is true then it keeps the style until you call a line with another
     * style or you call $this->setNormalText()
     * if string then its just the foreground color
     */
    public function log($message, $type = "MESSAGE", $text_attributes = [])
    {

        $type = strtoupper($type);

        switch ($type) {

            case 'RAW':
                break;

            case 'ERROR':
                $this->number_of_errors++;
                $this->onError($message);
            default:
                $message = $type . ': ' . $message;
        }

        $this->logToFile($message);

        if(empty($text_attributes)){
            file_put_contents("php://stdout", "\n".$message);
            return $message;
        }

        if(is_array($text_attributes)){
            if(isset($text_attributes['bold']) && $text_attributes['bold']){
                file_put_contents("php://stdout", "\033[1m");
            }

            if(isset($text_attributes['underline']) && $text_attributes['underline']){
                file_put_contents("php://stdout","\033[4m");
            }

            if(isset($text_attributes['fgcolor']) && isset($this->fgcolors[$text_attributes['fgcolor']])){
                file_put_contents("php://stdout", "\033[".$this->fgcolors[$text_attributes['fgcolor']]."m");
            }

            if(isset($text_attributes['bgcolor']) && isset($this->fgcolors[$text_attributes['bgcolor']])){
               file_put_contents("php://stdout", "\033[".$this->bgcolors[$bgcolor]."m");
            }

            if(isset($text_attributes['underline']) && $text_attributes['underline']){
                file_put_contents("php://stdout","\033[4m");
            }

            if(isset($text_attributes['overwrite']) && $text_attributes['overwrite']){
                file_put_contents("php://stdout", "\033[2K\033[A");
            }
        } else if(is_string($text_attributes) && isset($this->fgcolors[$text_attributes])){
            file_put_contents("php://stdout", "\033[".$this->fgcolors[$text_attributes]."m");
        }


        file_put_contents("php://stdout", "\n".$message);

        if(!is_array($text_attributes) || !isset($text_attributes['keep']) || !$text_attributes['keep']){
            file_put_contents("php://stdout","\033[0m");
        }

        return $message;
    }

    /**
     * Logs to file
     * @param string $message
     * @return string
     */
    protected function logToFile($message){
        if(isset($this->logger) && $this->logger instanceof \sb\Logger\Base){
            return $this->logger->{$this->log_name}($message);
        }
    }

    /**
     * Sets the text back to normal non-colored, non-bold
     */
    public function setNormalText(){
        file_put_contents("php://stdout","\033[0m");
    }

    /**
     * Logs error to std out
     * @param string $message
     */
    protected function error($message)
    {
        $this->log($message, 'ERROR');
    }

    /**
     * Fires when the error method is called
     * @param string $message
     * @return boolean
     */
    protected function onError($message)
    {
        return true;
    }


    /**
     * Returns the name of a controller method in a format that can
     * be used in a commandline invocation.
     *
     *
     * @param string  $method_name Fully qualified name to method whose invocation is being generated
     * @param string  $http_host   --http_host arg, optional, defaults to Gateway::$http_host
     * @param array   $http_args   Query string args, optional, defaults to empty array
     * @return string The commandline invocation for the given args
     *
     * e.g. php /var/www/html/enterpriseteam/public/index.php '--request=/jobs_invision/load?doctype=obmt85'  --http_host=enterpriseteam.roswellpark.org
     *
     * Note: It is up to client code to add any bash redirects
     */
    public static function getCommandlineInvocation($method_name, $http_host=null, $http_args=[])
    {
        // Allow project-specific overrides of which php is used. This is for situations in which a project
        // is expected to run under a different version of php than the one found in the process owner's PATH.
        if (defined('PHP_EXE')) {
            $php_exe = PHP_EXE;
        } else {
            $php_exe = 'php';
        }

        $command_prefix = "$php_exe " . ROOT . "/public/index.php";

        // Match fully-qualified method name: e.g. \Foo\Controllers\Jobs\Bar::baz()
        preg_match('/Controllers.([^:]+)::([A-Za-z_]+)/', $method_name, $matches);
        if (count($matches) !== 3) {
            return '';
        }
        $class_name = strtolower($matches[1]);
        $class_name = preg_replace('/\\\/', '_', $class_name);
        $method_name = strtolower($matches[2]);

        // Build argument for --request opt
        $request_arg = "/$class_name/$method_name";
        if (!empty($http_args)) {
            $request_arg .= "?" . http_build_query($http_args);
        }

        // Build argument for --http_host opt
        if (is_null($http_host)) {
            $http_host = \sb\Gateway::$http_host;
        }

        return "$command_prefix --request='$request_arg' --http_host=$http_host";
    }


    /**
     * Throw a 404 exception when a non-existent commandline route is requested.
     */
    public function notFound()
    {
        throw new \Exception('404 Not Found');
    }


    /**
     * Calculates time and logs to the destructor log if it exists
     */
    public function __destruct()
    {
        $milliseconds = round((microtime(true) - $this->start_time) * 1000, 2);
        $this->log($this->getMemoryUsage(true), 'MEMORY PEAK');
        $this->log($this->getMemoryUsage(false), 'MEMORY TOTAL');
        $this->log($this->number_of_errors, 'ERRORS');
        $this->log($milliseconds . "ms", 'TIME_MS');
        $this->log(date('Y/m/d H:i:s') . "\n", 'END');

        // Run any subsequent processes found in the method's docblock
        if (isset($this->docblock->triggers))
        {
            if (!$this->trigger_next_process) {
                $this->log("Cannot trigger '{$this->docblock->triggers}' because trigger_next_process flag was not set to true");
            } else {
                // Forbid recursion
                if (strtolower($this->docblock->method_name) === strtolower($this->docblock->triggers)) {
                    throw new \Exception("@triggers docblock method cannot invoke itself");
                }
                $commandline_invocation = $this->getCommandlineInvocation($this->docblock->triggers);
                if ($commandline_invocation) {
                    $triggered_process = new \sb\Linux\Process($commandline_invocation);
                    if ($triggered_process->status()) {
                        $this->log("Triggering Process for: '$commandline_invocation'");
                        $this->log("Triggered process has PID: {$triggered_process->getPid()}");
                    } else {
                        throw new Exception("Failed to start configured process for command '{$method_to_trigger}'");
                    }
                }
            }
        }
    }
}
