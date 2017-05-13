<?php
/**
 * 字符串处理类
 */
class string{
	/**
	 * 过滤字符串
	 * @param $str 字符串
	 * @return 过滤好的字符串
	 */
	public static function filter($str){
		if(!$str){
			return;
		}
		if(is_array($str)) {
			foreach ($str as $key => $s) {
				$str[$key] = self::fliter($s);
			}
		} else {
			$str = htmlspecialchars($str);
			$str = strip_tags($str);
			$str = trim($str);
		}
		return $str;
	}

	/**
	 * 解除字符串过滤
	 * @param $str 待解除的字符串
	 * @return 解除过滤的字符串
	 */
	public static function unfilter($str){
		if(!$str){
			return;
		}
		if(is_array($str)) {
			foreach ($str as $key => $s) {
				$str[$key] = self::unfliter($s);
			}
		} else {
			$str = htmlspecialchars_decode($str);
			$str = strip_tags($str);
			$str = trim($str);
		}
		return $str;
	}

	/**
	 * 防XSS注入
	 * @param $str
	 */
	public static function xss($str){
		if (is_array($str)){
			foreach($str as $k => $v){
				$str[$k] = crack_xss($v);
			}
		} else {
			$farr = array(
        '/\\s+/',
        '/<(\\/?)(script|i?frame|style|html|body|title|link|meta|object|\\?|\\%)([^>]*?)>/isU',
        '/(<[^>]*)on[a-zA-Z]+\s*=([^>]*>)/isU',
			);
			$str = preg_replace($farr,'',$str);
			$str = addslashes($str);
		}
		return trim($str);
	}

	/**
	 * 随机生成字符串
	 * @param $length 长度
	 * @return 指定长度的随机字符串
	 */
	public static function rand_str($length) {
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$password = '';
		$len =  strlen($chars);
		for ($i = 0; $i < $length; $i++){
			$password .= $chars[mt_rand(0, $len - 1)];
		}
		return $password;
	}

	/**
	 * 判断字符串$string的最后一位是不是$value
	 * @param $string
	 * @param $value
	 */
	public static function last_is($string, $value){
		return $string{strlen($string) - 1} === $value;
	}
}