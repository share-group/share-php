<?php
/**
 * snmp类
 */
class snmpworker{
	/**
	 * 操作系统类型
	 */
	private $os_type = null;
	/**
	 * 主机ip地址
	 */
	private $host = null;
	/**
	 * 版本
	 */
	private $version = 3;
	/**
	 * 团体名称
	 */
	private $community = null;
	/**
	 * 用户名
	 */
	private $sec_name = null;
	/**
	 * 认证等级
	 */
	private $sec_level = null;
	/**
	 * 认证加密方式
	 */
	private $auth_protocol = null;
	/**
	 * 认证密码
	 */
	private $auth_passphrase = null;
	/**
	 * 加密/解密协议
	 */
	private $priv_protocol = null;
	/**
	 * 加密/解密协议所用key的生成参数
	 */
	private $priv_passphrase = null;

	/**
	 * 构造函数
	 * @param $os_type 操作系统类型(SNMP_LINUX|SNMP_WINDOWS)
	 * @param $host 主机ip地址(可以包含端口)，例子:127.0.0.1:161
	 * @param $sec_name 用户名
	 * @param $auth_protocol 认证加密方式(MD5|SHA)
	 * @param $auth_passphrase 认证密码
	 * @param $priv_protocol 加密/解密协议(DES|AES)
	 * @param $priv_passphrase 加密/解密协议所用key的生成参数(一般和认证密码一样)
	 */
	public function __construct($os_type, $host, $sec_name = null, $sec_level = null, $auth_protocol = null, $auth_passphrase = null, $priv_protocol = null, $priv_passphrase = null){
		$this->os_type = trim($os_type);
		$this->host = trim($host);
		switch($this->version){
			case 3:
				$this->sec_name = trim($sec_name);
				$this->sec_level = trim($sec_level);
				$this->auth_protocol = trim($auth_protocol);
				$this->auth_passphrase = trim($auth_passphrase);
				$this->priv_protocol = trim($priv_protocol);
				$this->priv_passphrase = trim($priv_passphrase);
				break;
			default:
				if(DEBUG){
					die('Invalid version...');
				} else {
					die;
				}
				break;
		}
	}

	/**
	 * 获取操作系统某一个snmp对象的数据
	 * @param $objectid 欲获取的snmp对象(不填则获取全部)
	 */
	public function get($objectid = null){
		switch($this->version){
			case 3:
				$snmp = snmp3_real_walk($this->host, $this->sec_name, $this->sec_level, $this->auth_protocol, $this->auth_passphrase, $this->priv_protocol, $this->priv_passphrase, $objectid);
				break;
		}
		return $snmp;
	}
}

//可能用到的常量
define('SNMP_NO_AUTH_NO_PRIV', 'noAuthNoPriv');
define('SNMP_AUTH_NO_PRIV', 'authNoPriv');
define('SNMP_AUTH_PRIV', 'authPriv');

define('SNMP_MD5', 'MD5');
define('SNMP_SHA', 'SHA');
define('SNMP_DES', 'DES');
define('SNMP_AES', 'AES');

define('SNMP_VERSION_1', 1);
define('SNMP_VERSION_2', 2);
define('SNMP_VERSION_3', 3);

define('SNMP_LINUX', 'LINUX');
define('SNMP_WINDOWS', 'WINDOWS');
?>