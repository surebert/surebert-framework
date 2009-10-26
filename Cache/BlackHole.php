<?php
/**
 * Does not actually cache any data, but uses the Cache interface so that you can
 * removed caching without changing application code
 * @author visco
 * @version 1.0 01-23-2009 05-12-2009
 * @package sb_Cache
 * 
<code>
$cache = new sb_Cache_BlackHole();
$cache->store('/my/key', 'something');
echo $cache->fetch('/my/key');
</code>
 */
class sb_Cache_BlackHole implements sb_Cache_Base{
	
	/**
	 * Store the cache data in memcache
	 * (non-PHPdoc)
	 * @see trunk/private/framework/sb/sb_Cache#store()
	 */
	public function store($key, $data, $lifetime = 0) {
		return true;
	}
	        
	/**
	 * Fetches the cache from memcache
	 * (non-PHPdoc)
	 * @see trunk/private/framework/sb/sb_Cache#fetch()
	 */
	public function fetch($key) {
		return false;
	}
	
	/**
	 * Deletes cache data
	 * (non-PHPdoc)
	 * @see trunk/private/framework/sb/sb_Cache#delete()
	 */
	public function delete($key) {
		return true;
	} 
	
	/**
	 * Clears the whole cache
	 * (non-PHPdoc)
	 * @see private/framework/sb/sb_Cache#clear_all()
	 */
	public function clear_all(){
		return true;
	}
	
	/**
	 * Loads the current catalog
	 * @return Array a list of all keys stored in the cache
	 */
	public function get_keys(){
		return Array();
	}
	
}
?>