<?php

/**
 * Describes and creates RSS 2.0 feeds. Remember to validate http://feedvalidator.org/check.cgi
 * 
 * @author paul.visco@roswellpark.org
 * @package RSS
*/
namespace sb\RSS;

class Feed extends \DomDocument{
    
    /**
     * The title of the feed e.g. GoUpstate.com News Headlines
     *
     * @var string
     */
    public $title;
    
    /**
     * The URL to the HTML website corresponding to the channel
     * e.g. http://www.goupstate.com/
     *
     * @var string
     */
    public $link;
    
    /**
     * Phrase or sentence describing the channel e.g. The latest news from GoUpstate.com, a Spartanburg Herald-Journal Web site.
     *
     * @var string
     */
    public $description;
    
    /**
     * The \sb\RSS\Items for the feed
     *
     * @var array
     */
    public $items = Array();
    
    #######################################
    ###All props  after this is optional###
    #######################################
    
    /**
     * The language the feed is in
     *
     * @var string Default is english
     */
    public $language = 'en-us';
    
    /**
     * Copyright notice for content in the channel.
     *
     * e.g. Copyright 2002, Spartanburg Herald-Journal
     *
     * @var string
     */
    public $copyright;
    
    /**
     * Email address for person responsible for editorial content.
     *
     * e.g. geo@herald.com (George Matesky)
     *
     * @var string
     */
    public $managingEditor;
    
    /**
     * Email address for person responsible for technical issues relating to channel.
     *
     * e.g. betty@herald.com (Betty Guernsey)
     * @var string
     */
    public $webMaster;
    
    /**
     * The publication date for the content in the channel. For example, the New York Times publishes on a daily basis, the publication date flips once every 24 hours.
     * 
     * e.g. Sat, 07 Sep 2002 00:00:01 GMT
     * @var string Defaults to date('r') which is now in rss time format
     */
    public $pubDate;
    
    /**
     * The last time the content of the channel changed.
     * e.g. Sat, 07 Sep 2002 09:42:31 GMT
     * @var string
     */
    public $lastBuildDate;
    
    /**
     * Categories that describe the feed, this is an array, all categories get their own categry tag as a feed can have multiple categories
     *
     * @var array
     */
    public $categories = Array();
    
    
    /**
     * Allows processes to register with a cloud to be notified of updates to the channel, implementing a lightweight publish-subscribe protocol for RSS feeds.
     *
     * @var \sb\RSS\Cloud
     */
    public $cloud;
    
    /**
     * ttl stands for time to live. e.g. 60 It's a number of minutes that indicates how long a channel can be cached before refreshing from the source.
     *
     * @var integer
     */
    public $ttl;
    
    /**
     * The image of the feed
     *
     * @var \sb\RSS\Image object
     */
    public $image;
    
    /**
     * A hint for aggregators telling them which hours they can skip. In 24 hour time.
     *
     <code>
    $feed->skipHours = Array(0,5,6,9,12,15);
     </code>
     *
     * @var array
     *
     */
    public $skipHours = array();
    
    /**
     * An XML element that contains up to seven <day> sub-elements whose value is Monday, Tuesday, Wednesday, Thursday, Friday, Saturday or Sunday. Aggregators may not read the channel during days listed in the skipDays element.
     * <code>
     * $feed->skipDays = Array('Sunday', 'Tuesday');
     * </code>
     *
     * @var array
     */
    public $skipDays = array();
    
    /**
     * The PICS rating for the channel.
     *
     * @var string? Not sure on this more info here http://www.w3.org/PICS/
     */
    public $rating;
        
    /**
     * A URL that points to the documentation for the format used in the RSS file. It's probably a pointer to this page.
     *
     * @var string
     */
    private $docs ='http://blogs.law.harvard.edu/tech/rss';
    
    /**
     * What was used to generate the feed
     *
     * @var string
     */
    private  $generator = "Paul Visco's surebert framework RSS creator";
    
    /**
     * The root of the RSS XML doc
     *
     * @var object DOM node
     */
    private $root;

    /**
     * The channel element of the RSS XML doc
     *
     * @var object DOM node
     */
    private $channel;
    
    /**
     * Creates a new \sb\RSSFeed, youc an either pass the required paramters to the contructor or add them afterwards to the result of the constructor
     *
     *  <code>
     * //set content type as xml//set content type as xml
     * header("Content-Type: application/xml");
     *
     * //create a new feed
     * $feed = new \sb\RSS\Feed("My Test Feed", "http://www.test.com", "A test feed for test.com");
     *
     * //add optional image
     * $feed->image= new \sb\RSS\Image("Test's Feed", "http://test.com/test.gif");
     *
     * //optional RSS cloud
     * $feed->cloud= new \sb\RSS\Cloud("rpc.sys.com",80, "/RPC2", "myCloud.rssPleaseNotify", "xml-rpc");
     *
     * //add some optional categories
     * $feed->categories[] = 'dancing';
     * $feed->categories[] = 'swimming';
     * 
     * //add optional skipHours and skipDays
     * $feed->skipHours = Array(0,3,5,7);
     * $feed->skipDays = Array('Monday', 'Tuesday');
     *
     * //add an item to the rss feed - the constructor takes the required properties, they can also be added afterwards, as with author below
     * $item_one = $feed->addItem(new \sb\RSS\Item("Test's First Article", "http://test.com?a=1", "<h1>Here is a simple HTML feed</h1><p>With a list</p><ol><li>one</li><li>two</li><li>three</li></ol>", date('r')));
     *
     * //properties can also be added to the item afterwards, here are some optional ones
     * $item_one->author='paul@test.com';
     * $item_one->categories[] = 'swimming';
     * 
     * //for podcasts add an enclose, remember file size is required
     * $item_one->enclosure = new \sb\RSS\ItemEnclosure('http://www.surebert.com/song.mp3', 2279344, 'audio/mpeg');
     * 
     * //add second item to the feed
     * $item_two = $feed->addItem(new \sb\RSS\Item("Test's Second Article", "http://test.com?a=2", "This is just a plain text feed.  Hello World"), date('r'));
     * 
     * //echo out the RSS feed
     * echo $feed->display();
     * </code>
     *
     * @param string $title The title of the feed
     * @param string $link The link to the feed
     * @param string $description A description of the feed
     */
    public function __construct($title='', $link='', $description='')
    {
        
        parent::__construct();
        
        $this->root = $this->appendChild($this->createElement('rss'));
        $this->root->setAttribute('version', '2.0');
        
        $this->channel = $this->root->appendChild($this->createElement('channel'));
        $this->title = $title;
        $this->link = $link;
        $this->description = $description;
    }
    
    /**
     * Adds a \sb\RSS\Item instance to a a \sb\RSSFeed instance
     *
     * @param \sb\RSS\Item $item
     * @return \sb\RSS\Item the reference to the item
     */
    public function addItem(\sb\RSS\Item &$item)
    {
        
        $this->items[] = $item;
        return $item;
    }
    
    /**
     * Converts the \sb\RSSFeed instance into XML for display
     * <code>
     * echo $myFeed->display();
     * </code>
     *
     * @return string
     */
    public function display()
    {
        
        //add feed properties
        $this->channelPropertiesToDOM();
        
        //add items
        foreach($this->items as $item){
            
            $this->appendItem($item);    
        }
        
        return $this->saveXML();
    }
    
   /**
    * Creates and returns a new node with textNode, ready for appending
    *
    * @param string $nodeName
    * @param string $nodeValue
    * @return object DOM node
    */
    private function createNode($nodeName, $nodeValue, $cdata = false)
    {
        $node = $this->createElement($nodeName);
        if($cdata){
            $text = $this->createCDATASection($nodeValue);
        } else {
            $text = $this->createTextNode($nodeValue);
        }
        
        $node->appendChild($text);
        return $node;
        
    }
    
    /**
     * Takes an \sb\RSS\Item and converts it into a DOMM node followed by inserting it into the feed DOM
     *
     * @param \sb\RSS\Item $item
     */
    private function appendItem(\sb\RSS\Item $item)
    {
        
        $new_item = $this->createElement("item");
        
        foreach(get_object_vars($item) as $key=>$val){
            if($item->{$key} instanceof \sb\RSS\ItemEnclosure){
                $enclosure = $this->createElement('enclosure');
                foreach($item->{$key} as $n=>$v){
                    $enclosure->setAttribute($n, $v);
                }
                
                $new_item->appendChild($enclosure);
            }
            
            if($key == 'categories'){
                foreach($item->{$key} as $category){
                    $new_item->appendChild($this->createNode('category', $category));
                }
            }
            
            if(is_string($val) && !empty($val)){
                
                $new_item->appendChild($this->createNode($key, $val, $key == 'description'));
            }
        }
        
        if(empty($item->guid)){
            $new_item->appendChild($this->createNode('guid', $item->link));
        }
        
        $this->channel->appendChild($new_item);
        
    }
    
    /**
     * Converts all the feed object properties into RSS DOM nodes and adds them to the channel node
     *
     */
    private function channelPropertiesToDOM()
    {
      
        foreach(get_object_vars($this) as $key=>$val){
            
            //parse string based key value pairs
            if (is_string($val) && !empty($val)){
                $this->channel->appendChild($this->createNode($key, $val, $key == 'description'));
            
            //parse image    
            } elseif ($this->{$key} instanceof \sb\RSS\Image){
            
            
                $image = $this->createElement('image');
                foreach($this->{$key} as $n=>$v){
                    
                    $image->appendChild($this->createNode($n, $v));
                }
                
                $image->appendChild($this->createNode('link', $this->link));
                
                $this->channel->appendChild($image);
            
            //parse cloud
            } elseif ($this->{$key} instanceof \sb\RSS_Cloud){
                
                $cloud = $this->createElement('cloud');
                foreach($this->{$key} as $n=>$v){
                    $cloud->setAttribute($n, $v);
                }
                
                $this->channel->appendChild($cloud);
            
            } elseif($key == 'categories'){
                foreach($this->{$key} as $category){
                    $this->channel->appendChild($this->createNode('category', $category));
                }
                
            //parse skipHours and skipDays
            } elseif($key == 'skipHours' || $key == 'skipDays'){
                
                $node = $this->createElement($key);
                $nodeName = ($key =='skipHours') ? 'hour' : 'day';
                foreach($this->{$key} as $value){
                    //force caps on day name as it is required to validate
                    $node->appendChild($this->createNode($nodeName, ucwords($value)));
                }
                
                $this->channel->appendChild($node);
                
            } 
            
        }    
    }
}
