[![Latest Stable Version](https://poser.pugx.org/zoujingli/ip2region/v/stable)](https://packagist.org/packages/zoujingli/ip2region)
[![Total Downloads](https://poser.pugx.org/zoujingli/ip2region/downloads)](https://packagist.org/packages/zoujingli/ip2region)
[![Monthly Downloads](https://poser.pugx.org/zoujingli/ip2region/d/monthly)](https://packagist.org/packages/zoujingli/ip2region)
[![Daily Downloads](https://poser.pugx.org/zoujingli/ip2region/d/daily)](https://packagist.org/packages/zoujingli/ip2region)
[![PHP Version Require](https://poser.pugx.org/zoujingli/ip2region/require/php)](https://packagist.org/packages/zoujingli/ip2region)
[![License](https://poser.pugx.org/zoujingli/ip2region/license)](https://packagist.org/packages/zoujingli/ip2region)

# 🌍 ip2region for PHP

`zoujingli/ip2region` 是一个基于 [ip2region](https://github.com/lionsoul2014/ip2region) XDB 数据格式的 PHP IP 地理位置查询库。它提供函数式和面向对象两种调用方式，支持 IPv4、IPv6、批量查询、三种缓存策略和特殊用途地址识别。

> ✅ **IPv4 开箱即用**：仓库内置 `db/ip2region_v4.xdb`。
>
> ⚠️ **IPv6 按需启用**：普通 IPv6 查询需要额外准备 `ip2region_v6.xdb`。
>
> 🧩 **零额外扩展依赖**：PHP 7.1+ 即可使用。

## 🧭 快速导航

- 主要能力
- 安装
- 数据库准备
- 快速开始
- API 参考
- 特殊用途地址
- 命令行工具
- 测试与性能
- 故障排除

## ✨ 主要能力

| 图标  | 能力      | 说明                                      |
|-----|---------|-----------------------------------------|
| 🚀  | IPv4 查询 | 内置 `db/ip2region_v4.xdb`，安装后即可查询        |
| 🌐  | IPv6 查询 | 支持 IPv6 XDB，数据库按需下载或自定义传入               |
| 🔌  | 调用方式    | 支持 `ip2region()` 全局函数和 `Ip2Region` 类    |
| ⚙️  | 缓存策略    | 支持 `file`、`vectorIndex`、`content` 三种模式  |
| 🛡️ | 特殊地址    | `simple()` 优先识别私网、回环、链路本地、文档测试、组播、保留地址等 |
| 🧰  | 数据库工具   | 提供 `ip2down` 下载、查看、测试、清理数据库             |
| 🧩  | 运行环境    | PHP 7.1+，纯 PHP 实现，无额外扩展依赖               |

## 📦 安装

```bash
composer require zoujingli/ip2region:^3.0
```

源码仓库开发或验证时，先生成 Composer 自动加载文件：

```bash
composer install
```

## 🗄️ 数据库准备

### 🔎 数据库查找顺序

`Ip2Region` 会按以下顺序查找数据库文件：

1. **自定义路径**：构造函数传入的 `$dbPathV4`、`$dbPathV6`
2. **下载目录**：`vendor/bin/ip2data/ip2region_v4.xdb`、`vendor/bin/ip2data/ip2region_v6.xdb`
3. **包内默认目录**：`db/ip2region_v4.xdb`、`db/ip2region_v6.xdb`

IPv4 数据库已随包发布。IPv6 数据库默认不内置，需要下载到 `vendor/bin/ip2data/`、手动放入 `db/`，或通过构造函数提供绝对路径。

### 📥 下载 IPv6 数据库

推荐使用内置工具：

```bash
./vendor/bin/ip2down download v6
```

常用数据库命令：

```bash
# 下载 IPv6 数据库
composer download:v6

# 下载 IPv4 和 IPv6 数据库
composer download

# 查看已下载的数据库
./vendor/bin/ip2down list

# 测试数据库查询能力
./vendor/bin/ip2down test

# 清理 vendor/bin/ip2data/ 下的下载文件，不会删除包内 db/ 文件
./vendor/bin/ip2down clear
```

源码仓库中如果没有 `vendor/bin/ip2down`，可以直接运行：

```bash
php bin/ip2down download v6
```

也可以手动下载官方 XDB 文件：

```bash
mkdir -p db
curl -L -o db/ip2region_v6.xdb \
  "https://raw.githubusercontent.com/lionsoul2014/ip2region/master/data/ip2region_v6.xdb"
```

> 💡 如果直连 GitHub 不稳定，可以改用你自己的镜像或代理地址。请确认下载到的是 `.xdb` 文件，不是 HTML 错误页。

## 🚀 快速开始

### ⚡ 全局函数

```php
<?php
require 'vendor/autoload.php';

echo ip2region('61.142.118.231') . PHP_EOL;
// 中国广东省中山市【电信】

echo ip2region('61.142.118.231', 'search') . PHP_EOL;
// 中国|广东省|中山市|电信|CN

$result = ip2region('61.142.118.231', 'memory');
print_r($result);
// Array
// (
//     [city_id] => 0
//     [region] => 中国|广东省|中山市|电信|CN
// )
```

`ip2region()` 会先验证 IP 格式。非法 IP 会抛出 `Exception`；IPv6 数据库缺失时，普通 IPv6 查询也会抛出异常。生产代码建议使用 `try/catch`：

```php
try {
    echo ip2region('2400:3200::1') . PHP_EOL;
} catch (Exception $e) {
    echo '查询失败: ' . $e->getMessage() . PHP_EOL;
}
```

### 🧱 面向对象调用

```php
<?php
require 'vendor/autoload.php';

$searcher = new Ip2Region();

echo $searcher->simple('61.142.118.231') . PHP_EOL;
echo $searcher->search('61.142.118.231') . PHP_EOL;

$info = $searcher->getIpInfo('61.142.118.231');
print_r($info);

$batch = $searcher->batchSearch([
    '61.142.118.231',
    '114.114.114.114',
    '8.8.8.8',
]);
print_r($batch);
```

### 🧭 自定义数据库路径

```php
<?php
require 'vendor/autoload.php';

$searcher = new Ip2Region(
    'file',
    '/absolute/path/ip2region_v4.xdb',
    '/absolute/path/ip2region_v6.xdb'
);

echo $searcher->simple('61.142.118.231') . PHP_EOL;

$searcher->setCustomDbPaths('/absolute/path/new_v4.xdb', '/absolute/path/new_v6.xdb');
print_r($searcher->getDatabaseInfo());
```

## 📚 API 参考

### 🔹 `ip2region(string $ip, string $method = 'simple')`

全局便捷函数。`$method` 支持：

| 方法 | 返回值 | 说明 |
| --- | --- | --- |
| `simple` | `string|null` | 默认方法，返回友好显示文本；特殊用途地址会返回地址类型 |
| `search` | `string` | 返回 XDB 原始地区字符串，如 `中国\|广东省\|中山市\|电信\|CN` |
| `memory` | `array` | 返回 `['city_id' => 0, 'region' => '...']` |
| `binary` | `array` | 兼容旧版本，实际委托 `memorySearch()` |
| `btree` | `array` | 兼容旧版本，实际委托 `memorySearch()` |

非法 IP 会抛出 `Exception`。未知 `$method` 会按 `simple` 处理。

### 🔹 `Ip2Region` 构造函数

```php
new Ip2Region(string $cachePolicy = 'file', ?string $dbPathV4 = null, ?string $dbPathV6 = null)
```

| 缓存策略 | 说明 | 适用场景 |
| --- | --- | --- |
| `file` | 默认模式，按需读取 XDB 文件，内存占用低 | 低频查询、命令行脚本、内存敏感环境 |
| `vectorIndex` | 预加载向量索引，减少文件 IO | 常驻进程、频繁查询 |
| `content` | 将完整数据库加载到内存，查询时不再读取文件 | 高并发、内存充足的常驻服务 |

### 🔍 查询方法

| 方法 | 返回值 | 说明 |
| --- | --- | --- |
| `simple(string $ip)` | `string|null` | 友好格式，例如 `中国广东省中山市【电信】`；特殊地址优先返回地址类型 |
| `search(string $ip)` | `string` | 原始 XDB 地区字符串 |
| `memorySearch(string $ip)` | `array` | 返回 `city_id` 和 `region` |
| `binarySearch(string $ip)` | `array` | 兼容方法，委托 `memorySearch()` |
| `btreeSearch(string $ip)` | `array` | 兼容方法，委托 `memorySearch()` |
| `searchByBytes(string $ipBytes)` | `string` | 使用 `inet_pton()` 或 `Util::parseIP()` 得到的二进制 IP 查询 |
| `searchIPv6(string $ip)` | `string` | IPv6 专用查询，会验证 IPv6 格式 |
| `getIpInfo(string $ip)` | `array|null` | 返回 `country`、`province`、`city`、`isp`、`ip`、`version`，其中 `region` 字段保留但已弃用 |
| `batchSearch(array $ips)` | `array` | 批量查询，单个 IP 查询失败时该 IP 的结果为空字符串 |

### 📊 状态与配置方法

| 方法 | 说明 |
| --- | --- |
| `getStats()` | 当前内存、峰值内存、IO 次数、IPv4/IPv6 加载状态和缓存策略 |
| `getMemoryUsage()` | 人类可读的当前/峰值内存和加载状态 |
| `getIOCount()` | IPv4、IPv6 和总 IO 计数 |
| `getProtocolVersion(string $ip)` | 返回 `v4`、`v6` 或 `unknown` |
| `isIPv4Supported()` | 当前版本固定返回 `true` |
| `isIPv6Supported()` | 当前版本固定返回 `true`，但实际查询仍需要 IPv6 数据库 |
| `getDatabaseInfo()` | 数据库加载状态、缓存策略、自定义路径和实际查找路径 |
| `setCustomDbPaths(?string $v4Path = null, ?string $v6Path = null)` | 动态设置数据库路径，并重置已加载查询器 |
| `isUsingCustomDb()` | 返回 IPv4/IPv6 是否正在使用自定义数据库 |
| `getCustomDbInfo()` | 返回自定义数据库文件大小、修改时间等信息 |

### 🧬 底层 XDB 工厂

如果直接使用 `ip2region\xdb\Searcher`，工厂方法兼容两种版本参数：

```php
$searcher = ip2region\xdb\Searcher::newWithFileOnly(4, '/path/ip2region_v4.xdb');
$searcher = ip2region\xdb\Searcher::newWithFileOnly(ip2region\xdb\IPv4::default(), '/path/ip2region_v4.xdb');
```

`newWithVectorIndex()` 和 `newWithBuffer()` 同样支持 `4/6` 与 `IPv4::default()` / `IPv6::default()`。加载自定义 XDB 前可用 `ip2region\xdb\Util::verifyFromFile($file)` 做兼容性检查；高层 `Ip2Region` 会自动完成文件结构和 IP 版本校验。

## 🛡️ 特殊用途地址

`simple()` 会先识别 IANA/RFC 定义的特殊用途地址段，再访问 XDB 数据库。这样可以避免私网、回环、链路本地、文档测试、组播、保留地址等被数据库统一显示为不明确的地区。

```php
$searcher = new Ip2Region();

echo $searcher->simple('127.0.0.1') . PHP_EOL;      // 回环地址
echo $searcher->simple('192.168.1.1') . PHP_EOL;    // 私网地址
echo $searcher->simple('169.254.1.1') . PHP_EOL;    // 链路本地地址
echo $searcher->simple('192.0.2.1') . PHP_EOL;      // 文档测试地址
echo $searcher->simple('::1') . PHP_EOL;            // 回环地址
echo $searcher->simple('2001:db8::1') . PHP_EOL;    // 文档测试地址
echo $searcher->simple('fc00::1') . PHP_EOL;        // 唯一本地地址（私网地址）
```

> ✅ IPv6 特殊用途地址识别不依赖 IPv6 XDB 文件。
>
> ⚠️ 普通 IPv6 公网地址查询仍需要 IPv6 数据库。

## 🧰 命令行工具

### 🔎 查询与测试

```bash
# 运行演示
composer demo

# 运行演示、特殊用途地址和 XDB 校验测试
composer test

# 查询单个 IP
composer query 61.142.118.231

# 批量查询，逗号分隔
composer query:batch "61.142.118.231,114.114.114.114,8.8.8.8"

# 查看运行时统计
composer stats
```

### 🗃️ 数据库管理

```bash
# 下载全部数据库
composer download

# 只下载 IPv4
composer download:v4

# 只下载 IPv6
composer download:v6

# 使用下载工具查看、测试、清理
./vendor/bin/ip2down list
./vendor/bin/ip2down test
./vendor/bin/ip2down clear
```

## 🧪 测试与性能

```bash
composer validate --strict
composer test
composer performance
```

`composer performance` 会输出当前系统、PHP 版本、缓存命中、查询方法、批量查询、循环查询、QPS 和内存统计。结果会受机器性能、PHP 版本、缓存策略、数据库文件位置以及是否已准备 IPv6 数据库影响，README 不固定承诺某个具体数值。

`tests/demo.php` 中的公网地址期望值来自当前 XDB 的真实查询结果；更新数据库文件后如输出变化，应同步更新测试期望值。特殊用途地址测试使用库内 IANA/RFC 地址段识别规则。

## 🗂️ 项目结构

```text
ip2region/
├── bin/
│   └── ip2down                     # 数据库下载管理工具
├── db/
│   └── ip2region_v4.xdb            # 内置 IPv4 数据库
├── src/
│   ├── common.php                  # ip2region() 全局函数
│   ├── Ip2Region.php               # 主查询类
│   └── ip2region/xdb/
│       ├── IPv4.php
│       ├── IPv6.php
│       ├── Searcher.php
│       └── Util.php
├── tests/
│   ├── demo.php                    # 使用演示
│   ├── quick_performance_test.php  # 性能测试
│   ├── special_addresses.php       # 特殊用途地址测试
│   └── xdb_validation.php          # XDB 校验与工厂兼容测试
├── composer.json
└── README.md
```

## 📦 PHAR 环境

库会检测 `phar://` 路径并跳过项目根目录推断。PHAR 中可直接使用随包放入的 `db/ip2region_v4.xdb`；如需查询普通 IPv6 地址，请将 `ip2region_v6.xdb` 一并打包到 `db/`，或在运行时传入可读的自定义路径。

## ❓ 故障排除

### IPv6 查询提示需要下载数据库

普通 IPv6 地址需要 `ip2region_v6.xdb`。运行：

```bash
composer download:v6
```

或：

```bash
./vendor/bin/ip2down download v6
```

### 数据库文件不存在或不可读

检查文件名、路径和权限：

```bash
ls -lh db/ip2region_v4.xdb
ls -lh vendor/bin/ip2data/ip2region_v6.xdb
```

自定义路径建议使用绝对路径，并确认 PHP 进程有读取权限。

### XDB 数据库校验失败或版本不匹配

运行时会先校验 XDB 文件头和 IP 版本。常见错误包括下载到 HTML 错误页、文件被截断、IPv4/IPv6 文件路径填反。

```bash
./vendor/bin/ip2down list
./vendor/bin/ip2down test
```

`list` 会显示 XDB 结构版本、IP 版本、创建时间和指针字节数；如果校验失败，重新下载对应数据库：

```bash
composer download:v4
composer download:v6
```

### `content` 模式内存不足

`content` 会把完整数据库读入内存。内存受限时改用默认 `file` 模式：

```php
$searcher = new Ip2Region('file');
```

常驻服务频繁查询时，可以先用 `vectorIndex` 平衡内存和 IO：

```php
$searcher = new Ip2Region('vectorIndex');
```

### 示例输出和 README 略有差异

地区和运营商名称来自 XDB 数据库，数据库更新后输出可能变化。README 示例用于说明返回格式，不应作为固定断言；自动化断言请参考 `tests/demo.php`、`tests/special_addresses.php` 和 `tests/xdb_validation.php`。

## 🔗 相关链接

- [Packagist: zoujingli/ip2region](https://packagist.org/packages/zoujingli/ip2region)
- [官方 ip2region 项目](https://github.com/lionsoul2014/ip2region)
- [ip2region 官网](https://www.ip2region.net/)

## 🤝 贡献

欢迎通过 Issue 或 Pull Request 改进文档、示例和实现。提交前建议运行：

```bash
composer validate --strict
composer test
```

## 📄 许可证

本项目基于 Apache-2.0 许可证开源。

## 💖 赞助支持

- [PhpStorm](https://www.jetbrains.com/phpstorm/)：JetBrains 专业 PHP IDE
