<?php
/**
 * Represents a XMPP Message for sending and receiving
 *
 * @author Paul Visco
 * @package sb_XMPP
 */
class sb_XMPP_Message extends sb_XMPP_Packet{

	/**
	 * The SimpleXMLElement if one is imported
	 * @var SimpleXMLElement
	 */
	public $xml;

	/**
	 * Creates a new sb_XMPP_Message instance
	 * @param string $xml Optional XML string to base the Document on
	 */
	public function __construct($xml = ''){
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
	 * @return string
	 */
	public function get_body(){

		$nodes = $this->doc->getElementsByTagName('body');
		$node =$nodes->item(0);
		if($node){
			return $node->nodeValue;
		} else {
			return '';
		}
	}

	/**
	 * Gets the subject of the message
	 * @param string $subject
	 */
	public function get_subject(){

		$nodes = $this->doc->getElementsByTagName('subject');
		$node =$nodes->item(0);
		if($node){
			return $node->nodeValue;
		} else {
			return '';
		}
	}

	/**
	 * Returns the HTML string if there is one, this is expiremental
	 * @return string
	 */
	public function get_html(){
		$nodes = $this->doc->getElementsByTagName('html');
		$node =$nodes->item(0);
		if($node){
			return $node->nodeValue;
		} else {
			return '';
		}
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

	/**
	 * Creates a reply message and sends it to the user that sent the
	 * original message.  This can be used only on sb_XMPP_Message instances
	 * that came over the socket and were passed to the on_message method.
	 * @param string $str
	 */
	public function reply($str){
		$message = new sb_XMPP_Message();
		$message->set_to($this->get_from());
		$message->set_from($this->get_to());
		$message->set_body($str);
		$this->client->send_message($message);
	}

}
?>