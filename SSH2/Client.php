<?php
/**
 * Create an SSH2 client for executing commands also the basis of SFTP client
 * requires php-pecl-ssh2 extension
 * see http://php.net/manual/en/wrappers.ssh2.php for more info
 *
 * <code>
 * $client = new \sb\SSH2\Client('server.com', 1027);
 * $client->login('uname', 'pass');
 * OR
 * $client->loginWithKey('uname', 'id_rsa.pub', 'id_rsa', '');
 *
 * $data = $client->exec('ls /tmp');
 * </code>
 * 
 * @author paul.visco@roswellpark.org
 * @package SSH2
 */
namespace sb\SSH2;

class Client{

    protected $connection;

    /**
     * Instantiates the ssh2 connection
     * @param string $host The host server to connect to
     * @param integer $port The port to connect on
     */
    public function __construct($host, $port=22)
    {

        if(!function_exists('ssh2_connect')){
            throw new \Exception("You must install pecl ssh2 extension to use \sb\SFTP_Client");
        }

        $this->connection = \ssh2_connect($host, $port);
        if(!$this->connection){
            throw new \Exception("Could not connect to $host on port $port.");
        }

    }

    /**
     * Login to a remote server with uname and pass based credentials
     * @param string $uname The user name to log in with
     * @param <type> $pass The password to login in with
     */
    public function login($uname, $pass)
    {
        
        if (!@ssh2_auth_password($this->connection, $uname, $pass)){
            throw new \Exception("Could not authenticate with credentials given.");
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
        if (!ssh2_auth_pubkey_file($this->connection, $uname, $public_key_file, $private_key_file, $pass))
    {
            throw new \Exception("Could not authenticate with credentials given.");
        }

        return true;
    }

    /**
     *
     * @param string $command The command to exec on the remote server. You can separate multiple commands with ;
     * @param integer $timeout The timeout to wait for the command to exec
     * @param boolean $return_stream If true, returns stream instead of data string
     * @return mixed If return stream, stream is returned else the data from the command on the remote machine as a string
     */
    public function exec($command, $timeout = 30, $return_stream = false) 
    {

        $stream = @ssh2_exec($this->connection, $command);

        if (!$stream) {
            throw new \Exception('Unable to exec command: '.$command);
        }

        if($return_stream){
            return $stream;
        }

        stream_set_blocking($stream, true);
        stream_set_timeout($stream, $timeout);
        $data = stream_get_contents($stream);
        fclose($stream);

        return trim($data);
    }

    /**
     * Tunnels a connection
     * @param string $host The host to tunnel to via the ssh2 client
     * @param integer $port The port to tunnel to via the ssh2 client
     * @return stream The tunnel stream
     */
    public function tunnel($host, $port)
    {
        $stream = @ssh2_tunnel($this->connection, $command);
        if(!$stream){
            throw new \Exception('Cannot create tunnel to: '.$host.' on port'.$port);
        }

        return $stream;
    }

}

