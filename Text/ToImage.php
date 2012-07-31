<?php
/**
 * This class is used to convert text strings into true type font based png images
 * @author paul.visco@roswellpark.org
 * @package Text
 */
namespace sb;

class Text_ToImage{

    /**
     * The path to the ttf you want your use.  must be in true type format.
     *
     * @var string
     */
    public $font ='';
    
    /**
     * The text rotation angel of the text , 0 is the default and stands for normal horizontal left to righ text
     *
     * @var integer
     */
    public $rotation=0;
    
    /**
     * The image resource
     *
     * @var resource
     */
    private $image;
    
    /**
     * Used to instantiate a new TextToImage
     *
     * @param integer $width The total image width
     * @param integer $height  The total image height
     * @param string $background_color  The background color as an rgb comma delimited list, e.g. (255,255,0) is red
     * @param string $text_color The background color as an rgb comma delimited list, e.g. (255,255,0) is red
     *
     * <code>
     * error_reporting(E_ALL);
     * ini_set('Display_Errors', 'On');
     * $textImage = new \sb\Text_ToImage(800, 600, '255,0,0', '0,9,0');
     * $textImage->font = '../media/fonts/Eurostile-ExtendedTwo.ttf';
     * $textImage->rotation = 0;
     * $word = (isset($_GET['word'])) ? $_GET['word'] : 'hello world';
     * $im = $textImage->draw($word, 20, 10, 30);
     * header('Content-Type:image/gif');
     * imagegif($im);
     * </code>
    */
    public function __construct($width, $height, $background_color = '', $text_color = '')
    {
        $this->image = imagecreate($width, $height);
        
        
        $color = explode(',', $background_color);
        if(count($color) ==3){
            $background_color = imagecolorallocate($this->image, $color[0], $color[1], $color[2]);
        } else {
            $background_color = imagecolorallocate($this->image, 0, 0, 0);
        }
        
        imagefill($this->image, 0, 0, $background_color);
        
        $color = explode(',', $text_color);
        
        if(count($color) ==3){
            $this->text_color = imagecolorallocate($this->image, $color[0], $color[1], $color[2]);
        } else {
            $this->text_color = imagecolorallocate($this->image, 255, 255, 255);
        }
    }
    
    /**
     * Draws the text onto the image
     *
     * @param string $text  The text to write on the image
     * @param integer $size  The font size to use
     * @param integer $x The x position to start draiwng on the image
     * @param integer $y    The y position to start drawing on the image
     * @return resource A png image resource that can be used by imagepng, imagegif for output
     */
    public function draw($text, $size=12, $x=0, $y=0)
    {
        
        imagettftext($this->image, $size, $this->rotation, $x, $y, $this->text_color, $this->font, $text);
        
        return $this->image;
        
    }
    
}
