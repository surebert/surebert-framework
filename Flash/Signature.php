<?php

/**
 * A secure signature object which holds the data for each signature
 *
 * @author: Paul Visco
 * @version 1.2 05/10/06 02/19/08
 * 
 * 
 * <code>
 * DROP TABLE IF EXISTS `storage`;
CREATE TABLE `storage` (
  `sid` int(11) NOT NULL auto_increment,
  `id` varchar(100) default NULL,
  `user_name` varchar(100) default NULL,
  `ip` varchar(15) default NULL,
  `time_stamp` datetime default NULL,
  `path` text,
  `width` smallint(5) unsigned default NULL,
  `height` smallint(5) unsigned default NULL,
  `app_id` smallint(5) unsigned default NULL,
  `deleted` char(1) default 0,
  PRIMARY KEY  (`sid`)
) 

//using signature
$db = new PDO("mysql:host=HOST;dbname=DBNAME", "USERNAME", "PASSWORD");

//saving a signature
$signature = new sb_Flash_Signature();
$signature->app_id = 999;
$signature->width=600;
$signature->height=150;
$signature->user_name = $_SERVER['AUTH_USER'];

//set the path for the Signature object
$signature->path = $path_data_from_flash_file

$signature->save($db);
echo 'transaction='.$signature->id;

//loading a signature
$signature = new sb_Flash_Signature($db, '08fb669424c5dcbf6e73a943df2bc2a8');

$signature->to_img('gif', null, 100, null, null, 2, Array(255,235,0), Array(255,255,255), null);
<//code>
 */
class sb_Flash_Signature{
	
	/**
	 * The logged in username of the signature creator
	 *
	 * @var string
	 */
	public $user_name;
	
	/**
	 * The unique transaction for this signature
	 *
	 * @var string
	 */
	public $id;
	
	/**
	 * The time and date when the signature was created
	 *
	 * @var string
	 */
	public $time_stamp;
	
	/**
	 * The IP address of the user that created the signature
	 *
	 * @var string
	 */
	public $ip;
	
	/**
	 * The id of the application that this signature was created with
	 *
	 * @var integer
	 */
	public $app_id;
	
	/**
	 * The array holding the signature drawing information
	 *
	 * @var string
	 */
	
	public $path;
	
	/**
	 * The width of the signature file in pixels
	 *
	 * @var int
	 */
	public $width;
	
	/**
	 * The height of the signature file in pixels
	 *
	 * @var int
	 */
	public $height;
	
	/**
	 * The temporary directory for images when being manipulated, they are deleted after use
	 *
	 * @var string
	 */
	public $cache = 'tmp';
	
	/**
	 * The thickness of the signature line
	 *
	 * @var int
	 */
	private $thickness = 2;
	
	/**
	 * The bg_color of the signature image
	 *
	 * @var array (r,b,g)
	 */
	private $bg_color = array( 255,255,255);
		
	/**
	 * The ink color for the signature
	 *
	 * @var array (r,b,g) ink color should be a solid green color for easy ink validation
	 */
	private $signature_color = array(0,0,0);
	
	
	/**
	 * The security data stamp color
	 *
	 * @var array (r,b,g)
	 */
	private $security_stamp_color = null;
	
	/**
	 * An instance of PDO used for saving and loading signatures from a database 
	 *
	 * @var PDO
	 */
	private $db;
	
	/**
	 * The image data of the signature itself
	 *
	 * @var binary
	 */
	private $image;
	
	/**
	 * The image data for creating images from imagegif, imagepng, imagejpeg
	 *
	 * @var blob
	 */
	private $data;
	
	/**
	 * Create a new signature
	 *
	 * @param string $ip The IP address that the signature was made from
	 * @param string $id The unqiue transaction id of the signature
	 
	 */
	public function __construct($id=null, $db=null){
		
		if(is_null($id) && empty($this->id)){
			//create the randomized unqiue sec id
			$this->id = md5(uniqid(rand()));
		} else if(is_string($id)){
			$this->id = $id;
			
		}
		
		if($db instanceof PDO){
			$this->db = $db;
			$this->load($id);
		}
		
		if(empty($this->ip)){
			//set the Signature IP addr to that of the user who created the signature
			$this->ip = $_SERVER['REMOTE_ADDR'];
		}
		
		if(empty($this->time_stamp)){
			$this->time_stamp=date('Y-m-d H:i:s');
		}
		
		return $this;
	}
	
	/**
	 * Draws a populated Signature object into a signature image
	 *
	 * @param string $format png, jpg, or gif
	 * 
	 *
	 * @param string $format The format to isplay jpeg, png, or gif
	 * @param string $file  If a filename is specified it exports the image to the file instead of displaying it
	 * @param integer $quality The quality of the jpeg output, default to 100
	 * * @param integer $width The width of the signature, specified in pixels
	 * @param integer $height The height of the signature, specified in pixels
	 * @param integer $thickness  The signature line thickness
	 * @param array $background_color The background color of the signatures specified as an Array(r,g,b) e.g. Array(255,0,45);
	 * @param array $signature_color The ink color of the signatures specified as an Array(r,g,b) e.g. Array(255,0,45);
	 * @param array $security_stamp_color The security stamp  color of the signatures specified as an Array(r,g,b) e.g. Array(255,0,45);
	 */
	public function to_img($format, $file=null, $quality=100, $width=null, $height=null, $thickness=null, $bg_color=null, $signature_color=null, $security_stamp_color=null){
		
		
		if(is_integer($thickness)){
			$this->thickness = $thickness;
		}
		
		if(is_array($bg_color) && isset($bg_color[0]) && is_numeric($bg_color[0]) && isset($bg_color[1]) && is_numeric($bg_color[1]) && isset($bg_color[2]) && is_numeric($bg_color[2])){
			$this->bg_color = $bg_color;
		}
		
		if(is_array($signature_color) && isset($signature_color[0]) && is_numeric($signature_color[0]) && isset($signature_color[1]) && is_numeric($signature_color[1]) && isset($signature_color[2]) && is_numeric($signature_color[2])){
			$this->signature_color = $signature_color;
		}
		
		
		if(is_array($security_stamp_color) && isset($security_stamp_color[0]) && is_numeric($security_stamp_color[0]) && isset($security_stamp_color[1]) && is_numeric($security_stamp_color[1]) && isset($security_stamp_color[2]) && is_numeric($security_stamp_color[2])){
			$this->security_stamp_color = $security_stamp_color;
		
		}
		
		//create a reference to the image pointer
		$this->data = imagecreate($this->width, $this->height);
		
		//create a reference to the image pointer
		$this->data = imagecreate($this->width, $this->height);
		
		imagesetthickness($this->data, $this->thickness);
		
		//set background color
		imagecolorallocate($this->data, $this->bg_color[0], $this->bg_color[1], $this->bg_color[2]);
		
		//set the security info color
		$this->security_info = imagecolorallocate($this->data, $this->security_stamp_color[0], $this->security_stamp_color[1], $this->security_stamp_color[2]);
		
		//set the ink color for the signature
		$this->ink = imagecolorallocate($this->data,$this->signature_color[0], $this->signature_color[1], $this->signature_color[2]);
		
		//create a new image with the data from this object
		$this->draw();
		
			
		if(!(is_null($width) && is_null($height))){
			$this->resize_image($width, $height);
		}
		
		//set the image 
		$imageformat = ($format =='jpg') ? 'imagejpeg' : 'image'.$format;
		
		if(is_null($file)){
			header("Content-Type: image/".$format);
		}
		
		$imageformat($this->data, $file, $quality);
	
	}
	
	/**
	 * Uses the path from the flash file to trace the signature into the image file for storage
	 *
	 * @param string $path a set of comma delimited x,y values of points that is plotted
	 */
	
	private function draw(){
		
		$i=0;

		$point = explode (",", $this->path);
	
		while($i< count ($point)){
			if(isset($point[$i+3]) && $point[$i] !='undefined'){
				$x1=$point[$i];
				$y1=$point[$i+1];
				$x2=$point[$i+2];
				$y2=$point[$i+3];
				
				if ($x2==0 || $y2==0){
					$x2=$x1;
					$y2=$y1;
			
				} else if ($x2 == -1 || $y2 == -1){
					$key = key ($point);
					$i=$i+2;
				} else {
					imageline($this->data,$x1,$y1,$x2,$y2,$this->ink);
				}
			}
			
			$i=$i+2;
		}
	
		if(!is_null($this->security_stamp_color)){
			
			//stamp the username and uniqueID
			$this->write($this->user_name.' '.$this->id, 10, 10);
			
			//stamp the time_stamp and IP
			$this->write($this->time_stamp.' '.$this->ip, 10, $this->height-30);
		}
		
	}
	
	/**
	 * Resize the image to specific dimesions if specified, you can specify either as proportional to the other by setting the argument to *
	 *
	 * @param int $new_width the desired width of the image, can be proportional to new_height if set to *
	 * @param int $new_height the desired height of the image, can be proportional to new_width if set to 
	 */
	private function resize_image($new_width ='*', $new_height='*'){
	
		
		//create proportial height or width if either is set to be proportional
		if ($new_width == '*'){
			
			$new_width = ($new_height * $this->width) /$this->height;
			
		} elseif ($new_height == '*') {
			
			$new_height = ($new_width * $this->height) / $this->width;
		}
		
		$new_image = imagecreatetruecolor ($new_width, $new_height);
		
		$resampled = imagecopyresampled($new_image, $this->data, 0, 0, 0, 0, $new_width, $new_height, $this->width, $this->height);
		
		$this->data = $new_image;	
		
	}
	
	/**
	 * Writes text onto an image
	 *
	 * @param string $text the text that is writtenonto the image
	 * @param int $x the x position of the text on the image
	 * @param int $y the y position of the text on the image
	 * @param array $color the color of the text on the image expressed as an array(r,g,b);
	 */
	private function write($text, $x, $y, $color=''){
	
		if(is_array($color) && count($color) == 3){
			$font_color = imagecolorallocate($this->data, $color[0], $color[1], $color[2]);
		} else {
			$font_color = $this->security_info;
		}

		imagestring($this->data, 5, $x, $y, $text, $font_color);
	}
	
	/**
	 * A PDO database connection
	 *
	 * @param PDO $db
	 */
	public function save($db=null){
		
		if($db instanceof PDO){
			$this->db = $db;
		}
		
		if(!$this->db instanceof PDO){
			throw new Exception("The signatures's database property must be an instance of PDO");
			return null;
		}
		
		$sql = "INSERT INTO storage (id, user_name, ip, time_stamp, path, width, height, app_id, deleted) VALUES (:id, :user_name, :ip, :time_stamp, :path, :width, :height, :app_id, 0)";
		
		$stmt = $this->db->prepare($sql);
		
		$insert = $stmt->execute(Array(
			':id' => $this->id,
			':user_name' => $this->user_name,
			':ip' => $this->ip,
			':time_stamp' => $this->time_stamp,
			':path' => $this->path,
			':width' => $this->width,
			':height' => $this->height,
			':app_id' => $this->app_id
			
		));
		
		if(!$insert){
			throw new Exception('Could  not insert signature into the database');
		}
	}
	
	/**
	 * Display a signature as png as referenced by transaction id
	 *
	 * @param integer $id The signature transaction_id
	 */
	public function load($db=null){
		
		if($db instanceof PDO){
			$this->db = $db;
		}
		
		if(!$this->db instanceof PDO){
			throw new Exception("The signatures's database property must be an instance of PDO");
			return null;
		}
		
		if(!is_string($this->id)){
			throw new Exception("The id of the signature must be a string");
			return null;
		}
		
		$sql = "SELECT id, user_name, ip, time_stamp, path, width, height FROM storage WHERE id=:id";
		
		$stmt = $this->db->prepare($sql);
	
		$stmt->execute(Array(":id" => $this->id));
		$rows = $stmt->fetchAll(PDO::FETCH_CLASS, 'sb_Flash_Signature');
		foreach($rows[0] as $prop=>$val){
			$this->{$prop} = $val;
		}
		return $this;
	
	}
	
	/**
	 * Destroys the reference to the image pointer
	 *
	 */
	public function __destruct(){
		if(isset($this->image)){
			imagedestroy($this->image);
		} 
	}
	
}

?>