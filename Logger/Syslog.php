<?php
/**
 * As described by syslog RFC3164 http://tools.ietf.org/html/rfc3164
 *
 * e.g. <34>Oct 11 22:14:15 mymachine su: 'su root' failed for lonvick on /dev/pts/8
 *
 * Used to create syslog compatible logs
 *
 * @author Paul Visco
 * @package sb_Logger
 */
class sb_Logger_Syslog{

	/**
	 *   Facility values:
	 *      0 kernel messages
	 *      1 user-level messages
	 *      2 mail system
	 *      3 system daemons
	 *      4 security/authorization messages
	 *      5 messages generated internally by syslogd
	 *      6 line printer subsystem
	 *      7 network news subsystem
	 *      8 UUCP subsystem
	 *      9 clock daemon
	 *     10 security/authorization messages
	 *     11 FTP daemon
	 *     12 NTP subsystem
	 *     13 log audit
	 *     14 log alert
	 *     15 clock daemon
	 *     16 local user 0 (local0) (default value)
	 *     17 local user 1 (local1)
	 *     18 local user 2 (local2)
	 *     19 local user 3 (local3)
	 *     20 local user 4 (local4)
	 *     21 local user 5 (local5)
	 *     22 local user 6 (local6)
	 *     23 local user 7 (local7)
	 * @var integer
	 */
	public $facility = 16;

	/**
	 * The severity of the message
	 *	0 Emergency: system is unusable
	 *	1 Alert: action must be taken immediately
	 *	2 Critical: critical conditions
	 *	3 Error: error conditions
	 *	4 Warning: warning conditions
	 *	5 Notice: normal but significant condition (default value)
	 *	6 Informational: informational messages
	 *	7 Debug: debug-level messages
	 * @var integer
	 */
	public $severity = 5;

	/**
	 * (Optional) By default is hostname of machine logging, can override if logging for other machine
	 * no embedded space, no domain name, only a-z A-Z 0-9 and other authorized characters
	 * @var string
	 */
	public $hostname='';

	/**
	 * Process used to generate the message
	 * e.g. ls, su, php limit to alphnum < 32 chars, details can go in contents if longer is required
	 * @var string
	 */
	public $process;

	/**
	 * The plain text log message to send, limited to 1024 characters
	 * remember to allow space for the header which is ~40 chars
	 *
	 * Anything over 1024 will be truncated
	 * @var string
	 */
	public $content;

	/**
	 * Raw data packet to send instead of assembled message
	 * @var string
	 */
	protected $raw_data ='';

	/**
	 * Create a new log to send or save
	 * @param string $process The process used to create the log e.g. ls, su - limit to alphnum < 32 chars - truncates access
	 * @param string $content The plain text log message, must be under 1024
	 */
	public function __construct($process, $content, $facility=16, $severity=5){
		
		$this->process = $this->check_length($process, 32, 'process');
		
		$this->content = $this->check_length($content);

		$this->facility = intval($facility);
		$this->severity = intval($severity);
		if ($this->facility <  0) { $this->facility =  0;}
		if ($this->facility > 23) { $this->facility = 23;}
		if ($this->severity <  0) { $this->severity =  0;}
		if ($this->severity >  7) { $this->severity =  7;}
	}

	/**
	 * Saves data to local logs dir when syslog server is not available
	 * @param string $log_dir The log dir the data is written to
	 * @param string $log_type The log_type being written to
	 * @return boolean If the data was written or not
	 */
	public function save($log_dir=''){
		if(empty($log_dir)){
			$log_dir = ROOT.'/private/logs/syslog/';
		}
		
		if(!is_dir($log_dir)){
			mkdir($log_dir, 0777, true);
		}
	
		return file_put_contents($log_dir.date('Y_m_d').'.log', $this->construct_message()."\n", FILE_APPEND);
	}

	/**
	 * @param string $server The server to send syslog info to
	 * @param integer $port default port is 514
	 * @param integer $timeout The timeout on the udp connection
	 * @return string message returned or error string
	 */
	public function send($server='', $port=514, $timeout = 0){
		if(!empty($server)){
			$this->server = $server;
		}
		
		if (intval($timeout) > 0){
			$this->timeout = intval($timeout);
		}

		$message = $this->construct_message();

		$fp = fsockopen("udp://".$this->server, $this->port, $errno, $errstr);
		if ($fp){
			fwrite($fp, $message);
			fclose($fp);
			$result = $message;
		} else {
			$result = "ERROR: $errno - $errstr";
		}

		return $result;

	}

	/**
	 *Constructs the message to log from the properties of the instance
	 * @return string
	 */
	protected function construct_message(){
	
		if(!empty($this->raw_data)){
			$packet = $this->raw_data;
		} else {

			$this->process = $this->check_length($this->process, 32, 'process');

			$time = time();
			$month      = date("M", $time);
			$day        = substr("  ".date("j", $time), -2);
			$hhmmss     = date("H:i:s", $time);
			$tstamp  = $month." ".$day." ".$hhmmss;

			$priority    = "<".($this->facility*8 + $this->severity).">";
			$header = $tstamp." ".(empty($this->hostname) ? php_uname('n') : $this->hostname);

			$packet = $priority.$header." ".$this->process.": ".$this->content;

		}

		$packet = $this->check_length($packet);

		return $packet;

	}

	/**
	 *Checks to make sure the length of something is expected or warn and truncate
	 * @param string $str The string to check
	 * @param int $max_length Teh maximun length to check for
	 * @param string $type The type of thing to check packet or process
	 * @return <type>
	 */
	protected function check_length($str, $max_length=1024, $type='packet'){

		$strlen = strlen($str);
		if($strlen > $max_length){
			trigger_error("Syslog ".$type." is > ".$max_length." (".$strlen.") in length and will be truncated.  Original str is: ".$str, E_FATAL);
			return substr($str, 0, $max_length);
		}

		return $str;
	}

	/**
	 * Send a raw string instead of the assembled packet
	 * @param string $packet If this is set it is sent instead of contructing the message packet, allows you to construct custom packets to send
	 */
	public function set_raw_data($packet){

		$this->raw_packet = $packet;

	}
}
?>