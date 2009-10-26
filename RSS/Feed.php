<?php
/**
 * Describes and creates RSS 2.0 feeds. Remember to validate http://feedvalidator.org/check.cgi
 * @author Paul Visco 07/07/2007
 * @version 1.0 07/07/2007
 * @package sb_RSS
 * @example
 * <code>
//set content type as xml//set content type as xml
header("Content-Type: application/xml");

//create a new feed
$feed = new sb_RSS_Feed("My Test Feed", "http://www.test.com", "A test feed for test.com");

//add optional image
$feed->image= new sb_RSS_Image("Test's Feed", "http://test.com/test.gif");

//optional RSS cloud
$feed->cloud= new sb_RSS_Cloud("rpc.sys.com",80, "/RPC2", "myCloud.rssPleaseNotify", "xml-rpc");

//add some optional categories
$feed->categories[] = 'dancing';
$feed->categories[] = 'swimming';

//add optional skipHours and skipDays
$feed->skipHours = Array(0,3,5,7);
$feed->skipDays = Array('Monday', 'Tuesday');

//add an item to the rss feed - the constructor takes the required properties, they can also be added afterwards, as with author below
$item_one = $feed->add_item(new sb_RSS_Item("Test's First Article", "http://test.com?a=1", "<h1>Here is a simple HTML feed</h1><p>With a list</p><ol><li>one</li><li>two</li><li>three</li></ol>", date('r')));

//properties can also be added to the item afterwards, here are some optional ones
$item_one->author='paul@test.com';
$item_one->categories[] = 'swimming';

//for podcasts add an enclose, remember file size is required
$item_one->enclosure = new sb_RSS_ItemEnclosure('http://www.surebert.com/song.mp3', 2279344, 'audio/mpeg');

//add second item to the feed
$item_two = $feed->add_item(new sb_RSS_Item("Test's Second Article", "http://test.com?a=2", "This is just a plain text feed.  Hello World"), date('r'));

//echo out the RSS feed
echo $feed->display();
 * </code>
 *
 */

class sb_RSS_Feed extends DomDocument{
	
	/**
	 * The title of the feed
	 *
	 * @var string
	 * @example GoUpstate.com News Headlines
	 */
	public $title;
	
	/**
	 * The URL to the HTML website corresponding to the channel
	 *
	 * @var string
	 * @example http://www.goupstate.com/
	 */
	public $link;
	
	/**
	 * Phrase or sentence describing the channel
	 *
	 * @var string
	 * @example The latest news from GoUpstate.com, a Spartanburg Herald-Journal Web site.
	 */
	public $description;
	
	/**
	 * The sb_RSS_Items for the feed
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
	 * @var string
	 * @example Copyright 2002, Spartanburg Herald-Journal
	 */
	public $copyright;
	
	/**
	 * Email address for person responsible for editorial content.
	 *
	 * @var string
	 * @example geo@herald.com (George Matesky)
	 */
	public $managingEditor;
	
	/**
	 * Email address for person responsible for technical issues relating to channel.
	 *
	 * @var string
	 * @example betty@herald.com (Betty Guernsey)
	 */
	public $webMaster;
	
	/**
	 * The publication date for the content in the channel. For example, the New York Times publishes on a daily basis, the publication date flips once every 24 hours.
	 * 
	 *
	 * @var string Defaults to date('r') which is now in rss time format
	 * @example Sat, 07 Sep 2002 00:00:01 GMT
	 */
	public $pubDate;
	
	/**
	 * 	The last time the content of the channel changed.
	 *
	 * @var string
	 * @example Sat, 07 Sep 2002 09:42:31 GMT
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
	 * @var unknown_type
	 */
	public $cloud;
	
	/**
	 * ttl stands for time to live. It's a number of minutes that indicates how long a channel can be cached before refreshing from the source.
	 *
	 * @var integer
	 * @example 60
	 */
	public $ttl;
	
	/**
	 * The image of the feed
	 *
	 * @var sb_RSS_Image object
	 */
	public $image;
	
	/**
	 * A hint for aggregators telling them which hours they can skip. In 24 hour time.
	 *
	 * @var array
	 * @example $feed->skipHours = Array(0,5,6,9,12,15);
	 */
	public $skipHours = array();
	
	/**
	 * An XML element that contains up to seven <day> sub-elements whose value is Monday, Tuesday, Wednesday, Thursday, Friday, Saturday or Sunday. Aggregators may not read the channel during days listed in the skipDays element.
	 *
	 * @var array
	 * @example $feed->skipDays = Array('Sunday', 'Tuesday');
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
	 * Creates a new sb_RSSFeed, youc an either pass the required paramters to the contructor or add them afterwards to the result of the constructor
	 *
	 * @param string $title The title of the feed
	 * @param string $link The link to the feed
	 * @param string $description A description of the feed
	 */
	public function __construct($title='', $link='', $description=''){
		
		parent::__construct();
		
		$this->root = $this->appendChild($this->createElement('rss'));
		$this->root->appendChild($this->create_attribute('version', '2.0'));
		
		$this->channel = $this->root->appendChild($this->createElement('channel'));
		$this->title = $title;
		$this->link = $link;
		$this->description = $description;
	}
	
	/**
	 * Adds a sb_RSS_Item instance to a a sb_RSSFeed instance
	 *
	 * @param sb_RSS_Item $item
	 * @return sb_RSS_Item the reference to the item
	 */
	public function add_item(sb_RSS_Item &$item){
		
		$this->items[] = $item;
		return $item;
	}
	
	/**
	 * Converts the sb_RSSFeed instance into XML for display
	 *
	 * @return string
	 * @example echo $myFeed->display();
	 */
	public function display(){
    	
    	//add feed properties
    	$this->channel_properties_to_DOM();
    	
    	//add items
    	foreach($this->items as $item){
    		
    		$this->append_item($item);	
    	}
    	
    	return $this->saveXML();
    }
    
    /**
     * Creates and returns a DOM node attribute for appending
     *
     * @param string $name The name of the attribute
     * @param unknown_type $value The value of the attribute
     * @return object attribute node
     */
	private function create_attribute($name, $value){
		$attribute = $this->createAttribute($name);
		$val = $this->createTextNode($value);
		$attribute->appendChild($val);
		return $attribute;
	}
	
   /**
    * Creates and returns a new node with textNode, ready for appending
    *
    * @param string $nodeName
    * @param string $nodeValue
    * @return object DOM node
    */
    private function create_node($nodeName, $nodeValue){
		$node = $this->createElement($nodeName);
		$text = $this->createTextNode($nodeValue);
		$node->appendChild($text);
		return $node;
		
    }
	
    /**
     * Takes an sb_RSS_Item and converts it into a DOMM node followed by inserting it into the feed DOM
     *
     * @param sb_RSS_Item $item
     */
	private function append_item(sb_RSS_Item $item){
    	
        $new_item = $this->createElement("item");
        
        foreach(get_object_vars($item) as $key=>$val){
        	if($item->{$key} instanceof sb_RSS_ItemEnclosure){
    			$enclosure = $this->createElement('enclosure');
    			foreach($item->{$key} as $n=>$v){
    				$enclosure->appendChild($this->create_attribute($n, $v));
    			}
    			
    			$new_item->appendChild($enclosure);
    		}
    		
        	if($key == 'categories'){
    			foreach($item->{$key} as $category){
    				$new_item->appendChild($this->create_node('category', $category));
    			}
    		}
    		
        	if(is_string($val) && !empty($val)){
        		
		        $new_item->appendChild($this->create_node($key, $val));
        	}
        }
        
        if(empty($item->guid)){
        	$new_item->appendChild($this->create_node('guid', $item->link));
        }
        
        $this->channel->appendChild($new_item);
        
    }
    
    /**
     * Converts all the feed object properties into RSS DOM nodes and adds them to the channel node
     *
     */
    private function channel_properties_to_DOM(){
	  
    	
    	foreach(get_object_vars($this) as $key=>$val){
    		
    		//parse string based key value pairs
    		if (is_string($val) && !empty($val)){
    			
    			$this->channel->appendChild($this->create_node($key, $val));
    			
    		//parse image	
    		} else if ($this->{$key} instanceof sb_RSS_Image){
    		
    		
    			$image = $this->createElement('image');
    			foreach($this->{$key} as $n=>$v){
    				
    				$image->appendChild($this->create_node($n, $v));
    			}
    			
    			$image->appendChild($this->create_node('link', $this->link));
    			
    			$this->channel->appendChild($image);
    		
    		//parse cloud
    		} else if ($this->{$key} instanceof sb_RSS_Cloud){
    			
    			$cloud = $this->createElement('cloud');
    			foreach($this->{$key} as $n=>$v){
    				$cloud->appendChild($this->create_attribute($n, $v));
    			}
    			
    			$this->channel->appendChild($cloud);
    		
    		} else if($key == 'categories'){
    			foreach($this->{$key} as $category){
    				$this->channel->appendChild($this->create_node('category', $category));
    			}
    			
    		//parse skipHours and skipDays
    		} else if($key == 'skipHours' || $key == 'skipDays'){
    			
    			$node = $this->createElement($key);
    			$nodeName = ($key =='skipHours') ? 'hour' : 'day';
    			foreach($this->{$key} as $value){
    				//force caps on day name as it is required to validate
    				$node->appendChild($this->create_node($nodeName, ucwords($value)));
    			}
    			
    			$this->channel->appendChild($node);
    			
    		} 
    		
    	}	
    }
}
?>