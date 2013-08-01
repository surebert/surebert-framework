<?php
/**
 * Takes a sbf2 project and converts all methods and calls to them
 *  in .view and .php files in an sbf2 project and converts them to sfb3
 * camelCase
 * 
 * RUN FROM ROOT OF PROJECT in same directory as private, public, mod, etc
 * @author paul.visco@roswellpark.org
 * 
 * <code>
 * php camelize.php
 * </code>
 */
//created a unique the log name to write to
define("REPLACEMENT_LOG", "replacement.".date('m_d_y_h_i_s').".log");
//clear the log
file_put_contents(REPLACEMENT_LOG, "");

/**
 * Converts underscore based naming to camelCase
 * @param string $str
 * @return string
 */
function toCamelCase($str){
	return preg_replace_callback('/_([a-z])/', function($v){
	  return strtoupper($v[1]);
	}, strtolower($str));
}

/**
 * Writes log file of actions taken
 * @param string $file
 * @param type $found
 * @param type $replaced
 */
function writeLog($file, $found, $replaced){
	$log = PHP_EOL.$file.",".$found.",".$replaced;
	file_put_contents("php://stdout", $log);
	file_put_contents(REPLACEMENT_LOG, $log, FILE_APPEND);
}

//grab all of the php files in this directory
$directory = new RecursiveDirectoryIterator(__DIR__);
$flattened = new RecursiveIteratorIterator($directory);
//make sure it is just .php and .view files
$files = new RegexIterator($flattened, '/(php|view)$/');

//loop through files, find underscore and replace with camelCase method calls
foreach($files as $file) {
        //make sure its only in mod and private and does not include /private/resources
	if(preg_match("~./(mod|private)/~", $file) && !preg_match("~private/resources/~", $file)){
		$contents = file_get_contents($file);
		$matches_count = preg_match_all("~((?:::|\->|(?:public|private|protected)\s?(?:static)?\s?function)\s?[a-zA-Z]+_\w+?\().*?\)~", $contents, $matches);
		if($matches_count){
			$fixes = array_unique($matches[1]);
			foreach($fixes as $fix){
				$replacement = toCamelCase($fix);
				$contents = str_replace($fix, $replacement, $contents);
				writeLog($file->__toString(), $fix, $replacement);
				
			}
			
			file_put_contents($file->__toString(), $contents);
				
		}
		

	}
	
}
