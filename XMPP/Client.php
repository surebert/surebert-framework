<?php
/**
 * Connects to XMPP servers, tested with openfire/ichat
 *
 * <code>
 * class ClientDemo extends \sb\XMPP_Client{
 *    public $status = 'thinking';
 *    public $uname = 'your uname';
 *    public $pass = 'your pass';
 *
 *    public onMessage(\sb\XMPP\Message $message)
    {
 *        //do something
 *
 *    }
 *
 * public onPresence(\sb\XMPP\Message $message)
    {
 *        //do something
 *
 *    }
 *
 * $client = new ClientDemo();
 * </code>
 *
 * @author paul.visco@roswellpark.org
 * @package XMPP
 */
namespace sb\XMPP;

class Client extends \sb\Socket\StreamingClient{

    /**
     * The host XMPP server to connect to.  You also specifiy the transport as tcp
     * or ssl e.g. tcp://chat.roswellpark.org or ssl://chat.roswellpark.org
     * @var string
     */
    protected $host;

    /**
     * The host port to connect to e.g. 5222 or 5223
     * @var integer
     */
    protected $port;

    /**
     * The amount of itme to wait for connection before aborting in secs
     * @var integer
     */
    protected $timeout = 10;

    /**
     * The uname currently logged in as
     * @var string
     */
    protected $uname;

    /**
     * The password to login in with
     * @var string
     */
    protected $pass;

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
     * Set to true when the connection is connected
     * @var boolean
     */
    protected $connected = false;

    /**
     * The full jid of the user
     * @var string
     */
    protected $jid;

    /**
     * The current packet id, increments after each packet is sent
     * @var integer
     */
    protected $packet_id = 0;

    /**
     * Connects to the XMPP server
     */
    public function connect()
    {

          //if port is not set, try and determine based on transport
        if(!is_numeric($this->port)){
            if(substr($this->host, 0, 3) == 'ssl'){
                $this->port = 5223;
                $this->log("Connecting with SSL");

            } else {
                $this->port = 5222;
            }
        }

        parent::__construct($this->host.':'.$this->port, $this->timeout);
        
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
    public function reconnect()
    {
        $this->log('Reconnecting...');
        $this->close();
        $this->connect();
        
    }

    /**
     * Login to the XMPP server as a user and set their precence to avaiable
     * @return boolean
     */
    public function login()
    {

        //connect if not connected
        if(!$this->connected){

            $this->reconnect();
        }
        //calc the jid from uname+host
        $this->jid = $this->uname.'@'.substr($this->host, strpos($this->host, '//')+2);
     
        if($this->pass){
            
            $this->write("<auth xmlns='urn:ietf:params:xml:ns:xmpp-sasl' mechanism='PLAIN'>" . base64_encode("\x00" . $this->uname . "\x00" . $this->pass) . "</auth>");
        } else {
            $this->write("<auth xmlns='urn:ietf:params:xml:ns:xmpp-sasl' mechanism='ANONYMOUS' />");
    }
     
        $buffer = $this->read();
        if(substr($buffer, 0, 5) == '<fail'){
            $this->onError(0, 'Cound not log in');
            return false;
            
        }
        
        $this->client_name = empty($this->client_name) ? __CLASS__ : $this->client_name;

        if(!$this->sendClientName()){
            return false;
        }

        $this->setStatus($this->status);

        $this->onAfterLogin();
        
        return true;
        
    }

    /**
     * Converts a presense request instance to XML and sends it.  It sets the from
     * to the bots jid for you
     *
     * @param \sb\XMPP\Presence $presence The presence instance to send
     * @return boolean
     */
    public function sendPresence(\sb\XMPP\Presence $presence)
    {

        $presence->setFrom($this->jid);
        return $this->write($presence);
    }

    /**
     * Sets status message
     * @param string $status The status message to send
     */
    public function setStatus($status=null)
    {

        $presence = new \sb\XMPP\Presence();
        $presence->setStatus(is_string($status) ? $status : $this->status);

        $this->sendPresence($presence);
    }

    /**
     * Sends simple string based text message
     *
     * @param string $to The jid to send the message to
     * @param string $body The text to send
     */
    public function sendSimpleMessage($to, $body)
    {

        $message = new \sb\XMPP\Message();

        $message->setTo($to);
        $message->setBody($body);
        $this->sendMessage($message);
    }

    /**
     * Send a presence message accepting subscription request to a jid
     * @param string $to The jid of the person to send the acceptance request to
     * @return boolean
     */
    public function sendAcceptSubscriptionRequest($to)
    {
        $presence = new \sb\XMPP\Presence();
        $presence->setType('subscribed');
        $presence->setTo($to);
        return $this->sendPresence($presence);
    }

    /**
     * Sends a subscription request to a jid
     * @param string $to The hid to send the subscription request to
     * @return boolean
     */
    public function sendSubscriptionRequest($to)
    {

        $presence = new \sb\XMPP\Presence();
        $presence->setTo($to);
        $presence->setType('subscribe');
        $presence->setFrom($this->jid);
        return $this->sendPresence($presence);

    }

    /**
     * Sends a \sb\XMPP\Message via the socket
     * @param \sb\XMPP\Message $message
     * @return boolean
     */
    public function sendMessage(\sb\XMPP\Message $message)
    {
        return $this->write($message);
    }

    /**
     * Sends a xml string
     * @param string $xml
     * @return boolean
     */
    public function sendXML($xml)
    {
        return $this->write($xml);
    }

    /**
     * Broadcasts message to all online buddies not in except array
     * @param mixed $message String/\sb\XMPP\Message The message to send
     * @param string $except An array of jids to not send to
     */
    public function broadcast($message, $except=array()) 
    {

        if(!$message instanceOf \sb\XMPP\Message){
            $body = $message;
            $message = new \sb\XMPP\Message();
            $message->setBody($body);
        }


        foreach($this->buddies_online as $buddy=>$status){
            if(!in_array($buddy, $except)){
                $message->setTo($buddy);
                $this->sendMessage($message);
            }
        }
    }

    /**
     * Logs what is doing
     * @param string $message The message being received
     * @todo convert to sb_Logger
     */
    public function log($message)
    {

        file_put_contents("php://stdout", "\n\n" . $message);
    }

    /**
     * Puts the bot to sleep for a while
     * @param integer $secs
     */
    public function sleep($secs)
    {
        $str = 'Going to sleep for '.$secs.'secs';
        $this->log('NOTICE: '.$str);
        $this->setStatus($str);
        sleep($secs);
        $this->setStatus($this->status ? $this->status : 'awake');
    }

    /**
     * Send an indication that the bot is composing text.
     * @param string $to The jid to send to
     */
    public function composingStart($to)
    {
        $message = new \sb\XMPP\Message();

        $message->setTo($to);
        $message->setFrom($this->jid);
        $message->setType('chat');
        $node = $message->createElement('composing');
        $attr = $message->createAttribute('xmlns');
        $node->appendChild($attr);
        $attr->appendChild($message->createTextNode('http://jabber.org/protocol/chatstates'));
        
        $message->doc->appendChild($node);

        $this->sendMessage($message);
    }

    /**
     * Sends message to indicating that the bot is no longer composing
     * @param string $to The jid to send to
     */
    public function composingStop($to)
    {
        $message = new \sb\XMPP\Message();

        $message->setTo($to);
        $message->setFrom($this->jid);
        $message->setType('chat');
        $node = $message->createElement('active');
        $attr = $message->createAttribute('xmlns');
        $node->appendChild($attr);
        $attr->appendChild($message->createTextNode('http://jabber.org/protocol/chatstates'));

        $message->doc->appendChild($node);

        $this->sendMessage($message);
    }

    /**
     * Reads from the socket, should be protected but can't because this is inherited as public
     *
     * @return string The buffer data read from the socket
     */
    final public function read($byte_count=null)
    {

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
     * Listens for incoming chat on the socket
     */
    final public function listen()
    {

        $this->log("Listening...");

        $x = 1;
        while($x){

            $x++;
            $xml = $this->read(1024);

            if(!empty($xml)){
                if($this->onRead($xml) === false){
                    return false;
                }
            }

            if(substr($xml, 0, 8 ) == '<message'){
                $message = new \sb\XMPP\Message($xml);
                $message->client = $this;
                $this->onMessage($message);
            } elseif(substr($xml, 0, 9 ) == '<presence'){

                $presence = new \sb\XMPP\Presence($xml);
                $from = $presence->getFrom();
                $type = $presence->getType();

                if(is_array($this->buddies_online)){

                    $status = $presence->getStatus();

                    if($from && $type == 'unavailable'){
                        unset($this->buddies_online[$from]);
                        $this->log('NOTICE: '.$from.' is unavailable');
                    } elseif($from){

                        $this->buddies_online[$from] = $status;
                        $this->log('NOTICE: '.$from.' is '.$status);
                    }
                }

                if(strstr($type, 'subscribe') && $this->onSubscriptionRequest($presence)){

                    $this->sendAcceptSubscriptionRequest($from);
                    $this->log('NOTICE: Auto accepting subscription request from '.$from);
                }

                $this->on_presence($presence);
            }

            if($x % 100 == 0){

                $presence = new \sb\XMPP\Presence();
                $presence->setStatus($this->status);


                $this->sendPresence($presence);
                $x = 1;

            }

           //extra little sleep reduces CPU
           usleep(100000);
        }
    }

    /**
     * Writes XML to the socket client
     * @param mixed $xml Can be DOMDocument, SimpleXMLElement or string of XML
     * @return <type>
     */
    final public function write($xml)
    {

        if($xml instanceof \SimpleXMLElement){
            $xml = $xml->asXML();
        } elseif($xml instanceof \DOMDocument){
            $xml = $xml->saveXML();
        }

        $this->log("SENT: ".$xml);

        return parent::write($xml);
    }


    /**
     * Determines the peak memory usage
     * @return string The value in b, KB, or MB depending on size
     */
    final public function getMemoryUsage($peak=false) 
    {

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
     * Ends the stream and closes the connection
     */
    final public function close()
    {
        
        $this->onCloseConnection();

        if($this->socket){
            $this->write("</stream:stream>");
            parent::close();
            $this->connected = false;
        }

    }

    /**
     * Sends the client name to the XMPP server
     * @return string The server response
     */
    final protected function sendClientName()
    {

        $this->write('<iq xmlns="jabber:client" type="set" id="'.$this->nextId().'"><bind xmlns="urn:ietf:params:xml:ns:xmpp-bind"><resource>'.$this->client_name.'</resource></bind></iq>');
        return $this->read();
    }

    /**
     * Increments the packet id
     * @return integer
     */
    final protected function nextId()
    {
        return $this->packet_id++;
    }

    /**
     * An event handler that fires when a message is received with $this->listen
     * @param \sb\XMPP\Message $message
     */
    protected function onMessage(\sb\XMPP\Message $message)
    {}

    /**
     * This is an event handler for errors, you would extend it in your class that extends
     * this class
     * @param string $error
     */
    protected function onError($error_code, $error_str)
    {}

    /**
     * Determines if a subscription request is accepted or not
     * 
     * By default it automatically accepts all requests
     * 
     * @param \sb\XMPP\Presence $presence You can use this to determine who it is and if you want to accept
     * @return boolean If returns true it automatically accepts the subscription, false it does not
     */
    protected function onSubscriptionRequest(\sb\XMPP\Presence $presence)
    {
        return true;
    }

    /**
     * Fires just before closing connection to give you one last chance
     * to cleanup, send out goodbye messages, etc
     */
    protected function onCloseConnection()
    {}

    /**
     * Fires after login to allow you to do things, like blast out
     * messages to your buddies, check presence, etc
     *
     */
    protected function onAfterLogin()
    {}

    /**
     * Generic onRead function that passes raw xml packet as string
     * so that you can do custom stuff with custom kinds of packets
     *
     * If you return false, processing of the packet stops there, meaning
     * it will not be passed onto the onMessage and on_presense methods
     *
     * @param string $xml
     * @return boolean
     */
    protected function onRead($xml)
    {
        return true;
    }

    /**
     * Closes the socket connection
     */
    public function __destruct()
    {
        $this->close();
    }
    
}

