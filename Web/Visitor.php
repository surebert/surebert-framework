<?php
/**
 * Models an online visitor - usage requires \sb\Web_Visitors
 * 
 * @author paul.visco@roswellpark.org 
 * @package Web
 *
 */
namespace sb\Web;

class Visitor{
    /**
     * The IP address of the visitor
     * @var string
     */
    public $ip;
    /**
     * The tstamp of the visit
     * @var integer
     */
    public $tstamp;

    /**
     * The unique user name of the visitor in your system, or guest
     * @var string
     */
    public $uname;
    
    /**
     * The display name of the visitor in your system
     * @var string
     */
    public $dname;

    /**
     * The user agent of the visitor in short SF, FF, IE, bot etc
     * @var string
     */
    public $agent;

    /**
     * The full agent string if the short name was not determined
     * @var string
     */
    public $agent_str;

    /**
     * If the user coming from a mobile device
     * @var boolean
     */
    public $mobl;

    /**
     * Creates a new \sb\Web_Visitor
     * @param string $uname The unique username of the visitor
     * @param string $dname The display name of the visitor
     * @param boolean $mobl If the user is coming from mobile site or not
     */
    public function __construct($uname='guest', $dname='', $mobl=0)
    {

        $this->uname = $uname;
        $this->dname = $dname;
        $this->mobl = $mobl;
        $this->tstamp = time();
        $this->ip = class_exists('Gateway') ? Gateway::$remote_addr : '';
        $this->agent = class_exists('Gateway') && isset(Gateway::$agent) ? Gateway::$agent : '';
    }

    /**
     * Logs a \sb\Web_Visitor in the database
     * @param PDO $db Optional database connection to use for \sb\Web_Vistors
     */
    public function log($db=null)
    {
        
        if($db instanceof PDO){
            \sb\Web\Visitors::$db=$db;
        }

        return \sb\Web\Visitors::log($this);

    }
}

