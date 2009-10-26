<?php
/**
 * Describes a media object that is attached to the item
 * 
 * 
 * This can be used when creating podcasts, each item would get an MP3
 * @author Paul Visco
 * @package sb_RSS
 *
 */
class sb_RSS_ItemEnclosure{
	
	/**
	 * The url for the enclosure
	 *
	 * @var string
	 */
	public $url;
	
	/**
	 * The length for the enclosure in bytes
	 *
	 * @var string
	 */
	public $length;
	
	/**
	 * The mime type of the enclosure
	 *
	 * @var string
	 * @example audio/mpeg
	 */
	public $type;
	
	/**
	 * Used to create an sb_RSSEnclosure suitable for adding to an sb_RSS_Item in an sb_RSSFeed
	 *
	 * @param string $url The URL of the media file
	 * @param integer $length The length of the file in bytes
	 * @param string $type The mime type of the enclose
	 * @return unknown
	 *
	<code>
	$myItem = new sb_RSS_Item();
	$myItem->enclosure = new sb_RSS_ItemEnclosure('http://www.surebert.com/song.mp3', 2279344, 'audio/mpeg');
	<code>
	 */
	public function __construct($url, $length, $type){
		
        $this->url = $url;
        $this->length = $length;
        $this->type = $type;
        
        return true;
	}
	
}

?>