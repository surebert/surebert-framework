<?php
/**
 * Manages a streaming socket connection
 *
 * @author paul.visco@roswellpark.org
 * @version 0.1 05-05-2009 05-05-2009
 * @package sb_Socket
 *
 */
namespace \sb\Socket;
class StreamingClient extends Client 
    {

    /**
     * The remote socket to connect to
     * @var string e.g. tcp://www.example.com:80
     */
    protected $remote_socket = '';

    /**
     * Sets up the connection
     *
     *  <code>
     *   $conn = new sb_Socket_StreamingConnection('tcp://www.google.com:80');
     *   $conn->write("GET / HTTP/1.1\r\nHost: www.example.com\r\nConnection: Close\r\n\r\n");
     *   echo $conn->read();
     *   </code>
     *
     * @param string $remote_socket The remote socket to connect to 'tcp://www.example.com:80'
     * @param integer $timeout The timeout to wait before dropping connection e.g. 10
     */
    public function __construct($remote_socket, $timeout=10) 
    {
        
        $this->countInstance();

        $this->unique_id = self::$instances;

        $this->remote_socket = $remote_socket;
        $this->timeout = $timeout;
    }

    /**
     * Open the connection
     * @return boolean Throws exception on error
     */
    public function open() 
    {

        $this->log('open socket connection');
        $this->socket = @stream_socket_client($this->remote_socket,  $errno, $errstr, $this->timeout);

        if ($this->socket) {

            return true;
        } else {
            throw(new \Exception("{$errstr} (#{$errno})"));
        }
    }

    /**
     * Read data from the socket
     * @param integer $byte_count The amount of data to read, if not set, it reads until feof
     * @return string The data read from the socket
     */
    public function read($byte_count=null) 
    {

        $this->log('read from  socket');
        $buffer = '';

        if(!is_null($byte_count)) {
        //read the specified amount of data
            $buffer .= fgets($this->socket, 1024);
        } else {
        //read all the data
            while (!feof($this->socket)) {
                $buffer .= fgets($this->socket, 1024);
            }
        }


        return $buffer;
    }

}
