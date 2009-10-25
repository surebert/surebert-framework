<?php
/**
 * Models a presence packet for sending and receiving
 * @author Paul Visco
 */
class sb_XMPP_Presence extends sb_XMPP_Packet{

	/**
	 * The SimpleXMLElement if one is imported
	 * @var SimpleXMLElement
	 */
	public $xml;

	/**
	 * Creates a new DOMDocument
	 * @param string $xml Optional XML string to base the Document on
	 */
	public function __construct($xml = ''){
		if(!empty($xml)){
			
			$xml = preg_replace("~</presence>.*$~", "</presence>", $xml);
			
			try{
				$xml = '<root>'.$xml.'</root>';
			$sxml = simplexml_load_string($xml);
			$this->xml = $sxml->presence[0];
			} catch(Exception $e){
				file_put_contents("php://stdout", "\n\n|||".print_r($xml, 1).'|||'."\n\n");
			}
		} else {
			parent::__construct('1.0', 'UTF-8');
			$this->doc = $this->appendChild($this->createElement('presence'));
		}
	}

	/**
	 * Gets the status of a presence packet
	 * @return string
	 */
    public function get_status(){
        if(isset($this->xml->status[0])){
            return (String) $this->xml->status[0];
        } else {
            return 'available';
        }
    }

	/**
	 * Gets the show value of a presence packet
	 * @return string
	 */
    public function get_show(){
        if(isset($this->xml->show[0])){
            return (String) $this->xml->show[0];
        } else {
            return '';
        }
    }

	/**
	 * Gets the priority value of a presence packet
	 * @return string
	 */
    public function get_priority(){
        if(isset($this->xml->priority[0])){
            return (String)$this->xml->priority[0];
        } else {
            return '';
        }
    }

	/**
	 * Set the status of the presence packet
	 * @param string $status
	 */

	/**
     * Sends a presence notification
     *
     * @param string $status The status message to display in human readible format
     */
	public function set_status($status){
		$node = $this->createElement('status');
		$node->nodeValue = htmlspecialchars($status);
		$this->doc->appendChild($node);
	}

	/**
	 * Shows the status of the bot
	 * @param string $show A code to describe the state. see http://xmpp.org/rfcs/rfc3921.html
     * away - The entity or resource is temporarily away.
     * chat - The entity or resource is actively interested in chatting.
     * dnd - The entity or resource is busy (dnd = "Do Not Disturb").
     * xa - The entity or resource is away for an extended period (xa = "eXtended Away").
	 */
    public function set_show($show){

        if($show == 'unavailable') {
            $this->set_type($show);
        }

		$node = $this->createElement('show');
		$node->nodeValue = htmlspecialchars($show);
		$this->doc->appendChild($node);

    }

	/**
	 * Sets the priority of the presence
	 * @param integer $priority
	 */
    public function set_priority($priority=1){
		$node = $this->createElement('priority');
		$node->nodeValue = htmlspecialchars($priority);
		$this->doc->appendChild($node);
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
	public function set_type($type){
		$attr = $this->createAttribute('type');
		$this->doc->appendChild($attr);
		$attr->appendChild($this->createTextNode($type));
	}

	

}

?>