<?php

/**
 * Stores data in a hash.  Data is only alive until the end of script, then
 * all data is gone.  You can still set lifetime, etc in case you script uses sleep has
 * a long timeout
 *
 *
 * <code>
 * App::$cache = new \sb\Cache_Hash(');
 * App::$cache->store('/users/paul', $user);
 * </code>
 *
 * @package Cache
 *
 */
namespace sb\Cache;

class Hash implements Base
{

    /**
     * The key to store the catalog in
     * @var string
     */
    private $catalog_key = '/sb_Cache_Catalog';

    /**
     * The hash array that the data is stored in
     * @var Array
     */
    public $hash = Array();

    /**
     * Constructs the mysql cache, pass the db connection to the constructor
     * @param $host The hostname the memcache server is stored on
     * @param $port The port to access the memcache server on
     * @param $namespace The namespace required when sharing memcache server.  
     * Must be totall unique, e.g. the name of your app?
     */
    public function __construct()
    {
    }

    /**
     * Store the cache data in memcache
     */
    public function store($key, $data, $lifetime = 0)
    {

        if ($lifetime != 0) {
            $lifetime = \time() + $lifetime;
        }

        $data = array($lifetime, $data);

        $this->hash[$key] = $data;

        if ($key != $this->catalog_key) {
            $this->catalog_key_add($key, $lifetime);
        }

        return true;
    }

    /**
     * Fetches the cache from memcache
     */
    public function fetch($key)
    {

        if (!\array_key_exists($key, $this->hash)) {
            return false;
        }

        $data = $this->hash[$key];

        //check to see if it expired
        if ($data && ($data[0] == 0 || \time() <= $data[0])) {
            return $data[1];
        } else {
            $this->delete($key);
            return false;
        }
    }

    /**
     * Deletes cache data
     */
    public function delete($key)
    {

        $deleted = false;

        $catalog = \array_keys($this->get_keys());
        foreach ($catalog as $k) {

            if ($k == $key) {
                unset($this->hash[$key]);
                if ($delete) {
                    $this->catalogKeyDelete($k);
                    $deleted = true;
                }
            }
        }

        return $deleted;
    }

    /**
     * Clears the whole cache
     */
    public function clearAll()
    {
        return $this->hash = Array();
    }

    /**
     * Keeps track of the data stored in the cache to make deleting groups of 
     * data possible
     * @param $key
     * @return boolean If the catalog is stored or not
     */
    private function catalogKeyAdd($key, $lifetime)
    {

        $catalog = $this->fetch($this->catalog_key);
        $catalog = \is_array($catalog) ? $catalog : Array();
        $catalog[$key] = ($lifetime == 0) ? $lifetime : $lifetime + time();
        return $this->store($this->catalog_key, $catalog);
    }

    /**
     * Delete keys from the data catalog
     * @param $key
     * @return boolean If the catalog is stored or not
     */
    private function catalogKeyDelete($key)
    {

        $catalog = $this->fetch($this->catalog_key);
        $catalog = \is_array($catalog) ? $catalog : Array();
        if (isset($catalog[$key])) {
            unset($catalog[$key]);
        };
        return $this->store($this->catalog_key, $catalog);
    }

    /**
     * Loads the current catalog
     * @return Array a list of all keys stored in the cache
     */
    public function getKeys()
    {

        $catalog = $this->fetch($this->catalog_key);

        $catalog = \is_array($catalog) ? $catalog : Array();
        $arr = Array();
        foreach ($catalog as $k => $v) {
            $arr[$k] = $v;
        }
        \ksort($arr);
        return $arr;
    }
}

