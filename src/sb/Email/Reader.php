<?php
/**
 * This class is used to read an email inbox.  It required \sb\Strings from the 
 * surebert framework REQUIRES \sb\Email\Reader.php
 *
 * @author paul.visco@roswellpark.org
 * @package \sb\Email
 *
 */
namespace sb\Email;

class Reader
{

    /**
     * The email address to load
     *
     * @var string e.g. paul@test.com
     */
    public $address;

    /**
     * The password to access the email account
     *
     * @var string
     */
    public $pass;

    /**
     * The host of the email account
     *
     * @var string
     */
    public $host;

    /**
     * The protocol of email account, imap or pop
     *
     * @var string
     */
    public $protocol;

    /**
     * The port of the email server
     *
     * @var integer Optional (default is 143 for imap or 25 for pop)
     */
    public $port = '';

    /**
     * A reference to the inbox once it is opened
     *
     * @var reference
     */
    public $inbox;

    /**
     * Are we connected to the inbox?
     *
     * @var boolean
     */
    public $connected = 0;

    /**
     * The total number of emails in the inbox
     *
     * @var integer
     */
    public $email_count = 0;

    /**
     * Determines is body HTML is captured along with plain text body
     *
     * @var boolean
     */
    public $capture_body_html = 0;

    /**
     * All of the emails found in the inbox
     *
     * @var array
     */
    public $emails = Array();

    /**
     * If not empty all email info found is logged in this file
     *
     * @var string
     */
    public $log_file = '';

    /**
     * Logs into the email inbox
     *
     * <code>
     * $email_box = new Email_Reader('paul@test.org', 'myPass', 'mail.test.org', 'imap');
     * $email_box->log_file = '../_cache/sb_Email_Reader.log';
     * //$email_box->capture_body_html =0;
     *
     * if ($email_box->open()) {
     *
     *     $email_box->fetchMessages();
     *
     *     foreach ($email_box->emails as $email) {
     *         //print_raw($email);
     *         foreach ($email->attachments as $attachment) {
     *
     *             if ($attachment->extension =='gif') {
     *                 $cached = '../_cache/'.$attachment->name;
     *
     *                 file_put_contents($cached, $attachment->contents);
     *                 echo '<img src="'.$cached.'">';
     *             }
     *         }
     *         $email_box->deleteMessage($email->index);
     *     }
     * }
     *
     * $email_box->close('expunge');
     * </code>
     *
     * @param string  $address  e.g. paul@domain.com
     * @param string  $pass     e.g. myPass
     * @param string  $host     e.g. mail.example.com
     * @param string  $protocol imap or pop
     * @param integer $port
     *
     */
    public function __construct($address, $pass, $host, $protocol, $port = '')
    {

        $this->log("\n-----------------" . date('m/d/y H:i:s') . "------------------\n");

        $this->address = $address;
        $this->pass = $pass;
        $this->host = $host;
        $this->protocol = $protocol;

        //set default port for protocols if not defined
        if (!empty($email_box->port)) {

            $this->port = $port;
        } else {

            if ($this->protocol == "imap") {

                $this->port = "143";
            } elseif ($this->protocol == "pop") {

                $this->port = "25";
            }
        }
    }

    /**
     * Logs messages to a log file if it is set
     *
     * @param string $message
     */
    public function log($message)
    {

        if (!empty($this->log_file)) {
            //echo $message;
            \file_put_contents($this->log_file, "\n" . $message, \FILE_APPEND);
        }
    }

    /**
     * Opens the inbox and returns the connection status
     *
     */
    public function open()
    {

        //check for mail
        $host = "{" . $this->host . ":" . $this->port . "/" . $this->protocol . "/notls}INBOX";

        //open the mail box for reading
        $this->inbox = \imap_open($host, $this->address, $this->pass);

        if (!$this->inbox) {

            $this->connected = 0;
        } else {

            $this->connected = 1;
        }

        return $this->connected;
    }

    /**
     * Closes the inbox and expunges deleted messages if expunge is true
     *
     * @param boolean $expunge true expunges
     */
    public function close($expunge)
    {
        //expunge emails set to delete if delete_after_read is set
        if ($expunge) {
            \imap_close($this->inbox, CL_EXPUNGE);
        } else {
            \imap_close($this->inbox);
        }
    }

    /**
     * Undeletes a specific messgae in the inbox
     *
     * @param  integer $index
     * @return boolean Deleted state
     */
    public function undeleteMessage($index)
    {

        $delete = \imap_undelete($this->inbox, $index);

        return $delete;
    }

    /**
     * Deletes a specific messgae in the inbox
     *
     * @param  integer $index
     * @return boolean Deleted state
     */
    public function deleteMessage($index)
    {

        $delete = \imap_delete($this->inbox, $index);

        return $delete;
    }

    /**
     * Counts the number of messages in the inbox and returns the total number of emails
     *
     * @return integer
     */
    public function countMessages()
    {

        $check = \imap_check($this->inbox);

        return $this->email_count = $check->Nmsgs;
    }

    /**
     * Parses header information from an email and returns it to as properties of the email object
     *
     * @param sb_Email $email
     * @param object   $header
     */
    public function parseHeaderInfo(Email &$email, $header)
    {

        //map the most useful header info to the email object itself
        $email->all_headers = $header;
        $email->subject = $header->subject;
        $email->to = $header->to[0]->mailbox . '@' . $header->to[0]->host;
        $email->from = $header->from[0]->mailbox . '@' . $header->from[0]->host;
        $email->reply_to = $header->reply_to[0]->mailbox . '@' . $header->reply_to[0]->host;
        $email->sender = $header->sender[0]->mailbox . '@' . $header->sender[0]->host;
        $email->size = $header->Size;
        $email->date = $header->date;
        $email->timestamp = $header->udate;
        $email->message_id = $header->message_id;
        if ($header->Deleted == 'D') {
            $email->deleted = 1;
        }

        //add the entire header in case we want access to custom headers later
        $this->headers = $header;
    }

    /**
     * Check in an email part for attachments and data
     *
     * @param \sb\Email $email   The email being examined
     * @param object    $part    The current part
     * @param string    $part_id The id of the part
     */
    private function examinePart(&$email, $part)
    {
        //echo '<br />'.$part->part_id.' '.$part->type.' '.$part->dparameters[0]->value;
        //get subtype JPEG, WAV, HTML, PLAIN, etc
        $subtype = \strtolower($part->subtype);

        //get encoding 0 = 7bit, 1= 8bit, 2=binary, 3=base64, 4=quoted prinatble, 5=other
        $encoding = $part->encoding;

        switch ($subtype) {

            case 'plain':
                if (empty($email->body)) {
                    $email->body_encoding = $encoding;
                    $email->body = imap_fetchbody($this->inbox, $email->index, $part->part_id);
                }
                break;

            case 'html':
                $email->body_HTML = imap_fetchbody($this->inbox, $email->index, $part->part_id);
                break;

            case 'applefile':
            case 'gif':
            case 'png':
            case 'jpg':
            case 'jpeg':
            case 'wav':
            case 'mp3':
            case 'mp4':
            case 'flv':

                if ($part->type == 5) {
                    $attachment = new Email_Attachment();
                    $attachment->sizeK = $part->bytes / 1000;
                    $attachment->subtype = $subtype;
                    $attachment->type = $part->type;
                    $attachment->name = $part->dparameters[0]->value;
                    //change jpeg into jpg
                    $attachment->name = \str_ireplace('jpeg', 'jpg', $attachment->name);
                    $attachment->extension = \strtolower(end(explode(".", $attachment->name)));

                    $attachment->contents = \imap_fetchbody($this->inbox, $email->index, $part->part_id);

                    //decode base64 contents
                    if ($part->encoding == 3) {
                        $attachment->contents = \imap_base64($attachment->contents);
                    }

                    $attachment->encoding = $part->encoding;
                    $email->attachments[] = $attachment;
                }
        }
    }

    /**
     * Fetches all the messages in an inbox and put them in the emails array
     *
     * @return returns the array of all email objects found
     */
    public function fetchMessages()
    {

        //count the messages
        $this->countMessages();

        //if there are zero emails report this to the user
        if ($this->email_count == 0) {

            $this->log('There are no emails to process!');

            return false;
        }

        $this->log($this->email_count . ' ' . \Strings::pluralize($this->email_count, 'email') . ' to process.');

        for ($i = 1; $i < $this->email_count + 1; $i++) {

            $email = new Email;

            //set the message index in case we want to delete it later
            $email->index = $i;

            //get all the header info
            $this->parseHeaderInfo($email, imap_headerinfo($this->inbox, $i));

            $structure = \imap_fetchstructure($this->inbox, $i);

            //the type of email format
            $email->subtype = \strtolower($structure->subtype);

            $email->structure = $structure;

            //if the email is divided into parts, html, plain, attachments, etc
            if (isset($structure->parts)) {

                $part_id = 1;

                foreach ($structure->parts as $part) {

                    //get type 0 = text, 1 = multipart, 2 = message,
                    //3 = application, 4 = audio, 5= image, 6= video, 7 = other
                    $type = $part->type;

                    //multipart
                    $sub_id = 1;
                    if ($type == 1 && isset($part->parts)) {
                        foreach ($part->parts as $part) {
                            $part->part_id = $part_id . '.' . $sub_id;
                            $this->examinePart($email, $part);

                            $sub_id++;
                        }
                    } else {
                        $part->part_id = $part_id;
                        $this->examinePart($email, $part, $part_id);
                    }

                    $part_id++;
                }
            } else {

                //it is just a plain text email
                $email->body = \imap_fetchbody($this->inbox, $i, 1);
                $email->body_encoding = $structure->encoding;
            }

            //if the body is base64 encoded, decode it
            if ($email->body_encoding == 3) {
                $email->body = \base64_decode($email->body);
            }

            $this->log(print_r($email, 1));

            //store the email
            $this->emails[] = $email;
        }
    }
}

