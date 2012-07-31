<?php
/**
 * Describe the most basic user in any sb framework system.  All users should extend this user
 * @author paul.visco@roswellpark.org
 * @package User
 *
 */
class User{
    
    /**
     * The last name of the person
     *
     * @var string
     */
    public $lname;
    
    /**
     * The first name of the person
     *
     * @var string
     */
    
    public $fname;

    /**
     * The sex of the person
     *
     * @var string m or f
     */
    public $sex;
    
    /**
     * The preferred display name of the person
     *
     * @var string
     */
    
    public $dname;
    
    /**
     * The username of the user
     *
     * @var string
     */
    public $uname;
    
    /**
     * The users id in terms of the application
     *
     * @var string
     */
    
    public $id;
    
    /**
     * The email address of the user
     *
     * @var string
     */
    public $email;
    
}
