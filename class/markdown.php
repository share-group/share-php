<?php
/**
 * md文件解析类
 */
class markdown{
	/**
	 * 输出的html代码(js部分与依赖jquery，请自行引入)
	 */
	private $html = '<style>*{word-break:break-all}ul,ol{margin-left:25px}pre{padding:10px;width:98%;margin-left:45px;border:1px solid #ccc;background:#F1F1F1}table th,table td{padding:10px}table th{background:#E2E2E2}table{margin-left:45px;width:98%;border-top:1px solid #ccc;border-left:1px solid #ccc}table th,table td{border-bottom:1px solid #ccc;border-right:1px solid #ccc;}h1,h2,h3,h4,h5,h6{margin:20px;font-weight:bold}h1{font-size:32px}h2{font-size:28px}h3{font-size:24px}h4{font-size:20px}h5{font-size:16px}h6{font-size:16px}p{margin-left:45px}.anchor{display:none;margin-top:5px;margin-right:5px;float:left;width:18px;height:10px;background:url(\'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABIAAAAKCAIAAAA2KZn2AAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAAuklEQVQokZ2RLRKDMBSElypukNwAHJKjgEMiOUZwHKG9QXCVSCQSHLYO3MNtRRvolHb6sybzzbx9b2fjkcTvOtzfZajLNNTeJh2mZT3M83BK9YqL85GktCZ+szY2rUhTqAckSZDsqghAnNtRuElGm8cAoqrbI5wrs9NtvDMKQH4WUppi85GTzRwe9rl8DcD3V74s+5kXIb/J/FyJKhr50JCrhCSltyYJFACVHPtpWtFJBYmxvbvv/ffdVycYutMLAImQAAAAAElFTkSuQmCC\') no-repeat}</style>';
	/**
	 * 文件句柄
	 */
	private $handle = null;
	/**
	 * 缓存时间，秒为单位
	 */
	private $cache_time = 0;
	/**
	 * 解析表格用到的参数变量集合
	 */
	private $table = array();
	/**
	 * 解析无序列表用到的参数变量集合
	 */
	private $ul = array();
	/**
	 * 解析有序列表用到的参数变量集合
	 */
	private $ol = array();
	/**
	 * 解析pre用到的参数变量集合
	 */
	private $pre = array();
	
	/**
	 * 构造函数
	 * @param $file 文件路径
	 * @param $cache_time 缓存时间，秒为单位(默认不用缓存)
	 */
	public function __construct($file, $cache_time = false){
		$this->cache_time = intval($cache_time);
		if(!is_file($file)){
			echo_('file '.$file.' not exists...', true);
		}
		if($this->cache_time <= 0) {
			$this->handle = fopen($file, 'r');
			if(!$this->handle) {
				echo_('can not read file '.$file, true);
			}
		}
	}
	
	/**
	 * 解析
	 */
	public function parse(){
		if($this->cache_time > 0) {
			return;
		}
		while (($buffer = fgets($this->handle)) !== false) {
			// 解析文本区域
			$this->parse_pre($buffer);
			
			// 解析标题
			$this->parse_title($buffer);
			
			// 解析表格
			$this->parse_table($buffer);
			
			// 解析图片
			$this->parse_img($buffer);
			
			// 解析无序列表
			$this->parse_ul($buffer);
			
			// 解析有序列表
			$this->parse_ol($buffer);
			
			// 解析分割线
			$this->parse_hr($buffer);
			
			// 解析文本
			$this->parse_content($buffer);
		}
		fclose($this->handle);
		return $this->html;
	}
	
	/**
	 * 是否为标题
	 * @param $buffer 文件流
	 */
	private function is_title($buffer){
		$h = intval(substr_count($buffer, '#'));
		return $h > 0 && $h <= 6;
	}
	
	/**
	 * 是否为分割线
	 * @param $buffer 文件流
	 */
	private function is_hr($buffer){
		$bool = false;
		$bool = $bool || is_int(strpos($buffer, '---'));
		$bool = $bool || is_int(strpos($buffer, '***'));
		return $bool;
	}
	
	/**
	 * 解析标题
	 * @param $buffer 文件流
	 */
	private function parse_title($buffer){
		$buffer = trim($buffer);
		if(!$this->is_title($buffer) || $buffer{0} === '!' || $buffer{0} !== '#') {
			return;
		}
		$h = intval(substr_count($buffer, '#'));
		$buffer = trim(str_replace('#', '', $buffer));
		$id = md5($buffer);
		$this->html .= '<h'.$h.' id="'.$id.'" onMouseOver="$(\'#'.$id.' .anchor\').css(\'display\',\'block\')" onMouseOut="$(\'#'.$id.' .anchor\').css(\'display\',\'none\')"><a href="#'.$id.'"><div class="anchor"></div></a><div style="margin-left:25px">'.$this->parse_link($buffer).'</div></h'.$h.'>';
	}
	
	/**
	 * 解析分割线
	 * @param $buffer 文件流
	 */
	private function parse_hr($buffer){
		$buffer = trim($buffer);
		if(!$this->is_hr($buffer) || $this->table['open'] || $buffer{0} === '!'){
			return;
		}
		$this->html .= '<hr>';
	}
	
	/**
	 * 解析文本
	 * @param $buffer 文件流
	 */
	private function parse_content($buffer){
		if($this->ul || $this->ol){
			return;
		}
		if($this->pre['open']){
			$this->html .= htmlspecialchars($buffer);
		} else {
			$buffer = trim($buffer);
			if(!$buffer || $buffer{0} === '!'){
				return;
			}
			$bool = true;
			$bool = $bool && !$this->is_title($buffer);
			$bool = $bool && !$this->is_hr($buffer);
			$bool = $bool && !$this->table['open'];
			if(!$bool){
				return;
			}
			$this->html .= '<p>'.$this->parse_link($buffer).'</p>';
		}
	}
	
	/**
	 * 解析超链接
	 * @param $buffer 文件流
	 */
	private function parse_link($buffer){
		$buffer = trim($buffer);
		if($buffer{0} === '!'){
			return;
		}
		return trim(preg_replace('/\[(.*)\]\((.*)\)/', '<a href="$2" target="_blank" title="$1   $2">$1</a>', $buffer));
	}
	
	/**
	 * 解析表格
	 * @param $buffer 文件流
	 */
	private function parse_table($buffer){
		$buffer = trim($buffer);
		if($buffer{0} === '!' || $this->pre['open']){
			if($this->pre['open'] && intval(strpos($buffer,'<')){
				$this->html .= trim(htmlspecialchars($buffer));
			}
			return;
		}
		$td_num = intval(substr_count($buffer, '|'));
		if($td_num <= 0){
			if($this->table['open']){
				unset($this->table);
				$this->html .= '</tbody></table>';
			}
			return;
		}
		$td_array = explode('|', $buffer);
		if(empty($td_array) || !is_array($td_array)){
			return;
		}
		if($this->table['open'] && feof($this->handle)){
			$this->table['close'] = true;
		}
		$size = count($td_array);
		if(!$this->table['open']){
			$this->table['open'] = true;
			$this->html .= '<table cellpadding="0" cellspacing="0">';
		}
		if($this->table['open'] && !$this->table['head']){
			$this->table['head'] = true;
			$this->html .= '<thead><tr>';
			for($i = 0; $i < $size; $i++){
				$this->html .= '<th>'.trim($td_array[$i]).'</th>';
			}
			$this->html .= '</tr></thead>';
		}
		if($this->table['open'] && $this->is_hr($buffer)){
			$this->table['body'] = true;
			$this->html .= '<tbody>';
		}
		if($this->table['body']){
			if($this->is_hr($buffer)){
				return;
			}
			$this->html .= '<tr>';
			for($i = 0; $i < $size; $i++){
				$this->html .= '<td>'.trim($td_array[$i]).'</td>';
			}
			$this->html .= '</tr>';
		}
		if($this->table['close']){
			unset($this->table);
			$this->html .= '</tbody></table>';
		}
	}
	
	/**
	 * 解析文本区域
	 * @param $buffer 文件流
	 */
	private function parse_pre(&$buffer){
		$str = trim($buffer);
		if($str{0} === '!'){
			return;
		}
		$buffer = str_replace('	', '    ', $buffer);
		if($buffer{0} === '`' && $buffer{1} === '`' && $buffer{2} === '`'){
			if(!$this->pre['open']){
				$this->pre['open'] = true;
				$this->html .= '<pre>';
			} else {
				unset($this->pre);
				$this->html .= '</pre>';
			}
			$buffer = '';
		}
	}
	
	/**
	 * 解析图片
	 * @param $buffer 文件流
	 */
	private function parse_img(&$buffer){
		if($buffer{0} !== '!'){
			return;
		}
		$buffer = substr($buffer, 1);
		$this->html .= trim(preg_replace('/\[(.*)\]\((.*)\)/', '<a href="$2" target="_blank" title="$1   $2"><img src="$2" alt="$1"/></a>', trim($buffer)));
	}
	
	/**
	 * 解析无序列表
	 * @param $buffer 文件流
	 */
	private function parse_ul($buffer){
		$str = trim($buffer);
		if($str{0} === '*' || $str{0} === '+' || ($str{0} === '-' && $str{1} !== '-')){
			if(!$this->ul['open']){
				$this->ul['open'] = true;
				$this->html .= '<ul>';
			}
			$str = str_replace('*', '', $str);
			$str = str_replace('+', '', $str);
			$str = str_replace('-', '', $str);
			$this->html .= '<li>'.trim($str).'</li>';
		} else {
			if($this->ul['open']){
				$this->ul['close'] = true;
				$this->html .= '</ul>';
				unset($this->ul['open']);
			}
		}
		if($this->ul['open'] && feof($this->handle)){
			$this->ul['close'] = true;
			$this->html .= '</ul>';
			unset($this->ul['open']);
		}
	}
	
	/**
	 * 解析有序列表
	 * @param $buffer 文件流
	 */
	private function parse_ol($buffer){
		$str = trim($buffer);
		$pattern = '/^\d+\./';
		if(preg_match($pattern, $str)){
			if(!$this->ol['open']){
				$this->ol['open'] = true;
				$this->html .= '<ol>';
			}
			$str = trim(preg_replace($pattern, '', $str));
			$this->html .= '<li>'.trim($str).'</li>';
		} else {
			if($this->ol['open']){
				$this->ol['close'] = true;
				$this->html .= '</ol>';
				unset($this->ol['open']);
			}
		}
		if($this->ol['open'] && feof($this->handle)){
			$this->ol['close'] = true;
			$this->html .= '</ol>';
			unset($this->ol['open']);
		}
	}
}
?>