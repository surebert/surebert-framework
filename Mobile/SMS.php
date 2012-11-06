<?php

/**
 * SMS
 * 
 * <code>
 * $sms = new \sb\Mobile\SMS();
 * $sms->send_to['num'] ='716-228-7445';
 * $sms->send_to['carrier'] ='sprint';
 * $sms->send_from['num'] ='x@t.com';
 * $sms->send_from['carrier'] = '';
 * if($sms->send('Once upon a time there was a limited amount of characters')){
 *    echo 'sent';
 * }
 * </code>
 *
 * @author paul.visco@roswellpark.org
 * @package Mobile
 * 
 */
namespace sb\Mobile;

class SMS{
    
    /**
     * The telephone number to send to and the carrier
     *
     * @var string e.g. 716-555-5555
     */
    public $send_to = Array();
    
    /**
     * The telephone number to send to and the carrier
     *
     * @var string e.g. 716-555-5555
     */
    public $send_from = Array();
    
    /**
     * An array listing the email gateways for the carriers SMS service
     *
     * @var array
     */
    public static $carriers = Array(
        'tmobile' => 'tmomail.net',
        'sprint' => 'messaging.sprintpcs.com',
        'nextel' => 'messaging.nextel.com',
        'att' => 'txt.att.net ',
        'verizon' => 'vtext.com',
        'virgin' => 'vmobl.com' 
    );
    
    /**
     * Used to send the SMS
     *
     * @param string $message
     * @return boolean
     */
    public function send($message)
    {
        
        $send_to = str_replace("-","", $this->send_to['num'])
            .'@'.self::$carriers[$this->send_to['carrier']];
        
        $send_from = str_replace("-","", $this->send_from['num']);
          // .'@'.self::$carriers[$this->send_from['carrier']];
        
        //define headers - send from receiver
        $headers = "From: ".$send_from."\nEnvelope-To: ".$send_from."\n";

        //define return path
        $send_from =  "-f".$send_from;

        if(mail($send_to, "", $message, $headers, $send_from)){
            return true;
        }
        
        return false;
    }
    
}

