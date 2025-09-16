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
     * 全局 IP 查询函数
     * 
     * 自动识别 IPv4/IPv6 地址，提供统一的查询接口
     * 
     * @param string $ip IP 地址
     * @param string $method 查询方法，支持：simple, search, memory, binary, btree
     * @return string|array|null 查询结果
     * @throws Exception 当 IP 地址格式无效时抛出异常
     * 
     * @example
     * // IPv4 查询
     * echo ip2region('61.142.118.231'); // 中国广东省中山市【电信】
     * echo ip2region('61.142.118.231', 'search'); // 中国|广东省|中山市|电信
     * 
     * // IPv6 查询
     * echo ip2region('2001:4860:4860::8888'); // 美国加利福尼亚州圣克拉拉【专线用户】
     * echo ip2region('2400:3200::1'); // 中国浙江省杭州市【专线用户】
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