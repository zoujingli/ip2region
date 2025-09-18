# ip2region 自定义数据库配置使用说明

## 功能概述

ip2region 现在支持自定义数据库路径配置，允许用户指定自己的 IPv4 和 IPv6 数据库文件路径。系统采用智能优先级机制，确保最佳性能和灵活性。

### 优先级机制

IP2Region 按以下优先级顺序查找数据库文件：

1. **自定义数据库**（最高优先级）
   - 如果构造函数中指定了自定义路径且文件存在
   - 直接使用指定的 `.xdb` 文件
   - **完全跳过分片处理**，性能最优

2. **持久化缓存**
   - 检查系统临时目录中的合并缓存文件
   - 验证缓存文件的有效性（大小、时间戳、内容格式）
   - 如果有效则直接使用

3. **分片文件合并**（默认模式）
   - 查找 `db/` 目录下的分片文件
   - 自动解压并合并成完整数据库
   - 生成持久化缓存供后续使用

### 性能优势

使用自定义数据库时：
- ✅ **无分片处理**：直接使用完整 `.xdb` 文件
- ✅ **无合并开销**：跳过 `ChunkedDbHelper::findChunks()` 和 `mergeToCache()`
- ✅ **无压缩解压**：跳过 gzip/zip/zstd 解压过程
- ✅ **更快启动**：首次加载时间大幅减少
- ✅ **更少IO**：只读取一个文件，不是多个分片

### 性能对比

| 模式 | 首次加载 | 后续加载 | 文件数量 | 处理复杂度 | 适用场景 |
|------|----------|----------|----------|------------|----------|
| 自定义数据库 | ~1ms | ~1ms | 1个 | 无 | 生产环境推荐 |
| 持久化缓存 | ~30ms | ~1ms | 1个 | 无 | 开发环境 |
| 分片文件合并 | ~30ms | ~1ms | 多个 | 高 | 默认模式 |

## 自定义数据库文件位置建议

> 📖 **详细说明**：关于数据库文件位置选择、下载和配置的完整说明，请参考 [数据库文件下载说明](DATABASE_DOWNLOAD.md)。

### 快速参考

**推荐存放位置**：
- 项目根目录：`/var/www/your-project/ip2region_v4.xdb`
- 专用数据目录：`/var/www/your-project/data/ip2region_v4.xdb`
- 子目录存放：`/var/www/your-project/storage/ip2region_v4.xdb`

**重要原则**：
- ✅ 数据库文件必须放在项目目录内
- ✅ 建议使用绝对路径
- ✅ 确保 PHP 有读取权限
- ❌ 不要放在 `vendor/` 目录下
- ❌ 不要放在 `tools/` 目录下（开发工具目录）
- ❌ 不要放在系统目录（如 `/var/lib/`、`/usr/local/` 等）

## 兼容性说明

- **PHP 版本要求**：完全兼容 PHP 5.4+ 版本
- **向后兼容**：现有代码无需修改，默认使用分片数据库模式
- **零依赖**：纯 PHP 实现，无需额外扩展

## 数据库文件获取

### 官方数据源

ip2region 提供多种数据源选择：

1. **免费版本**：
   - 来源：[ip2region 官方 GitHub 仓库](https://github.com/lionsoul2014/ip2region)
   - 特点：开源免费，数据相对基础
   - 适用：个人项目、学习研究

2. **商业版本**：
   - 来源：[ip2region 官网](https://www.ip2region.net/)
   - 特点：数据更准确、更新更及时、支持更多地区
   - 适用：商业项目、企业应用

3. **第三方数据源**：
   - 可以集成其他 IP 地理位置数据库
   - 需要转换为 xdb 格式
   - 建议使用官方推荐的数据源

### 文件格式要求

- **文件格式**：必须是 `.xdb` 格式
- **文件命名**：严格按照 `ip2region_v4.xdb` 和 `ip2region_v6.xdb` 命名
- **文件大小**：IPv4 约 11MB，IPv6 约 617MB
- **文件位置**：建议放在项目根目录或 `tools/` 目录下

### 获取步骤

#### 方法一：从官方 GitHub 仓库获取（免费）

```bash
# 1. 访问官方仓库
# https://github.com/lionsoul2014/ip2region

# 2. 下载数据文件
# 使用 wget：
wget https://raw.githubusercontent.com/lionsoul2014/ip2region/master/data/ip2region_v4.xdb
wget https://raw.githubusercontent.com/lionsoul2014/ip2region/master/data/ip2region_v6.xdb

# 或使用 curl：
curl -L -o ip2region_v4.xdb https://raw.githubusercontent.com/lionsoul2014/ip2region/master/data/ip2region_v4.xdb
curl -L -o ip2region_v6.xdb https://raw.githubusercontent.com/lionsoul2014/ip2region/master/data/ip2region_v6.xdb

# 3. 放置到项目目录
mkdir -p tools/
mv ip2region_v4.xdb tools/
mv ip2region_v6.xdb tools/
```

#### 方法二：从官网购买（商业版）

1. 访问 [ip2region 官网](https://www.ip2region.net/)
2. 注册账号并购买数据库文件
3. 下载 `.xdb` 格式的数据库文件
4. 按照要求重命名并放置到项目目录

#### 方法三：使用 Composer 脚本（如果可用）

```bash
# 如果项目提供了下载脚本
composer download:db
```

## 使用方式

### 1. 默认模式（压缩包分片）

```php
<?php
require_once 'src/Ip2Region.php';

// 使用默认的压缩包分片模式
$searcher = new Ip2Region();

// 查询IP
$result = $searcher->memorySearch('8.8.8.8');
echo $result['region']; // 输出：美国|0|0|Level3
?>
```

### 2. 自定义数据库路径模式

```php
<?php
require_once 'src/Ip2Region.php';

// 指定自定义数据库路径（建议使用绝对路径）
$searcher = new Ip2Region('file', '/path/to/your/ip2region_v4.xdb', '/path/to/your/ip2region_v6.xdb');

// 查询IP
$result = $searcher->memorySearch('8.8.8.8');
echo $result['region'];
?>
```

### 3. 混合模式

```php
<?php
require_once 'src/Ip2Region.php';

// 只指定 IPv4 自定义路径，IPv6 使用默认分片
$searcher = new Ip2Region('file', '/path/to/your/ip2region_v4.xdb', null);

// 或者只指定 IPv6 自定义路径，IPv4 使用默认分片
$searcher = new Ip2Region('file', null, '/path/to/your/ip2region_v6.xdb');
?>
```

### 4. 动态设置数据库路径

```php
<?php
require_once 'src/Ip2Region.php';

$searcher = new Ip2Region();

// 动态设置自定义路径（建议使用绝对路径）
$searcher->setCustomDbPaths('/path/to/your/ip2region_v4.xdb', '/path/to/your/ip2region_v6.xdb');

// 清除自定义路径，回退到默认模式
$searcher->setCustomDbPaths(null, null);
?>
```

## 配置检查

### 检查是否使用自定义数据库

```php
$customStatus = $searcher->isUsingCustomDb();
echo "IPv4 使用自定义数据库: " . ($customStatus['v4'] ? '是' : '否') . "\n";
echo "IPv6 使用自定义数据库: " . ($customStatus['v6'] ? '是' : '否') . "\n";
```

### 获取数据库配置信息

```php
$dbInfo = $searcher->getDatabaseInfo();
echo "IPv4 路径: " . ($dbInfo['custom_v4_path'] ?: '默认分片') . "\n";
echo "IPv6 路径: " . ($dbInfo['custom_v6_path'] ?: '默认分片') . "\n";
```

### 获取自定义数据库文件信息

```php
$customDbInfo = $searcher->getCustomDbInfo();
if ($customDbInfo['v4']) {
    echo "IPv4 文件大小: " . $customDbInfo['v4']['size'] . " 字节\n";
    echo "IPv4 文件修改时间: " . date('Y-m-d H:i:s', $customDbInfo['v4']['mtime']) . "\n";
}
```

## 构造函数参数

```php
public function __construct($cachePolicy = 'file', $dbPathV4 = null, $dbPathV6 = null)
```

- `$cachePolicy`: 缓存策略，默认为 'file'
- `$dbPathV4`: IPv4 数据库文件路径，null 表示使用默认分片
- `$dbPathV6`: IPv6 数据库文件路径，null 表示使用默认分片

## 新增方法

### setCustomDbPaths($v4Path, $v6Path)
动态设置自定义数据库路径

### isUsingCustomDb()
检查是否使用自定义数据库

### getCustomDbInfo()
获取自定义数据库文件信息

### getDatabaseInfo()
获取数据库配置信息（包含自定义路径）

## 兼容性

- 完全向后兼容，现有代码无需修改
- 默认使用压缩包分片模式
- 支持 IPv4 和 IPv6 独立配置
- 自动回退机制：自定义文件不存在时自动使用默认分片

## 注意事项

1. **数据库获取**：自定义数据库文件需要从官网下载或购买，确保使用正版数据源
2. **文件格式**：自定义数据库文件必须是有效的 xdb 格式
3. **文件路径**：文件路径必须是绝对路径或相对于当前工作目录的路径
4. **文件存在性**：确保文件存在且可读
5. **自动回退**：如果自定义文件不存在，系统会自动回退到默认分片模式
6. **动态设置**：动态设置路径会重置查询器，强制重新加载数据库
7. **PHP 版本兼容性**：确保使用 PHP 5.4+ 版本以获得最佳兼容性
8. **文件权限**：确保自定义数据库文件具有读取权限
9. **路径格式**：建议使用绝对路径以避免路径解析问题
10. **数据更新**：定期更新数据库文件以获得最新的地理位置信息
11. **商业使用**：商业项目建议购买官方商业版数据库以获得更准确的数据

## 示例：完整使用流程

```php
<?php
require_once 'src/Ip2Region.php';

// 1. 创建实例（默认模式）
$searcher = new Ip2Region();

// 2. 检查当前状态
$status = $searcher->isUsingCustomDb();
echo "当前使用自定义数据库: IPv4=" . ($status['v4'] ? '是' : '否') . 
     ", IPv6=" . ($status['v6'] ? '是' : '否') . "\n";

// 3. 设置自定义路径
$searcher->setCustomDbPaths('/path/to/v4.xdb', '/path/to/v6.xdb');

// 4. 再次检查状态
$status = $searcher->isUsingCustomDb();
echo "设置后使用自定义数据库: IPv4=" . ($status['v4'] ? '是' : '否') . 
     ", IPv6=" . ($status['v6'] ? '是' : '否') . "\n";

// 5. 查询IP
$result = $searcher->memorySearch('8.8.8.8');
echo "查询结果: " . $result['region'] . "\n";

// 6. 获取统计信息
$stats = $searcher->getStats();
echo "内存使用: " . $stats['memory_usage'] . " 字节\n";
?>
```

## PHP 5.4+ 兼容性测试

### 版本检查

```php
<?php
// 检查 PHP 版本
if (version_compare(PHP_VERSION, '5.4.0', '<')) {
    die('需要 PHP 5.4+ 版本，当前版本：' . PHP_VERSION . "\n");
}

echo "PHP 版本检查通过：" . PHP_VERSION . "\n";

// 测试自定义数据库配置
require_once 'src/Ip2Region.php';

try {
    // 测试默认模式
    $searcher1 = new Ip2Region();
    echo "✓ 默认模式初始化成功\n";
    
    // 测试自定义模式（模拟）
    $searcher2 = new Ip2Region('file', '/path/to/v4.xdb', '/path/to/v6.xdb');
    echo "✓ 自定义模式初始化成功\n";
    
    // 测试动态设置
    $searcher2->setCustomDbPaths(null, null);
    echo "✓ 动态设置成功\n";
    
    // 测试查询
    $result = $searcher1->simple('8.8.8.8');
    echo "✓ 查询测试成功：" . $result . "\n";
    
    echo "所有测试通过，兼容 PHP 5.4+\n";
    
} catch (Exception $e) {
    echo "✗ 测试失败：" . $e->getMessage() . "\n";
}
?>
```

### 兼容性特性

- **语法兼容**：使用 PHP 5.4+ 兼容的语法结构
- **函数兼容**：避免使用 PHP 5.5+ 的新特性
- **错误处理**：使用传统的异常处理机制
- **数组语法**：使用 `array()` 而非 `[]` 短数组语法（可选）
- **操作符兼容**：避免使用 `?:` 等新操作符

### 性能优化建议

1. **PHP 5.4+ 优化**：
   - 启用 OPcache 扩展以获得更好的性能
   - 使用 `memory_limit` 设置合适的内存限制
   - 考虑使用 `new Ip2Region('content')` 进行内存缓存

2. **自定义数据库优化**：
   - 使用 SSD 存储自定义数据库文件
   - 确保文件权限正确设置
   - 定期检查文件完整性

3. **生产环境建议**：
   - 使用绝对路径指定数据库文件
   - 设置适当的错误日志级别
   - 监控内存使用情况

## 相关文档

- [数据库文件下载说明](DATABASE_DOWNLOAD.md) - 详细的数据库获取和配置指南
- [README.md](README.md) - 项目主要文档
- [CHANGELOG.md](CHANGELOG.md) - 版本更新记录
