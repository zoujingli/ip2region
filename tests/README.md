# 测试文件说明

本目录包含 IP2Region 项目的测试文件。

## 数据库优先级测试

IP2Region 采用智能优先级机制：

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

## 文件列表

### demo.php
演示程序，展示 IP2Region 的基本功能和使用方法。

**运行方式**：
```bash
composer demo
# 或
php tests/demo.php
```

**功能**：
- IPv4 查询演示
- IPv6 查询演示
- 详细信息查询
- 性能统计
- 内存使用情况
- 批量查询演示

### quick_performance_test.php
快速性能测试脚本，测试 IP2Region 在不同场景下的性能表现。

**运行方式**：
```bash
composer test:performance
# 或
php tests/quick_performance_test.php
```

**测试项目**：
- 首次加载测试（无缓存）：`new Ip2Region() + simple()`
- 缓存命中测试：`new Ip2Region() + simple()` (使用缓存)
- 不同查询方法性能对比：
  - `ip2region->simple()` (格式化输出)
  - `ip2region->search()` (原始数据)
  - `ip2region->memorySearch()` (数组格式)
- 批量查询测试：
  - `ip2region->batchSearch(10个IP)`
  - `ip2region->batchSearch(10000个IP)`
- 循环查询测试：10000次 `ip2region->simple()` 循环调用
- 内存使用测试：`getStats()` + `getMemoryUsage()`
- 缓存清理性能测试：`Ip2Region::clearCache() + clearPersistentCache()`

**测试结果**：
- IPv4 性能提升：98.4%（32ms → 0.5ms）
- IPv6 性能提升：99.9%（1175ms → 1ms）
- 查询方法性能：
  - `simple()`：0.17ms
  - `search()`：0.01ms
  - `memorySearch()`：0.01ms
- 批量查询：
  - 10个IP：1.94ms (5155 QPS)
  - 10000个IP：100.75ms (99256 QPS)
- 循环查询：10000次约 55.23ms (181061 QPS)
- 缓存清理：约 0.23ms

## 使用说明

1. **运行演示**：`composer demo`
2. **性能测试**：`composer test:performance`
3. **查询测试**：`composer test:ipv4` 或 `composer test:ipv6`

## 注意事项

- 性能测试会自动清理缓存，确保测试结果的准确性
- 首次运行需要合并分片文件，耗时较长
- 后续运行会使用缓存，性能显著提升
- 测试结果可能因硬件配置而异
