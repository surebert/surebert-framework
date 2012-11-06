<?php
/**
 * Used to connect to and interact with windows machines from php code on a linux machine
 *    maps the smbclient command line executable to a local PHP object.
 *
 * @author , Paul Visco, Anthony Cashaw
 * @package Samba
 */
namespace sb\Samba;

class Connection 
    {

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
     * Determines if commands executed and raw results are echoed in stdout
     * @var boolean
     */
    public $debug = false;

    /**
     * Object to log with
     * @var \sb\Logger_Base
     */
    public static $log;

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
     *
     * <code>
     * $win = new \sb\Samba_Connection('Compy', 'c$', 'fella', 'supasecrect', 'Workspace');
     * print_r($win->ls());
     * </code>
     *
     * @param $uname
     * @param $pass
     * @param $path
     *
     */
    public function __construct($host, $share, $uname, $pass, $domain = '') 
    {
        $this->username = $uname;
        $this->password = $pass;
        $this->domain = $domain;
        $this->host = $host;
        $this->share = $share;
    }

    /**
     * gets files from the windows machine to the linux machine
     * @param $remotepath the file path at the windows machine
     * @param $localfile the file path on the local linux box where the file will be placed
     *
     * @return array $output the raw command line output for smbclient
     */
    public function get($remotepath, $localfile = '.') 
    {
        $remotepath = self::winslashes($remotepath);
        $this->execute('get "'.$remotepath.'" "'.$localfile.'"', $output);
        return $output;

    }

    /**
     * Allows the placent of files from the local system to the remote windows system
     * @param $localfile The local file on the linux server
     * @param $remotepath The remote path to use
     *
     * @return array $output
     */
    public function put($localfile, $remotepath = ".") 
    {
        $remotepath = self::winslashes($remotepath);
        $command = 'put "'.$localfile.'" "'.$remotepath.'"';

        $this->execute($command, $output);
        return $output;
    }

    /**
     * rename a remote file
     * @param string $remote_file_path The original file path/name
     * @param string $new_remote_file_path The new file path/name
     *
     * @return array $output
     */
    public function rename($remote_file_path, $new_remote_file_path)
    {
        //get file string massage
        $remote_file_path = self::winslashes($remote_file_path);
    $new_remote_file_path = self::winslashes($new_remote_file_path);
    $this->execute('rename "'.$remote_file_path.'" "'.$new_remote_file_path.'"', $output);
    return $output;
    }

    /**
     * Executes the command line function that completes the remote windows operations
     * @param $command string the command to issue to the smbclient
     * @param $output array what the command line returns
     * @param $log boolean weather to log this transaction
     */
    public function execute($command, &$output = null) 
    {

        $cmd = "smbclient '\\\\{$this->host}\\{$this->share}' $this->password -U $this->username -W $this->domain -c '$command' 2>&1";
        exec($cmd, $output, $return);

        if(stristr(implode(" ", $output), 'NT_STATUS_ACCOUNT_LOCKED_OUT')){
            throw(new \Exception('NT_STATUS_ACCOUNT_LOCKED_OUT: '.$this->username));
        }

        if($this->debug == true){
            file_put_contents("php://stdout", "\n".$cmd);
            file_put_contents("php://stdout", "\n".print_r($output, 1));
        }

        //LOG: Transaction
        if(self::$log) {
            self::$log->samba("Command: $cmd \n Output:" . print_r($output, 1) . "\n Return: " . print_r($return, 1) . "\n\n\n");
        }

        return $return;
    }

    /**
     * Returns a list of the contents of the root of the share, or what ever directory is requested in $subdir
     * @param $subdir
     * @return unknown_type
     */
    public function ls($subdir = '', &$raw = NULL) 
    {

        $teststr  = str_replace('\\', '-', $subdir);
        $nub =  (preg_match('/[-?|\/?]*([\w \-]+\.\w{1,4})/', $teststr))?'':'\*';

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
    public function dir($subdir = '', &$raw = NULL) 
    {

        return $this->ls($subdir, $raw);
    }

    /**
     * Internal operation: converts raw commanline ls returns into an array of samba share listing objects
     * @private
     */
    private function processLS($raw_ls, $subdir = '') 
    {
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
     * Converts a line of returned output into a \sb\Samba_Listing object
     * @param $listing
     * @param $subdir
     * @return \sb\Samba\Listing
     */
    private function parseListing($listing, $subdir = '') 
    {
        $ret = new \sb\Samba\Listing();
        $exp = '/^\s{2}([\w \-]+\.?\w{3,4})\s+([A-Z]?)\s+(\d+)\s+(\w{3}.+)$/';

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
     *
     * @param string $local_dir The local directory  e.g. /blah/foo/
     * @param string $remote_dir The remote directory  e.g. \windows\share\files
     *
     * @param array $output The raw out as a reference in array if passed
     * @return boolean was it successful or not
     */
    public function mput($local_dir, $remote_dir, $file_pattern='*', &$raw = NULL)
    {
        $result = $this->execute('cd '.self::winslashes($remote_dir).';lcd '.$local_dir.';prompt;mput '.$file_pattern.';exit;', $raw);

        return $result;
    }

    /**
     *
     * @param string $remote_dir The remote directory to make e.g. \windows\share\files
     *
     * @param array $output The raw out as a reference in array if passed
     * @return boolean was it successful or not
     */
    public function mkdir($remote_dir, &$raw = NULL)
    {
        $result = $this->execute('mkdir '.self::winslashes($remote_dir), $raw);

        return $result;
    }

    /**
     * Converts all slashes in a string to be windows readable slashes
     * @param $str
     * @return string
     */
    public static function winslashes($str) 
    {
        return str_replace("/", "\\", $str);
    }

}

