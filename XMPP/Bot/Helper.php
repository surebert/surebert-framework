<?php

/**
 * A Base Bot based on the XMPP Client
 *
 * All methods that start with serve_ are served as commands and show up
 * in the list of commands when the user types help.  methods with @secret in their
 * phpdocs, still are executable but do not show up in the list of help
 * commands.
 *
 * <code>
 * class BotDemo extends sb_Bot_Helper{
 *	public $status = 'thinking';
 *	public $uname = 'you bot name';
 *	public $pass = 'your bot pass';
 *
 *	public serve_hello(sb_XMPP_Message $message, $str){
 *		return 'hello '.$message->get_from().' you said '.$message->get_body();
 *
 *	}
 *
 * $bot = new Bot_Demo();
 * </code>
 * 
 * @author paul.visco@roswellpark.org, gregary.dean@roswellpark.org
 * @package sb_XMPP
 */
class sb_XMPP_Bot_Helper extends sb_XMPP_Client{

	/**
	 * The status to display for the bot
	 * @var string
	 */
    protected $status = 'helping';

	/**
	 * Display a list of commands the bot can understand.
	 *
	 * This list is based on any public methods that begin with serve_
	 *
	 * @return string
	 *
	 */
	public function serve_help($message){

		$commands = Array("I can respond to the following commands");

		foreach($this->get_methods() as $name=>$desc){
			$commands[] = $name.' - '.$desc;
		}
		$str = implode("\n\n", $commands);
		unset($commands);

		return 'Hello '.ucfirst(preg_replace("~\..*?$~", '', $message->get_from())).', '.$str;
	}


	/**
	 * Debug messages for auditing bot
	 *
	 * @param sb_XMPP_Message $message Has additional ->arguments property which is
	 * the message body minus the method name
	 * @param string $srguments The arguments passed to the command
	 *
	 * @secret true
	 * @return string The debug message data
	 */
	public function serve_debug(sb_XMPP_Message $message, $command){

		$str = '';
		switch($command){
			case 'memory':
				$str = 'I am currently using: '.$this->get_memory_usage();
				$str .= "\nMy peak usage: ".$this->get_memory_usage(true);
				break;

			case 'buddies':
				$str = 'My buddies that are online: '.print_r($this->buddies_online, 1);
				break;

			default:
				if(method_exists($this, 'on_debug')){
						$str = $this->on_debug($message, $command);
				}
		}

		if(empty($str)){
			$str = 'I do not know how to debug that ;(';
		}

		return $str;
	}

	/**
	 * Get the commands available for help menu
	 * @return Array Key=command names, value=php docs first sentence
	 */
	protected function get_methods() {

		if(!empty($this->methods)){
			return $this->methods;
		} else {
			$this->methods = Array();
			$methods = get_class_methods($this);

			foreach($methods as $method) {
				if(substr($method, 0, 6) == 'serve_'){

					$reflect = new ReflectionMethod($this, $method);

					if($reflect) {
						$phpdoc = $reflect->getDocComment();
						if(!preg_match('~@secret~', $phpdoc) && preg_match("~ \*(.*)\n~", $phpdoc, $match)){
							$this->methods[preg_replace("~^serve_~", '', $method)] = $match[1];
						}
					}

				}
			}

			return $this->methods;
		}
	}

	/**
	 * Fires when a new message is received
	 * @param string $message
	 */
    protected function on_message(sb_XMPP_Message $message_in){

		$str = $message_in->get_body();
		$str = trim((String) $str);

		if(!empty($str)){

			if(preg_match("~(\w+) ?(.*)?~", $str, $match)){
				$action = 'serve_'.strtolower($match[1]);
				$argument_str = $match[2];

				if(method_exists($this, $action)){
					$data = $this->{$action}($message_in, $argument_str);
				}

			}

			if(!isset($data)){
				$data = $this->method_not_found($message_in);
			}

			if($data){
				$message_out = new sb_XMPP_Message();
				$message_out->set_to($message_in->get_from());
				$message_out->set_body($data);
				$this->send_message($message_out);
			}
		}


		unset($message_in);
		unset($message_out);
		unset($action);
		unset($args);

    }

	/**
	 * Mirrors the words sent to it
	 * @param sb_XMPP_Message $message
	 * @return string
	 */
	public function method_not_found(sb_XMPP_Message $message){
		$body = $message->get_body();

		if($body == '?'){
			return $this->serve_help($message);
		}
		return 'Want to know what I can do, type: help';
	}

	/**
	 * Fires when error packet is received
	 * @param integer $error_code
	 * @param string $error_str
	 */
    protected function on_error($error_code, $error_str){

        if($error_code === 0){
            echo "\n\n".$error_str;

            $this->close();
            exit;
        }
    }

	/**
	 * Fires when presence packets are received and keeps track of online buddies
	 * @param sb_XMPP_Presence $presence
	 */
   protected function on_presence(sb_XMPP_Presence $presence){
			//do something with
	}

}

?>