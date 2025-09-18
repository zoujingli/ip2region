<?php
/**
 * IP2Region 快速性能测试
 * 
 * 简化的性能测试，专注于核心性能指标
 */

require_once 'vendor/autoload.php';

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
    echo "系统负载: " . implode(', ', array_map(function($v) { return round($v, 2); }, $load)) . "\n";
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
if (PHP_OS_FAMILY === 'Linux' && is_readable('/proc/cpuinfo')) {
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
} elseif (PHP_OS_FAMILY === 'Darwin') {
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

// 清理缓存
echo "清理缓存...\n";
\Ip2Region::clearPersistentCache();
\Ip2Region::clearCache();
echo "缓存已清理\n\n";

// 测试函数
function testPerformance($name, $callback, $methodInfo = '') {
    $start = microtime(true);
    $result = $callback();
    $time = round((microtime(true) - $start) * 1000, 2);
    echo "{$name}: {$time}ms";
    if ($methodInfo) {
        echo " ({$methodInfo})";
    }
    echo "\n";
    return $time;
}

// 1. 首次加载测试
echo "1. 首次加载测试:\n";
$firstLoadV4 = testPerformance("  IPv4首次加载", function() {
    $ip2region = new \Ip2Region();
    return $ip2region->simple('61.142.118.231');
}, "new Ip2Region() + simple()");

$firstLoadV6 = testPerformance("  IPv6首次加载", function() {
    $ip2region = new \Ip2Region();
    return $ip2region->simple('2001:4860:4860::8888');
}, "new Ip2Region() + simple()");

// 2. 缓存命中测试
echo "\n2. 缓存命中测试:\n";
$cacheHitV4 = testPerformance("  IPv4缓存命中", function() {
    $ip2region = new \Ip2Region();
    return $ip2region->simple('8.8.8.8');
}, "new Ip2Region() + simple() (使用缓存)");

$cacheHitV6 = testPerformance("  IPv6缓存命中", function() {
    $ip2region = new \Ip2Region();
    return $ip2region->simple('2400:3200::1');
}, "new Ip2Region() + simple() (使用缓存)");

// 3. 查询方法对比
echo "\n3. 查询方法对比:\n";
$ip2region = new \Ip2Region();
$simpleTime = testPerformance("  simple方法", function() use ($ip2region) {
    return $ip2region->simple('61.142.118.231');
}, "ip2region->simple()");

$searchTime = testPerformance("  search方法", function() use ($ip2region) {
    return $ip2region->search('61.142.118.231');
}, "ip2region->search()");

$memoryTime = testPerformance("  memorySearch方法", function() use ($ip2region) {
    return $ip2region->memorySearch('61.142.118.231');
}, "ip2region->memorySearch()");

// 4. 批量查询测试
echo "\n4. 批量查询测试:\n";
$batchTime = testPerformance("  批量查询(10个IP)", function() use ($ip2region) {
    $ips = ['61.142.118.231', '8.8.8.8', '114.114.114.114', '1.1.1.1', '223.5.5.5', 
            '2001:4860:4860::8888', '2400:3200::1', '2606:4700:4700::1111', '180.76.76.76', '202.96.134.133'];
    return $ip2region->batchSearch($ips);
}, "ip2region->batchSearch(10个IP)");

// 生成10000个测试IP
echo "  生成10000个测试IP...\n";
$testIps = [];
$baseIps = [
    '61.142.118.', '8.8.8.', '114.114.114.', '1.1.1.', '223.5.5.',
    '202.96.134.', '180.76.76.', '114.114.115.', '8.8.4.', '1.0.0.',
    '2001:4860:4860::', '2400:3200::', '2606:4700:4700::', '2400:da00::', '2001:db8::'
];

for ($i = 0; $i < 10000; $i++) {
    $baseIp = $baseIps[$i % count($baseIps)];
    if (strpos($baseIp, ':') !== false) {
        // IPv6
        $testIps[] = $baseIp . dechex($i % 65536);
    } else {
        // IPv4
        $testIps[] = $baseIp . ($i % 255);
    }
}

$batch10000Time = testPerformance("  批量查询(10000个IP)", function() use ($ip2region, $testIps) {
    return $ip2region->batchSearch($testIps);
}, "ip2region->batchSearch(10000个IP)");

// 5. 循环查询测试
echo "\n5. 循环查询测试:\n";
$loopTime = testPerformance("  循环查询(10000次)", function() use ($ip2region) {
    for ($i = 0; $i < 10000; $i++) {
        $ip2region->simple('61.142.118.231');
    }
    return 10000;
}, "10000次 ip2region->simple() 循环调用");

// 6. 缓存清理测试
echo "\n6. 缓存清理测试:\n";
$clearTime = testPerformance("  清理所有缓存", function() {
    \Ip2Region::clearCache();
    return \Ip2Region::clearPersistentCache();
}, "Ip2Region::clearCache() + clearPersistentCache()");

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
$v4Improvement = round(($firstLoadV4 - $cacheHitV4) / $firstLoadV4 * 100, 1);
$v6Improvement = round(($firstLoadV6 - $cacheHitV6) / $firstLoadV6 * 100, 1);

echo "首次加载 vs 缓存命中:\n";
echo "  IPv4: {$firstLoadV4}ms → {$cacheHitV4}ms (提升 {$v4Improvement}%)\n";
echo "  IPv6: {$firstLoadV6}ms → {$cacheHitV6}ms (提升 {$v6Improvement}%)\n";

echo "\n查询方法性能:\n";
echo "  simple: {$simpleTime}ms (ip2region->simple())\n";
echo "  search: {$searchTime}ms (ip2region->search())\n";
echo "  memorySearch: {$memoryTime}ms (ip2region->memorySearch())\n";

echo "\n批量处理性能:\n";
echo "  10个IP: {$batchTime}ms (ip2region->batchSearch())\n";
echo "  10000个IP: {$batch10000Time}ms (ip2region->batchSearch())\n";
echo "  10000次循环: {$loopTime}ms (10000次 ip2region->simple())\n";

// 计算QPS（每秒查询数）
$qps10 = round(10 / ($batchTime / 1000), 0);
$qps10000 = round(10000 / ($batch10000Time / 1000), 0);
$qps10000Loop = round(10000 / ($loopTime / 1000), 0);

echo "\nQPS性能:\n";
echo "  10个IP: {$qps10} QPS\n";
echo "  10000个IP: {$qps10000} QPS\n";
echo "  10000次循环: {$qps10000Loop} QPS\n";

echo "\n缓存管理性能:\n";
echo "  清理缓存: {$clearTime}ms (Ip2Region::clearCache() + clearPersistentCache())\n";

// 9. 测试环境总结
echo "\n9. 测试环境总结:\n";
echo "==================\n";
echo "测试时间: " . date('Y-m-d H:i:s') . "\n";
echo "测试环境: " . PHP_OS . " " . php_uname('r') . "\n";
echo "PHP版本: " . PHP_VERSION . "\n";
echo "内存限制: " . ini_get('memory_limit') . "\n";
echo "最终内存使用: " . $memory['current'] . "\n";
echo "峰值内存使用: " . $memory['peak'] . "\n";

// 计算性能评分
$performanceScore = 0;
if ($cacheHitV4 < 1) $performanceScore += 30;
elseif ($cacheHitV4 < 5) $performanceScore += 20;
elseif ($cacheHitV4 < 10) $performanceScore += 10;

if ($cacheHitV6 < 1) $performanceScore += 30;
elseif ($cacheHitV6 < 5) $performanceScore += 20;
elseif ($cacheHitV6 < 10) $performanceScore += 10;

if ($batchTime < 5) $performanceScore += 20;
elseif ($batchTime < 10) $performanceScore += 15;
elseif ($batchTime < 20) $performanceScore += 10;

if ($loopTime < 100) $performanceScore += 20;
elseif ($loopTime < 200) $performanceScore += 15;
elseif ($loopTime < 500) $performanceScore += 10;

echo "性能评分: {$performanceScore}/100\n";

if ($performanceScore >= 90) {
    echo "性能等级: 优秀 ⭐⭐⭐⭐⭐\n";
} elseif ($performanceScore >= 80) {
    echo "性能等级: 良好 ⭐⭐⭐⭐\n";
} elseif ($performanceScore >= 70) {
    echo "性能等级: 中等 ⭐⭐⭐\n";
} elseif ($performanceScore >= 60) {
    echo "性能等级: 及格 ⭐⭐\n";
} else {
    echo "性能等级: 需要优化 ⭐\n";
}

echo "\n=== 测试完成 ===\n";
?>
