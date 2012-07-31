<?php
/**
 * Abstract class base for logging
 *
 * @author paul.visco@roswellpark.org
 * @package Logger
 *
 */
namespace sb;
abstract class Logger_Base{
    
    /**
     * If logging is enabled or not
     * @var boolean true = is logging
     */
    public $enabled = true;
    
    /**
     * An array of accepted log files
     * @var array
     */
    protected $_enabled_logs = Array();
    
    /**
     * The method used to convert non-string values for logging
     * @var string json or print_r
     */
    protected $_conversion_method = 'json';
    
    /**
     * The log root ROOT.'/logs';
     * @var string
     */
    protected $_log_root = '';
    
    /**
     * The string represting the agent/user that initiated the action
     *
     * @var string Set with $this->set_agent($str);
     */
    protected $_agent_str = 'n/a';
    
    /**
    * Creates an \sb\Logger\Base instance
    * @param $agent String The agent string
    *
    * <code>
    * //LOGGER_TYPE replace with FileSystem() etc
    * \App::$logger = new \sb\Logger\LOGGER_TYPE();
    * \App::$logger->set_agent_string("\t".App::$user->uname."\t".App::$user->roswell_id."\t".\sb\Gateway::$remote_addr);
    * \App::$logger->debug('Here is a message');
    * //If the argument is anything other than a string it is converted to json for logging as string
    * \App::$logger->files($obj);
    * </code>
    */
    public function __construct($agent = '')
    {
    
        $this->_agent_str = !empty($agent) ? $agent : Gateway::$remote_addr;
    }

    /**
     * Sets the agent string representing the agent/user that initiated the action
     *
     * @param string $str It is a string instead of an object as it may require specific formating for your needs e.g. "\t".App::$user->uname."\t".App::$user->roswell_id."\t".App::$user->ip
     */
    public function set_agent_string($str)
    {
        $this->_agent_str = $str;
    }
    
    /**
     * When any accepted logging method is called that is not defined it runs this
     * @param $log_type The type of log to produce
     * @param $arguments The arguments passed to the missing method, of which [0] is the message or object
     * @return boolean If the log is written or not
     */
    public function __call($log_type, $arguments)
    {

        //if logging is not enabled, just return true
        if(!$this->enabled){
            return true;
        }

        $data = $arguments[0];

        if(!is_string($data)){
            if($this->_conversion_method == 'print_r'){
                $data = print_r($data, 1);
            } else {
                $data = json_encode($data);
            }
        }

        return $this->__write($data, $log_type);

        return false;
    }

    /**
     * Writes data to whatever output method is defined
     * @param string $data The data to be written
     * @param string $log_type The log_type being written to
     * @return boolean If the data was written or not
     */
    abstract protected function __write($data, $log_type);
}
