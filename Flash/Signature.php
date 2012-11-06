<?php

/**
 * A secure Flash_Signature object which holds the data for each Flash_Signature
 *
 * @author paul.visco@roswellpark.org
 * @package Flash
 * 
 * <code>
 * DROP TABLE IF EXISTS `storage`;
 * CREATE TABLE `storage` (
 *   `sid` int UNSIGNED NOT NULL auto_increment,
 *   `id` char(32) default NULL,
 *   `user_name` varchar(30) default NULL,
 *   `ip` INT UNSIGNED default NULL,
 *   `time_stamp` datetime default NULL,
 *   `path` text,
 *   `width` smallint(5) unsigned default NULL,
 *   `height` smallint(5) unsigned default NULL,
 *   `app_id` smallint(5) unsigned default NULL,
 *   `deleted` char(1) default 0,
 *   PRIMARY KEY  (`sid`)
 * )
 * 
 * //using Flash_Signature
 * $db = new PDO("mysql:host=HOST;dbname=DBNAME", "USERNAME", "PASSWORD");
 * 
 * //saving a \sb\Flash\Signature
 * $Flash_Signature = new \sb\Flash\Signature();
 * $Flash_Signature->app_id = 999;
 * $Flash_Signature->width=600;
 * $Flash_Signature->height=150;
 * $Flash_Signature->user_name = $_SERVER['AUTH_USER'];
 * 
 * //set the path for the Signature object
 * $Flash_Signature->path = $path_data_from_flash_file
 * 
 * $Flash_Signature->save($db);
 * echo 'transaction='.$Flash_Signature->id;
 * 
 * //loading a Flash_Signature
 * $Flash_Signature = new \sb\Flash_Signature($db, '08fb669424c5dcbf6e73a943df2bc2a8');
 * 
 * $Flash_Signature->toImg('gif', null, 100, null, null, 2, Array(255,235,0), Array(255,255,255), null);
 * </code>
 */
namespace sb\Flash;

class Signature
{

    /**
     * The logged in username of the Flash_Signature creator
     *
     * @var string
     */
    public $user_name;

    /**
     * The unique transaction for this Flash_Signature
     *
     * @var string
     */
    public $id;

    /**
     * The time and date when the Flash_Signature was created
     *
     * @var string
     */
    public $time_stamp;

    /**
     * The IP address of the user that created the Flash_Signature
     *
     * @var string
     */
    public $ip;

    /**
     * The id of the application that this Flash_Signature was created with
     *
     * @var integer
     */
    public $app_id;

    /**
     * The array holding the Flash_Signature drawing information
     *
     * @var string
     */
    public $path;

    /**
     * The width of the Flash_Signature file in pixels
     *
     * @var int
     */
    public $width;

    /**
     * The height of the Flash_Signature file in pixels
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
     * Writes the ID on the Flash_Signature if set to true
     * @var boolean
     */
    public $stamp_id = false;

    /**
     * The thickness of the Flash_Signature line
     *
     * @var int
     */
    private $thickness = 2;

    /**
     * The bg_color of the Flash_Signature image
     *
     * @var array (r,b,g)
     */
    private $bg_color = array(255, 255, 255);

    /**
     * The ink color for the Flash_Signature
     *
     * @var array (r,b,g) ink color should be a solid green color for easy ink validation
     */
    private $Flash_Signature_color = array(0, 0, 0);

    /**
     * The security data stamp color
     *
     * @var array (r,b,g)
     */
    private $security_stamp_color = null;

    /**
     * An instance of PDO used for saving and loading Flash_Signatures from a database 
     *
     * @var PDO
     */
    private $db;

    /**
     * The image data of the Flash_Signature itself
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
     * Create a new Flash_Signature
     *
     * @param string $ip The IP address that the Flash_Signature was made from
     * @param string $id The unqiue transaction id of the Flash_Signature

     */
    public function __construct($id = null, $db = null)
    {

        if (\is_null($id) && empty($this->id)) {
            //create the randomized unqiue sec id
            $this->id = \md5(uniqid(rand()));
        } elseif (\is_string($id)) {
            $this->id = $id;
        }

        if ($db instanceof PDO) {
            $this->db = $db;
            $this->load($id);
        }

        if (empty($this->ip)) {
            $this->ip = \sb\Gateway::$remote_addr;
        }

        if (empty($this->time_stamp)) {
            $this->time_stamp = date('Y-m-d H:i:s');
        }

        return $this;
    }

    /**
     * Draws a populated Flash_Signature object into a Flash_Signature image
     *
     * @param string $format png, jpg, or gif
     * 
     *
     * @param string $format The format to isplay jpeg, png, or gif
     * @param string $file  If a filename is specified it exports 
     * the image to the file instead of displaying it
     * @param integer $quality The quality of the jpeg output, 
     * default to 100
     * * @param integer $width The width of the Flash_Signature, 
     * specified in pixels
     * @param integer $height The height of the Flash_Signature, 
     * specified in pixels
     * @param integer $thickness  The Flash_Signature line thickness
     * @param array $background_color The background color of the 
     * Flash_Signatures specified as an Array(r,g,b) e.g. Array(255,0,45);
     * @param array $Flash_Signature_color The ink color of the 
     * Flash_Signatures specified as an Array(r,g,b) e.g. Array(255,0,45);
     * @param array $security_stamp_color The security stamp  
     * color of the Flash_Signatures specified as an Array(r,g,b) 
     * e.g. Array(255,0,45);
     */
    public function toImg($format, $file = null, $quality = 100, $width = null, $height = null, $thickness = null, $bg_color = null, $Flash_Signature_color = null, $security_stamp_color = null)
    {


        if (\is_integer($thickness)) {
            $this->thickness = $thickness;
        }

        if (\is_array($bg_color) && isset($bg_color[0]) && \is_numeric($bg_color[0]) && isset($bg_color[1]) && \is_numeric($bg_color[1]) && isset($bg_color[2]) && \is_numeric($bg_color[2])) {
            $this->bg_color = $bg_color;
        }

        if (\is_array($Flash_Signature_color) && isset($Flash_Signature_color[0]) && \is_numeric($Flash_Signature_color[0]) && isset($Flash_Signature_color[1]) && \is_numeric($Flash_Signature_color[1]) && isset($Flash_Signature_color[2]) && \is_numeric($Flash_Signature_color[2])) {
            $this->Flash_Signature_color = $Flash_Signature_color;
        }


        if (\is_array($security_stamp_color) && isset($security_stamp_color[0]) && \is_numeric($security_stamp_color[0]) && isset($security_stamp_color[1]) && \is_numeric($security_stamp_color[1]) && isset($security_stamp_color[2]) && \is_numeric($security_stamp_color[2])) {
            $this->security_stamp_color = $security_stamp_color;
        }

        //create a reference to the image pointer
        $this->data = \imagecreate($this->width, $this->height);

        //create a reference to the image pointer
        $this->data = \imagecreate($this->width, $this->height);

        \imagesetthickness($this->data, $this->thickness);

        //set background color
        \imagecolorallocate($this->data, $this->bg_color[0], $this->bg_color[1], $this->bg_color[2]);

        //set the security info color
        $this->security_info = \imagecolorallocate($this->data, $this->security_stamp_color[0], $this->security_stamp_color[1], $this->security_stamp_color[2]);

        //set the ink color for the Flash_Signature
        $this->ink = \imagecolorallocate($this->data, $this->Flash_Signature_color[0], $this->Flash_Signature_color[1], $this->Flash_Signature_color[2]);

        //create a new image with the data from this object
        $this->draw();

        if (!(\is_null($width) && \is_null($height))) {
            $this->resizeImage($width, $height);
        }

        $imageformat = ($format == 'jpg') ? 'imagejpeg' : 'image' . $format;

        if (\is_null($file)) {
            header("Content-Type: image/" . $format);
        }

        $imageformat($this->data, $file, $quality);
    }

    /**
     * Uses the path from the flash file to trace the Flash_Signature into the image file for storage
     *
     * @param string $path a set of comma delimited x,y values of points that is plotted
     */
    private function draw()
    {

        $i = 0;

        $point = \explode(",", $this->path);

        while ($i < \count($point)) {
            if (isset($point[$i + 3]) && $point[$i] != 'undefined') {
                $x1 = $point[$i];
                $y1 = $point[$i + 1];
                $x2 = $point[$i + 2];
                $y2 = $point[$i + 3];

                if ($x2 == 0 || $y2 == 0) {
                    $x2 = $x1;
                    $y2 = $y1;
                } elseif ($x2 == -1 || $y2 == -1) {
                    $key = key($point);
                    $i = $i + 2;
                } else {
                    imageline($this->data, $x1, $y1, $x2, $y2, $this->ink);
                }
            }

            $i = $i + 2;
        }

        if ($this->stamp_id) {
            $this->write($this->user_name, 10, 10);

            //stamp the time_stamp and IP
            $this->write($this->time_stamp . ' ' . $this->ip, 10, $this->height - 30);
        }
    }

    /**
     * Resize the image to specific dimesions if specified, you can specify either as proportional to the other by setting the argument to *
     *
     * @param int $new_width the desired width of the image, can be proportional to new_height if set to *
     * @param int $new_height the desired height of the image, can be proportional to new_width if set to 
     */
    private function resizeImage($new_width = '*', $new_height = '*')
    {


        //create proportial height or width if either is set to be proportional
        if ($new_width == '*') {

            $new_width = ($new_height * $this->width) / $this->height;
        } elseif ($new_height == '*') {

            $new_height = ($new_width * $this->height) / $this->width;
        }

        $new_image = \imagecreatetruecolor($new_width, $new_height);

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
    private function write($text, $x, $y, $color = '')
    {

        if (\is_array($color) && \count($color) == 3) {
            $font_color = \imagecolorallocate($this->data, $color[0], $color[1], $color[2]);
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
    public function save($db = null)
    {

        if ($db instanceof PDO) {
            $this->db = $db;
        }

        if (!$this->db instanceof PDO) {
            throw new \Exception("The Flash_Signatures's database property must be an instance of PDO");
            return null;
        }

        $sql = "INSERT INTO storage (id, user_name, ip, time_stamp, path, width, height, app_id, deleted) VALUES (:id, :user_name, INET_ATON(:ip), :time_stamp, :path, :width, :height, :app_id, 0)";

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

        if (!$insert) {
            throw new \Exception('Could  not insert Flash_Signature into the database');
        }
    }

    /**
     * Display a Flash_Signature as png as referenced by transaction id
     *
     * @param integer $id The Flash_Signature transaction_id
     */
    public function load($db = null)
    {

        if ($db instanceof PDO) {
            $this->db = $db;
        }

        if (!$this->db instanceof PDO) {
            throw new \Exception("The Flash_Signatures's database property must be an instance of PDO");
            return null;
        }

        if (!is_string($this->id)) {
            throw new \Exception("The id of the Flash_Signature must be a string");
            return null;
        }

        $sql = "SELECT id, user_name, INET_NTOA(ip) AS ip, time_stamp, path, width, height FROM storage WHERE id=:id";

        $stmt = $this->db->prepare($sql);

        $stmt->execute(Array(":id" => $this->id));
        $rows = $stmt->fetchAll(PDO::FETCH_CLASS, '\sb\Flash_Signature');
        foreach ($rows[0] as $prop => $val) {
            $this->{$prop} = $val;
        }
        return $this;
    }

    /**
     * Destroys the reference to the image pointer
     *
     */
    public function __destruct()
    {
        if (isset($this->image)) {
            imagedestroy($this->image);
        }
    }
}

