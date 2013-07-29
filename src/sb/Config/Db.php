<?php

/**
 * Used to manage site config defintions
 * @author paulsidekick@gmail.com   
 * 
 * <code>
 * //could be set to App::$config if you wanted it globally accessible
 * $config = new \sb\Config\Db(App::$db);
 * $config->set('name', 'paul');
 * $config->delete('name');
 * echo $config->get('name');
 * print_r($config->getAll());
 * </code>
 * 
 * Database must have following table and support INSERT AND DELETE
 * 
    CREATE TABLE sb_config(
      k VARCHAR(100) PRIMARY KEY,
      v VARCHAR(255)
    );
 */

namespace sb\Config;

class Db{
    
    /**
     * Caches variables
     * @var array 
     */
    protected $data = Array();
    
    /**
     * The database to load the defintions from.
     * @var PDO 
     */
    protected $db;
    
    /**
     * The table name that the config values are stored in
     * @var string 
     */
    protected $table = 'sb_config';
    
    /**
     * Instantiates the class and sets the db used
     * @param \PDO $db
     */
    public function __construct(\PDO $db){
        $this->db = $db;
        $this->load();
    }
    
    /**
     * Used to load all site definitions
     */
    public function load(){
        
        $sql = "SELECT k, v FROM ".$this->table;
        $rows = $this->db->query($sql);
        foreach($rows as $row){
            $this->data[$row->k] = $row->v;
        }
        
    }
    
    /**
     * Get a specific definition
     * @param string $key The key to load the value for
     * @return string
     */
    public function get($key){
        //return val for key from $data
        return isset($this->data[$key]) ? $this->data[$key] : NULL;
    }
    
    /**
     * Return all the currently existing pairs for debugging, etc
     * @return array
     */
    public function getAll(){
        return $this->data;
    }
    
    /**
     * Save the defintions back to the db
     * @param string $key
     * @param string $val
     */
    public function set($key, $val){
        
        $this->delete($key);
        
        $sql = "INSERT INTO ".$this->table." (k, v) VALUES (:k, :v)";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(Array(':k' => $key, ':v' => $val));
        if($result){
            $this->data[$key] = $val;
        }
        
        return $result ? 1 : 0;
        
    }
    
    /**
     * Deletes a key so that it no longer exists at all
     * @param string $key
     * @return boolean
     */
    public function delete($key){
        $sql = "DELETE FROM ".$this->table." WHERE k = :k";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(Array(":k" => $key));
        
        if(isset($this->data[$key])){
            unset($this->data[$key]);
        }
        
        return $result;
    }
}
