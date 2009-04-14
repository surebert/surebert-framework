<?php
/**
 * Used to create an sb_RSSFeed image for the channel
 *
 */
class sb_RSS_Image{
	
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
	 * Returns an object suitable to use as an image for the sb_RSSFeed
	 *
	 * @param string $title The title of the image
	 * @param string $url The url of the image
	 * @return sbRSSImage
	 */
	public function __construct($title, $url){
		
        $this->title = $title;
        $this->url = $url;
        
     
        return true;
	}
	
}

?>