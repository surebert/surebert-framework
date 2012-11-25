<?php
/**
 * Common OOP interface for FTP and SFTP client
 *
 * @package FTP
 */
namespace sb\FTP;
interface Base
{

    /**
     * Login to a remote server with uname and pass based credentials
     * @param string $uname The user name to log in with
     * @param string $pass The password to login in with
     */
    public function login($uname, $pass);

    /**
     * Get a file from the remote machine
     *
     * @param string $remote_file The path to the remote file to read
     * @param string $local_file The path to the local file to write
     * @param const $mode FTP_ASCII or FTP_BINARY default FTP_ASCII
     */
    public function get($remote_file, $local_file);

    /**
     * Put a file on the remote machine
     * @param string $local_file The path to the local file to read
     * @param string $remote_file The path to the remote file to write
     */
    public function put($local_file, $remote_file);

    /**
     * Removes a remote file - be careful!
     * @param string $remote_file Delete remote path
     */
    public function delete($remote_file);

    /**
     * Gets the size of a remote file
     * @param string $remote_file path to remote file
     * @return integer The size of the file in bytes
     */
    public function size($remote_file);

    /**
     * Creates a remote directory
     * @param string $remote_path Remote path
     */
    public function mkdir($remote_path);

    /**
     * Removes a remote directory - be careful!
     * @param string $remote_path Delete remote path
     */
    public function rmdir($remote_path);

    public function chmod($remote_path, $mode);
}

