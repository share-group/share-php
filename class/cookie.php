<?php
/**
 * cookie类
 */
class cookie{
	/**
	 * 写入cookie
	 * @param $key 键
	 * @param $value 值
	 * @param $time cookie有效期
	 * @param $path cookie的服务器路径
	 * @param $domain cookie的域名
	 * @param $secure 是否通过安全的 HTTPS连接来传输 cookie
	 * @param $httponly 是否允许HTTP连接池传输cookie
	 */
	public static function set($key, $value, $time = 0, $path = '/', $domain = null, $secure = false, $httponly = false){
		if(!$key && !$value){
			return;
		}
		if(is_array($value)){
			foreach($value as $k => $v){
				setcookie($k, $v, $time, $path, $domain, $secure);
			}
		} else {
			setcookie($key, $value, $time, $path, $domain, $secure);
		}
	}

	/**
	 * 获取cookie
	 * @param $key 键
	 */
	public static function get($key){
		return trim($GLOBALS['_COOKIE'][$key]);
	}

	/**
	 * 删除cookie
	 * @param $key 键(无限个key)
	 */
	public static function delete($key){
		$param_arr = func_get_args();
		foreach ($param_arr as $param){
			if(!$param){
				continue;
			}
			self::set($param, '', $_SERVER['REQUEST_TIME'] - 86400);
		}
	}

	/**
	 * 打印所有cookie
	 */
	public static function show(){
		echo_($GLOBALS['_COOKIE']);
	}
}
?>