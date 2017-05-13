<?php
/**
 * mysql 数据库类(mysqli实现)
 */
class shareMysql {
	/**
	 * 数据库对象
	 */
	private $mysqli = null;
	/**
	 * 数据库前缀
	 */
	private $pre = null;
	/**
	 * 数据库名称
	 */
	private $db_name = null;
	/**
	 * SQL语句
	 */
	private $sql = '';

	/**
	 * 构造函数
	 * @param $db_host 数据库主机
	 * @param $db_user 数据库用户
	 * @param $db_pass 数据库密码
	 * @param $db_pre 数据库前缀
	 * @param $db_name 数据库名称
	 * @param $db_port 数据库端口(默认3306)
	 * @param $db_charset 字符集(默认utf8)
	 */
	public function __construct($db_host, $db_user, $db_pass, $db_pre, $db_name, $db_port = 3306, $db_charset = 'utf8') {
		$this->mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port) or $this->show_error();
		$this->charset($db_charset);
		$this->pre = $db_pre;
		$this->db_name = $db_name;
	}

	/**
	 * 析构函数
	 */
	public function __destruct() {
		$this->clear();
		if ($this->mysqli) {
			$this->mysqli->close();
		}
	}
	
	/**
	 * 切换数据库
	 * @param $db 数据库
	 */
	public function select_db($db) {
		$this->db_name = $db;
		$this->mysqli->select_db($db);
	}

	/**
	 * 是否可用
	 */
	public function can_use() {
		return is_string($this->mysqli->client_info);
	}

	/**
	 * 获取mysql版本
	 * @return mysql版本
	 */
	public function version() {
		$version = $this->mysqli->server_version;
		$main_version = intval($version / 10000);
		$minor_version = intval(($version - $main_version * 10000) / 100);
		$sub_version = $version - $main_version * 10000 - $minor_version * 100;
		return $main_version . '.' . $minor_version . '.' . $sub_version;
	}

	/**
	 * 获取mysql连接方式
	 * @return mysql连接方式
	 */
	public function host_info() {
		return $this->mysqli->host_info;
	}

	/**
	 * 插入数据(支持批量)
	 * @param $table 表名
	 * @param $data 数据
	 * @return 最后操作的自增id
	 */
	public function insert($table, $data) {
		if (empty($data)) {
			return 0;
		}
		$this->sql = 'insert into ';
		return $this->add_data($table, $data);
	}

	/**
	 * 替换数据(支持批量)
	 * @param $table 表名
	 * @param $data 数据
	 * @return 最后操作的自增id
	 */
	public function replace($table, $data) {
		if (empty($data)) {
			return 0;
		}
		$this->sql = 'replace into ';
		return $this->add_data($table, $data);
	}

	/**
	 * 删除数据
	 * @param $table 表名
	 * @return 影响行数
	 */
	public function delete($table) {
		$this->sql = 'delete from ';
		$this->table($table);
		return $this;
	}

	/**
	 * 更新数据
	 * @param $table 表名
	 * @param $update 要更新的数据array('k'=>'v')
	 * @return 影响行数
	 */
	public function update($table, $update) {
		$this->sql = 'update ';
		$this->table($table);
		$this->sql .= ' set ';
		foreach ($update as $k => $v) {
			$this->sql .= '`' . $this->mysqli->real_escape_string($k) . '`=' . '\'' . ($v) . '\',';
		}
		$this->sql = substr($this->sql, 0, -1);
		return $this;
	}

	/**
	 * 选择某一个表
	 * @param $table 表名
	 * @param $content 选择的内容
	 * @return $this
	 */
	public function select($table, $content = '') {
		$this->sql = 'select SQL_CALC_FOUND_ROWS ';
		if (!is_array($content)) {
			$this->sql .= '*';
		} else {
			$this->sql .= '`' . implode('`,`', $content) . '`';
		}
		$this->sql .= ' from ';
		$this->table($table);
		return $this;
	}

	/**
	 * 去除相同的数据
	 * @return $this
	 */
	public function distinct() {
		$arr = explode('SQL_CALC_FOUND_ROWS', $this->sql);
		$this->sql = $arr[0] . ' SQL_CALC_FOUND_ROWS  distinct ' . $arr[1];
		return $this;
	}

	/**
	 * 统计的记录总数
	 * @param $table 表名
	 * @param $column 字段名
	 * @param $as 别名
	 * @return $this
	 */
	public function count($table, $column = '', $as = 'count') {
		if (!$column) {
			$column = '*';
		}
		$this->sql = 'select count(' . $column . ') as ' . $as . ' from ';
		$this->table($table);
		return $this;
	}

	/**
	 * 计算某个字段的最大值
	 * @param $table 表名
	 * @param $column 字段名
	 * @param $as 别名(默认max)
	 * @return $this
	 */
	public function select_max($table, $column, $as = 'max') {
		$this->sql = 'select max(' . $column . ') as ' . $as . ' from ';
		$this->table($table);
		return $this;
	}

	/**
	 * 计算某个字段的最小值
	 * @param $table 表名
	 * @param $column 字段名
	 * @param $as 别名(默认min)
	 * @return $this
	 */
	public function select_min($table, $column, $as = 'min') {
		$this->sql = 'select min(' . $column . ') as ' . $as . ' from ';
		$this->table($table);
		return $this;
	}

	/**
	 * 计算某个字段的总合
	 * @param $table 表名
	 * @param $column 字段名
	 * @param $as 别名(默认sum)
	 * @return $this
	 */
	public function select_sum($table, $column, $as = 'sum') {
		$this->sql = 'select sum(' . $column . ') as ' . $as . ' from ';
		$this->table($table);
		return $this;
	}

	/**
	 * 计算某个字段的平均值
	 * @param $table 表名
	 * @param $column 字段名
	 * @param $as 别名(默认avg)
	 * @return $this
	 */
	public function select_avg($table, $column, $as = 'avg') {
		$this->sql = 'select avg(' . $column . ') as ' . $as . ' from ';
		$this->table($table);
		return $this;
	}

	/**
	 * 获取对上一次数据库请求的记录总数
	 * @return int
	 */
	public function get_count() {
		$this->sql = 'select FOUND_ROWS() as count';
		$rs = $this->query();
		return intval($rs[0]['count']);
	}

	/**
	 * 指定条件
	 * @param $key 键
	 * @return $this
	 */
	public function where($key) {
		$this->sql .= ' where `' . $key . '`';
		return $this;
	}

	/**
	 * like
	 * @param $value 值
	 * @param $option like选项(默认LIKE)
	 * <br>可选项：
	 * <br>LIKE
	 * <br>LIKE_LEFT
	 * <br>LIKE_RIGHT
	 * @return $this
	 */
	public function like($value, $option = LIKE) {
		$this->sql .= ' like';
		switch ($option) {
			case 'like':
				$this->sql .= '\'%' . $value . '%\' ';
				break;
			case 'like_left':
				$this->sql .= '\'%' . $value . '\' ';
				break;
			case 'like_right':
				$this->sql .= '\'' . $value . '%\' ';
				break;
		}
		return $this;
	}

	/**
	 * 等于
	 * @param $value 值
	 * @return $this
	 */
	public function eq($value) {
		$this->sql .= ' =\'' . $value . '\'';
		return $this;
	}

	/**
	 * 大于
	 * @param $value 值
	 * @return $this
	 */
	public function gt($value) {
		$this->sql .= ' >\'' . $value . '\'';
		return $this;
	}

	/**
	 * 大于等于
	 * @param $value 值
	 * @return $this
	 */
	public function gte($value) {
		$this->sql .= ' >=\'' . $value . '\'';
		return $this;
	}

	/**
	 * 小于
	 * @param $value 值
	 * @return $this
	 */
	public function lt($value) {
		$this->sql .= ' <\'' . $value . '\'';
		return $this;
	}

	/**
	 * 小于等于
	 * @param $value 值
	 * @return $this
	 */
	public function lte($value) {
		$this->sql .= ' <=\'' . $value . '\'';
		return $this;
	}

	/**
	 * and
	 * @param $key 键
	 * @return $this
	 */
	public function and_where($key) {
		$this->sql .= ' and `' . $key . '`';
		return $this;
	}

	/**
	 * or
	 * @param $key 键
	 * @return $this
	 */
	public function or_where($key) {
		$this->sql .= ' or `' . $key . '`';
		return $this;
	}

	/**
	 * 左联表
	 * @param $table 连接的表(数组)
	 * @return $this
	 */
	public function left_join($table) {
		$this->join('left', $table);
		return $this;
	}

	/**
	 * 右联表
	 * @param $table 连接的表(数组)
	 * @return $this
	 */
	public function right_join($table) {
		$this->join('right', $table);
		return $this;
	}

	/**
	 * 内联表
	 * @param $table 连接的表(数组)
	 * @return $this
	 */
	public function inner_join($table) {
		$this->join('inner', $table);
		return $this;
	}

	/**
	 * 在某个集合里面
	 * @param $key 键
	 * @param $array 集合
	 * @return $this
	 */
	public function in($key, $array) {
		$this->sql .= ' where `' . $key . '` in (\'' . implode('\',\'', $array) . '\')';
		return $this;
	}

	/**
	 * 不在某个集合里面
	 * @param $key 键
	 * @param $array 集合
	 * @return $this
	 */
	public function not_in($key, $array) {
		$this->sql .= ' where `' . $key . '` not in (\'' . implode('\',\'', $array) . '\')';
		return $this;
	}

	/**
	 * 联表后的条件
	 * @param $on 条件
	 * @return $this
	 */
	public function on($on) {
		$this->sql .= ' on ' . $on;
		return $this;
	}

	/**
	 * 排序
	 * @param $order_by 排序的字段，例子array('a' => DESC, 'b' => ASC)
	 * @return $this
	 */
	public function order_by($order_by) {
		$this->sql .= ' order by ';
		foreach ($order_by as $k => $by) {
			$this->sql .= '`' . $k . '` ' . $by . ',';
		}
		$this->sql = substr($this->sql, 0, -1);
		return $this;
	}

	/**
	 * 分组
	 * @param $group_by 分组
	 * @return $this
	 */
	public function group_by($group_by) {
		$this->sql .= ' group by `' . implode('`,`', $group_by) . '`';
		return $this;
	}

	/**
	 * 限制条数
	 * @param $x
	 * @param $y
	 * @return $this
	 */
	public function limit($x, $y = 0) {
		$x = intval($x);
		$y = intval($y);
		if ($y > 0) {
			$this->sql .= ' limit ' . $x . ',' . $y;
		} else {
			$this->sql .= ' limit ' . $x;
		}
		return $this;
	}

	/**
	 * 执行SQL查询
	 * @param $sql sql语句
	 * @return 查询的结果集(字段大小写敏感)
	 */
	public function query($sql = '') {
		if ($sql) {
			$this->sql = $sql;
		}
		$result = $this->mysqli->query($this->sql);
		$this->clear();
		if ($result === false) {
			return $this->show_error();
		}
		if ($result === true) {
			return $this->affected_rows();
		}
		$rs = array ();
		while ($row = $result->fetch_assoc()) {
			$rs[] = $row;
		}
		$result->free_result();
		return $rs;
	}

	/**
	 * 设置字符集
	 * @param $charset 字符集
	 * @return 成功返回true
	 */
	public function charset($charset) {
		return $this->mysqli->set_charset($charset);
	}

	/**
	 * 创建数据库
	 * @param $db 数据库名
	 * @return 成功返回true
	 */
	public function create_db($db) {
		$this->sql = 'create database ' . $db;
		$this->query();
		$this->clear();
		return true;
	}

	/**
	 * 删除数据库
	 * @param $db 数据库名
	 * @return 成功返回true
	 */
	public function drop_db($db) {
		$this->sql = 'drop database ' . $db;
		$this->query();
		$this->clear();
		return true;
	}

	/**
	 * 删除数据表
	 * @param $table(无限参数)
	 * @return 成功返回true
	 */
	public function drop_table($table) {
		$this->sql = 'drop table ';
		$param = func_get_args();
		$this->sql .= '`' . implode('`,`', $param) . '`';
		$this->query();
		$this->clear();
		return true;
	}

	/**
	 * 清空数据表
	 * @param $table 表名
	 * @return 成功返回true
	 */
	public function truncate($table) {
		$this->sql = 'truncate `' . $table . '`';
		$this->query();
		$this->clear();
		return true;
	}

	/**
	 * 检查表
	 * @param $table 表名(无限参数)
	 */
	public function check($table) {
		$this->sql = 'check table ';
		$param = func_get_args();
		$this->sql .= '`' . implode('`,`', $param) . '`';
		$this->query();
		$this->clear();
		return true;
	}

	/**
	 * 分析表
	 * @param $table 表名(无限参数)
	 */
	public function analyze($table) {
		$this->sql = 'analyze table ';
		$param = func_get_args();
		$this->sql .= '`' . implode('`,`', $param) . '`';
		$this->query();
		$this->clear();
		return true;
	}

	/**
	 * 修复表
	 * @param $table 表名(无限参数)
	 */
	public function repair($table) {
		$this->sql = 'repair table ';
		$param = func_get_args();
		$this->sql .= '`' . implode('`,`', $param) . '`';
		$this->query();
		$this->clear();
		return true;
	}

	/**
	 * 优化表
	 * @param $table 表名(无限参数)
	 */
	public function optimize($table) {
		$this->sql = 'optimize table ';
		$param = func_get_args();
		$this->sql .= '`' . implode('`,`', $param) . '`';
		$this->query();
		$this->clear();
		return true;
	}

	/**
	 * 刷新表
	 * @param $table 表名
	 */
	public function flush($table) {
		$this->sql = 'flush table `' . $table . '`';
		$rs = $this->query();
		$this->clear();
		return $rs;
	}

	/**
	 * 备份
	 * @param $filename 文件名
	 * @param $db 数据库名(默认当前库)
	 * @param $table 数据表名(默认为所有表)
	 * @param $backup_data 是否备份数据(默认只备份数据结构)
	 */
	public function backup($filename, $db = '', $table = '', $backup_data = false) {
		filesystem::rm($filename);
		if ($db) {
			$this->db_name = $db;
			$this->mysqli->select_db($this->db_name);
		}
		set_time_limit(0);
		$table = $this->mysqli->query('show tables') or die($this->show_error());
		$temp_dir = sys_get_temp_dir();
		while ($t = $table->fetch_assoc()) {
			$table_name = '`' . $this->db_name . '`.`' . $t['Tables_in_' . $this->db_name] . '`';
			$rs_ = $this->mysqli->query('show create table ' . $table_name) or die($this->show_error());
			while ($row = $rs_->fetch_assoc()) {
				$rs = $row;
			}
			$data = $rs['Create Table'] . ";\r\n\r\n";
			if ($backup_data) {
				$result = $this->mysqli->query('show columns from ' . $table_name) or die($this->show_error());
				$insert = 'insert into ' . $t['Tables_in_' . $this->db_name] . ' (';
				while ($row = $result->fetch_assoc()) {
					$insert .= '`' . $row['Field'] . '`,';
				}
				$result->free_result();
				$insert = substr($insert, 0, -1);
				$insert .= ') values ';
				$result = $this->mysqli->query('select * from ' . $table_name) or die($this->show_error());
				$i = 0;
				while ($row = $result->fetch_assoc()) {
					$insert .= '(';
					foreach ($row as $r) {
						$insert .= '\'' . trim(addslashes($r)) . '\',';
					}
					$insert = substr($insert, 0, -1);
					$insert .= '),';
					$i += 1;
				}
				$result->free_result();
				if ($i > 0) {
					$data .= substr($insert, 0, -1) . ";\r\n\r\n";
				}
			}
			$data = preg_replace('/(?i)AUTO_INCREMENT=\d+\s+/', '', $data);
			filesystem::write($filename, $data, true);
		}
	}

	/**
	 * 联表
	 * @param $method 联表方式(left,right,inner)
	 * @param $table 连接的表(数组)
	 * @return $this
	 */
	private function join($method, $table) {
		return $this->sql .= ' ' . $method . ' join `' . $this->db_name . '`.`' . implode('`,`' . $this->db_name . '`.`', $table) . '`';
	}

	/**
	 * 设置要操作的表名
	 * @param $table 表名
	 */
	public function table($table) {
		$table = '`' . $this->db_name . '`.`' . $this->pre . $table . '`';
		$this->sql .= $table;
		return $table;
	}

	/**
	 * 增加数据(支持批量)
	 * @param $table 表名
	 * @param $key 键
	 * @param $value 值
	 * @return 最后操作的自增id
	 */
	private function add_data($table, $data) {
		$this->table($table);
		$column_str = $value_str = '(';
		$i = $row = 0;
		$backup_sql = $this->sql;
		foreach ($data as $column => $value) {
			if (is_array($value)) {
				foreach ($value as $k => $v) {
					if ($i <= 0) {
						$column_str .= '`' . $this->mysqli->real_escape_string($k) . '`,';
					}
					$value_str .= '\'' . $this->mysqli->real_escape_string($v) . '\',';
				}
				$i = $row += 1;
				$value_str = substr($value_str, 0, -1) . '),(';
				if ($row >= 10000) {
					$this->query($backup_sql . substr($column_str, 0, -1) . ') values ' . substr($value_str, 0, -3) . ')');
					$row = 0;
					$value_str = '(';
				}
			} else {
				$column_str .= '`' . $this->mysqli->real_escape_string($column) . '`,';
				$value_str .= '\'' . $this->mysqli->real_escape_string($value) . '\',';
			}
		}
		$offset = -1;
		if ($i >= 1) {
			$offset = -3;
		}
		$this->sql = $backup_sql . substr($column_str, 0, -1) . ') values ' . substr($value_str, 0, $offset) . ')';
		$this->query();
		return $this->last_insert_id();
	}

	/**
	 * 获取受影响的行数
	 * @return 受影响的行数
	 */
	private function affected_rows() {
		return $this->mysqli->affected_rows;
	}

	/**
	 * 获取上一次插入的id
	 * @return 最后一次操作的自增id
	 */
	private function last_insert_id() {
		return $this->mysqli->insert_id;
	}

	/**
	 * 清理全局变量
	 */
	private function clear() {
		$this->sql = null;
	}

	/**
	 * 输出数据库错误
	 * @return 数据库错误
	 */
	private function show_error() {
		return $this->mysqli->error;
	}
}

//常量定义


//数据库引擎
define('MYISAM', 'MyISAM');
define('MEMORY', 'MEMORY');
define('INNODB', 'InnoDB');

//排序
define('ASC', 'asc');
define('DESC', 'desc');

//like
define('LIKE', 'like');
define('LIKE_LEFT', 'like_left');
define('LIKE_RIGHT', 'like_right');
?>