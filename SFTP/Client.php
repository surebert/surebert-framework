<?php
/**
 * Uses the pecl ssh2 extension in order
 * based on code sample from php manual http://www.php.net/manual/en/ref.ssh2.php
 * requires php-pecl-ssh2 extension
 *
 * <code>
 * try{
 *        $client = new \sb\SFTP\Client("server.com", 1027);
 *        $client->login("uname", "pass");
 *        OR
 *        $client->loginWithKey('uname', 'id_rsa.pub', 'id_rsa', '');
 *        $client->put("/tmp/to_be_sent", "/tmp/to_be_received");
 *        $client->get("/tmp/to_be_received", "/tmp/to_be_sent_new");
 * } catch (Exception $e){
 *        echo $e->getMessage() . "\n";
 * }
 * </code>
 *
 * @package SFTP
 */
namespace sb\SFTP;

class Client extends \sb\SSH2\Client implements \sb\FTP\Base{

    protected $sftp;

    /**
     * Instantiates the ssh2 connection
     * @param string $host The host server to connect to
     * @param integer $port The port to connect on
     */
    public function __construct($host, $port=22)
    {
        parent::__construct($host, $port);
    }

    /**
     * Connects to the SFTP subsystem
     */
    protected function connect()
    {
        $this->sftp = ssh2_sftp($this->connection);
        if (!$this->sftp){
            throw new \Exception("Could not initialize SFTP subsystem.");
        }
    }


    /**
     * Login to a remote server with uname and pass based credentials
     * @param string $uname The user name to log in with
     * @param <type> $pass The password to login in with
     */
    public function login($uname, $pass)
    {

        if(parent::login($uname, $pass)){
            $this->connect();
        }

        return true;
    }

    /**
     * Login with public key
     * @param string $uname The username to login in as
     * @param string $public_key_file The path to the public key file to use (id_rsa.pub), make sure it is readible by your script
     * @param string $private_key_file The private key file to use id (id_rsa), make sure it is readible by your script
     * @param string $pass The passphrase of the keyfile to use if one is required
     */
    public function loginWithKey($uname, $public_key_file, $private_key_file, $pass='')
    {

         if(parent::login($uname, $public_key_file, $private_key_file, $pass))
    {
            $this->connect();
        }

        return true;
    }

    /**
     * Put a file on the remote machine
     * @param string $local_file The path to the local file to read
     * @param string $remote_file The path to the remote file to write
     * @param integer $mode File permission mode, if is set, uses scp which is slower
     */
    public function put($local_file, $remote_file, $mode=null)
    {

        if(!is_null($mode)){
            return $this->scpSend($local_path, $remote_path, $mode);
            return true;
        }
        $stream = @fopen("ssh2.sftp://".$this->sftp.$remote_file, 'w');

        if (! $stream){
            throw new \Exception("Could not open file: $remote_file");
        }

        $data_to_send = @file_get_contents($local_file);
        if ($data_to_send === false){
            throw new \Exception("Could not open local file: $local_file.");
        }

        if (@fwrite($stream, $data_to_send) === false){
            throw new \Exception("Could not send data from file: $local_file.");
        }

        @fclose($stream);

        return true;
    }

    /**
     * Get a file from the remote machine
     *
     * @param string $remote_file The path to the remote file to read
     * @param string $local_file The path to the local file to write
     */
    public function get($remote_file, $local_file)
    {

        $stream = @fopen("ssh2.sftp://".$this->sftp.$remote_file, 'r');
        if (! $stream){
            throw new \Exception("Could not get remote file:". $remote_file);
        }

        $size = $this->size($remote_file);
        $contents = '';
        $read = 0;
        $len = $size;
        while ($read < $len && ($buf = fread($stream, $len - $read))) {
          $read += strlen($buf);
          $contents .= $buf;
        }
        file_put_contents ($local_file, $contents);
        @fclose($stream);

        return true;

    }

    /**
     * Lists the contents of a remote directory
     * @param string $remote_dir
     * @return Array The array of files
     */
    public function ls($remote_dir)
    {
        $dir = "ssh2.sftp://".$this->sftp.$remote_dir;
        $handle = opendir($dir);
        $files = Array();
        while (false !== ($file = readdir($handle))) {
            if (substr($file, 0, 1) != '.'){
                $files[] = $file;
            }
        }
        
        closedir($handle);

        return $files;

    }

    /**
     * Gets the size of a remote file
     * @param string $remote_file path to remote file
     * @return integer The size of the file in bytes
     */
    public function size($remote_file)
    {
        return filesize("ssh2.sftp://".$this->sftp.$remote_file);
    }

    /**
     * Renames remote files
     * @param string $from The old path/file name
     * @param string $to The new path/file name
     */
    public function renameRemoteFile($from, $to)
    {
        if(@ssh2_sftp_rename($this->sftp, $from, $to)){
            throw new \Exception("Could not rename file from $from to $to");
        }

        return true;
    }

    /**
     * Creates a remote directory
     * @param string $path Delete remote path
     */
    public function mkdir($path)
    {
        if(@ssh2_sftp_mkdir($this->sftp, $path)){
            throw new \Exception("Could not create remote directory: ".$path);
        }

        return true;
    }

    /**
     * Removes a remote directory - be careful!
     * @param string $path Delete remote path
     */
    public function rmdir($path)
    {
        if(@ssh2_sftp_rmdir($this->sftp, $path)){
            throw new \Exception("Could not remove remote directory: ".$path);
        }

        return true;
    }

    /**
     * Removes a remote file - be careful!
     * @param string $path Delete remote path
     */
    public function delete($path)
    {
        return ssh2_sftp_unlink($this->sftp, $path);
    }

    /**
     * Gets remote file stats
     * @param string $path The path to the file to get stats for
     * @return Array with size, gid, uid, atime, mtime, mode keys
     */
    public function getFileStats($path)
    {
        $stats = @ssh2_sftp_stat($this->sftp, $path);
        if(!$stats['size']){
            throw new \Exception("Could get file stat: ".$path);
        }
        return $stats;
    }

    /**
     * Return the target of a symbolic link
     * @param string $path
     * @return string the real path to the file
     */
    public function readlink($path)
    {
        $result = @ssh2_sftp_readlink($this->sftp, $path);
        if(!$result){
            throw new \Exception("Could get readlink: ".$path);
        }
        return $result;
    }

    /**
     * Create a symlink on the remote system
     * @param string $orig_path The path to the original remote file
     * @param string $symlink_path The path to the symlink you want to create
     * @return boolean success or failure
     */
    public function ssh2SftpSymlink($orig_path, $symlink_path)
    {
        $result = @ssh2_sftp_symlink($this->sftp, $orig_path, $symlink_path);
        if(!$result){
            throw new \Exception("Could create symlink: ".$path);
        }
        return $result;
    }

    public function chmod($file, $mode, $recursive=false) 
    {
        if (!$this->exists($file)){
            return false;
        }


        if ( ! $recursive || ! $this->is_dir($file) )
            return $this->run_command(sprintf('chmod %o %s', $mode, escapeshellarg($file)), true);
        return $this->run_command(sprintf('chmod -R %o %s', $mode, escapeshellarg($file)), true);
    }

    /**
     * Use scp to send, slower than $this->get which uses sftp but allows mode change
     * @param string $local_path The local file path
     * @param string $remote_path The remote file path
     * @param int $mode The file mode to set for the remote file
     * @return boolean
     */
    protected function scpSend($local_path, $remote_path, $mode=0644)
    {
        if(@ssh2_scpSend($connection, $remote_path, $remote_path, $mode)){
            throw new \Exception("Could send file with scp: ".$local_path.' to '.$remote_path);
        }
    }

}
