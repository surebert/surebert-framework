<?php
/**
 * Used to log application state to the log files.
 *
 * @author paul.visco@roswellpark.org
 * @package Logger
 */
 namespace sb;

class Logger_StdOut extends Logger_Base{

    /**
     * Writes the data to file
     * @param string $data The data to be written
     * @param string $log_type The log_type being written to
     * @return boolean If the data was written or not
     */
    protected function write($data, $log_type)
    {
        return file_put_contents("php://stdout", "\n\n".date('Y/m/d H:i:s')."\n".$data);
    }

}
