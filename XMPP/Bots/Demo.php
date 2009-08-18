<?php

/**
 * A Demo Bot based on the XMPP Client
 */
class sb_XMPP_Bots_Demo extends sb_XMPP_Client{

	/**
	 * The status to display for the bot
	 * @var string
	 */
    protected $status = 'dancing';

	/**
	 * Fires when a new message is received
	 * @param string $message
	 */
    protected function on_message(sb_XMPP_Message $message){
        echo "\n\n".$message->get_type();
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
	 * Fires when presence packets are received
	 * @param <type> $presence
	 */
    protected function on_presence(sb_XMPP_Presence $presence){
        echo var_dump($presence);
    }
}



?>