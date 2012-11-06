<?php
/**
 * Describes a media object that is attached to the item
 * 
 * 
 * This can be used when creating podcasts, each item would get an MP3
 * @author paul.visco@roswellpark.org
 * @package RSS
 *
 */
namespace sb\RSS;

class ItemEnclosure{
    
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
     * The mime type of the enclosure e.g. audio/mpeg
     *
     * @var string
     */
    public $type;
    
    /**
     * Used to create an \sb\RSSEnclosure suitable for adding to an \sb\RSS\Item in an \sb\RSSFeed
     *
     * <code>
     * $myItem = new \sb\RSS\Item();
     * $myItem->enclosure = new \sb\RSS\ItemEnclosure('http://www.surebert.com/song.mp3', 2279344, 'audio/mpeg');
     * </code>
     *
     * @param string $url The URL of the media file
     * @param integer $length The length of the file in bytes
     * @param string $type The mime type of the enclose
     * @return unknown
     *
     */
    public function __construct($url, $length, $type)
    {
        
        $this->url = $url;
        $this->length = $length;
        $this->type = $type;
        
        return true;
    }
    
}

