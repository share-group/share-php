<?php
/**
 * 文件上传类
 */
class upload{
	/**
	 * 文件上传信息数组
	 */
	private static $upload_arr = null;
	/**
	 * 是否可覆盖文件
	 */
	private static $can_cover = false;
	/**
	 * 当文件最大上限
	 */
	private static $file_max_size = 0;
	/**
	 * 默认可上传的文件类型
	 */
	private static $file_type = 'jpg|jpeg|gif|png|mp3|mp4|wma';

	/**
	 * 上传单个文件
	 * @param $input_name 文件上传域的id名称
	 * @param $dir 文件要上传的文件夹
	 * @param $name 文件名
	 * @return 上传后保存在服务器的文件名
	 */
	public static function upload_single($input_name, $dir, $name = null){
		self::init($input_name);
		$dir .= '/';
		self::mkdir($dir);
		$file_arr = explode('.', self::$upload_arr['name']);
		$filetype = $file_arr[count($file_arr)-1];

		//文件类型检查
		$file_type_arr = explode('|', self::$file_type);
		if(!in_array($filetype, $file_type_arr)){
			die(self::$upload_arr['name'].' is can\'t upload to server...');
		}

		if(!$name){
			$name = date('YmdHis').'.'.$filetype;
		} else {
			$name .= '.'.$filetype;
		}
		$file = $dir.$name;

		//如果不可以覆盖文件，检查文件是否存在
		if(!self::$can_cover && file_exists($file)){
			die('file '.self::$upload_arr['name'].' upload failure...');
		}

		//检查上传文件大小
		if(intval(self::$upload_arr['size']) > self::$file_max_size){
			die('file '.self::$upload_arr['name'].' is too large...');
		}
		
		move_uploaded_file(self::$upload_arr['tmp_name'], $file) or die('upload error...');
		return $name;
	}

	/**
	 * 上传多个文件
	 * @param $input_name 文件上传域的id名称
	 * @param $dir 文件要上传的文件夹
	 * @param $name 文件名
	 * @return 上传后保存在服务器的文件名列表
	 */
	public static function upload_multi($input_name, $dir, $name = null){
		self::init($input_name);
		self::mkdir($dir);
		$dir .= '/';
		$i = 0;
		$filelist = array();
		$file_type_arr = explode('|', self::$file_type);
		foreach (self::$upload_arr['name'] as $key => $file){
			if(self::$upload_arr['tmp_name'][$key] && self::$upload_arr['name'][$key] && self::$upload_arr['type'][$key]){
				$file_arr = explode('.', self::$upload_arr['name'][$key]);
				$filetype = $file_arr[count($file_arr)-1];
				if($name){
					$filelist[$i] = $filename = $name.($i+1).'.'.$filetype;
						
					//文件类型检查
					if(!in_array($filetype, $file_type_arr)){
						$filelist[$i] = self::$upload_arr['name'][$key].' is can\'t upload to server...';
					}

					//如果不可以覆盖文件，检查文件是否存在
					if(!self::$can_cover && file_exists($dir.$filename)){
						$filelist[$i] = 'file '.self::$upload_arr['name'][$key].' upload failure...';
					}

					//检查上传文件大小
					if(intval(self::$upload_arr['size'][$key]) > self::$file_max_size){
						$filelist[$i] = 'file '.self::$upload_arr['name'][$key].' is too large...';
					}
				} else {
					$filelist[$i] = $filename = date('YmdHis').'_'.($i+1).'.'.$filetype;
				}

				move_uploaded_file(self::$upload_arr['tmp_name'][$key], $dir.$filename) or die('upload error...');
				$i += 1;
			}
		}
		return $filelist;
	}

	/**
	 * 设置可以上传的文件大小
	 * @param $file_type (type1|type2|type3|...)
	 */
	public static function set_file_type($file_type){
		self::$file_type = trim($file_type);
	}

	/**
	 * 设置单个文件上传最大上限
	 * @param $size 文件大小(-1为不限大小)
	 */
	public static function set_file_max_size($size){
		self::$file_max_size = intval($size);
	}

	/**
	 * 上次初始化检查
	 * @param $input_name 文件上传域的id名称
	 */
	private static function init($input_name){
		self::$upload_arr = $GLOBALS['_FILES'][$input_name];
		if(!is_array(self::$upload_arr)){
			die('upload failure, please check your server...');
		}

		//如果没有设置文件上传大小，就使用php默认的
		if(self::$file_max_size === 0){
			//一般都配置M的，没人配置G吧
			$php_upload_max_filesize = intval(ini_get('upload_max_filesize')) * 1048576;
			$php_post_max_size = intval(ini_get('post_max_size')) * 1048576;
			self::$file_max_size = $php_upload_max_filesize > $php_post_max_size ? $php_post_max_size: $php_upload_max_filesize;
		}
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
}
?>