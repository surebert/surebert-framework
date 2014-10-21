<?php
/**
 * Used to describe gitlab issues
 * @author paul.visco@gmail.com
 */
namespace sb\Gitlab;

class Issue {
    
  
    /**
     * The global id of the issue
     * @var int 
     */
    public $id;
    
    /**
     * The internal id of the issue within the project
     * @var int 
     */
    public $iid;
    
    /**
     * The project id the issue is associated with
     * @var int 
     */
    public $project_id;
    
    /**
     * The title of the issue
     * @var string 
     */
    public $title;
    
    /**
     * A description of the issue
     * @var string 
     */
    public $description;
    
    /**
     * The state of the issue opened or closed
     * @var string
     */
    public $state;
    
    /**
     * The datetime the issue was created at
     * @var string
     */
    public $created_at;
    
    /**
     * The datetime the issues was last update
     * @var string
     */
    public $updated_at;
    /**
     * The labels applied to the issue
     * @var array
     */
    public $labels;
    
    /**
     * The milestone the issue is assigned to
     * @var strings 
     */
    public $milestone;
    
    /**
     * The person the issue is assigned to
     * @var \sb\Gitlab\User 
     */
    public $assignee;
    
    /**
     * The author of the issue
     * @var \sb\Gitlab\User 
     */
    public $author;
    
    /**
     * Convert stdClass Object to actual \sb\Gitlab\Issue
     * @param \stdClass $object
     * @return \sb\Gitlab\Issue
     */
    public static function fromObject($object){
        $issue = new \sb\Gitlab\Issue();
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