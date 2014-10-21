<?php

/**
 * Used to describe gitlab Projects
 * @author paul.visco@gmail.com
 */

namespace sb\Gitlab;

class Project {

    /**
     * The connection to gitlab
     * @var \sb\Gitlab\Client 
     */
    protected $_client;

    /**
     * The id of the project
     * @var integer 
     */
    public $id;
    
    /**
     * The name of the repo
     * @var string 
     */
    public $name;
    
    /**
     * The name of the repo with namespace
     * @var string 
     */
    public $name_with_namespace;
    
    /**
     * The title of the project
     * @var string
     */
    public $title;
    
    /**
     * The description of the project
     * @var string
     */
    public $description;
    
    /**
     * The ssh url to the repo
     * @var string
     */
    public $ssh_url_to_repo;
    
    /**
     * The http url to the repo
     * @var type 
     */
    public $http_url_to_repo;
    
    /**
     * The web url to the repo
     * @var string
     */
    public $web_url;
    /**
     * THe owner of the repo
     * @var \stdClass
     */
    public $owner;
    
    /**
    * The path to the url
    * @var string
    */
    public $path;
    
    /**
     * The path of the url with namespace
     * @var string 
     */
    public $path_with_namespace;
    
    /**
     * The datetime the project was created at
     * @var string 
     */
    public $created_at;
    
    /**
     * The last activity datetime
     * @var string
     */
    public $last_activity_at;
    
    /**
     * The namespace of the project
     * @var string
     */
    public $namespace;
    
    /**
     * The default branch of the project
     * @var string 
     */
    public $default_branch;

    /**
     * Loads a project from gitlab by id or namespace:name
     * @param string $data id or project namespace:name
     * @param \sb\Gitlab\Client $client
     */
    public function __construct($data, $client) {

        $this->_client = $client;
        if (ctype_digit(strval($data))) {
            $this->id = $data;
            $project = $this->getById($data);
        } else {
            $project = $this->getByName($data);
        }

        if(!$project){
            throw new \Exception("Project not found: ".$data);
        }
        
        foreach ($project as $k => $v) {
            $this->{$k} = $v;
            switch($k){
                case 'owner':
                    if(!is_object($v)){
                        $this->{$k} = $v;
                        break;
                    }
                    $user = new \sb\Gitlab\User();
                    foreach(get_object_vars($v) as $uk=>$uv){
                        $user->{$uk} = $uv;
                    }
                    $this->{$k} = $user;
                    break;

                default:
                    $this->{$k} = $v;
            }
        }
        
    }
    
    public function getIssues($state='opened', $labels=[]){
        $get = [];
        if(in_array($state, ['opened', 'closed'])){
            $get['state'] = $state;
        }
        
        if($labels && is_array($labels)){
            $get['labels'] = implode(',', $labels);
        } else if(is_string($labels)){
            $get['labels'] = $labels;
        }
        
        $url = "/projects/".urlencode($this->id)."/issues?".($get ? http_build_query($get) :'');
        
        $response = $this->_client->get($url);
     
        if(!is_array($response)){
            throw new \Exception("Issues not found for project ".$this->id.": ".json_encode($response));
        }
        
        $issues = [];
        foreach($response as $obj){
            $issues[] = \sb\Gitlab\Issue::fromObject($obj);
        }
        return $issues;
    }
    
     /**
     * Adds an issue to a git repository
     * @param string $title
     * @param string $description
     * @param string $assignee_email
     * @return \Gitlab\Model\Issue
     * @throws \Exception
     */
    public function issueCreate($title, $description, $assignee_email, $labels='') {
        
        $post = [
            'id' => $this->id,
            'title' => $title,
            'description' => $description
        ];
        
        if($assignee_email){
            $user = new \sb\Gitlab\User($assignee_email, $this->_client);
            $post['assignee_id']= $user->id;
        }
        
       $response = $this->_client->get('/projects/'.$this->id.'/issues',$post);
       if($response){
           return \sb\Gitlab\Issue::fromObject($response);
       }
    }
    
    /**
     * Closes an issue by id within the project
     * @param int $issue_id
     * @return type
     */
    public function issueClose(\sb\Gitlab\Issue $issue){
        $response = $this->_client->get("/projects/".$this->id."/issues/".$issue->id, [
            'state_event' => 'close'
        ], 'PUT');
        
        if(is_object($response) && isset($response->message)){
             throw new \Exception("Could not close issue ".$issue->id." on project ".$this->id.": ".json_encode($response));
        }
        
        return $response;
    }
    
    /**
     * Closes an issue by id within the project
     * @param int $issue_id
     * @return type
     */
    public function issueReopen(\sb\Gitlab\Issue $issue){
        $response = $this->_client->get("/projects/".$this->id."/issues/".$issue->id, [
            'state_event' => 'reopen'
        ], 'PUT');
        
        if(is_object($response) && isset($response->message)){
             throw new \Exception("Could not close issue ".$issue->id." on project ".$this->id.": ".json_encode($response));
        }
        
        return $response;
    }
    
    /**
     * Loads project data by namespace:name
     * @param string $project_name namespace:name
     * @return \stdClass
     * @throws \Exception
     */
    protected function getByName($project_name) {

        if (!strstr($project_name, ":")) {
            throw (new \Exception("You must search for project with namespace: prefix"));
        }
        $parts = explode(':', $project_name);

        $projects = $this->_client->get('/projects/search/' . urlencode($parts[1]));

        $found_project = false;
        foreach ($projects as $project) {
            if (preg_match("~^" . $parts[0] . "$~", $project->namespace->name)) {
                return $project;
            }
        }

        if (!$found_project) {
            return false;
        }
    }

    /**
     * Loads a project by id
     * @param integer $id
     * @return \stdClass
     */
    protected function getById($id) {
        return $client = $this->_client->get('/projects/' . urlencode($id));
    }
    
    /**
     * Convert stdClass Object to actual \sb\Gitlab\Issue
     * @param \stdClass $object
     * @return \sb\Gitlab\Issue
     */
    public static function fromObject($object){
        $issue = new \sb\Gitlab\P();
        foreach(get_object_vars($object) as $k=>$v){
           
            switch($k){
                case 'assignee':
                case 'author':
                    if(!is_object($v)){
                        $issue->{$k} = $v;
                        break;
                    }
                    $user = new \sb\Gitlab\User();
                    foreach(get_object_vars($v) as $uk=>$uv){
                        $user->{$uk} = $uv;
                    }
                    $issue->{$k} = $user;
                    break;

                default:
                    $issue->{$k} = $v;
            }
                
        }
        
        return $issue;
    }

}
