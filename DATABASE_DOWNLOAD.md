# 数据库文件下载说明

## 概述

ip2region 项目支持自定义数据库文件配置，但需要用户自行获取数据库文件。本文档详细说明如何获取和使用自定义数据库文件。

## 数据源选择

### 1. 免费版本（推荐用于学习和个人项目）

**来源**：[ip2region 官方 GitHub 仓库](https://github.com/lionsoul2014/ip2region)

**特点**：
- 开源免费
- 数据相对基础
- 适合学习和个人项目
- 更新频率较低

**获取方式**：

使用 wget：
```bash
# 下载 IPv4 数据库
wget https://raw.githubusercontent.com/lionsoul2014/ip2region/master/data/ip2region_v4.xdb

# 下载 IPv6 数据库（需要 Git LFS）
wget https://raw.githubusercontent.com/lionsoul2014/ip2region/master/data/ip2region_v6.xdb
```

使用 curl：
```bash
# 下载 IPv4 数据库
curl -L -o ip2region_v4.xdb https://raw.githubusercontent.com/lionsoul2014/ip2region/master/data/ip2region_v4.xdb

# 下载 IPv6 数据库（需要 Git LFS）
curl -L -o ip2region_v6.xdb https://raw.githubusercontent.com/lionsoul2014/ip2region/master/data/ip2region_v6.xdb
```

**重要说明**：
- IPv4 数据库可以直接下载
- IPv6 数据库文件较大（约 617MB），使用了 Git LFS 存储
- 如果直接下载失败，请使用 Git LFS 或从官网购买

### 2. 商业版本（推荐用于商业项目）

**来源**：[ip2region 官网](https://www.ip2region.net/)

**特点**：
- 数据更准确
- 更新更及时
- 支持更多地区
- 提供技术支持
- 适合商业项目

**获取方式**：
1. 访问 [ip2region 官网](https://www.ip2region.net/)
2. 注册账号
3. 购买数据库文件
4. 下载 `.xdb` 格式文件

### 3. 第三方数据源

**说明**：
- 可以集成其他 IP 地理位置数据库
- 需要转换为 xdb 格式
- 建议使用官方推荐的数据源

## 文件要求

### 格式要求
- **文件格式**：必须是 `.xdb` 格式
- **文件命名**：严格按照 `ip2region_v4.xdb` 和 `ip2region_v6.xdb` 命名
- **文件大小**：IPv4 约 11MB，IPv6 约 617MB

### 位置要求
- **推荐位置**：项目根目录或 `tools/` 目录
- **绝对路径**：建议使用绝对路径以避免路径解析问题
- **文件权限**：确保文件具有读取权限

## 使用步骤

### 1. 下载数据库文件

```bash
# 创建目录
mkdir -p tools/

# 下载 IPv4 数据库（免费版本）
# 使用 wget：
wget -O tools/ip2region_v4.xdb https://raw.githubusercontent.com/lionsoul2014/ip2region/master/data/ip2region_v4.xdb

# 或使用 curl：
curl -L -o tools/ip2region_v4.xdb https://raw.githubusercontent.com/lionsoul2014/ip2region/master/data/ip2region_v4.xdb

# 下载 IPv6 数据库（如果可用）
# 使用 wget：
wget -O tools/ip2region_v6.xdb https://raw.githubusercontent.com/lionsoul2014/ip2region/master/data/ip2region_v6.xdb

# 或使用 curl：
curl -L -o tools/ip2region_v6.xdb https://raw.githubusercontent.com/lionsoul2014/ip2region/master/data/ip2region_v6.xdb
```

### 2. 验证文件

```bash
# 检查文件是否存在
ls -la tools/ip2region_v*.xdb

# 检查文件大小
du -h tools/ip2region_v*.xdb

# 应该看到类似输出：
# -rw-r--r-- 1 user staff 11042429 Dec 19 10:00 tools/ip2region_v4.xdb
# -rw-r--r-- 1 user staff 617000000 Dec 19 10:00 tools/ip2region_v6.xdb
```

### 3. 生成分片文件（可选）

```bash
# 生成 IPv4 分片文件
php tools/split_db.php v4

# 生成 IPv6 分片文件
php tools/split_db.php v6
```

### 4. 使用自定义数据库

```php
<?php
require_once 'src/Ip2Region.php';

// 使用自定义数据库文件
$searcher = new Ip2Region('file', '/path/to/ip2region_v4.xdb', '/path/to/ip2region_v6.xdb');

// 查询IP
$result = $searcher->simple('8.8.8.8');
echo $result; // 输出：美国【Level3】
?>
```

### 5. 快速下载脚本

创建一个下载脚本 `download_db.sh`：

```bash
#!/bin/bash
# 数据库文件下载脚本

echo "开始下载 ip2region 数据库文件..."

# 创建目录
mkdir -p tools/

# 下载 IPv4 数据库
echo "下载 IPv4 数据库..."
if command -v wget &> /dev/null; then
    wget -O tools/ip2region_v4.xdb https://raw.githubusercontent.com/lionsoul2014/ip2region/master/data/ip2region_v4.xdb
elif command -v curl &> /dev/null; then
    curl -L -o tools/ip2region_v4.xdb https://raw.githubusercontent.com/lionsoul2014/ip2region/master/data/ip2region_v4.xdb
else
    echo "错误：未找到 wget 或 curl 命令"
    exit 1
fi

# 下载 IPv6 数据库
echo "下载 IPv6 数据库..."
if command -v wget &> /dev/null; then
    wget -O tools/ip2region_v6.xdb https://raw.githubusercontent.com/lionsoul2014/ip2region/master/data/ip2region_v6.xdb
elif command -v curl &> /dev/null; then
    curl -L -o tools/ip2region_v6.xdb https://raw.githubusercontent.com/lionsoul2014/ip2region/master/data/ip2region_v6.xdb
else
    echo "错误：未找到 wget 或 curl 命令"
    exit 1
fi

# 验证文件
echo "验证下载的文件..."
ls -la tools/ip2region_v*.xdb

echo "下载完成！"
```

使用方法：
```bash
chmod +x download_db.sh
./download_db.sh
```

## 注意事项

### 法律合规
- **正版数据**：确保使用正版数据源，避免版权问题
- **商业使用**：商业项目建议购买官方商业版数据库
- **数据更新**：定期更新数据库文件以获得最新的地理位置信息

### 技术注意事项
- **文件完整性**：确保下载的文件完整且未损坏
- **格式验证**：确保文件是有效的 xdb 格式
- **权限设置**：确保文件具有适当的读取权限
- **路径配置**：使用绝对路径以避免路径解析问题

### 性能优化
- **SSD 存储**：将数据库文件放在 SSD 上以获得更好的性能
- **内存缓存**：考虑使用 `new Ip2Region('content')` 进行内存缓存
- **定期更新**：定期更新数据库文件以获得最新数据

## 故障排除

### 常见问题

1. **文件不存在**
   - 检查文件路径是否正确
   - 确认文件已下载完成
   - 验证文件权限

2. **文件格式错误**
   - 确认文件是 `.xdb` 格式
   - 检查文件是否损坏
   - 重新下载文件

3. **权限问题**
   - 检查文件读取权限
   - 确认目录访问权限
   - 使用 `chmod` 设置权限

4. **路径问题**
   - 使用绝对路径
   - 检查路径分隔符
   - 确认文件存在

### 获取帮助

- **官方文档**：[ip2region 官方文档](https://github.com/lionsoul2014/ip2region)
- **问题反馈**：[GitHub Issues](https://github.com/lionsoul2014/ip2region/issues)
- **商业支持**：[ip2region 官网](https://www.ip2region.net/)

## 更新日志

- **2025-09-16**：添加数据库下载说明文档
- **2025-09-16**：完善自定义数据库配置功能
- **2025-09-16**：增强 PHP 5.4+ 兼容性支持
