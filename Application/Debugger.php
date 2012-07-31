<?php
/**
 * @package Application
 * @author paul.visco@roswellpark.org
 */
namespace sb;
class Application_Debugger{
	
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
     * @param integer $code The error code
     * @param string $message The error message
     * @param string $file The file the error occurred in
     * @param integer $line The line the error occurred on
     */
    public static function error_handler($code, $message, $file, $line) {
        
        if (error_reporting() === 0) {
            // This error code is not included in error_reporting
            return false;
        }
        throw new sb_Exception($code, $message, $file, $line);
    }

    /**
     * Handles acceptions and turns them into strings
     * @param Exception $e
     */
    public static function exception_handler(Exception $e){
		
		$message = 'Code: ' . $e->getCode() . "\n" .
				'Path: ' . Gateway::$request->path . "\n" .
				'Message: ' . $e->getMessage() . "\n" .
				'Location: ' . $e->getFile() . "\n" .
				'Line: ' . $e->getLine() . "\n";

		if (self::$show_trace) {
			$message .= "Trace: \n\t" . str_replace("\n", "\n\t", print_r($e->getTrace(), 1));
		}
		
		if(method_exists("\App", "exception_handler")){
			if(\App::exception_handler($e, $message) === false){
				return false;
			}
		}
	
		if(ini_get("display_errors") == true){
			if (Gateway::$command_line) {
				file_put_contents('php://stderr', "\n" . $message  . "\n");
			} else {
				echo '<pre style="background-color:red;padding:10px;color:#FFF;">' . $message . '</pre>';
			}
		}
		
		if (!isset(\App::$logger)) {
			\App::$logger = new sb_Logger_FileSystem();
		}

		\App::$logger->exceptions($message);
      
    }

	/**
	 * Shutdown function catches additional parse errors
	 */
	public static function shutdown(){
		if(is_null($e = error_get_last()) === false){ 
			self::exception_handler(new sb_Exception($e['type'], $e['message'], $e['file'], $e['line']));
		}
	}
	/**
	 * Sets up debugger
	 * @param string $error_reporting_level The error level like
	 * @param boolean $display_errors Should errors be dumped to output
	 * @param type $show_trace Should trace message be shown
	 */
	public static function init($error_reporting_level=E_ALL, $display_errors=true, $show_trace=true){
		
		error_reporting($error_reporting_level);
		ini_set("display_errors", $display_errors ? true : false);
		self::$show_trace = $show_trace ? true : false;
		set_error_handler('sb_Application_Debugger::error_handler');
		set_exception_handler('sb_Application_Debugger::exception_handler');
		register_shutdown_function('sb_Application_Debugger::shutdown');
	}
}

/**
 * Used to throw custom exceptions
 * @author paul.visco@roswellpark.org
 * @package sb_Exception
 */
class sb_Exception extends Exception{

    private $context = null;

    public function __construct($code, $message, $file, $line, $context = null){
        parent::__construct($message, $code);
        $this->file = $file;
        $this->line = $line;
        $this->context = $context;
    }
};
?>