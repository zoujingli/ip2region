<?php

/**
 * IPv4 处理类
 *
 * 功能特性：
 * - IPv4 地址解析和验证
 * - IPv4 地址转换（字符串 <-> 长整型）
 * - IPv4 地址比较和排序
 * - 支持 IPv4 数据库查询
 *
 * @package ip2region\xdb
 * @author The Ip2Region Authors
 */

// Copyright 2022 The Ip2Region Authors. All rights reserved.
// Use of this source code is governed by a Apache2.0-style
// license that can be found in the LICENSE file.

namespace ip2region\xdb;

/**
 * IPv4 处理类
 *
 * 提供IPv4地址相关的处理功能，包括地址比较、转换等
 * 用于IPv4数据库查询和地址处理
 */
class IPv4
{
    /** @var int IP版本ID */
    public $id;

    /** @var string IP版本名称 */
    public $name;

    /** @var int IP地址字节长度 */
    public $bytes;

    /** @var int 段索引大小 */
    public $segmentIndexSize;

    /** @var IPv4|null 默认实例缓存 */
    private static $C = null;

    /**
     * 获取默认IPv4实例
     *
     * 返回预配置的IPv4实例，用于IPv4地址处理
     * 使用单例模式避免重复创建实例
     *
     * @return IPv4 返回默认的IPv4实例
     *
     * @example
     * ```php
     * $ipv4 = IPv4::default();
     * echo $ipv4->name; // 输出：IPv4
     * echo $ipv4->bytes; // 输出：4
     * ```
     */
    public static function default(): ?IPv4
    {
        if (self::$C == null) {
            // 14 = 4 + 4 + 2 + 4
            self::$C = new self(Util::IPv4VersionNo, 'IPv4', 4, 14);
        }
        return self::$C;
    }

    /**
     * 构造函数
     *
     * 初始化IPv4实例的各个属性
     *
     * @param int $id IP版本ID
     * @param string $name IP版本名称
     * @param int $bytes IP地址字节长度
     * @param int $segmentIndexSize 段索引大小
     *
     * @example
     * ```php
     * $ipv4 = new IPv4(4, 'IPv4', 4, 14);
     * ```
     */
    public function __construct(int $id, string $name, int $bytes, int $segmentIndexSize)
    {
        $this->id = $id;
        $this->name = $name;
        $this->bytes = $bytes;
        $this->segmentIndexSize = $segmentIndexSize;
    }

    /**
     * 比较两个IP字节与当前版本
     *
     * 将IP地址字节与缓冲区中指定偏移量的字节进行比较
     * 专门针对IPv4地址的字节比较，使用小端序编码
     *
     * @param string $ip1 要比较的IP地址字节
     * @param string $buff 字节缓冲区
     * @param int $offset 缓冲区中的起始偏移量
     * @return int 返回比较结果：-1表示ip1小于buff中的字节，0表示相等，1表示ip1大于buff中的字节
     *
     * @example
     * ```php
     * $ipv4 = IPv4::default();
     * $ip1 = inet_pton('61.142.118.231');
     * $buff = "some data" . $ip1 . "more data";
     * $result = $ipv4->ipSubCompare($ip1, $buff, 9);
     * ```
     */
    public function ipSubCompare(string $ip1, string $buff, int $offset): int
    {
        // ip1: Little endian byte order encoded long from searcher.
        // ip2: Little endian byte order read from xdb index.
        $len = strlen($ip1);
        $eIdx = $offset + $len;
        for ($i = 0, $j = $eIdx - 1; $i < $len; $i++, $j--) {
            $i1 = ord($ip1[$i]) & 0xFF;
            $i2 = ord($buff[$j]) & 0xFF;
            // printf("i:%d, j:%d, i1:%d, i2:%d\n", $i, $j, $i1, $i2);
            if ($i1 > $i2) {
                return 1;
            } else if ($i1 < $i2) {
                return -1;
            }
        }

        return 0;
    }

    /**
     * 转换为字符串表示
     *
     * 返回IPv4实例的字符串表示，用于调试和显示
     *
     * @return string 返回格式化的字符串表示
     *
     * @example
     * ```php
     * $ipv4 = IPv4::default();
     * echo $ipv4; // 输出：{id:4, name:IPv4, bytes:4, segmentIndexSize:14}
     * ```
     */
    public function __toString(): string
    {
        return sprintf(
            "{id:%d, name:%s, bytes:%d, segmentIndexSize:%d}",
            $this->id,
            $this->name,
            $this->bytes,
            $this->segmentIndexSize
        );
    }

    /**
     * 解析IP地址字符串
     *
     * 委托 Util::parseIP，接受 IPv4 或 IPv6；需要限制版本时先调用 isValid
     *
     * @param string $ipString 要解析的IP地址字符串
     * @return string|null 返回二进制字节格式的IP地址，解析失败返回 null
     *
     * @example
     * ```php
     * $bytes = IPv4::parse('61.142.118.231'); // 返回4字节二进制数据
     * $invalid = IPv4::parse('invalid-ip'); // 返回 null
     * ```
     */
    public static function parse(string $ipString): ?string
    {
        return Util::parseIP($ipString);
    }

    /**
     * 将IP地址字符串转换为长整型值
     *
     * 将IPv4地址字符串转换为32位长整型值
     * 使用小端序字节序进行转换
     * 与使用大端序的 toStr 不互为逆操作，保留旧接口行为
     *
     * @param string $ipString 要转换的IPv4地址字符串
     * @return int|null 返回转换后的长整型值，转换失败返回 null
     *
     * @example
     * ```php
     * $long = IPv4::toLong('1.2.3.4'); // 返回：67305985
     * $invalid = IPv4::toLong('invalid-ip'); // 返回 null
     * ```
     */
    public static function toLong(string $ipString): ?int
    {
        $ip = self::parse($ipString);
        if ($ip === null) {
            return null;
        }
        return Util::getLongBE($ip, 0);
    }

    /**
     * 将长整型值转换为IP地址字符串
     *
     * 将32位长整型值转换为IPv4地址字符串
     * 使用大端序字节序进行转换
     *
     * @param int $long 要转换的长整型值
     * @return string|false 返回转换后的IPv4地址字符串，转换失败返回 false
     *
     * @example
     * ```php
     * $ip = IPv4::toStr(16909060); // 返回："1.2.3.4"
     * ```
     */
    public static function toStr(int $long)
    {
        $ip = pack('N', $long);
        return inet_ntop($ip);
    }

    /**
     * 检查IP地址字符串是否有效
     *
     * 验证给定的字符串是否为有效的IPv4地址
     * 这是Util::isIPv4的便捷方法
     *
     * @param string $ipString 要检查的IP地址字符串
     * @return bool 返回 true 表示是有效的IPv4地址，false 表示不是
     *
     * @example
     * ```php
     * var_dump(IPv4::isValid('61.142.118.231')); // 输出：true
     * var_dump(IPv4::isValid('2400:3200::1')); // 输出：false
     * var_dump(IPv4::isValid('invalid-ip')); // 输出：false
     * ```
     */
    public static function isValid(string $ipString): bool
    {
        return Util::isIPv4($ipString);
    }
}
