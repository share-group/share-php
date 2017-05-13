<?php
/**
 * 生成文档类
 */
class doc{
	private $html_head;
	private $html_foot;
	private $title;
	private $path;
	/**
	 * 构造函数
	 * @param $title 文档名
	 */
	public function __construct($title){
		$this->make_head($title);
		$this->make_foot();
	}

	/**
	 * 析构函数
	 */
	public function __destruct(){
		$html = $this->html_head;
		$html .= '<div id="left"><ul>';
		$dir = str_replace('\\', '/', $dir);
		$i = strrpos($dir, '/');
		$len = strlen($dir);
		if($len - $i == 1){
			$tmp = substr($dir, 0, $i);
		} else {
			$tmp = $dir;
		}
		$tmp = explode('/', $tmp);
		$tmp = $tmp[count($tmp) - 1];
		$list = $this->ls_dir($this->path);
		foreach($list as $ls){
			$html .= '<li><div class="block"></div><a href="'.$ls.'/index.html">'.$ls.'</a></li>';
		}
		$html .= '</ul></div>'.$this->html_foot;
		$html = str_replace($this->title.'文档</span>', '<a href="index.html" title="'.$this->title.'文档">'.$this->title.'文档</a></span>', $html);
		$this->write($this->path.'/index.html', $html);
	}

	/**
	 * 生成文档
	 * @param $dir 要生成的文档的应用程序路径
	 * @param $path 存放文档的路径
	 */
	public function create($dir, $path){
		$this->path = $path;
		$list = $this->ls_dir($dir);
		foreach($list as $ls){
			$dir_ = $dir.'/'.$ls;
			$html .= '<li><div class="block"></div><a href="'.$ls.'/index.html">'.$ls.'</a></li>';
			$this->create_($dir_, $path);
		}
		$this->create_($dir, $path);
		$list = $this->ls_dir($path);
		foreach ($list as $ls){
			$dir_ = $path.'/'.$ls.'/';
			if(!is_dir($dir_)){
				continue;
			}
			$list_ = $this->ls($dir_, '*.html');
			copy($dir_.$list_[0], $dir_.'index.html');
		}
	}

	/**
	 * 私有生成文档函数
	 * @param $dir 要生成的文档的应用程序路径
	 * @param $path 存放文档的路径
	 */
	private function create_($dir, $path){
		$dir = str_replace('\\', '/', $dir);
		$path = str_replace('\\', '/', $path);
		$i = strrpos($dir, '/');
		$len = strlen($dir);
		if($len - $i == 1){
			$tmp_dir = substr($dir, 0, $i);
		} else {
			$tmp_dir = $dir;
		}
		$tmp_dir = explode('/', $tmp_dir);
		$tmp_dir = $tmp_dir[count($tmp_dir) - 1];
		$list = $this->ls($dir);
		$html_body_left = '<div id="left"><ul>';
		foreach($list as $file){
			if($file === basename(__FILE__) || strpos($file, 'config') > -1){
				continue;
			}
			$file = explode('.', $file);
			$html_body_left .= '<li><div class="block"></div><a href="'.$file[0].'.html">'.$file[0].'</a></li>';
		}
		$html_body_left .= '<li><a href="../index.html">返回上一级</a></li>';
		$html_body_left .= '</ul></div>';
		$html_body_left_backup = $html_body_left;
		foreach($list as $file){
			$html_body_left = $html_body_left_backup;
			$html_body_right = '<div id="right">';
			if($file === basename(__FILE__)){
				continue;
			}
			$str = file_get_contents($dir.'/'.$file);
			$file = explode('.', $file);
			$str = htmlspecialchars($str);
			$str = explode('/**', $str);
			$i = strrpos($str[1], '*/');
			$str[1] = trim(str_replace('*', '', substr($str[1], 0, strpos($str[1], '{'))));
			$class = trim(str_replace('*', '', substr($str[1], 0, $i)));
			$class = str_replace('/', '', $class);	
			$class = trim(str_replace('cl', '', $class));
			$html_body_right .= '<div id="class"><div style="background:#26a0da;width:5px;height:16px;margin-top:13px;margin-right:10px"></div>'.$class.'</div>';
			$html_body_right_len = strlen($html_body_right);
			foreach($str as $s){
				$s = explode('*',$s);
				unset($param, $function);
				foreach($s as $ss){
					$ss = str_replace('&lt;?php', '', $ss);
					if(!trim($ss)){
						continue;
					}
					if(strpos($ss, '@') <= 0 && strpos($ss, 'function') <= 0 && strpos($ss, '&lt;p&gt;') <= 0 && strpos($ss, '&lt;br&gt;') <= 0){
						continue;
					}
					$index = stripos($ss, '){');
					if($index > 0){
						$p1 = strpos($ss, '/');
						$p2 = strpos($ss, 'p');
						$ss = substr($ss, $p1 + 1, -$p2);
						$function = '<div style="background:#ff9000;width:5px;height:16px;margin-top:3px;margin-right:10px"></div>'.trim($s[1]).'<br/>'.trim(substr($ss, 0, $index)).'<br/>';
					} else {
						$ss = trim($ss);
						$ss = str_replace('&lt;p&gt;', '', $ss);
						$ss = str_replace('&lt;br&gt;', '', $ss);
						$param .= $ss.'<br/>';

					}
				}
				if(strpos($function, 'private') > 0 || strpos($function, 'protected') > 0){
					continue;
				}
				if($this->change_color($function).$param){
					$start = strpos($function, 'function ');
					$end = strrpos($function, '(');
					$tmp = substr($function, $start + 9, $end - $start - 9);
					if($param){
						$param = '<div class="param"><div class="top"><a href="#" title="返回顶部">top</a></div>'.$param.'</div>';
					}
					$html_body_right .= '<div class="function" id="'.$tmp.'">'.$this->change_color($function).$param.'</div>';
				}
			}
			$html_body_left = str_replace('<li><div class="block"></div><a href="'.$file[0].'.html"', '<li class="current"><div class="block"></div><a href="'.$file[0].'.html"', $html_body_left);
			if($html_body_right_len === strlen($html_body_right)){
				$html_body_right .= '<div class="param">文档尚未编写！</div>';
			}
			$html_body_right .= '<br/>'.$this->html_foot;
			$html = $this->html_head.$html_body_left.$html_body_right;
			$html = str_replace('&lt;pre&gt;', '<pre>', $html);
			$html = str_replace('&lt;/pre&gt;', '</pre>', $html);
			$html = str_replace($this->title.'文档</span>', '<a href="../index.html" title="'.$this->title.'文档">'.$this->title.'文档</a></span>', $html);
			$this->write($path.'/'.$tmp_dir.'/'.$file[0].'.html', $html);
		}
	}

	/**
	 * 列出所有文件夹
	 * @param $dir 目录路径
	 * @param $sort 排序(默认是按文件名顺序)
	 * @return 文件夹列表
	 */
	private function ls_dir($dir, $sort = FILE_ASC){
		chdir($dir);
		$rs = glob('*', GLOB_ONLYDIR);
		if(!count($rs)){
			return array();
		}
		switch($sort){
			case 1:
				sort($rs);
				break;
			case -1:
				rsort($rs);
				break;
			default:
				return array();
		}
		return $rs;
	}

	/**
	 * 列出目录所有内容
	 * @param $dir 目录路径
	 * @param $mode 匹配模式(默认是php文件)
	 * @param $sort 排序(默认是按文件名顺序)
	 * @return 指定文件夹内容列表
	 */
	private function ls($dir, $mode = '*.php', $sort = FILE_ASC){
		chdir($dir);
		$rs = glob($mode, GLOB_NOSORT);
		if(!count($rs)){
			return array();
		}
		switch($sort){
			case 1:
				sort($rs);
				break;
			case -1:
				rsort($rs);
				break;
			default:
				return array();
		}
		return $rs;
	}

	/**
	 * 写入文件
	 * @param $flie 文件路径
	 * @param $data 写入的数据
	 * @param $append 是否追加(默认不追加)
	 * @return 写入到文件内数据的字节数
	 */
	private function write($file, $data, $append = false){
		if(!file_exists($file)){
			$dir = dirname($file);
			$file = $dir.'/'.basename($file);
			$this->mkdir($dir);
		} elseif(!is_writable($file)) {
			return;
		}
		if($append === true){
			$byte = file_put_contents($file, $data, FILE_APPEND);
		} else {
			$byte = file_put_contents($file, $data, LOCK_EX);
		}
		return $byte;
	}

	/**
	 * 创建文件夹
	 * @param $dir 文件夹路径
	 * @param $mode 模式(默认0777)
	 * @param $recursive 是否递归创建(默认true)
	 */
	private function mkdir($dir, $mode = 0777, $recursive = true){
		if(file_exists($dir)){
			return;
		}
		mkdir($dir, $mode, $recursive) or die('create dir '.$dir.' error...');
	}

	/**
	 * 根据数组的值删除该键
	 * @param $array 数组
	 * @param $value 值(无限参数)
	 */
	private function delete_by_value($array, $value = ''){
		$value = func_get_args();
		unset($value[0]);
		foreach ($value as $key => $v) {
			if(!$v){
				continue;
			}
			$i = array_search($v, $array);
			if($i === false){
				continue;
			}
			unset($array[$i]);
		}
		return $array;
	}

	/**
	 * 切换某些字符串的颜色
	 * @param $str 字符串
	 */
	private function change_color($str){
		if(!$str || strlen($str) <= 0){
			return;
		}
		$str = str_replace('public', '<strong><font color="#009900">public</font></strong>', $str);
		$str = str_replace('private', '<strong><font color="#009900">private</font></strong>', $str);
		$str = str_replace('protected', '<strong><font color="#009900">protected</font></strong>', $str);
		$str = str_replace('static', '<strong><font color="#FF0000">static</font></strong>', $str);
		$str = str_replace('function', '<strong><font color="#0000FF">function</font></strong>', $str);
		$str = str_replace('true', '<strong><font color="#996600">true</font></strong>', $str);
		$str = str_replace('false', '<strong><font color="#996600">false</font></strong>', $str);
		$str = str_replace('null', '<strong><font color="#009900">null</font></strong>', $str);
		return $str;
	}

	/**
	 * 生成文档头代码
	 * @param $title 文档名
	 */
	private function make_head($title){
		$this->title = $title;
		$this->html_head = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html;charset=utf-8"/><meta http-equiv="pragma" content="no-cach"/><meta http-equiv="cache-control" content="no-cache"/><meta http-equiv="expires" content="0"/><title>'.$title.'文档</title></head><style>*{margin:0;font-family:\'微软雅黑\';height:100%;font-size:16px}div{float:left}a,#right{color:#333;text-decoration:none}a:hover{color:#2366a8}ul{list-style:none;padding:0;}ul li:hover,.current{background:#dbf1fe;cursor:pointer;}ul li{height:30px;line-height:30px}ul li a{margin-left:20px;}#left{width:180px;background:#f2f9fd;height:100%;border-right:#b5cfd9 1px solid}#right{margin-left:20px;font-size:12px;width:70%}.function,.param{height:auto;float:none}.function{margin-bottom:50px;margin-top:10px;width:100%}.param{margin-top:8px;background:#f3f3f3;padding:8px;border:#cac9c9 1px solid;font-size:14px}.top{float:right;}.top a{font-size:12px}#class{height:40px;float:none;line-height:40px;font-weight:bold;}.block{width:8px;height:20px;background:#FC0;margin-top:5px;margin-left:10px}</style><body><div style="height:100px;line-height:100px;font-size:18px;background:#f2f9fd;border-bottom:#b5cfd9 1px solid;width:100%"><span style="margin-left:50px;font-size:20px">'.$this->title.'文档</span></div>';
	}

	/**
	 * 生成文档尾部代码
	 */
	private function make_foot(){
		$this->html_foot = "</div><script>function $(id){return document.getElementById(id);}var left=$('left').scrollHeight;var right=$('right').scrollHeight;if(left>=right){ $('right').style.height=left+'px';$('left').style.height=left+'px';}else{ $('left').style.height=right+'px';$('right').style.height=right+'px';}</script></body></html>";
	}
}

//可能用到的常量
define('FILE_ASC', 1);
define('FILE_DESC', -1);
?>