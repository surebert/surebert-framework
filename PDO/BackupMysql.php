<?php
/**
 * This script is used to backup all mysql databases on a server via mysqldump.
 * It is only suitable for databases that can be dumped using mysqldump.
 * The files are then gzippped into the dumps directory in the folder it was run in.
 * Dumps are broken down into numbered directorys.  1 is the newest and they
 * keep going up until max version.  When a dump directory goes beyond max version,
 * it is deleted.  Each time the script is run, the dump directories increment by 1.
 *
 * The db credetials should be for root or a user that has SELECT access to every database
 * @author Paul Visco paul.visco@roswellpark.org
 *
 * <code>
$backup = new sb_PDO_BackupMysql('127.0.0.1', 'root', 'abc123');

//optional
$back->max_version = 3;

$backup->backup();
 * </code>
 */

/**
 * This class handles backups of mysql databases on a box.
 * It stores 5 versions of each dtabase before starting again at 1.
 */
class sb_PDO_BackupMysql{

    /**
     * An array of databases to ignore
     * @var array
     */
    protected $ignore = Array();

    /**
     * Connection to database
     * @var PDO
     */
    protected $db;

    /**
     * The directory the data is dumped into, must include end /
     * @var string
     */
    protected $dump_dir = 'dumps/';

    /**
     * The maximum version number to keep before deleting
     * @var integer
     */
    public $max_version = 3;

    /**
     * Determines if log data is dumped to screen
     * @var boolean
     */
    public $debug = true;

     /**
      * Connects to the database for SELECT and mysqldump
      * @param string $db_host The mysql database host
      * @param string $db_user The mysql database user
      * @param string $db_pass The mysql database pass
      */
    public function __construct($db_host, $db_user, $db_pass){

        $this->db_host = $db_host;
        $this->db_user = $db_user;
        $this->db_pass = $db_pass;

        $this->start = microtime(true);
    }

    /**
     * Initiates the backup process
     */
    public function backup(){

        $this->check_dump_dir();
        $this->connect_to_db();
        $this->dump_databases();
    }

    /**
     * An array of database names to ignore during backup, also databases named
     * nobackup_* are ignore
     * @param Array $array
     */
    public function set_ignore($array){

        if(is_array($array)){
            $this->ignore = $array;
        } else {
            die("Set_ignore only accepts an array");
        }
    }

    /**
     * Connects to the database
     */
    protected function connect_to_db(){
        try{
            $this->db=new PDO("mysql:dbname=;".$this->db_host, $this->db_user, $this->db_pass);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

        } catch(Exception $e){
            $this->log("Cannot connect to database.  Are the credentials correct?");
            $this->log(print_r($e), 1);
            exit;
        }
    }

    /**
     * check to make sure dump directory exists and if not create it
     */
    protected function check_dump_dir(){

        if(!is_dir($this->dump_dir)){
            mkdir($this->dump_dir, true);
        }

        foreach(range($this->max_version, 1) as $version){
            $dir = $this->dump_dir.$version;

            if(is_dir($dir)){

                if($version == $this->max_version){
                    $this->recursive_delete($dir, 1);
                    $this->log('Deleting backup '.$this->max_version);
                } else{
                    $new_version = $version+1;
                    rename($dir, $this->dump_dir.$new_version);
                    $this->log('Moving backup '.$version.' to version '.$new_version);
                }
            }
        }

        if(!is_dir($this->dump_dir.'1')){
            mkdir($this->dump_dir.'1', true);

        }
    }


    /**
     * Dump the database files and gzip, add version number
     */
    protected function dump_databases(){

        foreach($this->db->query("SHOW DATABASES") as $list){
            $database = $list->Database;

            if(!in_array($database, $this->ignore) || preg_match("~_no_backup$~", $database)){
                $start = microtime(true);

                $dir = $this->dump_dir.'1/';

                $filename = $dir.$database;

                $this->log("Dumping Database: ".$database);
                $command = "mysqldump -u ".$this->db_user." -h ".$this->db_host." -p".$this->db_pass." ".$database.">".$filename.".sql";

                exec($command);

                $command = "tar -zcvf ".$filename.".gz ".$filename.".sql";
                exec($command);
                $ms = round((microtime(true)-$start)*1000, 2);

                clearstatcache();
                $size = round((filesize($filename.'.gz')/1024),2).'kb';

                $this->log($list->Database." was backed up in ".$ms.' ms and is '.$size.' bytes');
                unlink($filename.'.sql');
            }
        }
    }

    /**
     * Send messages to stdout
     * @param string $message
     */
    protected function log($message){

        if($this->debug == true){
            file_put_contents("php://stdout", $message."\n");
        }
        
        file_put_contents($this->dump_dir.'/dump.log', $message."\n", FILE_APPEND);
       
    }

    /**
	 * Recursively deletes the files in a diretory
	 *
	 * @param string $dir The directory path
	 * @param boolean $del Should directory itself be deleted upon completion
	 * @return boolean
	 */
	protected function recursive_delete($dir, $del=0){

        if(substr($dir, 0, 1) == '/'){
            die("You cannot delete root directories");
        }

		$iterator = new RecursiveDirectoryIterator($dir);
		foreach (new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST) as $file){
		  $name = $file->getFilename();
          if ($file->isDir() && $name != '.' && $name != '..') {
		     rmdir($file->getPathname());
		  } else if($file->isFile()){
		     unlink($file->getPathname());
		  }
		}

		if($del ==1){
			rmdir($dir);
		}
	}

    /**
     * Stamp the final time and move the dump file into the newest version directory
     */
    public function  __destruct() {
        $ms = round(microtime(true)-$this->start, 2);
        $this->log($ms.'ms elapsed');
        rename($this->dump_dir.'dump.log', $this->dump_dir.'1/dump.log');
    }

}
?>