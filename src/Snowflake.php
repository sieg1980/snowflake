<?php

namespace Zimutech;

class Snowflake
{
    // 仅支持2000年1月1日之后生成uuid
    const twepoch = 946684800000;

    private string $key;
    private string $dict;
    private int $machineId;
    private object $redis;

    public function __construct(string $prefix, int $machineId, object &$redis, string $dict = '')
    {
        $this->key = $prefix . '.sfsn';
        $this->machineId = $machineId;
        $this->dict = $dict;
        $this->redis = $redis;
    }

    public function generate() : string
    {
        $now = ceil(microtime(true) * 1000.0) - self::twepoch;
        $serial = $this->redis->incr($this->key) % 16384;

        // 由于PHP目前不支持无符号整数，所以3114年12月13日之前产生的uuid不会产生越界错误
        // 之后如果PHP支持了无符号证书，在4229年11月24日之前产生的uuid都不会产生越界错误
        $uuid = $now << 18 | $this->machineId << 14 | $serial;

        if($this->dict === '')
            return $uuid;
        else
            return $this->convertToString($uuid);
    }

    private function convertToString(int $key) : string
    {
        $uuid = '';

        while($key > 0)
        {
            $mod = $key % 62;
            $uuid = $this->dict[$mod] . $uuid;
            $key = ($key - $mod) / 62;
        }

        return $uuid;
    }

    public static function makeDict(int $times = 10000) : string
    {
        $dict = 'li2VpEAWcKn8tdGFIqDg4bmf0UMHu97aYBP1kheyX6SNCvZrzjOQxo5L3sJwTR';

        for($i = 0; $i < $times; $i++)
        {
            $p1 = random_int(0, 61);
            $p2 = random_int(0, 61);
            $tmp = $dict[$p2];
            $dict[$p2] = $dict[$p1];
            $dict[$p1] = $tmp;
        }

        return $dict;
    }

    public function convertToNumber(string $uuid)
    {
        $length = strlen($uuid);
        $total = 0;

        for($i = 0; $i < $length; $i++)
        {
            $m = $uuid[$length - 1 - $i];

            for($j = 0; $j < 62; $j++)
            {
                if($this->dict[$j] === $m)
                    break;
            }

            $total += $j * pow(62, $i);
        }

        return $total;
    }
}
