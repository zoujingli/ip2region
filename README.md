[![Latest Stable Version](https://poser.pugx.org/zoujingli/ip2region/v/stable)](https://packagist.org/packages/zoujingli/ip2region)
[![Total Downloads](https://poser.pugx.org/zoujingli/ip2region/downloads)](https://packagist.org/packages/zoujingli/ip2region)
[![Monthly Downloads](https://poser.pugx.org/zoujingli/ip2region/d/monthly)](https://packagist.org/packages/zoujingli/ip2region)
[![Daily Downloads](https://poser.pugx.org/zoujingli/ip2region/d/daily)](https://packagist.org/packages/zoujingli/ip2region)
[![PHP Version Require](http://poser.pugx.org/zoujingli/ip2region/require/php)](https://packagist.org/packages/ip2region)
[![License](https://poser.pugx.org/zoujingli/ip2region/license)](https://packagist.org/packages/zoujingli/ip2region)

# ip2region v3.0

🚀 **企业级 IP 地理位置查询库**：**支持 IPv4 和 IPv6**，智能加载，零依赖，**开箱即用**。

基于官方 [ip2region](https://github.com/lionsoul2014/ip2region) 深度优化，专为 PHP 项目定制，提供毫秒级 IP 地理位置查询服务。支持 IPv4 和 IPv6 双协议查询，具备智能加载机制和错误处理，**需要 PHP 7.1+**，全面使用类型声明提升代码质量和 IDE 支持，适用于企业级应用场景。

> ⚠️ **重要提示**：
> - **IPv4 查询**：✅ 开箱即用，无需下载
> - **IPv6 查询**：⚠️ **需要下载完整数据库**（约 36.09MB / 34.42MiB / 36086712 bytes），请使用 `./vendor/bin/ip2down download v6`（下载到 `vendor/bin/ip2data/` 后自动生效），或手动下载到 `db/` 或通过自定义路径提供

> 📢 **v3.0 更新**：
> - ✅ **代码优化**：工具代码减少 48.7%，更简洁高效
> - ✅ **开箱即用**：IPv4 查询无需下载，直接使用内置数据库文件
> - ✅ **按需下载**：IPv6 查询需要时再下载完整数据库
> - ✅ **多种缓存策略**：支持 file、vectorIndex、content 三种缓存模式
> - ✅ **简化部署**：直接使用未压缩的 xdb 文件，无需解压过程
> - ✅ **PHP 7.1+ 优化**：全面使用类型声明，提升代码质量和 IDE 支持
> - ✅ **字段映射修复**：修复 getIpInfo() 字段错位问题，region 字段已弃用

> 💡 **版本选择建议**：
> - **V3.0**：推荐使用，IPv4 开箱即用，IPv6 按需下载，自动缓存，代码更简洁

## 📦 核心特性

| 特性          | 描述                                        |
| ------------- | ------------------------------------------- |
| **IPv4 支持** | ✅ 开箱即用，内置数据库文件                  |
| **IPv6 支持** | ⚠️ 需要下载完整数据库（约 36.09MB / 34.42MiB / 36086712 bytes） |
| **PHP 7.1+**  | ✅ 需要 PHP 7.1+，全面使用类型声明          |
| 缓存策略      | ✅ 支持 file、vectorIndex、content 三种模式  |
| 性能          | ✅ 极快，微秒级响应                         |
| 零依赖        | ✅ 纯 PHP 实现，无需额外扩展                |
| 企业级        | ✅ 完善的错误处理和性能监控                  |

## 🎯 项目简介

ip2region 是一个高性能的 IP 地址定位库，**支持 IPv4 和 IPv6 地址查询**。通过多种缓存策略，实现了大数据库文件的高效管理，为企业和开发者提供准确、快速的 IP 地理位置查询服务。

**V3.0 核心特性**：
- 🚀 **开箱即用**：IPv4 查询无需下载，直接使用内置数据库文件
- ⚠️ **IPv6 按需下载**：IPv6 查询需要下载完整数据库（约 36.09MB / 34.42MiB / 36086712 bytes）
- ⚡ **多种缓存策略**：支持 file、vectorIndex、content 三种缓存模式
- 📦 **简化部署**：直接使用未压缩的 xdb 文件，无需解压过程
- 🔧 **PHP 7.1+ 优化**：全面使用类型声明，提升代码质量和 IDE 支持
- ✅ **字段映射修复**：修复 getIpInfo() 字段错位，region 字段已弃用

**使用示例**：
```php
echo ip2region('61.142.118.231'); 
// 输出：中国广东省中山市【电信】

echo ip2region('114.114.114.114'); 
// 输出：中国江苏省南京市
```


## ✨ 核心特性

-   **🌍 双协议支持**：**支持 IPv4 和 IPv6 地址查询**，自动识别 IP 版本
-   **⚡ 高性能**：基于官方 xdb 格式，查询速度极快，微秒级响应
-   **📦 零依赖**：纯 PHP 实现，需要 PHP 7.1+，无需额外扩展
-   **🚀 开箱即用**：IPv4 查询无需下载，直接使用内置文件
-   **⚠️ IPv6 支持**：IPv6 查询需要下载完整数据库（约 36.09MB / 34.42MiB / 36086712 bytes）
-   **🔧 自定义数据库**：支持自定义 IPv4/IPv6 数据库路径配置
-   **🔧 易集成**：支持 Composer 安装，提供函数式和面向对象两种 API
-   **💾 智能加载**：自动按优先级查找数据库文件，无需手动管理
-   **⚡ 多种缓存策略**：支持 file、vectorIndex、content 三种缓存模式
-   **📦 简化部署**：IPv4 使用未压缩数据库文件，无需解压过程
-   **🛡️ 企业级**：完善的错误处理、异常管理和性能监控
-   **🔄 懒加载**：IPv4/IPv6 查询器按需创建，优化内存使用
-   **📦 PHAR 支持**：完全支持 PHAR 环境，自动检测并适配不同的文件系统，在 PHAR 中直接使用内置数据库

## 🏗️ 技术架构

### 自动加载机制

项目采用优化的 Composer 自动加载策略：

```json
{
  "autoload": {
    "psr-4": {
      "ip2region\\": "src/ip2region/"
    },
    "classmap": [
      "src/Ip2Region.php"
    ],
    "files": [
      "src/common.php"
    ]
  }
}
```

**设计特点**：
- **PSR-4 加载**：`ip2region\xdb\*` 类使用 PSR-4 标准自动加载
- **全局类**：主类 `Ip2Region` 使用全局命名空间，便于直接使用
- **全局函数**：`src/common.php` 提供便捷的全局函数接口
- **组件化**：保持原有组件的命名空间和目录结构

### 类结构设计

```
Ip2Region (全局类)
├── 使用 ip2region\xdb\IPv4
├── 使用 ip2region\xdb\IPv6  
└── 使用 ip2region\xdb\Searcher

ip2region\xdb\* (组件类)
├── Util.php       # 工具类
├── IPv4.php       # IPv4 处理
├── IPv6.php       # IPv6 处理
└── Searcher.php   # 搜索引擎
```

### 工具集成

- **工具类设计**：`bin/ip2down` 使用内置类实现数据库管理功能
- **代码优化**：工具代码减少 48.7%，更简洁高效
- **减少依赖**：移除独立的 `DatabaseManager.php` 文件
- **简化维护**：所有工具功能集中在一个文件中

## 📁 项目结构

```
ip2region/
├── src/                    # 核心源码
│   ├── Ip2Region.php      # 主类（全局命名空间）
│   └── ip2region/
│       └── xdb/
│           ├── Util.php       # 工具类（ip2region\xdb\Util）
│           ├── IPv4.php       # IPv4 处理类（ip2region\xdb\IPv4）
│           ├── IPv6.php       # IPv6 处理类（ip2region\xdb\IPv6）
│           └── Searcher.php   # 搜索引擎类（ip2region\xdb\Searcher）
├── db/                    # 数据库文件目录（内置 IPv4）
│   └── ip2region_v4.xdb          # IPv4 数据库文件（未压缩）
├── vendor/
│   └── bin/
│       └── ip2data/       # 下载的数据库文件缓存目录（`ip2down download` 会写入这里）
│           ├── ip2region_v4.xdb   # IPv4 数据库文件（可选）
│           └── ip2region_v6.xdb   # IPv6 数据库文件（可选）
├── bin/                   # 命令行工具
│   └── ip2down            # 数据库下载管理工具（内置类实现，支持实时进度显示）
├── tests/                 # 测试文件
│   ├── demo.php           # 演示程序
│   └── quick_performance_test.php # 性能测试脚本
├── src/common.php         # 全局函数入口
├── composer.json          # Composer 配置
└── README.md              # 项目文档
```

> **💡 重要提示**：
>
> -   **IPv4 查询**：✅ 开箱即用，项目已包含数据库文件
> -   **IPv6 查询**：⚠️ 需要下载完整数据库文件（约 36.09MB / 34.42MiB / 36086712 bytes），推荐使用 `ip2down download v6`
> -   **自定义数据库**：支持通过构造函数指定自定义数据库路径
> -   **数据库文件**：IPv4 使用内置 xdb；IPv6 需下载后自动生效（可放到 `vendor/bin/ip2data/` 或 `db/`）

## 🆕 v3.0 新增功能

### 数据库管理

-   **内置数据库**：IPv4 数据库直接包含在项目中，无需额外下载
-   **简化部署**：使用未压缩的 xdb 文件，避免解压过程
-   **智能加载**：自动按优先级查找数据库文件，简化使用

### 增强的 API

-   **双协议支持**：`ip2region()` 函数自动识别 IPv4/IPv6
-   **面向对象**：`Ip2Region` 类提供完整的面向对象接口
-   **批量查询**：`batchSearch()` 方法支持批量 IP 查询
-   **性能监控**：`getStats()` 和 `getMemoryUsage()` 方法监控性能

### 企业级特性

-   **错误处理**：完善的异常处理和错误提示
-   **并发安全**：支持多进程/多线程安全使用
-   **缓存策略**：支持文件、VectorIndex、完整数据三种缓存方式
-   **PHP 7.1+ 要求**：需要 PHP 7.1 及以上版本

## 🚀 快速开始

### 环境要求

- **PHP 版本**：>= 7.1.0
- **Composer**：用于安装和管理依赖

### 1. 通过 Composer 安装

```bash
# 安装 V3.0 版本（推荐，功能完整）
composer require zoujingli/ip2region:^3.0
```

### 2. 下载数据库文件

> 💡 **说明**：IPv4 数据库已内置到 `db/`，可开箱即用；IPv6 数据库需要额外下载（约 36.09MB / 34.42MiB / 36086712 bytes），下载后会被自动识别并使用。

**IPv4 查询**：✅ 开箱即用，无需下载
**IPv6 查询**：⚠️ 需要下载完整数据库文件

**方法一：使用下载工具（推荐）**

```bash
# 下载 IPv6 数据库（约 36.09MB / 34.42MiB / 36086712 bytes，支持实时进度显示）
./vendor/bin/ip2down download v6

# 下载所有数据库
./vendor/bin/ip2down download all

# 查看已下载的文件
./vendor/bin/ip2down list

# 测试数据库功能
./vendor/bin/ip2down test

# 清除下载的数据库文件
./vendor/bin/ip2down clear
```

> **💡 开发环境提示**：
> 如果在开发环境中遇到 `./vendor/bin/ip2down: No such file or directory` 错误，可以使用以下命令：
> ```bash
> # 方法1：直接使用 PHP 运行
> php bin/ip2down download v6
> 
> # 方法2：创建符号链接
> ln -sf ../../bin/ip2down vendor/bin/ip2down
> ```

**方法二：手动下载**

```bash
# 创建数据库目录
mkdir -p db

# 下载 IPv6 数据库（约 36.09MB / 34.42MiB / 36086712 bytes）
# 方法1：使用 GitHub 原始链接（推荐）
# 首选：代理下载
wget -O db/ip2region_v6.xdb "https://gh-proxy.org/https://raw.githubusercontent.com/lionsoul2014/ip2region/master/data/ip2region_v6.xdb"
# 备用：官方直链
# wget -O db/ip2region_v6.xdb "https://raw.githubusercontent.com/lionsoul2014/ip2region/master/data/ip2region_v6.xdb"

# 方法2：使用 Gitee 镜像（国内访问更快，如果可用）
# wget -O db/ip2region_v6.xdb "https://gitee.com/lionsoul/ip2region/raw/master/data/ip2region_v6.xdb"

# 或者使用 curl（如果 wget 不可用）
# 首选：代理下载
curl -L -o db/ip2region_v6.xdb "https://gh-proxy.org/https://raw.githubusercontent.com/lionsoul2014/ip2region/master/data/ip2region_v6.xdb"
# 备用：官方直链
# curl -L -o db/ip2region_v6.xdb "https://raw.githubusercontent.com/lionsoul2014/ip2region/master/data/ip2region_v6.xdb"
```

> 💡 **下载提示**：
> - **IPv4**：已包含在项目中，无需下载（约 10.64MB / 10.15MiB / 10641955 bytes）
> - **IPv6**：推荐使用 GitHub 原始链接，也可尝试 Gitee 镜像（如果可用）
> - **文件大小**：IPv6 数据库约 36.09MB（34.42MiB / 36086712 bytes），建议使用原始链接或下载工具

**方法三：使用 Composer 脚本或下载工具**

```bash
# 下载 IPv6 数据库（约 36.09MB / 34.42MiB / 36086712 bytes，支持实时进度显示）
./vendor/bin/ip2down download v6

# 或者下载所有数据库
./vendor/bin/ip2down download all

# 尝试自动下载（可能因网络问题失败）
composer download

# 查看已下载的文件
./vendor/bin/ip2down list

# 测试数据库功能
./vendor/bin/ip2down test
```

**进度显示特性**：
- ✅ **实时进度**：显示已下载大小和下载速度
- ✅ **大小校验**：下载完成后按最小文件大小校验，避免保存错误页
- ✅ **流式下载**：避免大文件内存溢出
- ✅ **失败清理**：下载文件过小时自动清理，避免保留错误页或残缺文件

**进度显示示例**：
```bash
开始下载 IPv6 数据库...
已下载: 9.02 MB 速度: 3.01 MB/s
已下载: 22.03 MB 速度: 3.67 MB/s
已下载: 30.05 MB 速度: 3.34 MB/s
...
✅ v6: 34.41 MB
```

> 📝 **注意**：
> - IPv4 数据库可以正常自动下载（约 10.64MB / 10.15MiB / 10641955 bytes）
> - IPv6 数据库（约 36.09MB / 34.42MiB / 36086712 bytes），建议使用下载工具或手动下载

### 数据库优先级

系统按以下优先级查找数据库文件：

1. **自定义数据库**：通过构造函数指定的 `.xdb` 文件路径
2. **下载的数据库**：通过 `ip2down` 工具下载的完整数据库文件
3. **默认路径**：`db/` 目录下的内置 IPv4 文件；IPv6 可手动放入 `db/`

> ⚠️ **重要**：
> - **IPv4**：使用未压缩 xdb 文件，开箱即用
> - **IPv6**：需要下载完整数据库文件（约 36.09MB / 34.42MiB / 36086712 bytes）

### 3. 自定义数据库配置

项目已包含 IPv4 数据库文件，可直接使用。如需使用自定义数据库：

```php
<?php
require 'vendor/autoload.php';

try {
    // 使用自定义数据库路径（建议使用绝对路径）
    $ip2region = new \Ip2Region('file', 
        '/path/to/your/ip2region_v4.xdb',  // IPv4 数据库路径
        '/path/to/your/ip2region_v6.xdb'   // IPv6 数据库路径
    );
    
    // 查询示例
    echo $ip2region->simple('61.142.118.231'); // 中国广东省中山市【电信】
    echo $ip2region->simple('2400:3200::1'); // 阿里云 IPv6 DNS
?>
```

**获取数据库文件**：
- **IPv4 数据库**：✅ 已包含在项目中，无需下载
- **IPv6 数据库**：⚠️ **需要下载**（约 36.09MB / 34.42MiB / 36086712 bytes）
  - [代理下载（推荐）](https://gh-proxy.org/https://raw.githubusercontent.com/lionsoul2014/ip2region/master/data/ip2region_v6.xdb)
  - [官方直链（备用）](https://raw.githubusercontent.com/lionsoul2014/ip2region/master/data/ip2region_v6.xdb)
  - [Gitee 镜像](https://gitee.com/lionsoul/ip2region/raw/master/data/ip2region_v6.xdb)（国内访问更快，如果可用）
- **商业版本**：从 [ip2region 官网](https://www.ip2region.net/) 购买或下载
- **详细说明**：请参考本文档的自定义数据库部分

### 3. 一行代码开始使用

```php
<?php
require 'vendor/autoload.php';

// 最简单的使用方式
echo ip2region('61.142.118.231') . "\n"; // 中国广东省中山市【电信】（使用内置数据库）
echo ip2region('2400:3200::1') . "\n"; // 中国浙江省杭州市【专线用户】（需要下载完整数据库）

// 使用不同查询方法
echo ip2region('61.142.118.231', 'search'); // 中国|广东省|中山市|电信|CN
echo ip2region('61.142.118.231', 'memory'); // 返回数组格式

// 或者使用类方式
$ip2region = new \Ip2Region();
echo $ip2region->simple('61.142.118.231'); // 中国广东省中山市【电信】
?>
```

### 4. 验证安装

```bash
# 运行演示程序
composer demo

# 查询指定 IP
composer query 61.142.118.231

# 批量查询 IP
composer query:batch "61.142.118.231,114.114.114.114"

# 运行性能测试
composer performance
```

## 在项目中快速调用

### 函数式调用

```php
<?php
require 'vendor/autoload.php';

// 简单查询
echo ip2region('61.142.118.231') . "\n";        // 中国广东省中山市【电信】
echo ip2region('2400:3200::1') . "\n"; // 中国浙江省杭州市【专线用户】

// 使用不同查询方法
echo ip2region('61.142.118.231', 'search') . "\n"; // 中国|广东省|中山市|电信|CN
echo ip2region('61.142.118.231', 'memory') . "\n"; // 返回数组格式

// 批量查询
$ips = ['61.142.118.231', '114.114.114.114', '2400:3200::1'];
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
    // 默认模式（使用内置数据库）
    $ip2region = new \Ip2Region();
    
    // 如需使用自定义数据库，请参考下面的"自定义数据库配置"部分
    // $ip2region = new \Ip2Region('file', '/path/to/your/ip2region_v4.xdb', '/path/to/your/ip2region_v6.xdb');

    // 基础查询
    echo $ip2region->simple('61.142.118.231') . "\n";
    echo $ip2region->search('2400:3200::1') . "\n";

    // 获取详细信息
    $info = $ip2region->getIpInfo('61.142.118.231');
    print_r($info);
    // 输出: Array(
    //   [country] => 中国
    //   [province] => 广东省
    //   [city] => 中山市
    //   [isp] => 电信
    //   [ip] => 61.142.118.231
    //   [version] => v4
    //   [region] =>  // 已弃用，原用于世界级区域，现已不再使用
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


### 自定义数据库配置

```php
<?php
require 'vendor/autoload.php';

try {
    // 使用自定义数据库路径（建议使用绝对路径）
    $ip2region = new \Ip2Region('file', '/path/to/your/ip2region_v4.xdb', '/path/to/your/ip2region_v6.xdb');

    // 查询IP
    echo $ip2region->simple('61.142.118.231') . "\n";

    // 检查是否使用自定义数据库
    $customStatus = $ip2region->isUsingCustomDb();
    echo "IPv4 使用自定义数据库: " . ($customStatus['v4'] ? '是' : '否') . "\n";
    echo "IPv6 使用自定义数据库: " . ($customStatus['v6'] ? '是' : '否') . "\n";

    // 动态设置数据库路径
    $ip2region->setCustomDbPaths('/path/to/v4.xdb', '/path/to/v6.xdb');

    // 获取数据库配置信息
    $dbInfo = $ip2region->getDatabaseInfo();
    echo "IPv4 路径: " . ($dbInfo['custom_v4_path'] ?: '默认内置') . "\n";
    echo "IPv6 路径: " . ($dbInfo['custom_v6_path'] ?: '下载目录/默认路径/需下载') . "\n";

} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
}
?>
```

## 数据库文件准备

### 数据库加载优先级

ip2region 库按照以下优先级自动查找数据库文件：

1. **自动路径**：通过构造函数指定的自定义数据库路径
2. **vendor 目录**：`vendor/bin/ip2data/` 目录下的下载文件
3. **默认路径**：`db/` 目录下的内置文件（本仓库内置 IPv4；IPv6 需下载到 `vendor/bin/ip2data/` 或通过自定义路径提供）

### 使用预置数据库

本仓库已包含 IPv4 官方数据库文件，位于 `db/` 目录：

-   `ip2region_v4.xdb` - IPv4 数据库文件（已包含）
-   `ip2region_v6.xdb` - IPv6 数据库文件（未内置；可用 `ip2down download v6` 下载到 `vendor/bin/ip2data/`，或手动放入 `db/`）

### PHAR 环境使用

在 PHAR 环境中，ip2region 库会自动检测环境并调整数据库加载策略：

```php
// PHAR 环境中的使用方式
$searcher = new Ip2Region(); // 默认文件缓存模式

// 或使用其他缓存策略
$searcher = new Ip2Region('vectorIndex'); // 向量索引模式
$searcher = new Ip2Region('content');     // 内容缓存模式

// IPv4 查询（使用内置数据库）
echo $searcher->simple('61.142.118.231'); // 中国广东省中山市【电信】

// IPv6 查询（需要预先将数据库文件放入 PHAR）
echo $searcher->simple('2400:3200::1'); // 中国浙江省杭州市【专线用户】
```

**PHAR 环境特点**：
- ✅ IPv4 查询：开箱即用，使用内置数据库
- ⚠️ IPv6 查询：需要预先将 `ip2region_v6.xdb` 放入 PHAR 的 `db/` 目录
- 🔧 自定义路径：支持通过构造函数指定自定义数据库路径
- 📦 自动检测：无需手动配置，库会自动检测 PHAR 环境

### 使用自定义数据库

如果需要使用自定义的数据库文件，请按以下步骤操作：

#### 1. 获取完整数据库文件

**重要**：IPv4 数据库文件已包含在 `db/` 目录，可直接使用：

```
db/
└── ip2region_v4.xdb    # IPv4 数据库文件（已包含）
```

**文件说明**：

-   **IPv4 数据库**：已包含在 `db/` 目录，可直接使用
-   **IPv6 数据库**：需要从官方仓库下载到 `db/` 目录
-   **文件大小**：IPv4 约 10.64MB（10.15MiB / 10641955 bytes），IPv6 约 36.09MB（34.42MiB / 36086712 bytes）
-   **文件格式**：必须是有效的 xdb 格式文件

**获取数据库文件**：

-   **免费版本**：从 [ip2region 官方仓库](https://github.com/lionsoul2014/ip2region) 下载
-   IPv4 数据库（代理优先）：[ip2region_v4.xdb](https://gh-proxy.org/https://raw.githubusercontent.com/lionsoul2014/ip2region/master/data/ip2region_v4.xdb) (约 10.64MB / 10.15MiB / 10641955 bytes)
    - 备用直链：`https://raw.githubusercontent.com/lionsoul2014/ip2region/master/data/ip2region_v4.xdb`
-   IPv6 数据库（代理优先）：[ip2region_v6.xdb](https://gh-proxy.org/https://raw.githubusercontent.com/lionsoul2014/ip2region/master/data/ip2region_v6.xdb) (约 36.09MB / 34.42MiB / 36086712 bytes)
    - 备用直链：`https://raw.githubusercontent.com/lionsoul2014/ip2region/master/data/ip2region_v6.xdb`
-   **商业版本**：从 [ip2region 官网](https://www.ip2region.net/) 购买或下载
-   **格式要求**：确保下载的是 `.xdb` 格式，不是 `.txt` 或其他格式
-   **版本选择**：建议使用最新版本以获得最准确的地理位置数据
-   **重要提醒**：自定义数据库文件需要从官网下载或购买，确保使用正版数据源

#### 2. 放置数据库文件

将下载的数据库文件放置到 `db/` 目录：

```
db/
├── ip2region_v4.xdb    # IPv4 数据库文件（已包含）
└── ip2region_v6.xdb    # IPv6 数据库文件（手动下载后可放置）
```

#### 3. 验证数据库文件

```bash
# 测试 IPv4 查询
$ composer query 61.142.118.231
中国广东省中山市【电信】

# 测试 IPv6 查询（若你的项目缺失 IPv6 数据库，请先下载）
$ composer query 2400:3200::1
中国浙江省杭州市【专线用户】
```

## Composer 脚本

项目提供了便捷的 Composer 脚本命令：

### 核心功能
```bash
# 运行演示程序
composer demo

# 运行测试程序
composer test

# 查询单个 IP 地址
composer query 61.142.118.231

# 批量查询 IP 地址（逗号分隔）
composer query:batch "61.142.118.231,114.114.114.114,2400:3200::1"
```

### 数据库管理
```bash
# 下载所有数据库
composer download

# 下载 IPv4 数据库
composer download:v4

# 下载 IPv6 数据库（IPv6 需要完整数据库）
composer download:v6
```

### 工具命令
```bash
# 运行性能测试
composer performance

# 查看性能统计
composer stats

# 查看版本信息
composer version
```

## API 参考

### 全局函数

#### `ip2region($ip, $method = 'simple')`

-   **功能**：全局 IP 地理位置查询函数，提供统一的查询接口
-   **特性**：
    -   自动识别 IPv4/IPv6 地址类型
    -   支持多种查询方法和返回格式
    -   内置 IP 地址格式验证
    -   懒加载机制，按需初始化查询器
    -   异常安全，提供详细的错误信息
-   **参数**：
    -   `$ip` (string) - IP 地址，支持 IPv4 和 IPv6 格式
    -   `$method` (string) - 查询方法，可选值：simple, search, memory, binary, btree
-   **返回**：`string|array|null` - 查询结果，失败时返回 null
-   **异常**：当 IP 地址格式无效时抛出 `Exception`
-   **示例**：

    ```php
    // 简单查询（默认）
    echo ip2region('61.142.118.231');
    // 输出: 中国广东省中山市【电信】

    // 详细查询
    echo ip2region('61.142.118.231', 'search');
    // 输出: 中国|广东省|中山市|电信|CN

    // 内存查询（返回数组）
    $result = ip2region('61.142.118.231', 'memory');
    // 输出: Array([city_id] => 0, [region] => 中国|广东省|中山市|电信|CN)

    // IPv6 查询
    echo ip2region('2400:3200::1');
    // 输出: 中国浙江省杭州市【专线用户】

    // 异常处理
    try {
        $result = ip2region('invalid-ip');
    } catch (Exception $e) {
        echo "错误: " . $e->getMessage();
        // 如果是 IPv6 查询失败（通常是项目缺少 IPv6 数据库文件），提示下载数据库
        if (strpos($e->getMessage(), 'IPv6') !== false) {
            echo "\n提示: IPv6 查询需要下载完整数据库，请运行: ./vendor/bin/ip2down download v6";
        }
    }
    ```

#### 查询方法对比

| 方法名   | 描述             | 返回值类型 | 性能特点 | 适用场景               | 示例输出                                              |
| -------- | ---------------- | ---------- | -------- | ---------------------- | ----------------------------------------------------- |
| `simple` | 简单查询（默认） | string     | 最快     | 一般查询，用户友好显示 | `中国广东省中山市【电信】`                            |
| `search` | 详细查询         | string     | 快       | 需要原始数据格式       | `中国\|广东省\|中山市\|电信\|CN`                          |
| `memory` | 内存查询         | array      | 快       | 需要结构化数据         | `{"city_id":0,"region":"中国\|广东省\|中山市\|电信\|CN"}` |
| `binary` | 二进制搜索       | array      | 中等     | 有序数据快速查找       | `{"city_id":0,"region":"中国\|广东省\|中山市\|电信\|CN"}` |
| `btree`  | B 树索引         | array      | 中等     | 大规模数据平衡查询     | `{"city_id":0,"region":"中国\|广东省\|中山市\|电信\|CN"}` |

#### 使用建议

-   **一般查询**：使用 `simple` 方法，返回格式化的地理位置字符串
-   **数据处理**：使用 `search` 方法，获取原始数据格式便于解析
-   **程序集成**：使用 `memory` 方法，获取结构化数组数据
-   **性能优化**：根据数据量选择 `binary` 或 `btree` 方法
-   **异常处理**：始终使用 try-catch 包装函数调用
-   **IPv6 支持**：⚠️ IPv6 查询需要先下载完整数据库（约 36.09MB / 34.42MiB / 36086712 bytes）

### Ip2Region 类

#### 构造函数

```php
new Ip2Region($cachePolicy = 'file', $dbPathV4 = null, $dbPathV6 = null)
```

-   **参数**：
    -   `$cachePolicy` (string) - 缓存策略：'file', 'vectorIndex', 'content'
    -   `$dbPathV4` (string|null) - IPv4 数据库文件路径，null 表示使用默认路径
    -   `$dbPathV6` (string|null) - IPv6 数据库文件路径，null 表示自动查找下载目录或默认路径

-   **缓存策略说明**：
    -   `file`：文件缓存模式（默认），适合大文件，内存占用少
    -   `vectorIndex`：向量索引模式，减少 IO 操作，提升查询速度
    -   `content`：内容缓存模式，零 IO 操作，但占用更多内存

-   **示例**：
    ```php
    // 默认文件缓存模式
    $ip2region = new Ip2Region();
    
    // 向量索引缓存模式（减少 IO 操作）
    $ip2region = new Ip2Region('vectorIndex');
    
    // 内容缓存模式（零 IO，但占用更多内存）
    $ip2region = new Ip2Region('content');
    
    // 使用自定义数据库路径
    $ip2region = new Ip2Region('file', '/path/to/your/ip2region_v4.xdb', '/path/to/your/ip2region_v6.xdb');
    
    // 自定义缓存策略 + 自定义数据库路径
    $ip2region = new Ip2Region('vectorIndex', '/path/to/v4.xdb', '/path/to/v6.xdb');
    ```

#### 核心查询方法

##### `simple($ip)`

-   **功能**：简单查询，返回格式化结果；特殊用途地址会优先返回明确类型（如私网地址、回环地址、链路本地地址）
-   **参数**：`$ip` (string) - IP 地址
-   **返回**：`string|null` - 格式化查询结果
-   **示例**：`$ip2region->simple('61.142.118.231'); // 中国广东省中山市【电信】`
-   **特殊地址示例**：`$ip2region->simple('127.0.0.1'); // 回环地址`，`$ip2region->simple('192.168.1.1'); // 私网地址`

##### `search($ip)`

-   **功能**：基础查询，返回原始结果
-   **参数**：`$ip` (string) - IP 地址
-   **返回**：`string|null` - 原始查询结果
-   **示例**：`$ip2region->search('61.142.118.231'); // 中国|广东省|中山市|电信|CN`

##### `memorySearch($ip)`

-   **功能**：内存查询，返回数组格式
-   **参数**：`$ip` (string) - IP 地址
-   **返回**：`array` - 包含 city_id 和 region 的数组
-   **示例**：`$ip2region->memorySearch('61.142.118.231'); // ['city_id' => 0, 'region' => '中国|广东省|中山市|电信|CN']`

##### `batchSearch($ips)`

-   **功能**：批量查询多个 IP
-   **参数**：`$ips` (array) - IP 地址数组
-   **返回**：`array` - IP 地址为键的查询结果数组
-   **示例**：`$ip2region->batchSearch(['61.142.118.231', '114.114.114.114']);`

##### `getIpInfo($ip)`

-   **功能**：获取详细的 IP 信息
-   **参数**：`$ip` (string) - IP 地址
-   **返回**：`array|null` - 包含 country, province, city, isp, ip, version 的数组（region 字段已弃用）
-   **示例**：
    ```php
    $info = $ip2region->getIpInfo('61.142.118.231');
    // 返回: [
    //   'country' => '中国',
    //   'province' => '广东省',
    //   'city' => '中山市',
    //   'isp' => '电信',
    //   'ip' => '61.142.118.231',
    //   'version' => 'v4',
    //   'region' => '' // 已弃用
    // ]
    ```

#### 兼容性方法

##### `binarySearch($ip)`

-   **功能**：二进制搜索（兼容旧版本）
-   **参数**：`$ip` (string) - IP 地址
-   **返回**：`array` - 查询结果数组

##### `btreeSearch($ip)`

-   **功能**：B 树搜索（兼容旧版本）
-   **参数**：`$ip` (string) - IP 地址
-   **返回**：`array` - 查询结果数组

##### `searchByBytes($ipBytes)`

-   **功能**：二进制字节搜索
-   **参数**：`$ipBytes` (string) - 二进制 IP 地址
-   **返回**：`string|null` - 查询结果

#### 工具方法

##### `getStats()`

-   **功能**：获取统计信息
-   **返回**：`array` - 包含内存使用、IO 计数、加载状态等
-   **示例**：`$stats = $ip2region->getStats();`

##### `getMemoryUsage()`

-   **功能**：获取内存使用情况
-   **返回**：`array` - 包含当前内存、峰值内存、加载状态等
-   **示例**：`$memory = $ip2region->getMemoryUsage();`

##### `getIOCount()`

-   **功能**：获取 IO 计数
-   **返回**：`array` - 包含 IPv4、IPv6 和总 IO 计数
-   **示例**：`$io = $ip2region->getIOCount();`

##### `getProtocolVersion($ip)`

-   **功能**：获取 IP 协议版本
-   **参数**：`$ip` (string) - IP 地址
-   **返回**：`string` - 'v4', 'v6' 或 'unknown'
-   **示例**：`echo $searcher->getProtocolVersion('2400:3200::1'); // 输出: v6`

##### `isIPv4Supported()`

-   **功能**：检查是否支持 IPv4
-   **返回**：`bool` - 是否支持 IPv4
-   **示例**：`echo $searcher->isIPv4Supported() ? '支持' : '不支持';`

##### `isIPv6Supported()`

-   **功能**：检查是否支持 IPv6
-   **返回**：`bool` - 是否支持 IPv6
-   **示例**：`echo $searcher->isIPv6Supported() ? '支持' : '不支持';`

##### `getDatabaseInfo()`

-   **功能**：获取数据库信息
-   **返回**：`array` - 包含加载状态、缓存策略、版本信息、自定义路径等
-   **示例**：`$info = $searcher->getDatabaseInfo(); print_r($info);`

##### `setCustomDbPaths($v4Path, $v6Path)`

-   **功能**：动态设置自定义数据库路径
-   **参数**：
    -   `$v4Path` (string|null) - IPv4 数据库文件路径
    -   `$v6Path` (string|null) - IPv6 数据库文件路径
-   **示例**：`$ip2region->setCustomDbPaths('/path/to/v4.xdb', '/path/to/v6.xdb');`

##### `isUsingCustomDb()`

-   **功能**：检查是否使用自定义数据库
-   **返回**：`array` - 包含 IPv4 和 IPv6 的使用状态
-   **示例**：`$status = $ip2region->isUsingCustomDb();`

##### `getCustomDbInfo()`

-   **功能**：获取自定义数据库文件信息
-   **返回**：`array` - 包含自定义数据库文件的大小、修改时间等信息
-   **示例**：`$info = $ip2region->getCustomDbInfo();`

## 性能监控示例

```php
<?php
require 'vendor/autoload.php';

try {
    // 默认模式（使用内置数据库）
    $ip2region = new \Ip2Region();
    
    // 或者使用自定义数据库（建议使用绝对路径）
    // $ip2region = new \Ip2Region('file', '/path/to/your/ip2region_v4.xdb', '/path/to/your/ip2region_v6.xdb');

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
$dbInfo = $searcher->getDatabaseInfo();
echo "IPv4已加载: " . ($dbInfo['v4_loaded'] ? '是' : '否') . "\n";
echo "IPv6已加载: " . ($dbInfo['v6_loaded'] ? '是' : '否') . "\n";
echo "缓存策略: " . $dbInfo['cache_policy'] . "\n";

// 获取性能统计
$stats = $searcher->getStats();
echo "内存使用: " . round($stats['memory_usage'] / 1024 / 1024, 2) . " MB\n";
echo "缓存策略: " . $stats['cache_policy'] . "\n";
?>
```

## FPM 环境优化

### 缓存策略优化

ip2region 支持三种缓存策略，可根据不同使用场景选择最优策略：

#### 缓存策略说明

1. **file 模式**（默认）：
   - 适合大文件，内存占用少
   - 首次查询较慢，后续查询快
   - 适合单次查询或低频查询场景

2. **vectorIndex 模式**：
   - 减少 IO 操作，提升查询速度
   - 内存占用适中
   - 适合频繁查询场景

3. **content 模式**：
   - 零 IO 操作，查询最快
   - 占用大量内存
   - 适合高并发场景

#### 使用示例

```php
<?php
// 文件缓存模式（默认）
$searcher = new Ip2Region('file');

// 向量索引模式
$searcher = new Ip2Region('vectorIndex');

// 内容缓存模式
$searcher = new Ip2Region('content');

// 查询 IP
echo $searcher->simple('61.142.118.231');
?>
```

#### 性能对比

| 缓存策略 | 内存使用 | IO 次数 | 查询速度 | 适用场景 |
|---------|---------|---------|---------|---------|
| file | 低 | 多 | 中等 | 单次查询 |
| vectorIndex | 中等 | 少 | 快 | 频繁查询 |
| content | 高 | 无 | 最快 | 高并发 |

## 性能测试

### 快速性能测试

运行 `composer performance` 进行快速性能测试：

```bash
composer performance
```

**测试结果示例**：
```
测试环境信息:
==================
操作系统: Darwin 25.0.0
PHP版本: 8.1.29
内存限制: 128M
最大执行时间: 0秒
时区: UTC
当前时间: 2025-10-18 05:38:46
系统负载: 1.69, 2.68, 2.96
当前内存使用: 4MB
峰值内存使用: 4MB
磁盘空间: 567.13GB 可用 / 926.35GB 总计
CPU: Apple M4 Pro
CPU核心数: 14

首次加载 vs 缓存命中:
  IPv4: 0.71ms → 0.16ms (提升 77.5%) (new Ip2Region() + simple())
  IPv6: 0.71ms → 0.16ms (提升 77.5%) (new Ip2Region() + simple())

查询方法性能:
  simple: 0.02ms (ip2region->simple())
  search: 0.01ms (ip2region->search())
  memorySearch: 0.01ms (ip2region->memorySearch())

批量处理性能:
  10个IP: 1.19ms (ip2region->batchSearch())
  10000个IP: 89.94ms (ip2region->batchSearch())
  10000次循环: 53.86ms (10000次 ip2region->simple())

QPS性能:
  10个IP: 8403 QPS
  10000个IP: 111185 QPS
  10000次循环: 185667 QPS

性能监控:
  性能监控: 0.01ms (getStats() 性能监控)

性能评分: 100/100
性能等级: 优秀 ⭐⭐⭐⭐⭐
```

### 性能测试内容

快速性能测试包含以下项目：

- 首次加载测试（无缓存）
- 缓存命中测试
- 不同查询方法性能对比
- 批量查询测试（10个IP + 10000个IP）
- 循环查询测试（10000次）
- 内存使用测试
- 缓存清理性能测试
- 系统环境信息展示
- 性能评分和等级评定

### 性能特点

#### 测试环境
- **硬件配置**：Apple M4 Pro (14核心)，16GB+ 内存
- **操作系统**：macOS 15.0 (Darwin 25.0.0)
- **PHP版本**：8.1.29
- **磁盘空间**：587GB+ 可用空间

#### 性能指标
1. **首次加载**：直接使用数据库文件，IPv4 约 0.71ms，IPv6 约 0.71ms (`new Ip2Region() + simple()`)
2. **缓存命中**：IPv4 约 0.16ms，IPv6 约 0.16ms (`new Ip2Region() + simple()`)
3. **性能表现**：查询速度快，内存占用低，缓存效果显著
4. **查询方法**：
   - `simple()`：约 0.02ms (格式化输出)
   - `search()`：约 0.01ms (原始数据)
   - `memorySearch()`：约 0.01ms (数组格式)
5. **批量查询**：
   - 10个IP：约 1.19ms (8403 QPS) (`ip2region->batchSearch()`)
   - 10000个IP：约 89.94ms (111185 QPS) (`ip2region->batchSearch()`)
   - 10000次循环：约 53.86ms (185667 QPS) (10000次 `ip2region->simple()`)
6. **内存使用**：当前 4MB，峰值 4MB
7. **性能监控**：约 0.01ms (`getStats()` 性能监控)
8. **性能评分**：100/100 (优秀 ⭐⭐⭐⭐⭐)

#### 最新性能测试结果 (2025-10-18)

**测试环境**：
- 操作系统：Darwin 25.0.0 (macOS)
- PHP版本：8.1.29
- CPU：Apple M4 Pro (14核心)
- 内存：4MB (峰值4MB)

**详细性能数据**：
- **IPv4查询**：平均 0.02ms，QPS 50,000+
- **IPv6查询**：平均 0.16ms，QPS 6,250+
- **批量查询**：10个IP 1.19ms (8403 QPS)，10000个IP 89.94ms (111185 QPS)
- **循环查询**：10000次循环 53.86ms (185667 QPS)
- **内存效率**：极低内存占用，仅4MB
- **缓存性能**：首次加载后缓存命中率接近100%，性能提升显著

#### 性能等级说明
- **90-100分**：优秀 ⭐⭐⭐⭐⭐ (企业级性能)
- **80-89分**：良好 ⭐⭐⭐⭐ (生产环境推荐)
- **70-79分**：中等 ⭐⭐⭐ (一般应用)
- **60-69分**：及格 ⭐⭐ (基础应用)
- **0-59分**：需要优化 ⭐ (性能不足)

## 故障排除

### 常见问题

#### 1. 数据库文件不存在

**错误信息**：`数据库文件不存在: /path/to/ip2region_v4.xdb`
**解决方案**：

-   **检查文件位置**：确保 IPv4 数据库文件存在于 `db/` 目录下，或通过 `ip2down` 下载到 `vendor/bin/ip2data/`
-   **检查文件名**：IPv4 源文件必须严格按照 `ip2region_v4.xdb` 命名
-   **检查文件权限**：确保文件可读
-   **下载 IPv6 数据库**：从官方仓库下载 IPv6 的 `.xdb` 文件到 `vendor/bin/ip2data/` 或 `db/` 目录
-   **获取数据源**：
    -   免费版本：从 [ip2region 官方仓库](https://github.com/lionsoul2014/ip2region) 下载
        -   IPv4（代理优先）：[ip2region_v4.xdb](https://gh-proxy.org/https://raw.githubusercontent.com/lionsoul2014/ip2region/master/data/ip2region_v4.xdb) (约 10.64MB / 10.15MiB / 10641955 bytes)
            - 备用直链：`https://raw.githubusercontent.com/lionsoul2014/ip2region/master/data/ip2region_v4.xdb`
        -   IPv6（代理优先）：[ip2region_v6.xdb](https://gh-proxy.org/https://raw.githubusercontent.com/lionsoul2014/ip2region/master/data/ip2region_v6.xdb) (约 36.09MB / 34.42MiB / 36086712 bytes)
            - 备用直链：`https://raw.githubusercontent.com/lionsoul2014/ip2region/master/data/ip2region_v6.xdb`
    -   商业版本：从 [ip2region 官网](https://www.ip2region.net/) 购买或下载
-   **数据库文件**：直接使用未压缩的 xdb 文件，无需额外处理

**文件摆放检查**：

```bash
# 检查文件是否存在
ls -la db/ip2region_v*.xdb*

# 应该看到类似输出：
# -rw-r--r-- 1 user staff 10641955 May 08 10:21 db/ip2region_v4.xdb
# -rw-r--r-- 1 user staff 36086712 May 08 10:21 db/ip2region_v6.xdb
```

#### 2. 内存不足

**错误信息**：`Fatal error: Allowed memory size exhausted`
**解决方案**：

-   增加 PHP 内存限制：`ini_set('memory_limit', '256M');`
-   使用文件缓存策略：`new Ip2Region('file')`
-   检查数据库文件是否完整

#### 3. 数据库文件损坏

**错误信息**：`数据库文件不存在` 或 `无法读取数据库文件`
**解决方案**：

-   检查 `db/` 目录下的数据库文件是否完整
-   重新下载数据库文件：`./vendor/bin/ip2down download v4`
-   检查文件权限是否正确

#### 4. 并发使用问题

**错误信息**：`Too many open files`
**解决方案**：

-   每个进程/线程创建独立的 `Ip2Region` 实例
-   增加系统文件描述符限制
-   使用内存缓存策略：`new Ip2Region('content')`

### 性能优化建议

1. **选择合适的缓存策略**：
   -   **file 模式**：适合大文件，内存占用少，首次查询较慢
   -   **vectorIndex 模式**：减少 IO 操作，提升查询速度，内存占用适中
   -   **content 模式**：零 IO 操作，查询最快，但占用大量内存

2. **根据使用场景选择**：

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

4. **文件管理**：

    - 定期检查文件完整性
    - 使用 `getStats()` 监控性能状态
    - 根据使用场景选择合适的缓存策略

5. **性能优化建议**：
    - **选择合适的缓存策略**：根据使用场景选择 file、vectorIndex 或 content 模式
    - **定期检查文件完整性**：确保数据库文件完整无损
    - **监控内存使用**：根据内存情况选择合适的缓存策略

## 许可证

本项目基于 Apache-2.0 许可证开源。

## 🔧 通用查询函数

IP2Region 提供了通用的 `ip2region()` 函数，支持多种查询方法：

### 函数签名

```php
ip2region(string $ip, string $method = 'simple'): string|array|null
```

### 支持的查询方法

| 方法     | 描述             | 返回值                 | 示例                   |
| -------- | ---------------- | ---------------------- | ---------------------- |
| `simple` | 简单查询（默认） | 格式化的地理位置字符串，特殊地址返回地址类型 | `"中国广东省中山市【电信】"` / `"回环地址"` |
| `search` | 详细查询         | 管道分隔的详细信息     | `"中国\|广东省\|中山市\|电信\|CN"` |
| `binary` | 二进制查询       | 原始二进制数据         | 二进制字符串           |
| `btree`  | B 树查询         | B 树索引查询结果       | 查询结果字符串         |
| `memory` | 内存查询         | 内存中的查询结果       | 查询结果字符串         |

### 使用示例

```php
<?php
require 'vendor/autoload.php';

// 简单查询（默认方法）
echo ip2region('61.142.118.231'); // 输出: 中国广东省中山市【电信】

// 详细查询
echo ip2region('61.142.118.231', 'search'); // 输出: 中国|广东省|中山市|电信|CN

// 内存查询（返回数组）
$result = ip2region('61.142.118.231', 'memory');
print_r($result); // 输出: Array([city_id] => 0, [region] => 中国|广东省|中山市|电信|CN)

// IPv6查询
echo ip2region('2400:3200::1'); // 输出: 中国浙江省杭州市【专线用户】

// 异常安全
$result = ip2region('invalid-ip'); // 返回: null
if ($result === null) {
    echo "IP地址无效或查询失败";
}
```

### 特性说明

-   **自动识别**：自动识别 IPv4 和 IPv6 地址
-   **多种缓存策略**：支持 file、vectorIndex、content 三种缓存模式
-   **智能加载**：自动按优先级查找数据库文件
-   **异常安全**：查询失败返回 null，不会抛出异常
-   **静态实例**：使用静态实例，避免重复初始化
-   **IPv6 支持**：⚠️ IPv6 查询需要先下载完整数据库（约 36.09MB / 34.42MiB / 36086712 bytes）

## 📚 相关链接
-   [V3.0 版本文档](https://github.com/zoujingli/ip2region/tree/master) - 完整版本，支持 IPv4 + IPv6
-   [官方 ip2region 项目](https://github.com/lionsoul2014/ip2region) - 原始项目

## 贡献

欢迎提交 Issue 和 Pull Request 来改进这个项目。

## 联系方式

如有问题或建议，请通过以下方式联系：

-   **GitHub Issues**：[提交问题或建议](https://github.com/zoujingli/ip2region/issues)
-   **邮箱**：zoujingli@qq.com
-   **作者主页**：[https://thinkadmin.top](https://thinkadmin.top)

> 📢 **推荐服务**：[VSLLM](https://vsllm.com)
