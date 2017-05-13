<?php
/**
 * 加密类
 */
class secret{
	/**
	 * crypt加密
	 * @param $value 要加密的字符串
	 * @param $key 加密的key
	 * @param $action ENCODE加密(默认)，DECODE解密
	 */
	public static function crypt($value, $key, $action = ENCODE){
		switch($action){
			case 'encode':
				$text = urlencode($value);
				$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
				$iv = mcrypt_create_iv($iv_size,MCRYPT_RAND);
				$crypttext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $text,MCRYPT_MODE_ECB, $iv);
				$str = base64_encode($crypttext);
				break;

			case 'decode':
				$crypttext = base64_decode($value);
				$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
				$iv = mcrypt_create_iv($iv_size,MCRYPT_RAND);
				$decrypttext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $crypttext, MCRYPT_MODE_ECB, $iv);
				$str = urldecode($decrypttext);
				break;
		}
		return trim($str);
	}

	/**
	 * DZ的加密解密函数(不稳定)
	 * @param $txt 要加密的字符串
	 * @param $key 加密的key
	 * @param $action ENCODE加密(默认)，DECODE解密
	 */
	public static function dz($txt, $key, $action = ENCODE){
		switch($action){
			case'encode':
				$encrypt_key = md5(rand(0, 32000));
				$ctr = 0;
				$tmp = '';
				$len = strlen($txt);
				for($i = 0; $i < $len; $i++){
					$ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
					$tmp.= $encrypt_key[$ctr].($txt[$i] ^ $encrypt_key[$ctr++]);
				}
				return base64_encode(self::p($tmp, $key));

			case'decode':
				$txt = self::p(base64_decode($txt), $key);
				$tmp = '';
				$len = strlen($txt);
				for($i = 0; $i < $len; $i++){
					$tmp .= $txt[$i] ^ $txt[++$i];
				}
				return $tmp;
		}
	}

	/**
	 * DZ的加密解密函数辅助函数
	 * @param $txt 要加密的字符串
	 * @param $encrypt_key key
	 */
	private static function p($txt, $encrypt_key){
		$encrypt_key = md5($encrypt_key);
		$ctr = 0;
		$tmp = '';
		$len = strlen($txt);
		for($i = 0; $i < $len; $i++){
			$ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
			$tmp.= $txt[$i] ^ $encrypt_key[$ctr++];
		}
		return $tmp;
	}

	/**
	 * PHP168的加密解密函数(不支持中文)
	 * @param $string 要加密的字符串
	 * @param $action ENCODE加密(默认)，DECODE解密
	 */
	public static function php168($string, $action = ENCODE){
		$secret_string='5j,.^&;?.%#@!';
		if($string == ''){
			return $string;
		}
		if($action == 'encode'){
			$md5code = substr(md5($string), 8, 10);
		} else {
			$md5code = substr($string, -10);
			$string = substr($string, 0, strlen($string) - 10);
		}
		$key = md5($md5code.$secret_string);
		$string = ($action=='encode' ? $string : base64_decode($string));
		$len = strlen($key);
		$code = '';
		for($i = 0; $i < strlen($string); $i++){
			$k = $i % $len;
			$code .= $string[$i] ^ $key[$k];
		}
		$code = ($action == 'decode' ? (substr(md5($code), 8, 10) == $md5code ? $code : NULL) : base64_encode($code).$md5code);
		return $code;
	}

	/**
	 * dede的加密函数
	 * @param $string 要加密的字符串
	 * @param $key 加密的key
	 * @param $operation ENCODE加密(默认)，DECODE解密
	 */
	public static function dede($string, $key, $operation = ENCODE){
		$key = md5($key);
		$key_length = strlen($key);
		$string = $operation == 'decode' ? base64_decode($string) : substr(md5($string.$key), 0, 8).$string;
		$string_length = strlen($string);
		$rndkey = $box = array();
		$result = '';
		for($i = 0; $i <= 255; $i++){
			$rndkey[$i] = ord($key[$i % $key_length]);
			$box[$i] = $i;
		}
		for($j = $i = 0; $i < 256; $i++){
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}
		for($a = $j = $i = 0; $i < $string_length; $i++){
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}
		if($operation == 'decode'){
			if(substr($result, 0, 8) == substr(md5(substr($result, 8).$key), 0, 8)){
				return substr($result,8);
			} else {
				return '';
			}
		} else {
			return str_replace('=', '', base64_encode($result));
		}
	}

	/**
	 * dz可以设置指定时间的加密函数，超时无法还原
	 * @param $string 要加密的字符串
	 * @param $key 加密的key
	 * @param $operation ENCODE加密(默认)，DECODE解密
	 * @param $expiry 过期时间(默认0，不限时)
	 */
	public static function authcode($string, $key, $operation = ENCODE, $expiry = 0){
		$ckey_length = 4;
		$key = md5($key);
		$keya = md5(substr($key, 0, 16));
		$keyb = md5(substr($key, 16, 16));
		$keyc = $ckey_length ? ($operation == 'decode' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
		$cryptkey  =$keya.md5($keya.$keyc);
		$key_length = strlen($cryptkey);
		$string = $operation == 'decode' ? base64_decode(substr($string,$ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
		$string_length = strlen($string);
		$result = '';
		$box = range(0, 255);
		$rndkey = array();
		for($i = 0; $i <= 255; $i++){
			$rndkey[$i] = ord($cryptkey[$i%$key_length]);
		}
		for($j = $i = 0; $i < 256; $i++){
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}
		for($a = $j = $i = 0; $i < $string_length; $i++){
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
		}
		if($operation == 'decode'){
			if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)){
				return substr($result, 26);
			} else {
				return '';
			}
		} else {
			return $keyc.str_replace('=', '', base64_encode($result));
		}
	}
}

//常量定义
define('ENCODE', 'encode');
define('DECODE', 'decode');
?>