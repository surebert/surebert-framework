<?php

/**
 * Used to commicate with NTLM web services
 * @author paulsidekick@gmail.com
 */

namespace sb\CURL;

class Client {

    /**
     * Where cookies are stored for request /dev/null means memory
     * @var string 
     */
    protected $cookies = 'cookies.txt';

    /**
     * Should curl output verbose
     * @var boolean 
     */
    public $verbose = false;

    /**
     * The user agent to use with the request
     * @var string 
     */
    public $agent = 'sb_CURL_Client';

    /**
     * Loads web content from a URL
     * <code>
     * $client = new \sb\CURL\Client();
     * $client->get('http://google.com');
     * </code>
     */
    public function __construct() {

        //creates cookie file to handle auth
        $dir = ROOT . '/private/cache/curl_client';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $this->cookies = tempnam($dir, 'cookies');
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
        
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookies);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookies);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if($headers){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if ($data) {
            curl_setopt($ch, CURLOPT_POST, true);
            if(is_array($data)){
                $data = http_build_query($data);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        if ($this->verbose) {
            curl_setopt($ch, CURLOPT_VERBOSE, 2);
        }
        
        foreach ($curl_opts as $opt => $val) {
            curl_setopt($ch, $opt, $val);
        }

        $response = curl_exec($ch);

        $i = curl_getinfo($ch);
        if ($i['http_code'] != 200) {
            throw(new \Exception("ERROR READING URL:\n" . curl_error($ch) . print_r($i, 1)));
        }

        return $response;
    }

    /**
     * GETs data from the server
     * @param string $url The URL to grab
     * @param array $headers additional HTTP options
     * @param array $curl_opts additional CURL options
     * @return string
     * @throws \Exception
     */
    public function get($url, $headers = [], $curl_opts = []) {
        return $this->load($url, [], $headers, $curl_opts);
    }

    /**
     * GETs data from the server
     * @param string $url The URL to grab
     * @param array $data The data to pass
     * @param array $headers additional HTTP options
     * @param array $curl_opts additional CURL options
     * @return string
     * @throws \Exception
     */
    public function post($url, $data = [], $headers = [], $curl_opts = []) {
        return $this->load($url, $data, $headers, $curl_opts);
    }

    /**
     * PUTs data to the server
     * @param string $url The URL to grab
     * @param array $data The data to pass
     * @param array $headers additional HTTP options
     * @param array $curl_opts additional CURL options
     * @return string
     * @throws \Exception
     */
    public function put($url, $data = [], $headers = [], $curl_opts = []) {
        $curl_opts[CURLOPT_CUSTOMREQUEST] = 'PUT';
        return $this->load($url, $data, $headers, $curl_opts);
    }

    /**
     * DELETEs data from the server
     * @param string $url The URL to grab
     * @param array $data The data to pass
     * @param array $headers additional HTTP options
     * @param array $curl_opts additional CURL options
     * @return string
     * @throws \Exception
     */
    public function delete($url, $data = [], $headers = [], $curl_opts = []) {
        $curl_opts[CURLOPT_CUSTOMREQUEST] = 'DELETE';
        return $this->load($url, $data, $headers, $curl_opts);
    }

    /**
     * Cleanup routine, used to delete cookies
     */
    public function __destruct() {
        unlink($this->cookies);
    }

}
