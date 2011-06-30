<?php
/**
 * @package sb_Application
 */
class sb_Application_Debugger{

    /**
     * Converts errors into exceptions
     * @param integer $code The error code
     * @param string $message The error message
     * @param string $file The file the error occurred in
     * @param integer $line The line the error occurred on
     */
    public static function error_handler($code, $message, $file, $line) {
        throw new sb_Exception($code, $message, $file, $line);
    }

    /**
     * Handles acceptions and turns them into strings
     * @param Exception $e
     */
    public static function exception_handler(Exception $e){

        $s = Gateway::$html_errors ? '<br />' : "\n";
        $m = 'Code: '.$e->getCode()."\n".
            'Message: '.$e->getMessage()."\n".
            'Location: '.$e->getFile()."\n".
            'Line: '.$e->getLine()."\n".
            "Trace: \n\t".str_replace("\n", "\n\t",$e->getTraceAsString());

        if(Gateway::$html_errors){
			echo '<div style="background-color:red;padding:10px;color:#FFF;">'.nl2br(str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $m)).'</div>';
        } else if(Gateway::$command_line){
            file_put_contents('php://stderr', "\n".$m."\n");
        } else {
			echo "\n".$m."\n";
		}
    }

	public static function init(){
		set_error_handler('sb_Application_Debugger::error_handler');
		set_exception_handler('sb_Application_Debugger::exception_handler');

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