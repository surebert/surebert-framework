<?php
/**
 * An ssh2 Shell
 *
 * <code>
 * $shell = new \sb\SSH2\Shell('server.com', 1027);
 * $shell->login('uname', 'pass');
 *
 * $shell->create('vt102', null, 80, 24, SSH2_TERM_UNIT_CHARS);
 * $shell->exec('ls -al');
 *
 * </code>
 *
 * @package SSH2
 */
namespace sb\SSH2;

class Shell extends \sb\SSH2\Client{

    /**
     * The shell stream
     * @var stream
     */
    public $shell;

    /**
     * Opens a shell at the remote end and allocate a stream for it.
     * @param string $term_type hould correspond to one of the entries in the target system's /etc/termcap file. e.g. xterm, vt102
     * @param array $env may be passed as an associative array of name/value pairs to set in the target environment
     * @param integer $width Width of the virtual terminal.
     * @param integer $height Height of the virtual terminal.
     * @param constant $width_height_type SSH2_TERM_UNIT_CHARS or SSH2_TERM_UNIT_PIXELS
     *
     * @return sb_SSH2_Shell
     */
    public function create($term_type='xterm', $env=null, $width=80, $height=24, $width_height_type='')
    {
        $width_height_type = $width_height_type ? $width_height_type : SSH2_TERM_UNIT_CHARS;
        $this->shell = @ssh2_shell($this->connection, $term_type, $env, $width, $height, $width_height_type);
        
        stream_set_blocking($this->shell, true);

        if(!$this->shell){
            throw new \Exception('Cannot create shell');
        }

        return $this->shell;
    }

    /**
     * Execute a command on the interactive shell
     * @param string $command The command to exec
     * @param boolean $with_login_env o run a shell script with all the variables that you would have when logged in interactively
     * @return boolean
     */
    public function exec($command, $timeout=30, $with_login_env=true)
    {
        stream_set_timeout($this->shell, $timeout);
        if($with_login_env){
            $command = 'bash -l '.$command;
        }
        
        return fwrite($this->shell, $command.PHP_EOL);
    }
}
