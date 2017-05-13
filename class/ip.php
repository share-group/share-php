<?php
/**
 * ip地址相关
 */
class ip{

	/**
	 * ip地址转化成整数
	 * @param $ip ip地址
	 * @return 该ip对应的整数
	 */
	public static function ip_to_int($ip){
		$array = explode('.', $ip);
		return ($array[0] * 256 * 256 * 256) + ($array[1] * 256 * 256) + ($array[2] * 256) + $array[3];
	}

	/**
	 * 整数转化成ip地址
	 * @param $int 整数
	 * @return 对应的ip地址
	 */
	public static function int_to_ip($int){
		$b1 = ($int & 0xff000000) >> 24;
		if($b1 < 0) {
			$b1 += 0x100;
		}
		$b2 = ($int & 0x00ff0000) >> 16;
		if($b2 < 0){
			$b2 += 0x100;
		}
		$b3 = ($int & 0x0000ff00) >> 8;
		if($b3 < 0){
			$b3 += 0x100;
		}
		$b4 = $int & 0x000000ff;
		if($b4 < 0){
			$b4 += 0x100;
		}
		$ip = $b1.'.'.$b2.'.'.$b3.'.'.$b4;
		return $ip;
	}

	/**
	 * ip地址转化成长整型
	 * @param $ip ip地址
	 * @return 该ip对应的长整型
	 */
	public static function ip_to_long($ip){
		return intval(ip2long($ip));
	}

	/**
	 * 长整型转化成ip地址
	 * @param $long 长整型
	 * @return 对应的ip地址
	 */
	public static function long_to_ip($long){
		return long2ip($long);
	}

	/**
	 * 获取客户端真实 ip地址
	 * @return 用户的真实ip地址
	 */
	public static function get_user_ip(){
		static $realip;
		if (isset($_SERVER)){
			if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
				$realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
			} else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
				$realip = $_SERVER["HTTP_CLIENT_IP"];
			} else {
				$realip = $_SERVER["REMOTE_ADDR"];
			}
		} else {
			if (getenv("HTTP_X_FORWARDED_FOR")){
				$realip = getenv("HTTP_X_FORWARDED_FOR");
			} else if (getenv("HTTP_CLIENT_IP")) {
				$realip = getenv("HTTP_CLIENT_IP");
			} else {
				$realip = getenv("REMOTE_ADDR");
			}
		}
		return $realip;
	}

	/**
	 * 获取IP所在的地理位置
	 * @param $ip ip地址
	 */
	public static function get_location($ip){
		$url = 'http://ip.taobao.com/service/getIpInfo.php?ip='.$ip;
		//$ip = json_decode(file_get_contents($url));

		$ch = curl_init();
		// 设置URL和相应的选项
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		// 抓取URL并把它传递给浏览器
		echo curl_exec($ch);

		// 关闭cURL资源，并且释放系统资源
		curl_close($ch);

		if((string)$ip->code=='1'){
			return false;
		}
		$data = (array)$ip->data;
		return $data;
	}
}
?>