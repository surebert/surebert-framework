<?php
/**
 * @package Application
 * @author paul.visco@roswellpark.org
 */
namespace sb\Application;

class Debugger
{
    /**
     * Are errors displayed
     * @var boolean
     */
    protected static $display_errors = true;

    /**
     * Determines if the trace is logged or displayed with errors
     * @var boolean
     */
    protected static $show_trace = true;

    /**
     * Converts errors into exceptions
     * @param integer $code    The error code
     * @param string  $message The error message
     * @param string  $file    The file the error occurred in
     * @param integer $line    The line the error occurred on
     */
    public static function errorHandler($code, $message, $file, $line)
    {

        if (error_reporting() === 0) {
            // This error code is not included in error_reporting
            return false;
        }
        throw new \sb\Exception($code, $message, $file, $line);
    }

    /**
     * Handles acceptions and turns them into strings
     * @param Exception $e
     */
    public static function exceptionHandler(\Exception $e)
    {

        $message = 'Code: ' . $e->getCode() . "\n" .
                'Path: ' . \sb\Gateway::$request->path . "\n" .
                'Message: ' . $e->getMessage() . "\n" .
                'Location: ' . $e->getFile() . "\n" .
                'Line: ' . $e->getLine() . "\n";

        if (self::$show_trace) {
            $message .= "Trace: \n\t" . str_replace("\n", "\n\t", \print_r($e->getTrace(), 1));
        }

        if (\method_exists("\App", "exception_handler")) {
            if (\App::exceptionHandler($e, $message) === false) {
                return false;
            }
        }

        if (\ini_get("display_errors") == true) {
            if (\sb\Gateway::$command_line) {
                \file_put_contents('php://stderr', "\n" . $message  . "\n");
            } else {
                echo '<pre style="background-color:red;padding:10px;color:#FFF;">' . $message . '</pre>';
            }
        }

        if (!isset(\App::$logger)) {
            \App::$logger = new \sb\Logger\FileSystem();
        }

        \App::$logger->exceptions($message);

    }

    /**
     * Shutdown function catches additional parse errors
     */
    public static function shutdown()
    {
        if (\is_null($e = \error_get_last()) === false) {
            self::exceptionHandler(new \sb\Exception($e['type'], $e['message'], $e['file'], $e['line']));
        }
    }
    /**
     * Sets up debugger
     * @param string  $error_reporting_level The error level like
     * @param boolean $display_errors        Should errors be dumped to output
     * @param type    $show_trace            Should trace message be shown
     */
    public static function init($error_reporting_level = E_ALL, $display_errors = true, $show_trace = true)
    {

        \error_reporting($error_reporting_level);
        \ini_set("display_errors", $display_errors ? true : false);
        self::$show_trace = $show_trace ? true : false;
        \set_error_handler('\sb\Application\Debugger::errorHandler');
        \set_exception_handler('\sb\Application\Debugger::exceptionHandler');
        \register_shutdown_function('\sb\Application\Debugger::shutdown');
    }
}

