<?php
/**
 * Used to easily convert XMPP_Packets from simpleXML to string - doctype
 * @author Paul Visco
 * @version 1.0 05-12-2009 05-12-2009
 */
class sb_XMPP_Packet extends SimpleXMLElement {

	public function to_string() {
		$xml = parent::asXML();
		return str_replace('<?xml version="1.0"?>'."\n", "", $xml);
	}
}
?>