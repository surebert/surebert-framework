<?php

/**
 *  Used to create a control new processes
 *
 * Adapted from http://us.php.net/manual/en/function.exec.php
 * @author paul.visco@roswellpark.org
 * @package Linux
 */
namespace sb\Linux;

class Process
{

    /**
     * The pid of the command executed
     * @var integer
     */
    protected $pid = false;

    /**
     * The command that gets executed
     * @var string
     */
    protected $command = '';

    /**
     * Create a new linux process
     * @param mixed $data The process ID to manage if a integer, else the command if string
     * 
     * <code>
     $process = new \sb\Linux\Process('ping google.com');
     
      if($process->status()){
         echo $process->getPid();
      }
     
      $process = new \sb\Linux\Process($process->getPid());
      if($process->status()){
         $process->stop();
      }
     * </code>
     */
    public function __construct($data = false)
    {
        if (preg_match("~^\d+$~", $data)) {
            $this->setPid($data);
        } elseif (is_string($data)) {
            $this->setCommand($data);
            $this->start();
        }
    }

    /**
     * Sets the command to execute
     * @param string $command
     */
    protected function setCommand($command)
    {
        $this->command = $command;
    }

    /**
     * Sets the pid of the process
     * @param integer $pid
     */
    protected function setPid($pid)
    {
        $this->pid = $pid;
    }

    /**
     * Gets the pid of the current process or false if not set
     *
     * @return integer
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * Gets the process CPU and memory usage
     * @return <type>
     */
    public function getUsage()
    {
        $command = 'ps u -p ' . $this->pid;
        exec($command, $op);
        if (isset($op[1])) {
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
        $command = 'ps -p ' . $this->pid;
        exec($command, $op);
        if (!isset($op[1])) {
            return false;
        }

        return true;
    }

    public function start()
    {
        if ($this->command != '') {
            $command = 'nohup ' . $this->command . ' > /dev/null 2>&1 & echo $!';
            exec($command, $op);
            $this->pid = (int) $op[0];
            return true;
        } else {
            throw(new \Exception('$this->command not set!'));
        }
    }

    public function stop()
    {
        $command = 'kill ' . $this->pid;
        exec($command);
        if ($this->status() == false) {
            return true;
        }
        return false;
    }
}

