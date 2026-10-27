<?php
/**
 * IP2Region 快速性能测试
 *
 * 使用默认 file 策略测量首次查询、同实例再次查询及批量查询。
 * 先验证真实样本可查询；IPv6 未安装时跳过，查询异常或空结果使测试失败。
 */

set_exception_handler(function (Throwable $e) {
    fwrite(STDERR, '性能测试失败: ' . $e->getMessage() . PHP_EOL);
    exit(1);
});

require_once __DIR__ . '/../vendor/autoload.php';

echo "=== IP2Region 快速性能测试 ===\n\n";

// 显示测试环境信息
echo "测试环境信息:\n";
echo "==================\n";
echo "操作系统: " . PHP_OS . " " . php_uname('r') . "\n";
echo "PHP版本: " . PHP_VERSION . "\n";
echo "内存限制: " . ini_get('memory_limit') . "\n";
echo "最大执行时间: " . ini_get('max_execution_time') . "秒\n";
echo "时区: " . date_default_timezone_get() . "\n";
echo "当前时间: " . date('Y-m-d H:i:s') . "\n";

// 获取系统信息
if (function_exists('sys_getloadavg')) {
    $load = sys_getloadavg();
    echo "系统负载: " . implode(', ', array_map(function ($v) {
            return round($v, 2);
        }, $load)) . "\n";
}

// 获取内存使用情况
$memoryUsage = memory_get_usage(true);
$peakMemory = memory_get_peak_usage(true);
echo "当前内存使用: " . round($memoryUsage / 1024 / 1024, 2) . "MB\n";
echo "峰值内存使用: " . round($peakMemory / 1024 / 1024, 2) . "MB\n";

// 获取磁盘空间信息
$diskFree = disk_free_space('.');
$diskTotal = disk_total_space('.');
if ($diskFree !== false && $diskTotal !== false) {
    echo "磁盘空间: " . round($diskFree / 1024 / 1024 / 1024, 2) . "GB 可用 / " . round($diskTotal / 1024 / 1024 / 1024, 2) . "GB 总计\n";
}

// 获取CPU信息（如果可用）
if (PHP_OS === 'Linux' && is_readable('/proc/cpuinfo')) {
    $cpuInfo = file_get_contents('/proc/cpuinfo');
    if ($cpuInfo) {
        preg_match('/model name\s*:\s*(.+)/', $cpuInfo, $matches);
        if (isset($matches[1])) {
            echo "CPU: " . trim($matches[1]) . "\n";
        }
        preg_match_all('/processor\s*:\s*\d+/', $cpuInfo, $matches);
        if (isset($matches[0])) {
            echo "CPU核心数: " . count($matches[0]) . "\n";
        }
    }
} elseif (PHP_OS === 'Darwin') {
    $cpuInfo = shell_exec('sysctl -n machdep.cpu.brand_string 2>/dev/null');
    if ($cpuInfo) {
        echo "CPU: " . trim($cpuInfo) . "\n";
    }
    $cores = shell_exec('sysctl -n hw.ncpu 2>/dev/null');
    if ($cores) {
        echo "CPU核心数: " . trim($cores) . "\n";
    }
}

echo "\n";

// 创建测试实例
$searcher = new \Ip2Region();
echo "测试环境已准备\n\n";

// 显示数据库状态
echo "数据库状态信息:\n";
$dbInfo = $searcher->getDatabaseInfo();
echo "  IPv4已加载: " . ($dbInfo['v4_loaded'] ? '是' : '否') . "\n";
echo "  IPv6已加载: " . ($dbInfo['v6_loaded'] ? '是' : '否') . "\n";
echo "  缓存策略: " . $dbInfo['cache_policy'] . "\n\n";

// 测试函数
function testPerformance($name, $callback, $methodInfo = '')
{
    $start = microtime(true);
    $callback();
    $time = (microtime(true) - $start) * 1000;
    echo $name . ': ' . round($time, 4) . 'ms';
    if ($methodInfo) {
        echo " ({$methodInfo})";
    }
    echo "\n";
    return $time;
}

function repeatSamples(array $samples, $targetCount)
{
    $result = array();
    $sampleCount = count($samples);
    for ($i = 0; $i < $targetCount; $i++) {
        $result[] = $samples[$i % $sampleCount];
    }
    return $result;
}

$ipv4Samples = array(
    '113.117.234.114',
    '61.142.118.231',
    '202.96.134.133',
    '180.76.76.76',
    '114.114.114.114',
    '223.5.5.5',
);

$ipv6Samples = array();
if (!empty($dbInfo['v6_path'])) {
    $ipv6Samples = array(
        '2400:3200::1',
        '2606:4700:4700::1111',
        '2400:da00::6666',
    );
}

$performanceIpPool = array_merge($ipv4Samples, $ipv6Samples);
$batchSampleIps = repeatSamples($performanceIpPool, 10);

$expectedRegions = array();
foreach ($performanceIpPool as $ip) {
    $region = $searcher->search($ip);
    if ($region === '') {
        throw new RuntimeException("性能样本查询返回空结果: {$ip}");
    }
    $expectedRegions[$ip] = $region;
}
echo '样本预检通过: ' . count($expectedRegions) . " 个IP\n";

// 1. 首次加载测试
echo "1. 首次加载测试:\n";
$v4Searcher = new \Ip2Region();
$firstLoadV4 = testPerformance("  IPv4首次查询", function () use ($v4Searcher) {
    return $v4Searcher->simple('61.142.118.231');
}, "simple() 含数据库加载");

$firstLoadV6 = null;
if (!empty($ipv6Samples)) {
    $v6Searcher = new \Ip2Region();
    $firstLoadV6 = testPerformance("  IPv6首次查询", function () use ($v6Searcher) {
        return $v6Searcher->simple('2400:3200::1');
    }, "simple() 含数据库加载");
} else {
    echo "  IPv6查询跳过: 缺少 IPv6 数据库\n";
}

// 2. 复用已加载查询器，file 模式仍会读取数据库
echo "\n2. 同实例再次查询:\n";
$repeatV4 = testPerformance("  IPv4再次查询", function () use ($v4Searcher) {
    return $v4Searcher->simple('61.142.118.231');
}, "复用同一实例和IP");

$repeatV6 = null;
if ($firstLoadV6 !== null) {
    $repeatV6 = testPerformance("  IPv6再次查询", function () use ($v6Searcher) {
        return $v6Searcher->simple('2400:3200::1');
    }, "复用同一实例和IP");
}

// 3. 查询方法对比
echo "\n3. 查询方法对比:\n";
$ip2region = $searcher;
$simpleTime = testPerformance("  simple方法", function () use ($ip2region) {
    return $ip2region->simple('61.142.118.231');
}, "ip2region->simple()");

$searchTime = testPerformance("  search方法", function () use ($ip2region) {
    return $ip2region->search('61.142.118.231');
}, "ip2region->search()");

$memoryTime = testPerformance("  memorySearch方法", function () use ($ip2region) {
    return $ip2region->memorySearch('61.142.118.231');
}, "ip2region->memorySearch()");

// 4. 批量查询测试
echo "\n4. 批量查询测试:\n";
$batchTime = testPerformance("  批量查询(10次)", function () use ($ip2region, $batchSampleIps, $expectedRegions) {
    $results = $ip2region->batchSearch($batchSampleIps);
    foreach ($batchSampleIps as $ip) {
        if (!isset($results[$ip]) || $results[$ip] !== $expectedRegions[$ip]) {
            throw new RuntimeException("批量查询结果不匹配: {$ip}");
        }
    }
    return $results;
}, "batchSearch() 输入10项，含重复IP");

// 重复当前 XDB 已验证的真实样本池，避免拼接构造不存在的测试地址。
echo "  真实样本池: " . count($performanceIpPool) . " 个IP，重复生成10000次查询...\n";
$testIps = repeatSamples($performanceIpPool, 10000);

$batch10000Time = testPerformance("  批量查询(10000次)", function () use ($ip2region, $testIps, $expectedRegions) {
    $results = $ip2region->batchSearch($testIps);
    if ($results !== $expectedRegions) {
        throw new RuntimeException('批量查询结果与样本预检不一致');
    }
    return $results;
}, "batchSearch() 输入10000项，含重复IP");

// 5. 循环查询测试
echo "\n5. 循环查询测试:\n";
$loopTime = testPerformance("  循环查询(10000次)", function () use ($ip2region) {
    for ($i = 0; $i < 10000; $i++) {
        $ip2region->simple('61.142.118.231');
    }
    return 10000;
}, "10000次 ip2region->simple() 循环调用");

// 6. 运行时统计读取
echo "\n6. 运行时统计读取:\n";
$statsTime = testPerformance("  获取运行时统计", function () use ($ip2region) {
    return $ip2region->getStats();
}, "getStats() 性能监控");

// 7. 内存使用统计
echo "\n7. 内存使用统计:\n";
$stats = $ip2region->getStats();
$memory = $ip2region->getMemoryUsage();
echo "  当前内存: " . $memory['current'] . "\n";
echo "  峰值内存: " . $memory['peak'] . "\n";
echo "  IPv4已加载: " . ($stats['v4_loaded'] ? '是' : '否') . "\n";
echo "  IPv6已加载: " . ($stats['v6_loaded'] ? '是' : '否') . "\n";

// 8. 性能总结
echo "\n8. 性能总结:\n";
echo "==================\n";
echo "首次查询 vs 同实例再次查询:\n";
printf("  IPv4: %.4fms → %.4fms\n", $firstLoadV4, $repeatV4);
if ($firstLoadV6 !== null) {
    printf("  IPv6: %.4fms → %.4fms\n", $firstLoadV6, $repeatV6);
} else {
    echo "  IPv6: 跳过\n";
}

echo "\n查询方法性能:\n";
printf("  simple: %.4fms (ip2region->simple())\n", $simpleTime);
printf("  search: %.4fms (ip2region->search())\n", $searchTime);
printf("  memorySearch: %.4fms (ip2region->memorySearch())\n", $memoryTime);

echo "\n批量处理性能:\n";
printf("  10次查询: %.4fms (ip2region->batchSearch())\n", $batchTime);
printf("  10000次查询: %.4fms (ip2region->batchSearch())\n", $batch10000Time);
printf("  10000次循环: %.4fms (10000次 ip2region->simple())\n", $loopTime);

// 计算QPS（每秒查询数）
$qps10 = $batchTime > 0 ? round(10000 / $batchTime, 0) : 'N/A';
$qps10000 = $batch10000Time > 0 ? round(10000000 / $batch10000Time, 0) : 'N/A';
$qps10000Loop = $loopTime > 0 ? round(10000000 / $loopTime, 0) : 'N/A';

echo "\nQPS性能:\n";
echo "  10次查询: {$qps10} QPS\n";
echo "  10000次查询: {$qps10000} QPS\n";
echo "  10000次循环: {$qps10000Loop} QPS\n";

echo "\n性能监控:\n";
printf("  性能监控: %.4fms (getStats() 性能监控)\n", $statsTime);

// 9. 测试环境总结
echo "\n9. 测试环境总结:\n";
echo "==================\n";
echo "测试时间: " . date('Y-m-d H:i:s') . "\n";
echo "测试环境: " . PHP_OS . " " . php_uname('r') . "\n";
echo "PHP版本: " . PHP_VERSION . "\n";
echo "内存限制: " . ini_get('memory_limit') . "\n";
echo "最终内存使用: " . $memory['current'] . "\n";
echo "峰值内存使用: " . $memory['peak'] . "\n";

// 显示最终数据库状态
$finalDbInfo = $searcher->getDatabaseInfo();
echo "最终数据库状态:\n";
echo "  IPv4已加载: " . ($finalDbInfo['v4_loaded'] ? '是' : '否') . "\n";
echo "  IPv6已加载: " . ($finalDbInfo['v6_loaded'] ? '是' : '否') . "\n";
echo "  缓存策略: " . $finalDbInfo['cache_policy'] . "\n";

echo "\n=== 测试完成 ===\n";
