<?php

/**
 * Used to interact with gitlab
 * @author paul.visco@gmail.com
 */

namespace sb\Gitlab;

class Client {

    /**
     * The client connectionto gitlab using private key
     * @var string 
     */
    protected $client;

    /**
     * The gitlab host to connec to
     * @var string 
     */
    protected $gitlab_host;
    
    /**
     * The api key to connect with
     * @var string 
     */
    protected $private_key;
    
    /**
     * Do you want to debug curl output
     * @var boolean 
     */
    protected $debug;

    /**
     * Connects to gitlab
     * @param string $gitlab_host The gitlab host to connect to
     * @param string $private_key The private key
     * <code>
     * $gitlab = new \sb\Gitlab\Client('https://gitlab.yoursite.com','YOUR_PRIVATE_TOKEN', true);
     * $project =  new \sb\Gitlab\Project('namespace:project', $client);
     * $issue = $project->addIssue("title", "description", "assignee.email@yoursite.com", "bugfix");
     * $project->issueClose($issue);
     * $project->issueReopen($issue);
     * $issues = $project->getIssue();
     * </code>
     */
    public function __construct($gitlab_host, $private_key, $debug=false) {
        $this->gitlab_host = $gitlab_host;
        $this->private_key = $private_key;
        $this->debug = $debug;
    }

    /**
     * GRabs data from a URL and json_decodes it 
     * @param string $url URL to grab
     * @param array $data http data to pass
     * @param string $method post, put delete, default post
     * @return object
     * @throws Exception
     */
    public function get($url, $data = [], $method = 'post') {

        $ch = curl_init($this->gitlab_host . '/api/v3' . $url);
       
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'PRIVATE-TOKEN:' . $this->private_key
        ));
        if($this->debug){
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            if(!\sb\Gateway::$command_line){
                $curl_log = fopen("php://temp", 'rw');
                curl_setopt($ch, CURLOPT_STDERR, $curl_log);
            }
            
        }
         
       
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        if ($data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            if ($method != 'post') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            }
        }

        $error = curl_error($ch);
        $error_no = curl_errno($ch);
      
        if ($error_no) {
            throw(new \Exception($error . ': ' . $error_no));
        }
        $data = json_decode(curl_exec($ch));
        
        if($this->debug && !\sb\Gateway::$command_line){
            rewind($curl_log);
            $output= fread($curl_log, 2048);
            echo "<pre>". print_r($output, 1). "</pre>";
            fclose($curl_log);

        }
        
        return $data;
    }
    
    public function getApi($api){
        $classname = "\\sb\\Gitlab\\".$api;
        $api = new $classname($this->client);
        return $api;
        
    }

}
