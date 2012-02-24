<?php
/**
 * Object to represent an email used by sb_EmailReader and sb_EmailWriter
 * 
 * @author Paul Visco
 * @version 2.21 10-15-07 - 06-21-09 added cc and bcc support
 * @package sb_Email
 *
 */
class sb_Email{
	
	/**
	 * The index number of the message in the inbox
	 * 
	 * Used to delete messages
	 *
	 * @var integer
	 */
	public $index;
	
	/**
	 * The message id as specified by the email server in the email headers
	 *
	 * @var string
	 */
	public $message_id;
	
	/**
	 * The subject of the email
	 *
	 * @var string
	 */
	public $subject;
	
	/**
	 * The first "to" address listed
	 * 
	 * For a more complete list of to addresses look in the $this->header_info->to array
	 *
	 * @var string e.g. paul@test.com
	 */
	public $to;
	
	/**
	 * An array of "cc" addresses when sending
	 * 
	 * @var array e.g. Array('paul@test.com');
	 */
	public $cc = Array();
	
	/**
	 * An array of "bcc" addresses when sending
	 * 
	 * @var array e.g. Array('paul@test.com');
	 */
	public $bcc = Array();
		
	/**
	 * The first "from" address listed
	 * 
	 * For a more complete list of to addresses look in the $this->header_info->from array
	 *
	 * @var string e.g. paul@test.com
	 */
	public $from;
	
	/**
	 * The first "reply_to" address listed
	 * 
	 * For a more complete list of to addresses look in the $this->header_info->reply_to array
	 *
	 * @var string e.g. paul@test.com
	 */
	public $reply_to;
	
	/**
	 * The first "sender" address listed
	 * 
	 * For a more complete list of to addresses look in the $this->header_info->sender array
	 *
	 * @var string e.g. paul@test.com
	 */
	public $sender;
	
	/**
	 * The date that the email was sent
	 *
	 * @var string
	 */
	public $date;
	
	/**
	 * The date the email was sent as a unix timestamp
	 *
	 * @var integer
	 */
	public $timestamp;
	
	/**
	 * The size of the email in bytes
	 *
	 * @var integer
	 */
	public $size;
	
	/**
	 * Has the message been set to delete $deleted =1;
	 *
	 * @var boolean
	 */
	
	public $deleted =0;
	
	/**
	 * The subtype of the email, e.g. MULTIPART, ALTERNATIVE, PLAIN
	 *
	 * @var string
	 */
	public $subtype;
	
	/**
	 * The entire headers in an array, this holds all custom headers too
	 *
	 * @var array
	 */
	public $headers = Array();
	
	/**
	 * The body of the email
	 *
	 * @var string
	 */
	public $body;
	
	/**
	 * The body of the email as HTML
	 *
	 * @var string
	 */
	public $body_HTML;
	
	/**
	 * The charset of the email text e.g. iso-8859-1 or UTF-8
	 * @var string 
	 */
	public $charset = 'UTF-8';
	
	/**
	 * Character encoding 7bit, 8bit
	 * @var string 
	 */
	public $transfer_encoding = '8bit';
	
	
	/**
	 * An array of attachment objects
	 *
	 * @var array
	 */
	public $attachments = Array();
	
    /**
     * Constructs an email
	 *
	 * <code>
	 * //create an email to send $to, $subject, $message, $from
	 * $myMail = new sb_Email('paul.visco@roswellpark.org', 'Testing Email', 'Hello World', 'paul.visco@roswellpark.org');
	 *
	 * //you can set the cc array to add addresses which are cced
	 * //$myMail->cc = Array("paulsidekick@gmail.com");
	 *
	 * //you can reference inline attachments in the HTML by their cid:{THEIR NAME} e.g.
	 * //$myMail->body_HTML = '<h1>Hello there</h1><img src="cid:MyPicture.jpg" />';
	 *
	 * //$myMail->body_HTML = '<h1>Hello there</h1>';
	 *
	 * //create an optional attachment
	 * //$myAttachment = new sb_Email_Attachment(ROOT.'/private/config/App.php', 'application/php');
	 *
	 * //or zipping the attachment
	 * //$myAttachment->zip();
	 *
	 * //PGP encrypt the attachment
	 * //$myAttachment->pgp_encrypt('XXXX98D01A6CXXXXXX5700');
	 *
	 * //add the attachment to the email object, you could add more attachments as necessary
	 * $myMail->add_attachment($myAttachment);
	 *
	 * var_dump($myMail->send());
	 *
	 * //you can also manually add an attachment from a non file e.g. data from database
	 * //create an optional attachment
	 * $myAttachment = new sb_Email_Attachment();
	 *
	 * //set up the properties for the attachment
	 * $myAttachment->name = 'MyPicture.jpg';
	 *
	 * //this is the content, in this case I am ready the blob data from a saved image file but you could easily replace this with blob data from a database.  The mime type will be added based on the extension using sb_Files::extension_to_mime.  For bizarre mime-types that are not in sb_Files::extension_to_mime you can override this by setting the mime-type manually $myAttachment->mime_type ='bizarre/weird';
	 * $myAttachment->contents = $filedata;
	 *
	 * </code>
	 *
	 * @param String $to The address to send the email to
	 * @param String $subject The subject of the email
     * @param String $message The plaintext message being sent
     * @param String $from The address it is being sent from
     */
    public function  __construct($to='', $subject='', $message='', $from=''){

        $this->to = $to;
        $this->subject = $subject;
        $this->body = $message;
        $this->from = $from;

    }

	/**
	 * Adds an attachment to the email
	 * @param $attachment sb_Email
	 */
	public function add_attachment(sb_Email_Attachment $attachment){
		$this->attachments[] = $attachment;
	}

	/**
	 * Add an sb_ICalendar_Event request
	 * 
	 * @param sb_ICalendar_Event $event
	 */
	public function add_ICalendar_Event(sb_ICalendar_Event $event){

		$a = new sb_Email_Attachment();
		$a->mime_type = 'text/calendar;';
		$a->set_encoding('8bit');
		$a->name = 'event.ics';
		$a->contents = $event->__toString();

		$this->add_attachment($a);
	}

    /**
     * An instance of sb_Email_Writer used to send
     * @var sb_Email_Writer
     */
    protected static $outbox;

	/**
	 * Fires before sending, if returns false, then sending does not occur
	 * @return boolean
	 */
	public function on_before_send(){
		return true;
	}
    /**
     * Uses sb_Email_Writer to send the email
     */
    public function send($outbox=null){

        if($outbox instanceof sb_Email_Writer){
            self::$outbox = $outbox;
        } else if(!self::$outbox){
            self::$outbox = new sb_Email_Writer();
        }

		if($this->on_before_send($this) !== false){
			self::$outbox->add_email_to_outbox($this);

			//return if sent
			return self::$outbox->send();
		}

    }

    /**
     * Ups the importance of the email, in outlook this displays a exclamation point
     */
    public function make_important(){
        $this->headers[] = $this->add_header('Priority', 'Urgent');
        $this->headers[] = $this->add_header('Importance', 'high');
    }

    /**
     * Adds custom email headers by key value
     * <code>
     * $mail->add_header('Priority', 'low');
     * </code>
     */
    public function add_header($key, $value){
        $this->headers[$key] = $value;
    }
	
	/**
	 * Convert the email to a multipart_message
	 * @return string the raw email source
	 */
	public function construct_multipart_message() {
		
        $mixed_boundary = '__mixed_1S2U3R4E5B6E7R8T9';
        $alterative_boundary = '__alter_1S2U3R4E5B6E7R8T9';

        $this->attachments_in_HTML =0;

        if(strstr($this->body_HTML, "cid:")) {

            $this->attachments_in_HTML =1;
            $related_boundary = '__relate_1S2U3R4E5B6E7R8T9';
        }

        $this->_header_text = "From: ".$this->from."\r\nReply-To: ".$this->from."\r\nReturn-Path: ".$this->from."\r\n";

        foreach($this->cc as $cc) {
            $this->_header_text .="Cc:".$cc."\r\n";
        }

        foreach($this->bcc as $bcc) {
            $this->_header_text .="Bcc:".$bcc."\r\n";
        }

        $this->_header_text .= "MIME-Version: 1.0"."\r\n";
        
        foreach($this->headers as $key=>$val) {
            $this->_header_text .= $key.":".$val."\r\n";
        }
        
        $this->_header_text .= "Content-Type: multipart/mixed;"."\r\n";
        $this->_header_text .= ' boundary="'.$mixed_boundary.'"'."\n\n";
        
        // Add a message for peoplewithout mime
        $message = "This message has an attachment in MIME format created with surebert mail.\r\n\r\n";

        //if there is body_HTML use it otherwise use just plain text
        if(!empty($this->body_HTML)) {

            $message .= "--".$mixed_boundary."\r\n";

            if($this->attachments_in_HTML == 1) {
                $message .= "Content-Type: multipart/related;"."\r\n";
                $message .= ' boundary="'.$related_boundary.'"'."\r\n\r\n";
                $message .= "--".$related_boundary."\r\n";
            }

            $message .= "Content-Type: multipart/alternative;"."\r\n";
            $message .= ' boundary="'.$alterative_boundary.'"'."\r\n\r\n";

            $message .= "--".$alterative_boundary."\r\n";
            $message .= "Content-Type: text/plain; charset=".$this->charset."; format=flowed\r\n";
            $message .= "Content-Transfer-Encoding: ".$this->transfer_encoding."\r\n";
            $message .= "Content-Disposition: inline\r\n\r\n";
            $message .= $this->body . "\r\n";
			
            $message .= "--".$alterative_boundary."\r\n";
            $message .= "Content-Type: text/html; charset=".$this->charset."\r\n";
            $message .= "Content-Transfer-Encoding: ".$this->transfer_encoding."\r\n\r\n";
            $message .= $this->body_HTML . "\r\n";
			
            $message .="--".$alterative_boundary."--\r\n";

        } else {

            $message .= "--".$mixed_boundary."\r\n";
            $message .= "Content-Type: text/plain; charset=".$this->charset."; format=flowed\r\n";
            $message .= "Content-Transfer-Encoding: ".$this->transfer_encoding."\r\n";
            $message .= "Content-Disposition: inline\r\n\r\n";
            $message .= $this->body . "\r\n";
        }

        //add all attachments for this email
        foreach($this->attachments as &$attachment) {

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

			if($attachment->encoding == 'base64'){
				$attachment->contents = chunk_split(base64_encode($attachment->contents));

			}
           
            // Add file attachment to the message

            if($this->attachments_in_HTML == 1) {
                $message .= "--".$related_boundary."\r\n";
            } else {
                $message .= "--".$mixed_boundary."\r\n";
            }

			if($attachment->mime_type == 'text/calendar'){
				$message .= "Content-class: urn:content-classes:calendarmessage;\r\n";
			}
			
			$message .= "Content-Type: ".$attachment->mime_type.";\r\n";
            $message .= " name=".$attachment->name."\r\n";

            $message .= "Content-Transfer-Encoding: ".$attachment->encoding."\r\n";
            $message .= "Content-ID: <".$attachment->name.">\r\n\r\n";

            $message .=  $attachment->contents."\r\n";

        }

        //end related if using body_HTML
        if($this->attachments_in_HTML == 1) {
            $message .= "--".$related_boundary."--\r\n";
        }

        //end message
        $message .="--".$mixed_boundary."--\r\n";

        $this->body = $message;
		
		$raw = "To: ".$this->to."\r\n";
		$raw .= "Subject: ".$this->subject."\r\n";
		$raw .= $this->_header_text .$this->body;
		return $raw;
	}

}

?>