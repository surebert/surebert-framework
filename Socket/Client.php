<?
/**
 * Manages a socket connection
 * 
 * <code>
 * //load data from google homepage
 * $conn = new \sb\Socket\Client('www.google.com', 80);
 * $conn->write("GET / HTTP/1.1\r\nHost: www.example.com\r\nConnection: Close\r\n\r\n");
 * echo $conn->read();
 * </code>
 * 
 * @author paul.visco@roswellpark.org
 * @package Socket
 */
namespace sb\Socket;

class Client 
    {

    /**
     *
     * The socket connection resource
     * @var Resource
     */
    protected $socket = null;
    /**
     * The host to connect to
     * @var string e.g. www.google.com
     */
    protected $host = '';
    /**
     * The timeout to wait for the connection
     * @var integer default 10
     */
    protected $timeout = 10;
    /**
     * The "id" of the connection host:port, used for logging
     * @var string
     */
    public static $instances = 0;

    /**
     * Sets up the connection
     * @param string $host The host to connect to e.g. www.google.com
     * @param integer $port The port to connect to e.g. 80
     * @param integer $timeout The timeout to wait before dropping connection e.g. 10
     */
    public function __construct($host, $port, $timeout=10) 
    {

        $this->countInstance();

        $this->host = $host;
        $this->port = $port;
        $this->id = $host . ':' . $port;
        $this->timeout = $timeout;
        $this->open();
    }

    /**
     * Keeps track of instances
     */
    public function countInstance() 
    {

        self::$instances++;
        $this->unique_id = self::$instances;
    }

    /**
     * Open the connection
     * @return boolean Throws exception on error
     */
    public function open() 
    {

        $this->log('open socket connection');
        $this->socket = fsockopen($this->host, $this->port, $errno, $errstr, $this->timeout);
        if ($this->socket) {
            socket_set_blocking($this->socket, 1);

            return true;
        } else {
            throw(new \Exception("{$errstr} (#{$errno})"));
        }
    }

    /**
     * Closes the connection.  Automatically is called on __destruct
     * @return boolean
     */
    public function close() 
    {
        $this->log('close socket connection');

        $state = fclose($this->socket);
        if ($state) {
            $this->socket = null;
        }

        return $state;
    }

    /**
     * Writes data to the connection
     * @param String $data The data to send
     * @return boolean
     */
    public function write($data) 
    {

        return fwrite($this->socket, $data);
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

        if (!is_null($byte_count)) {
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

    /**
     * Closes the connection if the user forgot
     */
    public function __destruct() 
    {
        if ($this->socket) {
            $this->close();
        }
    }

    /**
     * Logs what is doing
     * @param string $message
     * @todo convert to sb_Logger
     */
    protected function log($message) 
    {

        echo "\n\n" . $this->unique_id . ': ' . $message;
    }

}

