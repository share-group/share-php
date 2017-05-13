<?php
/**
 * 非法字过滤
 */
class illegalwords{	
	/**
	 * 检查输入的字符串是否含有非法字
	 * @param $illegalwords 非法字符词库(数组)
	 * @param $str 字符串
	 * @return true合法，false不合法
	 */
	public static function check($illegalwords, $str){
		if(!$str){
			return true;
		}
		if(!is_array($illegalwords) || count($illegalwords) <= 0){
			die('illegal params : '.print_r($illegalwords, true));
		}
		foreach(self::$illegalwords as $i){
			if(preg_match('/^(?i)'.$i.'$/', $str) > 0){
				return false;
			}
		}
		return true;
	}
}
?>