<?php

/**
 * XDB 文件校验与上游工厂兼容测试
 *
 * 使用当前真实 XDB 数据库文件验证：
 * - Searcher 工厂同时兼容 int 版本号和 IPv4/IPv6 版本对象
 * - 自定义数据库路径放错 IPv4/IPv6 文件时能提前报出版本不匹配
 * - 截断或非 XDB 文件能被运行时校验拒绝
 */

require_once __DIR__ . '/../vendor/autoload.php';

$failures = array();
$skipped = array();

function xdbAssertSame($label, $expected, $actual)
{
    global $failures;
    if ($actual !== $expected) {
        $failures[] = sprintf('%s expected `%s`, got `%s`', $label, $expected, $actual);
    }
}

function xdbAssertExceptionContains($label, $needle, $callback)
{
    global $failures;
    try {
        $callback();
        $failures[] = sprintf('%s expected exception containing `%s`, got no exception', $label, $needle);
    } catch (Throwable $e) {
        if (strpos($e->getMessage(), $needle) === false) {
            $failures[] = sprintf('%s expected exception containing `%s`, got `%s`', $label, $needle, $e->getMessage());
        }
    }
}

$v4File = __DIR__ . '/../db/ip2region_v4.xdb';
$v6File = __DIR__ . '/../vendor/bin/ip2data/ip2region_v6.xdb';

if (!is_file($v4File)) {
    echo "XDB 校验测试失败：缺少内置 IPv4 数据库 {$v4File}\n";
    exit(1);
}

$v4Expected = '中国|广东省|中山市|电信|CN';

foreach (array('file', 'vectorIndex', 'content') as $cachePolicy) {
    try {
        $searcher = new Ip2Region($cachePolicy, $v4File);
        xdbAssertSame(
            'high-level cache policy ' . $cachePolicy,
            $v4Expected,
            $searcher->search('61.142.118.231')
        );
    } catch (Throwable $e) {
        $failures[] = 'high-level cache policy ' . $cachePolicy . ' threw `' . $e->getMessage() . '`';
    }
}

try {
    $searcher = ip2region\xdb\Searcher::newWithFileOnly(ip2region\xdb\IPv4::default(), $v4File);
    xdbAssertSame('factory object v4 file search', $v4Expected, $searcher->search('61.142.118.231'));
    $searcher->close();
} catch (Throwable $e) {
    $failures[] = 'factory object v4 file search threw `' . $e->getMessage() . '`';
}

try {
    $searcher = ip2region\xdb\Searcher::newWithFileOnly('4', $v4File);
    xdbAssertSame('factory numeric string v4 file search', $v4Expected, $searcher->search('61.142.118.231'));
    $searcher->close();
} catch (Throwable $e) {
    $failures[] = 'factory numeric string v4 file search threw `' . $e->getMessage() . '`';
}

try {
    $vectorIndex = ip2region\xdb\Util::loadVectorIndexFromFile($v4File);
    $searcher = ip2region\xdb\Searcher::newWithVectorIndex(ip2region\xdb\IPv4::default(), $v4File, $vectorIndex);
    xdbAssertSame('factory object v4 vector search', $v4Expected, $searcher->search('61.142.118.231'));
    $searcher->close();
} catch (Throwable $e) {
    $failures[] = 'factory object v4 vector search threw `' . $e->getMessage() . '`';
}

try {
    $content = ip2region\xdb\Util::loadContentFromFile($v4File);
    $searcher = ip2region\xdb\Searcher::newWithBuffer(ip2region\xdb\IPv4::default(), $content);
    xdbAssertSame('factory object v4 content search', $v4Expected, $searcher->search('61.142.118.231'));
    $searcher->close();
} catch (Throwable $e) {
    $failures[] = 'factory object v4 content search threw `' . $e->getMessage() . '`';
}

xdbAssertExceptionContains('invalid factory version', '无效的 IP 版本', function () use ($v4File) {
    ip2region\xdb\Searcher::newWithFileOnly(5, $v4File);
});

xdbAssertExceptionContains('invalid high-level ip bytes length', '无效的 IP 字节长度', function () use ($v4File) {
    $searcher = new Ip2Region('file', $v4File);
    $searcher->searchByBytes("\x01\x02\x03\x04\x05");
});

if (is_file($v6File)) {
    $v6Expected = '中国|浙江省|杭州市|阿里|CN';
    try {
        $searcher = ip2region\xdb\Searcher::newWithFileOnly(ip2region\xdb\IPv6::default(), $v6File);
        xdbAssertSame('factory object v6 file search', $v6Expected, $searcher->search('2400:3200::1'));
        $searcher->close();
    } catch (Throwable $e) {
        $failures[] = 'factory object v6 file search threw `' . $e->getMessage() . '`';
    }

    try {
        $searcher = ip2region\xdb\Searcher::newWithFileOnly('6', $v6File);
        xdbAssertSame('factory numeric string v6 file search', $v6Expected, $searcher->search('2400:3200::1'));
        $searcher->close();
    } catch (Throwable $e) {
        $failures[] = 'factory numeric string v6 file search threw `' . $e->getMessage() . '`';
    }

    xdbAssertExceptionContains('v4 path points to v6 database', '数据库版本不匹配', function () use ($v6File) {
        $searcher = new Ip2Region('file', $v6File);
        $searcher->search('61.142.118.231');
    });

    xdbAssertExceptionContains('v6 path points to v4 database', '数据库版本不匹配', function () use ($v4File) {
        $searcher = new Ip2Region('file', null, $v4File);
        $searcher->searchIPv6('2400:3200::1');
    });
} else {
    $skipped[] = '缺少 IPv6 数据库，跳过 IPv6 工厂和版本放反测试';
}

$corruptFile = tempnam(sys_get_temp_dir(), 'ip2region-corrupt-');
file_put_contents($corruptFile, '<html>not an xdb file</html>');

xdbAssertExceptionContains('corrupt xdb file', 'XDB 数据库校验失败', function () use ($corruptFile) {
    $searcher = new Ip2Region('file', $corruptFile);
    $searcher->search('61.142.118.231');
});

@unlink($corruptFile);

if (!empty($failures)) {
    echo "XDB 校验测试失败：\n";
    foreach ($failures as $failure) {
        echo "  - {$failure}\n";
    }
    exit(1);
}

foreach ($skipped as $message) {
    echo "XDB 校验测试跳过：{$message}\n";
}

echo "XDB 校验测试通过\n";
