<?php

/**
 * XDB 工具类
 *
 * 提供各种字节操作和字符串转换功能，包括：
 * - IP地址解析和验证
 * - 字节序转换（大端序/小端序）
 * - IP地址比较和排序
 * - 数值转换和格式化
 *
 * @package ip2region\xdb
 * @author The Ip2Region Authors
 * @version 3.0
 */

// Copyright 2022 The Ip2Region Authors. All rights reserved.
// Use of this source code is governed by a Apache2.0-style
// license that can be found in the LICENSE file.

namespace ip2region\xdb;

/**
 * XDB 工具类
 *
 * 提供各种字节操作和字符串转换功能，包括：
 * - IP地址解析和验证
 * - 字节序转换（大端序/小端序）
 * - IP地址比较和排序
 * - 数值转换和格式化
 */
class Util
{
    // ============================================================================
    // 常量定义
    // ============================================================================

    /** @var int XDB 2.0 结构版本 */
    const Structure_20 = 2;

    /** @var int XDB 3.0 结构版本 */
    const Structure_30 = 3;

    /** @var int IPv4 版本号 */
    const IPv4VersionNo = 4;

    /** @var int IPv6 版本号 */
    const IPv6VersionNo = 6;

    /** @var int 头部信息长度（字节） */
    const HeaderInfoLength = 256;

    /** @var int 向量索引行数 */
    const VectorIndexRows = 256;

    /** @var int 向量索引列数 */
    const VectorIndexCols = 256;

    /** @var int 向量索引大小（字节） */
    const VectorIndexSize = 8;

    // ============================================================================
    // IP地址解析和验证方法
    // ============================================================================

    /**
     * 解析指定的IP地址并返回其字节格式
     *
     * 将字符串格式的IP地址转换为二进制字节格式（打包字节）
     * 支持IPv4和IPv6地址的解析
     *
     * @param string $ipString 要解析的IP地址字符串
     * @return string|null 成功返回打包的二进制字节，失败返回 null
     *
     * @example
     * ```php
     * $ipv4Bytes = Util::parseIP('61.142.118.231'); // 返回4字节二进制数据
     * $ipv6Bytes = Util::parseIP('2400:3200::1'); // 返回16字节二进制数据
     * $invalid = Util::parseIP('invalid-ip'); // 返回 null
     * ```
     */
    public static function parseIP($ipString)
    {
        $flag = FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6;
        if (!filter_var($ipString, FILTER_VALIDATE_IP, $flag)) {
            return null;
        }

        $ip = inet_pton($ipString);
        if ($ip === false) {
            return null;
        }

        return $ip;
    }

    /**
     * 检查IP地址是否为IPv4格式
     *
     * 验证给定的字符串是否为有效的IPv4地址
     *
     * @param string $ipString 要检查的IP地址字符串
     * @return bool 返回 true 表示是有效的IPv4地址，false 表示不是
     *
     * @example
     * ```php
     * var_dump(Util::isIPv4('61.142.118.231')); // 输出：true
     * var_dump(Util::isIPv4('2400:3200::1')); // 输出：false
     * var_dump(Util::isIPv4('invalid-ip')); // 输出：false
     * ```
     */
    public static function isIPv4($ipString)
    {
        return filter_var($ipString, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }

    /**
     * 检查IP地址是否为IPv6格式
     *
     * 验证给定的字符串是否为有效的IPv6地址
     *
     * @param string $ipString 要检查的IP地址字符串
     * @return bool 返回 true 表示是有效的IPv6地址，false 表示不是
     *
     * @example
     * ```php
     * var_dump(Util::isIPv6('2400:3200::1')); // 输出：true
     * var_dump(Util::isIPv6('61.142.118.231')); // 输出：false
     * var_dump(Util::isIPv6('invalid-ip')); // 输出：false
     * ```
     */
    public static function isIPv6($ipString)
    {
        return filter_var($ipString, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    /**
     * 获取IP地址版本号
     *
     * 根据IP地址字符串返回对应的版本号
     *
     * @param string $ipString 要检查的IP地址字符串
     * @return int 返回版本号：4表示IPv4，6表示IPv6，0表示无效IP
     *
     * @example
     * ```php
     * echo Util::getIPVersion('61.142.118.231'); // 输出：4
     * echo Util::getIPVersion('2400:3200::1'); // 输出：6
     * echo Util::getIPVersion('invalid-ip'); // 输出：0
     * ```
     */
    public static function getIPVersion($ipString)
    {
        if (self::isIPv4($ipString)) {
            return self::IPv4VersionNo;
        } else if (self::isIPv6($ipString)) {
            return self::IPv6VersionNo;
        }
        return 0;
    }

    // ============================================================================
    // 字节序转换方法
    // ============================================================================

    /**
     * 将字节数组转换为长整型值（大端序）
     *
     * 从指定偏移量开始读取4个字节，按大端序转换为32位长整型
     *
     * @param string $bytes 字节数组
     * @param int $offset 起始偏移量
     * @return int 返回转换后的长整型值
     *
     * @example
     * ```php
     * $bytes = "\x00\x00\x00\x01"; // 大端序的1
     * $value = Util::getLong($bytes, 0); // 返回：1
     * ```
     */
    public static function getLong($bytes, $offset)
    {
        $val = (ord($bytes[$offset]) & 0xFF);
        $val |= ((ord($bytes[$offset + 1]) << 8) & 0xFF00);
        $val |= ((ord($bytes[$offset + 2]) << 16) & 0xFF0000);
        $val |= ((ord($bytes[$offset + 3]) << 24) & 0xFF000000);
        return $val;
    }

    /**
     * 将字节数组转换为整型值（大端序）
     *
     * 从指定偏移量开始读取2个字节，按大端序转换为16位整型
     *
     * @param string $bytes 字节数组
     * @param int $offset 起始偏移量
     * @return int 返回转换后的整型值
     *
     * @example
     * ```php
     * $bytes = "\x00\x01"; // 大端序的1
     * $value = Util::getInt($bytes, 0); // 返回：1
     * ```
     */
    public static function getInt($bytes, $offset)
    {
        $val = (ord($bytes[$offset]) & 0xFF);
        $val |= ((ord($bytes[$offset + 1]) << 8) & 0xFF00);
        return $val;
    }

    /**
     * 将字节数组转换为整型值（小端序）
     *
     * 从指定偏移量开始读取2个字节，按小端序转换为16位整型
     *
     * @param string $bytes 字节数组
     * @param int $offset 起始偏移量
     * @return int 返回转换后的整型值
     *
     * @example
     * ```php
     * $bytes = "\x01\x00"; // 小端序的1
     * $value = Util::getIntLE($bytes, 0); // 返回：1
     * ```
     */
    public static function getIntLE($bytes, $offset)
    {
        $val = (ord($bytes[$offset + 1]) & 0xFF);
        $val |= ((ord($bytes[$offset]) << 8) & 0xFF00);
        return $val;
    }

    /**
     * 将字节数组转换为长整型值（小端序）
     *
     * 从指定偏移量开始读取4个字节，按小端序转换为32位长整型
     *
     * @param string $bytes 字节数组
     * @param int $offset 起始偏移量
     * @return int 返回转换后的长整型值
     *
     * @example
     * ```php
     * $bytes = "\x01\x00\x00\x00"; // 小端序的1
     * $value = Util::getLongLE($bytes, 0); // 返回：1
     * ```
     */
    public static function getLongLE($bytes, $offset)
    {
        $val = (ord($bytes[$offset + 3]) & 0xFF);
        $val |= ((ord($bytes[$offset + 2]) << 8) & 0xFF00);
        $val |= ((ord($bytes[$offset + 1]) << 16) & 0xFF0000);
        $val |= ((ord($bytes[$offset]) << 24) & 0xFF000000);
        return $val;
    }

    /**
     * 将字节数组转换为整型值（大端序）
     *
     * 从指定偏移量开始读取2个字节，按大端序转换为16位整型
     * 与 getInt 方法功能相同，提供更明确的命名
     *
     * @param string $bytes 字节数组
     * @param int $offset 起始偏移量
     * @return int 返回转换后的整型值
     *
     * @example
     * ```php
     * $bytes = "\x00\x01"; // 大端序的1
     * $value = Util::getIntBE($bytes, 0); // 返回：1
     * ```
     */
    public static function getIntBE($bytes, $offset)
    {
        $val = (ord($bytes[$offset]) & 0xFF);
        $val |= ((ord($bytes[$offset + 1]) << 8) & 0xFF00);
        return $val;
    }

    /**
     * 将字节数组转换为长整型值（大端序）
     *
     * 从指定偏移量开始读取4个字节，按大端序转换为32位长整型
     * 与 getLong 方法功能相同，提供更明确的命名
     *
     * @param string $bytes 字节数组
     * @param int $offset 起始偏移量
     * @return int 返回转换后的长整型值
     *
     * @example
     * ```php
     * $bytes = "\x00\x00\x00\x01"; // 大端序的1
     * $value = Util::getLongBE($bytes, 0); // 返回：1
     * ```
     */
    public static function getLongBE($bytes, $offset)
    {
        $val = (ord($bytes[$offset]) & 0xFF);
        $val |= ((ord($bytes[$offset + 1]) << 8) & 0xFF00);
        $val |= ((ord($bytes[$offset + 2]) << 16) & 0xFF0000);
        $val |= ((ord($bytes[$offset + 3]) << 24) & 0xFF000000);
        return $val;
    }

    // ============================================================================
    // 字符串转换方法
    // ============================================================================

    /**
     * 将字节数组转换为字符串
     *
     * 从指定偏移量开始提取指定长度的字节并转换为字符串
     *
     * @param string $bytes 字节数组
     * @param int $offset 起始偏移量
     * @param int $length 要提取的字节长度
     * @return string 返回转换后的字符串
     *
     * @example
     * ```php
     * $bytes = "Hello World";
     * $str = Util::getString($bytes, 0, 5); // 返回："Hello"
     * ```
     */
    public static function getString($bytes, $offset, $length)
    {
        return substr($bytes, $offset, $length);
    }

    /**
     * 将字节数组转换为字符串（空字符终止）
     *
     * 从指定偏移量开始提取指定长度的字节并转换为字符串
     * 自动截断到第一个空字符（\0）处
     *
     * @param string $bytes 字节数组
     * @param int $offset 起始偏移量
     * @param int $length 要提取的最大字节长度
     * @return string 返回转换后的字符串（已截断到空字符）
     *
     * @example
     * ```php
     * $bytes = "Hello\0World";
     * $str = Util::getStringNT($bytes, 0, 10); // 返回："Hello"
     * ```
     */
    public static function getStringNT($bytes, $offset, $length)
    {
        $str = substr($bytes, $offset, $length);
        $pos = strpos($str, "\0");
        if ($pos !== false) {
            $str = substr($str, 0, $pos);
        }
        return $str;
    }

    /**
     * 将字节数组转换为UTF-8字符串
     *
     * 从指定偏移量开始提取指定长度的字节并转换为UTF-8字符串
     * 自动截断到第一个空字符（\0）处
     *
     * @param string $bytes 字节数组
     * @param int $offset 起始偏移量
     * @param int $length 要提取的最大字节长度
     * @return string 返回转换后的UTF-8字符串
     *
     * @example
     * ```php
     * $bytes = "Hello\0World";
     * $str = Util::getStringUTF8($bytes, 0, 10); // 返回："Hello"
     * ```
     */
    public static function getStringUTF8($bytes, $offset, $length)
    {
        $str = substr($bytes, $offset, $length);
        $pos = strpos($str, "\0");
        if ($pos !== false) {
            $str = substr($str, 0, $pos);
        }
        return mb_convert_encoding($str, 'UTF-8', 'UTF-8');
    }

    /**
     * 将字节数组转换为GBK字符串
     *
     * 从指定偏移量开始提取指定长度的字节并转换为GBK字符串
     * 自动截断到第一个空字符（\0）处，并转换为UTF-8编码
     *
     * @param string $bytes 字节数组
     * @param int $offset 起始偏移量
     * @param int $length 要提取的最大字节长度
     * @return string 返回转换后的UTF-8字符串（从GBK转换）
     *
     * @example
     * ```php
     * $bytes = "你好\0世界";
     * $str = Util::getStringGBK($bytes, 0, 10); // 返回："你好"
     * ```
     */
    public static function getStringGBK($bytes, $offset, $length)
    {
        $str = substr($bytes, $offset, $length);
        $pos = strpos($str, "\0");
        if ($pos !== false) {
            $str = substr($str, 0, $pos);
        }
        return mb_convert_encoding($str, 'UTF-8', 'GBK');
    }

    /**
     * 将字节数组转换为GB2312字符串
     *
     * 从指定偏移量开始提取指定长度的字节并转换为GB2312字符串
     * 自动截断到第一个空字符（\0）处，并转换为UTF-8编码
     *
     * @param string $bytes 字节数组
     * @param int $offset 起始偏移量
     * @param int $length 要提取的最大字节长度
     * @return string 返回转换后的UTF-8字符串（从GB2312转换）
     *
     * @example
     * ```php
     * $bytes = "你好\0世界";
     * $str = Util::getStringGB2312($bytes, 0, 10); // 返回："你好"
     * ```
     */
    public static function getStringGB2312($bytes, $offset, $length)
    {
        $str = substr($bytes, $offset, $length);
        $pos = strpos($str, "\0");
        if ($pos !== false) {
            $str = substr($str, 0, $pos);
        }
        return mb_convert_encoding($str, 'UTF-8', 'GB2312');
    }

    /**
     * 将字节数组转换为Big5字符串
     *
     * 从指定偏移量开始提取指定长度的字节并转换为Big5字符串
     * 自动截断到第一个空字符（\0）处，并转换为UTF-8编码
     *
     * @param string $bytes 字节数组
     * @param int $offset 起始偏移量
     * @param int $length 要提取的最大字节长度
     * @return string 返回转换后的UTF-8字符串（从Big5转换）
     *
     * @example
     * ```php
     * $bytes = "你好\0世界";
     * $str = Util::getStringBig5($bytes, 0, 10); // 返回："你好"
     * ```
     */
    public static function getStringBig5($bytes, $offset, $length)
    {
        $str = substr($bytes, $offset, $length);
        $pos = strpos($str, "\0");
        if ($pos !== false) {
            $str = substr($str, 0, $pos);
        }
        return mb_convert_encoding($str, 'UTF-8', 'Big5');
    }

    /**
     * 将字节数组转换为ISO-8859-1字符串
     *
     * 从指定偏移量开始提取指定长度的字节并转换为ISO-8859-1字符串
     * 自动截断到第一个空字符（\0）处，并转换为UTF-8编码
     *
     * @param string $bytes 字节数组
     * @param int $offset 起始偏移量
     * @param int $length 要提取的最大字节长度
     * @return string 返回转换后的UTF-8字符串（从ISO-8859-1转换）
     *
     * @example
     * ```php
     * $bytes = "Hello\0World";
     * $str = Util::getStringISO8859_1($bytes, 0, 10); // 返回："Hello"
     * ```
     */
    public static function getStringISO8859_1($bytes, $offset, $length)
    {
        $str = substr($bytes, $offset, $length);
        $pos = strpos($str, "\0");
        if ($pos !== false) {
            $str = substr($str, 0, $pos);
        }
        return mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
    }

    /**
     * 将字节数组转换为Windows-1252字符串
     *
     * 从指定偏移量开始提取指定长度的字节并转换为Windows-1252字符串
     * 自动截断到第一个空字符（\0）处，并转换为UTF-8编码
     *
     * @param string $bytes 字节数组
     * @param int $offset 起始偏移量
     * @param int $length 要提取的最大字节长度
     * @return string 返回转换后的UTF-8字符串（从Windows-1252转换）
     *
     * @example
     * ```php
     * $bytes = "Hello\0World";
     * $str = Util::getStringWindows1252($bytes, 0, 10); // 返回："Hello"
     * ```
     */
    public static function getStringWindows1252($bytes, $offset, $length)
    {
        $str = substr($bytes, $offset, $length);
        $pos = strpos($str, "\0");
        if ($pos !== false) {
            $str = substr($str, 0, $pos);
        }
        return mb_convert_encoding($str, 'UTF-8', 'Windows-1252');
    }

    /**
     * 将字节数组转换为ASCII字符串
     *
     * 从指定偏移量开始提取指定长度的字节并转换为ASCII字符串
     * 自动截断到第一个空字符（\0）处
     *
     * @param string $bytes 字节数组
     * @param int $offset 起始偏移量
     * @param int $length 要提取的最大字节长度
     * @return string 返回转换后的ASCII字符串
     *
     * @example
     * ```php
     * $bytes = "Hello\0World";
     * $str = Util::getStringASCII($bytes, 0, 10); // 返回："Hello"
     * ```
     */
    public static function getStringASCII($bytes, $offset, $length)
    {
        $str = substr($bytes, $offset, $length);
        $pos = strpos($str, "\0");
        if ($pos !== false) {
            $str = substr($str, 0, $pos);
        }
        return $str;
    }

    /**
     * 将字节数组转换为Latin1字符串
     *
     * 从指定偏移量开始提取指定长度的字节并转换为Latin1字符串
     * 自动截断到第一个空字符（\0）处，并转换为UTF-8编码
     *
     * @param string $bytes 字节数组
     * @param int $offset 起始偏移量
     * @param int $length 要提取的最大字节长度
     * @return string 返回转换后的UTF-8字符串（从Latin1转换）
     *
     * @example
     * ```php
     * $bytes = "Hello\0World";
     * $str = Util::getStringLatin1($bytes, 0, 10); // 返回："Hello"
     * ```
     */
    public static function getStringLatin1($bytes, $offset, $length)
    {
        $str = substr($bytes, $offset, $length);
        $pos = strpos($str, "\0");
        if ($pos !== false) {
            $str = substr($str, 0, $pos);
        }
        return $str;
    }

    /**
     * 将字节数组转换为UTF-16字符串
     *
     * 从指定偏移量开始提取指定长度的字节并转换为UTF-16字符串
     * 自动截断到第一个双空字符（\0\0）处，并转换为UTF-8编码
     *
     * @param string $bytes 字节数组
     * @param int $offset 起始偏移量
     * @param int $length 要提取的最大字节长度
     * @return string 返回转换后的UTF-8字符串（从UTF-16转换）
     *
     * @example
     * ```php
     * $bytes = "Hello\0\0World";
     * $str = Util::getStringUTF16($bytes, 0, 10); // 返回："Hello"
     * ```
     */
    public static function getStringUTF16($bytes, $offset, $length)
    {
        $str = substr($bytes, $offset, $length);
        $pos = strpos($str, "\0\0");
        if ($pos !== false) {
            $str = substr($str, 0, $pos);
        }
        return mb_convert_encoding($str, 'UTF-8', 'UTF-16');
    }

    /**
     * 将字节数组转换为UTF-16LE字符串
     *
     * 从指定偏移量开始提取指定长度的字节并转换为UTF-16LE字符串
     * 自动截断到第一个双空字符（\0\0）处，并转换为UTF-8编码
     *
     * @param string $bytes 字节数组
     * @param int $offset 起始偏移量
     * @param int $length 要提取的最大字节长度
     * @return string 返回转换后的UTF-8字符串（从UTF-16LE转换）
     *
     * @example
     * ```php
     * $bytes = "Hello\0\0World";
     * $str = Util::getStringUTF16LE($bytes, 0, 10); // 返回："Hello"
     * ```
     */
    public static function getStringUTF16LE($bytes, $offset, $length)
    {
        $str = substr($bytes, $offset, $length);
        $pos = strpos($str, "\0\0");
        if ($pos !== false) {
            $str = substr($str, 0, $pos);
        }
        return mb_convert_encoding($str, 'UTF-8', 'UTF-16LE');
    }

    /**
     * 将字节数组转换为UTF-16BE字符串
     *
     * 从指定偏移量开始提取指定长度的字节并转换为UTF-16BE字符串
     * 自动截断到第一个双空字符（\0\0）处，并转换为UTF-8编码
     *
     * @param string $bytes 字节数组
     * @param int $offset 起始偏移量
     * @param int $length 要提取的最大字节长度
     * @return string 返回转换后的UTF-8字符串（从UTF-16BE转换）
     *
     * @example
     * ```php
     * $bytes = "Hello\0\0World";
     * $str = Util::getStringUTF16BE($bytes, 0, 10); // 返回："Hello"
     * ```
     */
    public static function getStringUTF16BE($bytes, $offset, $length)
    {
        $str = substr($bytes, $offset, $length);
        $pos = strpos($str, "\0\0");
        if ($pos !== false) {
            $str = substr($str, 0, $pos);
        }
        return mb_convert_encoding($str, 'UTF-8', 'UTF-16BE');
    }

    /**
     * 将字节数组转换为UTF-32字符串
     *
     * 从指定偏移量开始提取指定长度的字节并转换为UTF-32字符串
     * 自动截断到第一个四空字符（\0\0\0\0）处，并转换为UTF-8编码
     *
     * @param string $bytes 字节数组
     * @param int $offset 起始偏移量
     * @param int $length 要提取的最大字节长度
     * @return string 返回转换后的UTF-8字符串（从UTF-32转换）
     *
     * @example
     * ```php
     * $bytes = "Hello\0\0\0\0World";
     * $str = Util::getStringUTF32($bytes, 0, 10); // 返回："Hello"
     * ```
     */
    public static function getStringUTF32($bytes, $offset, $length)
    {
        $str = substr($bytes, $offset, $length);
        $pos = strpos($str, "\0\0\0\0");
        if ($pos !== false) {
            $str = substr($str, 0, $pos);
        }
        return mb_convert_encoding($str, 'UTF-8', 'UTF-32');
    }

    /**
     * 将字节数组转换为UTF-32LE字符串
     *
     * 从指定偏移量开始提取指定长度的字节并转换为UTF-32LE字符串
     * 自动截断到第一个四空字符（\0\0\0\0）处，并转换为UTF-8编码
     *
     * @param string $bytes 字节数组
     * @param int $offset 起始偏移量
     * @param int $length 要提取的最大字节长度
     * @return string 返回转换后的UTF-8字符串（从UTF-32LE转换）
     *
     * @example
     * ```php
     * $bytes = "Hello\0\0\0\0World";
     * $str = Util::getStringUTF32LE($bytes, 0, 10); // 返回："Hello"
     * ```
     */
    public static function getStringUTF32LE($bytes, $offset, $length)
    {
        $str = substr($bytes, $offset, $length);
        $pos = strpos($str, "\0\0\0\0");
        if ($pos !== false) {
            $str = substr($str, 0, $pos);
        }
        return mb_convert_encoding($str, 'UTF-8', 'UTF-32LE');
    }

    /**
     * 将字节数组转换为UTF-32BE字符串
     *
     * 从指定偏移量开始提取指定长度的字节并转换为UTF-32BE字符串
     * 自动截断到第一个四空字符（\0\0\0\0）处，并转换为UTF-8编码
     *
     * @param string $bytes 字节数组
     * @param int $offset 起始偏移量
     * @param int $length 要提取的最大字节长度
     * @return string 返回转换后的UTF-8字符串（从UTF-32BE转换）
     *
     * @example
     * ```php
     * $bytes = "Hello\0\0\0\0World";
     * $str = Util::getStringUTF32BE($bytes, 0, 10); // 返回："Hello"
     * ```
     */
    public static function getStringUTF32BE($bytes, $offset, $length)
    {
        $str = substr($bytes, $offset, $length);
        $pos = strpos($str, "\0\0\0\0");
        if ($pos !== false) {
            $str = substr($str, 0, $pos);
        }
        return mb_convert_encoding($str, 'UTF-8', 'UTF-32BE');
    }

    // ============================================================================
    // 字节序读取方法
    // ============================================================================

    /**
     * 从字节缓冲区解码一个4字节长整数（小端序）
     *
     * 从指定索引开始读取4个字节，按小端序转换为32位无符号整数
     * 在32位操作系统上自动将有符号整数转换为无符号整数
     *
     * @param string $b 字节缓冲区
     * @param int $idx 起始索引
     * @return int|string 返回转换后的无符号整数
     *
     * @example
     * ```php
     * $bytes = "\x01\x00\x00\x00"; // 小端序的1
     * $value = Util::le_getUint32($bytes, 0); // 返回：1
     * ```
     */
    public static function le_getUint32($b, $idx)
    {
        $val = (ord($b[$idx])) | (ord($b[$idx + 1]) << 8)
            | (ord($b[$idx + 2]) << 16) | (ord($b[$idx + 3]) << 24);

        // convert signed int to unsigned int if on 32 bit operating system
        if ($val < 0 && PHP_INT_SIZE == 4) {
            $val = sprintf("%u", $val);
        }

        return $val;
    }

    /**
     * 从字节缓冲区读取一个2字节整数（小端序）
     *
     * 从指定索引开始读取2个字节，按小端序转换为16位无符号整数
     *
     * @param string $b 字节缓冲区
     * @param int $idx 起始索引
     * @return int 返回转换后的无符号整数
     *
     * @example
     * ```php
     * $bytes = "\x01\x00"; // 小端序的1
     * $value = Util::le_getUint16($bytes, 0); // 返回：1
     * ```
     */
    public static function le_getUint16($b, $idx)
    {
        return ((ord($b[$idx])) | (ord($b[$idx + 1]) << 8));
    }

    // ============================================================================
    // IP地址比较方法
    // ============================================================================

    /**
     * 比较两个IP字节（parseIP 返回的打包字符串）
     *
     * 将IP地址字节与缓冲区中指定偏移量的字节进行比较
     * 用于在二进制数据中查找匹配的IP地址
     *
     * @param string $ip1 要比较的IP地址字节
     * @param string $buff 字节缓冲区
     * @param int $offset 缓冲区中的起始偏移量
     * @return int 返回 -1 (ip1 < ip2), 0 (ip1 == ip2) 或 1 (ip1 > ip2)
     *
     * @example
     * ```php
     * $ip1 = inet_pton('61.142.118.231');
     * $buff = "some data" . $ip1 . "more data";
     * $result = Util::ipSubCompare($ip1, $buff, 9); // 比较IP地址
     * ```
     */
    public static function ipSubCompare($ip1, $buff, $offset)
    {
        // $r = substr_compare($ip1, $buff, $offset, strlen($ip1));
        // @Note: substr_compare is not working, use the substr + strcmp instead
        $r = strcmp($ip1, substr($buff, $offset, strlen($ip1)));
        if ($r < 0) {
            return -1;
        } else if ($r > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * 比较两个IP字节
     *
     * 直接比较两个IP地址的字节表示
     * 用于IP地址的排序和查找
     *
     * @param string $ip1 第一个IP地址字节
     * @param string $ip2 第二个IP地址字节
     * @return int 返回 -1 (ip1 < ip2), 0 (ip1 == ip2) 或 1 (ip1 > ip2)
     *
     * @example
     * ```php
     * $ip1 = inet_pton('61.142.118.231');
     * $ip2 = inet_pton('8.8.8.9');
     * $result = Util::ipCompare($ip1, $ip2); // 返回：-1
     * ```
     */
    public static function ipCompare($ip1, $ip2)
    {
        $r = strcmp($ip1, $ip2);
        if ($r < 0) {
            return -1;
        } else if ($r > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    // ============================================================================
    // IP地址转换方法
    // ============================================================================

    /**
     * 将IP字节转换为字符串
     *
     * 将二进制格式的IP地址转换为可读的字符串格式
     * 支持IPv4和IPv6地址
     *
     * @param string $ipBytes 二进制格式的IP地址字节
     * @return string 返回可读的IP地址字符串，无效IP返回 '<invalid-ip-bytes>'
     *
     * @example
     * ```php
     * $ipBytes = inet_pton('61.142.118.231');
     * echo Util::ipToString($ipBytes); // 输出：61.142.118.231
     * ```
     */
    public static function ipToString($ipBytes)
    {
        $l = strlen($ipBytes);
        return ($l == 4 || $l == 16) ? inet_ntop($ipBytes) : '<invalid-ip-bytes>';
    }

    // ============================================================================
    // 版本解析方法
    // ============================================================================

    /**
     * 从名称解析IP版本
     *
     * 根据版本名称（如 "V4", "IPv4", "V6", "IPv6"）返回对应的版本对象
     *
     * @param string $ver_name 版本名称
     * @return IPv4|IPv6 返回对应的版本对象
     * @throws \Exception 当版本名称无效时抛出异常
     *
     * @example
     * ```php
     * $version = Util::versionFromName('IPv4'); // 返回 IPv4::default()
     * $version = Util::versionFromName('V6'); // 返回 IPv6::default()
     * ```
     */
    public static function versionFromName($ver_name)
    {
        $name = strtoupper($ver_name);
        if ($name == "V4" || $name == "IPV4") {
            return IPv4::default();
        } else if ($name == "V6" || $name == "IPV6") {
            return IPv6::default();
        } else {
            throw new \Exception("invalid version name `{$ver_name}`");
        }
    }

    /**
     * 从头部信息解析IP版本
     *
     * 根据XDB文件头部信息返回对应的版本对象
     * 支持2.0和3.0两种结构版本
     *
     * @param array $header 头部信息数组
     * @return IPv4|IPv6 返回对应的版本对象
     * @throws \Exception 当版本信息无效时抛出异常
     *
     * @example
     * ```php
     * $header = Util::loadHeaderFromFile('/path/to/ipv4.xdb');
     * $version = Util::versionFromHeader($header);
     * ```
     */
    public static function versionFromHeader($header)
    {
        // Old structure 2.0 with IPv4 supports ONLY
        if ($header['version'] == self::Structure_20) {
            return IPv4::default();
        }

        // structure 3.0 after IPv6 supporting
        if ($header['version'] != self::Structure_30) {
            throw new \Exception("invalid xdb structure version `{$header['version']}`");
        }

        if ($header['ipVersion'] == self::IPv4VersionNo) {
            return IPv4::default();
        } else if ($header['ipVersion'] == self::IPv6VersionNo) {
            return IPv6::default();
        } else {
            throw new \Exception("invalid ip version number `{$header['ipVersion']}`");
        }
    }

    // ============================================================================
    // 字节数组格式化方法
    // ============================================================================

    /**
     * 将字节数组转换为格式化字符串
     *
     * 将二进制字节数组转换为空格分隔的数字字符串，用于调试
     *
     * @param string $buff 字节缓冲区
     * @param int $offset 起始偏移量
     * @param int $length 要转换的字节长度
     * @return string 返回格式化的字符串表示
     *
     * @example
     * ```php
     * $bytes = "\x01\x02\x03\x04";
     * echo Util::bytesToString($bytes, 0, 4); // 输出：[1 2 3 4]
     * ```
     */
    public static function bytesToString($buff, $offset, $length)
    {
        $sb = [];
        for ($i = 0; $i < $length; $i++) {
            $sb[] = ord($buff[$offset + $i]) & 0xFF;
        }
        return '[' . implode(' ', $sb) . ']';
    }

    // ============================================================================
    // XDB文件验证和加载方法
    // ============================================================================

    /**
     * 验证当前搜索引擎是否可用于搜索指定的XDB文件
     *
     * 为什么需要这个检查？
     * 未来XDB实现的新特性可能导致当前搜索引擎无法正常工作
     *
     * @Note 注意：只需在服务启动时检查一次
     * 或使用另一个进程（如命令）检查一次以确认适用性
     *
     * @param resource $handle 文件句柄
     * @return string|null 一切正常返回 null，否则返回错误字符串
     *
     * @example
     * ```php
     * $handle = fopen('/path/to/ipv4.xdb', 'r');
     * $error = Util::verify($handle);
     * if ($error !== null) {
     *     echo "验证失败: {$error}";
     * }
     * fclose($handle);
     * ```
     */
    public static function verify($handle)
    {
        // load the header
        $header = self::loadHeader($handle);
        if ($header == null) {
            return 'failed to load the header';
        }

        // get the runtime ptr bytes
        $runtimePtrBytes = 0;
        if ($header['version'] == self::Structure_20) {
            $runtimePtrBytes = 4;
        } else if ($header['version'] == self::Structure_30) {
            $runtimePtrBytes = $header['runtimePtrBytes'];
        } else {
            return "invalid structure version `{$header['version']}`";
        }

        // 1, confirm the xdb file size
        // to ensure that the maximum file pointer does not overflow
        $stat = fstat($handle);
        if ($stat == false) {
            return 'failed to stat the xdb file';
        }

        $maxFilePtr = (1 << ($runtimePtrBytes * 8)) - 1;
        // print_r([$stat['size'], $maxFilePtr]);
        if ($stat['size'] > $maxFilePtr) {
            return "xdb file exceeds the maximum supported bytes: {$maxFilePtr}";
        }

        return null;
    }

    /**
     * 从文件验证XDB文件
     *
     * 打开并验证指定的XDB文件是否可用
     *
     * @param string $dbFile XDB文件路径
     * @return string|null 返回错误信息字符串，验证通过返回 null
     *
     * @example
     * ```php
     * $error = Util::verifyFromFile('/path/to/ipv4.xdb');
     * if ($error !== null) {
     *     echo "验证失败: {$error}";
     * }
     * ```
     */
    public static function verifyFromFile($dbFile)
    {
        $handle = fopen($dbFile, 'r');
        if ($handle === false) {
            return null;
        }

        $r = self::verify($handle);
        fclose($handle);
        return $r;
    }

    /**
     * 从文件句柄加载头部信息
     *
     * 从XDB文件句柄读取并解析头部信息
     *
     * @param resource $handle 文件句柄
     * @return array|null 返回头部信息数组，失败返回 null
     *
     * @example
     * ```php
     * $handle = fopen('/path/to/ipv4.xdb', 'r');
     * $header = Util::loadHeader($handle);
     * print_r($header);
     * fclose($handle);
     * ```
     */
    public static function loadHeader($handle)
    {
        if (fseek($handle, 0) == -1) {
            return null;
        }

        $buff = fread($handle, self::HeaderInfoLength);
        if ($buff === false) {
            return null;
        }

        // read bytes length checking
        if (strlen($buff) != self::HeaderInfoLength) {
            return null;
        }

        // return the decoded header info
        return array(
            'version'         => self::le_getUint16($buff, 0),
            'indexPolicy'     => self::le_getUint16($buff, 2),
            'createdAt'       => self::le_getUint32($buff, 4),
            'startIndexPtr'   => self::le_getUint32($buff, 8),
            'endIndexPtr'     => self::le_getUint32($buff, 12),
            'ipVersion'       => self::le_getUint16($buff, 16),
            'runtimePtrBytes' => self::le_getUint16($buff, 18)
        );
    }

    /**
     * 从文件路径加载头部信息
     *
     * 打开XDB文件并读取头部信息
     *
     * @param string $dbFile XDB文件路径
     * @return array|null 返回头部信息数组，失败返回 null
     *
     * @example
     * ```php
     * $header = Util::loadHeaderFromFile('/path/to/ipv4.xdb');
     * print_r($header);
     * ```
     */
    public static function loadHeaderFromFile($dbFile)
    {
        $handle = fopen($dbFile, 'r');
        if ($handle === false) {
            return null;
        }

        $header = self::loadHeader($handle);
        fclose($handle);
        return $header;
    }

    /**
     * 从文件句柄加载向量索引
     *
     * 从XDB文件句柄读取向量索引数据
     *
     * @param resource $handle 文件句柄
     * @return string|null 返回向量索引二进制数据，失败返回 null
     *
     * @example
     * ```php
     * $handle = fopen('/path/to/ipv4.xdb', 'r');
     * $vIndex = Util::loadVectorIndex($handle);
     * fclose($handle);
     * ```
     */
    public static function loadVectorIndex($handle)
    {
        if (fseek($handle, self::HeaderInfoLength) == -1) {
            return null;
        }

        $rLen = self::VectorIndexRows * self::VectorIndexCols * self::VectorIndexSize;
        $buff = fread($handle, $rLen);
        if ($buff === false) {
            return null;
        }

        if (strlen($buff) != $rLen) {
            return null;
        }

        return $buff;
    }

    /**
     * 从文件路径加载向量索引
     *
     * 打开XDB文件并读取向量索引数据
     *
     * @param string $dbFile XDB文件路径
     * @return string|null 返回向量索引二进制数据，失败返回 null
     *
     * @example
     * ```php
     * $vIndex = Util::loadVectorIndexFromFile('/path/to/ipv4.xdb');
     * $searcher = Searcher::newWithVectorIndex(4, '/path/to/ipv4.xdb', $vIndex);
     * ```
     */
    public static function loadVectorIndexFromFile($dbFile)
    {
        $handle = fopen($dbFile, 'r');
        if ($handle === false) {
            return null;
        }

        $vIndex = self::loadVectorIndex($handle);
        fclose($handle);
        return $vIndex;
    }

    /**
     * 从文件句柄加载XDB内容
     *
     * 从XDB文件句柄读取整个文件内容到内存
     *
     * @param resource $handle 文件句柄
     * @return string|null 返回文件内容，失败返回 null
     *
     * @example
     * ```php
     * $handle = fopen('/path/to/ipv4.xdb', 'r');
     * $content = Util::loadContent($handle);
     * fclose($handle);
     * ```
     */
    public static function loadContent($handle)
    {
        if (fseek($handle, 0, SEEK_END) == -1) {
            return null;
        }

        $size = ftell($handle);
        if ($size === false) {
            return null;
        }

        // seek to the head for reading
        if (fseek($handle, 0) == -1) {
            return null;
        }

        $buff = fread($handle, $size);
        if ($buff === false) {
            return null;
        }

        // read length checking
        if (strlen($buff) != $size) {
            return null;
        }

        return $buff;
    }

    /**
     * 从文件路径加载XDB内容
     *
     * 读取整个XDB文件内容到内存
     *
     * @param string $dbFile XDB文件路径
     * @return string|null 返回文件内容，失败返回 null
     *
     * @example
     * ```php
     * $content = Util::loadContentFromFile('/path/to/ipv4.xdb');
     * $searcher = Searcher::newWithBuffer(4, $content);
     * ```
     */
    public static function loadContentFromFile($dbFile)
    {
        $str = file_get_contents($dbFile, false);
        if ($str === false) {
            return null;
        } else {
            return $str;
        }
    }

    /**
     * 获取当前时间戳（毫秒）
     *
     * 返回当前时间的毫秒级时间戳
     *
     * @return float 返回毫秒级时间戳
     *
     * @example
     * ```php
     * $start = Util::now();
     * // 执行一些操作
     * $elapsed = Util::now() - $start;
     * echo "耗时: {$elapsed} 毫秒";
     * ```
     */
    public static function now()
    {
        return (microtime(true) * 1000);
    }
}
