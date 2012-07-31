<?php
/**
 *  Used to create a control new processes
 *
 * Adapted from http://us.php.net/manual/en/function.exec.php
 * @author paul.visco@roswellpark.org
 * @package Linux
 */
namespace sb;

class Linux_Process{
    
    /**
     * The pid of the command executed
     * @var integer
     */
    protected $pid = false;

    /**
     * The command that gets executed
     * @var string
     */
    protected $command ='';

    /**
     * $process = new Process('ls -al');
     * if($process->status){
     *    echo $process->get_pid();
     * }
     *
     * $process = new Process(1234);
     * if($process->status()){
     *    $process->stop();
     * }
     *
     * @param mixed $data The process ID to manage if a integer, else the command if string
     */
    public function __construct($data=false)
    {
        if(preg_match("~^\d+$~", $data)){
            $this->set_pid($data);
        } else if(is_string($data)){
            $this->set_command($data);
            $this->start();
        }
    }

    /**
     * Sets the command to execute
     * @param string $command
     */
    protected function set_command($command)
    {
        $this->command = $command;
    }

    /**
     * Sets the pid of the process
     * @param integer $pid
     */
    protected function set_pid($pid)
    {
        $this->pid = $pid;
    }

    /**
     * Gets the pid of the current process or false if not set
     *
     * @return integer
     */
    public function get_pid()
    {
        return $this->pid;
    }

    /**
     * Gets the process CPU and memory usage
     * @return <type>
     */
    public function get_usage()
    {
        $command = 'ps u -p '.$this->pid;
        exec($command, $op);
        if (isset($op[1])){
            $data = explode(" ", $op[1]);
            $usage = Array(
                '%CPU' => $data[5],
                '%MEM' => $data[7]
            );
            return $usage;
        }

        return false;
    }

    /**
     * Gets the status of the process, running or not
     * @return boolean
     */
    public function status()
    {
        $command = 'ps -p '.$this->pid;
        exec($command,$op);
        if (!isset($op[1])){
            return false;
        }

        return true;
    }

    public function start()
    {
        if ($this->command != ''){
            $command = 'nohup '.$this->command.' > /dev/null 2>&1 & echo $!';
            exec($command ,$op);
            $this->pid = (int)$op[0];
            return true;

        } else {
            throw(new \Exception('$this->command not set!'));
        }
    }

    public function stop()
    {
        $command = 'kill '.$this->pid;
        exec($command);
        if ($this->status() == false){
            return true;
        }
        return false;
    }
}

?>