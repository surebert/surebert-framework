<?php
/**
 * Represents a XMPP Message for sending and receiving
 */
class sb_XMPP_Message extends sb_XMPP_Packet{

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
			$this->loadXML(trim($xml));
			$this->xml = simplexml_import_dom($this);
		} else {
			parent::__construct('1.0', 'UTF-8');
			$this->doc = $this->appendChild($this->createElement('message'));
		}
	}

	/**
	 * Gets the body of the message
	 * @return string
	 */
	public function get_body(){
		if($this->xml instanceof SimpleXMLElement){
			return $this->xml->body;
		} else {
			return '';
		}
	}

	/**
	 * Gets the subject of the message
	 * @param string $subject
	 */
	public function get_subject($subject){
		if($this->xml instanceof SimpleXMLElement && $this->xml->subject){
			return $this->xml->subject;
		} else {
			return '';
		}
	}

	/**
	 * Returns the HTML string if there is one, this is expiremental
	 * @return string
	 */
	public function get_html(){
		return $this->xml->html;
	}

	/**
	 * Sets the body of the message to be send
	 * @param string $body
	 */
	public function set_body($body){
		$node = $this->createElement('body');
		$node->nodeValue = htmlspecialchars($body);
		$this->doc->appendChild($node);
	}

	/**
	 * Set the subject of the message
	 * @param string $subject
	 */
	public function set_subject($subject){
		$node = $this->createElement('subject');
		$node->nodeValue = htmlspecialchars($subject);
		$this->doc->appendChild($node);
	}

	/**
	 * Sets the html node of the message, expiremental
	 * @param string $html
	 */
	public function set_html($html){
		$next_elem = $this->createDocumentFragment();
		$next_elem->appendXML('<html xmlns="http://www.w3.org/1999/xhtml">'.$html.'</html>');
		$this->doc->appendChild($next_elem);
	}

}
?>