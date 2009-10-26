<?php

/**
 * Used to send plain text emails, HTML emails, or plain text and html emails with attachments both inline and not REQUIRES sb_Email.php and sb_Files (<-unless you specify the mime types on attachments manually)
 *
 * If DEBUG_EMAIL constant is defined, then all email goes to that address.
 *
 * @Author Paul Visco
 * @Version 2.25 06/08/03 06/16/09
 * @package sb_Email
 *
 */

class sb_Email_Writer {

/**
 * An instance of sb_Logger for logging the emails sent
 * @var sb_Logger
 */
    public $logger;

    /**
     * Determines if the body of the emails are logged in the log
     * @var boolean
     */
    public $log_body = true;

    /**
     * An instance of sb_Email which describes the email being sent
     *
     * @var sb_Email
     */
    protected $emails = Array();

    /**
     * The ip address of the sender
     * @var string
     */
    protected $remote_addr = '127.0.0.1';

    /**
     *The http host of the server sending the email, defaults to php_uname('n') if $_SERVER['HTTP_HOST'] is not set
     * @var string
     */
    protected $http_host = 'localhost';


    /**
     * Creates a new outbox to send from
     *
     * @param sb_Logger $logger optional
	 *
	 * <code>
	 //instanciate the email writer
	 $myEmailWriter = new sb_Email_Writer();

	 //add an instance of sb_Email to the outbox, you can add as many as you want
	 $myEmailWriter->add_email_to_outbox($myMail);

	 //then send, you could add more emails before sending
	 var_dump($myEmailWriter->send());

	 </code>
     */
    public function __construct($logger=null, $remote_addr='', $http_host='') {

        if($logger instanceOf sb_Logger) {

            $this->logger->add_log_type(Array('sb_Email_Writer_Sent', 'sb_Email_Writer_Error'));
        } else if(isset(App::$logger) && App::$logger instanceof sb_Logger_Base) {
                App::$logger->add_log_types(Array('sb_Email_Writer_Sent',  'sb_Email_Writer_Error'));
                $this->logger = App::$logger;
            } else {
                $this->logger = new sb_Logger_FileSystem(Array('sb_Email_Writer_Sent',  'sb_Email_Writer_Error'));
            }

        $this->remote_addr = (!empty($remote_addr)) ? $remote_addr : Gateway::$remote_addr;
        $this->http_host = (!empty($http_host)) ? $http_host : (isset($_SERVER['HTTP_HOST'])? $_SERVER['HTTP_HOST'] : php_uname('n')) ;
    }

    /**
     * Sends the emails in the $emails array that were attached using add_email_to_outbox, logs progress if log file is specified
     *
     */
    public function send($email=0) {

        if($email instanceof sb_Email) {
            $this->add_email_to_outbox($email);
        }

        $sent_emails=0;

        foreach($this->emails as &$email) {

            $this->add_security_info($email);

            //all email goes to DEBUG_EMAIL if specified
            if(defined("DEBUG_EMAIL")) {
                $email->body = "DEBUG MODE: Should be sent to: ".$email->to." when not in debug mode!\n\n".$email->body;
                $email->body = "DEBUG MODE: Should be sent from: ".$email->from." when not in debug mode!\n\n".$email->body;

                $email->to = DEBUG_EMAIL;
                $email->from = DEBUG_EMAIL;
            }

            $this->construct_multipart_message($email);

            if(mail($email->to, $email->subject, $email->body, $email->_header_text)) {

                $email->sent = 1;
                $sent_emails++;

                $this->log_email($email, true);

            } else {
                $this->log_error($email, false);
            }

        }

        if($sent_emails == count($this->emails)) {
			$this->emails = Array();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Adds an email to the outbox which is sent with the send method
     *
     * @param sb_Email $email
     * @return boolean false if it has injectors, true if added to outbox
     */
    public function add_email_to_outbox($email) {
    
        if($this->check_headers_for_injection($email)) {
            return 0;
        } else {

            $this->emails[] = $email;
            return true;
        }

    }

    /**
     * Logs the sending of emails if logging is enable by specifying the log_file property
     *
     * @param $email sb_Email
     * @param $sent Boolean, was the email sent or not
     */
    private function log_email($email, $sent) {

        $message = "\nEmail sent at ".date('m/d/y h:i:s');
        $message .= "\nFrom:".$email->from. '@'.$this->remote_addr;
        $message .= "\nTo: ".$email->to;
        $message .= "\nSubject: ".$email->subject;
        $message .= "\nAttachments: ".count($email->attachments).' ';
        if($this->log_body) {
            $message .= "\nBody: ".$email->body;
            $message .= "\nBody_HTML: ".$email->body_HTML;
        }
        foreach($email->cc as $cc) {
            $message .="\nCc:".$cc;
        }

        foreach($email->bcc as $bcc) {
            $message .="\nBcc:".$bcc;
        }

        $names = Array();
        foreach($email->attachments as $attachment) {
            $names[] = $attachment->name;
        }

        $message .= "(".implode(",", $names).")";


        if($sent) {
            return $this->logger->sb_Email_Writer_Sent($message);
        } else {
            return $this->logger->sb_Email_Writer_Error($message);
        }

    }

    /**
     * Adds security info of sender
     *
     * @param sb_Email $email
     */
    private function add_security_info(sb_Email &$email) {

        $email->body .= "\n\nFor security purposes the following information was recorded: \nSending IP: ".$this->remote_addr." \nSending Host: ".$this->http_host;

        if(!empty($email->body_HTML)) {
            $email->body_HTML .= '<br /><br /><span style="font-size:10px;color:#ff0000;margin-top:20px;">For security purposes the following information was recorded: <br />Sending IP:'.$this->remote_addr.' <br />Sending Host: '.$this->http_host.'</span>';
        }
    }

    /**
     * Constructs multipart messages based on attachments
     *
     * @param sb_Email $email
     */
    private function construct_multipart_message(sb_Email &$email) {

        $mixed_boundary = '__mixed_1S2U3R4E5B6E7R8T9';
        $alterative_boundary = '__alter_1S2U3R4E5B6E7R8T9';


        $email->attachments_in_HTML =0;

        if(strstr($email->body_HTML, "cid:")) {

            $email->attachments_in_HTML =1;
            $related_boundary = '__relate_1S2U3R4E5B6E7R8T9';
        }

        $email->_header_text = "From: ".$email->from."\r\nReply-To: ".$email->from."\r\nReturn-Path: ".$email->from."\r\n";

        foreach($email->cc as $cc) {
            $email->_header_text .="Cc:".$cc."\r\n";
        }

        foreach($email->bcc as $bcc) {
            $email->_header_text .="Bcc:".$bcc."\r\n";
        }

        $email->_header_text .= "MIME-Version: 1.0"."\r\n";
        
        foreach($email->headers as $key=>$val) {
            $email->_header_text .= $key.":".$val."\r\n";
        }
        
        $email->_header_text .= "Content-Type: multipart/mixed;"."\r\n";
        $email->_header_text .= ' boundary="'.$mixed_boundary.'"'."\n\n";
        
        // Add a message for peoplewithout mime
        $message = "This message has an attachment in MIME format created with surebert mail.\r\n\r\n";

        //if there is body_HTML use it otherwise use just plain text
        if(!empty($email->body_HTML)) {

            $message .= "--".$mixed_boundary."\r\n";

            if($email->attachments_in_HTML == 1) {
                $message .= "Content-Type: multipart/related;"."\r\n";
                $message .= ' boundary="'.$related_boundary.'"'."\r\n\r\n";
                $message .= "--".$related_boundary."\r\n";
            }

            $message .= "Content-Type: multipart/alternative;"."\r\n";
            $message .= ' boundary="'.$alterative_boundary.'"'."\r\n\r\n";

            $message .= "--".$alterative_boundary."\r\n";
            $message .= "Content-Type: text/plain; charset=iso-8859-1; format=flowed\r\n";
            $message .= "Content-Transfer-Encoding: 7bit\r\n";
            $message .= "Content-Disposition: inline\r\n\r\n";
            $message .= $email->body . "\r\n";


            $message .= "--".$alterative_boundary."\r\n";
            $message .= "Content-Type: text/html; charset=iso-8859-1 \r\n";
            $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
            $message .= $email->body_HTML . "\r\n";

            $message .="--".$alterative_boundary."--\r\n";

        } else {

            $message .= "--".$mixed_boundary."\r\n";
            $message .= "Content-Type: text/plain; charset=iso-8859-1; format=flowed\r\n";
            $message .= "Content-Transfer-Encoding: 7bit\r\n";
            $message .= "Content-Disposition: inline\r\n\r\n";
            $message .= $email->body . "\r\n";
        }

        //add all attachments for this email
        foreach($email->attachments as &$attachment) {

        //if only filepath is set, grab name and contents from there
            if(isset($attachment->filepath)) {
                if(empty($attachment->name)) {
                    $attachment->name = basename($attachment->filepath);
                }

                if(empty($attachment->contents)) {
                    $attachment->contents = file_get_contents($attachment->filepath);
                }

                if(empty($attachment->mime_type)) {
                    $attachment->mime_type = sb_Files::file_to_mime($attachment->filepath);
                }

            }
            $ex = explode(".", $attachment->name);
            $attachment->extension = strtolower(array_pop($ex));

            //try and guess the mime type unless it is set
            if(empty($attachment->mime_type)) {
                $attachment->mime_type = sb_Files::extension_to_mime($attachment->extension);
            }

            $attachment->encoding =3;
            $attachment->type = 5;
            $attachment->contents = chunk_split(base64_encode($attachment->contents));

            // Add file attachment to the message

            if($email->attachments_in_HTML == 1) {
                $message .= "--".$related_boundary."\r\n";
            } else {
                $message .= "--".$mixed_boundary."\r\n";
            }

            $message .= "Content-Type: ".$attachment->mime_type.";\r\n";
            $message .= " name=".$attachment->name."\r\n";

            $message .= "Content-Transfer-Encoding: base64\r\n";
            //$message .= "Content-ID: ".$attachment->name."\r\n\r\n";
            $message .= "Content-ID: <".$attachment->name.">\r\n\r\n";

            $message .=  $attachment->contents."\r\n";

        }

        //end related if using body_HTML
        if($email->attachments_in_HTML == 1) {
            $message .= "--".$related_boundary."--\r\n";
        }

        //end message
        $message .="--".$mixed_boundary."--\r\n";

        $email->body = $message;

    }

    /**
     * Checks email for injections in from and to addr
     *
     * @param sb_Email $email
     * @return boolean
     */
    private function check_headers_for_injection(sb_Email $email) {
    //try and catch injection attempts and alert admin user
        if (preg_match("~\r|:~i",$email->to) || preg_match("~\r|:~i",$email->from)) {
            return true;
        //do something here to alert admin
        }

        return false;
    }
}

?>