<?php

/**
 * Used to put sessions in mysql instead of using file based ones
 *
 * <code>
 * #table required
  CREATE TABLE surebert_sessions (
  session_id CHAR(32) NOT NULL,
  created DATETIME,
  last_access TIMESTAMP,
  agent VARCHAR(255),
  ip VARCHAR(15),
  data MEDIUMTEXT,
  PRIMARY KEY (session_id)
  )
 * </code>
 *
 * @author paul.visco@roswellpark.org
 * @package Session
 *
 */

namespace sb\Session;

class Mysql extends \sb\Session implements \SessionHandlerInterface {

    /**
     * The database connection
     * @var PDO
     */
    private $db;

    /**
     * The session ma xlifetime
     * @var integer
     */
    private $session_life_time;

    /**
     * The IP of the session
     * @var string
     */
    private $ip = '';

    /**
     * The user agent of the session
     * @var string 
     */
    private $agent = '';

    /**
     * Prepared statements cache
     * @var array
     */
    private $stmts = Array();

    /**
     * Connects to the mysql server for session storage
     * <code>
      new \sb\Session\Mysql(\App::$db);
     * </code>
     *
     * @param $db PDO the database conection to store the sessions in
     * @param $session_life_time integer
     * @return unknown_type
     */
    public function __construct(\PDO $db, $session_life_time = null) {

        $this->db = $db;

        // get session lifetime
        $this->session_life_time = !is_null($session_life_time) ? $session_life_time : ini_get("session.gc_maxlifetime");

        // register the new handler
        session_set_save_handler($this, false);
        session_start();
    }

    /**
     * Opens the session, not needed for db based sessions
     * @return boolean
     */
    public function open($save_path, $session_name) :bool {
        return true;
    }

    /**
     * Closes the session
     * @return boolean
     */
    public function close() :bool {
        $this->gc($this->session_life_time);
        return true;
    }

    /**
     * Closes the session, not needed for db based sessions
     * @return boolean
     */
    public function read($session_id) : string|false {

        $stmt_cache = md5(__METHOD__);
        if (!isset($this->stmts[$stmt_cache])) {

            $this->stmts[$stmt_cache] = $this->db->prepare("
                SELECT
                    data
                FROM
                    surebert_sessions
                WHERE
                    session_id = :session_id
                    AND UNIX_TIMESTAMP(last_access) > UNIX_TIMESTAMP(NOW())-:session_lifetime
            ");
        }

        $stmt = $this->stmts[$stmt_cache];

        if ($stmt->execute(Array(
                    ':session_id' => $session_id,
                    ':session_lifetime' => $this->session_life_time
                ))) {
            $rows = $stmt->fetchAll();
            if (isset($rows[0])) {
                $this->updateAccess($session_id);
                return $rows[0]->data;
            }
        }

        return "";
    }

    /**
     * Updates the access time after reading the data
     * @param $session_id
     * @return boolean
     */
    public function updateAccess($session_id) {

        $stmt_cache = md5(__METHOD__);
        if (!isset($this->stmts[$stmt_cache])) {

            $this->stmts[$stmt_cache] = $this->db->prepare("
                UPDATE
                    surebert_sessions
                    SET last_access = NOW()
                WHERE
                    session_id = :session_id

            ");
        }

        $stmt = $this->stmts[$stmt_cache];

        return $stmt->execute(Array(
                    ':session_id' => $session_id
        ));
    }

    /**
     * updates session data in the mysql database
     * @param $session_id
     * @param $data The session data to write
     * @return boolean
     */
    public function write($session_id, $data) : bool {

        $stmt_cache = md5(__METHOD__);
        if (!isset($this->stmts[$stmt_cache])) {

            $this->stmts[$stmt_cache] = $this->db->prepare("
                SELECT
                    session_id
                FROM
                    surebert_sessions
                WHERE
                    session_id = :session_id
            ");
        }

        $stmt = $this->stmts[$stmt_cache];

        $result = $stmt->execute(Array(
            ':session_id' => $session_id
        ));

        if (!$result) {
            return false;
        }

        $rows = $stmt->fetchAll();

        if (isset($rows[0])) {
            $result = $this->update($session_id, $data);
        } else {
            $result = $this->insert($session_id, $data);
        }
        
        return $result;
    }

    /**
     * writes session data to the mysql database
     * @param $session_id
     * @param $data The session data to write
     * @return boolean
     */
    private function insert($session_id, $data) {

        $stmt_cache = md5(__METHOD__);
        if (!isset($this->stmts[$stmt_cache])) {
            $this->stmts[$stmt_cache] = $this->db->prepare("
                REPLACE INTO
                    surebert_sessions
                (session_id, last_access, created, agent, ip, data)
                VALUES
                (:session_id, NOW(), NOW(), :agent, :ip, :data)
            ");
        }

        $stmt = $this->stmts[$stmt_cache];

        return $stmt->execute(Array(
                    ':session_id' => $session_id,
                    ':agent' => \sb\Gateway::$agent,
                    ':ip' => \sb\Gateway::$remote_addr,
                    ':data' => $data
        ));
    }

    /**
     * Updates an existing session's data
     * @param $session_id
     * @param $data
     * @return boolean
     */
    private function update($session_id, $data) {

        $stmt_cache = md5(__METHOD__);
        if (!isset($this->stmts[$stmt_cache])) {

            $this->stmts[$stmt_cache] = $this->db->prepare("
                UPDATE
                    surebert_sessions
                SET
                    data = :data
                WHERE
                    session_id = :session_id
            ");
        }

        $stmt = $this->stmts[$stmt_cache];

        return $stmt->execute(Array(
            ':session_id' => $session_id,
            ':data' => $data
        ));
    }

    /**
     * Destroys a sessions by deleting it from the database
     * @return unknown_type
     */
    public function destroy($session_id) :bool {

        $sql = "
        DELETE FROM
            surebert_sessions
        WHERE
            session_id = :session_id
        ";


        $stmt = $this->db->prepare($sql);

        $result = $stmt->execute(Array(
            ':session_id' => $session_id
        ));

        if ($result && $stmt->rowCount()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Garbage collects any open sessions that are no longer valid
     * @return boolean
     */
    public function gc($maxlifetime) : int|false{

        $sql = "
            DELETE FROM
                surebert_sessions
            WHERE
                UNIX_TIMESTAMP(last_access) < UNIX_TIMESTAMP(NOW())-:session_lifetime

        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute(Array(
                    ':session_lifetime' => $maxlifetime ? : $this->session_life_time
        ));
    }

    /**
     * regenerate the session id
     * @return boolean
     */
    public function regenerateId() {

        $old_session_id = session_id();

        session_regenerate_id();

        $this->destroy($old_session_id);

        return true;
    }

}
