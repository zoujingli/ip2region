<?php
/**
 * ip2region 全局函数库
 * 
 * 提供便捷的全局函数接口，支持 IPv4 和 IPv6 地址查询
 * 
 * @package ip2region
 * @version 3.0.0
 * @author Anyon <zoujingli@qq.com>
 * @link https://github.com/zoujingli/ip2region
 * @license Apache-2.0
 */

if (!function_exists('ip2region')) {
    /**
     * 全局 IP 地理位置查询函数
     * 
     * 这是一个便捷的全局函数，提供统一的 IP 地址地理位置查询接口。
     * 支持 IPv4 和 IPv6 地址的自动识别，并提供多种查询方法以满足不同需求。
     * 
     * **特性：**
     * - 自动识别 IPv4/IPv6 地址类型
     * - 支持多种查询方法和返回格式
     * - 内置 IP 地址格式验证
     * - 懒加载机制，按需初始化查询器
     * - 异常安全，提供详细的错误信息
     * 
     * **支持的查询方法：**
     * - `simple` (默认): 返回格式化的地理位置字符串，如 "中国广东省中山市【电信】"
     * - `search`: 返回原始查询结果，如 "中国|广东省|中山市|电信"
     * - `memory`: 返回数组格式，包含 city_id 和 region 字段
     * - `binary`: 使用二进制搜索算法，返回数组格式
     * - `btree`: 使用 B 树索引算法，返回数组格式
     * 
     * @param string $ip IP 地址，支持 IPv4 和 IPv6 格式
     * @param string $method 查询方法，可选值：simple, search, memory, binary, btree
     * @return string|array|null 查询结果，失败时返回 null
     * @throws Exception 当 IP 地址格式无效时抛出异常
     * 
     * @example
     * // IPv4 查询示例
     * echo ip2region('61.142.118.231'); 
     * // 输出: 中国广东省中山市【电信】
     * 
     * echo ip2region('61.142.118.231', 'search'); 
     * // 输出: 中国|广东省|中山市|电信
     * 
     * $result = ip2region('61.142.118.231', 'memory');
     * // 输出: Array([city_id] => 0, [region] => 中国|广东省|中山市|电信)
     * 
     * // IPv6 查询示例
     * echo ip2region('2001:4860:4860::8888'); 
     * // 输出: 美国加利福尼亚州圣克拉拉【专线用户】
     * 
     * echo ip2region('2400:3200::1'); 
     * // 输出: 中国浙江省杭州市【专线用户】
     * 
     * // 异常处理示例
     * try {
     *     $result = ip2region('invalid-ip');
     * } catch (Exception $e) {
     *     echo "错误: " . $e->getMessage();
     * }
     * 
     * @since 3.0.0
     * @see \Ip2Region 底层查询类
     */
    function ip2region($ip, $method = 'simple')
    {
        // 验证 IP 地址格式
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new Exception('Invalid IP address format: ' . $ip);
        }
        
        // 懒加载 Ip2Region 类
        if (!class_exists('Ip2Region')) {
            require_once __DIR__ . '/src/Ip2Region.php';
        }
        
        // 创建实例并查询
        $ip2region = new Ip2Region();
        
        switch (strtolower($method)) {
            case 'memory':
                return $ip2region->memorySearch($ip);
            case 'binary':
                return $ip2region->binarySearch($ip);
            case 'btree':
                return $ip2region->btreeSearch($ip);
            case 'search':
                return $ip2region->search($ip);
            case 'simple':
            default:
                return $ip2region->simple($ip);
        }
    }
}