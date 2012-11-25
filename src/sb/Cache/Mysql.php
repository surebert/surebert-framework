<?php

/**
 *
 * Used to store cache data in a mysql table
 *
 * <code>
 * CREATE TABLE sb_cache_mysql
 * (
 *     cache_key varchar(200) NOT NULL,
 *     expires_by INT,
 *     data TEXT,
 *     PRIMARY KEY (cache_key)
 * )
 * </code>
 *
 * @author paul.visco@roswellpark.org
 * @package Cache
 *
 *
 */
namespace sb\Cache;

class Mysql implements Base
{

    /**
     * The database connection to store the data in
     * @var PDO
     */
    private $db;

    /**
     * The DB prepared statments cache
     * @var Array
     */
    private $stmts = Array();

    /**
     * Constructs the mysql cache, pass the db connection to the constructor
     * @param $db PDO
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Stores the cache data in mysql
     */
    public function store($key, $data, $lifetime = 0)
    {
        $stmt_cache = \md5(__METHOD__);

        if (!isset($this->stmts[$stmt_cache])) {

            $this->stmts[$stmt_cache] = $this->db->prepare("
                REPLACE INTO
                sb_cache_mysql
                (cache_key, expires_by, data)
                VALUES
                (:cache_key, :expires_by, :data)
            ");
        }

        if ($lifetime != 0) {
            echo 'dd';
            $lifetime = \time() + $lifetime;
        }

        $stmt = $this->stmts[$stmt_cache];

        return $stmt->execute(Array(
                    ':cache_key' => $key,
                    ':expires_by' => $lifetime,
                    ':data' => \serialize($data)
                ));
    }

    /**
     * Fetches the cache data from mysql
     */
    public function fetch($key)
    {
        $stmt_cache = \md5(__METHOD__);

        if (!isset($this->stmts[$stmt_cache])) {
            $this->stmts[$stmt_cache] = $this->db->prepare("
                SELECT
                    expires_by,
                    data
                FROM
                    sb_cache_mysql
                WHERE
                    cache_key = :cache_key
            ");
        }

        $stmt = $this->stmts[$stmt_cache];

        $result = $stmt->execute(Array(
            ':cache_key' => $key
                ));

        $rows = $stmt->fetchAll();

        if (isset($rows[0]) && ($rows[0]->expires_by == 0 || \time() < $rows[0]->expires_by)) {

            $data = @\unserialize($rows[0]->data);

            if ($data) {
                return $data;
            }
        } else {
            $this->delete($key);
        }

        return false;
    }

    /**
     * Delete the cache from the mysql database
     */
    public function delete($key)
    {
        $stmt_cache = \md5(__METHOD__);

        if (!isset($this->stmts[$stmt_cache])) {
            $this->stmts[$stmt_cache] = $this->db->prepare("
                DELETE
                FROM
                    sb_cache_mysql
                WHERE
                    cache_key LIKE :cache_key
            ");
        }

        return $this->stmts[$stmt_cache]->execute(Array(
                    ':cache_key' => $key . '%'
                ));
    }

    /**
     * Clears the cache
     * @return unknown_type
     */
    public function clearAll()
    {
        $stmt_cache = \md5(__METHOD__);

        if (!isset($this->stmts[$stmt_cache])) {
            $this->stmts[$stmt_cache] = $this->db->prepare("
                TRUNCATE TABLE
                    sb_cache_mysql
            ");
        }

        return $this->stmts[$stmt_cache]->execute();
    }

    /**
     * Loads the current catalog
     * @return Array a list of all keys stored in the cache
     */
    public function getKeys()
    {

        $stmt_cache = md5(__METHOD__);

        if (!isset($this->stmts[$stmt_cache])) {
            $this->stmts[$stmt_cache] = $this->db->prepare("
                SELECT
                    cache_key,
                    expires_by
                FROM
                    sb_cache_mysql
                ORDER BY cache_key
            ");
        }
        $stmt = $this->stmts[$stmt_cache];
        $stmt->setFetchMode(\PDO::FETCH_NUM);

        $result = $stmt->execute();

        if ($result) {
            $rows = $stmt->fetchAll();
            $arr = Array();

            foreach ($rows as $r) {
                $arr[$r[0]] = $r[1];
            }

            return $arr;
        } else {
            return Array();
        }
    }
}

