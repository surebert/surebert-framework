<?php
/**
 * Object to represent an email used by sb_EmailReader and sb_EmailWriter
 * 
 * @author: Paul Visco
 * @package: email
 * @Version 2.2 10/15/07 - added cc and bcc support
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
	 * @var unknown_type
	 */
	public $header_info;
	
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
	 * An array of attachment objects
	 *
	 * @var array
	 */
	public $attachments = Array();
	
	/**
	 * Adds an attachment to the email
	 * @param $attachment sb_Email
	 */
	public function add_attachment(sb_Email_Attachment $attachment){
		$this->attachments[] = $attachment;
	}

}

?>