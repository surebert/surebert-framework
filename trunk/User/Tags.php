<?php
/**
 * Used to add tags to users for grouping and application access purposes
 * @author visco
 * @version 0.1 03/17/09 03/17/09
 *
 * At first run you should run ->setup() in order to create the table.  After that 
 * make sure it is commented out because it just wastes time once the table is created
 *
 */

/*
 * EXAMPEL CRAP REMOVE AFTER I FINISH DOCS
$y->delete_all_tags();
$y->setup();
//$y->add_tag_to_user('webprogrammer', 14650, '01/22/1977', '01/22/2020', 14650);
$y->add_tags_to_user(Array('programmer', 'it_worker'), 14650, '01/22/1977', '01/22/2020', 14650);
print_r($y->get_user_tags(14650));
var_dump($y->does_user_have_tags(14650, Array('programmer')));
var_dump($y->get_all_users_with_tag('programmer'));
var_dump($y->get_all_users_with_tags(Array('programmer', 'it_worker')));
*/

class sb_User_Tags  {
    
    /**
     * An sb_PDO database connection
     * @var sb_PDO
     */
    protected $db;
    
    /**
     * The table name to use
     * @var string
     */
    public $table = 'user_tags';

    /**
     * Sets up the database connection
     * @param sb_PDO $db
     */
    function __construct(sb_PDO $db=null) {

        if($db instanceof PDO){
            $this->db = $db;
        } else if(App::$db instanceof PDO){
            $this->db = App::$db;
        }
    }

    /**
     * Used to setup the initial table
     * @return unknown_type
     */
    public function setup(){
        
        $sql = "CREATE TABLE IF NOT EXISTS ".$this->table."(
            user_id INT UNSIGNED,
            tag VARCHAR(50),
            eff_begin_date DATETIME,
            eff_end_date DATETIME,
            added_by_user_id INT UNSIGNED,
            date_created DATETIME,
            PRIMARY KEY (user_id, tag),
            INDEX (user_id),
            INDEX (tag)
        );";
        $this->db->query($sql);

    }

    /**
     * Deletes all the users and tags in the table.
     * THIS IS EXTREMELY DANGEROUS AS IT WILL WIPEOUT ALL YOUR RECORDS
     * @return boolean
     */
    public function delete_all_tags(){
        return $this->db->query("TRUNCATE TABLE ".$this->table);
    }

    /**
     * Adds a new tag to a user.
     * A convenience wrapper for add_tags_to_user when used with a single tag
     * @param string $tag The tag to add
     * @param integer $user_id The id of the user
     * @return boolean true upon sucess, false otherwise
     */
    public function add_tag_to_user($tag, $user_id, $eff_begin_date=null, $eff_end_date=null, $added_by_user_id=null) {
        
        $sql = "INSERT INTO 
         ".$this->table."(

            user_id,
            tag,
            eff_begin_date,
            eff_end_date,
            added_by_user_id,
            date_created

        ) VALUES (
            :user_id,
            :tag,
            :eff_begin_date,
            :eff_end_date,
            :added_by_user_id,
            NOW()


        )";
        
        $stmt = $this->db->prepare($sql);
      
        $result = $stmt->execute(Array(
            ':user_id' => $user_id,
            ':tag' => $tag,
            ':eff_begin_date' => date("Y-m-d", (is_null($eff_begin_date) ? time() : strtotime($eff_begin_date))),
            ':eff_end_date' =>  date("Y-m-d", (is_null($eff_end_date) ? time() : strtotime($eff_end_date))),
            ':added_by_user_id' => $added_by_user_id
        ));

        return ($stmt->rowCount() >= 1) ? true : false;
    }

    /**
     * Adds multiple new tags to a user
     * @param Array $tags An array of string tags
     * @param integer $user_id The is of the user
     * @return boolean true upon sucess, false otherwise
     */
    public function add_tags_to_user($tags, $user_id, $eff_begin_date=null, $eff_end_date=null, $added_by_user_id=null) {
        
        $success = 1;
        
        foreach($tags as $tag){
            
            $success = ($this->add_tag_to_user($tag, $user_id, $eff_begin_date, $eff_end_date, $added_by_user_id)) ? $success++ : $success;
        }
        
        return $success == count($tags) ? true : false;

    }

    /**
     * Grabs an array of all tags for a specific user
     * @param integer $user_id The id of the user
     * @return array
     */
    public function get_user_tags($user_id){
        $sql = "SELECT
            tag
            FROM ".$this->table."
            WHERE
            user_id = :user_id
            AND eff_begin_date < NOW()
            AND eff_end_date > NOW()
        ";

        $rows = $this->db->s2o($sql, Array(':user_id' => $user_id));
        if(count($rows) == 0){
            return $rows;
        }
        
        $tags = Array();

        foreach($rows as $row){
            $tags[] = $row->tag;
        }

        return $tags;
    }

    /**
     * Checks to see if a user has specific tags
     * @param integer $user_id The id of the user
     * @param array $tags
     */
    public function does_user_have_tags($user_id, $tags=Array()){

        $values = Array();
        for($x=0;$x<count($tags);$x++){
            $values[':tag'.$x] = $tags[$x];
        }
        
        $sql = "
            SELECT
            user_id
            FROM ".$this->table."
            WHERE
            user_id = :user_id
            AND eff_begin_date < NOW()
            AND eff_end_date > NOW()
            AND tag IN (".implode(",", array_flip($values)).")
        ";

        $values[':user_id'] = $user_id;
       
        $rows = $this->db->s2o($sql, $values);
        return count($rows) == count($tags);

    }

    /**
     * Selects all users that are tagged with a particular tag
     * @param string $tag
     * @return Array Users that have the tag $tag
     */
    public function get_all_users_with_tag($tag){
        $sql ="
            SELECT
                user_id
            FROM ".$this->table."
                WHERE tag = :tag
                AND eff_begin_date < NOW()
            	AND eff_end_date > NOW()
        ";

        $rows = App::$db->s2o($sql, Array(':tag' => $tag));
        
        if(count($rows) == 0){
            return $rows;
        }

        $users = Array();

        foreach($rows as $row){
            $users[] = $row->user_id;
        }

        return $users;
    }

    /**Selects all the user with a specific combo of tags
     *
     * @param array $tags An array of string tags
     * @return Array Of User ids that have all the tags given
     */
    public function get_all_users_with_tags($tags=Array()){

        $values = Array();
        for($x=0;$x<count($tags);$x++){
            $values[':tag'.$x] = $tags[$x];
        }

        $sql = "
            SELECT
                DISTINCT user_id
            FROM ".$this->table."
                WHERE
            tag IN (".implode(",", array_flip($values)).")
            AND eff_begin_date < NOW()
            AND eff_end_date > NOW()
        ";

        $rows = $this->db->s2o($sql, $values);
        
         if(count($rows) == 0){
            return $rows;
        }

        $users = Array();

        foreach($rows as $row){
            $users[] = $row->user_id;
        }

        return $users;
    }
}
?>