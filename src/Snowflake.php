<?php

namespace Iserlohn\Snowflake;

class Snowflake
{
	const twepoch = 1419120000;
	
	private $key;
	private $machineId;
	private $redis;
	
	public function __construct($prefix, $machineId, &$redis)
	{
    	$this->key = $prefix . '.atom.generate.random';
		$this->machineId = $machineId;
		
		if(is_object($redis)) {
    		$this->redis = $redis;
		} else {
    		$this->redis = new \Redis();
    		$this->redis->open($redis['host'], $redis['port']);
		}
	}
	
	public function generate() : int
	{
		do {
			$random = mt_rand(0, 67168863);
		} while($this->redis->sadd($this->key, $random) == false);
		
		if($this->redis->ttl($this->key) == -2) {
			$this->redis->expire($this->key, 1);
		}
		
		$uuid = ((time() - self::twepoch) << 30) | ($this->machineId << 26) | $random;
		
		return $uuid;
	}
}