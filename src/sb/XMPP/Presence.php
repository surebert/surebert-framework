<?php
/**
 * Models a presence packet for sending and receiving
 *
 * @author paul.visco@roswellpark.org
 * @package XMPP
 */
namespace sb\XMPP;

class Presence extends Packet{

    /**
     * The SimpleXMLElement if one is imported
     * @var SimpleXMLElement
     */
    public $xml;

    /**
     * Creates a new DOMDocument
     * @param string $xml Optional XML string to base the Document on
     */
    public function __construct($xml = '')
    {
        parent::__construct('1.0', 'UTF-8');
        
        if(!empty($xml)){
            
            $xml = preg_replace("~</presence>.*$~", "</presence>", $xml);
            
            try{
                $xml = '<root>'.$xml.'</root>';
                $packet = simplexml_load_string($xml);
                $xml = $packet->presence[0];
                $this->doc = dom_import_simplexml($xml);
                
                $xml = null;
                $packet = null;
            
            } catch(Exception $e){
                file_put_contents("php://stdout", "\n\n|||".print_r($xml, 1).'|||'."\n\n");
            }



        } else {
            
            $this->doc = $this->appendChild($this->createElement('presence'));
        }
    }

    /**
     * Gets the status of a presence packet
     *
     * @param boolean $as_string Determines if node is returned as xml node or string, true by default
     * @return string
     */
    public function getStatus($as_string=true)
    {
        $nodes = $this->doc->getElementsByTagName('status');
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
     * Gets the show value of a presence packet
     *
     * @param boolean $as_string Determines if node is returned as xml node or string, true by default
     * @return string
     */
    public function getShow($as_string=true)
    {
        $nodes = $this->doc->getElementsByTagName('show');
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
     * Gets the priority value of a presence packet
     *
     * @param boolean $as_string Determines if node is returned as xml node or string, true by default
     * @return string
     */
    public function getPriority($as_string=true)
    {
        $nodes = $this->doc->getElementsByTagName('priority');
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
     * Set the status of the presence packet
     *
     * @param string $status The status message to display in human readible format
     */
    public function setStatus($status)
    {

        $node = $this->getStatus(false);
        if(!$node){
            $node = $this->createElement('status');
            $this->doc->appendChild($node);
        }
        $node->nodeValue = htmlspecialchars($status);
        
    }

    /**
     * Shows the status of the bot
     * @param string $show A code to describe the state. see http://xmpp.org/rfcs/rfc3921.html
     * away - The entity or resource is temporarily away.
     * chat - The entity or resource is actively interested in chatting.
     * dnd - The entity or resource is busy (dnd = "Do Not Disturb").
     * xa - The entity or resource is away for an extended period (xa = "eXtended Away").
     */
    public function setShow($show)
    {

        if($show == 'unavailable') {
            $this->setType($show);
        }

        $node = $this->getShow(false);
        if(!$node){
            $node = $this->createElement('show');
            $this->doc->appendChild($node);
        }

        $node->nodeValue = htmlspecialchars($show);

    }

    /**
     * Sets the priority of the presence
     * @param integer $priority
     */
    public function setPriority($priority=1)
    {

        $node = $this->getPriority(false);
        if(!$node){
            $node = $this->createElement('priority');
            $this->doc->appendChild($node);
        }
        $node->nodeValue = htmlspecialchars($priority);
        
    }

    /**
     * Sets the presence type
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
    public function setType($type)
    {
        $attr = $this->createAttribute('type');
        $this->doc->appendChild($attr);
        $attr->appendChild($this->createTextNode($type));
    }

}

