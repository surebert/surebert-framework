<?php
/**
 * Connects to XMPP servers, tested with openfire/ichat
 * @author Paul Visco
 */

class sb_XMPP_Client extends sb_Socket_StreamingClient{

    /**
     * The host XMPP server to connect to.  You also specifiy the transport as tcp
     * or ssl e.g. tcp://obi.roswellpark.org or ssl://obi.roswellpark.org
     * @var string
     */
    protected $host;

    /**
     * The host port to connect to e.g. 5222 or 5223
     * @var integer
     */
    protected $port;

    /**
     * The uname currently logged in as
     * @var string
     */
    protected $uname;

    /**
     * The full jid of the user
     * @var string
     */
    protected $jid;

    /**
     * Set to true when the connection is connected
     * @var boolean
     */
    protected $connected = false;

    /**
     * The current packet id, increments after each packet is sent
     * @var integer
     */
    protected $packet_id = 0;

	/**
	 * The status to display to other users
	 * @var string
	 */
    protected $status = 'available';

	/**
	 * The buddies that are online
	 * @var array
	 */
	protected $buddies_online = Array();

    /**
     * Create a new connection to a XMPP server
     * @param string $host The host to connect to with transport e.g.
     * tcp://xmpp.my.org or ssl://xmpp.my.org
     * @param integer $port The port the user
     * @param integer $timeout The number of seconds to wait for the initial connection
	 * <code>
	 * $client = new sb_XMPP_Client('chat.blah.org', 5223);
	 * </code>
	 */
    public function __construct($host, $port=null, $timeout=10){

        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;

        //if port is not set, try and determine based on transport
        if(!is_numeric($this->port)){
            if(substr($this->host, 0, 3) == 'ssl'){
                $this->port = 5223;
                $this->log("Connecting with SSL");
                
            } else {
                $this->port = 5222;
            }
        }

        parent::__construct($this->host.':'.$this->port, $timeout);
       
        $this->connect();

    }

    /**
     * Connects to the XMPP server
     */
    public function connect(){

        $this->open();
       
        //begin stream
        $this->write("<stream:stream to='$this->host' xmlns='jabber:client'
xmlns:stream='http://etherx.jabber.org/streams' xml:lang='en' version='1.0'>");

        $this->read();
        $this->read();

        $this->connected = true;

    }

    /**
     * Reconnects to the XMPP server
     */
    public function reconnect(){
        $this->log('Reconnecting...');
        $this->close();
        $this->connect();
        
    }

    /**
     * Login to the XMPP server as a user and set their precence to avaiable
     * @param string $uname The uname to login as
     * @param string $pass The password to login with
     * @param string/boolean $status A status string to a string to display for presence
     * or empty string for simply available set to false for unavailable
     * @return boolean
     */
    public function login($uname, $pass=null, $status=null){

        //connect if not connected
        if(!$this->connected){
            $this->reconnect();
        }
        //calc the jid from uname+host
        $this->jid = $uname.'@'.substr($this->host, strpos($this->host, '//')+2);
        $this->uname = $uname;
  
        if($pass){
            
            $this->write("<auth xmlns='urn:ietf:params:xml:ns:xmpp-sasl' mechanism='PLAIN'>" . base64_encode("\x00" . $this->uname . "\x00" . $pass) . "</auth>");
        } else {
            $this->write("<auth xmlns='urn:ietf:params:xml:ns:xmpp-sasl' mechanism='ANONYMOUS' />");
	}
     
        $buffer = $this->read();
        if(substr($buffer, 0, 5) == '<fail'){
            $this->on_error(0, 'Cound not log in');
            return false;
            
        }
        
        $this->client_name = empty($this->client_name) ? __CLASS__ : $this->client_name;

        if(!$this->send_client_name()){
            return false;
        }

        return $this->send_presence(is_string($status) ? $status : $this->status);
        
    }


    /**
     * Ends the stream and closes the connection
     */
    public function close(){

        if($this->socket){
            $this->write("</stream:stream>");
            parent::close();
            $this->connected = false;
        }

    }

    /**
     * Sends a presence notification
     *
     * @param string $status The status message to display in human readible format
     * @param string $to The jid of the user to send to
     * @param string $show A code to describe the state. see http://xmpp.org/rfcs/rfc3921.html
     * away - The entity or resource is temporarily away.
     * chat - The entity or resource is actively interested in chatting.
     * dnd - The entity or resource is busy (dnd = "Do Not Disturb").
     * xa - The entity or resource is away for an extended period (xa = "eXtended Away").
     
     * @param string $type see http://xmpp.org/rfcs/rfc3921.html for more info
     * unavailable -- Signals that the entity is no longer available for communication.
     * subscribe -- The sender wishes to subscribe to the recipient's presence.
     * subscribed -- The sender has allowed the recipient to receive their presence.
     * unsubscribe -- The sender is unsubscribing from another entity's presence.
     * unsubscribed -- The subscription request has been denied or a previously-granted subscription has been cancelled.
     * probe -- A request for an entity's current presence; SHOULD be generated only by a server on behalf of a user.
     * error -- An error has occurred regarding processing or delivery of a previously-sent presence stanza.
     * @return boolean If it is written or not
     */
    public function send_presence($status = null, $show = null, $type=null, $to = null) {

       $xml = new sb_XMPP_Presence();

       if($to){
           $xml->set_to($to);
       }

       $xml->set_from($this->jid);

       if($type){
           $xml->set_type($type);
       }

       if(!is_null($show)){
           $xml->set_show($show);
       }

       if($status){
           $xml->set_status($status);
       }

       return $this->write($xml);

    }

	/**
	 * Listens for incoming chat on the socket
	 */
    public function listen(){

        $this->log("Listening...");
        $x = 1;
        while($x){
           
            $x++;
            $xml = $this->read(1024);

            if(substr($xml, 0, 8 ) == '<message'){
				$message = new sb_XMPP_Message($xml);
				$message->client = $this;
                $this->on_message($message);
            } else if(substr($xml, 0, 9 ) == '<presence'){

				$presence = new sb_XMPP_Presence($xml);
				if(is_array($this->buddies_online)){

					$from = $presence->get_from();
					$type = $presence->get_type();
					$status = $presence->get_status();

					if($from && $type == 'unavailable'){
						unset($this->buddies_online[$from]);
						$this->log('NOTICE: '.$from.' is unavailable');
					} else if($from){

						$this->buddies_online[$from] = $status;
						$this->log('NOTICE: '.$from.' is '.$status);
					}
				}
				
                $this->on_presence($presence);
            }

            if($x % 10 == 0){

                $this->send_presence($this->status);
                $x = 1;

            }

           //extra little sleep reduces CPU
           usleep(100000);
        }
    }

	/**
	 * Sends a sb_XMPP_Message via the socket
	 * @param sb_XMPP_Message $message
	 * @return boolean
	 */
	public function send_message(sb_XMPP_Message $message){
		return $this->write($message);
	}

	/**
	 * Writes XML to the socket client
	 * @param mixed $xml Can be DOMDocument, SimpleXMLElement or string of XML
	 * @return <type>
	 */
    public function write($xml){

		if($xml instanceof SimpleXMLElement){
			$xml = $xml->asXML();
		} else if($xml instanceof DOMDocument){
			$xml = $xml->saveXML();
		}

		$this->log("SENT: ".$xml);
		
        return parent::write($xml);
    }

	/**
	 * An event handler that fires when a message is received with $this->listen
	 * @param sb_XMPP_Message $message
	 */
    protected function on_message(sb_XMPP_Message $message){}

    /**
     * This is an event handler for errors, you would extend it in your class that extends
     * this class
     * @param string $error
     */
    protected function on_error($error_code, $error_str){}

    /**
     * Increments the packet id
     * @return integer
     */
    protected function next_id(){
        return $this->packet_id++;
    }

	/**
	 * Send an indication that the bot is composing text.
	 * @param string $to The jid to send to
	 */
	protected function composing_start($to){
		$message = new sb_XMPP_Message();

		$message->set_to($to);
		$message->set_from($this->jid);
		$message->set_type('chat');
		$node = $message->createElement('composing');
		$attr = $message->createAttribute('xmlns');
		$node->appendChild($attr);
		$attr->appendChild($message->createTextNode('http://jabber.org/protocol/chatstates'));
		
		$message->doc->appendChild($node);

		$this->send_message($message);
	}

	/**
	 * Sends message to indicating that the bot is no longer composing
	 * @param string $to The jid to send to
	 */
	protected function composing_stop($to){
		$message = new sb_XMPP_Message();

		$message->set_to($to);
		$message->set_from($this->jid);
		$message->set_type('chat');
		$node = $message->createElement('active');
		$attr = $message->createAttribute('xmlns');
		$node->appendChild($attr);
		$attr->appendChild($message->createTextNode('http://jabber.org/protocol/chatstates'));

		$message->doc->appendChild($node);

		$this->send_message($message);
	}

    /**
     * Logs what is doing
     * @param string $message The message being received
     * @todo convert to sb_Logger
     */
    protected function log($message){

        file_put_contents("php://stdout", "\n\n" . $message);
    }

    /**
     * Sends the client name to the XMPP server
     * @return string The server response
     */
    protected function send_client_name(){

        $this->write('<iq xmlns="jabber:client" type="set" id="'.$this->next_id().'"><bind xmlns="urn:ietf:params:xml:ns:xmpp-bind"><resource>'.$this->client_name.'</resource></bind></iq>');
        return $this->read();
    }

    /**
     * Reads from the socket, should be protected but can't because this is inherited as public
     *
     * @return string The buffer data read from the socket
     */
    public function read($byte_count=null){

        $buffer = '';
        $read = array($this->socket);
        
        $updated = @stream_select($read, $write, $except, 1);

        if ($updated > 0) {
            $data = fread($this->socket, 4096);
            if($data) {
                   $buffer = $data;
            }

        }

        if(!empty($buffer)){
            $this->log('RECEIVED: '.$buffer);
        }

        return $buffer;
    }

	/**
	 * Determines the peak memory usage
	 * @return string The value in b, KB, or MB depending on size
	 */
	protected function get_memory_usage($peak=false) {

		if($peak){
			$mem_usage = memory_get_peak_usage(true);
		} else {
			$mem_usage = memory_get_usage(true);
		}

		$str = '';
		if ($mem_usage < 1024) {
			$str = $mem_usage." b";
		} elseif ($mem_usage < 1048576) {
			$str = round($mem_usage/1024,2)." KB";
		} else {
			$str = round($mem_usage/1048576,2)." MB";
		}
		return $str;
	}


	/**
	 * Closes the socket connection
	 */
    public function __destruct(){
        $this->close();
    }
    
    
}

?>