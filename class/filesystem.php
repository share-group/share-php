<?php
/**
 * 文件系统类
 */
class filesystem{
	private static $sizes = array('Byte', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
	/**
	 * 计算某文件的大小(32位系统只能到1.96GB)
	 * @param $file 文件路径
	 * @param $round 精确度(默认小数点后两位)
	 * @return 文件大小
	 */
	public static function size($file, $round = 2){
		$size = filesize($file);
		if ($size == 0) {
			return '0 '.self::$sizes[0];
		}
		return self::bytes($file, $round);
	}
	
	/**
	 * 字节数转换
	 * @param $bytes
	 * @param $round
	 */
	public static function bytes($bytes, $round = 2){
		return round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), $round).' '.self::$sizes[$i];
	}

	/**
	 * 创建文件夹
	 * @param $dir 文件夹路径
	 * @param $mode 模式(默认0777)
	 * @param $recursive 是否递归创建(默认true)
	 */
	public static function mkdir($dir, $mode = 0777, $recursive = true){
		if(file_exists($dir)){
			return;
		}
		mkdir($dir, $mode, $recursive) or die('create dir '.$dir.' error...');
	}

	/**
	 * 删除文件或文件夹
	 * @param $dir 路径
	 */
	public static function rm($dir){
		if(is_dir($dir)){
			$list = self::ls($dir);
			foreach ($list as $file){
				$file = $dir.'/'.$file;
				if (is_file($file)){
					unlink($file);
				}
				if(is_dir($file)){
					self::rm($file);
				}
			}
			rmdir($dir);
		} elseif (is_file($dir)){
			unlink($dir);
		}
	}

	/**
	 * 写入文件
	 * @param $flie 文件路径
	 * @param $data 写入的数据
	 * @param $append 是否追加(默认不追加)
	 * @return 写入到文件内数据的字节数
	 */
	public static function write($file, $data, $append = false){
		if(!file_exists($file)){
			$dir = dirname($file);
			$file = $dir.'/'.basename($file);
			self::mkdir($dir);
		} elseif(!is_writable($file)) {
			return;
		}
		if($append === true){
			$byte = file_put_contents($file, $data, FILE_APPEND);
		} else {
			$byte = file_put_contents($file, $data, LOCK_EX);
		}
		return $byte;
	}

	/**
	 * 获取文件最后修改时间
	 * @param $file 文件路径
	 * @param $format 返回的时间格式，默认是时间戳，若已经定义了格式的话按照定义的格式返回
	 * @return 文件最后修改时间
	 */
	public static function mtime($file, $format = null){
		return self::get_file_time('filemtime', $file, $format);
	}

	/**
	 * 获取文件最后访问时间
	 * @param $file 文件路径
	 * @param $format 返回的时间格式，默认是时间戳，若已经定义了格式的话按照定义的格式返回
	 * @return 文件最后访问时间
	 */
	public static function atime($file, $format = null){
		return self::get_file_time('fileatime', $file, $format);
	}

	/**
	 * 获取文件最后改变时间
	 * @param $file 文件路径
	 * @param $format 返回的时间格式，默认是时间戳，若已经定义了格式的话按照定义的格式返回
	 * @return 文件最后改变时间
	 */
	public static function ctime($file, $format = null){
		return self::get_file_time('filectime', $file, $format);
	}

	/**
	 * 获取与文件相关的时间
	 * @param $method 调用的函数名
	 * @param $file 文件路径
	 * @param $format 返回的时间格式，默认是时间戳，若已经定义了格式的话按照定义的格式返回
	 */
	private static function get_file_time($method, $file, $format = null){
		clearstatcache();
		$time = $method($file);
		if(!$format){
			return $time;
		}
		return date($format, $time);
	}

	/**
	 * 列出目录所有内容
	 * @param $dir 目录路径
	 * @param $mode 匹配模式(默认是文件夹内任意内容)
	 * @param $sort 排序(默认是按文件名顺序)，如果是false，则不排序
	 * @return 指定文件夹内容列表
	 */
	public static function ls($dir, $mode = false, $sort = FILE_ASC){
		chdir($dir);
		if($mode === false){
			$mode = '*';
		}
		$rs = glob($mode, GLOB_NOSORT);
		if(!count($rs)){
			return array();
		}
		switch($sort){
			case 1:
				sort($rs);
				break;
			case -1:
				rsort($rs);
				break;
			case false:echo 'false';
				return $rs;
		}
		return $rs;
	}

	/**
	 * 列出所有文件夹
	 * @param $dir 目录路径
	 * @param $mode 匹配模式(默认是所有文件夹)
	 * @param $sort 排序(默认是按文件名顺序)
	 * @return 文件夹列表
	 */
	public static function ls_dir($dir, $mode = false, $sort = FILE_ASC){
		chdir($dir);
		if($mode === false){
			$mode = '*';
		}
		$rs = glob($mode, GLOB_ONLYDIR);
		if(!count($rs)){
			return array();
		}
		switch($sort){
			case 1:
				sort($rs);
				break;
			case -1:
				rsort($rs);
				break;
			default:
				return array();
		}
		return $rs;
	}

	/**
	 * 列出指定文件夹内的所有文件
	 * @param $dir 目录路径
	 * @param $mode 匹配模式(默认是文件夹内任意内容)
	 * @param $rs 结果集(引用)
	 * @return array
	 */
	public static function get_files_list($dir, $mode = false, &$rs = array()){
		$list = self::ls($dir, $mode);
		foreach($list as $ls){
			$dir_ = $dir.'/'.$ls;
			if(is_dir($dir_)){
				self::get_files_list($dir_, $mode, $rs);
			} else {
				$rs[] = $dir_;
			}
		}
		return $rs;
	}

	/**
	 * 获取指定文件夹的文件数量
	 * @param $dir 目录路径
	 * @param $mode 匹配模式(默认是文件夹内任意内容)
	 * @return int
	 */
	public static function get_files_num($dir, $mode = false){
		return count(self::get_files_list($dir, $mode));
	}

	/**
	 * 获取指定文件夹的文件总大小
	 * @param $dir 目录路径
	 * @param $mode 匹配模式(默认是文件夹内任意内容)
	 * @return int 单位是字节
	 */
	public static function get_files_size($dir, $mode = false){
		$size = 0;
		$list = self::get_files_list($dir, $mode);
		foreach($list as $ls){
			$size += self::size($ls);
		}
		return $size;
	}

	/**
	 * 读取csv文件
	 * @param $file 目录路径
	 * @return 文件列表数组
	 */
	public static function readcsv($file){
		$data = array();
		$file = fopen($file, 'r');
		while ($tmp = fgetcsv($file)) {
			$data[] = $tmp;
		}
		fclose($file);
		return $data;
	}
}

//可能用到的常量
define('FILE_ASC', 1);
define('FILE_DESC', -1);
?>