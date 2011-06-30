<?php
/**
 * Used to connect to native SQL driver on a windows machine and run queries from non windows machines
 * @author paul.visco@roswellpark.org
 */
class sb_Mssql_Native{

	/**
	 * The windows proxy to send the request to
	 * @var string
	 */
	protected $proxy;

	/**
	 * The actual db host
	 * @var string
	 */
	protected $host;

	/**
	 * The database name
	 * @var string
	 */
	protected $db;

	/**
	 * The username to connectt to the database with
	 * @var string
	 */
	protected $uname;

	/**
	 * The password to connect to the database with
	 * @var string
	 */
	protected $pass;

	/**
	 * Set up the connection to the proxy
	 * @param string $proxy The proxy to connect to
	 * @param string $host The actual database host
	 * @param string $db The database name
	 * @param string $uname The db username
	 * @param string $pass  The db password
	 *
	 * <code>
	 * $con = new sb_Mssql_Native('http://padawan.roswellpark.org:9080/mssql.php', 'some_db_server.roswellpark.org', 'my_db', 'someuser', 'abc123');
	 * $rows = $con->get_rows("SELECT TOP 4 * FROM blah");
	 * </code>
	 */
	public function __construct($proxy, $host, $db, $uname, $pass){
		$this->proxy = $proxy;
		$this->host = $host;
		$this->db = $db;
		$this->uname = $uname;
		$this->pass = $pass;
	}

	/**
	 * Get the rows back with CURL
	 * @param string $sql The SQL statement to issue with an bound params
	 * @param array $params The bound params array is needed, null by default
	 * @return mixed array of rows or Exception
	 */
	public function get_rows($sql, $params=null){
		$data = Array(
			'server' => $this->host,
			'db' => $this->db,
			'uname' => $this->uname,
			'pass' => $this->pass,
			'sql' => $sql,
			'params' => $params
		);

		$ch = curl_init ($this->proxy);
		curl_setopt ($ch, CURLOPT_POST, true);
		curl_setopt ($ch, CURLOPT_POSTFIELDS, Array('request' => serialize($data)));
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		$returndata = curl_exec ($ch);

		if($returndata){
			$data = @unserialize($returndata);
			if($data){
				if(is_array($data)){
					return $data;
				} else {
					throw(new Exception($data));
				}
			} else {
				throw(new Exception($returndata));
			}
		}
	}

}
?>