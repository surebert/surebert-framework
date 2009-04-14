<?php

/**
 * Used to create search history
 * @author visco
 * 
 * Make sure you have the following table
 * <code>
create TABLE search_history(
	id INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
    query VARCHAR(100),
    num_results INT UNSIGNED,
    uid INT UNSIGNED,
    ip INT UNSIGNED,
    tstamp TIMESTAMP DEFAULT NOW()
);


$search_history = new sb_Search_History();
$search_history->uid = isset(App::$user->uid) ? App::$user->uid :0;
$search_history->record("Hello world", $num_results);
$search_history->record("Help", $num_results);
$search_history->record("Hell", $num_results);

$hints = $search_history->get_hints("he");

foreach($hints as $hint){
	echo '<li>'.$hint->query.' '.$hint->num.'</li>';
}
 *</code>
 */
class sb_Search_History{
	
	/**
	 * The uid of the person who is making the request
	 * @var integer
	 */
	public $uid = 0;
	
	/**
	 * 
	 * @param $query The query being recorded
	 * @param $uid The uid of the user making the query, 0 for no uid or anonymous/guest
	 * @return boolean If the query was recoreded or not
	 */
	public function record($query){
		
		$sql = "INSERT INTO search_history (query, ip, uid) VALUES (:query, INET_ATON(:ip), :uid)";
		
		$stmt = App::$db->prepare($sql);
		
		$result = $stmt->execute(Array(
			':query' => $query,
			':ip' => Gateway::$remote_addr,
			':uid' => $this->uid
		));
		
		return $result ? 1 : 0;
	}
	
	/**
	 * load the search hints from the search_history table
	 * @param $query The query to find hints for
	 * @param $limit 
	 * @return Array An array of object with ->query and ->num properties, representing similar queries issued within the X number of days, and the number of times they were issued
	 */
	public function get_hints($query, $limit=10){
		
		$limit = is_numeric($limit) ? $limit : 10;
		
		$sql = "
		SELECT 
			query,
			count(query) AS num_requests
		FROM 
			search_history 
		WHERE 
			query LIKE :query
			AND tstamp >= DATE_SUB(CURDATE(),INTERVAL 30 DAY)
			
		GROUP BY query
		LIMIT ".$limit;
		
		return App::$db->s2o($sql, Array(":query" => $query.'%'));
	}
	
	
}
?>