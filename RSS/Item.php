<?php

/**
 * Based on the description of an RSS 2.0 item at http://cyber.law.harvard.edu/rss/rss.html
 * @author paul.visco@roswellpark.org
 * @package RSS
 */
namespace sb\RSS;

class Item{
    
    /**
     * Creates an \sb\RSS\Item and adds the following properties
     *
     * @param string $title
     * @param string $link
     * 
     * @param string $description You can use html in the description, although escpaed it will appear.  if you want it to appear as HTML code, use encoded html.
     * @param string $pubDate
     */
    public function __construct($title='', $link='',$description='', $pubDate='')
    {
        $this->title = $title;
        $this->link = $link;
        $this->description = $description;
        $this->pubDate = $pubDate;
    }
    
    /**
     * The title of the item.  e.g. Venice Film Festival Tries to Quit Sinking
     *
     * @var string
     */
    public $title='';
    
    /**
     * The URL of the item. e.g. http://nytimes.com/2004/12/07FEST.html
     *
     * @var string
     */
    public $link='';
    
    /**
     * The item synopsis e.g.  Some of the most heated chatter at the Venice...
     *
     * @var string
     */
    public $description='';
    
    /**
     * Indicates when the item was published e.g. Sun, 19 May 2002 15:21:36 GMT
     *
     * @var string
     */
    public $pubDate='';
    
    /**
     * A string that uniquely identifies the item e.g. http://inessential.com/2002/09/01.php#a2
     *
     * @var string
     */
    public $guid='';
    
    /**
     * {optional} Email address of the author of the item e.g. test@test.com
     *
     * @var string
     */
    public $author='';
    
    /**
     * {Optional} You may include as many category elements as you need to, for different domains, and to have an item cross-referenced in different parts of the same domain.
     *
     * @var array An array of category strings
     */
    public $categories = Array();
    
    /**
     * {optional} The url of the comments if available
     *
     * @var string
     */
    public $comments='';
    
    /**
     * {optional} Describes a media object that is attached to the item.
     *
     * @var \sb\RSS\ItemEnclosure
     */
    public $enclosure;
    
    /**
     * {optional} The RSS channel that the item came from
     *
     * @var string
     */
    public $source='';
}


