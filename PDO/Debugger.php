<?php

/**
 * Extends native PDO class for logging and debugging
 *
 * @Author Paul Visco
 * @Version 1.1 03/31/2009 04/01/2009
 *
 */
class sb_PDO_Debugger extends sb_PDO{
	
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
        
        /*** set the error reporting attribute ***/
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	}

	/**
     * Additionally Logs the errors
     * {@inheritdoc }
     */
	public function s2o($sql, $params=null, $class_name='', $prepare_and_store=1){

        try{
            return parent::s2o($sql, $params, $class_name);
        } catch(Exception $e){
             throw(new Exception('CALLED: '.__METHOD__."(\"".$sql."\", ".(is_null($params) ? 'null' : print_r($params, 1)).", '".$class_name."');\nERROR RETURNED: ".print_r($this->errorInfo(), 1)));

        }
	}

}

?>