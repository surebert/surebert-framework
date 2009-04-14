<?php
/**
 * Stores data in Memcache, requires memcache server be installed and running on the host and port specified
 * @author visco
 * @version 0.4 01/23/2009 01/28/2009
 * 
<code>
App::$cache = new sb_Cache_Memcache('localhost', 11211, 'myapp');
App::$cache->store(
</code>
 */
class sb_Cache_Memcache implements sb_Cache{
	
	/**
	 * The memcache server object
	 * @var Memcache
	 */
	public $memcache;
	
	/**
	 * The key to store the catalog in http://us2.php.net/manual/en/book.memcache.php
	 * @var string
	 */
	private $catalog_key = '/sb_Cache_Catalog';
	
	/**
	 * The namespace for your cache.  By default this is empty, but if you are on a shared memcache server this will keep your values separate
	 * @var string
	 */
	private $namespace = '';
	
	/**
	 * Constructs the mysql cache, pass the db connection to the constructor
	 * @param $host The hostname the memcache server is stored on
	 * @param $port The port to access the memcache server on
	 * @param $namespace The namespace required when sharing memcache server.  Must be totall unique, e.g. the name of your app?
	 */
	public function __construct($host, $port, $namespace){
	
		$this->memcache = $memcache = new Memcache;
		$memcache->connect($host, $port) or die ("Could not connect to memcache");
		$this->namespace = $namespace;
	}
	
	/**
	 * Store the cache data in memcache
	 * (non-PHPdoc)
	 * @see trunk/private/framework/sb/sb_Cache#store()
	 */
	public function store($key, $data, $lifetime = 0, $compress = 0) {
		$key = $this->namespace.$key;
		
		$store = $this->memcache->set($key, $data, $compress, $lifetime);
		
		if($store && $key != $this->namespace.$this->catalog_key){
	    	$this->catalog_key_add($key, $lifetime);
	    }
	    
	    return $store;
	}
	        
	/**
	 * Fetches the cache from memcache
	 * (non-PHPdoc)
	 * @see trunk/private/framework/sb/sb_Cache#fetch()
	 */
	public function fetch($key) {
		$key = $this->namespace.$key;
		
		return $this->memcache->get($key);
	}
	
	/**
	 * Deletes cache data
	 * (non-PHPdoc)
	 * @see trunk/private/framework/sb/sb_Cache#delete()
	 */
	public function delete($key) {
		
		$deleted = false;
		
		$catalog = array_keys($this->get_keys());
		foreach($catalog as $k){
		
			if(substr($k, 0, strlen($key)) == $key){
			
				$delete = $this->memcache->delete($this->namespace.$key);
				if($delete){
					$this->catalog_key_delete($k);
					$deleted = true;
				}
			}
			
		}
		
		return $deleted;
	} 
	
	/**
	 * Clears the whole cache
	 * (non-PHPdoc)
	 * @see private/framework/sb/sb_Cache#clear_all()
	 */
	public function clear_all(){
		return $this->memcache->flush();
	}
	
	/**
	 * Keeps track of the data stored in the cache to make deleting groups of data possible
	 * @param $key
	 * @return boolean If the catalog is stored or not
	 */
	private function catalog_key_add($key, $lifetime){
		
		$catalog = $this->fetch($this->catalog_key);
		$catalog = is_array($catalog) ? $catalog : Array();
		$catalog[$key] = ($lifetime == 0) ? $lifetime : $lifetime+time();
		return $this->store($this->catalog_key, $catalog);
	}
	
	/**
	 * Delete keys from the data catalog
	 * @param $key
	 * @return boolean If the catalog is stored or not
	 */
	private function catalog_key_delete($key){
		
		$catalog = $this->fetch($this->catalog_key);
		$catalog = is_array($catalog) ? $catalog : Array();
		if(isset($catalog[$key])){
			unset($catalog[$key]);
		};
		return $this->store($this->catalog_key, $catalog);
	}
	
	/**
	 * Loads the current catalog
	 * @return Array a list of all keys stored in the cache
	 */
	public function get_keys(){
		$catalog = $this->fetch($this->catalog_key);
		$catalog = is_array($catalog) ? $catalog : Array();
		$arr = Array();
		foreach($catalog as $k=>$v){
			$arr[preg_replace("~^".$this->namespace."~", '', $k)] = $v;
		}
		ksort($arr);
		return $arr;
	}
	
}
?>