<?php

/**
 * Simple wrapper to send authenticated email via exchange without full SOAP client
 *
 * @author paul.visco@roswellpark.org
 * @package \sb\Email
 *
 */

namespace sb\Email;

class Exchange {

    /**
     * The addresses to send to
     * @var array
     */
    public $to = Array();

    /**
     * The addresses to cc to
     * @var array
     */
    public $cc = Array();

    /**
     * The addresses to bcc to
     * @var array
     */
    public $bcc = Array();

    /**
     * The message body of the email
     * @var string
     */
    public $message = '';

    /**
     * The subject of the email
     * @var string
     */
    public $subject = '';

    /**
     * Any X-name:value mail headers to send with email.  The X- is added for you
     * @var array
     */
    public $mail_headers = Array();

    /**
     * If set to true, curl is set to debug output
     * @var boolean
     */
    public $debug = false;

    /**
     * The uname to use for auth
     * @var string
     */
    protected $uname;

    /**
     * The password to use for auth
     * @var string
     */
    protected $passwd;

    /**
     * The server to use for auth
     * @var string
     */
    protected $server;

    /**
     * The logger to log with
     * @var sb_Logger_Base
     */
    protected $logger;

    /**
     * Instantiate an email
     * @param string $to The email address or comma delimited addresses to send to
     * @param string $subject
     * @param string $message
     * @throws Exception
     *
     * <code>
      $mail = new \sb\Email\Exchange("someaddress@gmail.com", "hello < world", "hey there");
      $mail->setAuthentication('https://yourexchangeserver.com/EWS/exchange.asmx', "user", "pass");
      var_dump($mail->send());
     * </code>
     */
    public function __construct($to, $subject, $message) {
        if (is_string($to)) {
            $to = explode(",", $to);
        }

        if (!is_array($to)) {
            throw(new Exception("To must be a string email address or an array of email addresses"));
        }
        $this->to = $to;
        $this->subject = $subject;
        $this->message = $message;
    }

    /**
     * Sets up the authentication parameters
     * @param string $server e.g. https://your_site.com/EWS/exchange.asmx
     * @param string $uname The username
     * @param string $passwd The password
     */
    public function setAuthentication($server, $uname, $passwd) {
        $this->server = $server;
        $this->uname = $uname;
        $this->passwd = $passwd;
    }

    /**
     * Sets up the authentication parameters
     * @param string $server e.g. https://your_site.com/EWS/exchange.asmx
     * @param string $uname The username
     * @param string $passwd The password
     */
    public function setLogger(\sb\Logger\Base $logger) {
        $this->logger = $logger;
    }

    /**
     * Adds security info to email. By default adds server, script, request, tstamp to X-sbfinfo header in base64 format
     * @param string $format Text or HTML
     */
    public function addSecurityInfo($format) {

        $this->mail_headers['SBF-INFO'] = base64_encode("H: " . php_uname('n') . "\nP: " . basename(dirname(ROOT)) . "\nR: " . \sb\Gateway::$request->request . "\nT: " . date('m/d/Y H:i:s'));
    }

    /**
     * Send the email and save to a specific exchange folder
     * @param string $format Text or HTML
     * @param string $save_to_folder sentitems by default
     * @param string $curl_opts by default ignore SSL cert errors, however you can override this
     * @return Array the result string and the curl handle as values
     * @throws Exception if there is no auth already set up
     */
    public function send($format = 'Text', $save_to_folder = 'sentitems', $curl_opts = Array()) {
        $format = $format == 'HTML' ? $format : 'Text';
        if (!$this->server || !$this->uname || !$this->passwd) {
            throw(new Exception("You must setAuthentication before sending"));
        }

        $this->addSecurityInfo($format);

        $message = $this->message;

        if (defined('DEBUG_EMAIL')) {
            $message .= "\n" . str_repeat("-", 50);
            $message .= "\nDEBUG MODE - All email redirected to this email.\nRecipients when not in debug mode:";

            foreach (Array('to', 'cc', 'bcc') as $send_type) {
                $recipients = implode(",", $this->$send_type);
                $message .= "\n" . strtoupper($send_type) . ": " . ($recipients ? $recipients : 'none');
                $this->$send_type = Array();
            }

            $this->to = Array(DEBUG_EMAIL);
        }
        if ($format == 'HTML') {
            $message = nl2br($message);
        }

        $xml = '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:m="http://schemas.microsoft.com/exchange/services/2006/messages" xmlns:t="http://schemas.microsoft.com/exchange/services/2006/types" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
        <soap:Header>
        <t:RequestServerVersion Version="Exchange2010_SP1" />
            </soap:Header>
        <soap:Body>
        <m:CreateItem MessageDisposition="SendAndSaveCopy">
        <m:SavedItemFolderId>
        <t:DistinguishedFolderId Id="sentitems" />
            </m:SavedItemFolderId>
        <m:Items>
        <t:Message>
        <t:Subject>' . htmlentities($this->subject) . '</t:Subject>
        <t:Body BodyType="' . $format . '">' . htmlentities($message) . '</t:Body>';
        foreach ($this->mail_headers as $k => $val) {
            $xml .= '<t:ExtendedProperty>
            <t:ExtendedFieldURI DistinguishedPropertySetId="InternetHeaders" PropertyName="X-' . $k . '" PropertyType="String" />
            <t:Value>' . $val . '</t:Value>
                </t:ExtendedProperty>';
        }
        foreach (Array('to', 'cc', 'bcc') as $send_type) {

            if (!empty($this->$send_type)) {
                $xml .= '<t:' . ucfirst($send_type) . 'Recipients>';
                foreach ($this->$send_type as $email) {
                    if (!empty($email)) {
                        $xml .= '
                        <t:Mailbox>
                        <t:EmailAddress>' . $email . '</t:EmailAddress>
                            </t:Mailbox>
                            ';
                    }
                }
                $xml .= '</t:' . ucfirst($send_type) . 'Recipients>';
            }
        }


        $xml .= '
            </t:Message>
            </m:Items>
            </m:CreateItem>
            </soap:Body>
            </soap:Envelope>';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->server);

        if ($this->debug) {
            curl_setopt($ch, CURLOPT_VERBOSE, 2);
        }
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_NTLM);
        curl_setopt($ch, CURLOPT_USERPWD, $this->uname . ':' . $this->passwd);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_COOKIEJAR, '/dev/null');
        $headers = array(
            'Content-type: text/xml;charset="utf-8"',
            'Accept: text/xml',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
            'SOAPAction: "run"',
            'Content-length: ' . strlen($xml),
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        foreach ($curl_opts as $k => $v) {
            curl_setopt($ch, $k, $v);
        }
        $result = curl_exec($ch);

        $sent = stristr($result, 'success') ? true : false;

        if ($this->logger) {
            if ($sent) {
                $this->logger->sbEmailExchangeSent($xml);
            } else {
                $this->logger->sbEmailExchangeError($xml);
            }
        }

        return (object) Array('sent' => $sent, 'result' => $result, 'curl_handle' => $ch);
    }

}
