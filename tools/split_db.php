<?php

/**
 * ip2region 数据库分片工具
 * 
 * 功能：
 * - 将大型 .xdb 数据库文件拆分为指定大小的分片
 * - 支持多种压缩算法（gzip, zip, zstd）
 * - 智能分片大小调整，确保分片大小均匀
 * - 支持快速模式，跳过压缩以提升速度
 * 
 * 用法:
 *   php tools/split_db.php v6 [sizeMB] [compress] [fast]
 *   php tools/split_db.php /abs/path/to/ip2region_v6.xdb [sizeMB] [compress] [fast]
 * 
 * 参数说明:
 *   sizeMB: 分片大小限制（MB），默认 100
 *   compress: 压缩方式，可选值：gzip, zip, zstd, none，默认 gzip
 *   fast: 快速模式，跳过压缩，默认 false
 * 
 * 示例:
 *   # 标准压缩模式（推荐）
 *   php tools/split_db.php v6 50 gzip
 *   
 *   # 快速模式（无压缩）
 *   php tools/split_db.php v6 100 gzip fast
 *   
 *   # 最大压缩（分片较多）
 *   php tools/split_db.php v6 30 gzip
 */

$arg1 = isset($argv[1]) ? $argv[1] : 'v6';
$sizeMb = isset($argv[2]) ? max(1, (int)$argv[2]) : 100;
$compress = isset($argv[3]) ? $argv[3] : 'gzip';
$fastMode = isset($argv[4]) && ($argv[4] === 'true' || $argv[4] === '1' || $argv[4] === 'fast');
$sizeLimit = $sizeMb * 1024 * 1024;

// 快速模式：跳过压缩
if ($fastMode) {
	$compress = 'none';
}

// 验证压缩方式
$validCompress = array('gzip', 'zip', 'zstd', 'none');
if (!in_array($compress, $validCompress)) {
	fwrite(STDERR, "无效的压缩方式: $compress，支持的方式: " . implode(', ', $validCompress) . "\n");
	exit(1);
}

// 检查压缩扩展
if ($compress === 'gzip' && !extension_loaded('zlib')) {
	fwrite(STDERR, "警告: zlib 扩展未加载，将使用 zip 压缩\n");
	$compress = 'zip';
}
if ($compress === 'zip' && !extension_loaded('zip')) {
	fwrite(STDERR, "警告: zip 扩展未加载，将使用 gzip 压缩\n");
	$compress = 'gzip';
}
if ($compress === 'zstd' && !extension_loaded('zstd')) {
	fwrite(STDERR, "警告: zstd 扩展未加载，将使用 gzip 压缩\n");
	$compress = 'gzip';
}
if ($compress === 'gzip' && !extension_loaded('zlib')) {
	fwrite(STDERR, "警告: 压缩扩展不可用，将不进行压缩\n");
	$compress = 'none';
}

// 允许传入绝对或相对路径
if (preg_match('/\.xdb$/', $arg1)) {
	$baseFile = realpath($arg1) ?: $arg1;
} else {
	$version = $arg1;
	$candidates = [
		__DIR__ . '/../gz/ip2region_' . $version . '.xdb',
		__DIR__ . '/ip2region_' . $version . '.xdb',
		dirname(__DIR__) . '/ip2region_' . $version . '.xdb',
		dirname(__DIR__) . '/src/ip2region_' . $version . '.xdb',
	];
	$baseFile = '';
	foreach ($candidates as $c) {
		if (file_exists($c)) {
			$baseFile = $c;
			break;
		}
	}
}

if (!$baseFile || !file_exists($baseFile)) {
	fwrite(STDERR, "源文件不存在: " . ($baseFile ?: '(未找到)') . "\n");
	exit(1);
}

// 分片输出目录：与源文件同级的 chunks/，若源在 dbs/ 则放 dbs/chunks
$root = dirname(__DIR__);
// 统一输出到 db/ 根目录（不使用子目录）
if (!is_dir($root . '/db')) {
	mkdir($root . '/db', 0755, true);
}
$chunkDir = $root . '/db';
if (!is_dir($chunkDir)) {
	mkdir($chunkDir, 0755, true);
}

$baseName = basename($baseFile);
$in = fopen($baseFile, 'rb');
if (!$in) {
	fwrite(STDERR, "无法读取: $baseFile\n");
	exit(1);
}

/**
 * 压缩文件函数
 * 
 * 使用流式处理避免内存问题，支持多种压缩算法
 * 
 * @param string $inputFile  输入文件路径
 * @param string $outputFile 输出文件路径
 * @param string $method     压缩方法 (gzip, zip, zstd, none)
 * @return bool 压缩是否成功
 */
function compressFile($inputFile, $outputFile, $method)
{
	switch ($method) {
		case 'gzip':
			// 使用流式 gzip 压缩
			$in = fopen($inputFile, 'rb');
			$out = fopen($outputFile, 'wb');
			if (!$in || !$out) {
				if ($in) fclose($in);
				if ($out) fclose($out);
				return false;
			}

			// 创建 gzip 压缩流 - 使用最高压缩级别
			$gz = gzopen($outputFile, 'wb9');
			if (!$gz) {
				fclose($in);
				fclose($out);
				return false;
			}

			// 流式复制和压缩 - 使用更大的块提升速度
			while (!feof($in)) {
				$data = fread($in, 64 * 1024); // 64KB 块
				if ($data !== false) {
					gzwrite($gz, $data);
				}
			}

			gzclose($gz);
			fclose($in);
			fclose($out);
			return true;

		case 'zip':
			// 使用流式 zip 压缩
			$zip = new ZipArchive();
			if ($zip->open($outputFile, ZipArchive::CREATE) === TRUE) {
				$zip->addFile($inputFile, 'data.xdb');
				$result = $zip->close();
				return $result;
			}
			return false;

		case 'zstd':
			// 使用 zstd 压缩
			$in = fopen($inputFile, 'rb');
			$out = fopen($outputFile, 'wb');
			if (!$in || !$out) {
				if ($in) fclose($in);
				if ($out) fclose($out);
				return false;
			}

			// 使用 zstd 压缩
			$data = file_get_contents($inputFile);
			if ($data === false) {
				fclose($in);
				fclose($out);
				return false;
			}

			$compressed = zstd_compress($data, 22); // 最高压缩级别
			if ($compressed === false) {
				fclose($in);
				fclose($out);
				return false;
			}

			fwrite($out, $compressed);
			fclose($in);
			fclose($out);
			return true;

		case 'none':
		default:
			// 直接复制文件
			return copy($inputFile, $outputFile);
	}
}

/**
 * 获取压缩文件扩展名
 * 
 * @param string $method 压缩方法
 * @return string 对应的文件扩展名
 */
function getFileExtension($method)
{
	switch ($method) {
		case 'gzip':
			return '.gz';
		case 'zip':
			return '.zip';
		case 'zstd':
			return '.zst';
		case 'none':
		default:
			return '';
	}
}

// 获取文件总大小用于计算平均分片大小
$fileSize = filesize($baseFile);
$estimatedChunks = ceil($fileSize / $sizeLimit);
$targetChunkSize = $fileSize / $estimatedChunks; // 目标分片大小

// 智能分片算法：动态调整分片大小确保均匀分布

$idx = 1;
$written = 0;
$bufferSize = 16 * 1024 * 1024; // 增大缓冲区到16MB
$out = null;
$outPath = '';
$totalSize = 0;
$compressedSize = 0;

echo "开始拆分文件: $baseFile\n";
echo "文件总大小: " . round($fileSize / 1024 / 1024, 2) . "MB\n";
echo "分片大小限制: " . round($sizeMb, 2) . "MB\n";
echo "预计分片数量: $estimatedChunks\n";
echo "目标分片大小: " . round($targetChunkSize / 1024 / 1024, 2) . "MB\n";
echo "压缩方式: $compress" . ($fastMode ? " (快速模式)" : "") . "\n";
echo "输出目录: $chunkDir\n\n";

while (!feof($in)) {
	// 动态调整分片大小，确保分片更加均匀
	$remainingBytes = $fileSize - ftell($in);
	$remainingChunks = $estimatedChunks - $idx + 1;

	// 智能分片算法：根据剩余数据动态调整分片大小
	// 如果剩余数据可以平均分配到剩余分片中，则使用平均大小
	$dynamicSizeLimit = $remainingChunks > 0 ? $remainingBytes / $remainingChunks : $sizeLimit;
	$dynamicSizeLimit = min($sizeLimit, max($dynamicSizeLimit, $sizeLimit * 0.8)); // 至少是限制大小的80%

	$needRotate = ($out === null) || ($written >= $dynamicSizeLimit);
	if ($needRotate) {
		if ($out) {
			fclose($out);

			// 压缩当前分片文件
			if ($compress !== 'none') {
				$tempFile = $outPath . '.tmp';
				$compressedFile = $outPath;

				// 重命名临时文件
				rename($outPath, $tempFile);

				// 压缩文件
				if (compressFile($tempFile, $compressedFile, $compress)) {
					$originalSize = filesize($tempFile);
					$compressedSize += filesize($compressedFile);
					$totalSize += $originalSize;

					$compressionRatio = $originalSize > 0 ? (1 - filesize($compressedFile) / $originalSize) * 100 : 0;
					echo "分片 $idx: " . round($originalSize / 1024 / 1024, 2) . "MB -> " . round(filesize($compressedFile) / 1024 / 1024, 2) . "MB (" . round($compressionRatio, 1) . "% 压缩)\n";

					// 删除临时文件
					unlink($tempFile);
				} else {
					// 压缩失败，恢复原文件
					rename($tempFile, $outPath);
					$compressedSize += filesize($outPath);
					$totalSize += filesize($outPath);
					echo "分片 $idx: " . round(filesize($outPath) / 1024 / 1024, 2) . "MB (压缩失败，使用原文件)\n";
				}
			} else {
				$compressedSize += filesize($outPath);
				$totalSize += filesize($outPath);
				echo "分片 $idx: " . round(filesize($outPath) / 1024 / 1024, 2) . "MB (未压缩)\n";
			}
		}

		// 生成分片文件名
		$extension = getFileExtension($compress);
		$outPath = $chunkDir . '/' . $baseName . '.part' . $idx . $extension;

		$out = fopen($outPath, 'wb');
		if (!$out) {
			fclose($in);
			fwrite(STDERR, "无法写入: $outPath\n");
			exit(1);
		}
		$written = 0;
		$idx++;
	}

	$data = fread($in, min($bufferSize, (int)($dynamicSizeLimit - $written)));
	if ($data === false || $data === '') break;

	fwrite($out, $data);
	$written += strlen($data);
}

// 处理最后一个分片
if ($out) {
	fclose($out);

	// 压缩最后一个分片
	if ($compress !== 'none') {
		$tempFile = $outPath . '.tmp';
		$compressedFile = $outPath;

		// 重命名临时文件
		rename($outPath, $tempFile);

		// 压缩文件
		if (compressFile($tempFile, $compressedFile, $compress)) {
			$originalSize = filesize($tempFile);
			$compressedSize += filesize($compressedFile);
			$totalSize += $originalSize;

			$compressionRatio = $originalSize > 0 ? (1 - filesize($compressedFile) / $originalSize) * 100 : 0;
			echo "分片 $idx: " . round($originalSize / 1024 / 1024, 2) . "MB -> " . round(filesize($compressedFile) / 1024 / 1024, 2) . "MB (" . round($compressionRatio, 1) . "% 压缩)\n";

			// 删除临时文件
			unlink($tempFile);
		} else {
			// 压缩失败，恢复原文件
			rename($tempFile, $outPath);
			$compressedSize += filesize($outPath);
			$totalSize += filesize($outPath);
			echo "分片 $idx: " . round(filesize($outPath) / 1024 / 1024, 2) . "MB (压缩失败，使用原文件)\n";
		}
	} else {
		$compressedSize += filesize($outPath);
		$totalSize += filesize($outPath);
		echo "分片 $idx: " . round(filesize($outPath) / 1024 / 1024, 2) . "MB (未压缩)\n";
	}
}

fclose($in);

echo "\n拆分完成！\n";
echo "总文件数: " . ($idx - 1) . "\n";
echo "原始大小: " . round($totalSize / 1024 / 1024, 2) . "MB\n";
echo "压缩后大小: " . round($compressedSize / 1024 / 1024, 2) . "MB\n";
if ($compress !== 'none') {
	echo "压缩率: " . round((1 - $compressedSize / $totalSize) * 100, 1) . "%\n";
}
echo "输出目录: $chunkDir\n";
