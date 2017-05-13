<?php
/**
 * curl类
 */
class curl{
	/**
	 * 执行一个get请求
	 * @param $url 要访问的网址
	 * @param $data 要提交的数据
	 */
	public static function get($url, $data = array()){
		$ch = curl_init();
		self::set_opt($ch);
		curl_setopt($ch, CURLOPT_URL, trim($url).'?'.http_build_query($data));
		$return = curl_exec($ch);
		curl_close($ch);
		return $return;
	}
	
	/**
	 * 执行一个post请求
	 * @param $url 要访问的网址
	 * @param $data 要提交的数据
	 */
	public static function post($url, $data = array()){
		$ch = curl_init();
		self::set_opt($ch);
		curl_setopt($ch, CURLOPT_URL, trim($url));
		curl_setopt($ch, CURLOPT_POST, 1);
		if(!empty($data)) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
		$return = curl_exec($ch);
		curl_close($ch);
		return $return;
	}
	
	/**
	 * 设置一些公共的参数
	 * @param $ch 
	 */
	private static function set_opt(&$ch){
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
	}
}
?>