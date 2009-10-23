<?php
/**
 * Used to easily convert XMPP_Packets from simpleXML to string - doctype
 * @author Paul Visco
 * @version 1.0 05-12-2009 05-12-2009
 */
class sb_XMPP_Packet extends DOMDocument {

	/**
	 * gets the jid of the user that send the packet
	 * @return string e.g. paul.visco@chat.server.com
	 */
    public function get_from(){
       return (String) $this->xml['from'];
    }

	/**
	 * gets he jid of the user that the packet was sent to
	 * @return string e.g. paul.visco@chat.server.com
	 */
	public function get_to(){
       return (String) $this->xml['to'];
    }

	/**
	 * Gets the type of packet
	 * @return string
	 */
    public function get_type(){
       return (String) $this->xml['type'];
    }

	/**
	 * Sets the to jid of the message
	 * @param string $to e.g. paul.visco@chat.roswellpark.org
	 */
	public function set_to($to){
		$attr = $this->createAttribute('to');
		$this->doc->appendChild($attr);
		$attr->appendChild($this->createTextNode($to));
	}

	/**
	 * Sets the from jid of the message
	 * @param string $from e.g. paul.visco@chat.roswellpark.org
	 */
	public function set_from($from){
		$attr = $this->createAttribute('from');
		$this->doc->appendChild($attr);
		$attr->appendChild($this->createTextNode($from));
	}

	/**
	 * Sets the type of the message
	 * @param string $type chat, etc
	 */
	public function set_type($type){
		$attr = $this->createAttribute('type');
		$this->doc->appendChild($attr);
		$attr->appendChild($this->createTextNode($type));
	}

	/**
	 * The simple XML element that represents the message
	 * @return mixed boolean or SimpleXMLElement
	 */
	public function get_xml(){
		return $this->xml;
	}

	/**
	 * Adds an arbitrary XML string to the packet's root node
	 * @param string $xml
	 */
	public function add_XML($xml){
		$next_elem = $this->createDocumentFragment();
		$next_elem->appendXML($xml);
		$this->doc->appendChild($next_elem);

	}
	/**
	 * A string representing the whole XML packet
	 * @return string
	 */
	public function __toString(){
		return $this->saveXML();
	}
	
}
?>