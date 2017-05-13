<?php


/**
 * 格式检查
 */
class check {
	/**
	 * 检查数字是否为正数
	 * @param $int 数字
	 * @return boolean
	 */
	public static function is_unsigned($int){
		return floatval($int) > 0;
	}

	/**
	 * 检查数字是否为负数
	 * @param $int 数字
	 * @return boolean
	 */
	public static function is_not_unsigned($int){
		return floatval($int) < 0;
	}

	/**
	 * 检查输入的是否为电话
	 * @param $number 电话号码
	 * @return boolean
	 */
	public static function is_phone($number){
		return preg_match('/^((0\d{2,3})-)?(\d{7,8})(-?(\d{0,}))$/', $number);
	}

	/**
	 * 检查输入的是否为手机号
	 * @param $number 手机号码
	 * @return boolean
	 */
	public static function is_mobile($number){
		//alert(/(0|86)?(13|15|18)[0-9]{9}$/.test(13712345678));
		return preg_match('/^(1[3458]\d{9})$/', $number);
	}

	/**
	 * 检查输入的是否为邮编
	 * @param $postcode 邮政编码
	 * @return boolean
	 */
	public static function is_postcode($postcode){
		return preg_match('/^[1-9]{4,6}$/', $postcode);
	}

	/**
	 * 邮箱地址合法性检查
	 * @param $email 邮箱地址
	 * @return boolean
	 */
	public static function is_email($email){
		return preg_match('/^\w+[@]\w+([.][a-zA-Z]+)+$/', $email);
	}

	/**
	 * 姓名昵称合法性检查，只能输入中文英文数字
	 * @param $name 姓名昵称
	 * @return boolean
	 */
	public static function is_name($name){
		return preg_match('/^[\x80-\xffa-zA-Z0-9]+$/', $name);
	}

	/**
	 * 检查输入是否为英文
	 * @param $english 英文字符串
	 * @return boolean
	 */
	public static function is_english($english){
		return preg_match('/^[a-zA-Z]+$/', $english);
	}

	/**
	 * 检查输入是否全为大写字母
	 * @param $str 字符串
	 * @return boolean
	 */
	public static function is_upper($str){
		return preg_match('/^[A-Z]+$/', $str);
	}

	/**
	 * 检查输入是否全为小写字母
	 * @param $str 字符串
	 * @return boolean
	 */
	public static function is_lower($str){
		return preg_match('/^[a-z]+$/', $str);
	}

	/**
	 * 检查是否输入为汉字
	 * @param $chinese 中文字符串
	 * @param $charset 字符集(默认utf8)
	 * <p>可选项：
	 * <br>CHARSET_UTF8
	 * <br>CHARSET_GB2312
	 * <br>CHARSET_GBK
	 * @return boolean
	 */
	public static function is_chinese($chinese, $charset = CHARSET_UTF8){
		switch ($charset){
			case 'utf8' :
				$preg_str = '/^[\x{4e00}-\x{9fa5}]+$/u';
				break;

			case 'gb2312' :
				$preg_str = '/^['.chr(0xa1).'-'.chr(0xff).']+$/';
				break;

			case 'gbk' :
				$preg_str = '/^['.chr(0x80).'-'.chr(0xff).']+$/';
				break;
		}
		return preg_match($preg_str, $chinese);
	}

	/**
	 * 返回检查日期用的分隔符
	 * @return string
	 */
	private static function get_delimiter(){
		return '(\-|\/|\.|\:)';
	}

	/**
	 * 检查是否符合日期格式
	 * <p>支持的格式：
	 * <br>Y-m-d <br>Y.m.d <br>Y/m/d
	 * @param $date 日期
	 * @return boolean
	 */
	public static function is_date($date){
		$delimiter = self::get_delimiter();
		return preg_match('/^(\d{1,4})'.$delimiter.'(0[1-9]|1[0-2])'.$delimiter.'(0[1-9]|[12][0-9]|3[01])$/', $date);
	}

	/**
	 * 检查是否符合时间格式
	 * <p>支持的格式：
	 * <br>H:i:s <br>H/i/s
	 * @param $time 时间
	 * @return boolean
	 */
	public static function is_time($time){
		$delimiter = self::get_delimiter();
		return preg_match('/^([01][0-9]|2[0-3])'.$delimiter.'[0-5][0-9]'.$delimiter.'[0-5][0-9]$/', $time);
	}

	/**
	 * 检查是否符合日期时间格式
	 * <p>支持的格式：
	 * <br>Y-m-d H:i:s <br>Y.m.d H:i:s <br>Y/m/d H:i:s
	 * @param $datetime 日期时间
	 * @return boolean
	 */
	public static function is_datetime($datetime){
		$delimiter = self::get_delimiter();
		return preg_match('/^(\d{1,4})'.$delimiter.'(0[1-9]|1[0-2])'.$delimiter.'(0[1-9]|[12][0-9]|3[01])( [0-9][0-3]'.$delimiter.'[0-5][0-9]'.$delimiter.'[0-5][0-9])?$/', $datetime);
	}

	/**
	 * 检查输入IP是否符合要求
	 * @param $ip IP地址
	 * @return boolean
	 */
	public static function is_ip($ip){
		return (bool) ip2long($ip);
	}

	/**
	 * 检查输入url地址是否符合要求
	 * @param $url url地址
	 * @return boolean
	 */
	public static function is_url($url){
		$exp_match = '/^((http:|https:)\/\/)?[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\'\'])*$/';
		return preg_match($exp_match, $url);
	}

	/**
	 * 检查输入的号码是否为QQ号码
	 * @param $qq QQ号码
	 * @return boolean
	 */
	public static function is_qq($qq){
		return preg_match('/^[1-9]\d{4,9}$/', $qq);
	}

	/**
	 * 检查输入的号码是否为身份证号码
	 * @param $id 身份证号码
	 * @return boolean
	 */
	public static function is_id($id){
		return preg_match('/^(\d{15})|(\d{18})$/', $id);
	}

	/**
	 * 检查是否为成年人
	 * @param $birthday 生日日期(可以是时间戳或者是日期格式)
	 * @return boolean
	 */
	public static function is_adult($birthday){
		if (!is_numeric($birthday)){
			$birthday = strtotime($birthday);
		}
		$rs = $_SERVER['REQUEST_TIME'] - $birthday - 567648000; //现在的时间戳 - 出生时候的时间戳 > 18年
		return self::is_unsigned($rs);
	}

	/**
	 * 检查是否为utf8字符串
	 * @param $str
	 */
	public static function is_utf8($str){
		return preg_match('/^(['.chr(228).'-'.chr(233).']{1}['.chr(128).'-'.chr(191).']{1}['.chr(128).'-'.chr(191).']{1}){1}/', $str) == true || preg_match('/(['.chr(228).'-'.chr(233).']{1}['.chr(128).'-'.chr(191).']{1}['.chr(128).'-'.chr(191).']{1}){1}$/', $str) == true || preg_match('/(['.chr(228).'-'.chr(233).']{1}['.chr(128).'-'.chr(191).']{1}['.chr(128).'-'.chr(191).']{1}){2,}/', $str);
	}

	/**
	 * 检查是否为物理地址
	 * @param $str
	 */
	public static function is_mac($str){
		return preg_match('/^([A-Z0-9]{2}:){5}[A-Z0-9]{2}$/', $str);
	}
	
	/**
	 * 判断字符串是否为uuid格式
	 * @param $str
	 */
	public static function is_uuid($str){
		return preg_match('/^[a-fA-F\d]{8}(-[a-fA-F\d]{4}){3}-[a-fA-F\d]{12}$/', $str);
	}
}

//可能用到的常量
define('CHARSET_UTF8', 'uft8');
define('CHARSET_GB2312', 'gb2312');
define('CHARSET_GBK', 'gbk');
?>