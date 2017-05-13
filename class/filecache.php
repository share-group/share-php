<?php
/**
 * 文件缓存
 */
class filecache{
	/**
	 * 缓存地址(默认为调用文件所在的目录)
	 */
	private static $cache_dir = null;

	/**
	 * 缓存时间(默认不限时)
	 */
	private static $cache_life_time = 0;

	/**
	 * 设置缓存生存时间
	 * @param $time 生存时间
	 */
	public static function set_life_time($time){
		self::$cache_life_time = intval($time);
	}

	/**
	 * 获取缓存生存时间
	 */
	public static function get_life_time(){
		return self::$cache_life_time;
	}

	/**
	 * 设置缓存地址(如输入的是非法数据，则使用默认设置)
	 * @param $dir 缓存地址
	 */
	public static function set_dir($dir){
		$dir = trim($dir);
		if(!$dir){
			return;
		}
		self::$cache_dir = $dir.'/';
	}

	/**
	 * 获取缓存地址
	 */
	public static function get_dir(){
		return self::$cache_dir;
	}

	/**
	 * 写入缓存
	 * @param $filename 缓存文件名
	 * @param $data 数据
	 * @return boolean
	 */
	public static function save($filename, $data){
		if(!$filename){
			die('the cache filename must be input...');
		}
		$filename = self::$cache_dir.$filename;
		$dir = null;
		if(!file_exists($filename)){
			$dir = dirname($filename);
			$filename = $dir.'/'.basename($filename);
			self::mkdir($dir);
		} elseif(!is_writable($filename)) {
			die($dir.' is unwritable, please check your server...');
		}
		$rs = self::write($filename, json_encode($data));
		return $rs > 0 ? true : false;
	}

	/**
	 * 读取缓存
	 * @param $filename 缓存文件名
	 * @return array
	 */
	public static function get($filename){
		if(!$filename){
			die('the cache filename must be input...');
		}
		$filename = self::$cache_dir.$filename;
		//文件不存在或者过期，返回一个空数组
		if(!file_exists($filename)){
			return array();
		}
		return json_decode(trim(file_get_contents($filename)), true);
	}

	/**
	 * 创建文件夹
	 * @param $dir 文件夹路径
	 * @param $mode 模式(默认0777)
	 * @param $recursive 是否递归创建(默认true)
	 */
	private static function mkdir($dir, $mode = 0777, $recursive = true){
		if(file_exists($dir)){
			return;
		}
		mkdir($dir, $mode, $recursive) or die('create dir '.$dir.' error...');
	}

	/**
	 * 写入文件
	 * @param $flie 文件路径
	 * @param $data 写入的数据
	 * @return 写入到文件内数据的字节数
	 */
	private static function write($file, $data){
		if(!file_exists($file)){
			$dir = dirname($file);
			$file = $dir.'/'.basename($file);
			self::mkdir($dir);
		} elseif(!is_writable($file)) {
			return 0;
		}
		return file_put_contents($file, $data, LOCK_EX);
	}
}
?>