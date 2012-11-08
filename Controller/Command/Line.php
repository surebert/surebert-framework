<?php

/**
 * Used to create command line utilities
 * 
 * @author paul.visco@roswellpark.org
 * @package Controller
 */
namespace sb\Controller\Command;

use \sb\Controller\Command\Base as Base;

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
     * Blocks non command line calls unless overridden
     * @param boolean $allow_from_anywhere Allows connections from elsewhere if true
     */
    public function __construct($allow_from_anywhere = false)
    {
        $this->allow_from_anywhere = $allow_from_anywhere;
        if (!Gateway::$command_line && !$allow_from_anywhere) {
            die('You can only use this command from the terminal');
        }

        $this->start();
    }

    /**
     * Sets teh start time etc,
     */
    protected function start()
    {
        $this->start_time = microtime(true);
        $this->log(date('Y/m/d H:i:s') . " - Begin Process " . get_called_class());

        $this->setMemoryLimit();
        $this->setMaxExecutionTime();

        if (method_exists($this, 'on_start')) {
            $this->on_start();
        }
    }

    /**
     * Sets the memory limit for the command
     * @param integer $memory_in_MB
     */
    public function setMemoryLimit($memory_in_MB = 200)
    {
        ini_set('memory_limit', $memory_in_MB . 'M');
    }

    /**
     * Sets the maximum execution time for the script
     * @param integer $time_in_seconds
     */
    public function setMaxExecutionTime($time_in_seconds = 3600)
    {
        ini_set('max_execution_time', $time_in_seconds);
    }

    /**
     * Determines the peak memory usage
     * @return string The value in b, KB, or MB depending on size
     */
    protected function getMemoryUsage()
    {
        $mem_usage = memory_get_peak_usage(true);
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
            default:
                $message = "\n" . $type . ': ' . $message;
        }
        file_put_contents("php://stdout", $message);

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

    protected function onError($message)
    {
        return true;
    }

    /**
     * Calculates time
     */
    public function __destruct()
    {
        $milliseconds = round((microtime(true) - $this->start_time) * 1000, 2);
        $this->log('PEAK MEMORY USAGE: ' . $this->getMemoryUsage(), 'MESSAGE');
        $this->log('TOTAL ERRORS: ' . $this->number_of_errors, 'MESSAGE');
        $this->log('TOTAL TIME REQUIRED: ' . $milliseconds . "ms", 'MESSAGE');

        $this->log(date('Y/m/d H:i:s') . " - End Log\n", 'MESSAGE');
    }
}

