[![Latest Stable Version](https://poser.pugx.org/zoujingli/ip2region/v/stable)](https://packagist.org/packages/zoujingli/ip2region)
[![Total Downloads](https://poser.pugx.org/zoujingli/ip2region/downloads)](https://packagist.org/packages/zoujingli/ip2region)
[![Monthly Downloads](https://poser.pugx.org/zoujingli/ip2region/d/monthly)](https://packagist.org/packages/zoujingli/ip2region)
[![Daily Downloads](https://poser.pugx.org/zoujingli/ip2region/d/daily)](https://packagist.org/packages/zoujingli/ip2region)
[![PHP Version Require](http://poser.pugx.org/zoujingli/ip2region/require/php)](https://packagist.org/packages/ip2region)
[![License](https://poser.pugx.org/zoujingli/ip2region/license)](https://packagist.org/packages/zoujingli/ip2region)

# ip2region v3.0

> 🚀 **企业级 IP 地理位置查询库**：基于官方 ip2region 深度优化，支持 IPv4/IPv6，分片文件管理，智能压缩，零依赖

本库基于 [ip2region](https://github.com/lionsoul2014/ip2region) 深度整合优化，专为 `PHP` 项目量身定制，提供企业级 IP 地理位置查询服务。

> ⚠️ **重要提示**：由于 V3.0 版本新增了 IPv6 数据库支持，尽管已进行智能压缩优化，但整体体积仍超过 100MB。如果您仅需 IPv4 查询功能，建议使用 V2 版本以获得更小的体积和更快的加载速度。

## 📦 版本选择

| 特性          | V2.0（轻量级）                    | V3.0（完整版）                        |
|-------------|------------------------------|----------------------------------|
| IPv4 支持     | ✅                           | ✅                               |
| IPv6 支持     | ❌                           | ✅                               |
| 体积大小      | 10MB+                       | 100MB+                           |
| 性能          | 极快                         | 极快                             |
| 适用场景      | 仅需 IPv4                    | 需要 IPv6 或企业级功能                |
| Composer 安装 | `composer require zoujingli/ip2region:^2.0` | `composer require zoujingli/ip2region:^3.0` |

> ⚠️ **重要提示**：默认的 `composer require zoujingli/ip2region` 命令会安装 V3.0 版本。如需 V2.0 版本，请使用 `composer require zoujingli/ip2region:^2.0`。

## 🎯 项目简介

ip2region 是一个高性能的IP地址定位库，支持IPv4和IPv6地址查询。通过智能分片和压缩技术，实现了大数据库文件的高效管理，为企业和开发者提供准确、快速的IP地理位置查询服务。

> 📖 **V2.0 版本文档**：如果您仅需 IPv4 查询功能，建议使用 [V2.0 版本](https://github.com/zoujingli/ip2region/tree/v2.0)，体积更小（10MB+），加载更快。

## ✨ 核心特性

- **🌍 双协议支持**：完整支持 IPv4 和 IPv6 地址查询，自动识别 IP 版本
- **⚡ 高性能**：基于官方 xdb 格式，查询速度极快，微秒级响应
- **📦 零依赖**：纯 PHP 实现，兼容 PHP 5.4+，无需额外扩展
- **🔧 易集成**：支持 Composer 安装，提供函数式和面向对象两种 API
- **💾 智能缓存**：支持文件缓存、VectorIndex 缓存、完整数据缓存
- **📊 分片管理**：大文件自动分片（<100MB），支持按需加载和合并
- **🗜️ 智能压缩**：支持 gzip、zip、zstd 多种压缩格式，压缩率高达 81%
- **🛡️ 企业级**：完善的错误处理、异常管理和性能监控
- **🔄 懒加载**：IPv4/IPv6 查询器按需创建，优化内存使用
- **💡 零配置**：开箱即用，自动检测数据库版本和格式

## 📁 项目结构

```
ip2region/
├── src/                    # 核心源码
│   ├── Ip2Region.php      # 主类，支持 IPv4/IPv6 双协议
│   ├── XdbSearcher.php    # 官方 xdb 查询器封装
│   └── ChunkedDbHelper.php # 分片文件管理助手
├── db/                    # 分片数据库文件（自动生成）
│   ├── ip2region_v4.xdb.part1    # IPv4 数据库分片
│   └── ip2region_v6.xdb.part*    # IPv6 数据库分片（多个文件）
├── tools/                 # 工具脚本
│   ├── split_db.php       # 数据库分片工具（重要！）
│   ├── ip2region_v4.xdb   # IPv4 完整数据库（需要下载）
│   └── ip2region_v6.xdb   # IPv6 完整数据库（需要下载）
├── tests/                 # 测试文件
│   └── demo.php           # 演示程序
├── function.php           # 全局函数入口
├── composer.json          # Composer 配置
└── README.md              # 项目文档
```

> **💡 重要提示**：
>
> - `db/` 目录中的分片文件是通过 `tools/split_db.php` 工具从 `tools/` 目录的完整数据库文件生成的
> - `tools/` 目录中的 `ip2region_v4.xdb` 和 `ip2region_v6.xdb` 是原始数据库文件，需要手动下载
> - 文件名必须严格按照 `ip2region_v4.xdb` 和 `ip2region_v6.xdb` 命名，不能有任何变化
> - 项目已包含分片文件，可直接使用，无需手动生成

## 🆕 v3.0 新增功能

### 智能分片管理

- **自动分片**：大文件自动分割为 <100MB 的小文件
- **压缩支持**：支持 gzip/zip 压缩，显著减小文件大小（压缩率可达 60-80%）
- **按需合并**：首次使用时自动解压并合并分片文件
- **智能缓存**：合并后的文件缓存到临时目录，避免重复解压合并
- **内存优化**：支持懒加载，IPv4/IPv6 查询器按需创建

### 增强的 API

- **双协议支持**：`ip2region()` 函数自动识别 IPv4/IPv6
- **面向对象**：`Ip2Region` 类提供完整的面向对象接口
- **批量查询**：`batchSearch()` 方法支持批量 IP 查询
- **性能监控**：`getStats()` 和 `getMemoryUsage()` 方法监控性能

### 企业级特性

- **错误处理**：完善的异常处理和错误提示
- **并发安全**：支持多进程/多线程安全使用
- **缓存策略**：支持文件、VectorIndex、完整数据三种缓存方式
- **PHP 5.4+ 兼容**：完全兼容 PHP 5.4 及以上版本

## 🚀 快速开始

### 1. 通过 Composer 安装

```bash
# 安装 V3.0 版本（推荐，功能完整）
composer require zoujingli/ip2region:^3.0

# 或安装 V2.0 版本（轻量级，仅 IPv4）
composer require zoujingli/ip2region:^2.0
```

### 2. 数据库文件准备

项目已包含分片数据库文件，可直接使用。如需使用自定义数据库：

```bash
# 下载完整数据库文件到 tools/ 目录
# 然后生成分片文件
php tools/split_db.php v4    # IPv4 分片
php tools/split_db.php v6    # IPv6 分片
```

### 3. 一行代码开始使用

```php
<?php
require 'vendor/autoload.php';

// 最简单的使用方式
echo ip2region('61.142.118.231') . "\n"; // 中国广东省中山市【电信】
echo ip2region('2001:4860:4860::8888') . "\n"; // 美国加利福尼亚州圣克拉拉【专线用户】

// 或者使用类方式
$ip2region = new \Ip2Region();
echo $ip2region->simple('61.142.118.231') . "\n"; // 中国广东省中山市【电信】
?>
```

### 4. 验证安装

```bash
# 测试 IPv4 查询
composer test:ipv4

# 测试 IPv6 查询
composer test:ipv6

# 运行演示程序
composer demo
```

## 在项目中快速调用

### 函数式调用

```php
<?php
require 'vendor/autoload.php';

// 简单查询
echo ip2region('61.142.118.231') . "\n";        // 中国广东省中山市【电信】
echo ip2region('2001:4860:4860::8888') . "\n"; // 美国加利福尼亚州圣克拉拉【专线用户】

// 批量查询
$ips = ['61.142.118.231', '114.114.114.114', '2001:4860:4860::8888'];
foreach ($ips as $ip) {
    echo "$ip => " . ip2region($ip) . "\n";
}
?>
```

### 面向对象调用

```php
<?php
require 'vendor/autoload.php';

try {
$ip2region = new \Ip2Region();

    // 基础查询
    echo $ip2region->simple('61.142.118.231') . "\n";
    echo $ip2region->search('2001:4860:4860::8888') . "\n";

    // 获取详细信息
    $info = $ip2region->getIpInfo('61.142.118.231');
    print_r($info);
    // 输出: Array(
    //   [country] => 中国
    //   [region] => 广东省
    //   [province] => 中山市
    //   [city] => 电信
    //   [isp] => 
    //   [ip] => 61.142.118.231
    //   [version] => v4
    // )

    // 批量查询
    $results = $ip2region->batchSearch(['61.142.118.231', '114.114.114.114']);
    print_r($results);

    // 性能监控
    $stats = $ip2region->getStats();
    echo "内存使用: " . $stats['memory_usage'] . " bytes\n";
    echo "IPv4 已加载: " . ($stats['v4_loaded'] ? '是' : '否') . "\n";
    echo "IPv6 已加载: " . ($stats['v6_loaded'] ? '是' : '否') . "\n";

} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
}
?>
```

## 数据库文件准备

### 使用预置数据库

项目已包含分片数据库文件，位于 `db/` 目录：

- `ip2region_v4.xdb.part1` - IPv4 数据库分片
- `ip2region_v6.xdb.part*` - IPv6 数据库分片（多个文件）

### 使用自定义数据库

如果需要使用自定义的数据库文件，请按以下步骤操作：

#### 1. 下载完整数据库文件

**重要**：必须将完整的 `.xdb` 文件放置到 `tools/` 目录，文件名必须完全匹配：

```
tools/
├── ip2region_v4.xdb    # IPv4 数据库文件（必须）
└── ip2region_v6.xdb    # IPv6 数据库文件（必须）
```

**文件要求**：

- **文件名**：必须严格按照 `ip2region_v4.xdb` 和 `ip2region_v6.xdb` 命名
- **文件位置**：必须放在 `tools/` 目录下
- **文件大小**：IPv4 约 11MB，IPv6 约 617MB
- **文件格式**：必须是有效的 xdb 格式文件

**获取数据库文件**：

- 从 [ip2region 官方仓库](https://github.com/lionsoul2014/ip2region) 下载
- 确保下载的是 `.xdb` 格式，不是 `.txt` 或其他格式

#### 2. 生成分片文件

使用项目提供的分片工具将大文件分割为小文件，支持压缩以减小文件大小：

```bash
# 基本用法（默认 100MB 分片，gzip 压缩）
php tools/split_db.php v4
php tools/split_db.php v6

# 自定义分片大小和压缩方式
php tools/split_db.php v4 50 gzip    # IPv4，50MB 分片，gzip 压缩
php tools/split_db.php v6 100 zip    # IPv6，100MB 分片，zip 压缩
php tools/split_db.php v4 100 none   # IPv4，100MB 分片，不压缩

# 使用绝对路径分割
php tools/split_db.php /path/to/ip2region_v4.xdb 100 gzip
php tools/split_db.php /path/to/ip2region_v6.xdb 100 zip

# 参数说明：
# 第一个参数：版本 (v4/v6) 或文件路径
# 第二个参数：分片大小限制（MB），默认 100
# 第三个参数：压缩方式 (gzip/zip/none)，默认 gzip
```

**压缩方式对比**：

- **gzip**：压缩率高，解压速度快，推荐使用
- **zip**：通用性好，兼容性强
- **none**：不压缩，文件较大但处理最快

#### 3. 分片文件说明

- **输入文件**：`tools/ip2region_v4.xdb` 或 `tools/ip2region_v6.xdb`
- **输出目录**：`db/` 目录
- **分片命名**：
    - 未压缩：`ip2region_v4.xdb.part1`, `ip2region_v4.xdb.part2`, ...
    - gzip 压缩：`ip2region_v4.xdb.part1.gz`, `ip2region_v4.xdb.part2.gz`, ...
    - zip 压缩：`ip2region_v4.xdb.part1.zip`, `ip2region_v4.xdb.part2.zip`, ...
- **分片大小**：默认 100MB，可通过参数调整
- **压缩支持**：支持 gzip、zip 压缩，显著减小文件大小
- **自动解压**：首次使用时自动解压并合并分片文件到临时缓存

#### 4. 实际使用示例

```bash
# 查看当前分片文件
$ ls -la db/ip2region_*.xdb.part*
-rw-r--r--  1 user  staff   11042429  Dec 19 10:00 db/ip2region_v4.xdb.part1
-rw-r--r--  1 user  staff  104857600  Dec 19 10:00 db/ip2region_v6.xdb.part1
-rw-r--r--  1 user  staff  104857600  Dec 19 10:00 db/ip2region_v6.xdb.part2
-rw-r--r--  1 user  staff  104857600  Dec 19 10:00 db/ip2region_v6.xdb.part3
-rw-r--r--  1 user  staff   17932583  Dec 19 10:00 db/ip2region_v6.xdb.part4

# 查看分片文件大小
$ du -h db/ip2region_*.xdb.part*
 11M    db/ip2region_v4.xdb.part1
100M    db/ip2region_v6.xdb.part1
100M    db/ip2region_v6.xdb.part2
100M    db/ip2region_v6.xdb.part3
 18M    db/ip2region_v6.xdb.part4

# 测试分片文件是否正常工作
$ composer test:ipv4
美国【Level3】

$ composer test:ipv6
美国加利福尼亚州圣克拉拉【专线用户】
```

## Composer 脚本

项目提供了便捷的 Composer 脚本命令：

```bash
# 运行演示程序
composer demo

# 测试 IPv4 查询
composer test:ipv4

# 测试 IPv6 查询
composer test:ipv6

# 查询指定 IP
composer query 61.142.118.231

# 分割 IPv4 数据库（gzip 压缩，需要先下载数据库文件）
composer split:v4

# 分割 IPv6 数据库（gzip 压缩，需要先下载数据库文件）
composer split:v6

# 使用 zip 压缩
composer split:v4:zip
composer split:v6:zip

# 不压缩
composer split:v4:none
composer split:v6:none
```

## API 参考

### 全局函数

#### `ip2region($ip)`

- **功能**：简单的 IP 查询函数
- **参数**：`$ip` (string) - IP 地址
- **返回**：`string|null` - 查询结果或 null
- **示例**：`echo ip2region('61.142.118.231'); // 中国广东省中山市【电信】`

### Ip2Region 类

#### 构造函数

```php
new Ip2Region($cachePolicy = 'file')
```

- **参数**：`$cachePolicy` (string) - 缓存策略：'file', 'vectorIndex', 'content'

#### 核心查询方法

##### `simple($ip)`

- **功能**：简单查询，返回格式化结果
- **参数**：`$ip` (string) - IP 地址
- **返回**：`string|null` - 格式化查询结果
- **示例**：`$ip2region->simple('61.142.118.231'); // 中国广东省中山市【电信】`

##### `search($ip)`

- **功能**：基础查询，返回原始结果
- **参数**：`$ip` (string) - IP 地址
- **返回**：`string|null` - 原始查询结果
- **示例**：`$ip2region->search('61.142.118.231'); // 中国|广东省|中山市|电信`

##### `memorySearch($ip)`

- **功能**：内存查询，返回数组格式
- **参数**：`$ip` (string) - IP 地址
- **返回**：`array` - 包含 city_id 和 region 的数组
- **示例**：`$ip2region->memorySearch('61.142.118.231'); // ['city_id' => 0, 'region' => '中国|广东省|中山市|电信']`

##### `batchSearch($ips)`

- **功能**：批量查询多个 IP
- **参数**：`$ips` (array) - IP 地址数组
- **返回**：`array` - IP 地址为键的查询结果数组
- **示例**：`$ip2region->batchSearch(['61.142.118.231', '114.114.114.114']);`

##### `getIpInfo($ip)`

- **功能**：获取详细的 IP 信息
- **参数**：`$ip` (string) - IP 地址
- **返回**：`array|null` - 包含 country, region, province, city, isp, ip, version 的数组
- **示例**：`$ip2region->getIpInfo('61.142.118.231');`

#### 兼容性方法

##### `binarySearch($ip)`

- **功能**：二进制搜索（兼容旧版本）
- **参数**：`$ip` (string) - IP 地址
- **返回**：`array` - 查询结果数组

##### `btreeSearch($ip)`

- **功能**：B 树搜索（兼容旧版本）
- **参数**：`$ip` (string) - IP 地址
- **返回**：`array` - 查询结果数组

##### `searchByBytes($ipBytes)`

- **功能**：二进制字节搜索
- **参数**：`$ipBytes` (string) - 二进制 IP 地址
- **返回**：`string|null` - 查询结果

#### 工具方法

##### `getStats()`

- **功能**：获取统计信息
- **返回**：`array` - 包含内存使用、IO 计数、加载状态等
- **示例**：`$stats = $ip2region->getStats();`

##### `getMemoryUsage()`

- **功能**：获取内存使用情况
- **返回**：`array` - 包含当前内存、峰值内存、加载状态等
- **示例**：`$memory = $ip2region->getMemoryUsage();`

##### `getIOCount()`

- **功能**：获取 IO 计数
- **返回**：`array` - 包含 IPv4、IPv6 和总 IO 计数
- **示例**：`$io = $ip2region->getIOCount();`

##### `getProtocolVersion($ip)`

- **功能**：获取 IP 协议版本
- **参数**：`$ip` (string) - IP 地址
- **返回**：`string` - 'v4', 'v6' 或 'unknown'

##### `isIPv4Supported()`

- **功能**：检查是否支持 IPv4
- **返回**：`bool` - 是否支持 IPv4

##### `isIPv6Supported()`

- **功能**：检查是否支持 IPv6
- **返回**：`bool` - 是否支持 IPv6

##### `getDatabaseInfo()`

- **功能**：获取数据库信息
- **返回**：`array` - 包含加载状态、缓存策略、版本信息等

#### 静态方法

##### `Ip2Region::clearCache()`

- **功能**：清理所有缓存
- **示例**：`Ip2Region::clearCache();`

##### `Ip2Region::clearExpiredCache($days = 7)`

- **功能**：清理过期缓存
- **参数**：`$days` (int) - 过期天数
- **示例**：`Ip2Region::clearExpiredCache(7);`

##### `Ip2Region::getCacheStats()`

- **功能**：获取缓存统计信息
- **返回**：`array` - 缓存统计信息
- **示例**：`$cacheStats = Ip2Region::getCacheStats();`

## 性能监控示例

```php
<?php
require 'vendor/autoload.php';

try {
    $ip2region = new \Ip2Region();

    // 查询前状态
    $statsBefore = $ip2region->getStats();
    echo "查询前内存使用: " . $statsBefore['memory_usage'] . " bytes\n";

    // 执行查询
    $result = $ip2region->simple('61.142.118.231');
    echo "查询结果: " . $result . "\n";

    // 查询后状态
    $statsAfter = $ip2region->getStats();
    echo "查询后内存使用: " . $statsAfter['memory_usage'] . " bytes\n";
    echo "IPv4 已加载: " . ($statsAfter['v4_loaded'] ? '是' : '否') . "\n";
    echo "IPv6 已加载: " . ($statsAfter['v6_loaded'] ? '是' : '否') . "\n";
    echo "IPv4 IO 次数: " . $statsAfter['v4_io_count'] . "\n";
    echo "IPv6 IO 次数: " . $statsAfter['v6_io_count'] . "\n";

    // 内存使用详情
    $memory = $ip2region->getMemoryUsage();
    echo "当前内存: " . $memory['current'] . "\n";
    echo "峰值内存: " . $memory['peak'] . "\n";

} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
}
?>
```

## 缓存管理示例

```php
<?php
require 'vendor/autoload.php';

// 获取缓存统计信息
$cacheStats = \Ip2Region::getCacheStats();
echo "缓存目录: " . $cacheStats['cache_dir'] . "\n";
echo "缓存文件数: " . $cacheStats['file_count'] . "\n";
echo "缓存总大小: " . $cacheStats['total_size'] . " bytes\n";
echo "内存缓存数: " . $cacheStats['memory_cached'] . "\n";

// 清理过期缓存（7天前）
\Ip2Region::clearExpiredCache(7);
echo "已清理7天前的过期缓存\n";

// 清理所有缓存
\Ip2Region::clearCache();
echo "已清理所有缓存\n";
?>
```

## 故障排除

### 常见问题

#### 1. 数据库文件不存在

**错误信息**：`数据库文件不存在: /path/to/ip2region_v4.xdb`
**解决方案**：

- **检查文件位置**：确保数据库文件存在于 `tools/` 目录下
- **检查文件名**：必须严格按照 `ip2region_v4.xdb` 和 `ip2region_v6.xdb` 命名
- **检查文件权限**：确保文件可读
- **下载数据库文件**：从官方仓库下载完整的 `.xdb` 文件
- **生成分片文件**：

  ```bash
  # 方法1：使用 Composer 脚本
  composer split:v4
  composer split:v6

  # 方法2：直接使用工具
  php tools/split_db.php v4
  php tools/split_db.php v6
  ```

**文件摆放检查**：

```bash
# 检查文件是否存在
ls -la tools/ip2region_v*.xdb

# 应该看到类似输出：
# -rw-r--r-- 1 user staff 11042429 Dec 19 10:00 tools/ip2region_v4.xdb
# -rw-r--r-- 1 user staff 617000000 Dec 19 10:00 tools/ip2region_v6.xdb
```

#### 2. 内存不足

**错误信息**：`Fatal error: Allowed memory size exhausted`
**解决方案**：

- 增加 PHP 内存限制：`ini_set('memory_limit', '256M');`
- 使用文件缓存策略：`new Ip2Region('file')`
- 定期清理缓存：`Ip2Region::clearCache()`

#### 3. 分片文件损坏

**错误信息**：`无法合并分割的IPv6数据库文件`
**解决方案**：

- 检查 `db/` 目录下的分片文件是否完整
- 重新分割数据库文件：`composer split:v6`
- 清理缓存后重试：`Ip2Region::clearCache()`

#### 4. 并发使用问题

**错误信息**：`Too many open files`
**解决方案**：

- 每个进程/线程创建独立的 `Ip2Region` 实例
- 增加系统文件描述符限制
- 使用内存缓存策略：`new Ip2Region('content')`

### 性能优化建议

1. **使用合适的缓存策略**：

    - 单次查询：使用 `file` 策略
    - 频繁查询：使用 `vectorIndex` 策略
    - 高并发：使用 `content` 策略

2. **批量查询优化**：

    - 使用 `batchSearch()` 方法进行批量查询
    - 避免在循环中重复创建实例

3. **内存管理**：

    - 定期清理过期缓存
    - 监控内存使用情况
    - 使用懒加载特性

4. **分片文件管理**：
    - 定期检查分片文件完整性
    - 使用 `getCacheStats()` 监控缓存状态
    - 合理设置分片大小（<100MB）
    - 使用压缩减少存储空间和传输时间：

        ```bash
        # 生成压缩分片文件（推荐）
        php tools/split_db.php v4 100 gzip    # IPv4，100MB 分片，gzip 压缩
        php tools/split_db.php v6 50 gzip     # IPv6，50MB 分片，gzip 压缩
        
        # 使用 zip 压缩（兼容性更好）
        php tools/split_db.php v4 100 zip     # IPv4，100MB 分片，zip 压缩
        php tools/split_db.php v6 50 zip      # IPv6，50MB 分片，zip 压缩

        # 检查分片文件
        ls -la db/ip2region_*.xdb.part*
        ```

5. **压缩优化建议**：
    - **gzip 压缩**：压缩率高（60-80%），解压速度快，推荐使用
    - **zip 压缩**：通用性好，兼容性强，适合跨平台使用
    - **无压缩**：处理最快，但文件较大，适合本地使用
    - **分片大小**：建议 50-100MB，平衡压缩效果和处理速度

## 更新日志

### v3.0.0 (2025-09-15) 🚀

#### 🎯 企业级优化

- 重构架构，支持分片文件自动管理
- **压缩支持**：支持 gzip/zip 压缩，文件大小减少 60-80%
- 智能缓存机制，避免重复解压合并文件
- 懒加载设计，IPv4/IPv6 查询器按需创建

#### 🚀 性能提升

- 微秒级查询速度
- 内存使用优化至 <8MB
- 支持三种缓存策略：file、vectorIndex、content

#### 🆕 新增功能

- 批量查询支持：`batchSearch()` 方法
- 性能监控：`getStats()` 和 `getMemoryUsage()` 方法
- 详细 IP 信息：`getIpInfo()` 方法
- 缓存管理：`clearCache()` 和 `clearExpiredCache()` 方法

#### 🛡️ 企业级特性

- 完善的错误处理和异常管理
- 支持多进程/多线程安全使用
- 完全兼容 PHP 5.4+ 版本
- 零依赖，纯 PHP 实现

#### 📱 IPv6 支持

- 完整支持 IPv6 地址查询
- 自动识别 IP 版本
- 分片文件管理优化大文件处理

#### 🔧 开发友好

- 统一 API 设计
- 函数式和面向对象两种使用方式
- 完整的 API 文档和示例
- Composer 脚本支持

## 许可证

本项目基于 Apache-2.0 许可证开源。

## 🔧 通用查询函数

IP2Region 提供了通用的 `ip2region()` 函数，支持多种查询方法：

### 函数签名

```php
ip2region(string $ip, string $method = 'simple'): string|array|null
```

### 支持的查询方法

| 方法       | 描述       | 返回值         | 示例                   |
|----------|----------|-------------|----------------------|
| `simple` | 简单查询（默认） | 格式化的地理位置字符串 | `"美国【Level3】"`       |
| `search` | 详细查询     | 管道分隔的详细信息   | `"美国\|0\|0\|Level3"` |
| `binary` | 二进制查询    | 原始二进制数据     | 二进制字符串               |
| `btree`  | B树查询     | B树索引查询结果    | 查询结果字符串              |
| `memory` | 内存查询     | 内存中的查询结果    | 查询结果字符串              |

### 使用示例

```php
<?php
require 'vendor/autoload.php';

// 简单查询（默认方法）
echo ip2region('61.142.118.231'); // 输出: 中国广东 省中山市【电信】 // 详细查询
echo ip2region('61.142.118.231', 'sea rch'); //  输出: 中国|广东省|中山市|电信

// IPv6查询
e cho ip2re  gion('2001:4860:4860::8888'); / / 输出: 美国加利 福尼亚州圣克拉拉【专线用户】

// 异常安全
$result = ip2region('invalid-ip'); // 返回: null
if ($result === null) {
    echo "IP地址无效或查询失败";
}
```

### 特性说明

- **自动识别**：自动识别IPv4和IPv6地址
- **分片支持**：自动处理分片数据库文件
- **智能缓存**：内置缓存机制，提升查询性能
- **异常安全**：查询失败返回null，不会抛出异常
- **静态实例**：使用静态实例，避免重复初始化

## 🔄 版本切换

如果您需要从 V3.0 切换到 V2.0 或反之，请按以下步骤操作：

### 从 V3.0 切换到 V2.0

```bash
# 卸载 V3.0 版本
composer remove zoujingli/ip2region

# 安装 V2.0 版本
composer require zoujingli/ip2region:^2.0
```

### 从 V2.0 升级到 V3.0

```bash
# 升级到 V3.0 版本
composer require zoujingli/ip2region:^3.0
```

> ⚠️ **注意事项**：
> - V2.0 仅支持 IPv4 查询，升级到 V3.0 后可使用 IPv6 功能
> - V2.0 体积 10MB+，V3.0 体积 100MB+，请确保服务器有足够空间
> - 代码兼容性：V2.0 的 API 在 V3.0 中完全兼容

## 📚 相关文档

- [V2.0 版本文档](https://github.com/zoujingli/ip2region/tree/v2.0) - 轻量级版本，仅支持 IPv4
- [V3.0 版本文档](https://github.com/zoujingli/ip2region/tree/master) - 完整版本，支持 IPv4 + IPv6
- [官方 ip2region 项目](https://github.com/lionsoul2014/ip2region) - 原始项目

## 贡献

欢迎提交 Issue 和 Pull Request 来改进这个项目。

## 联系方式

如有问题或建议，请通过以下方式联系：

- 提交 Issue
- 发送邮件
- 其他联系方式
