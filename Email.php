<?php
/**
 * Object to represent an email used by sb_EmailReader and sb_EmailWriter
 * 
 * @author paul.visco@roswellpark.org
 * @package Email
 *
 */
namespace sb;
use \sb\Email\Writer;

class Email{
    
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
     * //$myAttachment->pgpEncrypt('XXXX98D01A6CXXXXXX5700');
     *
     * //add the attachment to the email object, you could add more attachments as necessary
     * $myMail->addAttachment($myAttachment);
     *
     * var_dump($myMail->send());
     *
     * //you can also manually add an attachment from a non file e.g. data from database
     * //create an optional attachment
     * $myAttachment = new \sb\Email_Attachment();
     *
     * //set up the properties for the attachment
     * $myAttachment->name = 'MyPicture.jpg';
     *
     * //this is the content, in this case I am ready the blob data from a saved image file but you could easily replace this with blob data from a database.  The mime type will be added based on the extension using \sb\Files::extension_to_mime.  For bizarre mime-types that are not in \sb\Files::extension_to_mime you can override this by setting the mime-type manually $myAttachment->mime_type ='bizarre/weird';
     * $myAttachment->contents = $filedata;
     *
     * </code>
     *
     * @param String $to The address to send the email to
     * @param String $subject The subject of the email
     * @param String $message The plaintext message being sent
     * @param String $from The address it is being sent from
     */
    public function  __construct($to='', $subject='', $message='', $from='')
    {

        $this->to = $to;
        $this->subject = $subject;
        $this->body = $message;
        $this->from = $from;

    }

    /**
     * Adds an attachment to the email
     * @param $attachment \sb\Email
     */
    public function addAttachment(Email_Attachment $attachment)
    {
        $this->attachments[] = $attachment;
    }

    /**
     * Add an sb_ICalendar_Event request
     * 
     * @param sb_ICalendar_Event $event
     */
    public function addIcalendarEvent(ICalendar_Event $event)
    {

        $a = new Email_Attachment();
        $a->mime_type = 'text/calendar;';
        $a->setEncoding('8bit');
        $a->name = 'event.ics';
        $a->contents = $event->__toString();

        $this->addAttachment($a);
    }

    /**
     * An instance of \sb\Email\Writer used to send
     * @var \sb\Email\Writer
     */
    protected static $outbox;

    /**
     * Fires before sending, if returns false, then sending does not occur
     * @return boolean
     */
    public function onBeforeSend()
    {
        return true;
    }
    /**
     * Uses sb_Email_Writer to send the email
     */
    public function send($outbox=null)
    {

        if($outbox instanceof Writer){
            self::$outbox = $outbox;
        } elseif(!self::$outbox){
            self::$outbox = new Writer();
        }

        if($this->onBeforeSend($this) !== false){
            self::$outbox->addEmailToOutbox($this);

            //return if sent
            return self::$outbox->send();
        }

    }

    /**
     * Ups the importance of the email, in outlook this displays a exclamation point
     */
    public function makeImportant()
    {
        $this->headers[] = $this->addHeader('Priority', 'Urgent');
        $this->headers[] = $this->addHeader('Importance', 'high');
    }

    /**
     * Adds custom email headers by key value
     * <code>
     * $mail->addHeader('Priority', 'low');
     * </code>
     */
    public function addHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }
    
    /**
     * Convert the email to a multipart_message
     * @return string the raw email source
     */
    public function constructMultipartMessage() 
    {
        
        $mixed_boundary = '__mixed_1S2U3R4E5B6E7R8T9';
        $alterative_boundary = '__alter_1S2U3R4E5B6E7R8T9';

        $this->attachments_in_HTML =0;

        if(strstr($this->body_HTML, "cid:")) {

            $this->attachments_in_HTML =1;
            $related_boundary = '__relate_1S2U3R4E5B6E7R8T9';
        }

        $this->_header_text = "From: ".$this->from.PHP_EOL;
        $this->_header_text .= "Reply-To: ".$this->from.PHP_EOL;
        $this->_header_text .= "Return-Path: ".$this->from.PHP_EOL;

        foreach($this->cc as $cc) {
            $this->_header_text .="Cc:".$cc.PHP_EOL;
        }

        foreach($this->bcc as $bcc) {
            $this->_header_text .="Bcc:".$bcc.PHP_EOL;
        }

        $this->_header_text .= "MIME-Version: 1.0".PHP_EOL;
        
        foreach($this->headers as $key=>$val) {
            $this->_header_text .= $key.":".$val.PHP_EOL;
        }
        
        $this->_header_text .= "Content-Type: multipart/mixed;".PHP_EOL;
        $this->_header_text .= ' boundary="'.$mixed_boundary.'"'.PHP_EOL.PHP_EOL;
        
        // Add a message for peoplewithout mime
        $message = "This message has an attachment in MIME format created with surebert mail.".PHP_EOL.PHP_EOL;

        //if there is body_HTML use it otherwise use just plain text
        if(!empty($this->body_HTML)) {

            $message .= "--".$mixed_boundary.PHP_EOL;

            if($this->attachments_in_HTML == 1) {
                $message .= "Content-Type: multipart/related;".PHP_EOL;
                $message .= ' boundary="'.$related_boundary.'"'.PHP_EOL.PHP_EOL;
                $message .= "--".$related_boundary.PHP_EOL;
            }

            $message .= "Content-Type: multipart/alternative;".PHP_EOL;
            $message .= ' boundary="'.$alterative_boundary.'"'.PHP_EOL.PHP_EOL;

            $message .= "--".$alterative_boundary.PHP_EOL;
            $message .= "Content-Type: text/plain; charset=".$this->charset."; format=flowed".PHP_EOL;
            $message .= "Content-Transfer-Encoding: ".$this->transfer_encoding.PHP_EOL;
            $message .= "Content-Disposition: inline".PHP_EOL.PHP_EOL;
            $message .= $this->body . PHP_EOL;
            
            $message .= "--".$alterative_boundary.PHP_EOL;
            $message .= "Content-Type: text/html; charset=".$this->charset.PHP_EOL;
            $message .= "Content-Transfer-Encoding: ".$this->transfer_encoding.PHP_EOL.PHP_EOL;
            $message .= $this->body_HTML . PHP_EOL;
            
            $message .="--".$alterative_boundary."--".PHP_EOL;

        } else {

            $message .= "--".$mixed_boundary.PHP_EOL;
            $message .= "Content-Type: text/plain; charset=".$this->charset."; format=flowed".PHP_EOL;
            $message .= "Content-Transfer-Encoding: ".$this->transfer_encoding.PHP_EOL;
            $message .= "Content-Disposition: inline".PHP_EOL.PHP_EOL;
            $message .= $this->body . PHP_EOL;
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
                    $attachment->mime_type = \sb\Files\fileToMime($attachment->filepath);
                }

            }
            $ex = explode(".", $attachment->name);
            $attachment->extension = strtolower(array_pop($ex));

            //try and guess the mime type unless it is set
            if(empty($attachment->mime_type)) {
                $attachment->mime_type = Files::extensionToMime($attachment->extension);
            }

            if($attachment->encoding == 'base64'){
                $attachment->contents = chunk_split(base64_encode($attachment->contents));

            }
           
            // Add file attachment to the message

            if($this->attachments_in_HTML == 1) {
                $message .= "--".$related_boundary.PHP_EOL;
            } else {
                $message .= "--".$mixed_boundary.PHP_EOL;
            }

            if($attachment->mime_type == 'text/calendar'){
                $message .= "Content-class: urn:content-classes:calendarmessage;".PHP_EOL;
            }
            
            $message .= "Content-Type: ".$attachment->mime_type.";".PHP_EOL;
            $message .= " name=".$attachment->name.PHP_EOL;

            $message .= "Content-Transfer-Encoding: ".$attachment->encoding.PHP_EOL;
            $message .= "Content-ID: <".$attachment->name.">".PHP_EOL.PHP_EOL;

            $message .=  $attachment->contents.PHP_EOL;

        }

        //end related if using body_HTML
        if($this->attachments_in_HTML == 1) {
            $message .= "--".$related_boundary."--".PHP_EOL;
        }

        //end message
        $message .="--".$mixed_boundary."--".PHP_EOL;

        $this->body = $message;
        
        $raw = "To: ".$this->to.PHP_EOL;
        $raw .= "Subject: ".$this->subject.PHP_EOL;
        $raw .= $this->_header_text .$this->body;
        return $raw;
    }

}

