<?php


/**
 * 时间类
 */
class time {
	/**
	 * 获取当前日期是一年是的第几周
	 * @return 返回格式 201226
	 */
	public static function get_year_week() {
		$w = date('W');
		$y = date('Y');
		$m = date('m');
		if ($w >= 5 && $m <= 1) {
			$y -= 1;
		}
		if ($w == 1 && $m == 12) {
			$y += 1;
		}
		return $y . $w;
	}

	/**
	 * 获取某个日期是一周的星期几(默认是当前时间)
	 * @param $date 日期
	 * <p>支持的格式：
	 * <br>YYYY-mm-dd
	 * <br>YYYY.mm.dd
	 * <br>YYYY/mm/dd
	 * @param $format 返回格式
	 * <p>可选格式：
	 * <br>WEEK_DAY_ENG 英文表示的星期几(例如：Sunday)
	 * <br>WEEK_DAY_NUM 数字表示的星期中的第几天[1(表示星期一)到 7(表示星期天)]
	 * <br>WEEK_DAY_CHN 中文表示的星期几(例如：星期一)
	 * @return 星期几
	 */
	public static function get_week_day($date = '', $format = WEEK_DAY_ENG) {
		if ($date) {
			$date = str_replace(':', '-', $date);
			$date = str_replace(' ', '-', $date);
		}
		elseif (!preg_match('/^(\d{1,4})(\-|\/|\.|\:)(0[1-9]|1[0-2])(\-|\/|\.|\:)(0[1-9]|[12][0-9]|3[01])$/', $date)) {
			$date = date('Y-m-d-H-i-s');
		}
		$dateArr = explode('-', $date);
		if ($format === 'chn') {
			$week = date('N', mktime($dateArr[3], $dateArr[4], $dateArr[5], $dateArr[1], $dateArr[2], $dateArr[0]));
			switch ($week) {
				case 1 :
					$week = '星期一';
					break;
				case 2 :
					$week = '星期二';
					break;
				case 3 :
					$week = '星期三';
					break;
				case 4 :
					$week = '星期四';
					break;
				case 5 :
					$week = '星期五';
					break;
				case 6 :
					$week = '星期六';
					break;
				case 7 :
					$week = '星期七';
					break;
				default :
					break;
			}
			return $week;
		}
		return date($format, mktime($dateArr[3], $dateArr[4], $dateArr[5], $dateArr[1], $dateArr[2], $dateArr[0]));
	}

	/**
	 * 获取某个时间戳是一周的星期几(默认是当前时间)
	 * @param $timestamp 时间戳
	 * @param $format 返回格式
	 * <p>可选格式：
	 * <br>WEEK_DAY_ENG 英文表示的星期几(例如：Sunday)
	 * <br>WEEK_DAY_NUM 数字表示的星期中的第几天[1(表示星期一)到 7(表示星期天)]
	 * <br>WEEK_DAY_CHN 中文表示的星期几(例如：星期一)
	 * @return 星期几
	 */
	public static function get_week_day_by_timestamp($timestamp = 0, $format = WEEK_DAY_ENG) {
		if ($timestamp <= 0) {
			$timestamp = $_SERVER['REQUEST_TIME'];
		}
		return self :: get_week_day(date('Y-m-d-H-i-s', $timestamp), $format);
	}

	/**
	 * 获取指定时间与当前时间的距离
	 * @param $time 时间(可以是时间戳或者是日期格式)
	 * @return xxx分钟前，xxx小时前，xxx天前
	 */
	public static function get_day_before($time) {
		if (!is_numeric($time)) {
			$time = strtotime($time);
		}
		$left_time = $_SERVER['REQUEST_TIME'] - $time;
		$d = floor($left_time / 86400);
		$left_time = $left_time - $d * 86400;
		$h = floor($left_time / 3600);
		$left_time = $left_time - $h * 3600;
		$m = floor($left_time / 60);
		$s = $left_time - $m * 60;
		$str = '';
		if($d > 1){
			return date('Y-m-d H:i:s', $time);
		}
		if ($h > 0) {
			$str .= $h . '小时';
		}
		if ($m > 0) {
			$str .= $m . '分';
		}
		if ($s > 0) {
			$str .= $s . '秒';
		}
		if(!$str){
			return '刚刚';
		}
		$str .= '前';
		return $str;
	}

	/**
	 * 获取某一天凌晨的时间戳
	 * @param $date 日期(默认是今天凌晨)，格式是Y-m-d或者是Y-m-d H:i:s
	 */
	public static function day_break($date = null) {
		if (!$date) {
			$date = date('Y-m-d');
		}
		return strtotime($date);
	}

	/**
	 * 计算两个日期之间相隔的天数
	 * @param $t1
	 * @param $t2
	 * @return 两个日期之间相隔的天数
	 */
	public static function diff($t1, $t2) {
		return ceil(abs(strtotime($t1) - strtotime($t2)) / 86400);
	}
}

//可能用到的常量
define('WEEK_DAY_ENG', 'l');
define('WEEK_DAY_NUM', 'N');
define('WEEK_DAY_CHN', 'chn');
?>