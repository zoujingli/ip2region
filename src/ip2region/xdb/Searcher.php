<?php

/**
 * XDB 搜索引擎
 *
 * 功能特性：
 * - 高性能XDB格式数据库搜索引擎
 * - 支持IPv4和IPv6地址查询
 * - 多种搜索算法（二分查找、向量索引）
 * - 内存映射文件支持，减少内存占用
 * - 支持大文件分片加载
 * - 智能缓存机制
 *
 * 技术特点：
 * - 基于XDB格式的高效数据结构
 * - 支持多种索引类型（VectorIndex、Content）
 * - 优化的二分查找算法
 * - 内存友好的文件读取策略
 *
 * @package ip2region\xdb
 * @author The Ip2Region Authors
 * @version 3.0.3
 */

// Copyright 2022 The Ip2Region Authors. All rights reserved.
// Use of this source code is governed by a Apache2.0-style
// license that can be found in the LICENSE file.

namespace ip2region\xdb;

/**
 * XDB 搜索引擎
 *
 * 提供高性能的XDB格式数据库搜索功能
 * 支持IPv4和IPv6地址查询，使用优化的二分查找算法
 */
class Searcher
{
    /** @var IPv4|IPv6 IP版本对象 */
    private $version;

    /** @var resource|null XDB文件句柄 */
    private $handle = null;

    /** @var int IO操作计数器 */
    private $ioCount = 0;

    /** @var string|null 向量索引二进制字符串，使用字符串解码比基于数组的Map更快 */
    private $vectorIndex = null;

    /** @var string|null XDB内容缓冲区 */
    private $contentBuff = null;

    // ============================================================================
    // 静态工厂方法
    // ============================================================================

    /**
     * 创建基于文件的搜索引擎
     *
     * 创建一个基于XDB文件的搜索引擎实例
     * 使用文件句柄进行数据读取，适合大文件处理
     * 内部会将 int 版本参数转换为对应的版本对象
     *
     * @param int $version IP版本（4或6）
     * @param string $dbFile XDB数据库文件路径
     * @return Searcher 返回搜索引擎实例
     * @throws \Exception 当文件不存在或无法打开时抛出异常
     *
     * @example
     * ```php
     * $searcher = Searcher::newWithFileOnly(4, '/path/to/ipv4.xdb');
     * $result = $searcher->search('61.142.118.231');
     * ```
     */
    public static function newWithFileOnly(int $version, string $dbFile): self
    {
        $self = $version === 4 ? IPv4::default() : IPv6::default();
        return new self($self, $dbFile, null, null);
    }

    /**
     * 创建基于向量索引的搜索引擎
     *
     * 创建一个基于XDB文件和向量索引的搜索引擎实例
     * 使用预加载的向量索引提高搜索性能
     * 内部会将 int 版本参数转换为对应的版本对象
     *
     * @param int $version IP版本（4或6）
     * @param string $dbFile XDB数据库文件路径
     * @param string $vIndex 向量索引数据
     * @return Searcher 返回搜索引擎实例
     * @throws \Exception 当文件不存在或无法打开时抛出异常
     *
     * @example
     * ```php
     * $searcher = Searcher::newWithVectorIndex(4, '/path/to/ipv4.xdb', $vectorIndex);
     * $result = $searcher->search('61.142.118.231');
     * ```
     */
    public static function newWithVectorIndex(int $version, string $dbFile, string $vIndex): self
    {
        $versionObj = $version === 4 ? IPv4::default() : IPv6::default();
        return new self($versionObj, $dbFile, $vIndex, null);
    }

    /**
     * 创建基于缓冲区的搜索引擎
     *
     * 创建一个基于内存缓冲区的搜索引擎实例
     * 使用预加载的内容缓冲区，适合小文件或内存充足的环境
     * 内部会将 int 版本参数转换为对应的版本对象
     *
     * @param int $version IP版本（4或6）
     * @param string $cBuff 内容缓冲区数据
     * @return Searcher 返回搜索引擎实例
     *
     * @throws \Exception
     * @example
     * ```php
     * $searcher = Searcher::newWithBuffer(4, $contentBuffer);
     * $result = $searcher->search('61.142.118.231');
     * ```
     */
    public static function newWithBuffer(int $version, string $cBuff): self
    {
        $versionObj = $version === 4 ? IPv4::default() : IPv6::default();
        return new self($versionObj, null, null, $cBuff);
    }

    // ============================================================================
    // 构造函数和初始化
    // ============================================================================

    /**
     * 初始化XDB搜索引擎
     *
     * 根据提供的参数初始化搜索引擎实例
     * 支持文件句柄、向量索引和内容缓冲区三种模式
     *
     * @param IPv4|IPv6 $version IP版本对象（IPv4或IPv6）
     * @param string|null $dbFile XDB数据库文件路径
     * @param string|null $vectorIndex 向量索引数据
     * @param string|null $cBuff 内容缓冲区数据
     * @throws \Exception 当文件无法打开时抛出异常
     *
     * @example
     * ```php
     * $searcher = new Searcher(IPv4::default(), '/path/to/ipv4.xdb');
     * ```
     */
    function __construct($version, ?string $dbFile = null, ?string $vectorIndex = null, ?string $cBuff = null)
    {
        $this->version = $version;

        // check the content buffer first
        if ($cBuff != null) {
            $this->vectorIndex = null;
            $this->contentBuff = $cBuff;
        } else {
            // open the xdb binary file
            $this->handle = fopen($dbFile, "r");
            if ($this->handle === false) {
                throw new \Exception(sprintf("failed to open xdb file `%s`", $dbFile));
            }

            $this->vectorIndex = $vectorIndex;
        }
    }

    /**
     * 查找指定IP地址的地区信息
     *
     * 在XDB数据库中搜索指定IP地址对应的地区信息
     * 使用优化的二分查找算法进行快速定位
     *
     * @Note 重要提示：IP地址必须是人类可读的IP地址字符串
     * 不要使用 parseIP 返回的打包二进制字符串
     *
     * @param string $ip IP地址字符串（人类可读格式，如 "61.142.118.231"）
     * @return string 返回地区信息字符串，未找到返回空字符串
     * @throws \Exception 当IP地址无效或搜索过程中发生错误时抛出异常
     *
     * @example
     * ```php
     * $searcher = Searcher::newWithFileOnly(4, '/path/to/ipv4.xdb');
     * $region = $searcher->search('61.142.118.231');
     * echo $region; // 输出：中国|广东省|中山市|电信
     * ```
     */
    public function search(string $ip): string
    {
        $ipBytes = Util::parseIP($ip);
        if ($ipBytes == null) {
            throw new \Exception("invalid ip address `{$ip}`");
        }

        return $this->searchByBytes($ipBytes);
    }

    /**
     * 根据二进制IP字节查找地区信息
     *
     * 使用 parseIP 返回的二进制格式IP地址进行搜索
     * 这是 search 方法的底层实现，避免重复解析，提供更高的性能
     *
     * @param string $ipBytes 二进制格式的IP地址字节（由 parseIP 或 inet_pton 返回）
     * @return string 返回地区信息字符串，未找到返回空字符串
     * @throws \Exception 当IP版本不匹配或搜索过程中发生错误时抛出异常
     *
     * @example
     * ```php
     * $searcher = Searcher::newWithFileOnly(4, '/path/to/ipv4.xdb');
     * $ipBytes = Util::parseIP('61.142.118.231');
     * $region = $searcher->searchByBytes($ipBytes);
     * echo $region; // 输出：中国|广东省|中山市|电信
     * ```
     */
    public function searchByBytes(string $ipBytes): string
    {
        // ip version check
        if (strlen($ipBytes) != $this->version->bytes) {
            throw new \Exception("invalid ip address ({$this->version->name} expected)");
        }

        // reset the global counter
        $this->ioCount = 0;

        // locate the segment index block based on the vector index
        $il0 = ord($ipBytes[0]) & 0xFF;
        $il1 = ord($ipBytes[1]) & 0xFF;
        $idx = $il0 * Util::VectorIndexCols * Util::VectorIndexSize + $il1 * Util::VectorIndexSize;
        if ($this->vectorIndex != null) {
            $sPtr = Util::le_getUint32($this->vectorIndex, $idx);
            $ePtr = Util::le_getUint32($this->vectorIndex, $idx + 4);
        } else if ($this->contentBuff != null) {
            $sPtr = Util::le_getUint32($this->contentBuff, Util::HeaderInfoLength + $idx);
            $ePtr = Util::le_getUint32($this->contentBuff, Util::HeaderInfoLength + $idx + 4);
        } else {
            // read the vector index block
            $buff = $this->read(Util::HeaderInfoLength + $idx, 8);
            $sPtr = Util::le_getUint32($buff, 0);
            $ePtr = Util::le_getUint32($buff, 4);
        }

        // printf("sPtr: %d, ePtr: %d\n", $sPtr, $ePtr);
        // @Note: ptr validate, zero ptr means source data missing (same as upstream binding/php/xdb)
        if ($sPtr == 0 || $ePtr == 0) {
            return "";
        }

        list($bytes, $dBytes) = [strlen($ipBytes), strlen($ipBytes) << 1];

        // binary search the segment index to get the region info
        $idxSize = $this->version->segmentIndexSize;
        $indexRange = $ePtr - $sPtr;
        if ($indexRange < 0 || $indexRange % $idxSize !== 0) {
            throw new \UnexpectedValueException(
                sprintf('invalid segment index range: start=%d end=%d size=%d', $sPtr, $ePtr, $idxSize)
            );
        }
        list($dataLen, $dataPtr, $l, $h) = [0, 0, 0, intdiv($indexRange, $idxSize)];
        while ($l <= $h) {
            $m = ($l + $h) >> 1;
            $p = $sPtr + $m * $idxSize;

            // read the segment index
            $buff = $this->read($p, $idxSize);

            // compare the segment index
            if ($this->version->ipSubCompare($ipBytes, $buff, 0) < 0) {
                $h = $m - 1;
            } else if ($this->version->ipSubCompare($ipBytes, $buff, $bytes) > 0) {
                $l = $m + 1;
            } else {
                $dataLen = Util::le_getUint16($buff, $dBytes);
                $dataPtr = Util::le_getUint32($buff, $dBytes + 2);
                break;
            }
        }

        // empty match interception.
        // printf("dataLen: %d, dataPtr: %d\n", $dataLen, $dataPtr);
        if ($dataLen == 0) {
            return "";
        }

        // load and return the region data
        return $this->read($dataPtr, $dataLen);
    }

    // ============================================================================
    // 辅助方法
    // ============================================================================

    /**
     * 从指定索引读取指定字节数
     *
     * 根据当前模式（文件句柄或内容缓冲区）读取数据
     * 优先检查内存缓冲区，提高读取性能
     *
     * @param int $offset 起始偏移量（索引位置）
     * @param int $len 要读取的字节长度
     * @return string 返回读取的数据
     * @throws \Exception 当读取失败时抛出异常
     *
     * @example
     * ```php
     * $data = $this->read(1024, 256); // 从偏移量1024读取256字节
     * ```
     */
    private function read(int $offset, int $len): string
    {
        // check the in-memory buffer first
        if ($this->contentBuff != null) {
            return substr($this->contentBuff, $offset, $len);
        }

        // read from the file
        $r = fseek($this->handle, $offset);
        if ($r == -1) {
            throw new \Exception("failed to fseek to offset {$offset}");
        }

        $this->ioCount++;
        $buff = fread($this->handle, $len);
        if ($buff === false) {
            throw new \Exception("failed to fread {$len} bytes at offset {$offset}");
        }

        if (strlen($buff) != $len) {
            throw new \Exception(
                sprintf('incomplete read at offset %d: expected %d bytes, got %d', $offset, $len, strlen($buff))
            );
        }

        return $buff;
    }

    /**
     * 获取IO操作计数
     *
     * 返回当前搜索引擎实例的IO操作总次数
     * 用于性能监控和调试
     *
     * @return int 返回IO操作次数
     *
     * @example
     * ```php
     * $searcher = Searcher::newWithFileOnly(4, '/path/to/ipv4.xdb');
     * $searcher->search('61.142.118.231');
     * echo "IO次数: " . $searcher->getIOCount();
     * ```
     */
    public function getIOCount(): int
    {
        return $this->ioCount;
    }

    /**
     * 获取IP版本对象
     *
     * 返回当前搜索引擎实例的IP版本对象
     *
     * @return IPv4|IPv6 返回IP版本对象（IPv4或IPv6）
     *
     * @example
     * ```php
     * $searcher = Searcher::newWithFileOnly(4, '/path/to/ipv4.xdb');
     * $version = $searcher->getIPVersion();
     * echo "IP版本: " . $version->id; // 输出：4
     * ```
     */
    public function getIPVersion()
    {
        return $this->version;
    }


    /**
     * 关闭搜索引擎
     *
     * 关闭文件句柄并释放相关资源
     * 在对象销毁前调用以确保资源正确释放
     *
     * @return void
     *
     * @example
     * ```php
     * $searcher = Searcher::newWithFileOnly(4, '/path/to/ipv4.xdb');
     * $searcher->search('61.142.118.231');
     * $searcher->close(); // 关闭文件句柄
     * ```
     */
    public function close(): void
    {
        if ($this->handle != null) {
            fclose($this->handle);
        }
    }
}
