<?php
/**
 * MongoDB操作类
 */
class shareMongodb {
	private $connection;
	private $db;
	private $host;
	private $port;
	private $user;
	private $pass;
	private $dbname;
	private $pre;
	private $options = array('fsync' => true);
	private $selects = array();
	private $wheres = array();
	private $sorts = array();
	private $limit = 999999;
	private $offset = 0;

	/**
	 * 构造函数
	 * @param $db_host 数据库主机
	 * @param $db_user 数据库用户
	 * @param $db_pass 数据库密码
	 * @param $db_pre 数据库前缀
	 * @param $db_name 数据库名称
	 * @param $db_port 数据库端口(默认27017)
	 */
	public function __construct($db_host, $db_user, $db_pass, $db_pre, $db_name, $db_port = 27017){
		$this->host = $db_host;
		$this->port = $db_port;
		$this->user = $db_user;
		$this->pass = $db_pass;
		$this->dbname = $db_name;
		$this->pre = $db_pre;
	}

	/**
	 * 析构函数
	 */
	public function __destruct(){
		$this->close();
	}

	/**
	 * 连接mongodb
	 */
	private function connect(){
		if($this->connection || $this->db){
			return;
		}
		$str = 'mongodb://'.$this->host.':'.$this->port.'/'.$this->dbname;
		if($this->user && $this->pass){
			$options['username'] = $this->user;
			$options['password'] = $this->pass;
		}
		if($options){
			$this->connection = new Mongo($str, $options);
		} else {
			$this->connection = new Mongo($str);
		}
		$this->db = $this->connection->{$this->dbname};
	}

	/**
	 * 获取mongodb版本
	 * @return mongodb版本
	 */
	public function version(){
		return Mongo::VERSION;
	}

	/**
	 * 插入数据(支持批量)
	 * @param $collection 表名
	 * @param $data 数据
	 * @param $need_inc 是否需要自增id
	 */
	public function insert($collection, $data, $need_inc = true){
		$this->connect();
		$is_batch = false;
		foreach($data as $d){
			$is_batch = is_array($d);
			break;
		}
		$collection = $this->pre.$collection;
		if($is_batch){
			$ok = $this->db->{$collection}->batchInsert($data, $this->options);
		} else {
			if($need_inc){
				$data['_id'] = $this->insert_inc($collection);
			}
			$ok = $this->db->{$collection}->insert($data, $this->options);
			$ok['_id'] = $data['_id'];
		}
		return $ok;
	}
	
	/**
	 * 保存数据
	 * @param $collection 表名
	 * @param $data 数据
	 */
	public function save($collection, $data){
		$this->connect();
		return $this->db->{$collection}->save($data);
	}

	/**
	 * 删除数据
	 * @param $collection 表名
	 * @param $where 条件
	 */
	public function delete($collection, $where = array()){
		$this->connect();
		$collection = $this->pre.$collection;
		$this->db->{$collection}->remove($where, $this->options);
	}

	/**
	 * 更新数据
	 * @param $collection 表名
	 * @param $where 条件
	 * @param $update 要更新的数据array('k'=>'v')
	 */
	public function update($collection, $where, $update){
		$this->connect();
		$collection = $this->pre.$collection;
		$this->db->{$collection}->update($where, array('$set' => $update), $this->options);
	}

	/**
	 * 切换数据库
	 * @param $database 数据库名
	 */
	public function switch_db($database){
		$this->dbname = $database;
		$this->db = $this->connection->{$this->dbname};
	}

	/**
	 * 查询数据
	 * @param $includes 要查询的字段
	 */
	public function select($includes = array()){
		foreach($includes as $col){
			$this->selects[$col] = 1;
		}
		return $this;
	}

	/**
	 * 条件
	 * @param $wheres
	 */
	public function where($wheres = array()){
		foreach($wheres as $wh => $val){
			$this->wheres[$wh] = $val;
		}
		return $this;
	}

	/**
	 * 在某个集合里面
	 * @param $field 字段
	 * @param $in 集合
	 */
	public function in($field, $in = array()){
		$this->where_init($field);
		$this->wheres[$field]['$in'] = $in;
		return $this;
	}

	/**
	 * 不在某个集合里面
	 * @param $field 字段
	 * @param $in 集合
	 */
	public function not_in($field, $in = array()){
		$this->where_init($field);
		$this->wheres[$field]['$nin'] = $in;
		return $this;
	}

	/**
	 *大于
	 * @param $field 字段
	 * @param $x 值
	 */
	public function gt($field, $x){
		$this->where_init($field);
		$this->wheres[$field]['$gt'] = $x;
		return $this;
	}

	/**
	 * 大于等于
	 * @param $field 字段
	 * @param $x 值
	 */
	public function where_gte($field, $x){
		$this->where_init($field);
		$this->wheres[$field]['$gte'] = $x;
		return $this;
	}

	/**
	 * 小于
	 * @param $field 字段
	 * @param $x 值
	 */
	public function lt($field, $x){
		$this->where_init($field);
		$this->wheres[$field]['$lt'] = $x;
		return $this;
	}

	/**
	 * 小于等于
	 * @param $field 字段
	 * @param $x 值
	 */
	public function lte($field, $x){
		$this->where_init($field);
		$this->wheres[$field]['$lte'] = $x;
		return $this;
	}

	/**
	 * 在x - y之间[x,y]
	 * @param $field 字段
	 * @param $x
	 * @param $y
	 */
	public function between($field, $x, $y){
		$this->where_init($field);
		$this->wheres[$field]['$gte'] = $x;
		$this->wheres[$field]['$lte'] = $y;
		return $this;
	}

	/**
	 * 在x - y之间(x,y)
	 * @param $field 字段
	 * @param $x
	 * @param $y
	 */
	public function between_ne($field, $x, $y){
		$this->where_init($field);
		$this->wheres[$field]['$gt'] = $x;
		$this->wheres[$field]['$lt'] = $y;
		return $this;
	}

	/**
	 * 不等于
	 * @param $field 字段
	 * @param $x 值
	 */
	public function not_eq($field, $x){
		$this->where_init($field);
		$this->wheres[$field]['$ne'] = $x;
		return $this;
	}

	/**
	 * 或
	 * @param $field 字段
	 * @param $values 值
	 */
	public function where_or($field, $values){
		$this->where_init($field);
		$this->wheres[$field]['$or'] = $values;
		return $this;
	}

	/**
	 * 不存在
	 * @param $field 字段
	 * @param $values 值
	 */
	public function exists($field, $values){
		$this->where_init($field);
		$this->wheres[$field]['$exists'] = $values;
		return $this;
	}

	/**
	 * 与
	 * @param $elements_values
	 */
	public function where_and($elements_values = array()){
		foreach($elements_values as $element => $val) {
			$this->wheres[$element] = $val;
		}
		return $this;
	}

	/**
	 * 求模
	 * @param $field
	 * @param $num
	 * @param $result
	 */
	public function mod($field, $num, $result){
		$this->where_init($field);
		$this->wheres[$field]['$mod'] = array($num, $result);
		return $this;
	}

	/**
	 * size
	 * @param $field
	 * @param $size
	 */
	public function size($field, $size){
		$this->_where_init($field);
		$this->wheres[$field]['$size'] = $size;
		return $this;
	}

	/**
	 * like
	 * @param $field 字段
	 * @param $value 值
	 * @param $flags 是否忽略大小写(默认忽略)
	 * @param $enable_start_wildcard 是否开启正则开始符号
	 * @param $enable_end_wildcard 是否开启正则结束 符号
	 */
	public function like($field, $value, $flags = "i", $enable_start_wildcard = true, $enable_end_wildcard = true){
		$field = trim($field);
		$this->where_init($field);
		$value = trim($value);
		if($enable_start_wildcard !== true){
			$value = '^' . $value;

			if($enable_end_wildcard !== true){
				$value .= '$';
			}
			$regex = '/'.$value.'/'.$flags;
			$this->wheres[$field] = new MongoRegex($regex);
			return $this;
		}
	}

	/**
	 * order by
	 * @param $fields 根据哪些字段排序(array('key'=>1, 'kk'=>-1))
	 */
	public function order_by($fields){
		foreach($fields as $col => $val){
			if($val == -1 || $val === false || strtolower($val) == 'desc'){
				$this->sorts[$col] = -1;
			} else {
				$this->sorts[$col] = 1;
			}
		}
		return $this;
	}

	/**
	 * 获取所有表
	 */
	public function get_collections(){
		$rs = $this->db->listCollections();
		foreach ($rs as $collection) {
			$list[] = trim(str_replace($this->dbname.'.', '', $collection));
		}
		sort($list);
		return $list;
	}

	/**
	 * 限制条数
	 * @param $x
	 */
	public function limit($x, $y = 0) {
		$this->offset($x);
		$y = intval($y);
		if($y > 0){
			$this->limit = $y;
		}
		return $this;
	}

	/**
	 * 设置偏移量
	 * @param $x 偏移量
	 */
	public function offset($x = 0){
		$x = intval($x);
		if($x >= 1){
			$this->offset = $x;
		}
		return $this;
	}

	/**
	 * 读取一条数据
	 * @param $collection 表名
	 * @param $where 条件
	 */
	public function find_one($collection, $where = array()){
		$collection = $this->pre.$collection;
		return $this->where($where)->limit(1)->get($collection);
	}

	/**
	 * 统计某个字段的总值
	 * @param $collection 集合名
	 * @param $field 字段名
	 * @param $limit 限制条数
	 */
	public function sum($collection, $field, $limit){
		$total = 0;
		$collection = $this->pre.$collection;
		$cursor = $this->db->{$collection}->find($this->wheres, $this->selects)->limit($limit)->skip($this->offset);
		foreach($cursor as $rs){
			$total += intval($rs[$field]);
		}
		return $total;
	}

	/**
	 * 获取数据
	 * @param $collection 表名
	 */
	public function get($collection){
		$this->connect();
		$collection = $this->pre.$collection;
		$results = array();
		$documents = $this->db->{$collection}->find($this->wheres, $this->selects)->limit((int) $this->limit)->skip((int) $this->offset)->sort($this->sorts);
		$returns = array();
		foreach($documents as $doc){
			$returns[] = $doc;
		}
		$this->clear();
		return $returns;
	}

	/**
	 * 统计某个表的数据条数
	 * @param $collection 表名
	 */
	public function count($collection){
		$this->connect();
		$collection = $this->pre.$collection;
		$count = $this->db->{$collection}->find($this->wheres)->limit($this->limit)->skip($this->offset)->count();
		$this->clear();
		return($count);
	}
	
	/**
	 * 自增修改
	 * @param $table 表名
	 * @param $data 数据
	 * @param $column 字段名
	 * @param $inc 自增量
	 */
	public function update_inc($table, $data, $column, $inc = 1){
		$this->connect();
		$update = array('$inc'=>array($column=>$inc));
		$query = $data;
		$command = array(
			'findandmodify'=>$table, 
			'update'=>$update,
			'query'=>$query, 
			'new'=>true, 
			'upsert'=>true
		);
		$id = $this->db->command($command);
		return intval($id['value'][$column]);
	}

	/**
	 * 自增ID实现
	 * @param $table 表名
	 */
	private function insert_inc($table){
		$update = array('$inc'=>array('id'=>1));
		$query = array('table'=>$table);
		$command = array(
			'findandmodify'=>'_increase', 
			'update'=>$update,
			'query'=>$query, 
			'new'=>true, 
			'upsert'=>true
		);
		$id = $this->db->command($command);
		return intval($id['value']['id']);
	}

	/**
	 * 获取表最后一次插入的id值
	 * @param 表名 $collection
	 */
	public function get_last_insertid($collection){
		$collection = $this->pre.$collection;
		$result = $this->where(array('table'=>$collection))->get('_increase');
		$this->clear();
		return intval($result[0]['id']);
	}

	/**
	 * 更新表最后一次插入的id值
	 * @param 表名  $collection
	 * @param 更新的id值 $id
	 */
	private function update_insertid($collection, $id){
		$this->connect();
		$collection = $this->pre.$collection;
		return $this->where(array('table'=>$collection))->update('_increase', array('id'=>$id));
	}

	/**
	 * 添加一个索引
	 * @param $collection 表名
	 * @param $keys 索引字段 (array('key'=>1, 'kk'=>-1))
	 */
	public function add_index($collection, $keys = array()){
		foreach($keys as $col => $val){
			if($val == -1 || $val === false || strtolower($val) == 'desc'){
				$keys[$col] = -1;
			} else {
				$keys[$col] = 1;
			}
		}
		$this->connect();
		$collection = $this->pre.$collection;
		return $this->db->{$collection}->ensureIndex($keys, $this->options);
	}

	/**
	 * 删除一个索引
	 * @param $collection 表名
	 * @param $keys $val
	 */
	public function remove_index($collection, $keys = array()){
		$this->connect();
		$collection = $this->pre.$collection;
		return $this->db->{$collection}->deleteIndex($keys, $this->options);
	}

	/**
	 * 删除所有索引
	 * @param $collection 表名
	 */
	public function remove_all_indexes($collection) {
		$this->connect();
		$collection = $this->pre.$collection;
		return $this->db->{$collection}->deleteIndexes();
	}

	/**
	 * 列出所有索引
	 * @param $collection 表名
	 */
	public function list_indexes($collection) {
		$this->connect();
		$collection = $this->pre.$collection;
		return $this->db->{$collection}->getIndexInfo();
	}

	/**
	 * 删除某个表
	 * @param $collection 表名
	 */
	public function drop_collection($collection){
		$this->connect();
		$collection = $this->pre.$collection;
		return $this->db->{$collection}->drop();
	}

	/**
	 * 清理所有成员变量
	 */
	public function clear(){
		$this->selects = array();
		$this->wheres = array();
		$this->limit = null;
		$this->offset = null;
		$this->sorts = array();
	}

	/**
	 * where 初始化
	 * @param $param 参数
	 */
	private function where_init($param){
		if(!isset($this->wheres[$param])){
			$this->wheres[$param] = array();
		}
	}

	/**
	 *	关闭数据库
	 */
	public function close(){
		if($this->connection){
			$this->connection->close();
		}
	}

	/**
	 * select distinct
	 * @param string $collection 表名
	 * @param string $column 字段(支持数组)
	 * @param array $query 查询条件(默认为空)
	 * @return array
	 */
	public function findDistinct($collection, $column, $query = array()){
		$this->connect();
		$collection = $this->pre.$collection;
		if(is_array($column)){
			//如果是数组，循环取出所有唯一的键值
			foreach($column as $c){
				$rs = $this->db->command(array('distinct' => $collection, 'key' => $c, 'query' => $query));
				$return[$c] = $rs['values'];
			}
		} else {
			$rs = $this->db->command(array('distinct' => $collection, 'key' => $column, 'query' => $query));
			$return = $rs['values'];
		}
		return $return;
	}
}
?>