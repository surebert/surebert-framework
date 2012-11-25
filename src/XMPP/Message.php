<?php
/**
 * Represents a XMPP Message for sending and receiving
 *
 * @author paul.visco@roswellpark.org
 * @package XMPP
 */
namespace sb\XMPP;

class Message extends Packet{

    /**
     * The SimpleXMLElement if one is imported
     * @var SimpleXMLElement
     */
    public $xml;

    /**
     * Creates a new XMPP_Message instance
     * @param string $xml Optional XML string to base the Document on
     */
    public function __construct($xml = '')
    {
        parent::__construct('1.0', 'UTF-8');
        if(!empty($xml)){
            $xml = simplexml_load_string($xml);
            $this->doc = dom_import_simplexml($xml);
            
            $xml = null;
        } else {
            $this->doc = $this->appendChild($this->createElement('message'));
        }
    }

    /**
     * Gets the body of the message
     *
     * @param boolean $as_string Determines if node is returned as xml node or string, true by default
     * @return string
     */
    public function getBody($as_string=true)
    {

        $nodes = $this->doc->getElementsByTagName('body');
        $node =$nodes->item(0);
        if($node){
            if($as_string){
                return $node->nodeValue;
            } else {
                return $node;
            }
            
        } else {
            return '';
        }
    }

    /**
     * Gets the subject of the message
     *
     * @param boolean $as_string Determines if node is returned as xml node or string, true by default
     */
    public function getSubject($as_string=true)
    {

        $nodes = $this->doc->getElementsByTagName('subject');
        $node =$nodes->item(0);
        if($node){
            if($as_string){
                return $node->nodeValue;
            } else {
                return $node;
            }
        } else {
            return '';
        }
    }

    /**
     * Returns the HTML string if there is one, this is expiremental
     * 
     * @param boolean $as_string Determines if node is returned as xml node or string, true by default
     * @return string
     */
    public function getHTML($as_string=true)
    {
        $nodes = $this->doc->getElementsByTagName('html');
        $node =$nodes->item(0);
        if($node){
            if($as_string){
                return $node->nodeValue;
            } else {
                return $node;
            }
        } else {
            return '';
        }
    }

    /**
     * Sets the body of the message to be send
     * @param string $body
     */
    public function setBody($body)
    {
        $node = $this->getBody(false);
        if(!$node){
            $node = $this->createElement('body');
            $this->doc->appendChild($node);
        }
        $node->nodeValue = htmlspecialchars($body);
    }

    /**
     * Set the subject of the message
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $node = $this->getSubject(false);
        if(!$node){
            $node = $this->createElement('subject');
            $this->doc->appendChild($node);
        }
        $node->nodeValue = htmlspecialchars($subject);
    }

    /**
     * Sets the html node of the message, expiremental
     * @param string $html
     */
    public function setHTML($html)
    {
        $node = $this->createDocumentFragment();
        $node->appendXML('<html xmlns="http://www.w3.org/1999/xhtml">'.$html.'</html>');
        $this->doc->appendChild($node);
    }

    /**
     * Creates a reply message and sends it to the user that sent the
     * original message.  This can be used only on \sb\XMPP\Message instances
     * that came over the socket and were passed to the onMessage method.
     * @param string $str
     */
    public function reply($str)
    {
        $message = new \sb\XMPP\Message();
        $message->setTo($this->getFrom());
        $message->set_from($this->getTo());
        $message->setBody($str);
        $this->client->sendMessage($message);
    }

}
