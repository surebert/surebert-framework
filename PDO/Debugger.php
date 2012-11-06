<?php

/**
 * Extends native PDO class for logging and debugging
 *
 * @author paul.visco@roswellpark.org
 * @package PDO
 */
namespace sb\PDO;

class Debugger extends \PDO
{

    /**
     * Creates am extended PDO object
     *
     * @param string $connection The pdo connection string
     * @param string $user Username if required
     * @param string $pass Password for connection if required
     *
     * <code>
     * $db=new \sb\PDO("mysql:dbname=xxx;host=xxx", 'username', 'pass');
     * $db=new \sb\PDO("sqlite:myfile.db3');
     * </code>
     *
     */
    public function __construct($connection, $user = '', $pass = '')
    {

        parent::__construct($connection, $user, $pass);

        /*         * * set the error reporting attribute ** */
        $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Additionally Logs the errors
     */
    public function s2o($sql, $params = null, $class_name = '', $prepare_and_store = 1)
    {

        try {
            return parent::s2o($sql, $params, $class_name, $prepare_and_store);
        } catch (\Exception $e) {
            throw(new \sb\PDO\Exception('CALLED: ' . __METHOD__ . "\nERROR RETURNED: " . print_r($e, 1)));
        }
    }

    public static function paramify($data, $omit = Array())
    {

        if (!is_array($data) && !is_object($data)) {
            throw(new \sb\PDO\Exception('Paramify only accepts hashes and objects as data argument'));
        }

        return parent::paramify($data, $omit);
    }
}

