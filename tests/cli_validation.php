<?php

/**
 * 命令行退出码回归
 *
 * 在独立临时目录调用真实 CLI，验证参数错误、下载失败及损坏数据库返回非零状态。
 * IPv6 未安装仍允许跳过；下载失败通过禁用 URL 流复现，不访问网络。
 */

$root = dirname(__DIR__);
$fixture = sys_get_temp_dir() . '/ip2region-cli-' . bin2hex(random_bytes(8));
$failures = array();
$caseCount = 0;

function cliAssertExit($label, $arguments, $expected, $message = null)
{
    global $fixture, $failures, $caseCount;
    $parts = array(escapeshellarg(PHP_BINARY));
    foreach ($arguments as $argument) {
        $parts[] = escapeshellarg($argument);
    }
    $output = array();
    exec('cd ' . escapeshellarg($fixture) . ' && ' . implode(' ', $parts) . ' 2>&1', $output, $status);
    $caseCount++;
    if ($status !== $expected || ($message !== null && strpos(implode("\n", $output), $message) === false)) {
        $failures[] = sprintf('%s expected exit %d, got %d: %s', $label, $expected, $status, implode("\n", $output));
    }
}

try {
    foreach (array('src/ip2region/xdb', 'tests', 'bin', 'vendor/bin/ip2data', 'db') as $directory) {
        if (!is_dir($fixture . '/' . $directory) && !mkdir($fixture . '/' . $directory, 0755, true)) {
            throw new RuntimeException('无法创建 CLI 测试目录');
        }
    }

    $files = array_merge(
        glob($root . '/src/*.php'),
        glob($root . '/src/ip2region/xdb/*.php'),
        array($root . '/bin/ip2down', $root . '/composer.json', $root . '/tests/demo.php', $root . '/tests/quick_performance_test.php', $root . '/db/ip2region_v4.xdb')
    );
    foreach ($files as $file) {
        if (!copy($file, $fixture . substr($file, strlen($root)))) {
            throw new RuntimeException('无法复制 CLI 测试文件: ' . $file);
        }
    }

    // 使用临时目录内的真实类，避免 Composer 将查询器解析回工作区。
    $autoload = <<<'PHP'
<?php
spl_autoload_register(function ($name) {
    $file = dirname(__DIR__) . '/src/' . str_replace('\\', '/', $name) . '.php';
    if (is_file($file)) require_once $file;
});
require_once dirname(__DIR__) . '/src/common.php';
PHP;
    if (file_put_contents($fixture . '/vendor/autoload.php', $autoload) === false) {
        throw new RuntimeException('无法写入 CLI 测试自动加载器');
    }

    cliAssertExit('help', array('bin/ip2down', 'help'), 0);
    cliAssertExit('unknown command', array('bin/ip2down', 'invalid'), 1);
    cliAssertExit('invalid version', array('bin/ip2down', 'download', 'invalid'), 1, '无效的数据库版本');
    cliAssertExit('failed download', array('-d', 'allow_url_fopen=0', 'bin/ip2down', 'download', 'v4'), 1, '无法打开下载链接');
    cliAssertExit('empty download directory', array('bin/ip2down', 'list'), 0);
    cliAssertExit('optional IPv6 CLI', array('bin/ip2down', 'test'), 0, 'IPv4 数据库功能测试通过');
    cliAssertExit('optional IPv6 demo', array('tests/demo.php'), 0, '演示完成');
    cliAssertExit('optional IPv6 benchmark', array('tests/quick_performance_test.php'), 0, 'IPv6查询跳过');

    $v6File = $fixture . '/vendor/bin/ip2data/ip2region_v6.xdb';
    if (file_put_contents($v6File, '<html>not an xdb file</html>') === false) {
        throw new RuntimeException('无法创建损坏数据库样本');
    }
    cliAssertExit('corrupt IPv6 CLI', array('bin/ip2down', 'test'), 1, 'XDB 数据库校验失败');
    cliAssertExit('corrupt IPv6 list', array('bin/ip2down', 'list'), 1, '校验: 失败');
    cliAssertExit('corrupt IPv6 demo', array('tests/demo.php'), 1, 'XDB 数据库校验失败');
    cliAssertExit('corrupt IPv6 benchmark', array('tests/quick_performance_test.php'), 1, 'XDB 数据库校验失败');
} catch (Throwable $e) {
    $failures[] = $e->getMessage();
} finally {
    // 仅清理本次创建的测试目录，成功与失败均执行。
    if (is_dir($fixture)) {
        $entries = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($fixture, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($entries as $entry) {
            $removed = $entry->isDir() ? rmdir($entry->getPathname()) : unlink($entry->getPathname());
            if (!$removed) $failures[] = '无法清理 CLI 测试文件: ' . $entry->getPathname();
        }
        if (!rmdir($fixture)) $failures[] = '无法清理 CLI 测试目录: ' . $fixture;
    }
}

if (!empty($failures)) {
    echo "CLI 退出码测试失败：\n  - " . implode("\n  - ", $failures) . "\n";
    exit(1);
}

echo sprintf("CLI 退出码测试通过：%d 个场景\n", $caseCount);
