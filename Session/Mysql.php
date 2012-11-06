<?php
/**
 * Used to put sessions in mysql instead of using file based ones
 *
 *<code>
 * #table required
 * CREATE TABLE surebert_sessions
 * (
 * session_id CHAR(32) NOT NULL,
 * access TIMESTAMP,
 * token CHAR(32),
 * data TEXT,
 * PRIMARY KEY (session_id)
 * )
 * </code>
 *
 * @author paul.visco@roswellpark.org
 * @package Session
 *
 */
namespace sb;

class Session_Mysql extends Session{
    
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
     * A token combinig the ip and user agent munged as md5
     * @var string
     */
    private $token = '';
    
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
    public function __construct(PDO $db, $session_life_time=null)
    {
        
        $this->db = $db;
        
        $this->token = md5(Gateway::$remote_addr.Gateway::$agent);
        
        // get session lifetime
        $this->session_life_time = !is_null($session_life_time) ? $session_life_time : ini_get("session.gc_maxlifetime");
        // register the new handler
        
        session_set_save_handler(
            array($this, 'open'),
            array($this, 'close'),
            array($this, 'read'),
            array($this, 'write'),
            array($this, 'destroy'),
            array($this, 'gc')
        );
        register_shutdown_function('session_write_close');
        
        session_start();
    }
    
    /**
     * Opens the session, not needed for db based sessions
     * @return boolean
     */
    public function open()
    {
         return true;
    }
    
    /**
     * Closes the session, not needed for db based sessions
     * @return boolean
     */
    public function close()
    {
        return true;
    }
    
    /**
     * Closes the session, not needed for db based sessions
     * @return boolean
     */
    public function read($session_id)
    {

        $stmt_cache = md5(__METHOD__);
        if(!isset($this->stmts[$stmt_cache])){
            
            $this->stmts[$stmt_cache] = $this->db->prepare("
                SELECT
                    data
                FROM
                    surebert_sessions
                WHERE
                    session_id = :session_id
                    AND token = :token
                    AND UNIX_TIMESTAMP(access) > UNIX_TIMESTAMP(NOW())-:session_lifetime
            ");
            
        }
        
        $stmt = $this->stmts[$stmt_cache];
        
        if($stmt->execute(Array(
            ':session_id' => $session_id,
            ':token' => $this->token,
            ':session_lifetime' => $this->session_life_time
        ))){
            $rows = $stmt->fetchAll();
            if(isset($rows[0])){
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
    public function updateAccess($session_id)
    {
        
        $stmt_cache = md5(__METHOD__);
        if(!isset($this->stmts[$stmt_cache])){
            
            $this->stmts[$stmt_cache] = $this->db->prepare("
                UPDATE
                    surebert_sessions
                    SET access = NOW()
                WHERE
                    session_id = :session_id
                    AND token = :token
                   
            ");
            
        }
        
        $stmt = $this->stmts[$stmt_cache];
        
        return $stmt->execute(Array(
            ':session_id' => $session_id,
            ':token' => $this->token
        ));
    }
    
    /**
     * updates session data in the mysql database
     * @param $session_id
     * @param $data The session data to write
     * @return boolean
     */
    public function write($session_id, $data)
    {
        
        $stmt_cache = md5(__METHOD__);
        if(!isset($this->stmts[$stmt_cache])){
            
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
        
        if(!$result){
            return false;
        }
        
        $rows = $stmt->fetchAll();
        
        if(isset($rows[0])){
            $result = $this->update($session_id, $data);
           
        } else {
            $result = $this->insert($session_id, $data);
        }
        
        if($result && $stmt->rowCount()){
            return true;
        } else {
            return false;
        }
       
    }
    
    /**
     * writes session data to the mysql database
     * @param $session_id
     * @param $data The session data to write
     * @return boolean
     */
    private function insert($session_id, $data)
    {
        
        $stmt_cache = md5(__METHOD__);
        if(!isset($this->stmts[$stmt_cache])){
            $this->stmts[$stmt_cache] = $this->db->prepare("
                INSERT INTO
                    surebert_sessions
                (session_id, data, token)
                VALUES 
                (:session_id, :data, :token) 
            ");
        }
        
        $stmt = $this->stmts[$stmt_cache];
        
        return $stmt->execute(Array(
               ':session_id' => $session_id,
               ':data' => $data,
             ':token' => $this->token
        ));
        
    }
    
    
    /**
     * Updates an existing session's data
     * @param $session_id
     * @param $data
     * @return boolean
     */
    private function update($session_id, $data)
    {
        
        $stmt_cache = md5(__METHOD__);
        if(!isset($this->stmts[$stmt_cache])){
            
            $this->stmts[$stmt_cache] = $this->db->prepare("
                UPDATE
                    surebert_sessions
                SET
                    session_id = :session_id,
                    data = :data
                WHERE
                    token = :token
            ");
            
        }
        
        $stmt = $this->stmts[$stmt_cache];
        
        return $stmt->execute(Array(
               ':session_id' => $session_id,
               ':data' => $data,
               ':token' => $this->token
        ));
    }
    
    /**
     * Destroys a sessions by deleting it from the database
     * @return unknown_type
     */
    public function destroy($session_id)
    {

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
               
        if($result && $stmt->rowCount()){
               return true;
        } else {
               return false;
        }
    }
        
    /**
     * Garbage collects any open sessions that are no longer valid
     * @return boolean
     */
    public function gc()
    {

         $sql = "
            DELETE FROM
                surebert_sessions
            WHERE
                UNIX_TIMESTAMP(access) > UNIX_TIMESTAMP(NOW())-:session_lifetime
     
        ";
         
         $stmt = $this->db->prepare($sql);
               
         return $stmt->execute(Array(
              ':session_lifetime' => $this->session_life_time
         ));
         
    }
    
    /**
     * regenerate the session id
     * @return boolean
     */
    public function regenerate_id()
    {

        $old_session_id = session_id();
        
        session_regenerate_id();
       
        $this->destroy($old_session_id);
        
        return true;
    }
    
    
}

