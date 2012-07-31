<?php
/**
 * Extends native PDOStatement class for logging and debugging, when the logging is set on an \sb\PDO instance.  You would never access this directly
 *
 * @author paul.visco@roswellpark.org
 * @package PDO
 */
namespace sb;
class PDO_Statement_Logger extends \PDOStatement {

    public $connection;

    protected function __construct($connection='') {

        $this->connection = $connection;
    }

    /**
     * This function extends PDOStatement->execute in order to include logging
     *
     * @param unknown_type $arr
     * @return unknown
     */

    /**
     * Extends PDOStatement::execute in order to include logging
     * @param array $bound_input_params input params to bind Array(':myparam' => 1)
     * @return boolean
     */
    public function execute($bound_input_params = Array()) {
        $log = "Executing: ".$this->queryString;

        if(count($bound_input_params)>0) {

            foreach($bound_input_params as $key=>$val) {
                $log .= "\nBinding Values: ".$key.' = '.$val;
            }

        }

        $this->connection->write_to_log($log);
        return parent::execute($bound_input_params);
    }

    /**
     * Extends PDOStatement::bindParam in order to include logging
     * @param mixed $paramno
     * @param <type> $param
     * @param <type> $type
     * @param <type> $maxlen
     * @param <type> $driverdata
     * @return boolean
     */
    public function bindParam($paramno, &$param, $type=null, $maxlen=null, $driverdata=null) {
        $log = 'Binding Parameters: '.$paramno.'='.$param;
        if(!is_null($type)) {
            $log .= '| Type: '.$type;
        }
        if(!is_null($maxlen)) {
            $log .= '| Maxlen: '.$maxlen;
        }

        if(!is_null($driverdata)) {
            $log .= '| DriverData: '.$driverdata;
        }
        $this->connection->write_to_log($log);

        if(!empty($type)) {
            return parent::bindParam($paramno, $param, $type, $maxlen, $driverdata);
        }

        return parent::bindParam($key, $val);

    }

    /**
     * Extends PDOStatement::bindColumn in order to include logging
     * @param <type> $column
     * @param <type> $param
     * @param <type> $type
     * @param <type> $maxlen
     * @param <type> $driverdata
     * @return <type>
     */
    public function bindColumn($column, &$param, $type=null, $maxlen=null, $driverdata=null) {
        $log = 'Binding Column: '.$column.'='.$param;
        if(!is_null($type)) {
            $log .= '| Type: '.$type;
        }

        if(!is_null($maxlen)) {
            $log .= '| Maxlen: '.$maxlen;
        }

        if(!is_null($driverdata)) {
            $log .= '| DriverData: '.$driverdata;
        }
        $this->connection->write_to_log($log);

        if(!empty($type)) {
            return parent::bindColumn($column, $param, $type, $maxlen, $driverdata);
        }

        return parent::bindParam($key, $val);

    }

    /**
     * Extends PDOStatement::bindValue in order to include logging
     * @param <type> $column
     * @param <type> $param
     * @param <type> $type
     * @param <type> $maxlen
     * @param <type> $driverdata
     * @return <type>
     */
    public function bindValue($paramno, $param, $type=null) {
        $log = 'Binding Value: '.$paramno.'='.$param;
        if(!is_null($type)) {
            $log .= '| Type: '.$type;
        }

        $this->connection->write_to_log($log);

        return parent::bindParam($paramno, $param, $type);

    }

}

/**
 * Extends native PDO class for logging and debugging
 *
 * @author paul.visco@roswellpark.org
 * @package PDO
 *
 */
class PDO_Logger extends PDO_Debugger {

    /**
     * An instance of \sb\Logger_Base used to do the logging
     * @var \sb\Logger_Base
     */
    private $logger = null;

    /**
     * Limits the logging to requests from one sbf session ID
     * @var string
     */
    private $sbf_id = '';

    /**
     * Creates am extended PDO object that logs
     *
     * @param string $connection The pdo connection string
     * @param string $user Username if required
     * @param string $pass Password for connection if required
     *
     * <code>
     * $db=new  \sb\PDO_Logger("mysql:dbname=xxx;host=xxx", 'username', 'pass');
     * $db=new \sb\PDO_Logger("sqlite:myfile.db3');
     * $db->set_logger(App::$logger);
     * </code>
     *
     */
    function __construct($connection, $user='', $pass='') {

        $this->log_str = str_replace(Array(":", ";", "=", "."), "_", $connection);
        parent::__construct($connection, $user, $pass);
        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, Array('sb_PDO_Statement_Logger', Array($this)));
    }

    /**
     * Set the logger
     *
     * @param $logger \sb\Logger_Base An instance of \sb\Logge_rBase or one is created using FileSystem logging
     */
    public function set_logger($logger=null) {

        if($logger instanceOf Logger_Base) {
            $this->logger = $logger;
        } else {
            $this->logger = new Logger_FileSystem();
        }
    }

    /**
     * Only logs for a specific SBF_ID
     * @param string $sbf_id The SBF_ID to log for
     */
    public function log_only_for_SBF_ID($sbf_id) {
        $this->sbf_id = $sbf_id;
    }

    /**
     * Additionally Logs the errors
     * {@inheritdoc }
     */
    public function s2o($sql, $params=null, $class_name='', $prepare_and_store=1) {
        try {
            return parent::s2o($sql, $params, $class_name, $prepare_and_store);
        } catch(Exception $e) {

            $trace = $e->getTrace();

            $message = "Error: ".__CLASS__ ." Exception in ".$trace[1]['file']." on line ".$trace[1]['line']." with db message: \n".$e->getMessage();

            $this->write_to_log($message);

            return Array();
        }
    }

    /**
     * same as normal query, however, it allows logging if log file is set
     *
     * @param string $sql
     * @return object PDO result set
     */
    public function query($sql) {

        $this->write_to_log("Querying: ".$sql);
        return parent::query($sql);
    }

    /**
     * Used to issue statements which return no results but rather the number of rows affected
     *
     * @param string $sql
     * @return integer The number of rows affected
     */
    public function exec($sql) {
        $this->write_to_log("Exec: ".$sql);
        $result = parent::exec($sql);
        return $result;
    }

    /**
     * Used to prepare sql statements for value binding
     *
     * @param string $sql
     * @return PDO_Statement A PDO_statment instance
     */
    public function prepare($sql, $options=null) {

        $md5 = md5($sql);

        if(isset($this->prepared_sql[$md5])) {
            return $this->prepared_sql[$md5];
        }

        //$this->write_to_log("Preparing: ".$sql);
        $stmt = parent::prepare($sql);
        $this->prepared_sql[$md5] = $stmt;
        return $stmt;
    }

    /**
     * Logs all sql statements to a file, if the log file is specified
     *
     * @param string $message The string to log
     */
    public function write_to_log($message) {

        if(is_null($this->logger)) {
            $this->set_logger();
        }

        if(empty($this->sbf_id) || $this->sbf_id == Gateway::$cookie['SBF_ID']) {

            $message = preg_replace("~\t~", " ", $message);
            return $this->logger->{$this->log_str}($message);
        }
    }

}

?>