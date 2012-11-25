<?php

/**
 * Stores data in APC - requires the apc extension is installed
 * @author paul.visco@roswellpark.org
 * @version 1.0 01/23/2009 05/14/2009
 * @package Cache
 */
namespace \sb\Cache;

class APC implements Base
{

    /**
     * The key to store the catalog in
     * @var string
     */
    private $catalog_key = '/sb_Cache_Catalog';

    /**
     * The namespace for your cache.  By default this is empty, but if you 
     * are on a shared memcache server this will keep your values separate
     * @var string
     */
    private $namespace = '';

    /**
     * Creates namespace for the data, as the cache may be shared between 
     * different apps. 
     * @param $namespace The namespace required when sharing memcache server.  
     * Must be totall unique, e.g. the name of your app?
     */
    public function __construct($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * Store the cache data in APC
     */
    public function store($key, $data, $lifetime = 0)
    {

        $key = $this->namespace . $key;

        $store = apc_store($key, $data, $lifetime);
        if ($store && $key != $this->namespace . $this->catalog_key) {
            $this->catalog_key_add($key, $lifetime);
        }
        return $store;
    }

    /**
     * Fetches the cache from APC
     */
    public function fetch($key)
    {
        $key = $this->namespace . $key;

        return apc_fetch($key);
    }

    /**
     * Deletes cache data
     */
    public function delete($key)
    {

        $deleted = false;

        $catalog = array_keys($this->get_keys());
        foreach ($catalog as $k) {

            if (substr($k, 0, strlen($key)) == $key) {

                $delete = apc_delete($this->namespace . $k);
                if ($delete) {
                    $this->catalog_key_delete($k);
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
        return apc_clear_cache('user');
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
        $catalog = is_array($catalog) ? $catalog : Array();
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
        $catalog = is_array($catalog) ? $catalog : Array();
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
        $catalog = is_array($catalog) ? $catalog : Array();

        $arr = Array();
        foreach ($catalog as $k => $v) {
            $arr[preg_replace("~^" . $this->namespace . "~", '', $k)] = $v;
        }
        ksort($arr);
        return $arr;
    }
}

