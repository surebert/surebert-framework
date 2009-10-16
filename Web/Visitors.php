<?php
/**
 *
 * Keeps track of online users with an in memory mysql table
 *
 * @author Paul Visco
 * @version 2.22 10-02-2004 06-25-2009
 *
 * Requires the following SQL change myDatabase database to a database you want to use, if you already have one, skip the create step and just use it.  replace @myDatabase, @myUser, @myPass and @myHost with the appropriate data for your project
 *
 *
 * Make sure to change the length of uname VARCHAR to the longest uname you will store
 CREATE DATABASE @myDatabase;
 USE @myDatabase;
 CREATE TABLE online_visitors(
	id INT UNSIGNED NOT NULL AUTO_INCREMENT,
	mobl CHAR(1) DEFAULT 0,
	ip INT,
	tstamp INT(10) UNSIGNED,
	uname VARCHAR(15),
    dname VARCHAR(50),
	agent VARCHAR(50),
	agent_str VARCHAR(500),
	PRIMARY KEY (id)
) ENGINE = MEMORY;

GRANT ALL ON @myDatabase.* TO '@myUser'@'@myHost' IDENTIFIED BY '@myPass';

 * <code>
	$visitor = new sb_Web_Visitor(App::$user->uname, App::$user->dname);
    $visitor->log(App::$db);

	$visitors = sb_Web_Visitors::get_visitor_data();
	echo '<b title="'.implode(",", sb_Web_Visitors::list_users()).'">(?) </b>users: '.$visitors->users.'/ guests: '.$visitors->guests.'/ bots:'.$visitors->bots;
 * </code>
 *
 */
class sb_Web_Visitors{

	/**
	 * The database connection
	 *
	 * @var a pdo connection to the mysql database
	 */
	public static $db;
	/**
	 * Determines if debug messages are written to screen
	 *
	 * @var boolean
	 */
	public static $debug = 0;

	/**
	 * List of known user agents that can be distilled from longer user agent names
	 *
	 * @var array
	 */
	public static $bots = array("google", "yahoo", "msn", "crawl", "spider", "bot", "jakarta", "blog","searchfox", "rss", "feeddemon");

	/**
	 * List of shorted user agents
	 *
	 * @var Array
	 */
	public static $agents = array("firefox", "safari", "msie 5.5", "msie 6.0", "msie 7.0", "msn", "netscape", "mac_powerpc", "opera");

	/**
	 * How long, in seconds, without refresh that a guest is considered online 300=5 min, 1-hour = 3600, 1-day = 86400,
	 *
	 * @var integer
	 */
	public static $time_before_offline = 60;

	/**
	 * How long, in seconds, before guests are deleted from table 300=5 min, 1-hour = 3600, 1-day = 86400,
	 *
	 * @var integer
	 */
	public static $time_before_expire = 150;

	/**
	 * Are array of users online with a uname
	 *
	 * @var array
	 */
	public $users = array();

	/**
	 * The number of non-logged guests online
	 *
	 * @var integer
	 */
	public $num_guests;

	/**
	 * The number of robots online
	 *
	 * @var integer
	 */
	public $num_robots;

	/**
	 * The number of users online
	 *
	 * @var integer
	 */
	public $num_users;

	/**
	 * Logs a user into the system and removes any expired users
	 *
	 * @param sb_Web_Visitor $visitor
	 */
	public static function log(sb_Web_Visitor $visitor){

		$visitor = self::distill($visitor);

		self::insert($visitor);

		self::expire($visitor);

	}

	/**
	 * Inserts a new online visitor into the database
	 *
	 * @param sb_Web_Visitor $visitor
	 */
	private static function insert(sb_Web_Visitor $visitor){


		$delete = self::$db->prepare("DELETE FROM online_visitors WHERE ip = INET_ATON(:ip) AND uname='guest' OR uname=:uname");

		$delete->execute(Array(
			':ip' => $visitor->ip,
			':uname' => $visitor->uname
		));

		$sql = "INSERT INTO online_visitors (mobl, ip, tstamp, uname, dname, agent, agent_str) VALUES (:mobl, INET_ATON(:ip), :tstamp, :uname, :dname, :agent, :agent_str)";

		$insert = self::$db->prepare($sql);

		$values = Array(
			':mobl' => $visitor->mobl,
			':ip' => $visitor->ip,
			':tstamp' => $visitor->tstamp,
			':uname' => $visitor->uname,
            ':dname' => $visitor->dname,
			':agent' => $visitor->agent,
			':agent_str' => $visitor->agent_str
		);

		if(!$insert->execute($values) && self::$debug == 1){
			print_r($insert->errorInfo());
		}
	}

	/**
	 * Parses the visitor user agent data to determine if it is a bot or not
	 *
	 * @param sb_Web_Visitor $visitor
	 * @return sb_Web_Visitor The visitor with data parsed
	 */
	private static function distill(sb_Web_Visitor $visitor){

		//covert to agent_str to lowercase for comparison
		$agent_str = strtolower($visitor->agent_str);

		//empty agents and those with bot are considered bots
		if(substr_count($agent_str, "bot") || empty($agent_str)){

			$visitor->agent = 'bot';
		}

		//check for bot words in user agent
		foreach (self::$bots as $bot){

			if (substr_count($agent_str, $bot) != 0){
				$visitor->uname =  "";
				$visitor->agent =  "bot";
			}
		}

		//check for recogized agents and use short name
		foreach (self::$agents as $agent){

			if (substr_count($agent_str, $agent) != 0){
				$visitor->agent =  $agent;
				$visitor->agent_str =  "";
			}
		}

		if(empty($visitor->uname) && $visitor->agent !='bot'){
			$visitor->uname = 'guest';
		}

		return $visitor;
	}

	/**
	 * Expires a user from teh database when they are no longer fresh
	 *
	 * @param sb_Web_Visitor $visitor
	 */
	private static function expire(sb_Web_Visitor $visitor){
		$expiration = (time()-self::$time_before_expire);
		$sql = "DELETE FROM online_visitors WHERE tstamp < :expiration";

		self::$db->s2o($sql, Array(":expiration" => $expiration));
	}

	/**
	 * Loads an array of usernames online, excluding guest
	 *
	 * @return sb_Web_Visitors Object with data
	 */
	public static function get_visitor_data(){

		$expiration = (time()-self::$time_before_offline);

		$sql ="SELECT (SELECT COUNT(ip) FROM online_visitors WHERE uname ='guest' AND tstamp > :expiration) as guests, (SELECT COUNT(ip) FROM online_visitors WHERE agent ='bot' AND tstamp > :expiration) as bots, (SELECT COUNT(ip) FROM online_visitors WHERE uname !='guest' AND uname !='' AND tstamp > :expiration) AS users";

		$visitors = self::$db->s2o($sql, Array("expiration" => $expiration), 'sb_Web_VisitorCount');

		if(count($visitors) > 0){
			return $visitors[0];
		} else {
			return new sb_Web_Visitors();
		}

	}

	/**
	 *	Loads an array of users currently online
	 *
	 * @return array The usernames of the users online
	 */
	public static function list_users($unames_only=false){
		$expiration = (time()-self::$time_before_offline);

		$sql = "SELECT DISTINCT uname, dname FROM online_visitors WHERE uname !='guest' AND uname !='' AND tstamp > :expiration ORDER BY uname";
		$result = self::$db->s2o($sql, Array(":expiration" => $expiration));
		$users = Array();
		foreach($result as $user){
			if($unames_only){
				$name = $user->uname;
			} else {
				$name = !empty($user->dname) ? $user->dname : $user->uname;
			}
            
			array_push($users, $name);
		}

		return $users;

	}

	/**
	 * Dumps the users and IP addresses from the database into an html table
	 *
	 * @return string The html table
	 */
	public static function dump_users(){

		$expiration = (time()-self::$time_before_expire);

		$sql = "SELECT DISTINCT uname, INET_NTOA(ip) AS ip FROM online_visitors WHERE tstamp > :expiration";
		$users = self::$db->s2o($sql, Array(":expiration" => $expiration));

		$data = "\nuname\tip";
		foreach($users as $user){

			$data .= "\n".$user->uname."\t".$user->ip;
		}

		return $data;
	}

	/**
	 * Delete a user from the system when they log out
	 *
	 * @param string $uname The username of the user to logout
	 */
	public static function user_logout($uname){
		$sql = "DELETE FROM online_visitors WHERE uname =:uname";

		$result = self::$db->s2o($sql, Array(":uname" => $uname));
	}
}


/**
 * Represents all online users
 *
 * @author Paul Visco
 * @version 2.2 10-02-2004 06-25-2009
 *
 *
 */
class sb_Web_VisitorCount{

	public $bots = 0;
	public $guests = 0;
	public $users = 0;
}
?>