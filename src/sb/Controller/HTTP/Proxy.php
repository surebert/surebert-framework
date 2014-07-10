<?php

/**
 * Used to proxy http requests
 * @author paul.visco@roswellpark.org
 * @package Controller
 */

namespace Controllers;

class Proxy extends \sb\Controller\HTTP {

    /**
     * Use a ridiculous input arg delimiter so that all of args[0] is one string
     * @var type 
     */
    public $input_args_delimiter = '}';
    
    /**
     * Logs request to file if true
     * 
     * Log named after controller
     * 
     * @var boolean 
     */
    protected $log_to_file = true;
    
     /**
     * The default connection timeout
     * @var integer 
     */
    protected $connection_timeout = 10;
    
    /**
     * If set to true then it ignore https errors
     * @var type 
     */
    protected $ignore_ssl_errors = false;
    
    /**
     * Agent to use for transer, defaults to IE 10
     * @var type 
     */
    protected $agent = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)";
    
    /**
     * The default timeout to wait
     * @var int 
     */
    protected $timeout = 10;
    
    /**
     * Additional Curl opts to use during proxy request
     * 
     * e.g. [CURLOPT_POSTFIELDS => ['a' => 1], CURLOPT_TIMEOUT => 40]
     * @var array
     */
    protected $curl_opts = [];
    
    /**
     * Used to test if we want to continue or not
     * exits if you return false
     * 
     * @param string $url The url being requested
     */
    public function onBeforeGet($url){}
    
    /**
     * proxies request and responds
     * 
     * It will proxy both GET and POST data and return respose
     * 
     * @servable true
     * 
     * e.g. SITE_URL/proxy/get/http://someothersite?test=rest&ff=gg
     */
    public function get() {
        
        if (!isset($this->request->args[0])) {
            exit;
        }

        $url = $this->request->args[0];

        //add the $_GET vars to the request
        $query_string = http_build_query($this->request->get);
        $destination_url = $url . ($query_string ? '?'.$query_string : '');

        //exit if onBeforeGet doesn't pass
        if($this->onBeforeGet($destination_url) === false){
            return false;
        }
        
        //logs to file if enabled
        if($this->log_to_file){
            $logger = new \sb\Logger\CommandLine();
            $log_name = preg_replace("~[^\w+]~", "_", get_called_class());
            $logger->{$log_name}(json_encode(["ip" => \sb\Gateway::$remote_addr, "url" => $destination_url, "get" => $query_string, "post" => $this->request->post]));    
        }
        
        //proxy to site and return response
        $ch = curl_init();
        
        //set the url to grab the data from
        curl_setopt($ch, CURLOPT_URL, $destination_url);
        
        //set the function to pass the headers from the destination back to the client
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'headerCallBack'));
        
        //set the agent to be used for request
        curl_setopt($ch, CURLOPT_USERAGENT, $this->agent);
        
        //wait 10 seconds for timeout
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        
        //forward any post requests if they exist
        if(count($this->request->post)){
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->request->post);
        }
        
        //follow any redirects
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        
        //return the result
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        //ignore ssl errors if set to true
        if($this->ignore_ssl_errors){
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        
        //set any additional curl_opts given
        foreach($this->curl_opts as $key=>$val){
            curl_setopt($ch, $key, $val);
        }
        
        //display the output
        return curl_exec($ch);
    }
    
    /**
     * Called to process headers from URL being proxied
     * @param resource $ch The curl connection
     * @param string $data The header data
     * @return int length of the data from header
     */
    protected function headerCallBack($ch, $data){
        
        if (!is_null($data)) {
                if (preg_match("~^HTTP/.*? 404~", $data)) {
                   
                    header(trim($data));
                    while (ob_get_level() > 0) {
                        ob_end_flush();
                    }
                }

                if (preg_match("~^Content-disposition~", $data)) {
                    header(str_replace("filename=", 'filename="', trim($data)) . '"');
                    while (ob_get_level() > 0) {
                        ob_end_flush();
                    }
                }

                if (preg_match("~^Content-Type~i", $data)) {
                    header(trim($data));
                    while (ob_get_level() > 0) {
                        ob_end_flush();
                    }
                }
            }
            return strlen($data); //This means that we handled it, so cURL will keep processing
    }

}
