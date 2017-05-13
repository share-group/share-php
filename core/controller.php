<?php
/**
 * 所有Controller父类
 */
class controller {

	/**
	 * 构造函数
	 */
	public function __construct() {
		if (sharePHP::get_mode() === 2) {
			$get = fliter($_SERVER['REQUEST_URI']);
			$class = sharePHP::get_class();
			$method = sharePHP::get_method();
			$i = strpos(fliter($_SERVER['REQUEST_URI']), $class . '/' . $method);
			$get = explode('/', substr($get, $i + strlen($class . '/' . $method) + 1));
			$flag = null;
			foreach ($get as $k => $g) {
				if ($flag === null) {
					$GLOBALS['_GET'][$g] = '';
					$flag = fliter($g);
				} else {
					$GLOBALS['_GET'][$flag] = fliter($g);
					$flag = null;
				}
			}
		}
	}

	/**
	 * 是否有post数据
	 */
	protected function has_post() {
		return !empty($GLOBALS['_POST']);
	}

	/**
	 * post
	 *
	 * @param $key 键        	
	 */
	protected function post($key) {
		return trim($GLOBALS['_POST'][$key]);
	}

	/**
	 * post int
	 *
	 * @param $key 键        	
	 */
	protected function post_int($key) {
		return intval($this->post($key));
	}

	/**
	 * post uint
	 * @param $key 键        	
	 */
	protected function post_uint($key) {
		$int = $this->post_int($key);
		if ($int <= 0) {
			return 0;
		}
		return $int;
	}

	/**
	 * post float
	 * @param $key 键        	
	 */
	protected function post_float($key) {
		return floatval($this->post($key));
	}

	/**
	 * post ufloat
	 *
	 * @param $key 键        	
	 */
	protected function post_ufloat($key) {
		$int = $this->post_float($key);
		if ($int <= 0) {
			return 0;
		}
		return $int;
	}

	/**
	 * post array
	 *
	 * @param $key 键        	
	 */
	protected function post_array($key) {
		return is_array($GLOBALS['_POST'][$key]) ? $GLOBALS['_POST'][$key] : array ();
	}

	/**
	 * 是否有get数据
	 */
	protected function has_get() {
		return !empty($GLOBALS['_GET']);
	}

	/**
	 * get
	 *
	 * @param $key 键        	
	 */
	protected function get($key) {
		return trim($GLOBALS['_GET'][$key]);
	}

	/**
	 * get int
	 *
	 * @param $key 键        	
	 */
	protected function get_int($key) {
		return intval($this->get($key));
	}

	/**
	 * get uint
	 *
	 * @param $key 键        	
	 */
	protected function get_uint($key) {
		$int = $this->get_int($key);
		if ($int <= 0) {
			return 0;
		}
		return $int;
	}

	/**
	 * get float
	 *
	 * @param $key 键        	
	 */
	protected function get_float($key) {
		return floatval($this->get($key));
	}

	/**
	 * get ufloat
	 *
	 * @param $key 键        	
	 */
	protected function get_ufloat($key) {
		$int = $this->get_float($key);
		if ($int <= 0) {
			return 0;
		}
		return $int;
	}

	/**
	 * get array
	 *
	 * @param $key 键        	
	 */
	protected function get_array($key) {
		return is_array($GLOBALS['_GET'][$key]) ? $GLOBALS['_GET'][$key] : array ();
	}

	/**
	 * 是否有request数据
	 */
	protected function has_request() {
		return $this->has_post() || $this->has_get();
	}

	/**
	 * request
	 *
	 * @param $key 键        	
	 */
	protected function request($key) {
		$get = $this->get($key);
		return $get ? $get : $this->post($key);
	}

	/**
	 * request int
	 *
	 * @param $key 键        	
	 */
	protected function request_int($key) {
		return intval($this->request($key));
	}

	/**
	 * request uint
	 *
	 * @param $key 键        	
	 */
	protected function request_uint($key) {
		$int = $this->request_int($key);
		if ($int <= 0) {
			return 0;
		}
		return $int;
	}

	/**
	 * request float
	 *
	 * @param $key 键        	
	 */
	protected function request_float($key) {
		return floatval($this->request($key));
	}

	/**
	 * request ufloat
	 *
	 * @param $key 键        	
	 */
	protected function request_ufloat($key) {
		$int = $this->request_float($key);
		if ($int <= 0) {
			return 0;
		}
		return $int;
	}

	/**
	 * request array
	 *
	 * @param $key 键        	
	 */
	protected function request_array($key) {
		$array = $this->get_array($key);
		return $array ? $array : $this->post_array($key);
	}

	/**
	 * 输出提示信息
	 *
	 * @param $result success、notice、error        	
	 * @param $msg 消息内容        	
	 */
	protected function show_msg($result, $msg) {
		view::assign('result', $result);
		view::assign('tips', $msg);
	}

	/**
	 * 获取上传的文件
	 *
	 * @param $key 键        	
	 */
	protected function file($key) {
		return $GLOBALS['_FILES'][$key];
	}
}
?>