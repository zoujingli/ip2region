<?php

/**
 * ip2region - 高性能IP地址定位库
 *
 * 核心功能：
 * - 支持 IPv4 和 IPv6 地址查询
 * - 支持多种缓存策略（file、vectorIndex、content）
 * - 智能数据库加载（自动路径、vendor目录、默认路径）
 * - 高性能查询，毫秒级响应
 * - 支持 XDB 数据库格式
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
 * - 支持多种缓存策略，优化不同场景
 * - 智能加载机制，自动查找最优数据库文件
 *
 * 数据库优先级：
 * 1. 自定义数据库路径（通过构造函数指定）
 * 2. 下载的数据库文件（vendor/bin/ip2data/ 目录）
 * 3. 内置数据库文件（db/ 目录，IPv4 和 IPv6）
 *
 * @author Anyon <zoujingli@qq.com>
 * @version 3.0
 * @since 2022-01-01
 */

// Copyright 2022 The Ip2Region Authors. All rights reserved.
// Use of this source code is governed by a Apache2.0-style
// license that can be found in the LICENSE file.

// 通过 Composer autoload 自动加载

/**
 * ip2region 主类
 *
 * 提供统一的IP地址查询接口，支持IPv4和IPv6
 * 自动处理数据库加载和查询优化
 */
class Ip2Region
{
    private $searcherV4 = null;
    private $searcherV6 = null;

    // 缓存策略配置
    private $cachePolicy = 'file'; // file, vectorIndex, content

    // 自定义数据库路径配置
    private $dbPathV4 = null;
    private $dbPathV6 = null;


    /**
     * 构造函数
     *
     * 初始化 IP2Region 实例，配置缓存策略和自定义数据库路径
     *
     * @param string $cachePolicy 缓存策略，可选值：
     * - 'file': 文件缓存（默认）
     * - 'vectorIndex': 向量索引缓存
     * - 'content': 内容缓存
     * @param string|null $dbPathV4 IPv4 数据库自定义路径，为 null 时使用默认路径
     * @param string|null $dbPathV6 IPv6 数据库自定义路径，为 null 时使用默认路径
     *
     * @example
     * ```php
     * // 使用默认配置
     * $searcher = new Ip2Region();
     *
     * // 使用向量索引缓存策略
     * $searcher = new Ip2Region('vectorIndex');
     *
     * // 使用自定义数据库路径
     * $searcher = new Ip2Region('file', '/path/to/ipv4.xdb', '/path/to/ipv6.xdb');
     * ```
     */
    public function __construct(string $cachePolicy = 'file', ?string $dbPathV4 = null, ?string $dbPathV6 = null)
    {
        $this->cachePolicy = $cachePolicy;
        $this->dbPathV4 = $dbPathV4;
        $this->dbPathV6 = $dbPathV6;
    }

    /**
     * 析构函数
     *
     * 自动清理资源，关闭已打开的搜索引擎连接
     * 确保在对象销毁时释放文件句柄和内存资源
     *
     * @return void
     */
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
     * 获取IP地址版本
     *
     * 检测给定的IP地址是IPv4还是IPv6格式
     *
     * @param string $ip 要检测的IP地址
     * @return string 返回 'v4' 表示IPv4，'v6' 表示IPv6
     * @throws \Exception 当IP地址格式无效时抛出异常
     *
     * @example
     * ```php
     * $searcher = new Ip2Region();
     * echo $searcher->getIpVersion('61.142.118.231'); // 输出: v4
     * echo $searcher->getIpVersion('2400:3200::1'); // 输出: v6
     * ```
     */
    private function getIpVersion(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return 'v4';
        } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return 'v6';
        } else {
            throw new \Exception("无效的IP地址: {$ip}");
        }
    }

    /**
     * 懒加载获取对应的搜索引擎
     *
     * 根据IP版本自动创建或返回对应的搜索引擎实例
     * 采用懒加载模式，只有在实际查询时才创建搜索引擎
     *
     * @param string $ip 要查询的IP地址
     * @return \ip2region\xdb\Searcher 返回对应版本的搜索引擎实例
     * @throws \Exception 当IP地址无效或搜索引擎创建失败时抛出异常
     *
     * @example
     * ```php
     * $searcher = new Ip2Region();
     * $v4Searcher = $searcher->getSearcher('61.142.118.231'); // 返回IPv4搜索引擎
     * $v6Searcher = $searcher->getSearcher('2400:3200::1'); // 返回IPv6搜索引擎
     * ```
     */
    private function getSearcher(string $ip): \ip2region\xdb\Searcher
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
     * 获取数据库文件
     *
     * 按照优先级顺序查找数据库文件：
     * 1. 自动路径：自定义数据库路径（通过构造函数指定）
     * 2. vendor 目录：下载的数据库文件（vendor/bin/ip2data/ 目录）
     * 3. 默认路径：内置数据库文件（db/ 目录）
     *
     * @param string $version 版本标识，'v4' 表示IPv4，'v6' 表示IPv6
     * @return string 返回可用的数据库文件路径
     * @throws \Exception 当找不到可用的数据库文件时抛出异常
     *
     * @example
     * ```php
     * $searcher = new Ip2Region();
     * $v4DbPath = $searcher->getDbFile('v4'); // 获取IPv4数据库路径
     * $v6DbPath = $searcher->getDbFile('v6'); // 获取IPv6数据库路径
     * ```
     */
    private function detectProjectRoot(): ?string
    {
        // 通过反射获取实际文件路径
        $reflection = new ReflectionClass($this);
        $actualPath = dirname($reflection->getFileName());

        // 检查是否在 PHAR 环境中
        if (strpos($actualPath, 'phar://') === 0) {
            // PHAR 环境：无法确定项目根目录，返回 null
            // 在 PHAR 环境中，只能使用内置数据库文件
            return null;
        }

        // 只有两种情况：
        // 1. vendor/zoujingli/ip2region/src (Composer 安装) - 最具体
        // 2. src (开发模式) - 最通用

        if (strpos($actualPath, 'vendor' . DIRECTORY_SEPARATOR . 'zoujingli' . DIRECTORY_SEPARATOR . 'ip2region' . DIRECTORY_SEPARATOR . 'src') !== false) {
            // 情况1：vendor/zoujingli/ip2region/src
            // 需要往上4级：vendor/zoujingli/ip2region/src -> zoujingli/ip2region -> vendor -> project_root
            $projectRoot = dirname($actualPath, 4);
        } else {
            // 情况2：src (开发模式)
            // 向上1级就是项目根目录
            $projectRoot = dirname($actualPath);
        }

        // 验证找到的根目录是否正确（包含 composer.json）
        if (!file_exists($projectRoot . DIRECTORY_SEPARATOR . 'composer.json')) {
            die('错误：无法找到项目根目录，请确保在正确的 Composer 项目中使用此库');
        }

        return $projectRoot;
    }

    private function getDbFile(string $version): string
    {
        $dbPath = $version === 'v4' ? $this->dbPathV4 : $this->dbPathV6;

        // 1. 自动路径：如果使用自定义数据库，直接返回
        if ($dbPath !== null && file_exists($dbPath)) {
            return $dbPath;
        }

        // 2. vendor 目录：检查下载的数据库文件
        $projectRoot = $this->detectProjectRoot();
        if ($projectRoot !== null) {
            $downloadedFile = $projectRoot . '/vendor/bin/ip2data/ip2region_' . $version . '.xdb';
            if (file_exists($downloadedFile)) {
                return $downloadedFile;
            }
        }

        // 3. 默认路径：检查 db/ 目录下的数据库文件
        $dbFile = dirname(__DIR__) . '/db/ip2region_' . $version . '.xdb';
        if (file_exists($dbFile)) {
            return $dbFile;
        }

        // 4. 抛出异常，提示用户下载数据库
        if ($version === 'v6') {
            throw new \Exception("IPv6 查询需要下载完整数据库文件。\n\n下载方式：\n1. 使用 Composer 命令：composer download:v6\n2. 使用下载工具：./vendor/bin/ip2down download v6\n3. 手动下载（代理优先）：https://gh-proxy.org/https://raw.githubusercontent.com/lionsoul2014/ip2region/master/data/ip2region_v6.xdb\n   备用直链：https://raw.githubusercontent.com/lionsoul2014/ip2region/master/data/ip2region_v6.xdb");
        } else {
            throw new \Exception("未找到 IPv4 数据库文件。\n\n解决方案：\n1. 使用 Composer 命令：composer download:v4\n2. 使用下载工具：./vendor/bin/ip2down download v4\n3. 确保数据库文件 ip2region_v4.xdb 存在于 db/ 目录中");
        }
    }

    /**
     * 创建搜索引擎实例
     *
     * 根据指定版本创建对应的搜索引擎实例，支持IPv4和IPv6
     *
     * @param string $version 版本标识，'v4' 表示IPv4，'v6' 表示IPv6
     * @return \ip2region\xdb\Searcher 返回搜索引擎实例
     * @throws \Exception 当数据库文件不存在或搜索引擎创建失败时抛出异常
     *
     * @example
     * ```php
     * $searcher = new Ip2Region();
     * $v4Searcher = $searcher->createSearcher('v4'); // 创建IPv4搜索引擎
     * $v6Searcher = $searcher->createSearcher('v6'); // 创建IPv6搜索引擎
     * ```
     */
    private function createSearcher(string $version): \ip2region\xdb\Searcher
    {
        try {
            // 获取数据库文件
            $file = $this->getDbFile($version);

            if ($version === 'v4') {
                $ipVersion = 4;
            } else {
                $ipVersion = 6;
            }

            if (!file_exists($file)) {
                throw new \Exception("数据库文件不存在: {$file}");
            }

            // 根据缓存策略创建搜索引擎
            switch ($this->cachePolicy) {
                case 'vectorIndex':
                    // 向量索引模式需要先读取向量索引
                    $vectorIndex = file_get_contents($file, false, null, 0, 8192);
                    return \ip2region\xdb\Searcher::newWithVectorIndex($ipVersion, $file, $vectorIndex);
                case 'content':
                    // 内容缓存模式需要先读取整个文件
                    $content = file_get_contents($file);
                    return \ip2region\xdb\Searcher::newWithBuffer($ipVersion, $content);
                case 'file':
                default:
                    return \ip2region\xdb\Searcher::newWithFileOnly($ipVersion, $file);
            }
        } catch (\Exception $e) {
            // 如果是数据库文件相关的错误，直接传递原始错误信息
            if (
                strpos($e->getMessage(), 'IPv6 查询需要下载') !== false ||
                strpos($e->getMessage(), '未找到 IPv4 数据库文件') !== false
            ) {
                throw $e;
            }
            throw new \Exception("创建 {$version} 查询器失败: " . $e->getMessage());
        }
    }

    /**
     * 内存查询
     *
     * 使用内存模式查询IP地址的地理位置信息
     * 这是最常用的查询方法，返回标准格式的结果
     *
     * @param string $ip 要查询的IP地址（支持IPv4和IPv6）
     * @return array 返回包含城市ID和地区信息的数组
     *  格式：['city_id' => int, 'region' => string]
     * @throws \Exception 当IP地址无效或查询失败时抛出异常
     *
     * @example
     * ```php
     * $searcher = new Ip2Region();
     * $result = $searcher->memorySearch('61.142.118.231');
     * echo $result['region']; // 输出：中国|广东省|中山市|电信|CN
     *
     * $result = $searcher->memorySearch('8.8.8.8');
     * echo $result['region']; // 输出：United States|California|0|Google LLC|US
     * ```
     */
    public function memorySearch(string $ip): array
    {
        $searcher = $this->getSearcher($ip);
        $region = $searcher->search($ip);
        return array('city_id' => 0, 'region' => $region === null ? '' : $region);
    }

    /**
     * 批量查询
     *
     * 一次性查询多个IP地址的地理位置信息
     * 支持IPv4和IPv6混合查询，自动处理查询失败的情况
     *
     * @param array $ips 要查询的IP地址数组
     * @return array 返回以IP地址为键，地区信息为值的关联数组
     *  查询失败的IP地址对应的值为空字符串 ""
     *
     * @example
     * ```php
     * $searcher = new Ip2Region();
     * $ips = array('61.142.118.231', '1.1.1.1', '114.114.114.114');
     * $results = $searcher->batchSearch($ips);
     * foreach ($results as $ip => $region) {
     *     echo "$ip => $region\n";
     * }
     * ```
     */
    public function batchSearch(array $ips): array
    {
        $results = array();
        foreach ($ips as $ip) {
            try {
                $result = $this->memorySearch($ip);
                $results[$ip] = isset($result['region']) ? $result['region'] : '';
            } catch (Exception $e) {
                $results[$ip] = '';
            }
        }
        return $results;
    }

    /**
     * IPv6 专用查询
     *
     * 专门用于查询IPv6地址的地理位置信息
     * 包含IPv6地址格式验证，确保只处理有效的IPv6地址
     *
     * @param string $ip 要查询的IPv6地址
     * @return string 返回地理位置字符串，查询失败返回空字符串 ""
     * @throws \Exception 当不是有效的IPv6地址时抛出异常
     *
     * @example
     * ```php
     * $searcher = new Ip2Region();
     * $result = $searcher->searchIPv6('2400:3200::1');
     * echo $result; // 输出：中国|浙江省|杭州市|专线用户
     * ```
     */
    public function searchIPv6(string $ip): string
    {
        if (!$this->isIPv6($ip)) {
            throw new \Exception("不是有效的IPv6地址: {$ip}");
        }
        $result = $this->memorySearch($ip);
        return isset($result['region']) ? $result['region'] : '';
    }

    /**
     * 获取IP详细信息
     *
     * 查询IP地址并返回结构化的地理位置信息
     * 将原始查询结果解析为包含国家、省份、城市、ISP等信息的数组
     *
     * @param string $ip 要查询的IP地址（支持IPv4和IPv6）
     * @return array|null 返回包含详细地理信息的数组，查询失败返回 null
     *      格式：[
     *        'country' => '国家',
     *        'province' => '省份',
     *        'city' => '城市',
     *        'isp' => 'ISP服务商',
     *        'ip' => '原始IP地址',
     *        'version' => 'IP版本(v4/v6)',
     *        'region' => '' // 已弃用，原用于世界级区域，现已不再使用
     *      ]
     *
     * @example
     * ```php
     * $searcher = new Ip2Region();
     * $info = $searcher->getIpInfo('61.142.118.231');
     * echo $info['country']; // 输出：中国
     * echo $info['province']; // 输出：广东省
     * echo $info['city']; // 输出：中山市
     * echo $info['isp']; // 输出：电信
     *
     * $info = $searcher->getIpInfo('8.8.8.8');
     * echo $info['country']; // 输出：美国
     * echo $info['isp']; // 输出：Google LLC
     * ```
     */
    public function getIpInfo(string $ip): ?array
    {
        $result = $this->memorySearch($ip);
        if ($result === null || !isset($result['region'])) {
            return null;
        }

        $parts = explode('|', $result['region']);
        // 数据格式：国家|省份|城市|ISP (4个字段)
        return array(
            'country'  => isset($parts[0]) ? $parts[0] : '',
            'province' => isset($parts[1]) ? $parts[1] : '', // 省份
            'city'     => isset($parts[2]) ? $parts[2] : '', // 城市
            'isp'      => isset($parts[3]) ? $parts[3] : '', // ISP
            'ip'       => $ip,
            'version'  => $this->getIpVersion($ip),
            'region'   => '' // 已弃用，原用于世界级区域，现已不再使用
        );
    }

    /**
     * 检查是否为IPv6地址
     *
     * 使用PHP内置函数验证给定字符串是否为有效的IPv6地址格式
     *
     * @param string $ip 要检查的IP地址字符串
     * @return bool 返回 true 表示是有效的IPv6地址，false 表示不是
     *
     * @example
     * ```php
     * $searcher = new Ip2Region();
     * var_dump($searcher->isIPv6('2400:3200::1')); // 输出：true
     * var_dump($searcher->isIPv6('61.142.118.231')); // 输出：false
     * ```
     */
    private function isIPv6(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    /**
     * 获取统计信息
     *
     * 返回当前实例的详细统计信息，包括内存使用、IO次数、加载状态等
     * 用于性能监控和调试
     *
     * @return array 返回包含统计信息的数组
     *  格式：[
     *    'memory_usage' => int,      // 当前内存使用量（字节）
     *    'peak_memory' => int,       // 峰值内存使用量（字节）
     *    'v4_io_count' => int,       // IPv4 IO操作次数
     *    'v6_io_count' => int,       // IPv6 IO操作次数
     *    'v4_loaded' => bool,        // IPv4搜索引擎是否已加载
     *    'v6_loaded' => bool,        // IPv6搜索引擎是否已加载
     *    'cache_policy' => string    // 当前缓存策略
     *  ]
     *
     * @example
     * ```php
     * $searcher = new Ip2Region();
     * $searcher->memorySearch('61.142.118.231'); // 触发IPv4搜索引擎加载
     * $stats = $searcher->getStats();
     * echo "内存使用: " . $stats['memory_usage'] . " 字节\n";
     * echo "IPv4已加载: " . ($stats['v4_loaded'] ? '是' : '否') . "\n";
     * ```
     */
    public function getStats(): array
    {
        $stats = array(
            'memory_usage' => memory_get_usage(true),
            'peak_memory'  => memory_get_peak_usage(true),
            'v4_io_count'  => 0,
            'v6_io_count'  => 0,
            'v4_loaded'    => $this->searcherV4 !== null,
            'v6_loaded'    => $this->searcherV6 !== null,
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
     *
     * 返回当前实例的内存使用情况，包括当前内存和峰值内存
     * 提供人性化的字节格式显示
     *
     * @return array 返回包含内存使用信息的数组
     *  格式：[
     *    'current' => string,    // 当前内存使用量（格式化后）
     *    'peak' => string,       // 峰值内存使用量（格式化后）
     *    'v4_loaded' => bool,    // IPv4搜索引擎是否已加载
     *    'v6_loaded' => bool     // IPv6搜索引擎是否已加载
     *  ]
     *
     * @example
     * ```php
     * $searcher = new Ip2Region();
     * $memory = $searcher->getMemoryUsage();
     * echo "当前内存: " . $memory['current'] . "\n";
     * echo "峰值内存: " . $memory['peak'] . "\n";
     * ```
     */
    public function getMemoryUsage(): array
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
     *
     * 将字节数转换为人类可读的格式（B、KB、MB、GB、TB）
     *
     * @param int $bytes 要格式化的字节数
     * @param int $precision 小数位数，默认为2位
     * @return string 返回格式化后的字符串，如 "1.23 MB"
     *
     * @example
     * ```php
     * $searcher = new Ip2Region();
     * echo $searcher->formatBytes(1024); // 输出：1 KB
     * echo $searcher->formatBytes(1048576); // 输出：1 MB
     * ```
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, $precision) . ' ' . $units[$i];
    }


    /**
     * 简单查询方法（兼容旧版本）
     *
     * 提供简化的查询接口，返回格式化的地理位置字符串
     * 自动处理空值和内网IP，提供更友好的显示格式
     *
     * @param string $ip 要查询的IP地址（支持IPv4和IPv6）
     * @return string|null 返回格式化的地理位置字符串，查询失败返回 null
     *
     * @example
     * ```php
     * $searcher = new Ip2Region();
     * echo $searcher->simple('61.142.118.231'); // 输出：中国广东省中山市【电信】
     * echo $searcher->simple('114.114.114.114'); // 输出：中国江苏省南京市
     * echo $searcher->simple('8.8.8.8'); // 输出：United StatesCalifornia【Google LLC】
     * ```
     */
    public function simple(string $ip): ?string
    {
        $geo = $this->memorySearch($ip);
        $region = isset($geo['region']) ? $geo['region'] : '';
        if ($region === '') {
            return '';
        }

        // XDB 数据可能包含第 5 段国家/地区代码，例如：
        // 中国|广东省|中山市|电信|CN
        // simple() 保持旧版友好格式，仅展示国家/省/市和 ISP，不把国家代码误当 ISP。
        $parts = explode('|', $region);
        $location = array();
        for ($i = 0; $i < 3; $i++) {
            $part = isset($parts[$i]) ? $parts[$i] : '';
            $lastLocation = empty($location) ? null : $location[count($location) - 1];
            if ($part !== '' && $part !== '0' && $part !== $lastLocation) {
                $location[] = $part;
            }
        }

        $isp = isset($parts[3]) ? $parts[3] : '';
        if ($isp === '0' || $isp === '内网IP') {
            $isp = '';
        }

        return join('', $location) . ($isp === '' ? '' : "【{$isp}】");
    }

    /**
     * 搜索方法（兼容旧版本）
     *
     * 提供基础的搜索接口，返回原始的地理位置字符串
     * 与 memorySearch 方法功能相同，但返回格式更简洁
     *
     * @param string $ip 要查询的IP地址（支持IPv4和IPv6）
     * @return string 返回原始地理位置字符串，查询失败返回空字符串 ""
     *
     * @example
     * ```php
     * $searcher = new Ip2Region();
     * echo $searcher->search('61.142.118.231'); // 输出：中国|广东省|中山市|电信|CN
     * echo $searcher->search('114.114.114.114'); // 输出：中国|江苏省|南京市|0|CN
     * echo $searcher->search('0.0.0.0'); // 输出：""（空字符串）
     * ```
     */
    public function search(string $ip): string
    {
        $result = $this->memorySearch($ip);
        return isset($result['region']) ? $result['region'] : '';
    }

    /**
     * 二进制搜索方法（兼容旧版本）
     *
     * 提供二进制搜索接口，与 memorySearch 方法功能相同
     * 主要用于向后兼容，实际使用 memorySearch 方法
     *
     * @param string $ip 要查询的IP地址（支持IPv4和IPv6）
     * @return array 返回包含城市ID和地区信息的数组
     *  格式：['city_id' => int, 'region' => string]
     *
     * @example
     * ```php
     * $searcher = new Ip2Region();
     * $result = $searcher->binarySearch('61.142.118.231');
     * echo $result['region']; // 输出：中国|广东省|中山市|电信|CN
     *
     * $result = $searcher->binarySearch('8.8.8.8');
     * echo $result['region']; // 输出：United States|California|0|Google LLC|US
     * ```
     */
    public function binarySearch(string $ip): array
    {
        return $this->memorySearch($ip);
    }

    /**
     * 二进制字节搜索方法
     *
     * 使用二进制格式的IP地址进行查询，支持IPv4和IPv6
     * IPv4使用4字节，IPv6使用16字节的二进制格式
     *
     * @param string $ipBytes 二进制格式的IP地址
     *           - IPv4: 4字节二进制字符串
     *           - IPv6: 16字节二进制字符串
     * @return string 返回地理位置字符串，查询失败返回空字符串 ""
     * @throws \Exception 当IP版本无法确定或搜索引擎创建失败时抛出异常
     *
     * @example
     * ```php
     * $searcher = new Ip2Region();
     * $ipv4Bytes = inet_pton('61.142.118.231'); // 4字节二进制
     * $result = $searcher->searchByBytes($ipv4Bytes);
     * echo $result; // 输出：中国|广东省|中山市|电信|CN
     *
     * $ipv4Bytes = inet_pton('8.8.8.8'); // 4字节二进制
     * $result = $searcher->searchByBytes($ipv4Bytes);
     * echo $result; // 输出：United States|California|0|Google LLC|US
     * ```
     */
    public function searchByBytes(string $ipBytes): string
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
     *
     * 提供B树搜索接口，与 memorySearch 方法功能相同
     * 主要用于向后兼容，实际使用 memorySearch 方法
     *
     * @param string $ip 要查询的IP地址（支持IPv4和IPv6）
     * @return array 返回包含城市ID和地区信息的数组
     *  格式：['city_id' => int, 'region' => string]
     *
     * @example
     * ```php
     * $searcher = new Ip2Region();
     * $result = $searcher->btreeSearch('61.142.118.231');
     * echo $result['region']; // 输出：中国|广东省|中山市|电信|CN
     *
     * $result = $searcher->btreeSearch('8.8.8.8');
     * echo $result['region']; // 输出：United States|California|0|Google LLC|US
     * ```
     */
    public function btreeSearch(string $ip): array
    {
        return $this->memorySearch($ip);
    }

    /**
     * 获取IP协议版本（公共方法）
     *
     * 检测给定IP地址的协议版本，支持IPv4和IPv6
     * 这是 getIpVersion 方法的公共版本，不会抛出异常
     *
     * @param string $ip 要检测的IP地址
     * @return string 返回 'v4' 表示IPv4，'v6' 表示IPv6，'unknown' 表示无效IP
     *
     * @example
     * ```php
     * $searcher = new Ip2Region();
     * echo $searcher->getProtocolVersion('61.142.118.231'); // 输出：v4
     * echo $searcher->getProtocolVersion('2400:3200::1'); // 输出：v6
     * echo $searcher->getProtocolVersion('invalid-ip'); // 输出：unknown
     * ```
     */
    public function getProtocolVersion(string $ip): string
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
     *
     * 返回当前实例的IO操作统计信息
     * 包括IPv4和IPv6的IO次数以及总次数
     *
     * @return array 返回包含IO计数信息的数组
     *  格式：[
     *    'v4_io_count' => int,      // IPv4 IO操作次数
     *    'v6_io_count' => int,      // IPv6 IO操作次数
     *    'total_io_count' => int    // 总IO操作次数
     *  ]
     *
     * @example
     * ```php
     * $searcher = new Ip2Region();
     * $searcher->memorySearch('61.142.118.231'); // 触发IPv4查询
     * $ioCount = $searcher->getIOCount();
     * echo "总IO次数: " . $ioCount['total_io_count'] . "\n";
     * ```
     */
    public function getIOCount(): array
    {
        $stats = $this->getStats();
        return array(
            'v4_io_count'    => $stats['v4_io_count'],
            'v6_io_count'    => $stats['v6_io_count'],
            'total_io_count' => $stats['v4_io_count'] + $stats['v6_io_count']
        );
    }

    /**
     * 检查是否支持IPv6
     *
     * 检查当前实例是否支持IPv6查询
     * 当前版本始终返回 true，表示支持IPv6
     *
     * @return bool 返回 true 表示支持IPv6，false 表示不支持
     *
     * @example
     * ```php
     * $searcher = new Ip2Region();
     * if ($searcher->isIPv6Supported()) {
     *     echo "支持IPv6查询\n";
     * } else {
     *     echo "不支持IPv6查询\n";
     * }
     * ```
     */
    public function isIPv6Supported(): bool
    {
        return true;
    }

    /**
     * 检查是否支持IPv4
     *
     * 检查当前实例是否支持IPv4查询
     * 当前版本始终返回 true，表示支持IPv4
     *
     * @return bool 返回 true 表示支持IPv4，false 表示不支持
     *
     * @example
     * ```php
     * $searcher = new Ip2Region();
     * if ($searcher->isIPv4Supported()) {
     *     echo "支持IPv4查询\n";
     * } else {
     *     echo "不支持IPv4查询\n";
     * }
     * ```
     */
    public function isIPv4Supported(): bool
    {
        return true;
    }

    /**
     * 获取数据库信息
     *
     * 返回当前实例的数据库加载状态和配置信息
     * 包括IPv4/IPv6加载状态、缓存策略、自定义路径等
     *
     * @return array 返回包含数据库信息的数组
     *  格式：[
     *    'v4_loaded' => bool,        // IPv4搜索引擎是否已加载
     *    'v6_loaded' => bool,        // IPv6搜索引擎是否已加载
     *    'cache_policy' => string,   // 当前缓存策略
     *    'custom_v4_path' => string, // 自定义IPv4数据库路径
     *    'custom_v6_path' => string, // 自定义IPv6数据库路径
     *    'v4_version' => int,        // IPv4数据库版本（如果已加载）
     *    'v6_version' => int         // IPv6数据库版本（如果已加载）
     *  ]
     *
     * @example
     * ```php
     * $searcher = new Ip2Region();
     * $info = $searcher->getDatabaseInfo();
     * echo "IPv4已加载: " . ($info['v4_loaded'] ? '是' : '否') . "\n";
     * ```
     */
    public function getDatabaseInfo(): array
    {
        $info = array(
            'v4_loaded'      => $this->searcherV4 !== null,
            'v6_loaded'      => $this->searcherV6 !== null,
            'cache_policy'   => $this->cachePolicy,
            'custom_v4_path' => $this->dbPathV4,
            'custom_v6_path' => $this->dbPathV6
        );

        // 获取实际使用的数据库文件路径
        try {
            $info['v4_path'] = $this->getDbFile('v4');
        } catch (\Exception $e) {
            $info['v4_path'] = null;
        }

        try {
            $info['v6_path'] = $this->getDbFile('v6');
        } catch (\Exception $e) {
            $info['v6_path'] = null;
        }

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
     *
     * 返回自定义数据库文件的详细信息，包括文件路径、大小、修改时间等
     * 用于检查自定义数据库文件的状态
     *
     * @return array 返回包含自定义数据库文件信息的数组
     *  格式：[
     *    'v4_path' => string,        // IPv4自定义数据库路径
     *    'v6_path' => string,        // IPv6自定义数据库路径
     *    'v4_exists' => bool,        // IPv4文件是否存在
     *    'v6_exists' => bool,        // IPv6文件是否存在
     *    'v4_size' => int,           // IPv4文件大小（字节）
     *    'v6_size' => int,           // IPv6文件大小（字节）
     *    'v4_modified' => int,       // IPv4文件修改时间
     *    'v6_modified' => int        // IPv6文件修改时间
     *  ]
     *
     * @example
     * ```php
     * $searcher = new Ip2Region();
     * $info = $searcher->getCustomDbInfo();
     * if ($info['v4_exists']) {
     *     echo "IPv4文件大小: " . $info['v4_size'] . " 字节\n";
     * }
     * ```
     */
    public function getCustomDbInfo(): array
    {
        $info = array(
            'v4' => $this->getDbFileInfo($this->dbPathV4),
            'v6' => $this->getDbFileInfo($this->dbPathV6)
        );

        return $info;
    }

    /**
     * 设置自定义数据库路径
     *
     * 设置IPv4和IPv6的自定义数据库文件路径
     * 设置后会自动重置已加载的搜索引擎，强制重新加载
     *
     * @param string|null $v4Path IPv4数据库文件路径，为 null 时使用默认路径
     * @param string|null $v6Path IPv6数据库文件路径，为 null 时使用默认路径
     * @return void
     *
     * @example
     * ```php
     * $searcher = new Ip2Region();
     * // 设置自定义数据库路径
     * $searcher->setCustomDbPaths('/path/to/ipv4.xdb', '/path/to/ipv6.xdb');
     *
     * // 只设置IPv4路径
     * $searcher->setCustomDbPaths('/path/to/ipv4.xdb');
     *
     * // 重置为默认路径
     * $searcher->setCustomDbPaths();
     * ```
     */
    public function setCustomDbPaths(?string $v4Path = null, ?string $v6Path = null): void
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
     *
     * 检查当前实例是否正在使用自定义数据库文件
     * 包括IPv4和IPv6的自定义数据库使用状态
     *
     * @return array 返回包含使用状态的数组
     *  格式：[
     *    'v4' => bool,    // IPv4是否使用自定义数据库
     *    'v6' => bool     // IPv6是否使用自定义数据库
     *  ]
     *
     * @example
     * ```php
     * $searcher = new Ip2Region();
     * $searcher->setCustomDbPaths('/path/to/ipv4.xdb');
     * $status = $searcher->isUsingCustomDb();
     * echo "IPv4使用自定义数据库: " . ($status['v4'] ? '是' : '否') . "\n";
     * ```
     */
    public function isUsingCustomDb(): array
    {
        return array(
            'v4' => $this->dbPathV4 !== null && $this->isCustomDbExists($this->dbPathV4),
            'v6' => $this->dbPathV6 !== null && $this->isCustomDbExists($this->dbPathV6)
        );
    }


    /**
     * 获取数据库文件信息
     *
     * 获取指定数据库文件的详细信息，包括路径、大小、修改时间等
     * 用于检查数据库文件的状态和属性
     *
     * @param string|null $filePath 数据库文件路径
     * @return array|null 返回包含文件信息的数组，文件不存在时返回 null
     *      格式：[
     *        'path' => string,      // 文件路径
     *        'size' => int,        // 文件大小（字节）
     *        'modified' => int,    // 修改时间（Unix时间戳）
     *        'exists' => bool      // 文件是否存在
     *      ]
     *
     * @example
     * ```php
     * $searcher = new Ip2Region();
     * $info = $searcher->getDbFileInfo('/path/to/database.xdb');
     * if ($info) {
     *     echo "文件大小: " . $info['size'] . " 字节\n";
     *     echo "修改时间: " . date('Y-m-d H:i:s', $info['modified']) . "\n";
     * }
     * ```
     */
    private function getDbFileInfo(?string $filePath): ?array
    {
        if ($filePath === null || !file_exists($filePath)) {
            return null;
        }

        return array(
            'path'     => $filePath,
            'size'     => filesize($filePath),
            'modified' => filemtime($filePath),
            'exists'   => true
        );
    }

    /**
     * 检查自定义数据库是否存在
     *
     * 检查指定的自定义数据库文件是否存在且可读
     * 用于验证自定义数据库路径的有效性
     *
     * @param string|null $filePath 数据库文件路径
     * @return bool 返回 true 表示文件存在且可读，false 表示不存在或不可读
     *
     * @example
     * ```php
     * $searcher = new Ip2Region();
     * $exists = $searcher->isCustomDbExists('/path/to/database.xdb');
     * if ($exists) {
     *     echo "自定义数据库文件存在\n";
     * } else {
     *     echo "自定义数据库文件不存在或不可读\n";
     * }
     * ```
     */
    private function isCustomDbExists(?string $filePath): bool
    {
        return $filePath !== null && file_exists($filePath);
    }
}
