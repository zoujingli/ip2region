# 贡献指南

感谢您对 ip2region 项目的关注和贡献！

## 如何贡献

### 报告问题

如果您发现了 bug 或有功能建议，请通过以下方式报告：

1. **GitHub Issues**：[提交问题](https://github.com/zoujingli/ip2region/issues)
2. **邮箱**：zoujingli@qq.com

在报告问题时，请包含以下信息：
- 详细的错误描述
- 复现步骤
- 环境信息（PHP 版本、操作系统等）
- 相关代码示例

### 提交代码

1. **Fork 项目**
2. **创建功能分支**：`git checkout -b feature/your-feature-name`
3. **提交更改**：`git commit -m "Add some feature"`
4. **推送分支**：`git push origin feature/your-feature-name`
5. **创建 Pull Request**

### 代码规范

- 遵循 PSR-2 编码标准
- 添加适当的注释和文档
- 确保代码兼容 PHP 5.4+
- 添加必要的测试用例

### 文档贡献

- 保持文档的准确性和时效性
- 使用清晰的中文表达
- 添加适当的代码示例
- 保持文档结构的一致性

## 开发环境

### 环境要求

- PHP 5.4+
- Composer
- Git

### 本地开发

```bash
# 克隆项目
git clone https://github.com/zoujingli/ip2region.git
cd ip2region

# 安装依赖
composer install

# 运行测试
composer test:ipv4
composer test:ipv6
```

## 版本发布

项目遵循 [语义化版本](https://semver.org/) 规范：

- **主版本号**：不兼容的 API 修改
- **次版本号**：向下兼容的功能性新增
- **修订号**：向下兼容的问题修正

## 许可证

本项目基于 Apache-2.0 许可证开源。贡献的代码将遵循相同的许可证。

## 联系方式

- **作者**：Anyon
- **邮箱**：zoujingli@qq.com
- **主页**：https://thinkadmin.top
- **GitHub**：https://github.com/zoujingli/ip2region
