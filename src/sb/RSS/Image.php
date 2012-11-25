<?php
/**
 * Used to create an \sb\RSSFeed image for the channel
 * @author paul.visco@roswellpark.org
 * @package RSS
 */
namespace sb\RSS;

class Image{
    
    /**
     * The url for the image
     *
     * @var string
     */
    public $url='';
    
    /**
     * The title for the image
     *
     * @var string
     */
    public $title='';
    
    /**
     * Returns an object suitable to use as an image for the \sb\RSSFeed
     *
     * @param string $title The title of the image
     * @param string $url The url of the image
     * @return \sb\RSS\Image
     */
    public function __construct($title, $url)
    {
        
        $this->title = $title;
        $this->url = $url;
        
     
        return true;
    }
    
}

