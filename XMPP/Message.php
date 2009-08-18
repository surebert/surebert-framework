<?php
/**
 * Used to model a XMPP_Message
 * @author Paul Visco
 * @version 1.0 05-12-2009 05-12-2009
 */
class sb_XMPP_Message {

	public $xml;

	public function __construct($xml) {
		$this->xml = new sb_XMPP_Packet($xml);
	}

	public function get_body() {
		return $this->xml->body[0];
	}

	public function get_from() {
		return $this->xml['from'];
	}

	public function get_to() {
		return $this->xml['to'];
	}

	public function get_type() {
		return $this->xml['type'];
	}
}
?>