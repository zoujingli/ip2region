<?php

/**
 * IP2Region 演示脚本
 *
 * 功能说明：
 * - 展示IP2Region库的基本使用方法
 * - 演示IPv4和IPv6地址查询功能
 * - 展示多种缓存策略和数据库加载
 * - 性能测试和统计信息展示
 *
 * 演示内容：
 * 1. IPv4地址查询演示 - 展示常见公网地址的查询结果
 * 2. IPv6地址查询演示 - 展示公网地址和特殊用途地址的查询结果
 * 3. 详细信息查询 - 展示完整的IP信息结构
 * 4. 数据库加载状态 - 展示当前实例的加载情况
 * 5. 性能统计信息 - 内存使用和IO统计
 * 6. 批量查询演示 - 批量处理多个IP地址
 *
 * 说明：
 * - 公网地址期望值来自当前 XDB 数据库的真实查询结果。
 * - 特殊用途地址期望值来自库内 IANA/RFC 特殊地址识别规则。
 * - 普通 IPv6 公网地址查询需要预先准备 ip2region_v6.xdb。
 *
 * 运行方式：
 * php tests/demo.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "=== Ip2Region 演示 ===\n\n";

try {
    // 创建查询器实例
    $searcher = new Ip2Region("content");
    $failures = array();

    $runSimpleCase = function ($ip, $expected, $allowMissingIpv6Db = false) use ($searcher, &$failures) {
        try {
            $actual = $searcher->simple($ip);
        } catch (Exception $e) {
            if ($allowMissingIpv6Db && strpos($e->getMessage(), 'IPv6 查询需要下载') !== false) {
                echo "  {$ip} => 跳过: 缺少 IPv6 数据库\n";
                return;
            }

            $failures[] = sprintf('%s expected `%s`, got exception `%s`', $ip, $expected, $e->getMessage());
            echo "  {$ip} => 异常: " . $e->getMessage() . "\n";
            return;
        }

        $status = $actual === $expected ? '通过' : '失败';
        if ($actual !== $expected) {
            $failures[] = sprintf('%s expected `%s`, got `%s`', $ip, $expected, $actual);
        }

        echo "  {$ip}\n";
        echo "    期望: {$expected}\n";
        echo "    实际: {$actual}\n";
        echo "    状态: {$status}\n";
    };

    echo "1. IPv4 查询演示:\n";
    $ipv4Tests = [
        "113.117.234.114" => '中国广东省中山市【电信】',
        '61.142.118.231'  => '中国广东省中山市【电信】',
        '202.96.134.133'  => '中国广东省深圳市【电信】',
        '180.76.76.76'    => '中国北京北京市【百度】',
        '114.114.114.114' => '中国江苏省南京市',
        '223.5.5.5'       => '中国浙江省杭州市【阿里】'
    ];

    foreach ($ipv4Tests as $ip => $expected) {
        $runSimpleCase($ip, $expected);
    }

    echo "\n2. IPv6 查询演示:\n";
    $ipv6Tests = [
        '2400:3200::1'         => array('中国浙江省杭州市【阿里】', true),
        '2606:4700:4700::1111' => array('United KingdomEnglandLondon【Cloudflare, Inc.】', true),
        '2400:da00::6666'      => array('中国北京北京市【百度】', true),
        '::1'                  => array('回环地址', false)
    ];

    foreach ($ipv6Tests as $ip => $case) {
        $runSimpleCase($ip, $case[0], $case[1]);
    }

    echo "\n3. 详细信息查询:\n";
    $testIPs = array('61.142.118.231', '114.114.114.114', '2400:3200::1');
    foreach ($testIPs as $ip) {
        try {
            $info = $searcher->getIpInfo($ip);
            if ($info) {
                echo "  IP: {$info['ip']}\n";
                echo "    国家: {$info['country']}\n";
                echo "    省份: {$info['province']}\n";
                echo "    城市: {$info['city']}\n";
                echo "    ISP: {$info['isp']}\n";
                echo "    版本: {$info['version']}\n\n";
            }
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'IPv6 查询需要下载') !== false) {
                echo "  {$ip} => 跳过: 缺少 IPv6 数据库\n\n";
                continue;
            }

            echo "  {$ip} => 错误: " . $e->getMessage() . "\n\n";
        }
    }

    echo "4. 数据库加载状态:\n";
    $dbInfo = $searcher->getDatabaseInfo();
    echo "  IPv4已加载: " . ($dbInfo['v4_loaded'] ? '是' : '否') . "\n";
    echo "  IPv6已加载: " . ($dbInfo['v6_loaded'] ? '是' : '否') . "\n";
    echo "  自定义IPv4路径: " . ($dbInfo['custom_v4_path'] ?: '使用默认路径') . "\n";
    echo "  自定义IPv6路径: " . ($dbInfo['custom_v6_path'] ?: '使用默认路径') . "\n";

    echo "\n5. 性能统计:\n";
    $stats = $searcher->getStats();
    foreach ($stats as $key => $value) {
        echo "  {$key}: " . (is_bool($value) ? ($value ? '是' : '否') : $value) . "\n";
    }

    echo "\n6. 内存使用:\n";
    $memory = $searcher->getMemoryUsage();
    foreach ($memory as $key => $value) {
        echo "  {$key}: " . (is_bool($value) ? ($value ? '是' : '否') : $value) . "\n";
    }

    echo "\n7. 批量查询演示:\n";
    $batchExpected = array(
        '113.117.234.114' => '中国|广东省|中山市|电信|CN',
        '61.142.118.231'  => '中国|广东省|中山市|电信|CN',
        '202.96.134.133'  => '中国|广东省|深圳市|电信|CN',
        '180.76.76.76'    => '中国|北京|北京市|百度|CN',
        '114.114.114.114' => '中国|江苏省|南京市|0|CN',
        '223.5.5.5'       => '中国|浙江省|杭州市|阿里|CN',
        '::1'             => '',
    );

    if (!empty($dbInfo['v6_path'])) {
        $batchExpected = array_merge($batchExpected, array(
            '2400:3200::1'         => '中国|浙江省|杭州市|阿里|CN',
            '2606:4700:4700::1111' => 'United Kingdom|England|London|Cloudflare, Inc.|GB',
            '2400:da00::6666'      => '中国|北京|北京市|百度|CN',
        ));
    }

    $batchIPs = array_keys($batchExpected);
    $batchResults = $searcher->batchSearch($batchIPs);

    foreach ($batchExpected as $ip => $expected) {
        $actual = isset($batchResults[$ip]) ? $batchResults[$ip] : '';
        $status = $actual === $expected ? '通过' : '失败';
        if ($actual !== $expected) {
            $failures[] = sprintf('%s batch expected `%s`, got `%s`', $ip, $expected, $actual);
        }

        echo "  {$ip}\n";
        echo "    期望: " . ($expected === '' ? '<空字符串>' : $expected) . "\n";
        echo "    实际: " . ($actual === '' ? '<空字符串>' : $actual) . "\n";
        echo "    状态: {$status}\n";
    }

    if (!empty($failures)) {
        echo "\n演示用例校验失败：\n";
        foreach ($failures as $failure) {
            echo "  - {$failure}\n";
        }
        exit(1);
    }
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
}

echo "\n=== 演示完成 ===\n";
