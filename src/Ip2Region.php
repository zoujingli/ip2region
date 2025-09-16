<?php

/**
 * ip2region - 高性能IP地址定位库
 * 
 * 功能特性：
 * - 支持IPv4和IPv6地址查询
 * - 支持分片数据库文件，优化大文件加载
 * - 多种缓存策略（文件缓存、向量索引、内容缓存）
 * - 高性能查询，毫秒级响应
 * - 支持多种数据库格式（xdb、dat）
 * - 自动数据库版本检测和加载
 * 
 * 使用场景：
 * - 网站访问统计和地域分析
 * - 网络安全和访问控制
 * - 广告投放和精准营销
 * - 用户行为分析
 * 
 * 性能特点：
 * - 内存占用低，支持大数据库文件
 * - 查询速度快，支持高并发
 * - 支持分片数据库，减少内存使用
 * - 智能缓存机制，提升重复查询性能
 */

// Copyright 2022 The Ip2Region Authors. All rights reserved.
// Use of this source code is governed by a Apache2.0-style
// license that can be found in the LICENSE file.

if (!class_exists('ip2region\xdb\Searcher')) {
    require_once __DIR__ . '/XdbSearcher.php';
}
if (!class_exists('ChunkedDbHelper')) {
    require_once __DIR__ . '/ChunkedDbHelper.php';
}

/**
 * ip2region 主类
 * 
 * 提供统一的IP地址查询接口，支持IPv4和IPv6
 * 自动处理数据库加载、缓存管理和查询优化
 */
class Ip2Region
{
    private $searcherV4 = null;
    private $searcherV6 = null;
    private $cachePolicy = 'file'; // file, vectorIndex, content
    
    // 自定义数据库路径配置
    private $dbPathV4 = null;
    private $dbPathV6 = null;

    // 静态缓存，避免重复生成临时文件
    private static $mergedV4File = null;
    private static $mergedV6File = null;

    public function __construct($cachePolicy = 'file', $dbPathV4 = null, $dbPathV6 = null)
    {
        $this->cachePolicy = $cachePolicy;
        $this->dbPathV4 = $dbPathV4;
        $this->dbPathV6 = $dbPathV6;
    }

    public function __destruct()
    {
        if ($this->searcherV4 !== null) {
            $this->searcherV4->close();
        }
        if ($this->searcherV6 !== null) {
            $this->searcherV6->close();
        }
    }

    /**
     * 获取IP版本
     */
    private function getIpVersion($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return 'v4';
        } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return 'v6';
        } else {
            throw new Exception("无效的IP地址: {$ip}");
        }
    }

    /**
     * 懒加载获取对应的查询器
     */
    private function getSearcher($ip)
    {
        $version = $this->getIpVersion($ip);
        if ($version === 'v6') {
            // 懒加载 IPv6 查询器
            if ($this->searcherV6 === null) {
                $this->searcherV6 = $this->createSearcher('v6');
            }
            return $this->searcherV6;
        } else {
            // 懒加载 IPv4 查询器
            if ($this->searcherV4 === null) {
                $this->searcherV4 = $this->createSearcher('v4');
            }
            return $this->searcherV4;
        }
    }

    /**
     * 创建查询器
     */
    private function createSearcher($version)
    {
        try {
            if ($version === 'v4') {
                // 优先使用自定义数据库路径
                if ($this->dbPathV4 !== null && file_exists($this->dbPathV4)) {
                    $file = $this->dbPathV4;
                } else {
                    // 使用 ChunkedDbHelper 处理分割的 IPv4 文件
                    if (self::$mergedV4File === null) {
                        // 查找 IPv4 分片文件
                        $chunks = ChunkedDbHelper::findChunks(dirname(__DIR__) . '/tools/ip2region_v4.xdb');

                        if (!empty($chunks)) {
                            // 合并分割文件到缓存
                            $file = ChunkedDbHelper::mergeToCache($chunks);
                            if (!$file) {
                                throw new Exception("无法合并分割的IPv4数据库文件");
                            }
                            self::$mergedV4File = $file;
                        } else {
                            // 回退到完整文件
                            $file = dirname(__DIR__) . '/tools/ip2region_v4.xdb';
                            self::$mergedV4File = $file;
                        }
                    } else {
                        $file = self::$mergedV4File;
                    }
                }
                $ipVersion = \ip2region\xdb\IPv4::default();
            } else {
                // 优先使用自定义数据库路径
                if ($this->dbPathV6 !== null && file_exists($this->dbPathV6)) {
                    $file = $this->dbPathV6;
                } else {
                    // 使用 ChunkedDbHelper 处理分割的 IPv6 文件
                    if (self::$mergedV6File === null) {
                        // 直接查找分片文件，不依赖 getBaseFilePath
                        $chunks = ChunkedDbHelper::findChunks(null);

                        if (!empty($chunks)) {
                            // 合并分割文件到缓存
                            $file = ChunkedDbHelper::mergeToCache($chunks);
                            if (!$file) {
                                throw new Exception("无法合并分割的IPv6数据库文件");
                            }
                            self::$mergedV6File = $file;
                        } else {
                            // 回退到完整文件
                            $file = dirname(__DIR__) . '/tools/ip2region_v6.xdb';
                            self::$mergedV6File = $file;
                        }
                    } else {
                        $file = self::$mergedV6File;
                    }
                }
                $ipVersion = \ip2region\xdb\IPv6::default();
            }

            if (!file_exists($file)) {
                throw new Exception("数据库文件不存在: {$file}");
            }

            return \ip2region\xdb\Searcher::newWithFileOnly($ipVersion, $file);
        } catch (Exception $e) {
            throw new Exception("创建 {$version} 查询器失败: " . $e->getMessage());
        }
    }

    /**
     * 内存查询
     */
    public function memorySearch($ip)
    {
        $searcher = $this->getSearcher($ip);
        $region = $searcher->search($ip);
        return array('city_id' => 0, 'region' => $region === null ? '' : $region);
    }

    /**
     * 批量查询
     */
    public function batchSearch($ips)
    {
        $results = array();
        foreach ($ips as $ip) {
            try {
                $result = $this->memorySearch($ip);
                $results[$ip] = isset($result['region']) ? $result['region'] : null;
            } catch (Exception $e) {
                $results[$ip] = null;
            }
        }
        return $results;
    }

    /**
     * IPv6 专用查询
     */
    public function searchIPv6($ip)
    {
        if (!$this->isIPv6($ip)) {
            throw new Exception("不是有效的IPv6地址: {$ip}");
        }
        $result = $this->memorySearch($ip);
        return isset($result['region']) ? $result['region'] : null;
    }

    /**
     * 获取IP信息
     */
    public function getIpInfo($ip)
    {
        $result = $this->memorySearch($ip);
        if ($result === null || !isset($result['region'])) {
            return null;
        }

        $parts = explode('|', $result['region']);
        return array(
            'country'  => isset($parts[0]) ? $parts[0] : '',
            'region'   => isset($parts[1]) ? $parts[1] : '',
            'province' => isset($parts[2]) ? $parts[2] : '',
            'city'     => isset($parts[3]) ? $parts[3] : '',
            'isp'      => isset($parts[4]) ? $parts[4] : '',
            'ip'       => $ip,
            'version'  => $this->getIpVersion($ip)
        );
    }

    /**
     * 检查是否为IPv6
     */
    private function isIPv6($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    /**
     * 获取统计信息
     */
    public function getStats()
    {
        $stats = array(
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'v4_io_count' => 0,
            'v6_io_count' => 0,
            'v4_loaded' => $this->searcherV4 !== null,
            'v6_loaded' => $this->searcherV6 !== null,
            'cache_policy' => $this->cachePolicy
        );

        if ($this->searcherV4 !== null) {
            $stats['v4_io_count'] = $this->searcherV4->getIOCount() === null ? 0 : $this->searcherV4->getIOCount();
        }
        if ($this->searcherV6 !== null) {
            $stats['v6_io_count'] = $this->searcherV6->getIOCount() === null ? 0 : $this->searcherV6->getIOCount();
        }

        return $stats;
    }

    /**
     * 获取内存使用情况
     */
    public function getMemoryUsage()
    {
        $memory = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);

        return array(
            'current'   => $this->formatBytes($memory),
            'peak'      => $this->formatBytes($peak),
            'v4_loaded' => $this->searcherV4 !== null,
            'v6_loaded' => $this->searcherV6 !== null
        );
    }

    /**
     * 格式化字节数
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * 清理缓存
     */
    public static function clearCache()
    {
        ChunkedDbHelper::clearCache();
        self::$mergedV4File = null;
        self::$mergedV6File = null;
    }

    /**
     * 清理过期缓存
     */
    public static function clearExpiredCache($days = 7)
    {
        ChunkedDbHelper::clearExpiredCache($days);
    }

    /**
     * 获取缓存统计信息
     */
    public static function getCacheStats()
    {
        return ChunkedDbHelper::getCacheStats();
    }

    /**
     * 简单查询方法（兼容旧版本）
     * @param string $ip IP地址
     * @return string|null 查询结果
     */
    public function simple($ip)
    {
        $geo = $this->memorySearch($ip);
        $arr = explode('|', str_replace(array('0|'), '|', isset($geo['region']) ? $geo['region'] : ''));
        if (($last = array_pop($arr)) === '内网IP') $last = '';
        return join('', $arr) . (empty($last) ? '' : "【{$last}】");
    }

    /**
     * 搜索方法（兼容旧版本）
     * @param string $ip IP地址
     * @return string|null 查询结果
     */
    public function search($ip)
    {
        $result = $this->memorySearch($ip);
        return isset($result['region']) ? $result['region'] : null;
    }

    /**
     * 二进制搜索方法（兼容旧版本）
     * @param string $ip IP地址
     * @return array 查询结果
     */
    public function binarySearch($ip)
    {
        return $this->memorySearch($ip);
    }

    /**
     * 二进制字节搜索方法
     * @param string $ipBytes 二进制IP地址
     * @return string|null 查询结果
     */
    public function searchByBytes($ipBytes)
    {
        // 确定IP版本
        $version = strlen($ipBytes) == 4 ? 'v4' : 'v6';

        if ($version === 'v4') {
            if ($this->searcherV4 === null) {
                $this->searcherV4 = $this->createSearcher('v4');
            }
            return $this->searcherV4->searchByBytes($ipBytes);
        } else {
            if ($this->searcherV6 === null) {
                $this->searcherV6 = $this->createSearcher('v6');
            }
            return $this->searcherV6->searchByBytes($ipBytes);
        }
    }

    /**
     * B树搜索方法（兼容旧版本）
     * @param string $ip IP地址
     * @return array 查询结果
     */
    public function btreeSearch($ip)
    {
        return $this->memorySearch($ip);
    }

    /**
     * 获取IP协议版本（公共方法）
     * @param string $ip IP地址
     * @return string IP版本
     */
    public function getProtocolVersion($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return 'v4';
        } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return 'v6';
        } else {
            return 'unknown';
        }
    }

    /**
     * 获取IO计数
     * @return array IO计数信息
     */
    public function getIOCount()
    {
        $stats = $this->getStats();
        return array(
            'v4_io_count' => $stats['v4_io_count'],
            'v6_io_count' => $stats['v6_io_count'],
            'total_io_count' => $stats['v4_io_count'] + $stats['v6_io_count']
        );
    }

    /**
     * 检查是否支持IPv6
     * @return bool 是否支持IPv6
     */
    public function isIPv6Supported()
    {
        return true;
    }

    /**
     * 检查是否支持IPv4
     * @return bool 是否支持IPv4
     */
    public function isIPv4Supported()
    {
        return true;
    }

    /**
     * 获取数据库信息
     * @return array 数据库信息
     */
    public function getDatabaseInfo()
    {
        $info = array(
            'v4_loaded' => $this->searcherV4 !== null,
            'v6_loaded' => $this->searcherV6 !== null,
            'cache_policy' => $this->cachePolicy,
            'custom_v4_path' => $this->dbPathV4,
            'custom_v6_path' => $this->dbPathV6
        );

        if ($this->searcherV4 !== null) {
            $info['v4_version'] = $this->searcherV4->getIPVersion();
        }
        if ($this->searcherV6 !== null) {
            $info['v6_version'] = $this->searcherV6->getIPVersion();
        }

        return $info;
    }

    /**
     * 获取自定义数据库文件信息
     * @return array 自定义数据库文件信息
     */
    public function getCustomDbInfo()
    {
        $info = array(
            'v4' => ChunkedDbHelper::getDbFileInfo($this->dbPathV4),
            'v6' => ChunkedDbHelper::getDbFileInfo($this->dbPathV6)
        );

        return $info;
    }

    /**
     * 设置自定义数据库路径
     * @param string $v4Path IPv4数据库路径
     * @param string $v6Path IPv6数据库路径
     */
    public function setCustomDbPaths($v4Path = null, $v6Path = null)
    {
        $this->dbPathV4 = $v4Path;
        $this->dbPathV6 = $v6Path;
        
        // 重置查询器，强制重新加载
        if ($this->searcherV4 !== null) {
            $this->searcherV4->close();
            $this->searcherV4 = null;
        }
        if ($this->searcherV6 !== null) {
            $this->searcherV6->close();
            $this->searcherV6 = null;
        }
    }

    /**
     * 检查是否使用自定义数据库
     * @return array 使用状态
     */
    public function isUsingCustomDb()
    {
        return array(
            'v4' => $this->dbPathV4 !== null && ChunkedDbHelper::isCustomDbExists($this->dbPathV4),
            'v6' => $this->dbPathV6 !== null && ChunkedDbHelper::isCustomDbExists($this->dbPathV6)
        );
    }
}
