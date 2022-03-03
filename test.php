<?php

require 'Ip2Region.php';

$ip2region = new Ip2Region();

$ip = '223.104.111.41';
echo PHP_EOL;
echo "查询IP：{$ip}" . PHP_EOL;
$info = $ip2region->btreeSearch($ip);
var_export($info);

echo PHP_EOL;
$info = $ip2region->memorySearch($ip);
var_export($info);
echo PHP_EOL;

// array (
//     'city_id' => 1713,
//     'region' => '中国|0|河南省|郑州市|移动',
// )