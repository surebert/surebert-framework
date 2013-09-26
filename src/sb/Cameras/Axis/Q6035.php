<?php
/**
 * Used to interact with an Axis Q6035 camera
 * @author Visco, Paul <paulsidekick@gmail.com>
 */
namespace sb\Cameras\Axis;

class Q6035{
  
    /**
     * The URL of the camera e.g. http://10.1.23.34
     * @var string
     */
    protected $url;
    
    /**
     * The username for accessing the camera if needed
     * @var string
     */
    protected $uname;
    
    /**
     * The password for accessing the camera if needed
     * @var string
     */
    protected $pass;
    
    
    /**
     * Sets up the location and creds for cameras access
     * @param string $url The addr of the camera e.g. http://10.1.23.34
     * @param string $uname The username for accessing the camera if needed
     * @param string $pass The password for accessing the camera if needed
     * <code>
     * $camera = new \sb\Cameras\Axis\Q6035("http://10.23.4.5", "username", "passH");
     * $jpeg = $camera->takeSnapShot();
     * </code>
     */
    public function __construct($url, $uname='', $pass=''){
        $this->url = $url;
        $this->uname = $uname;
        $this->pass = $pass;
    }
    
    /**
     * Retrives a current snapshot
     * @return string jpeg data
     */
    public function takeSnapShot($user_agent='Surebert Kamera', $options=Array()){
        $ch = curl_init($this->url.'/jpg/1/image.jpg?timestamp='.time());
       
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
        curl_setopt($ch, CURLOPT_USERPWD, $this->uname.':'.$this->pass);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,2);
        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        //add any additional options passed
        foreach($options as $k=>$v){
            curl_setopt($ch, $k, $v);
        }
        return curl_exec($ch);
    }
    
    /**
     * Retrieves and stores a snapshot by date
     * @param type $dir
     * @return string Path if saved, empty string if not
     */
    public function saveSnapshot($dir){
        
        if(!is_dir($dir)){
            mkdir($dir, 0775, true);
        }
        
        $jpeg = $this->takeSnapShot();
        $file = $dir.'/'.date('Y_m_d_H_i_s').'.jpg';
        if(file_put_contents($file, $jpeg)){
            return $file;
        }
        
        return '';
    }
}
