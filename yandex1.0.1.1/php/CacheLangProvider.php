<?php
namespace Dadata;
class Cache
{
    public static $_instance = null;

    public $redis;

    public function __construct()
    {

        //$this->_instance = new \Redis();
        //$this->_instance->pconnect("localhost", 6379);;
        $this->redis = new \Redis;
        $this->redis->pconnect("127.0.0.1", 6379);
        //print "connect!!!";

    }

    private function __clone()
    {
    }

    private function __wakeup()
    {
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

        if (!$res)
            return FALSE;

        $unserRes = is_array($res) ? false : unserialize($res);
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
//$dd = new Cache;