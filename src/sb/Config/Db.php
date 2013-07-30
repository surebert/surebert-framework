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
      v TEXT,
      t VARCHAR(5) DEFAULT 'str'
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
     * @param string $prefix Only loads config values that start with a specific prefix
     */
    public function __construct(\PDO $db, $prefix=''){
        $this->db = $db;
        $this->load($prefix);
    }
    
    /**
     * Used to load all site definitions
     */
    public function load($prefix=''){
        $values = Array();
        
        $sql = "SELECT k, v, t FROM ".$this->table;
        if($prefix){
            $sql .= " WHERE k LIKE :prefix";
            $values[':prefix'] = $prefix.'%';
        }
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($values);
        $rows = $this->db->s2o($sql, $values);
        foreach($rows as $row){
            
            $v = $row->v;
            
            if($row->t == 'json'){
                $v = json_decode($v);
            } else if($row->t == 'php'){
                $v = unserialize($v);
            } else if($row->t == 'int'){
                $v = (int) $v;
            } else {
                $v = (string) $v;
            }
            
            $this->data[$row->k] = $v;
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
     * @param string $key The key to store the value under
     * @param string $val The value of the key
     * @param string $type The type of the object str, int, php, json
     */
    public function set($key, $val, $type='str'){
        
        $this->delete($key);
        
        $orig_val = $val;
        
        if($type == 'php' || (is_object($val) && $type == 'str')){
            $val = serialize($val);
        } else if($type == 'json'){
            $val = json_encode($val);
        } else if($type == 'int'){
            $val = (int) $val;
        } else {
            $val = (string) $val;
        }
            
        $sql = "INSERT INTO ".$this->table." (k, v, t) VALUES (:k, :v, :t)";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(Array(
            ':k' => $key,
            ':v' => $val,
            ':t' => $type
        ));
        
        if($result){
           
            $this->data[$key] = $orig_val;
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
