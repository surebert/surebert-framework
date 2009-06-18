<?php
/**
 * Object to represent an email used by sb_EmailReader and sb_EmailWriter
 * 
 * @author: Paul Visco
 * @package: email
 * @Version 2.2 10/15/07 - added cc and bcc support
 *
<code>
 //create an email to send $to, $subject, $message, $from
$myMail = new sb_Email('paul.visco@roswellpark.org', 'Testing Email', 'Hello World', 'paul.visco@roswellpark.org');

//you can set the cc array to add addresses which are cced
//$myMail->cc = Array("paulsidekick@gmail.com");

//you can reference inline attachments in the HTML by their cid:{THEIR NAME} e.g.
//$myMail->body_HTML = '<h1>Hello there</h1><img src="cid:MyPicture.jpg" />';

//$myMail->body_HTML = '<h1>Hello there</h1>';

//create an optional attachment
//$myAttachment = new sb_Email_Attachment(ROOT.'/private/config/App.php', 'application/php');

//or zipping the attachment
//$myAttachment->zip();

//PGP encrypt the attachment
//$myAttachment->pgp_encrypt('B902E698D01A6C99243D67A827C86F40B3FE5700');

//add the attachment to the email object, you could add more attachments as necessary
$myMail->add_attachment($myAttachment);

var_dump($myMail->send());

//you can also manually add an attachment from a non file e.g. data from database
//create an optional attachment
$myAttachment = new sb_Email_Attachment();

//set up the properties for the attachment
$myAttachment->name = 'MyPicture.jpg';

//this is the content, in this case I am ready the blob data from a saved image file but you could easily replace this with blob data from a database.  The mime type will be added based on the extension using sb_Files::extension_to_mime.  For bizarre mime-types that are not in sb_Files::extension_to_mime you can override this by setting the mime-type manually $myAttachment->mime_type ='bizarre/weird';
$myAttachment->contents = $filedata;

</code>
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
     * Constructs an email
     * @param String $to The address to send the email to
     * @param String $subject The subject of the email
     * @param String $message The plaintext message being sent
     * @param String $from The address it is being sent from
     */
    public function  __construct($to='', $subject='', $message='', $from=''){

        $this->to = $to;
        $this->subject = $subject;
        $this->message = $message;
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
     * An instance of sb_Email_Writer used to send
     * @var sb_Email_Writer
     */
    protected static $outbox;

    /**
     * Uses sb_Email_Writer to send the email
     */
    public function send($outbox=null){

        if($outbox instanceof sb_Email_Writer){
            self::$outbox = $outbox;
        } else if(!self::$outbox){
            self::$outbox = new sb_Email_Writer();
        }
        
        self::$outbox->add_email_to_outbox($this);

        //return if sent
        return self::$outbox->send();

    }

}

?>