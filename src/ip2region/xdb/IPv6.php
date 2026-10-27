<?php

/**
 * IPv6 处理类
 *
 * 功能特性：
 * - IPv6 地址解析和验证
 * - IPv6 地址转换（字符串 <-> 长整型）
 * - IPv6 地址比较和排序
 * - 支持 IPv6 数据库查询
 *
 * @package ip2region\xdb
 * @author The Ip2Region Authors
 */

// Copyright 2022 The Ip2Region Authors. All rights reserved.
// Use of this source code is governed by a Apache2.0-style
// license that can be found in the LICENSE file.

namespace ip2region\xdb;

/**
 * IPv6 处理类
 *
 * 提供IPv6地址相关的处理功能，包括地址比较、转换等
 * 用于IPv6数据库查询和地址处理
 */
class IPv6
{
    /** @var int IP版本ID */
    public $id;

    /** @var string IP版本名称 */
    public $name;

    /** @var int IP地址字节长度 */
    public $bytes;

    /** @var int 段索引大小 */
    public $segmentIndexSize;

    /** @var IPv6|null 默认实例缓存 */
    private static $C = null;

    /**
     * 获取默认IPv6实例
     *
     * 返回预配置的IPv6实例，用于IPv6地址处理
     * 使用单例模式避免重复创建实例
     *
     * @return IPv6 返回默认的IPv6实例
     *
     * @example
     * ```php
     * $ipv6 = IPv6::default();
     * echo $ipv6->name; // 输出：IPv6
     * echo $ipv6->bytes; // 输出：16
     * ```
     */
    public static function default(): ?IPv6
    {
        if (self::$C == null) {
            // 38 = 16 + 16 + 2 + 4
            self::$C = new self(Util::IPv6VersionNo, 'IPv6', 16, 38);
        }

        return self::$C;
    }

    /**
     * 构造函数
     *
     * 初始化IPv6实例的各个属性
     *
     * @param int $id IP版本ID
     * @param string $name IP版本名称
     * @param int $bytes IP地址字节长度
     * @param int $segmentIndexSize 段索引大小
     *
     * @example
     * ```php
     * $ipv6 = new IPv6(6, 'IPv6', 16, 38);
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
     * 比较IP地址字节与缓冲区中的字节
     *
     * 将IP地址字节与缓冲区中指定偏移量的字节进行比较
     * 专门针对IPv6地址的字节比较，使用Util::ipSubCompare方法
     *
     * @param string $ip 要比较的IP地址字节
     * @param string $buff 字节缓冲区
     * @param int $offset 缓冲区中的起始偏移量
     * @return int 返回比较结果：-1表示ip小于buff中的字节，0表示相等，1表示ip大于buff中的字节
     *
     * @example
     * ```php
     * $ipv6 = IPv6::default();
     * $ip = inet_pton('2400:3200::1');
     * $buff = "some data" . $ip . "more data";
     * $result = $ipv6->ipSubCompare($ip, $buff, 9);
     * ```
     */
    public function ipSubCompare(string $ip, string $buff, int $offset): int
    {
        // return Util::ipCompare($ip, substr($buff, $offset, strlen($ip)));
        return Util::ipSubCompare($ip, $buff, $offset);
    }

    /**
     * 转换为字符串表示
     *
     * 返回IPv6实例的字符串表示，用于调试和显示
     *
     * @return string 返回格式化的字符串表示
     *
     * @example
     * ```php
     * $ipv6 = IPv6::default();
     * echo $ipv6; // 输出：{id:6, name:IPv6, bytes:16, segmentIndexSize:38}
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
     * $bytes = IPv6::parse('2400:3200::1'); // 返回16字节二进制数据
     * $invalid = IPv6::parse('invalid-ip'); // 返回 null
     * ```
     */
    public static function parse(string $ipString): ?string
    {
        return Util::parseIP($ipString);
    }

    /**
     * 将IP地址字符串转换为长整型值
     *
     * 兼容旧接口：仅取地址前4字节并按小端序组合整数
     * 不能表示完整IPv6；完整转换请使用 parse 和 Util::ipToString
     *
     * @param string $ipString 要转换的IPv6地址字符串
     * @return int|null 返回前4字节的整型值，解析失败返回 null
     *
     * @example
     * ```php
     * $long = IPv6::toLong('2400:3200::1'); // 仅包含前4字节，不含末尾的1
     * $invalid = IPv6::toLong('invalid-ip'); // 返回 null
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
     * 兼容旧接口：把PHP原生整数展开为16字节地址
     * 受原生整数位宽限制，不能还原任意IPv6，也不与 toLong 互为逆操作
     *
     * @param int $long 要转换的长整型值
     * @return string|false 返回转换后的IPv6地址字符串，转换失败返回 false
     *
     * @example
     * ```php
     * $ip = IPv6::toStr(1); // 返回："::1"
     * ```
     */
    public static function toStr(int $long)
    {
        $ip = '';
        for ($i = 0; $i < 16; $i++) {
            $ip .= chr(($long >> (120 - $i * 8)) & 0xFF);
        }
        return inet_ntop($ip);
    }

    /**
     * 检查IP地址字符串是否有效
     *
     * 验证给定的字符串是否为有效的IPv6地址
     * 这是Util::isIPv6的便捷方法
     *
     * @param string $ipString 要检查的IP地址字符串
     * @return bool 返回 true 表示是有效的IPv6地址，false 表示不是
     *
     * @example
     * ```php
     * var_dump(IPv6::isValid('2400:3200::1')); // 输出：true
     * var_dump(IPv6::isValid('61.142.118.231')); // 输出：false
     * var_dump(IPv6::isValid('invalid-ip')); // 输出：false
     * ```
     */
    public static function isValid(string $ipString): bool
    {
        return Util::isIPv6($ipString);
    }
}
