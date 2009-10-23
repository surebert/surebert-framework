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
	public function set_status($status){
		$node = $this->createElement('status');
		$node->nodeValue = htmlspecialchars($status);
		$this->doc->appendChild($node);
	}

	/**
	 * Shows the status of the bot
	 * @param string $show
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

}

?>