<?php


/**
 * Extends native PDO class for logging and debugging
 * 
 * @Author Paul Visco
 * @Version 1.8 09/27/2007 01/26/2009
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
	public function s2o($sql, $params=null, $class_name='', $prepare_and_store=1){
		
		if(is_array($params) || is_object($params)){
			
			$param = (is_array($params)) ? $params : self::o2p($params);
			
			if($prepare_and_store == 1){
				$stmt = $this->prepare_and_store($sql);
			} else {
				$stmt = $this->prepare($sql);
			}
			
			$result = $stmt->execute($params);
			if($result){
				$rows = $stmt;
			}
			
		} else {
			
			$result = $this->query($sql);
			if(is_object($result)){
				$rows = $result;
			}
			
		}
		
		if(isset($rows)){
			//if the class_name is set return instances
			if(!empty($class_name)){
				if(class_exists($class_name)){
					return $rows->fetchAll(PDO::FETCH_CLASS, $class_name);
				} else {
					trigger_error("class_name '".$class_name."' does not exist on ".__METHOD__);
				}
			} else {
				//otherwise return standard objects
				return $rows->fetchAll(PDO::FETCH_OBJ);
			}
		}
		
		return array();
	
	}

	/**
	 * Converts an object into a bound params hash
	 *
	 * @param object $obj The object to convert to a bindParams hash with the colon in front of each key so that it can be used as a bid param array
	 * @param array $omit An simple array of key names to omit from the array
	 * @return array The object as an array desigend for passing to pdo's exec or bindParams
	 * @Author Paul Visco
	 * @version 1.4
	 * @example 
	 * <code>
	 * $question = new Question();
	 * $question->qid = 1;
	 * $question->end_date = '01/22/1977';
	 * $question->start_date = '01/22/1977';
	 * $question->question = 'How old are you?';
	 * 
	 * $params = $mySb_PDo_instance->o2p($question);
	 * //returns $params as Array ( [:qid] => 1 [:question] => How old are you? [:start_date] => 01/22/1977 [:end_date] => 01/22/1977 )
	 * </code>
	 * 
	 */
	public function o2p($obj, $omit=Array()){
		
		$arr = get_object_vars($obj);
	    $params = Array();
	    
	    foreach($arr as $key=>$val){
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
	protected function prepare_and_store($sql){
		
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