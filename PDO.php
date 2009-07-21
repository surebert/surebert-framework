<?php

/**
 * Extends native PDO class for logging and debugging
 * 
 * @Author Paul Visco
 * @Version 1.9 09/27/2007 04/01/2009
 * 
 */
class sb_PDO extends PDO{
	
	/**
	 * This is an array of stored prepared sql statements for use with prepare_and_store
	 *
	 * @var array
	 */
    private $prepared_and_stored = Array();
    
	/**
	 * Creates am extended PDO object
	 *
	 * @param string $connection The pdo connection string
	 * @param string $user Username if required
	 * @param string $pass Password for connection if required
	 * 
	 * <code>
	 * $db=new sb_PDO("mysql:dbname=xxx;host=xxx", 'username', 'pass');
	 * $db=new sb_PDO("sqlite:myfile.db3');
	 * </code>
	 * 
	 */
	function __construct($connection, $user='', $pass=''){
		
		parent::__construct($connection, $user, $pass);
		
		//sets default mode to fetch obj
		$this->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

        //silence all errors in production
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
	}
	
	/**
	 * Turn a sql query into an array of objects representing each row returned
	 *
	 * @param string $sql A sql string, can be used with :prop to create a prepared statement
	 * @param Array params An array hash of values to match with properties inside prepared statement
	 * @param Object params An object whose properties match with properties inside prepared statement.
	 * @param Array class_name Here you can specify a class_name, so that rows returned are instances of that class_name instead of standard objects
	 * @return array An array of objects representing rows returned
	 * @example:
	 * <code>
	 * 
	 * //without prepared statements
	 * $results = App::$db->s2o("SELECT * FROM pages WHERE pid =1");
	 * 
	 * //with prepared statement and params array
	 * $results = App::$db->s2o("SELECT * FROM pages WHERE pid =:pid", Array(":pid"=>1));
	 * 
	 * //with prepared statement and params object
	 * $params = new stdClass();
	 * $params->pid = 1;
	 * $results = App::$db->s2o("SELECT * FROM pages WHERE pid =:pid", $params);
	 * 
	 * //with prepared statement and params array plus class_name
	 * $results = App::$db->s2o("SELECT * FROM pages WHERE pid =:pid", Array(":pid" => 1), 'Page');
	 * 
	 * * //without prepared statement and plus class_name, fetches rows from the pages table into Page instances
	 * $results = App::$db->s2o("SELECT * FROM pages", null, 'Page');
	 * 
	 * </code>
	 *
	 */
	public function s2o($sql, $params=null, $class_name=''){

        
        //if it has parameters then prepare the statement and execute with the parameters
        //stmts that have already been prepared will reuse the prepared statement
		if(is_array($params) || is_object($params)){

            //convert object parameters
			$param = is_array($params) ? $params : $this->o2p($params);
			
			$stmt = $this->prepare($sql);
			$result = $stmt->execute($params);
		
			if(!$result){
				return Array();
			}

        //if there
		} else {
			
			$result = $this->query($sql);
			if(!is_object($result)){
				return Array();
			} else {
                $stmt = $result;
            }
            
		}
		
        //if the class_name is set return instances
      
        if(substr(ltrim($sql), 0, 1) != 'S'){
            return $stmt;
        } else if(!empty($class_name)){
            if(class_exists($class_name)){
                return $stmt->fetchAll(PDO::FETCH_CLASS, $class_name);
            }
        } else {
            //otherwise return standard objects
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        }
		
	}

	/**
	 * Converts an object into a bound params hash, use sb_PDO::paramify
	 *
	 * @param object $obj The object to convert to a bindParams hash with the colon in front of each key so that it can be used as a bid param array
	 * @param array $omit An simple array of key names to omit from the array
	 * @return array The object as an array desigend for passing to pdo's exec or bindParams
	 * @Author Paul Visco
     * @Depreciated
	 * @version 1.5
	 * 
	 */
	public function o2p($obj, $omit=Array()){
		
		return self::paramify($obj, $omit);
	}

    /**
	 * Converts an array into a bound params hash
	 *
	 * @param array/object $data The object/hash to convert to a bindParams compatible hash with the colon in front of each key so that it can be used as a bid param array
	 * @param array $omit An simple array of key names to omit from the array
	 * @return array The input data as an array designed for passing to pdo's execute or bindParams
	 * @Author Paul Visco
	 * @version 1.0
	 * @example
	 * <code>
	 * $question = new Question();
	 * $question->qid = 1;
	 * $question->end_date = '01/22/1977';
	 * $question->start_date = '01/22/1977';
	 * $question->question = 'How old are you?';
	 *
	 * $params = sb_PDO::paramify($question);
	 * //returns $params as Array ( [:qid] => 1 [:question] => How old are you? [:start_date] => 01/22/1977 [:end_date] => 01/22/1977 )
	 *
     * $this->request->post = Array('name' => paul, 'color' => 'red');
     * $params = sb_PDO::paramify($this->request->post);
     *
     * </code>
	 *
	 */
	public static function paramify($data, $omit=Array()){
	    $params = Array();
        if(is_object($data)){
            $data = get_object_vars($data);
        }

        if(!is_array($data)){
            throw(new sb_PDO_Exception('Paramify only accepts hashes and objects as data argument'));
        }

	    foreach($data as $key=>$val){
	    	if(!in_array($key, $omit)){
	        	$params[':'.$key] = $val;
	    	}
	    }

	    return $params;
	}
	
	/**
	 * Used to prepare and store sql statements for value binding
	 *
	 * @param string $sql
	 * @return PDO_Statement A PDO_statment instance
	 */
	public function prepare($sql){
		
		$md5 = md5($sql);
		
		if(isset($this->prepared_sql[$md5])){
			return $this->prepared_sql[$md5];
		}
		
		$stmt = parent::prepare($sql);
		$this->prepared_sql[$md5] = $stmt;
		return $stmt;
	}
	
}

?>