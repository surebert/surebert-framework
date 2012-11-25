<?php

/**
 * This class is used to edit images.
 * 
 * @author paul.visco@roswellpark.org 10/26/2005
 * @package Image
 * 
 *
 */
namespace sb;

class Image{
    /**
     * The file path to the original image file being edited
     *
     * @var string
     */
    public $path;
    
    /**
     * A resource pointer to the edited image resource
     *
     * @var resource
     */
    
    public $edited; //a pointer to the edited image
    /**
     * A resource pointer to the original image resource
     *
     * @var resource
     */
    
    public $original; // a pointer to the original image
    
    /**
     * An array of height data both original and dest
     *
     * @var array
     */
    
    private $height = array(
        'orig' =>0,
        'dest' =>0
    );
    
    /**
     * An array of width data both original and dest
     *
     * @var array
     */
    
    private $width = array(
        'orig' =>0,
        'dest' =>0
    );
    
    /**
     * The type of image file that is being manipulated as determined by $this->get_info
     *
     * @var string
     */
    
    private $type; //gif, jpg, png

    /**
     * optionally sets the image by passing arguments to $this->set
     *
     * @param string $orig the file path to the image being edited
     * @param string $dest optional, the file path to name the edited file should be saved as, without this the original file gets saved over with the edited version
     * <code>
     * $sb_Image = new \sb\Image('orig.jpg', 'orig3.jpg');
     *
     * //$sb_Image->to_grayscale();
     * $sb_Image->resize(200, -1);
     * $sb_Image->display();
     * $sb_Image->force_download();
     * //$sb_Image->rotate(90);
     * //$sb_Image->toJPG();
     *</code>
     */
    public function __construct($orig='', $dest='')
    {
        if(!empty($orig)){
            $this->set($orig, $dest='');
        }
        
    }
    
    /**
     * Sets the image being edited 
     *
     * @param string $orig the file path to the image being edited
     * @param string $dest optional, the file path to name the edited file should be saved as, without this the original file gets saved over with the edited version
     */
    public function set($orig, $dest='')
    {
        if(!empty($dest)){
            copy($orig, $dest);
            $orig = $dest;
        }
        
        $this->path = $orig;
        $this->getInfo();    
        
    }
    
    /**
     * Gets the image file type, width, and height
     *
     */
    public function getInfo()
    {
    
        $file_info = @getimagesize($this->path);
        
        //define the original width of the image
        $this->width['orig'] = $file_info['0']; 
        
        //define the original height of the image
        $this->height['orig'] = $file_info['1']; 
        
        //image type //1 = GIF, 2 = JPG, 3 = PNG, 4 = SWF, 5 = PSD, 6 = BMP, 7 = TIFF(intel byte order), 8 = TIFF(motorola byte order), 9 = JPC, 10 = JP2, 11 = JPX, 12 = JB2, 13 = SWC, 14 = IFF, 15 = WBMP, 16 = XBM
        
        switch ($file_info[2])
    
    {
            case "1":
                $this->type = "gif";
            break;
            
            case "2":
                $this->type = "jpg";
            break;
            
            case "3":
                $this->type = "png";
            break;
        }
        //////////////////////////
        $this->original = imagecreatefromstring(file_get_contents($this->path));
    
    }
    
    public function copy($destination)
    {
        copy($this->path, $destination);
    }
    
    /**
     * Dump the image data to the screen
     *
     */
    public function debug()
    {
        //echo dimensions
        echo '<pre>'.print_r($this, 1).'</pre>';
    }
    
    /**
     * Resizes an the edited image to the specified width and height
     *
     * @param int $width can be * to make it relative to a specified height
     * @param int $height can be * to make it relative to a specified width
     */
    public function resize($width, $height)
    {
        //if the width is not specified, make it relative to the height
        if($width == -1){
            
            $this->width['dest'] = ($height * $this->width['orig']) / $this->height['orig'];
            $this->height['dest'] = $height;
        
        //if the height is not specified, make it relative to the width
        } elseif ($height == -1){
            
            $this->width['dest'] = $width;
            $this->height['dest'] = ($width * $this->height['orig']) / $this->width['orig'];
            
        } else {
            
            $this->width['dest'] = $width;
            $this->height['dest'] = $height;
        }
        
        //set resize code depending on the type of image it is
        switch ($this->type)
    
    {
            case "gif":
                $this->edited = imagecreate($this->width['dest'], $this->height['dest']);
        
                imagecopyresampled($this->edited, $this->original, 0, 0, 0, 0, $this->width['dest'], $this->height['dest'], $this->width['orig'], $this->height['orig']);
            
            break;
            
            case "jpg":
                $this->edited = imagecreatetruecolor($this->width['dest'], $this->height['dest']);
    
                imagecopyresampled($this->edited, $this->original, 0, 0, 0, 0, $this->width['dest'], $this->height['dest'], $this->width['orig'], $this->height['orig']);
                
                
            break;
        
            case "png":
                
                $this->edited = imagecreatetruecolor ($this->width['dest'], $this->height['dest']);
                    
                //preserve the alpha if exists
                imagealphablending($this->edited, false);
                imagesavealpha($this->edited, true);    
            
                imagecopyresampled($this->edited, $this->original, 0, 0, 0, 0, $this->width['dest'], $this->height['dest'], $this->width['orig'], $this->height['orig']);
                
            break;
        }
        
    }
    
    /**
     * Converts the image being edited to grayscale
     *
     */
    public function to_grayscale()
    {
        $this->getInfo();
        
        $this->edited = imagecreate($this->width['orig'], $this->height['orig']);
        
        //Creates the 256 color palette
        for ($c=0;$c<256;$c++)
    
    {
            $palette[$c] = imagecolorallocate($this->edited,$c,$c,$c);
        }
        
        //Reads the origonal colors pixel by pixel
        for ($y=0;$y<$this->height['orig'];$y++)
    
    {
            for ($x=0;$x<$this->width['orig'];$x++)
        
    {
                $rgb = imagecolorat($this->original,$x,$y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                
                //This is where we actually use yiq to modify our rbg values, and then convert them to our grayscale palette
                $gs = $this->color_to_gray($r,$g,$b);
                imagesetpixel($this->edited,$x,$y,$palette[$gs]);
            }
        } 
    
    }
    
    /**
     * Converts a color to grayscale
     *
     * @param int $r 0-255
     * @param int $g 0-255
     * @param int $b 0-255
     * @return float grayscale color
     */
    private function color_to_gray($r,$g,$b)
    {
        return (($r*0.299)+($g*0.587)+($b*0.114));
    }
    
    /**
     * Rotates an image the number of degrees specified by $rotation
     *
     * @param int $rotation
     */
    public function rotate($rotation)
    {
        if(isset($this->edited)){
            $this->getInfo();
            $this->edited =  imagerotate($this->edited, $rotation, 0);
        } else {
            $this->edited =  imagerotate($this->original, $rotation, 0);
        }
        
    }
    
    /**
     * Writes text onto an image
     *
     * @param string $text the text that is writtenonto the image
     * @param int $x the x position of the text on the image
     * @param int $y the y position of the text on the image
     * @param array $color the color of the text on the image expressed as an array(r,g,b);
     */
    /**
     * Write text onto an image
     *
     * @param string $text
     * @param array $params color array(r,g,b), size, x, y
     */
    public function write($text, $params=array())
    {
        $color = (isset($params['color'])) ?  $params['color'] : array(0, 0, 0);
        $color = imagecolorallocate($this->edited, $color[0], $color[1], $color[2]);
        $size = (isset($params['size']))? $params['size'] : 5;
        $x = (isset($params['x']))? $params['x'] : 2;
        $y = (isset($params['y']))? $params['y'] : 2;
        
    
        imagestring($this->edited, $size, $x, $y, $text, $color);
    }
    
    /**
     * Adds a datestamp to the picture
     *
     */
    public function dateStamp()
    {
        $this->write(date('m/d/y H:i'), array('size'=>3, 'x'=>2, 'y'=>2, 'color'=>array(0,255,0)));
        $this->write(date('m/d/y H:i'), array('size'=>3, 'x'=>3, 'y'=>3, 'color'=>array(0,0,0)));
    }
    
    /**
     * Saves the image file being edited as a gif
     *
     */
    public function toGif()
    {
    
        imagegif($this->edited, $this->path);
    }
    
    /**
     * Saves the image file being edited as a jpg
     *
     */
    public function toJpg()
    {
        
        imagejpeg($this->edited, $this->path, 96);
    }
    
    /**
     * Saves the image file being edited as a png
     *
     */
    public function toPng()
    {
        
    
        imagepng($this->edited, $this->path, 1);
    }
    
    /**
     * Saves the edited image as a file based on the original images file type
     *
     */
    public function toFile()
    {
    
            
        if ($this->type == "jpg")
    
    {
            $this->toJpg();
            
        } elseif ($this->type == "png") {
        
            $this->toPng();
            
        } elseif ($this->type == "gif") {
        
            $this->toGif();
        }
        
    }
    
    /**
     * Displays the edited image to screen as a dynamic image file
     *
     */
    public function display()
    {
        if(isset($this->edited )){
            $image = $this->edited;
        } else {
            $image = $this->original;
        }
        
        if ($this->type == "jpg")
    
    {
            header("Content-type: image/jpeg");
            imagejpeg($image);
            
        } elseif ($this->type == "png") {
        
            header("Content-type: image/png");
            imagepng($image);
            
        } elseif ($this->type == "gif") {
        
            header("Content-type: image/gif");
            imagegif($image);
        }
    }
    
    /**
     * Forces the image being manipulated to the user as a force download
     *
     */
    public function force_download()
    {
    
            
        if ($this->type == "jpg")
    
    {
            $this->toJpg();
            
        } elseif ($this->type == "png") {
        
            $this->toPng();
            
        } elseif ($this->type == "gif") {
        
            $this->toGif();
        }
        
        header('Content-Description: File Transfer');
        header("Content-Type: application/octet-stream");
        header('Content-Length: ' . filesize($this->path));
        header('Content-Disposition: attachment; filename="' . basename($this->path.'"'));
        readfile($this->path);
        
        //remove the temp file
        unlink($this->path);
        
    }
    
    /**
     * Cleans up the image resources if they exist
     *
     */
    public function __destruct()
    {
        if(isset($this->original) || isset($this->edited)){
            imagedestroy($this->original);
            imagedestroy($this->edited);
        }
    }
    
}

