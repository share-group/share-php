<?php
/**
 * session类
 */
class session{
	/**
	 * 构造函数
	 */
	private static function constructor(){
		session_start();
	}

	/**
	 * 写入session
	 * @param $key 键
	 * @param $value 值
	 */
	public static function set($key, $value){
		if($value){
			$GLOBALS['_SESSION'][$key] = trim($value);
		} else {
			return $GLOBALS['_SESSION'][$key];
		}
	}

	/**
	 * 获取session值
	 * @param $key 键
	 */
	public static function get($key){
		return trim($GLOBALS['_SESSION'][$key]);
	}
	

	/**
	 * 删除session
	 * @param $key 键(无限个key)
	 */
	public static function delete($key){
		$param_arr = func_get_args();
		foreach ($param_arr as $param){
			if(!$param){
				continue;
			}
			unset($GLOBALS['_SESSION'][$param]);
		}
	}
	
	/**
	 * 打印所有session
	 */
	public static function show(){
		echo_($GLOBALS['_SESSION']);
	}
}
?>