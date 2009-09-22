<?php
/**
 * Used to connect to and interact with windows machines from php code on a linux machine
 *	maps the smbclient application to a local PHP object.
 * @author: Anthony Cashaw, Paul Visco
 * @version: 1.0 09/09/09 09/22/09
 * @required: smbclient command line program
 * <code>
 * $win = new sb_Samba_Connection('Compy', 'c$', 'fella', 'supasecrect', 'Workspace');
 * print_r($win->ls());
 *
 * </code>
 */
class sb_Samba_Connection {

/**
 * The domain of the connecting windows account
 * @var string
 */
	private $domain;

	/**
	 * The host machine that this share is on
	 * @var string
	 */
	private $host;
	/**
	 * Object to log with
	 * @var sb_Logger_Base
	 */
	public static $log;
	/**
	 * The password that to the windows account
	 * @var string
	 */

	/**
	 * Weather to log the ls transactions
	 * @var boolean
	 */
	public static $logls = 0;

	/**
	 * The password to the samba share
	 * @var string
	 */
	private $password;

	/**
	 * The name of the share on the host machine
	 * @var string
	 */
	private $share;

	/**
	 * The user name of the windows account
	 * @var string
	 */
	private $username;


	/**
	 * Class constructor for the rp_WindowsShare class.
	 * @param $uname
	 * @param $pass
	 * @param $path
	 * @return unknown_type
	 */
	public function __construct($host, $share, $uname, $pass, $domain = '') {
		$this->username = $uname;
		$this->password = $pass;
		$this->domain = $domain;
		$this->host = $host;
		$this->share = $share;
	}

	/**
	 * Copies files from the windows machine to the linux machine
	 * @var $getpath the file path at the windows machine
	 * @var $putpath the file path on the local linux box where the file will be placed
	 * @var $output the raw command line output for smbclient
	 */
	public function copy($remotepath, $localfile = '.', &$output = null) {
		//get file string massage
		$remotepath = self::winslashes($remotepath);
		$this->execute("get $remotepath $localfile", $output);
		return $output ? 0 : 1;

	}


	/**
	 * Executes the command line function that completes the remote windows operations
	 * @param $command string the command to issue to the smbclient
	 * @param $output array what the command line returns
	 * @param $log boolean weather to log this transaction
	 */
	private function execute($command, &$output = null) {

		$cmd = "smbclient '\\\\{$this->host}\\{$this->share}' $this->password -U $this->username -W $this->domain -c '$command' 2>&1";
		exec($cmd, $output, $return);

		//LOG: Transaction
		if(self::$log) {
			self::$log->samba("Command: $cmd \n Output:" . print_r($output, 1) . "\n Return: " . print_r($return, 1) . "\n\n\n");
		}

	}

	/**
	 * Returns a list of the contents of the root of the share, or what ever directory is requested in $subdir
	 * @param $subdir
	 * @return unknown_type
	 */
	public function ls($subdir = '', &$raw = NULL) {

		$teststr  = str_replace('\\', '-', $subdir);
		$nub =  (preg_match('/[-?|\/?]*(\w+\.\w{1,4})/', $teststr))?'':'\*';

		$this->execute("ls $subdir".$nub, $raw_ls);

		$raw = $raw_ls;
		$ret = ($raw_ls)? $this->processLS($raw_ls, $subdir):0;
		return $ret;
	}

	/**
	 * Returns a list of the contents of the root of the share, or what ever directory is requested in $subdir
	 * @param $subdir
	 * @return unknown_type
	 */
	public function dir($subdir = '', &$raw = NULL) {

		return $this->ls($subdir, $raw);
	}

	/**
	 * Allows the placent of files from the local system to the remote windows system
	 * @todo  fix the remote file portion of the command string
	 * @param $localfile
	 * @param $remotefile
	 * @param $output
	 * @return boolean success
	 */
	public function paste($localfile, $remotefile = "." , &$output = null) {

		$remotefile = rtrim($remotefile, "/ \\");

		$remotefile = self::winslashes("$remotefile");
		$command = "put $localfile $remotefile";

		$this->execute($command, $output);
		return $output ? 0 : 1;
	}

	/**
	 * Internal operation: converts raw commanline ls returns into an array of samba share listing objects
	 * @private
	 */
	private function processLS($raw_ls, $subdir = '') {
		$ret = array();

		foreach($raw_ls as $listing) {
			$temp = $this->parseListing($listing, $subdir);
			if($temp) {
				$ret[] = $temp;
			}
		}

		return $ret;
	}


	/**
	 * Converts a line of returned output into a sb_Samba_Listing object
	 * @param $listing
	 * @param $subdir
	 * @return sb_Samba_Listing
	 */
	private	function parseListing($listing, $subdir = '') {
		$ret = new sb_Samba_Listing();
		$exp = '/^\s*(\w+\.?\w{3})\s+([A-Z]?)\s+(\d+)\s+(\w{3}.+)$/';

		preg_match_all($exp, $listing, $matches);

		if($matches[0]) {
			$ret->name = $matches[1][0];
			$ret->type = $matches[2][0];
			$ret->size = $matches[3][0];
			$ret->path = $subdir;
			$ret->datemodified =  $matches[4][0];

			return $ret;
		}

		return 0;
	}

	/**
	 * Converts all slashes in a string to be windows readable slashes
	 * @param $str
	 * @return string
	 */
	public static function winslashes($str) {
		return str_replace("/", "\\", $str);
	}

}

?>