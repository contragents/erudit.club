<?php

namespace Dadata;

class Cache
{
    public static $_instance = null;

    public $redis;

    public function __construct()
    {
        $this->redis = new \Redis;
        $this->redis->pconnect("127.0.0.1", 6379);
    }

    /** Performing scan for HSET
     * @param string $key key for hset
     * @param int $iterator passed by reference!
     * @param string $mask Tme search mask within the key
     * @param int $numValues Desired number of values per scan iteration
     */
    public static function hscan($key, &$iterator, $mask = false, $numValues = 1)
    {
        self::checkInstance();
        if (!$mask) {
            $res = self::$_instance->redis->hscan($key, $iterator);
        } else {
            $res = self::$_instance->redis->hscan($key, $iterator, $mask, $numValues);
        }

        return self::prepareRes($res);
    }

    private static function checkInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self;
        }
    }

    /** Checking if res is a serialized array than return unser. array. Else return the res
     * @param string $res Result of Redis method
     */
    private static function prepareRes($res)
    {
        if (empty($res) && $res !== 0) {
            return false;
        }

        $unserRes = is_array($res) ? false : @unserialize($res);
        if ($unserRes === false) {
            return $res;
        } else {
            return $unserRes;
        }
    }

    public static function __callStatic($name, $arguments)
    {
        if (self::$_instance == null) {
            self::$_instance = new self;
        }

        foreach ($arguments as $num => $value) {
            if (is_array($value)) {
                $arguments[$num] = serialize($value);
            }
        }

        switch (count($arguments)) {
            case 1:
                $res = self::$_instance->redis->$name($arguments[0]);
                break;
            case 2:
                $res = self::$_instance->redis->$name($arguments[0], $arguments[1]);
                break;
            case 3:
                $res = self::$_instance->redis->$name($arguments[0], $arguments[1], $arguments[2]);
                break;
        }

        if (!$res) {
            return false;
        }

        $unserRes = is_array($res) ? false : @unserialize($res);
        if ($unserRes === false) {
            return $res;
        } else {
            return $unserRes;
        }
    }

    public static function getInstance()
    {
        if (self::$_instance != null) {
            return self::$_instance;
        }

        self::$_instance = new self;
        return self::$_instance;
    }
}