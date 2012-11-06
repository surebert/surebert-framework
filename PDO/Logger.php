<?php

/**
 * Extends native PDO class for logging and debugging
 *
 * @author paul.visco@roswellpark.org
 * @package PDO
 *
 */
namespace sb\PDO;

class Logger extends Debugger
{

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
     * $db->setLogger(App::$logger);
     * </code>
     *
     */
    public function __construct($connection, $user = '', $pass = '')
    {

        $this->log_str = str_replace(Array(":", ";", "=", "."), "_", $connection);
        parent::__construct($connection, $user, $pass);
        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, Array('sb_PDO_Statement_Logger', Array($this)));
    }

    /**
     * Set the logger
     *
     * @param $logger \sb\Logger\Base An instance of \sb\Logge_rBase or one is created using FileSystem logging
     */
    public function setLogger($logger = null)
    {

        if ($logger instanceOf \sb\Logger\Base) {
            $this->logger = $logger;
        } else {
            $this->logger = new \sb\Logger_FileSystem();
        }
    }

    /**
     * Only logs for a specific SBF_ID
     * @param string $sbf_id The SBF_ID to log for
     */
    public function logOnlyForSbfId($sbf_id)
    {
        $this->sbf_id = $sbf_id;
    }

    /**
     * Additionally Logs the errors
     * {@inheritdoc }
     */
    public function s2o($sql, $params = null, $class_name = '', $prepare_and_store = 1)
    {
        try {
            return parent::s2o($sql, $params, $class_name, $prepare_and_store);
        } catch (\Exception $e) {

            $trace = $e->getTrace();

            $message = "Error: " . __CLASS__ . " Exception in " 
                . $trace[1]['file'] . " on line " . $trace[1]['line'] 
                . " with db message: \n" . $e->getMessage();

            $this->writeToLog($message);

            return Array();
        }
    }

    /**
     * same as normal query, however, it allows logging if log file is set
     *
     * @param string $sql
     * @return object PDO result set
     */
    public function query($sql)
    {

        $this->writeToLog("Querying: " . $sql);
        return parent::query($sql);
    }

    /**
     * Used to issue statements which return no results but rather the number of rows affected
     *
     * @param string $sql
     * @return integer The number of rows affected
     */
    public function exec($sql)
    {
        $this->writeToLog("Exec: " . $sql);
        $result = parent::exec($sql);
        return $result;
    }

    /**
     * Used to prepare sql statements for value binding
     *
     * @param string $sql
     * @return PDO_Statement A PDO_statment instance
     */
    public function prepare($sql, $options = null)
    {

        $md5 = md5($sql);

        if (isset($this->prepared_sql[$md5])) {
            return $this->prepared_sql[$md5];
        }

        //$this->writeToLog("Preparing: ".$sql);
        $stmt = parent::prepare($sql);
        $this->prepared_sql[$md5] = $stmt;
        return $stmt;
    }

    /**
     * Logs all sql statements to a file, if the log file is specified
     *
     * @param string $message The string to log
     */
    public function writeToLog($message)
    {

        if (is_null($this->logger)) {
            $this->setLogger();
        }

        if (empty($this->sbf_id) || $this->sbf_id == Gateway::$cookie['SBF_ID']) {

            $message = preg_replace("~\t~", " ", $message);
            return $this->logger->{$this->log_str}($message);
        }
    }
}

