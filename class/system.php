<?php
/**
 * 系统相关类
 */
class system{
	/**
	 * 获取客户端的操作系统
	 * @return 操作系统名称
	 */
	public static function get_user_os(){
		$agent = $_SERVER['HTTP_USER_AGENT'];
		if (preg_match('/Windows ([A-Z 0-9\.]*)/i', $agent, $regs)) {
			$os = 'Windows';
			switch(trim($regs[1])){
				case 'NT 5.1':
					$ver = 'XP'; break;
				case 'NT 6.1':
					$ver = '7'; break;
				case 'NT 6.2':
					$ver = '8'; break;
				case 'NT 6.3':
					$ver = '8.1'; break;
				case 'NT 6.0':
					$ver = 'Vista'; break;
				case 'NT 5.0':
					$ver = '2000'; break;
				case 'NT 5.2':
					$ver = '2003'; break;
				case '98':
					$ver = '98'; break;
				default:
					return $os;
			}
			return $os.' '.$ver;
		}

		if (stripos($agent, 'win') !== false){
			return 'Windows';
		}

		if (stripos($agent, 'linux')) {
			$os = 'Linux';
		} else if (stripos($agent, 'unix')) {
			$os = 'Unix';
		} else if (stripos($agent, 'Mac')) {
			$os = 'Mac OS';
		} else if (stripos($agent, 'sun') && stripos($agent, 'os')) {
			$os = 'SunOS';
		} else {
			$os = 'Other OS';
		}
		return $os;
	}

	/**
	 * 获取客户端的浏览器
	 * @return 浏览器名称及版本号
	 */
	public static function get_browser(){
		$agent = $_SERVER['HTTP_USER_AGENT'];
		if(preg_match('/MSIE (\d+(?:\.\d+)*)/', $agent, $matchs)) {
			$browser = 'MSIE ' . $matchs[1];
		} else if (preg_match('/Firefox\/(\d+(?:\.\d+)*)/', $agent, $matchs)) {
			$browser = 'Firefox ' . $matchs[1];
		} else if (preg_match('/Chrome\/(\d+(?:\.\d+)*)/', $agent, $matchs)) {
			$browser = 'Chrome ' . $matchs[1];
		} else if (preg_match('/Version\/(\d+(?:\.\d+)*) Safari/', $agent, $matchs)) {
			$browser = 'Safari ' . $matchs[1];
		} else if (preg_match('/Opera\/.*Version\/(\d+(?:\.\d+)*)/', $agent, $matchs)) {
			$browser = 'Opera ' . $matchs[1];
		} else if (preg_match('/Opera\/(\d+(?:\.\d+)*)/', $agent, $matchs)) {
			$browser = 'Opera ' . $matchs[1];
		} else {
			$browser = 'Unknown';
		}
		return $browser;
	}

	/**
	 * 获取客户端的语言版本
	 * @return 客户端语言版本
	 */
	public static function get_language(){
		if (strpos($_SERVER['HTTP_ACCEPT_LANGUAGE'], 'zh') !== false){
			return 'ch-CN';
		} else if (strpos($_SERVER['HTTP_ACCEPT_LANGUAGE'], 'tw') !== false){
			return 'ch-TW';
		} else if (strpos($_SERVER['HTTP_ACCEPT_LANGUAGE'], 'en') !== false){
			return 'Eghlish';
		} else if (strpos($_SERVER['HTTP_ACCEPT_LANGUAGE'], 'ja') !== false){
			return 'Japanese';
		} else if (strpos($_SERVER['HTTP_ACCEPT_LANGUAGE'], 'ko') !== false){
			return 'Korean';
		} else {
			return '';
		}
	}

	/**
	 * 获取目录的磁盘总大小
	 * @param $dir 目录路径(默认当前路径)
	 * @param $flag 是否显示字节数(默认否)
	 * @param $round 精确度(默认小数点后两位)
	 * @return 目录大小
	 */
	public static function get_disk_total($dir = '', $flag = false, $round = 2){
		if(!$dir){
			$dir = dirname(__FILE__);
		}
		$size = disk_total_space($dir);
		if(!$flag){
			return $size;
		}
		return self::size($size, $round);
	}

	/**
	 * 获取目录的磁盘剩余空间大小
	 * @param $dir 目录路径(默认当前路径)
	 * @param $flag 是否显示字节数(默认否)
	 * @param $round 精确度(默认小数点后两位)
	 * @return 目录大小
	 */
	public static function get_disk_free($dir = '', $flag = false, $round = 2){
		if(!$dir){
			$dir = dirname(__FILE__);
		}
		$size = disk_free_space($dir);
		if(!$flag){
			return $size;
		}
		return self::size($size, $round);
	}

	/**
	 * 文件大小换算(32位系统只能到1.96GB)
	 * @param $size 字节数
	 * @param $round 精确度(默认小数点后两位)
	 */
	public static function size($size, $round = 2){
		if($size > 0 && $size < 1024){
			$size .= ' Byte';
		} elseif($size >= 1024 && $size < 1048576){
			$size = round($size / 1024, $round).' KB';
		} elseif($size >= 1048576 && $size < 1073741824){
			$size = round($size / 1048576, $round).' MB';
		} elseif($size >= 1073741824 && $size < 1099511627776){
			$size = round($size / 1073741824, $round).' GB';
		} elseif($size >= 1099511627776 && $size < 1125899906842624){
			$size = round($size / 1099511627776, $round).' TB';
		}
		return $size;
	}
}
?>