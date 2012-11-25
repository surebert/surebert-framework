<?php

/**
 * Stores data in Memcache, requires memcache server be installed and running 
 * on the host and port specified
 *
 * @author paul.visco@roswellpark.org
 * @package Cache
 *
 */
namespace sb\Cache;

class Memcache implements Base
{

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
     * The namespace for your cache.  By default this is empty, but if you are
     *  on a shared memcache server this will keep your values separate
     * @var string
     */
    private $namespace = '';

    /**
     * Constructs the mysql cache, pass the db connection to the constructor
     * 
     * <code>
     * App::$cache = new \sb\Cache\Memcache('localhost', 11211, 'myapp');
     * App::$cache->store(
     * </code>
     *
     * @param $host The hostname the memcache server is stored on
     * @param $port The port to access the memcache server on
     * @param $namespace The namespace required when sharing memcache server.  
     * Must be totall unique, e.g. the name of your app?
     */
    public function __construct($host, $port, $namespace)
    {

        $this->memcache = new \Memcache;
        if (!@$this->memcache->connect($host, $port)) {
            throw(new \Exception('Cannot connect to memcached server'));
        }
        $this->namespace = $namespace;
    }

    /**
     * Store the cache data in memcache
     * (non-PHPdoc)
     * @see trunk/private/framework/sb/sb_Cache#store()
     */
    public function store($key, $data, $lifetime = 0, $compress = 0)
    {
        $key = $this->namespace . $key;

        $store = $this->memcache->set($key, $data, $compress, $lifetime);

        if ($store && $key != $this->namespace . $this->catalog_key) {
            $this->catalogKeyAdd($key, $lifetime);
        }

        return $store;
    }

    /**
     * Fetches the cache from memcache
     * (non-PHPdoc)
     * @see trunk/private/framework/sb/sb_Cache#fetch()
     */
    public function fetch($key)
    {
        $key = $this->namespace . $key;

        return $this->memcache->get($key);
    }

    /**
     * Deletes cache data
     * (non-PHPdoc)
     * @see trunk/private/framework/sb/sb_Cache#delete()
     */
    public function delete($key)
    {

        $deleted = false;

        $catalog = \array_keys($this->get_keys());
        foreach ($catalog as $k) {

            if (substr($k, 0, strlen($key)) == $key) {

                $delete = $this->memcache->delete($this->namespace . $key);
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
        return $this->memcache->flush();
    }

    /**
     * Keeps track of the data stored in the cache to make deleting groups of data possible
     * @param $key
     * @return boolean If the catalog is stored or not
     */
    private function catalogKeyAdd($key, $lifetime)
    {

        $catalog = $this->fetch($this->catalog_key);
        $catalog = \is_array($catalog) ? $catalog : Array();
        $catalog[$key] = ($lifetime == 0) ? $lifetime : $lifetime + \time();
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
            $arr[\preg_replace("~^" . $this->namespace . "~", '', $k)] = $v;
        }
        \ksort($arr);
        return $arr;
    }
}

