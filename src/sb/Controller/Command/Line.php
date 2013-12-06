<?php

/**
 * Used to create command line utilities
 *
 * @author paul.visco@roswellpark.org
 * @package Controller
 */
namespace sb\Controller\Command;

use \sb\Controller\Base as Base;

class Line extends Base
{

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
     * If true then log messages are written to file at the end
     * @var mixed 
     */
    protected $destruct_logger = null;
    
    /**
     * An array of log messages to write to file if save_log_to_file is enabled
     * @var false 
     */
    protected $destruct_logs = Array();
    
    /**
     * The name of the destruct log to write to
     * @var string 
     */
    protected $destruct_log_name = 'commandLine';
    
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
     * Blocks non command line calls unless overridden
     * @param boolean $allow_from_anywhere Allows connections from elsewhere if true
     */
    public function __construct($allow_from_anywhere = false)
    {
        if($this->log_to_file){
            $this->setDestructLogger(new \sb\Logger\FileSystem());
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
    public function setDestructLogger(\sb\Logger\Base $logger, $logname=''){
        $this->destruct_logger = $logger;
        $this->destruct_log_name = $logname ? $logname : get_called_class();
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
     * @param string $message
     */
    protected function log($message, $type = "MESSAGE")
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
        
        file_put_contents("php://stdout", "\n".$message);

        if($this->destruct_logger){
            $this->destruct_logs[] = $message;
        }
        return $message;
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
        
        if($this->destruct_logger){
            $method = preg_replace("~[^\w+]~", "_", $this->destruct_log_name);
            $this->destruct_logger->{$method}(implode("\n", $this->destruct_logs));
        }
    }
}
