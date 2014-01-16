<?php

/**
 * Telnet class
 * 
 * Used to execute remote commands via telnet connection 
 * Usess sockets functions and fgetc() to process result
 * 
 * All methods throw Exceptions on error
 * 
 * Written by Dalibor Andzakovic <dali@swerve.co.nz>
 * Based on the code originally written by Marc Ennaji and extended by 
 * Matthias Blaser <mb@adfinis.ch>
 * 
 * Extended by Christian Hammers <chammers@netcologne.de>
 *
 * Extended by Paul Visco <paulsidekick@gmail.com>
 * 
 * More info about telnet here: http://pcmicro.com/netfoss/telnet.html
 */

namespace sb;

class Telnet {

    /**
     * The host to connect to
     * @var string
     */
    protected $host;

    /**
     * The port to connect to, default 23
     * @var int
     */
    protected $port;

    /**
     * The timeout to wait for a response
     * @var int
     */
    protected $timeout;

    /**
     * The socket connection
     * @var resource 
     */
    protected $socket = NULL;

    /**
     * The existing buffer
     * @var string
     */
    protected $buffer = "";

    /**
     * The prompt to seek to, default $
     * @var string
     */
    protected $prompt;

    /**
     * The error number for the connection
     * @var int
     */
    protected $errno;

    /**
     * The error string for the connection
     * @var string
     */
    protected $errstr;

    /**
     * The global buffer of the communication
     * @var string
     */
    protected $global_buffer = "";

    /**
     * Debugs nothing 0, read 1, write 2, read/write 3
     * @var int 
     */
    protected $debug_mode = 0;

    /**
     * NULL char used for telnet communication char(0)
     * @var string
     */
    protected $NULL;

    /**
     * DC1 char telnet chr(17)
     * @var string 
     */
    protected $DC1;

    /**
     * Sender wants to do something.  chr(251)
     * @var string 
     */
    protected $WILL;

    /**
     * Sender doesn't want to do something. chr(252)
     * @var string 
     */
    protected $WONT;

    /**
     * Sender wants the other end to do something. chr(253)
     * @var string 
     */
    protected $DO;

    /**
     * Sender wants the other not to do something. chr(254)
     * @var string 
     */
    protected $DONT;

    /**
     * Interpret as command chr(255)
     * @var string 
     */
    protected $IAC;

    /**
     * Constructor. Initialises host, port and timeout parameters
     * defaults to localhost port 23 (standard telnet port)
     * 
     * @param string $host Host name or IP addres
     * @param int $port TCP port number
     * @param int $timeout Connection timeout in seconds
     * @return void
     */
    public function __construct($host = '127.0.0.1', $port = '23', $timeout = 1) {

        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;

        // set some telnet special characters
        $this->NULL = chr(0);
        $this->DC1 = chr(17);
        $this->WILL = chr(251);
        $this->WONT = chr(252);
        $this->DO = chr(253);
        $this->DONT = chr(254);
        $this->IAC = chr(255);

        $this->connect();
    }

    /**
     * Destructor. Cleans up socket connection and command buffer
     * 
     * @return void 
     */
    public function __destruct() {

        // cleanup resources
        $this->disconnect();
        $this->buffer = NULL;
        $this->global_buffer = NULL;
    }

    /**
     * Attempts connection to remote host. Returns TRUE if sucessful.      
     * 
     * @return boolean
     */
    public function connect() {

        // check if we need to convert host to IP
        if (!preg_match('/([0-9]{1,3}\\.){3,3}[0-9]{1,3}/', $this->host)) {

            $ip = gethostbyname($this->host);

            if ($this->host == $ip) {

                throw new \Exception("Cannot resolve $this->host");
            } else {
                $this->host = $ip;
            }
        }

        // attempt connection
        $this->socket = fsockopen($this->host, $this->port, $this->errno, $this->errstr, $this->timeout);

        if (!$this->socket) {
            throw new \Exception("Cannot connect to " . $this->host . " on port " . $this->port . "\n" . $this->errstr . ": " . $this->errstr);
        }

        return true;
    }

    /**
     * Closes IP socket
     * 
     * @return boolean
     */
    public function disconnect() {
        if ($this->socket) {
            if (!fclose($this->socket)) {
                throw new \Exception("Error while closing telnet socket");
            }
            $this->socket = NULL;
        }
        return true;
    }

    /**
     * Executes command and returns a string with result.
     * This method is a wrapper for lower level private methods
     * 
     * @param string $command Command to execute      
     * @return string Command result
     */
    public function exec($command) {

        $this->write($command);
        $this->waitPrompt();
        return $this->getBuffer();
    }

    /**
     * Sets the string of characters to respond to.
     * This should be set to the last character of the command line prompt
     * 
     * @param string $s String to respond to
     * @return boolean
     */
    public function setPrompt($s = '$') {
        $this->prompt = $s;
        return true;
    }

    /**
     * Gets character from the socket
     *     
     * @return void
     */
    public function getc() {
        $c = fgetc($this->socket);
        $this->global_buffer .= $c;
        return $c;
    }

    /**
     * Clears internal command buffer
     * 
     * @return void
     */
    public function clearBuffer() {
        $this->buffer = '';
    }

    /**
     * Reads characters from the socket and adds them to command buffer.
     * Handles telnet control characters. Stops when prompt is ecountered.
     * 
     * @param string $prompt
     * @return boolean
     */
    public function readTo($prompt) {

        if (!$this->socket) {
            throw new \Exception("Telnet connection closed");
        }

        // clear the buffer 
        $this->clearBuffer();

        $until_t = time() + $this->timeout;
        do {
            // time's up (loop can be exited at end or through continue!)
            if (time() > $until_t) {
                throw new \Exception("Couldn't find the requested : '$prompt' within {$this->timeout} seconds");
            }

            $c = $this->getc();

            if ($c === false) {
                throw new \Exception("Couldn't find the requested : '" . $prompt . "', it was not in the data returned from server: " . $this->buffer);
            }

            // Interpreted As Command
            if ($c == $this->IAC) {
                if ($this->negotiateTelnetOptions()) {
                    continue;
                }
            }

            // append current char to global buffer           
            $this->buffer .= $c;
            if ($this->debug_mode === 1 || $this->debug_mode === 3) {
                $this->log($c);
            }

            // we've encountered the prompt. Break out of the loop
            if ((substr($this->buffer, strlen($this->buffer) - strlen($prompt))) == $prompt) {
                return true;
            }
        } while ($c != $this->NULL || $c != $this->DC1);
    }

    /**
     * Write command to a socket
     * 
     * @param string $buffer Stuff to write to socket
     * @param boolean $addNewLine Default true, adds newline to the command 
     * @return boolean
     */
    public function write($buffer, $addNewLine = true) {

        if (!$this->socket) {
            throw new \Exception("Telnet connection closed");
        }

        // clear buffer from last command
        $this->clearBuffer();

        if ($addNewLine == true) {
            $buffer .= "\n";
        }

        $this->global_buffer .= $buffer;
        if ($this->debug_mode === 2 || $this->debug_mode === 3) {
            $this->log($buffer);
        }
        if (!fwrite($this->socket, $buffer) < 0) {
            throw new \Exception("Error writing to socket");
        }

        return true;
    }

    /**
     * Allows you to debug communication to stdout
     * @param type $debug_mode 0=nothing, 1=read buffer, 2=write buffer, 3=both 3
     */
    public function setDebugMode($debug_mode = 0) {
        $this->debug_mode = $debug_mode;
    }

    /**
     * Log to stdout
     * @param string $c
     */
    public function log($str) {
        file_put_contents("php://stdout", $str);
    }

    /**
     * Returns the content of the command buffer
     * 
     * @return string Content of the command buffer 
     */
    public function getBuffer() {
        // cut last line (is always prompt)
        $buf = explode("\n", $this->buffer);
        unset($buf[count($buf) - 1]);
        $buf = implode("\n", $buf);
        return trim($buf);
    }

    /**
     * Returns the content of the global command buffer
     *
     * @return string Content of the global command buffer
     */
    public function getGlobalBuffer() {
        return $this->global_buffer;
    }

    /**
     * Telnet control character magic
     * 
     * @param string $command Character to check
     * @return boolean
     */
    public function negotiateTelnetOptions() {

        $c = $this->getc();

        if ($c != $this->IAC) {

            if (($c == $this->DO) || ($c == $this->DONT)) {

                $opt = $this->getc();
                fwrite($this->socket, $this->IAC . $this->WONT . $opt);
            } else if (($c == $this->WILL) || ($c == $this->WONT)) {

                $opt = $this->getc();
                fwrite($this->socket, $this->IAC . $this->DONT . $opt);
            } else {
                throw new \Exception('Error: unknown control character ' . ord($c));
            }
        } else {
            throw new \Exception('Error: Something Wicked Happened');
        }

        return true;
    }

    /**
     * Reads socket until prompt is encountered
     */
    public function waitPrompt() {
        return $this->readTo($this->prompt);
    }

}
