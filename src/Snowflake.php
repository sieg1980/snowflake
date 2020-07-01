<?php

namespace Zimutech;

class Snowflake
{
    // 仅支持2000年1月1日之后生成uuid
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

		// 由于PHP目前不支持无符号整数，所以3114年12月13日之前产生的uuid不会产生越界错误
        // 之后如果PHP支持了无符号证书，在4229年11月24日之前产生的uuid都不会产生越界错误
		$uuid = $now << 18 | $this->machineId << 14 | $serial;

		return $uuid;
	}
}
