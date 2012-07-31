<?php
/**
 * Represents a XMPP Message for sending and receiving
 *
 * @author paul.visco@roswellpark.org
 * @package XMPP
 */
namespace sb;

class XMPP_Message extends XMPP_Packet{

    /**
     * The SimpleXMLElement if one is imported
     * @var SimpleXMLElement
     */
    public $xml;

    /**
     * Creates a new XMPP_Message instance
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
     *
     * @param boolean $as_string Determines if node is returned as xml node or string, true by default
     * @return string
     */
    public function get_body($as_string=true){

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
    public function get_subject($as_string=true){

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
    public function get_html($as_string=true){
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
    public function set_body($body){
        $node = $this->get_body(false);
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
    public function set_subject($subject){
        $node = $this->get_subject(false);
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
    public function set_html($html){
        $node = $this->createDocumentFragment();
        $node->appendXML('<html xmlns="http://www.w3.org/1999/xhtml">'.$html.'</html>');
        $this->doc->appendChild($node);
    }

    /**
     * Creates a reply message and sends it to the user that sent the
     * original message.  This can be used only on \sb\XMPP_Message instances
     * that came over the socket and were passed to the on_message method.
     * @param string $str
     */
    public function reply($str){
        $message = new \sb\XMPP_Message();
        $message->set_to($this->get_from());
        $message->set_from($this->get_to());
        $message->set_body($str);
        $this->client->send_message($message);
    }

}
?>