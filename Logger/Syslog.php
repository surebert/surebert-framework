<?php

/**
 * As described by syslog RFC3164 http://tools.ietf.org/html/rfc3164
 *
 * e.g. <34>Oct 11 22:14:15 mymachine su: 'su root' failed for lonvick on /dev/pts/8
 *
 * Used to create syslog compatible logs
 *
 * $syslog = new \sb\Logger\Syslog('myprocess');
 * $syslog->setMessage('hello log', 1, 11)->save()
 * //OR
 * $syslog->setMessage('hello log', 1, 11)->send('mylogserver.com');
 * @author paul.visco@roswellpark.org
 * @package Logger
 */
namespace sb\Logger;

class Syslog
{

    /**
     * (Optional) By default is hostname of machine logging, can override if logging for other machine
     * no embedded space, no domain name, only a-z A-Z 0-9 and other authorized characters
     * @var string
     */
    public $hostname = '';

    /**
     * Process used to generate the message
     * e.g. ls, su, php limit to alphnum < 32 chars, details can go in contents if longer is required
     * @var string
     */
    public $process;

    /**
     * The server to send to when sending, can also be passed as argument to ->send()
     * @var string
     */
    public $server;

    /**
     * The port to send on
     * @var integer
     */
    public $port = 514;

    /**
     * The maximum packet length
     * @var integer
     */
    public $max_length = 1024;

    /**
     * The current line being logged
     * @var string
     */
    protected $message = '';

    /**
     * The agent string if needed, represents the user who generated the message
     * @var string
     */
    protected $agent = '';

    /**
     * Create a new log to send or save
     * @param string $process The process used to create the log e.g. ls, su - limit to alphnum < 32 chars - truncates access
     * @param string $hostname The hostname of the machine t1024hat generated the log messages
     * *  e.g. ls, su, php limit to alphnum < 32 chars, details can go in contents if longer is required
     *
     */
    public function __construct($process, $hostname = '')
    {

        $this->process = $this->checkLength($process, 32, 'process');
        $this->hostname = $hostname ? $hostname : php_uname('n');
    }

    /**
     *
     * @param string $agent Sets the user agent that produced the message within the system
     */
    public function setAgent($ip, $identifier)
    {
        $this->agent .= '|' . $ip . '|' . $identifier . '|';
    }

    /**
     * Set the message to send or save
     * @param string $content The plain text log message, must be under 1024
     * Anything over 1024 will be truncated remember to allow space for the
     * header which is ~40 chars
     *
     * @param integer $severity The severity of the message
     *        0 Emergency: system is unusable
     *        1 Alert: action must be taken immediately
     *        2 Critical: critical conditions
     *        3 Error: error conditions
     *        4 Warning: warning conditions
     *        5 Notice: normal but significant condition (default value)
     *        6 Informational: informational messages
     *        7 Debug: debug-level messages
     *
     *  @param integer $facility
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
     *     21 local user 5 (local5)Syslog
     *     22 local user 6 (local6)
     *     23 local user 7 (local7)
     *
     *  @param integer $time The tstamp to override the current time
     *    @return object This so you can chain ->send or ->save
     */
    public function setMessage($content, $severity = 5, $facility = 16, $time = null)
    {

        $this->content = $this->checkLength($content);

        $facility = intval($facility);
        $severity = intval($severity);
        if ($facility < 0) {
            $facility = 0;
        }
        if ($facility > 23) {
            $facility = 23;
        }
        if ($severity < 0) {
            $severity = 0;
        }
        if ($severity > 7) {
            $severity = 7;
        }

        $this->process = $this->checkLength($this->process, 32, 'process');

        $tstamp = $this->getTstamp($time);

        $priority = "<" . ($facility * 8 + $severity) . ">";
        $header = $tstamp . " " . $this->hostname;

        $this->message = $priority . $header . " " . $this->process . ": " . $this->agent . $this->content;

        $this->message = $this->checkLength($this->message);

        return $this;
    }

    /**
     * Gets the BSD syslog style timestamp Nov 17 12:30:19
     * @param The optional time to use, any format that strtotime understands
     * @return string
     */
    protected function getTstamp($time = null)
    {
        $time = !is_null($time) ? strtotime($time) : time();
        $month = date("M", $time);
        $day = substr("  " . date("j", $time), -2);
        $hhmmss = date("H:i:s", $time);
        return $month . " " . $day . " " . $hhmmss;
    }

    /**
     *
     * @param string $message The raw message to send
     * @return object This so you can chain ->send or ->save
     */
    public function setRawMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Saves data to local logs dir when syslog server is not available
     * @param string $log_dir The log dir the data is written to
     * @param string $log_type The log_type being written to
     * @return boolean If the data was written or not
     */
    public function save($log_dir = '')
    {
        if (empty($log_dir)) {
            $log_dir = \ROOT . '/private/logs/syslog/';
        }

        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0777, true);
        }

        return file_put_contents($log_dir . date('Y_m_d') . '.log',
                $this->message . "\n", \FILE_APPEND);
    }

    /**
     * Returns the current message for debugging
     * @return string
     */
    public function debug()
    {
        return $this->message;
    }

    /**
     * @param string $server The server to send syslog info to
     * @param integer $port default port is 514
     * @param integer $timeout The timeout on the udp connection
     * @return string message returned or error string
     */
    public function send($server = '', $port = 514, $timeout = 0)
    {
        if (!empty($server)) {
            $this->server = $server;
        }

        $this->port = $port;

        if (empty($this->server)) {
            throw new \Exception('No server to send to has been specified', E_USER_WARNING);
        }

        if (intval($timeout) > 0) {
            $this->timeout = intval($timeout);
        }

        $fp = fsockopen("udp://" . $this->server, $this->port, $errno, $errstr);
        if ($fp) {
            $result = fwrite($fp, $this->message);
            fclose($fp);
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * Checks to make sure the length of something is expected or warn and truncate
     * @param string $str The string to check
     * @param int $max_length The maximun length to check for, -1 means infinite length
     * @param string $type The type of thing to check packet or process
     * @return string The string truncated to max length
     */
    protected function checkLength($str, $max_length = '', $type = 'packet')
    {
        $max_length = $max_length ? $max_length : $this->max_length;
        if ($max_length == -1) {
            return $str;
        }

        $strlen = strlen($str);
        if ($strlen > $max_length) {
            throw new \Exception("Syslog " . $type . " is > " . $max_length . " (" . $strlen . ") in length and will be truncated.  Original str is: " . $str, E_USER_WARNING);
            return substr($str, 0, $max_length);
        }

        return $str;
    }
}

