<?php

/**
 * Used to communicate with NTLM web services
 * @author paulsidekick@gmail.com
 */

namespace sb\CURL\Client;

class NTLM extends \sb\CURL\Client {

    /**
     * The username and password to login to the service with
     * @param string $user
     * @param string $pass
     * <code>
     * $client = new \sb\NTLM\Client($user, $pass);
     * $client->get('https://somesite.com');
     * </code>
     */
    public function __construct($user, $pass) {
        $this->user = $user;
        $this->pass = $pass;

        parent::__construct();
    }

    /**
     * Loads a response from an NTLM service
     * @param string $url The url to visit
     * @param string/array $post The post data to send
     * @param array $headers The headers to send
     * @param array $curl_opts Any additional curl_opts to send
     * @return string the response text
     * @throws \Exception
     */
    public function load($url, $data = null, $headers = null, $curl_opts = []) {

        if(!isset($curl_opts[CURLOPT_HTTP_VERSION])){
            $curl_opts[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_1;
        }
        
        if(!isset($curl_opts[CURLOPT_HTTPAUTH])){
            $curl_opts[CURLOPT_HTTPAUTH] = CURLAUTH_NTLM;
        }
        
        if(!isset($curl_opts[CURLOPT_USERPWD])){
            $curl_opts[CURLOPT_USERPWD] = $this->user . ':' . $this->pass;
        }
        
        return parent::load($url, $data, $headers, $curl_opts);
    }

    /**
     * Cleanup routine, used to delete cookies
     */
    public function __destruct() {
        parent::__destruct();
    }

}
