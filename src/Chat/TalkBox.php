<?php

/**
 * This class is used to drive a talkbox chat block on a site
 *
 * Creates one table called sb_TalkBox_master and a table name sb_TalkBox_{ROOMNAME} for each room
 *
  BEFORE USING

  //Create a mysql database
  CREATE database @myDataBase

  //Grant privileges to your user
  GRANT ALL on @myDataBase.* To '@myUserName'@'@myHost' identified by '@myPassword';

  //flush the privilege cache to pick up new permissions
  FLUSH PRIVILEGES;

  //create the master table
  CREATE TABLE IF NOT EXISTS sb_TalkBox_master (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(75),
  created DATETIME,
  last_visited DATETIME,
  last_post DATETIME,
  PRIMARY KEY (id)
  )

 * @package Chat
 * 
 * 
 */
namespace sb\Chat;
use \sb\Text\Bling as Bling;

class TalkBox
{

    /**
     * Determines if debugging is enabled or not
     *
     * @var boolean
     */
    public $debug = 0;

    /**
     * determines if chat commands can be issued
     *
     * @var unknown_type
     */
    public $allow_commands = 0;

    /**
     * The current room the chat is being directed into
     *
     * @var string
     */
    public $room = 'talkbox';

    /**
     * The duration in seconds before the chat lines are deleted -1 means never
     *
     * @var integer
     */
    public $expiration_duration = -1;

    /**
     * Allow member without usernames to chat
     *
     * @var boolean
     */
    public $allow_guest_chat = 0;
    public $master_table;

    /**
     * A pdo object db conenction
     *
     * @var unknown_type
     */
    public $db;
    public $lines = Array();

    /**
     *
     * Instanitates a new talkbox
     * <code>
     * $db = new sb_PDO("mysql:dbname=sb_talkbox;host=localhost", 'talker', 'rt3');
     * $talkbox = new sb_TalkBox($db);
     *
     * //live filter the return rows
     * function sb_TalkBoxOnParse($str){
     *
     *     //in this case replace swear word
     *     return str_replace('fuck', 'f***', $str);
     * }
     *
     * //run this only the first time
     * $talkbox->createRoom('paul');
     *
     * //create a new line to insert, this would normally come from ajax or form
     * $line = new sb_Chat_Line();
     * $line->uname ='paul';
     * $line->ip=$_SERVER['REMOTE_ADDR'];
     * $line->message="hello there fuckhead";
     * $talkbox->insert($line);
     *
     * //echo the json of the latest chat starting with the newest line and 
     * going back 10 lines which can be used to build dom display
     * echo json_encode($talkbox->display(0, 10));
     *
     * </code>
     *
     * @param sb_PDO $pdo_connection
     * @param string $room
     *
     */
    public function __construct(PDO $pdo_connection, $room)
    {

        $this->db = $pdo_connection;

        $this->room = $room;

        $this->createRoom($room);
    }

    public function destroyRoom()
    {

        if ($room == 'master') {
            return;
        }

        $sql = "DROP TABLE sb_TalkBox" . $this->room;
        $result = $this->db->query($sql);

        $sql = "DROP TABLE sb_TalkBox" . $this->room . '_mem';
        $result = $this->db->query($sql);
    }

    public function updateLastVisit()
    {
        $sql = "UPDATE sb_TalkBox_master SET last_visited = NOW() WHERE name ='" . $this->room . "';";
        $result = $this->db->query($sql);
    }

    public function getLastVisit()
    {
        $sql = "SELECT last_visited FROM sb_TalkBox_master WHERE name ='" . $this->room . "';";
        $result = $this->db->query($sql);
        $row = $result->fetchAll(PDO::FETCH_OBJ);
        return $row[0]->last_visited;
    }

    //clears the history if the user types the clear command
    public function clearHistory()
    {

        $sql = "DELETE FROM sb_TalkBox_mem" . $this->room . " WHERE id > 1;";
        $result = $this->db->query($sql);

        $sql = "DELETE FROM sb_TalkBox_" . $this->room . " WHERE id > 1;";
        $result = $this->db->query($sql);
    }

    public function createRoom($room)
    {

        $this->room = $room;

        $sql = "SELECT COUNT(id) AS num FROM sb_TalkBox_" . $room;
        $result = $this->db->s2o($sql);

        //room exists
        if (isset($result[0])) {
            return false;
        }


        $sql = "CREATE TABLE IF NOT EXISTS sb_TalkBox_" . $room . " (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                uname VARCHAR(20),
                tstamp DATETIME,
                message VARCHAR(500),
                ip INT UNSIGNED,
                PRIMARY KEY (id)
            )";

        $result = $this->db->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS sb_TalkBox_" . $room . "_mem (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                uname VARCHAR(20),
                tstamp DATETIME,
                message VARCHAR(500),
                ip INT UNSIGNED,
                PRIMARY KEY (id)
            ) ENGINE=MEMORY";

        $result = $this->db->query($sql);

        $line = new Chat_Line();
        $line->uname = 'paul';
        $line->message = 'Welcome to p:chat a PHP/Surebert chat solution by Paul Visco';
        $this->insert($line);

        $sql = "INSERT INTO sb_TalkBox_master 
            (name, created, last_visited, last_post) 
            VALUES ('" . $room . "', NOW(), NOW(), NOW());";

        $result = $this->db->query($sql);
    }

    public function insert(Chat_Line $line)
    {

        if (empty($line->message)) {
            return 0;
        }

        if (empty($line->uname) && $this->allow_guest_chat == 0) {
            return 0;
        }

        if ($this->allow_commands == 1) {

            $line->message = $this->check_commands($line->message);
        }

        $line->message = Bling::stripAll($line->message);

        $line->message = Bling::typoFix($line->message);

        $sql = "INSERT INTO sb_TalkBox_" . $this->room . " 
            ( uname, tstamp, message, ip) 
            VALUES ( :uname, NOW(), :message, INET_ATON(:ip))";

        $stmt = $this->db->prepare($sql);

        $values = Array(
            ":uname" => $line->uname,
            ":message" => $line->message,
            ":ip" => $line->ip
        );

        $stmt->execute($values);

        $sql = str_replace('sb_TalkBox_' . $this->room, 'sb_TalkBox_' . $this->room . '_mem', $sql);

        $stmt = $this->db->prepare($sql);

        if ($stmt->execute($values)) {
            $line->id = $this->db->lastInsertId();
            $this->updateLastVisit();
        }
        return $line;
    }

    public $loaded_from_backup = 0;

    public function display($id, $dir = "up", $limit = 10)
    {

        $ltgt = ($dir == "up") ? ">" : "<";

        $sql = "
            SELECT 
                id, 
                DATE_FORMAT(tstamp,'%m/%d %h:%i') AS t, 
                uname AS u, 
                message AS m 
            FROM sb_TalkBox_" . $this->room . "_mem 
            WHERE id " . $ltgt . " :id ";

        $sql .= "ORDER BY id DESC";

        if ($dir == 'down' || $id == 0) {
            $sql .=" LIMIT " . $limit;
        }

        $chatter = $this->db->s2o($sql, Array(":id" => $id));

        if (empty($chatter) && !$this->loaded_from_backup) {

            $sql = "INSERT INTO sb_TalkBox_" . $this->room . "_mem SELECT * FROM sb_TalkBox_" . $this->room;

            $this->db->query($sql);
            $this->loaded_from_backup = 1;
            $this->display($id, $dir);
        }

        $this->updateLastVisit();

        //TODO convert to event based callback
        if (function_exists('sb_TalkBoxOnParse')) {
            foreach ($chatter as &$line) {
                $line->m = sb_TalkBoxOnParse($line->m);
            }
        }
        return $chatter;
    }
}

