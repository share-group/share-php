<?php
/**
 * 分页类
 */
class page{
	/**
	 * 当前页码
	 */
	private static $page = 0;
	/**
	 * 页面大小
	 */
	private static $size = 0;
	/**
	 * 总记录条数
	 */
	private static $count = 0;
	/**
	 * 总页数
	 */
	private static $total = 0;
	/**
	 * 查询条件
	 */
	private static $query = null;
	/**
	 * 当前页的左右边有多少个链接
	 */
	private static $set = 0;
	/**
	 * 是否启用查询功能
	 */
	private static $search = false;
	/**
	 * 是否启用下拉菜单
	 */
	private static $select = false;
	/**
	 * 是否显示总页数总记录条数
	 */
	private static $show_total = false;
	/**
	 * 分页字符串
	 */
	private static $page_string = null;
	/**
	 * 是否伪静态模式(默认是)
	 */
	private static $rewrite = true;
	/**
	 * 分页参数名称
	 */
	private static $param = 'page';
	/**
	 * 操作符
	 */
	private static $op = null;
	/**
	 * 分页参数名称是否为第一个参数
	 */
	private static $is_frist = false;

	/**
	 * 初始化参数
	 * @param $page 当前页码
	 * @param $size 页面大小
	 * @param $count 总记录条数
	 * @param $set 设置当前页的左右边有多少个链接(默认5个)
	 */
	public static function init($page, $size, $count, $set = 5){
		self::$page = $page;
		self::$size = $size;
		self::$count = $count;
		self::$query = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$i = strrpos(self::$query, 'page');
		if($i){
			self::$query = substr(self::$query, 0, $i);
		}
		$parse_url = parse_url(self::$query);
		$parse_url['query'] = preg_replace('/[\?]*&*/', '', $parse_url['query']);
		if(!strlen($parse_url['query']) && self::$rewrite === false){
			self::$is_frist = true;
			self::$query = $parse_url['scheme'].'://'.$parse_url['host'].$parse_url['path'].'?';
		}
		$i = strrpos(self::$query, '/');
		if($i != strlen(self::$query) - 1){
			self::$query .= '/';
		}
		self::$set = $set;
		self::$total = ceil(self::$count / self::$size);
		if(self::$rewrite === true){
			self::$op = '/';
		} else {
			self::$op = '=';
			self::$query = substr(self::$query, 0, -1);
			if(strrpos(self::$query, '&') === false){
				if(self::$is_frist === false){
					self::$query .= '&';
				}
			}
		}
	}

	/**
	 * 设置是否重写
	 * @param $rewrite
	 */
	public static function set_rewrite($rewrite){
		self::$rewrite = $rewrite;
	}

	/**
	 * 启用搜索
	 */
	public static function search(){
		self::$search = true;
	}

	/**
	 * 启用下拉菜单
	 */
	public static function select(){
		self::$select = true;
	}

	/**
	 * 启用显示总页数总记录条数
	 */
	public static function total(){
		self::$show_total = true;
	}

	/**
	 * 输出分页字符串
	 * @return 分页字符串
	 */
	public static function show(){
		self::search_();
		self::select_();
		self::first();
		self::prev();
		self::center();
		self::next();
		self::last();
		self::total_();
		return self::$page_string;
	}

	/**
	 * 生成首页链接
	 */
	private static function first(){
		if(self::$page != 1){
			self::$page_string .= '<a href="'.self::$query.self::$param.self::$op.'1" title="首页">首页</a>';
		}
	}

	/**
	 * 生成尾页链接
	 */
	private static function last(){
		if(self::$total > 1 && self::$total != self::$page){
			self::$page_string .= '<a href="'.self::$query.self::$param.self::$op.self::$total.'" title="尾页">尾页</a>';
		}
	}

	/**
	 * 生成下一页链接
	 */
	private static function next(){
		if(self::$total > self::$page){
			self::$page_string .= '<a href="'.self::$query.self::$param.self::$op.(self::$page + 1).'" title="下一页">下一页</a>';
		}
	}

	/**
	 * 生成上一页链接
	 */
	private static function prev(){
		if(self::$page > 1){
			self::$page_string .= '<a href="'.self::$query.self::$param.self::$op.(self::$page - 1).'" title="上一页">上一页</a>';
		}
	}

	/**
	 * 生成中间页码的链接
	 */
	private static function center(){
		$start = self::$page - self::$set;
		if($start <= 0){
			$start = 1;
		}
		$end = self::$page + self::$set + 1;
		if($end >= self::$total){
			$end = self::$total + 1;
		}
		for($i = $start; $i < $end; $i++){
			if(self::$page == $i){
				self::$page_string .='<font>'.$i.'</font>';
			} else {
				self::$page_string .='<a href="'.self::$query.self::$param.self::$op.$i.'" title="第'.$i.'页">'.$i.'</a>';
			}
		}
	}

	/**
	 * 生成搜索框
	 */
	private static function search_(){
		if(self::$search == true){
			self::$page_string .= '<form action="" method="get" onsubmit="window.location=\''.self::$query.self::$param.self::$op.'\'+this.page.value;return false;">转到第 <input type="text" name="'.self::$param.'" style="width:30px;height:auto"> 页 &nbsp;';
		}
	}

	/**
	 * 生成select
	 */
	private static function select_(){
		if(self::$select === true){
			self::$page_string .= '转到<select onchange="window.location=\''.self::$query.self::$param.self::$op.'\'+this.options[selectedIndex].value;return false;" style="height:auto;margin-right:5px;margin-left:5px">';
			$start = self::$page - self::$set;
			if($start <= 0){
				$start = 1;
			}
			$end = self::$page + self::$set + 1;
			if($end >= self::$total){
				$end = self::$total + 1;
			}
			for($i = $start; $i < $end; $i++){
				if(self::$page == $i){
					$select = ' selected="selected"';
				}
				self::$page_string .= '<option value="'.$i.'"'.$select.'>第'.$i.'页</option>';
				$select = '';
			}
			self::$page_string .= '</select>';
		}
	}

	/**
	 * 生成总页数总记录条数
	 */
	private static function total_(){
		if(self::$show_total  === true){
			self::$page_string .= '&nbsp;共'.number_format(self::$total).'页 &nbsp;'.number_format(self::$count).'条记录';
		}
	}
}
?>