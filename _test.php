<?php

// 建议使用 php _test.php 命令行运行测试文件

require 'Ip2Region.php';

$ip2region = new Ip2Region();

// array (
//     'city_id' => 1713,
//     'region' => '中国|0|广东省|广州市|电信',
// )

for ($i = 0; $i < 10; $i++) {
    test();
}

function getIp()
{
    $ip_long = array(
        array('607649792', '608174079'), // 36.56.0.0-36.63.255.255
        array('1038614528', '1039007743'), // 61.232.0.0-61.237.255.255
        array('1783627776', '1784676351'), // 106.80.0.0-106.95.255.255
        array('2035023872', '2035154943'), // 121.76.0.0-121.77.255.255
        array('2078801920', '2079064063'), // 123.232.0.0-123.235.255.255
        array('-1950089216', '-1948778497'), // 139.196.0.0-139.215.255.255
        array('-1425539072', '-1425014785'), // 171.8.0.0-171.15.255.255
        array('-1236271104', '-1235419137'), // 182.80.0.0-182.92.255.255
        array('-770113536', '-768606209'), // 210.25.0.0-210.47.255.255
        array('-569376768', '-564133889'), // 222.16.0.0-222.95.255.255
    );
    $rkey = mt_rand(0, 9);
    return long2ip(mt_rand($ip_long[$rkey][0], $ip_long[$rkey][1]));
}

function test()
{
    $ip = getIp();
    global $ip2region;

    echo PHP_EOL . "===============================";
    echo PHP_EOL . "测试 IP 地址: {$ip}";
    echo PHP_EOL . "--------【完整查询】------------" . PHP_EOL;
    $info = $ip2region->memorySearch($ip);
    var_export($info);

    echo PHP_EOL . "---------【简易查询】----------" . PHP_EOL;
    var_export($ip2region->simple($ip));
    echo PHP_EOL . "===============================" . PHP_EOL . PHP_EOL;
}