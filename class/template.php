<?php
/**
 * 模板类
 */
class template{
	/**
	 * 变量表
	 */
	private $vars = null;
	/**
	 * 模板根目录
	 */
	private $path = null;
	/**
	 * 模板皮肤
	 */
	private $skin = null;
	/**
	 * 缓存生存时间，单位是秒(0则为不使用缓存)
	 */
	private $cache_time = 0;

	/**
	 * 导入变量
	 * @param $varname 变量名
	 * @param $value 变量值
	 */
	public function assign($varname, $value) {
		$this->vars[$varname] = $value;
	}

	/**
	 * 显示模板
	 * @param $file 模板文件名
	 */
	public function display($file){
		//如果使用了缓存的直接使用缓存
		$file = trim($file);
		$md5_file = md5($_SERVER['REQUEST_URI']);
		if($this->cache_time > 0){
			clearstatcache();
			$filemtime = 0;
			$cache_file = $this->path.'cache/'.$md5_file.'.html';
			if(file_exists($cache_file)){
				$filemtime = filemtime($cache_file);
				if($filemtime > 0 && $_SERVER['REQUEST_TIME'] - $filemtime <= $this->cache_time){
					echo file_get_contents($cache_file);
					return;
				} else {
					unlink($cache_file);
				}
			}
		}

		//如果没有缓存直接用编译文件
		$file_ = $file;
		$filename = $this->path.'html/'.$file.'.html';
		if(!file_exists($filename)){
			die('file '.$file.'.html not exists...');
		}
		foreach ($this->vars as $k => $v) {
			eval('$'.$k.' = '.var_export($v, true).";\r\n");
		}
		$compile_flie = $this->path.'compile/'.$md5_file.'.php';
		if(file_exists($compile_flie)){
			require $compile_flie;
			return;
		}

		//如果都没有，解析模板代码
		//解析html代码
		$html = $this->parse(trim(file_get_contents($filename)));

		//解析include
		$html = $this->parse_include($html);

		//定义资源文件根目录
		$resource_path = 'http://'.$_SERVER['HTTP_HOST'].str_replace('index.php', '', $_SERVER['SCRIPT_NAME']).$this->skin.'/';

		//导入css
		$css_path = $resource_path.'css/';
		$html = preg_replace('/<!--{css\s\'([\.\/\w]+)\'}-->/', '<?php echo \'<link href="'.$css_path.'\1.css" type="text/css" rel="stylesheet"/>\';?>', $html);

		//导入js
		$js_path = $resource_path.'js/';
		$html = preg_replace('/<!--{js\s\'([\.\/\w]+)\'}-->/', '<?php echo \'<script type="text/javascript" src="'.$js_path.'\1.js"></script>\';?>', $html);


		//记录模板生成时间
		$html = $html.'<!-- template create time '.date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']).' -->';
		$file = realpath($this->path).'/compile/'.$md5_file.'.php';

		//保存编译文件
		$dir = dirname($file);
		if(!file_exists($dir)){
			mkdir($dir, 0777, true);
		}
		file_put_contents($file, $html, LOCK_EX);
		if($this->cache_time <= 0){
			require $file;
			return;
		}

		//生成缓存
		ob_start();
		require $file;
		$html = ob_get_contents();
		ob_clean();
		$dir = str_replace('compile', 'cache', $dir).'/';
		if(!file_exists($dir)){
			mkdir($dir, 0777, true);
		}
		file_put_contents($dir.$md5_file.'.html', $html, LOCK_EX);
		echo $html;
	}

	/**
	 * 解析 include
	 * @param $html html代码
	 */
	private function parse_include($html){
		$rs = preg_match_all('/<!--{include\s([\'\.\/\w]+)}-->/', $html, $matches);
		if($rs <= 0){
			return $html;
		}
		$path = realpath($this->path).'/html/';
		foreach($matches[1] as $match){
			$match = trim(str_replace('\'', '', $match));
			$file = $path.$match;
			$html = str_replace('<!--{include \''.$match.'\'}-->', $this->parse(trim(file_get_contents($file))), $html);
		}
		unset($matches);
		return $html;
	}

	/**
	 * 分析html代码
	 * @param $html html代码
	 */
	private function parse($html){
		//数组访问
		$html = str_replace('[', '[\'', $html);
		$html = str_replace(']', '\']', $html);

		//foreach，暂时只支持 foreach ($array as $k => $v) 形式
		$html = preg_replace('/<!--{foreach\s\$([a-zA-Z_]+(\[.*\])*\sas\s\$([a-zA-Z_]+)\s(=>\s\$([a-zA-Z_]+)))}-->/', '<?php foreach($\1):?>', $html);
		$html = preg_replace('/<!--{endforeach}-->/', '<?php endforeach;?>', $html);

		//for 一定需要换行，需完善
		$html = preg_replace('/<!--{for(.*)}-->/', '<?php for(\1):?>', $html);
		$html = preg_replace('/<!--{endfor}-->/', '<?php endfor;?>', $html);

		//if elseif else 一定需要换行，需完善
		$html = preg_replace('/<!--{if\s\$([a-zA-Z_]+(\[.*\])*\s(.*))}-->/', '<?php if($\1):?>', $html);
		$html = preg_replace('/<!--{if\s\$([a-zA-Z_]+)}-->/', '<?php if($\1):?>', $html);
		$html = preg_replace('/<!--{elseif\s\$([a-zA-Z_]+(\[.*\])*\s(.*))}-->/', '<?php elseif($\1):?>', $html);
		$html = preg_replace('/<!--{elseif\s\$([a-zA-Z_]+)}-->/', '<?php elseif($\1):?>', $html);
		$html = preg_replace('/<!--{endif}-->/', '<?php endif;?>', $html);

		//while 一定需要换行，需完善
		$html = preg_replace('/<!--{while(.*)}-->/', '<?php while(\1):?>', $html);
		$html = preg_replace('/<!--{endwhile}-->/', '<?php endwhile;?>', $html);

		//break
		$html = preg_replace('/<!--{break}-->/', '<?php break;?>', $html);

		//continue
		$html = preg_replace('/<!--{continue}-->/', '<?php continue;?>', $html);

		//$x++ $x-- $x+=1 $x-=1 $x*=1 $x/=1
		$html = preg_replace('/<!--{\$([a-zA-Z_]+[\+\+|\-\-|\+\=|\-\=|\*\=|\/\=]{1}(.*))}-->/', '<?php $\1;?>', $html);

		//函数调用 一定需要换行，需完善
		$html = preg_replace('/<!--{([a-zA-Z_]{1}[\w\:]+)\s*\((.*)\)}-->/', '<?php echo \1(\2);?>', $html);

		//变量输出
		$html = preg_replace('/<!--{\$([a-zA-Z_]+(\[.*\])*)}-->/', '<?php echo $\1;?>', $html);

		//常量输出
		$html = preg_replace('/<!--{\'(\w+)\'}-->/', '<?php echo \'\1\';?>', $html);
		return $html;
	}

	/**
	 * 设置模板根目录
	 * @param $path 目录
	 */
	public function path($path){
		if(!trim($path)){
			$path = dirname(__FILE__);
		}
		$path_arr = explode('/',$path);
		$this->skin = $path_arr[count($path_arr) - 1];
		$this->path = $path.'/';
		if(!file_exists($this->path)){
			$paths = explode('/', $this->path);
			die('template '.$paths[count($paths)-2].' is not exists...');
		}
	}

	/**
	 * 删除编译文件、缓存文件
	 */
	public function clean(){
		if(!$this->path){
			die('please set the template path...');
		}
		$path = $this->path.'compile/';
		chdir($path);
		$compile_list = glob('*');
		foreach($compile_list as $c){
			unlink($path.$c);
		}
		$path = $this->path.'cache/';
		chdir($path);
		$cache_list = glob('*');
		foreach($cache_list as $c){
			unlink($path.$c);
		}
	}

	/**
	 * 设置缓存时间，单位是秒(0为不使用缓存，默认0)
	 * @param $time 缓存时间
	 */
	public function set_cache_time($time = 0){
		$time = intval($time);
		$this->cache_time = $time;
	}
}

?>