<?php
/**
 * 字符串转换类
 */
class conver{
	/**
	 * 简繁转换字符字典
	 */
	private static $j2f_config = array();

	/**
	 * 简繁转换词组字典
	 */
	private static $j2f_words_config = array();

	/**
	 * 简繁转换初始化
	 * @todo 用于导入字典文件
	 */
	public static function init_jf_conver(){
		$file = dirname(__FILE__).'/j2f_config.php';
		if(!file_exists($file)){
			die('the j2f_config is not exists...');
		}
		include $file;
		self::$j2f_config = is_array($cfg_sim_trad) ? $cfg_sim_trad : array();
		self::$j2f_words_config = is_array($words_sim_trad) ? $words_sim_trad : array();
	}

	/**
	 * 简体转换成繁体
	 * @param $str 字符串
	 */
	public static function j2f($str){
		//如果是布尔型或者是数字则忽略
		if(is_bool($str)){
			return $str;
		}

		if(is_numeric($str)){
			return $str;
		}

		if(count(self::$j2f_words_config) <=0 || count(self::$j2f_config) <= 0){
			die('please run conver::init_jf_conver before this function...');
		}
		
		//单个字符
		foreach(self::$j2f_config as $kk => $vv){
			$str = str_replace($kk, $vv, $str);
		}

		//转换词组
		foreach(self::$j2f_words_config as $k => $v){
			$str = str_replace($k, $v, $str);
		}
		return $str;
	}

	/**
	 * 繁体转换成简体
	 * @param $str 字符串
	 */
	public static function f2j($str){
		//如果是布尔型或者是数字则忽略
		if(is_bool($str)){
			return $str;
		}

		if(is_numeric($str)){
			return $str;
		}

		if(count(self::$j2f_words_config) <=0 || count(self::$j2f_config) <= 0){
			die('please run conver::init_jf_conver before this function...');
		}
		
		//单个字符
		foreach(self::$j2f_config as $kk => $vv){
			$str = str_replace($vv, $kk, $str);
		}

		//转换词组
		foreach(self::$j2f_words_config as $k => $v){
			$str = str_replace($v, $k, $str);
		}
		return $str;
	}
}
?>