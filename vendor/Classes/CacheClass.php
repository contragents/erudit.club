<?php

class Cache
{
    public static $_instance = null;

    public $redis;

    public function __construct()
    {
        $this->redis = new \Redis;
        try {
            $this->redis->pconnect(Config::$config['cache']['HOST'], Config::$config['cache']['PORT'], 10);
        } catch (Exception $e) {
            $this->redis->connect(Config::$config['cache']['HOST'], Config::$config['cache']['PORT'], 10);
        }
    }

    public static function zrangestore($storeTo, $srcSet, $minPosition, $maxPosition)
    {
        return exec("redis-cli -h " . Config::$config['cache']['HOST'] . " zrangestore $storeTo $srcSet $minPosition $maxPosition");
    }

    public static function zdiffstore($storeTo, $srcSetMain, $srcSet2, $srcSet3)
    {
        return exec("redis-cli -h " . Config::$config['cache']['HOST'] . " zdiffstore $storeTo 3 $srcSetMain $srcSet2 $srcSet3");
    }

    public static function zmscore($set, $member)
    {
        return exec("redis-cli -h " . Config::$config['cache']['HOST'] . " zmscore $set $member");
    }

    public static function rawcommand($command, array $params)
    {
        self::checkInstance();
        $redis = static::$_instance->redis;
        $res = call_user_func_array([$redis, 'rawcommand'], array_merge([$command], $params));

        return $res;
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

    /** Performing scan for KEYS
     * @param int $iterator passed by reference!
     * @param string $mask Tme search mask of keys
     * @param int $numValues Desired number of values per scan iteration
     * @return array|false Array with keys or false
     */
    public static function scan(&$iterator, $mask = false, $numValues = 10): array
    {
        self::checkInstance();
        if (!$mask) {
            $res = self::$_instance->redis->scan($iterator);
        } else {
            $res = self::$_instance->redis->scan($iterator, $mask, $numValues);
        }

        return self::prepareRes($res);
    }


    private static function checkInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self;
        }
    }

    public static function __callStatic($name, $arguments)
    {
        self::checkInstance();
        //Сериализуем все массивы в параметрах, кроме z-команд для упорядоченных множеств
        //т.к. они могут принимать массивы в качестве параметров
        if (substr($name, 0, 1) != 'z') {
            foreach ($arguments as $num => $value) {
                if (is_array($value)) {
                    $arguments[$num] = serialize($value);
                }
            }
        }
        $success = false;
        while (!$success) {
            try {
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
                    case 4:
                        $res = self::$_instance->redis->$name($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
                        break;

                }
                $success = true;
            } catch (Exception $e) {
                self::$_instance = new self;
            }
        }

        return self::prepareRes($res);
    }

    /** Checking if res is a serialized array than return unser. array. Else return the res
     * @param string $res Result of Redis method
     */
    private static function prepareRes($res)
    {
        if (empty($res) && $res !== 0)
            return FALSE;

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