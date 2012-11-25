<?php
/**
 * Used to create captcha images for form submission
 *
 * If you want the php code to run from a png
 * use the following in your htaccess file for that directory: 
 * AddType application/x-httpd-php .png
 *
 * @author paul.visco@roswellpark.org
 * @package Web
 * 
 */
namespace sb\Web;

class Captcha{
    /**
     * The characters allowed in the captcha word
     *
     * @var string
     */
    public $allowed_characters='abcdefghijklmnopqurstuvwxyz';
    
    /**
     * The path to the truetype font
     *
     * @var string
     */
    public $font ='';
    
    /**
     * The currently selected random word
     *
     * @var string
     */
    public $word;
    
    /**
     * The png captcha image for use with imagepng, etc to display
     *
     * @var resource
     */
    public $image;
    
    /**
     * CReates a captcha image
     *
     *  <code>
     * //start the session
     * session_start();
     *
     * //You can pass it an optional 200px x 200px background image as a path to the first argument, you can also pass a text color as a 3 integer rgb array e.g. Array(255,255,45) as teh second argument
     * $cap = new \sb\Web\Captcha();
     *
     * //set the ttf font you want to use.  Otherwise uses default font which is ugly and not as scalable
     * $cap->font = '../media/fonts/cherokee.ttf';
     * $cap->allowed_characters = 'ascdgi';
     *
     * //optional filters argument Array(IMG_FILTER_EMBOSS)
     * $png = $cap->draw();
     *
     * //add background interference to make it more difficult for the computer to guess the word
     * $cap->addInterference();
     * 
     * //set teh session with the word printed in the image, you then check when the user entered against this session variable on your form submission processing page
     * $_SESSION['sb_Web_Captcha']= $cap->word;
     *
     * //set the content type to display a png
     * header("Content-Type: image/png");
     *
     * //display the png
     * imagepng($png);
     *
     *  //destroy the png
     * imagedestroy($png);
     * </code>
     * @param string $background The path to the background 8 bit png, make sure it is 128 color or less.  If left out, the image has a black background.
     */
    public function __construct($background='', $color='')
    {
        
        if(file_exists($background)){
            $this->image = imagecreatefrompng($background);
            
        } elseif(!empty($this->clouds)){
            $this->image = imagecreatefromstring(base64_decode($this->clouds));
        } else {
            $this->image = imagecreate(200, 200);
            imagefill($this->image, 0, 0, imagecolorallocate($this->image, 0, 0, 0));
        }
        
        if(is_array($color) && count($color) ==3){
            $this->ink_color = imagecolorallocate($this->image, $color[0], $color[1], $color[2]);
        } else {
            $this->ink_color = imagecolorallocate($this->image, 210, 210, 210);
        }
        
        imagestring($this->image, 3, 5, 2, 'surebert.com', imagecolorallocate($this->image, 255, 100, 0));
        
    }
    
    /**
     * Creates the word from the allowed characters
     *
     * @return string
     */
    private function createWord()
    {
        
        $this->word ='';
        for($x=0;$x<rand(5,7);$x++){
    
            $this->word .=$this->allowed_characters[ rand(0, strlen($this->allowed_characters)-1)];
        }
        return $this->word;
    }
    
    /**
     * Adds interference as circles int he backgroun the make it more difficult to parse.  This is optional
     *
     */
    public function addInterference()
    {
        
        for($i=0;$i<20;$i++){
            $radius =  rand(0,150);
            imageellipse($this->image, rand(0,200), rand(0,200), $radius, $radius, $this->ink_color);
        }
    }
    
    /**
     * Draws the wor text on the image using the true type font specified.  If no font is specified than
     *
     * @param arary $filters AN array of GD filter constants to run on the image
     * @return resource THe image is returned and can be used with imagepng to display or export to file
     */
    public function draw($filters=Array())
    {
        
        $word = $this->createWord();
    
        for($j=0;$j<5;$j++){
            
            
            if(file_exists($this->font)){
                imagettftext($this->image, rand(8,18), rand(0,30), rand(0,200), rand(0,200), $this->ink_color, $this->font, 'not the code');
            } else {
                imagestring($this->image, rand(1,3), rand(0,200), rand(0,200), 'not the word', $this->ink_color );
            }
        }
        
        if(file_exists($this->font)){
            imagettftext($this->image, 30, rand(-45,45), rand(10,80), rand(80,120), $this->ink_color, $this->font, $this->word);
        } else {
            
            imagestring($this->image, 5, rand(10,80), rand(80,120), $this->word, $this->ink_color );
        }
        
        foreach($filters as $filter){
            imagefilter($this->image, $filter);
        }
    
        return     $this->image;
    }
    
    /**
     * The binary content for the clouds background f no other backgorund is provided
     *
     * @var string base64 encoded
     */
    public $clouds = "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";
}

