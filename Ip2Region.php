<?php

/**
 * IP2Region - 高性能IP地址定位库
 *
 * 这是一个轻量级的IP地址定位库，支持IPv4和IPv6地址查询
 * 提供简单易用的API接口，支持多种数据库格式和缓存策略
 *
 * 主要特性：
 * - 支持IPv4和IPv6地址查询
 * - 支持分片数据库文件，优化大文件加载
 * - 多种缓存策略，提升查询性能
 * - 简单易用的API接口
 * - 高性能查询，毫秒级响应
 * - 通用查询函数，支持多种查询方法
 *
 * 使用示例：
 * ```php
 * // IPv4简单查询
 * $result = ip2region('61.142.118.231');
 *
 * // IPv4详细查询
 * $result = ip2region('61.142.118.231', 'search');
 *
 * // IPv6简单查询
 * $result = ip2region('2001:4860:4860::8888');
 *
 * // IPv6详细查询
 * $result = ip2region('2001:4860:4860::8888', 'search');
 *
 * // 高级查询
 * $searcher = new Ip2Region();
 * $result = $searcher->search('61.142.118.231');
 * ```
 */
if (!class_exists('Ip2Region')) {
    require_once __DIR__ . '/src/Ip2Region.php';
}

if (!function_exists('ip2region')) {
    /**
     * 通用IP地址查询函数
     * 
     * 提供简单易用的IP地址查询接口，支持多种查询方法
     * 自动处理IPv4和IPv6地址，支持分片数据库和智能缓存
     * 
     * 支持的查询方法：
     * - 'simple': 简单查询，返回格式化的地理位置信息
     * - 'search': 详细查询，返回完整的查询结果数组
     * - 'binary': 二进制查询，返回原始二进制数据
     * - 'btree': B树查询，使用B树索引进行查询
     * - 'memory': 内存查询，将数据库加载到内存中查询
     * 
     * 特性：
     * - 自动识别IPv4和IPv6地址
     * - 支持分片数据库自动合并
     * - 智能缓存机制，提升查询性能
     * - 异常安全，查询失败返回null
     * - 静态实例，避免重复初始化
     * 
     * @param string $ip IP地址（支持IPv4和IPv6）
     * @param string $method 查询方法，默认为'simple'
     * @return string|array|null 查询结果，失败返回null
     * 
     * @example
     * // IPv4简单查询
     * $result = ip2region('61.142.118.231');
     * // 输出: "中国广东省中山市【电信】"
     * 
     * @example
     * // IPv4详细查询
     * $result = ip2region('61.142.118.231', 'search');
     * // 输出: "中国|广东省|中山市|电信"
     * 
     * @example
     * // IPv6简单查询
     * $result = ip2region('2001:4860:4860::8888');
     * // 输出: "美国加利福尼亚州圣克拉拉【专线用户】"
     * 
     * @example
     * // IPv6详细查询
     * $result = ip2region('2001:4860:4860::8888', 'search');
     * // 输出: "美国|加利福尼亚州|圣克拉拉|专线用户"
     */
    function ip2region($ip, $method = 'simple')
    {
        static $searcher = null;
        if ($searcher === null) {
            $searcher = new Ip2Region();
        }
        try {
            if (method_exists($searcher, $method)) {
                return $searcher->$method($ip);
            } else {
                return $searcher->simple($ip);
            }
        } catch (Exception $e) {
            return null;
        }
    }
}
