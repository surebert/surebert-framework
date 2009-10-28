<?php
/**
 * Used to easily convert XMPP_Packets from simpleXML to string and back
 * @author Paul Visco
 * @package sb_XMPP
 */
abstract class sb_XMPP_Packet extends DOMDocument {

	/**
	 * gets the jid of the user that send the packet
	 * @return string e.g. paul.visco@chat.server.com
	 */
    public function get_from(){

		$attr = $this->doc->getAttribute('from');

		if($attr){
			return $attr;
		} else {
			return '';
		}
    }

	/**
	 * gets he jid of the user that the packet was sent to
	 * @return string e.g. paul.visco@chat.server.com
	 */
	public function get_to(){

		$attr = $this->doc->getAttribute('to');

		if($attr){
			return $attr;
		} else {
			return '';
		}
    }

	/**
	 * Gets the type of packet
	 * @return string
	 */
    public function get_type(){
		$attr = $this->doc->getAttribute('type');
		if($attr){
			return $attr;
		} else {
			return '';
		}
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