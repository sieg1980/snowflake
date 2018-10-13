<?php

namespace Zimutech;

class Snowflake
{
	const twepoch = 946684800000;

	private $key;
	private $machineId;
	private $redis;
	
	public function __construct(string $prefix, int $machineId, object &$redis)
	{
    	$this->key = $prefix . '.snowflake.serial';
		$this->machineId = $machineId;
		$this->redis = $redis;
	}
	
	public function generate() : int
	{
		$now = ceil(microtime(true) * 1000.0) - self::twepoch;
		$serial = $this->redis->incr($this->key) % 16384;
		$uuid = $now << 18 | $this->machineId << 14 | $serial;
		
		return $uuid;
	}
}