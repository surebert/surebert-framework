<?php
/**
 * Create an object oriented FTP client interface
 * 
 * @author paul.visco@roswellpark.org
 * requires PHP be compiled with ftp support
 */
class FTP_Client implements sb_FTP_Base{

	/**
	 * Instantiates an FTP connection
	 * @param string $host The host to connect to
	 * @param integer $port the port to connect to, 21 is default
	 * @param integer $timeout The time to wait before aborting if not connection is created
	 */
	public function __construct($host, $port=21, $timeout){
		$this->connection = ftp_connect($host, $port, $timeout);
		if(!$this->connection){
			throw new Exception("Could not connect to $host on port $port.");
		}
	}

	/**
	 * Login to a remote server with uname and pass based credentials
	 * @param string $uname The user name to login with
	 * @param string $pass The password to login with
	 */
	public function login($uname, $pass, $passive=false){
        if (!ftp_login($this->connection, $uname, $pass)){
            throw new Exception("Could not authenticate with credentials given.");
		}

		if($passive){
			 ftp_pasv($this->connection, true);
		}
    }

	/**
	 * Get a file from the remote machine
	 *
	 * @param string $remote_file The path to the remote file to read
	 * @param string $local_file The path to the local file to write
	 * @param const $mode FTP_ASCII or FTP_BINARY default FTP_ASCII
	 */
	public function get($remote_file, $local_file, $mode=null){
		$mode = $mode ? $mode : FTP_ASCII;
		if (!@ftp_get($this->connection, $local_file, $remote_file, $mode)) {
			 throw new Exception("Could not get remote file:". $remote_file);
		}

		return true;
	}

	/**
	 * Put a file on the remote machine
	 * @param string $local_file The path to the local file to read
	 * @param string $remote_file The path to the remote file to write
	 * @param const $mode FTP_ASCII or FTP_BINARY default FTP_ASCII
	 */
	public function put($local_file, $remote_file, $mode=null){
		$mode = $mode ? $mode : FTP_ASCII;
		if(!@ftp_put($this->connection, $remote_file, $local_file, $mode)) {
			throw new Exception("Could not authenticate with credentials given.");
		}

		return true;
	}

	/**
	 * Removes a remote file - be careful!
	 * @param string $remote_file Remote file
	 */
	public function delete($remote_file){
		if (!@ftp_delete($this->connection, $remote_file)) {
			 throw new Exception("Could not get delete remote file:". $remote_file);
		}

		return true;
	}

	/**
	 * Creates a remote directory
	 * @param string $path Delete Remote path
	 */
	public function mkdir($path){
		if(@ftp_mkdir($this->connection, $path)){
			throw new Exception("Could not create remote directory: ".$path);
		}

		return true;
	}

	/**
	 * Removes a remote directory - be careful!
	 * @param string $path Delete remote path
	 */
	public function rmdir($path){
		if(@ftp_rmdir($this->connection, $path)){
			throw new Exception("Could not remove remote directory: ".$path);
		}

		return true;
	}

	/**
	 * Gets the size of a remote file
	 * @param string $remote_file path to remote file
	 * @return integer The size of the file in bytes
	 */
	public function size($remote_file){
		$size = @ftp_size($this->connection, $remote_file);
		if($size && $size != -1){
			return $size;
		} else {
			throw new Exception("Could not get remote file size: ".$path);
		}
	}

	/**
	 * Change the file mode of a remote file
	 * @param string $remote_file The path to the remote file
	 * @param integer $mode The file mode e.g. 0644, 0777
	 * @return boolean
	 */
	public function chmod($remote_file, $mode){
		$mode = octdec(str_pad($mode, 4, '0', STR_PAD_LEFT));
		$mode = (int) $mode;

		if (!@ftp_chmod($conn_id, $mode, $file) !== false) {
			throw new Exception("Could not change remote file to mode: ".$mode);
		}

		return true;
	}

	/**
	 * Closes the FTP connection
	 */
	public function  __destruct() {
		ftp_close($this->connection);
	}
}
?>