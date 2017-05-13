<?php

/**
 * 所有Model父类
 */
class model {
	/**
	 * 数据库对象
	 */
	protected static $db = null;

	/**
	 * 初始化数据库
	 * @param $db 数据库对象
	 */
	public static function init_db($db){
		if (!is_object($db)){
			return;
		}
		self::$db = $db;
	}
	
	/**
	 * 获取数据库对象
	 */
	public static function get_db(){
		return self::$db;
	}
}
?>