<?php
/**
 * qqwry纯真IP地址数据库
 */
class qqwry{
	private static $StartIp = 0;
	private static $EndIp   = 0;
	private static $Country = '';
	private static $Local   = '';
	private static $CountryFlag = 0;
	private static $fp;
	private static $FirstStartIp = 0;
	private static $LastStartIp = 0;
	private static $EndIpOff = 0;

	private static function getStartIp($RecNo){
		$offset = self::$FirstStartIp + $RecNo * 7;
		fseek(self::$fp, $offset, SEEK_SET);
		$buf = fread(self::$fp, 7);
		self::$EndIpOff = ord($buf[4]) + (ord($buf[5]) * 256) + (ord($buf[6]) * 256 * 256);
		self::$StartIp = ord($buf[0]) + (ord($buf[1]) * 256) + (ord($buf[2]) * 256 * 256) + (ord($buf[3]) * 256 * 256 * 256);
		return self::$StartIp;
	}
	
	private static function getEndIp(){
		fseek( self::$fp ,self::$EndIpOff , SEEK_SET);
		$buf = fread(self::$fp, 5);
		self::$EndIp = ord($buf[0]) + (ord($buf[1]) * 256) + (ord($buf[2]) * 256 * 256) + (ord($buf[3]) * 256 * 256 * 256);
		self::$CountryFlag = ord ($buf[4]);
		return self::$EndIp;
	}
	
	private static function getCountry(){
		switch(self::$CountryFlag){
			case 1:
			case 2:
				self::$Country = self::getFlagStr(self::$EndIpOff + 4);
				self::$Local = (1 == self::$CountryFlag) ? '' : self::getFlagStr(self::$EndIpOff + 8);
				break;
			default :
				self::$Country = self::getFlagStr(self::$EndIpOff + 4);
				self::$Local =   self::getFlagStr(ftell(self::$fp));
		}
	}

	private static function getFlagStr($offset){
		while(1){
			fseek(self::$fp ,$offset ,SEEK_SET);
			$flag = ord(fgetc(self::$fp));
			if($flag == 1 || $flag == 2){
				$buf = fread(self::$fp, 3);
				if($flag == 2){
					self::$CountryFlag = 2;
					self::$EndIpOff = $offset - 4;
				}
				$offset = ord($buf[0]) + (ord($buf[1]) * 256) + (ord($buf[2]) * 256 * 256);
			} else {
				break;
			}
		}
		if ($offset < 12){
			return '';
		}
		fseek(self::$fp, $offset, SEEK_SET);
		return self::getStr();
	}
	
	private static function getStr(){
		while(1){
			$c = fgetc(self::$fp);
			if(ord($c[0]) == 0){
				break;
			}
			$str .= $c;
		}
		return $str;
	}

	private static function IpToInt($Ip){
		$array = explode('.', $Ip);
		$Int = ($array[0] * 256 * 256 * 256) + ($array[1] * 256 * 256) + ($array[2] * 256) + $array[3];
		return $Int;
	}
	
	/**
	 * 设置qqwry文件的路径
	 * @param $qqwry qqwry文件的路径
	 */
	public static function setqqwry($qqwry){
		self::$fp = fopen($qqwry, 'rb') or die('can not find qqwry.dat...');
	}

	/**
	 * 获取ip对应的地区
	 * @param $ip ip
	 */
	public static function get($ip){
		$ip = self::IpToInt($ip);
		if(self::$fp == NULL){
			die('can not find qqwry.dat...');
		}
		fseek(self::$fp, 0 ,SEEK_SET);
		$buf = fread(self::$fp, 8);
		self::$FirstStartIp = ord($buf[0]) + (ord($buf[1]) * 256) + (ord($buf[2]) * 256 * 256) + (ord($buf[3]) * 256 * 256 * 256);
		self::$LastStartIp = ord($buf[4]) + (ord($buf[5]) * 256) + (ord($buf[6]) * 256 * 256) + (ord($buf[7]) * 256 * 256 * 256);
		$RecordCount= floor((self::$LastStartIp - self::$FirstStartIp) / 7);
		if($RecordCount <= 1){
			self::$Country = 'FileDataError';
			fclose(self::$fp);
		}
		
		$RangB = 0;
		$RangE = $RecordCount;
		
		while($RangB < $RangE-1){
			$RecNo= floor(($RangB + $RangE) / 2);
			self::getStartIp($RecNo);
			if($ip == self::$StartIp){
				$RangB = $RecNo;
				break;
			}
			if($ip > self::$StartIp){
				$RangB = $RecNo;
			} else {
				$RangE = $RecNo;
			}
		}
		
		self::getStartIp($RangB);
		self::getEndIp();
		
		if((self::$StartIp <= $ip ) && ( self::$EndIp >= $ip)){
			self::getCountry();
			self::$Local = str_replace('我们一定要解放台湾！！！', '', self::$Local);
			self::$Local = str_replace('CZ88.NET', '', self::$Local);
		} else {
			self::$Country = '未知';
			self::$Local = '';
			self::$Local = str_replace('CZ88.NET', '', self::$Local);
		}
		fclose(self::$fp);
		return self::$Country.self::$Local;
	}
}
?>