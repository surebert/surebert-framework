<?php
/**
 * Used to describe gitlab Users
 * @author paul.visco@gmail.com
 */
namespace sb\Gitlab;

class User {
    
    /**
     * The connection to gitlab
     * @var \sb\Gitlab\Client 
     */
    protected $_client;
    
    /**
     * The id of the user
     * @var int 
     */
    public $id;
    
    /**
     * The name of the user
     * @var string 
     */
    public $name;
    
    /**
     * The username of the user
     * @var string 
     */
    public $username;
    
    /**
     * The state of the user account
     * @var string 
     */
    public $state;
    
    /**
     * The avatar url if it exists
     * @var string 
     */
    public $avatar_url;
    
     /**
     * Loads a user from gitlab by id or email
     * @param string $data id or email
     * @param \sb\Gitlab\Client $client
     */
    public function __construct($data=null, $client=null) {

        if($client){
            $this->_client = $client;
            if (ctype_digit(strval($data))) {
                $this->id = $data;
                $user = $this->getById($data);
            } else {
                $user = $this->getByEmail($data);
            }

            if(!$user){
                throw new \Exception("user not found: ".$data);
            }

            foreach ($user as $k => $v) {
                $this->{$k} = $v;
            }
        }
        
        
    }
    
    /**
     * Loads a user by id
     * @param integer $id
     * @return \stdClass
     */
    protected function getById($id) {
        return $this->_client->get('/users/' . urlencode($id));
    }
    
    /**
     * Loads a user by email
     * @param string $email
     * @return \stdClass
     */
    protected function getByEmail($email) {
        $users = $this->_client->get('/users?search='.urlencode($email));
        return isset($users[0]) ? $users[0] : false;
    }
}
