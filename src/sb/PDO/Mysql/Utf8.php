<?php
/**
 * Converts all tables in a db to utf8
 * @author Paul.Visco@roswellpark.org
 */

namespace sb\PDO\Mysql;

class Utf8{
    /**
     * Converts entire db to UTF8, only run once then delete
     * @servable true
     */
    public function convert(\PDO $db) {

        $this->setMaxExecutionTime(86400);
        $this->setMemoryLimit(2000);

        //find all the tables in the db
        $tables = [];
        $sql = "SHOW TABLES";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute();
        if ($result) {
            $tables = $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);
        }

        //loop through the tables and grab the create statements
        foreach ($tables as $table) {

            $this->log($table, "CONVERTING TABLE");
            $sql = "SHOW CREATE TABLE " . $table;
            $stmt = $db->prepare($sql);
            $result = $stmt->execute();
            if ($result) {
                $create = $stmt->fetchAll(\PDO::FETCH_COLUMN, 1)[0];
                $this->log($create . "\n", "ORIGINAL CREATE SQL");

                //convert the create statements into utf8 charset
                $utf_create = preg_replace("~CHARSET=(.*)~", "CHARSET=utf8", $create);

                $this->log($utf_create . "\n", "UTF8 CREATE SQL");
                $backup_table = $table . "_backup";

                //create table backup
                $sql = "CREATE TABLE `" . $backup_table . "` SELECT * FROM `" . $table . "`";
                $this->log($sql, "MAKING BACKUP");
                $stmt = $db->prepare($sql);
                $result = $stmt->execute();
                if ($result) {

                    $this->log($backup_table, "BACKUP COMPLETE");

                    //drop original table
                    $sql = "DROP TABLE `" . $table . "`";
                    $this->log($sql, "DROPPING ORIGINAL");
                    $stmt = $db->prepare($sql);
                    $result = $stmt->execute();
                    
                    if ($result) {
                        
                        $this->log($table, "DROPPED ORIGINAL");
                        //create the UTF8 version of the table
                        $this->log($table, "CREATING UTF TABLE");
                        $result = false;
                        try{
                        $stmt = $db->prepare($utf_create);
                        $result = $stmt->execute();
                        }catch(\PDOException $e){
                            $this->log($table.': '.print_r($e->getMessage()), "ERROR!!!!!");
                        }
                        
                        if ($result) {

                            $this->log($table, "UTF TABLE CREATED");

                            //restore the data from the backup table
                            $sql = "INSERT INTO `" . $table . "` SELECT * FROM `" . $backup_table . "`";
                            $this->log($backup_table . ' -> ' . $table, "RESTORING");
                            $stmt = $db->prepare($sql);
                            $result = $stmt->execute();
                           
                            if ($result) {
                                $this->log($backup_table . ' -> ' . $table, "RESTORED");

                                //dropping the backup table
                                $this->log($backup_table, "DROPPING");
                                $sql = "DROP TABLE " . $backup_table;
                                $stmt = $db->prepare($sql);
                                $result = $stmt->execute();
                                if ($result) {
                                    $this->log($backup_table, "BACKUP DROPPED");
                                }
                            }
                        }
                    }
                }
            }
        }
    }

}
