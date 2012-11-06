<?php
/**
 * Interface used to describe caching mechanism
 * @author paul.visco@roswellpark.org
 * @package Cache
 */
namespace sb\Cache;

interface Base
{
    /**
     * Stores the cache data based using the key and ttl
     * @param $key The key to the cache, can be any unique
     * @param $data The data being stored
     * @param $lifetime The lifetime the cache remains in seconds
     * @return boolean
     */
    public function store($key, $data, $lifetime = 0);

    /**
     * Fetches the cache based on the key
     * @param $key
     * @return mixed false if not found otherwise the data stored in the cache
     */
    public function fetch($key);

    /**
     * Deletes cached data based on the key
     * @param $key
     * @return boolean If the data was deleted or not
     */
    public function delete($key);

    /**
     * Clears all the data in the cache, regardless of key
     * @return boolean If the data has been cleared or not
     */
    public function clearAll();

    /**
     * Loads all the values stores in the cache into an array by key, 
     * should be in alphabetical order
     * @return Array keys are the cahce_key and the value is the expires_by time
     */
    public function getKeys();
}

