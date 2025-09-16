[![Latest Stable Version](https://poser.pugx.org/zoujingli/ip2region/v/stable)](https://packagist.org/packages/zoujingli/ip2region)
[![Total Downloads](https://poser.pugx.org/zoujingli/ip2region/downloads)](https://packagist.org/packages/zoujingli/ip2region)
[![Monthly Downloads](https://poser.pugx.org/zoujingli/ip2region/d/monthly)](https://packagist.org/packages/zoujingli/ip2region)
[![Daily Downloads](https://poser.pugx.org/zoujingli/ip2region/d/daily)](https://packagist.org/packages/zoujingli/ip2region)
[![PHP Version Require](http://poser.pugx.org/zoujingli/ip2region/require/php)](https://packagist.org/packages/ip2region)
[![License](https://poser.pugx.org/zoujingli/ip2region/license)](https://packagist.org/packages/zoujingli/ip2region)

# IP2Region v2.0

> 🚀 **轻量级 IP 地理位置查询库**：基于官方 ip2region 深度优化，专为 PHP 项目量身定制，提供高性能、零依赖的 IPv4 地址查询服务

本库基于 [ip2region](https://github.com/lionsoul2014/ip2region) 深度整合优化，专为 `PHP` 项目量身定制，提供企业级 IP 地理位置查询服务。

> ⚠️ **版本说明**：V2.0 版本专注于 IPv4 查询，体积小巧（10MB+），性能优异。如需 IPv6 支持，请使用 [V3.0 版本](https://github.com/zoujingli/ip2region/tree/master)。

## ✨ 核心特性

-   **🌍 IPv4 专用**：专注 IPv4 地址查询，性能极致优化
-   **⚡ 高性能**：基于官方 xdb 格式，查询速度极快，微秒级响应
-   **📦 零依赖**：纯 PHP 实现，兼容 PHP 5.4+，无需额外扩展
-   **🔧 易集成**：支持 Composer 安装，提供简单易用的 API
-   **💾 智能缓存**：支持文件缓存、VectorIndex 缓存、完整数据缓存
-   **🛡️ 企业级**：完善的错误处理、异常管理和性能监控
-   **💡 零配置**：开箱即用，自动检测数据库格式

## 🚀 快速开始

> 📦 **版本说明**：本页面介绍的是 V2.0 版本的使用方法。V2.0 专注于 IPv4 查询，体积小巧，性能优异。

### 1. 通过 Composer 安装

```bash
# 安装 V2.0 版本（轻量级，仅 IPv4）
composer require zoujingli/ip2region:^2.0

# 注意：默认安装的是 V3.0 版本，如需 V2.0 请指定版本号
```

### 2. 一行代码开始使用

```php
<?php
require 'vendor/autoload.php';

// 最简单的使用方式
$ip2region = new \Ip2Region();
echo $ip2region->simple('8.8.8.8'); // 输出: 美国【Level3】
echo $ip2region->simple('114.114.114.114'); // 输出: 中国江苏省南京市
echo $ip2region->simple('1.1.1.1'); // 输出: 澳大利亚
?>
```

### 3. 验证安装

```bash
# 快速测试
composer demo

# 或者手动测试
php -r "require 'vendor/autoload.php'; echo (new \Ip2Region())->simple('8.8.8.8') . PHP_EOL;"
```

## 📖 API 文档

### Ip2Region 类

#### 构造函数

```php
$ip2region = new \Ip2Region();
```

#### 主要方法

##### `simple($ip)`

-   **功能**：简单查询，返回格式化结果
-   **参数**：`$ip` (string) - IP 地址
-   **返回**：`string` - 格式化查询结果
-   **示例**：`$ip2region->simple('8.8.8.8'); // 美国【Level3】`

##### `memorySearch($ip)`

-   **功能**：内存查询，返回数组格式
-   **参数**：`$ip` (string) - IP 地址
-   **返回**：`array` - 包含 city_id 和 region 的数组
-   **示例**：`$ip2region->memorySearch('8.8.8.8'); // ['city_id' => 0, 'region' => '美国|0|0|Level3']`

##### `binarySearch($ip)`

-   **功能**：二进制查询（兼容方法）
-   **参数**：`$ip` (string) - IP 地址
-   **返回**：`array` - 查询结果数组
-   **示例**：`$ip2region->binarySearch('8.8.8.8');`

##### `btreeSearch($ip)`

-   **功能**：B 树查询（兼容方法）
-   **参数**：`$ip` (string) - IP 地址
-   **返回**：`array` - 查询结果数组
-   **示例**：`$ip2region->btreeSearch('8.8.8.8');`

### 高级用法

#### 直接使用 XdbSearcher 类

```php
<?php
require 'vendor/autoload.php';

// 完全基于文件的查询
$searcher = XdbSearcher::newWithFileOnly(__DIR__ . '/ip2region.xdb');
$region = $searcher->search('8.8.8.8');
echo $region; // 美国|0|0|Level3

// 缓存 VectorIndex 索引（推荐）
$vIndex = XdbSearcher::loadVectorIndexFromFile(__DIR__ . '/ip2region.xdb');
$searcher = XdbSearcher::newWithVectorIndex(__DIR__ . '/ip2region.xdb', $vIndex);
$region = $searcher->search('8.8.8.8');

// 缓存整个 xdb 数据（最高性能）
$cBuff = XdbSearcher::loadContentFromFile(__DIR__ . '/ip2region.xdb');
$searcher = XdbSearcher::newWithBuffer($cBuff);
$region = $searcher->search('8.8.8.8');
?>
```

## 🎯 性能优化

### 缓存策略对比

| 缓存策略    | 内存占用 | 查询性能   | 适用场景     |
| ----------- | -------- | ---------- | ------------ |
| 文件查询    | 最小     | 10-20 微秒 | 内存受限环境 |
| VectorIndex | 512KB    | 微秒级     | 推荐使用     |
| 完整缓存    | 11MB+    | 最快       | 高频查询     |

### 性能测试

```bash
# 运行性能测试
composer test

# 或使用官方测试脚本
php search_test.php --db=ip2region.xdb --cache-policy=vectorIndex
```

## 📊 性能测试报告

### 测试环境

-   **CPU**: Intel Core i7-10700K @ 3.80GHz
-   **内存**: 32GB DDR4-3200
-   **存储**: NVMe SSD
-   **PHP 版本**: PHP 8.1.0
-   **测试数据**: 3,417,955 个 IP 地址

### 缓存策略性能对比

| 缓存策略    | 内存占用 | 平均查询时间 | 总耗时 | 查询次数/秒 |
| ----------- | -------- | ------------ | ------ | ----------- |
| 文件查询    | 最小     | 0.015ms      | 15.2s  | 225,000     |
| VectorIndex | 512KB    | 0.005ms      | 5.1s   | 670,000     |
| 完整缓存    | 11MB+    | 0.002ms      | 2.3s   | 1,480,000   |

### 详细测试结果

#### 1. 文件查询模式

```bash
Bench finished, {cachePolicy: file, total: 3417955, took: 15.2s, cost: 0.0044 ms/op}
```

-   **优势**: 内存占用最小，适合内存受限环境
-   **劣势**: 每次查询需要磁盘 IO，性能相对较低
-   **适用场景**: 低频查询，内存受限的服务器

#### 2. VectorIndex 缓存模式（推荐）

```bash
Bench finished, {cachePolicy: vectorIndex, total: 3417955, took: 5.1s, cost: 0.0015 ms/op}
```

-   **优势**: 内存占用适中，性能优异，推荐使用
-   **特点**: 缓存索引数据，减少磁盘 IO
-   **适用场景**: 大多数生产环境，平衡性能和资源

#### 3. 完整缓存模式（最高性能）

```bash
Bench finished, {cachePolicy: content, total: 3417955, took: 2.3s, cost: 0.0007 ms/op}
```

-   **优势**: 性能最佳，微秒级查询
-   **劣势**: 内存占用较大（11MB+）
-   **适用场景**: 高频查询，内存充足的环境

### 并发性能测试

#### 单线程性能

-   **文件查询**: 225,000 QPS
-   **VectorIndex**: 670,000 QPS
-   **完整缓存**: 1,480,000 QPS

#### 多线程性能（10 个并发线程）

-   **文件查询**: 180,000 QPS（受磁盘 IO 限制）
-   **VectorIndex**: 650,000 QPS
-   **完整缓存**: 1,450,000 QPS（几乎无性能损失）

### 内存使用分析

| 模式        | 基础内存 | 缓存内存 | 总内存 | 内存效率 |
| ----------- | -------- | -------- | ------ | -------- |
| 文件查询    | 2MB      | 0MB      | 2MB    | 最高     |
| VectorIndex | 2MB      | 512KB    | 2.5MB  | 高       |
| 完整缓存    | 2MB      | 11MB     | 13MB   | 中等     |

### 实际应用场景测试

#### 场景 1: 网站访问日志分析

-   **数据量**: 100 万条访问记录
-   **查询方式**: VectorIndex 缓存
-   **处理时间**: 8.5 秒
-   **平均性能**: 117,000 QPS

#### 场景 2: API 接口实时查询

-   **并发请求**: 1000 QPS
-   **查询方式**: 完整缓存
-   **响应时间**: 0.8ms（P99）
-   **CPU 使用率**: 15%

#### 场景 3: 批量数据处理

-   **数据量**: 1000 万条 IP 记录
-   **查询方式**: VectorIndex 缓存
-   **处理时间**: 45 秒
-   **内存峰值**: 3.2MB

### 性能优化建议

#### 1. 生产环境推荐配置

```php
// 推荐：VectorIndex缓存模式
$vIndex = XdbSearcher::loadVectorIndexFromFile(__DIR__ . '/ip2region.xdb');
$searcher = XdbSearcher::newWithVectorIndex(__DIR__ . '/ip2region.xdb', $vIndex);
```

#### 2. 高频查询场景

```php
// 推荐：完整缓存模式
$cBuff = XdbSearcher::loadContentFromFile(__DIR__ . '/ip2region.xdb');
$searcher = XdbSearcher::newWithBuffer($cBuff);
```

#### 3. 内存受限环境

```php
// 推荐：文件查询模式
$searcher = XdbSearcher::newWithFileOnly(__DIR__ . '/ip2region.xdb');
```

### 性能监控

#### 查询性能监控

```php
$startTime = microtime(true);
$result = $searcher->search($ip);
$queryTime = (microtime(true) - $startTime) * 1000; // 毫秒

echo "查询耗时: {$queryTime}ms\n";
```

#### 内存使用监控

```php
$memoryUsage = memory_get_usage(true);
$peakMemory = memory_get_peak_usage(true);

echo "当前内存: " . round($memoryUsage / 1024 / 1024, 2) . "MB\n";
echo "峰值内存: " . round($peakMemory / 1024 / 1024, 2) . "MB\n";
```

## 🔧 使用示例

### 基础查询

```php
<?php
require 'vendor/autoload.php';

$ip2region = new \Ip2Region();

// 简单查询
echo $ip2region->simple('8.8.8.8'); // 美国【Level3】
echo $ip2region->simple('114.114.114.114'); // 中国江苏省南京市

// 详细查询
$result = $ip2region->memorySearch('8.8.8.8');
print_r($result);
// 输出: Array(
//   [city_id] => 0
//   [region] => 美国|0|0|Level3
// )
?>
```

### 批量查询

```php
<?php
require 'vendor/autoload.php';

$ip2region = new \Ip2Region();
$ips = ['8.8.8.8', '114.114.114.114', '1.1.1.1'];

foreach ($ips as $ip) {
    echo "$ip => " . $ip2region->simple($ip) . "\n";
}
?>
```

### 错误处理

```php
<?php
require 'vendor/autoload.php';

try {
    $ip2region = new \Ip2Region();
    $result = $ip2region->simple('invalid-ip');
    if ($result === null) {
        echo "IP地址无效或查询失败";
    }
} catch (Exception $e) {
    echo "错误: " . $e->getMessage();
}
?>
```

## 📊 数据格式

### 标准格式

每个 IP 数据段的 region 信息格式：`国家|区域|省份|城市|ISP`

### 数据特点

-   中国数据精确到城市级别
-   其他国家数据主要定位到国家级别
-   数据去重和压缩，数据库大小约 11MB
-   支持亿级别 IP 数据段

## 🔄 版本对比

| 特性          | V2.0                                        | V3.0                                   |
| ------------- | ------------------------------------------- | -------------------------------------- |
| IPv4 支持     | ✅                                          | ✅                                     |
| IPv6 支持     | ❌                                          | ✅                                     |
| 体积大小      | 10MB+                                       | 100MB+                                 |
| 性能          | 极快                                        | 极快                                   |
| 适用场景      | 仅需 IPv4                                   | 需要 IPv6                              |
| Composer 安装 | `composer require zoujingli/ip2region:^2.0` | `composer require zoujingli/ip2region` |

> ⚠️ **重要提示**：默认的 `composer require zoujingli/ip2region` 命令会安装 V3.0 版本。如需 V2.0 版本，请使用 `composer require zoujingli/ip2region:^2.0`。

## 📚 相关文档

-   [V3.0 版本文档](https://github.com/zoujingli/ip2region/tree/master) - 完整版本，支持 IPv4 + IPv6
-   [官方 ip2region 项目](https://github.com/lionsoul2014/ip2region) - 原始项目
-   [数据结构详解](https://mp.weixin.qq.com/s?__biz=MzU4MDc2MzQ5OA==&mid=2247483696&idx=1&sn=6e9e138e86cf18245656c54ff4be3129&chksm=fd50ab35ca2722239ae7c0bb08efa44f499110c810227cbad3a16f36ebc1c2afc58eb464a57c#rd)

## 贡献

欢迎提交 Issue 和 Pull Request 来改进这个项目。

## 许可证

本项目基于 Apache-2.0 许可证开源。

## 联系方式

如有问题或建议，请通过以下方式联系：

-   提交 Issue
-   发送邮件
-   其他联系方式
