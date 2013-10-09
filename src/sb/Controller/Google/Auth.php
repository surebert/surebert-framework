<?php

namespace sb\Controller\Google;

/**
 * Used to manage
 */
class Auth extends \sb\Controller\HTML\HTML5{
    
    /**
     * google auth config info fro json in google api consol
     * https://code.google.com/apis/console/
     * 
     * @var \stdClass 
     */
    public $config;
    
    /**
     * Used to communicate with google services
     * @var \Google_Client 
     */
    public $client;
    
    /**
     * Used to communcate with google oauth2 service
     * @var \Google_Oauth2Service
     */
    public $oauth2_service;
    
    /**
     * Your developer key from google API
     * @var type 
     */
    public $developer_key ='';
    
    /**
     * The JSON code from your google API console
     * https://code.google.com/apis/console/
     * 
     * @var string 
     */
    public $api_json ='';
    
    /**
     * Creates a OAuth client using the json data from
     * 
     * https://code.google.com/apis/console/
     * @param string $json_config
     */
    public function __construct(){
        if(empty($this->api_json)){
            throw(new \Exception("Please populate the \$this->api_json property of the controller with the json api info from https://code.google.com/apis/console"));
        }
        
        if(empty($this->developer_key)){
            throw(new \Exception("Please populate the \$this->developer_key property of the controller with your google developer key"));
        }
        $this->config = json_decode($this->api_json)->web;
        $this->getClient($this->developer_key);
    }
    
    /**
     * 
     * @return \Google_Client
     */
    public function getClient($developer_key=''){
        
        if(!$this->client){
            $this->client = new \Google_Client();
            // Visit https://code.google.com/apis/console?api=plus to generate your
            // oauth2_client_id, oauth2_client_secret, and to register your oauth2_redirect_uri.
            $this->client->setClientId($this->config->client_id);
            $this->client->setClientSecret($this->config->client_secret);
            $this->client->setRedirectUri($this->config->redirect_uris[0]);
            $this->client->setDeveloperKey($developer_key);
        }
        
        $this->oauth2_service = new \Google_Oauth2Service($this->client);
        return $this->client;
    }
    
    /**
     * Creates an auth URL to allow user to login
     * 
     * @return string
     */
    public function createAuthUrl(){
        
        $client = $this->getClient();
        return $client->createAuthUrl();
    }
    
    /**
     * Logs in the user
     * @return \stdClass
     * @servable true
     */
    public function login(){
        if ($this->getGet('code')) {
            $this->client->authenticate($this->getGet('code'));
            $this->setSession('token', $this->client->getAccessToken());

            $redirect = $this->config->redirect_uris[0];
            header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
            return;
        }


        $token = $this->getSession('token');
        if ($token) {
            $this->client->setAccessToken($token);
        }

        if ($this->client->getAccessToken()) {
            $user = $this->oauth2_service->userinfo->get();
            $this->setSession('token', $this->client->getAccessToken());
            return $user;
        }
    }
    
    /**
     * Logs the user out
     * @servable true
     */
    public function logout(){
        $this->unsetSession('token');
        $this->client->revokeToken();
        $this->sendRedirect("/");
        
    }
    
}
