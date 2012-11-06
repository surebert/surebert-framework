<?php
/**
 * Used to log application state to the log files.
 *
 * @author paul.visco@roswellpark.org
 * @package Logger
 * 
 */
namespace sb\Logger;

class FileSystem extends Base{
    
    /**
    * Creates a filesystem type logger
    * @param $agent String The agent string
    */
    public function __construct($agent = '', $log_root='')
    {
        
        parent::__construct($agent);
        $log_root = !empty($log_root) ? $log_root : ROOT.'/private/logs';
        $this->setLogRoot($log_root);
        
    }

    /**
     * Allows the setting of the log root
     * @param <type> $log_root
     */
    public function setLogRoot($log_root)
    {
        $this->log_root = $log_root;
    }

    /**
     * Grabs the log path based on the type of log
     * @param $log Sting the log type.  Should be in the $enabled_logs array
     * @return string The path to the log directory to be used
     */
    protected function getLogPath($log)
    {
        
        $dir = $this->log_root.'/'.$log.'/';
    
        if(!is_dir($dir)){
            mkdir($dir, 0777, true);
        }
        
        return $dir;
    }

    /**
     * Writes the data to file
     * @param string $data The data to be written
     * @param string $log_type The log_type being written to
     * @return boolean If the data was written or not
     */
    protected function write($data, $log_type)
    {
        return file_put_contents($this->getLogPath($log_type)
            .date('Y_m_d').'.log', "\n\n".\date('Y/m/d H:i:s')
            ."\t".$this->_agent_str
            ."\n".$data, \FILE_APPEND);
    }
}
