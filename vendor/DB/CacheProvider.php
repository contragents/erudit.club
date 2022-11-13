<?php
namespace Dadata;
class Cache
{
	public static $_instance = null;
	
	public $redis;

	public function __construct () {
		
		//$this->_instance = new \Redis();
		//$this->_instance->pconnect("localhost", 6379);;
		$this->redis = new \Redis;
		$this->redis->pconnect("127.0.0.1", 6379);
		//print "connect!!!";

	}

	private function __clone () {}
	private function __wakeup () {}
	
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