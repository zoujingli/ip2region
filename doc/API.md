# API 文档

## 全局函数

### ip2region($ip, $method = 'simple')

全局 IP 地理位置查询函数。

**参数**：
- `$ip` (string) - IP 地址，支持 IPv4 和 IPv6 格式
- `$method` (string) - 查询方法，可选值：simple, search, memory, binary, btree

**返回值**：
- `string|array|null` - 查询结果，失败时返回 null

**示例**：
```php
// 简单查询
echo ip2region('61.142.118.231'); // 中国广东省中山市【电信】

// 详细查询
echo ip2region('61.142.118.231', 'search'); // 中国|广东省|中山市|电信

// 内存查询
$result = ip2region('61.142.118.231', 'memory');
// 返回: ['city_id' => 0, 'region' => '中国|广东省|中山市|电信']
```

## Ip2Region 类

### 构造函数

```php
new Ip2Region($cachePolicy = 'file', $dbPathV4 = null, $dbPathV6 = null)
```

**参数**：
- `$cachePolicy` (string) - 缓存策略：'file', 'vectorIndex', 'content'
- `$dbPathV4` (string|null) - IPv4 数据库文件路径
- `$dbPathV6` (string|null) - IPv6 数据库文件路径

**示例**：
```php
// 默认模式
$ip2region = new Ip2Region();

// 使用自定义数据库
$ip2region = new Ip2Region('file', '/path/to/v4.xdb', '/path/to/v6.xdb');
```

### 查询方法

#### simple($ip)

简单查询，返回格式化的地理位置字符串。

**参数**：`$ip` (string) - IP 地址
**返回值**：`string|null` - 格式化的地理位置信息

**示例**：
```php
echo $ip2region->simple('61.142.118.231'); // 中国广东省中山市【电信】
```

#### search($ip)

基础查询，返回原始查询结果。

**参数**：`$ip` (string) - IP 地址
**返回值**：`string|null` - 原始查询结果

**示例**：
```php
echo $ip2region->search('61.142.118.231'); // 中国|广东省|中山市|电信
```

#### memorySearch($ip)

内存查询，返回数组格式。

**参数**：`$ip` (string) - IP 地址
**返回值**：`array` - 包含 city_id 和 region 的数组

**示例**：
```php
$result = $ip2region->memorySearch('61.142.118.231');
// 返回: ['city_id' => 0, 'region' => '中国|广东省|中山市|电信']
```

#### binarySearch($ip)

二进制搜索，使用二进制搜索算法。

**参数**：`$ip` (string) - IP 地址
**返回值**：`array` - 查询结果数组

#### btreeSearch($ip)

B 树搜索，使用 B 树索引算法。

**参数**：`$ip` (string) - IP 地址
**返回值**：`array` - 查询结果数组

#### batchSearch($ips)

批量查询多个 IP 地址。

**参数**：`$ips` (array) - IP 地址数组
**返回值**：`array` - IP 地址为键的查询结果数组

**示例**：
```php
$results = $ip2region->batchSearch(['61.142.118.231', '8.8.8.8']);
// 返回: ['61.142.118.231' => '中国广东省中山市【电信】', '8.8.8.8' => '美国【Level3】']
```

### 信息获取方法

#### getIpInfo($ip)

获取详细的 IP 信息。

**参数**：`$ip` (string) - IP 地址
**返回值**：`array|null` - 包含详细信息的数组

**示例**：
```php
$info = $ip2region->getIpInfo('61.142.118.231');
// 返回: [
//   'country' => '中国',
//   'region' => '广东省',
//   'province' => '中山市',
//   'city' => '电信',
//   'isp' => '',
//   'ip' => '61.142.118.231',
//   'version' => 'v4'
// ]
```

#### getStats()

获取统计信息。

**返回值**：`array` - 包含内存使用、IO 计数、加载状态等

#### getMemoryUsage()

获取内存使用情况。

**返回值**：`array` - 包含当前内存、峰值内存、加载状态等

#### getIOCount()

获取 IO 计数。

**返回值**：`array` - 包含 IPv4、IPv6 和总 IO 计数

#### getDatabaseInfo()

获取数据库信息。

**返回值**：`array` - 包含加载状态、缓存策略、版本信息、自定义路径等

### 自定义数据库方法

#### setCustomDbPaths($v4Path, $v6Path)

动态设置自定义数据库路径。

**参数**：
- `$v4Path` (string|null) - IPv4 数据库文件路径
- `$v6Path` (string|null) - IPv6 数据库文件路径

#### isUsingCustomDb()

检查是否使用自定义数据库。

**返回值**：`array` - 包含 IPv4 和 IPv6 的使用状态

#### getCustomDbInfo()

获取自定义数据库文件信息。

**返回值**：`array` - 包含自定义数据库文件的大小、修改时间等信息

### 静态方法

#### Ip2Region::clearCache()

清理所有缓存。

#### Ip2Region::clearExpiredCache($days = 7)

清理过期缓存。

**参数**：`$days` (int) - 过期天数

#### Ip2Region::getCacheStats()

获取缓存统计信息。

**返回值**：`array` - 缓存统计信息

## 错误处理

所有方法在遇到错误时会：
- 返回 `null`（对于返回字符串的方法）
- 返回空数组（对于返回数组的方法）
- 抛出 `Exception`（对于严重错误）

**示例**：
```php
try {
    $result = $ip2region->simple('invalid-ip');
    if ($result === null) {
        echo "查询失败";
    }
} catch (Exception $e) {
    echo "错误: " . $e->getMessage();
}
```

## 性能优化

### 缓存策略

- **file**：文件缓存，适合大多数场景
- **vectorIndex**：向量索引缓存，适合频繁查询
- **content**：内容缓存，适合内存充足的环境

### 内存管理

- 使用懒加载机制，按需创建查询器
- 支持分片数据库，减少内存使用
- 提供内存使用统计和监控

### 查询优化

- 支持 IPv4 和 IPv6 双协议查询
- 智能缓存机制，提升重复查询性能
- 批量查询支持，提高处理效率
