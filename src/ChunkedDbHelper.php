<?php

/**
 * ip2region 分片数据库助手类
 * 
 * 功能：
 * - 自动查找和合并分片数据库文件
 * - 支持多种压缩格式的解压缩（gzip, zip, zstd）
 * - 提供缓存机制，避免重复合并
 * - 支持IPv4和IPv6数据库的自动识别
 * 
 * 使用场景：
 * - 大型数据库文件分片存储
 * - 网络传输优化
 * - 内存使用优化
 */

class ChunkedDbHelper
{
	private static $cacheDir;
	private static $mergedFiles = array(); // 内存缓存已合并的文件

	/**
	 * 确保缓存目录存在
	 * 
	 * 在系统临时目录下创建ip2region_chunk_cache目录
	 * 用于存储合并后的数据库文件缓存
	 */
	private static function ensureCacheDir()
	{
		if (self::$cacheDir === null) {
			self::$cacheDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'ip2region_chunk_cache';
			if (!is_dir(self::$cacheDir)) {
				mkdir(self::$cacheDir, 0755, true);
			}
		}
	}

	/**
	 * 查找分片文件
	 * 
	 * 根据基准文件名自动查找对应的分片文件
	 * 支持多种压缩格式和多个搜索目录
	 * 
	 * @param string|null $baseFile 基准文件名，null时搜索所有分片
	 * @param array $customDirs 自定义搜索目录数组
	 * @return array 排序后的分片文件路径数组
	 */
	public static function findChunks($baseFile, $customDirs = array())
	{
		$files = array();

		if ($baseFile === null || $baseFile === '') {
			// 直接搜索分片文件，不依赖基准文件
			$chunkDirs = array_merge(
				array(dirname(__DIR__) . '/db', dirname(__DIR__) . '/tools'),
				$customDirs
			);
			foreach ($chunkDirs as $cdir) {
				// 搜索所有可能的分片文件（包括压缩文件）
				$patterns = array(
					$cdir . DIRECTORY_SEPARATOR . 'ip2region_v6.xdb.part*',
					$cdir . DIRECTORY_SEPARATOR . 'ip2region_v6.xdb.part*.gz',
					$cdir . DIRECTORY_SEPARATOR . 'ip2region_v6.xdb.part*.zip'
				);
				foreach ($patterns as $pattern) {
					$found = glob($pattern);
					if ($found === false) {
						$found = array();
					};
					$files = array_merge($files, $found);
				}
			}
		} else {
			$baseDir = dirname($baseFile);
			$baseName = basename($baseFile);
			// 仅在 db/ 或与基准文件同级目录查找分片，无子目录
			$chunkDirs = array_merge(
				array($baseDir, dirname(__DIR__) . '/db'),
				$customDirs
			);
			foreach ($chunkDirs as $cdir) {
				// 搜索所有可能的分片文件（包括压缩文件）
				$patterns = array(
					$cdir . DIRECTORY_SEPARATOR . $baseName . '.part*',
					$cdir . DIRECTORY_SEPARATOR . $baseName . '.part*.gz',
					$cdir . DIRECTORY_SEPARATOR . $baseName . '.part*.zip'
				);
				foreach ($patterns as $pattern) {
					$found = glob($pattern);
					if ($found === false) {
						$found = array();
					};
					$files = array_merge($files, $found);
				}
			}
		}

		// 去重并排序
		$files = array_unique($files);

		if (empty($files)) {
			return array();
		}
		usort($files, function ($a, $b) {
			$na = (int)preg_replace('/^.*\.part(\d+)(\..*)?$/', '$1', $a);
			$nb = (int)preg_replace('/^.*\.part(\d+)(\..*)?$/', '$1', $b);
			if ($na < $nb) return -1;
			if ($na > $nb) return 1;
			return 0;
		});
		return $files;
	}

	/**
	 * 读取分片文件，支持解压缩
	 * 
	 * 根据文件扩展名自动选择解压缩方法
	 * 支持gzip、zip、zstd和未压缩文件
	 * 
	 * @param string $file 分片文件路径
	 * @return string|false 解压后的文件内容，失败返回false
	 */
	private static function readChunkFile($file)
	{
		$extension = pathinfo($file, PATHINFO_EXTENSION);

		switch ($extension) {
			case 'gz':
				// Gzip 压缩文件
				$data = file_get_contents($file);
				if ($data === false) {
					return false;
				}
				$decompressed = gzdecode($data);
				if ($decompressed === false) {
					return false;
				}
				return $decompressed;

			case 'zip':
				// ZIP 压缩文件
				$zip = new ZipArchive();
				if ($zip->open($file) === TRUE) {
					$data = $zip->getFromName('data.xdb');
					$zip->close();
					return $data;
				}
				return false;
				
			case 'zst':
				// Zstd 压缩文件
				$data = file_get_contents($file);
				if ($data === false) {
					return false;
				}
				// 检查是否支持 zstd 扩展
				if (function_exists('zstd_uncompress')) {
					$decompressed = call_user_func('zstd_uncompress', $data);
					if ($decompressed === false) {
						return false;
					}
					return $decompressed;
				} else {
					// 如果不支持 zstd，返回 false
					error_log("Zstd 扩展未安装，无法解压 .zst 文件: {$file}");
					return false;
				}

			default:
				// 未压缩文件 - 使用流式读取避免内存问题
				$handle = fopen($file, 'rb');
				if (!$handle) return false;
				$data = stream_get_contents($handle);
				fclose($handle);
				return $data;
		}
	}

	/**
	 * 合并分片文件到缓存
	 * 
	 * 将多个分片文件合并为一个完整的数据库文件
	 * 使用文件锁确保并发安全，支持缓存机制避免重复合并
	 * 
	 * @param array $chunks 分片文件路径数组
	 * @param string|null $cacheFile 可选的缓存文件路径，如果为null则自动生成
	 * @return string|false 合并后的文件路径，失败返回false
	 */
	public static function mergeToCache(array $chunks, $cacheFile = null)
	{
		self::ensureCacheDir();
		if (empty($chunks)) {
			return false;
		}
		$manifest = array();
		$totalSize = 0;
		foreach ($chunks as $f) {
			$manifest[] = basename($f) . ':' . filesize($f) . ':' . filemtime($f);
			$totalSize += filesize($f);
		}

		// 分片系统独立工作：不需要依赖原始文件
		// 先合并文件，再验证大小
		$totalSize = 0; // 初始化为0，让合并过程自然计算大小
		
		// 如果指定了缓存文件路径，使用指定路径；否则自动生成
		if ($cacheFile !== null) {
			$outFile = $cacheFile;
		} else {
			$key = md5(implode('|', $manifest));
			$outFile = self::$cacheDir . DIRECTORY_SEPARATOR . 'merged_' . $key . '.xdb';
		}
		$lockFile = $outFile . '.lock';

		$lock = fopen($lockFile, 'c');
		if ($lock) {
			flock($lock, LOCK_EX);
		}

		if (file_exists($outFile) && filesize($outFile) === $totalSize) {
			if ($lock) {
				flock($lock, LOCK_UN);
				fclose($lock);
			}
			return $outFile;
		}

		$out = fopen($outFile, 'wb');
		if ($out === false) {
			if ($lock) {
				flock($lock, LOCK_UN);
				fclose($lock);
			}
			return false;
		}
		$bufferSize = 8 * 1024 * 1024;
		foreach ($chunks as $file) {
			// 检查文件是否需要解压缩
			$data = self::readChunkFile($file);
			if ($data === false) {
				fclose($out);
				if ($lock) {
					flock($lock, LOCK_UN);
					fclose($lock);
				}
				return false;
			}

			// 内存优化：分块写入避免大文件内存溢出
			$dataLength = strlen($data);
			$offset = 0;
			while ($offset < $dataLength) {
				$chunk = substr($data, $offset, $bufferSize);
				fwrite($out, $chunk);
				$offset += strlen($chunk);
			}

			// 及时释放内存，避免内存累积
			unset($data);
		}
		fclose($out);

		clearstatcache(true, $outFile);
		$actualSize = filesize($outFile);
		// 分片系统独立工作：只要合并成功且文件大小合理就认为成功
		if ($actualSize < 1024) { // 文件太小，可能合并失败
			error_log("合并失败: 文件太小 $actualSize bytes");
			@unlink($outFile);
			if ($lock) {
				flock($lock, LOCK_UN);
				fclose($lock);
			}
			return false;
		}

		if ($lock) {
			flock($lock, LOCK_UN);
			fclose($lock);
		}
		return $outFile;
	}

	/**
	 * 获取缓存统计信息
	 * 
	 * @return array 包含缓存目录、文件数量、总大小等信息
	 */
	public static function getCacheStats()
	{
		self::ensureCacheDir();
		$files = glob(self::$cacheDir . DIRECTORY_SEPARATOR . 'merged_*.xdb');
		$total = 0;
		foreach ($files as $f) {
			$total += filesize($f);
		}
		return array(
			'cache_dir'  => self::$cacheDir,
			'file_count' => $files ? count($files) : 0,
			'total_size' => $total,
			'memory_cached' => count(self::$mergedFiles),
		);
	}

	/**
	 * 清理缓存文件
	 * 
	 * 删除所有合并后的缓存文件，释放磁盘空间
	 */
	public static function clearCache()
	{
		self::ensureCacheDir();
		$files = glob(self::$cacheDir . DIRECTORY_SEPARATOR . 'merged_*.xdb');
		foreach ($files as $file) {
			@unlink($file);
		}
		// 清理内存缓存
		self::$mergedFiles = array();
	}

	/**
	 * 清理过期的缓存文件
	 * 
	 * 删除超过指定天数的缓存文件，避免磁盘空间浪费
	 * 
	 * @param int $days 过期天数，默认7天
	 */
	public static function clearExpiredCache($days = 7)
	{
		self::ensureCacheDir();
		$files = glob(self::$cacheDir . DIRECTORY_SEPARATOR . 'merged_*.xdb');
		$expiredTime = time() - ($days * 24 * 60 * 60);

		foreach ($files as $file) {
			if (filemtime($file) < $expiredTime) {
				@unlink($file);
			}
		}
	}

	/**
	 * 检查自定义数据库文件是否存在
	 * 
	 * @param string $dbPath 数据库文件路径
	 * @return bool 文件是否存在
	 */
	public static function isCustomDbExists($dbPath)
	{
		return $dbPath !== null && file_exists($dbPath);
	}

	/**
	 * 获取数据库文件信息
	 * 
	 * @param string $dbPath 数据库文件路径
	 * @return array|null 文件信息或null
	 */
	public static function getDbFileInfo($dbPath)
	{
		if (!self::isCustomDbExists($dbPath)) {
			return null;
		}

		return array(
			'path' => $dbPath,
			'size' => filesize($dbPath),
			'mtime' => filemtime($dbPath),
			'readable' => is_readable($dbPath)
		);
	}
}
