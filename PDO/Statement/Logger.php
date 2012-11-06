<?php

/**
 * Extends native PDOStatement class for logging and debugging, when the logging
 *  is set on an \sb\PDO instance.  You would never access this directly
 *
 * @author paul.visco@roswellpark.org
 * @package PDO
 */
namespace sb\PDO\Statment;

class Logger extends \PDOStatement
{

    public $connection;

    protected function __construct($connection = '')
    {

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
    public function execute($bound_input_params = Array())
    {
        $log = "Executing: " . $this->queryString;

        if (count($bound_input_params) > 0) {

            foreach ($bound_input_params as $key => $val) {
                $log .= "\nBinding Values: " . $key . ' = ' . $val;
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
    public function bindParam($paramno, &$param, $type = null, $maxlen = null, $driverdata = null)
    {
        $log = 'Binding Parameters: ' . $paramno . '=' . $param;
        if (!is_null($type)) {
            $log .= '| Type: ' . $type;
        }
        if (!is_null($maxlen)) {
            $log .= '| Maxlen: ' . $maxlen;
        }

        if (!is_null($driverdata)) {
            $log .= '| DriverData: ' . $driverdata;
        }
        $this->connection->write_to_log($log);

        if (!empty($type)) {
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
    public function bindColumn($column, &$param, $type = null, $maxlen = null, $driverdata = null)
    {
        $log = 'Binding Column: ' . $column . '=' . $param;
        if (!is_null($type)) {
            $log .= '| Type: ' . $type;
        }

        if (!is_null($maxlen)) {
            $log .= '| Maxlen: ' . $maxlen;
        }

        if (!is_null($driverdata)) {
            $log .= '| DriverData: ' . $driverdata;
        }
        $this->connection->write_to_log($log);

        if (!empty($type)) {
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
    public function bindValue($paramno, $param, $type = null)
    {
        $log = 'Binding Value: ' . $paramno . '=' . $param;
        if (!is_null($type)) {
            $log .= '| Type: ' . $type;
        }

        $this->connection->write_to_log($log);

        return parent::bindParam($paramno, $param, $type);
    }
}

