<?php
/**
 * 数据库工具,不支持切换数据库操作
 * @author Colin <15070091894@163.com>
 */
namespace system\Tool;
class DataBase{
	protected $db;							//数据库操作句柄
	protected $sql;							//最后生成sql语句
	protected $charset = 'utf8';			//默认字符格式
	protected $encoding = 'utf8_general_ci';//默认字符编码
	protected $engine = 'MyISAM';			//默认表引擎
	protected $comment;						//表的注释
	protected $attr;						//字段组，字段信息组
	protected $tables = null;				//选中数据表名，可带全缀，可不带
	protected $prefix = null;				//数据表前缀
	

	/**
	 * 构造方法初始化
	 * @author Colin <15070091894@163.com>
	 */
	public function __construct(){
		$this->db = \system\ObjFactory::getIns();
		$this->prefix = Config('DB_PREFIX');
	}

	/**
	 * 内部set方法
	 * @param string $key   键。相对应的本类的成员
	 * @param string $value 值。相对于的本类成员的值
	 * @author Colin <15070091894@163.com>
	 */
	public function __set($key , $value){
		$this->$key = $value;
	}
	
	/**
	 * get方法
	 */
	public function __get($key){
		return $this->$key;
	}
	
	/**
	 * 选中数据表操作
	 * @param string $table 选择的数据表
	 * @author Colin <15070091894@163.com>
	 */
	public function useTable($table = null){
		$this->tables = $this->prefix.$table;
		$this->db->CheckTables($this->tables);
		return $this;
	}

	/**
	 * 创建数据库
	 * @param string $database 创建的数据库名
	 * @return int 返回创建状态
	 * @author Colin <15070091894@163.com>
	 */
	public function createDatabase($database = null){
		$this->sql = "CREATE DATABASE `$database` DEFAULT CHARACTER SET $this->charset COLLATE $this->encoding;";
		return $this->execute($this->sql);
	}

	/**
	 * 删除数据库操作
	 * @param string $database 数据库名
	 * @return bool 返回删除状态
	 * @author Colin <15070091894@163.com>
	 */
	public function dropDatabase($database = null){
		$this->sql = "DROP DATABASE `$database`";
		return $this->execute($this->sql);
	}
	
	/**
	 * 删除数据表操作
	 * @param string $table 数据表名
	 * @return bool 返回删除状态
	 * @author Colin <15070091894@163.com>
	 */
	public function dropTable($table = null){
		$this->sql = "DROP TABLE `$this->prefix$table`";
		return $this->execute($this->sql);
	}

	/**
	 * 创建表
	 * @param string $tablename 表名
	 * @param array $fields 字段数组
	 * @return 无返回值
	 * @author Colin <15070091894@163.com>
	 */
	public function createTable($tablename = null , $fields = null){
		$this->sql = "CREATE TABLE IF NOT EXISTS `$this->prefix$tablename`(";
		if(is_array($fields) && !is_null($fields)){
			$this->sql .= $this->key('id' , 'int' , 11 , true , ',');
			$this->attr = $fields;
			$this->tables = $this->prefix . $tablename;
			$this->_parse_fieldinfo(',');
			$this->sql = substr($this->sql , 0 , -1);
		}else{
			$this->sql .= $this->key();
		}
		$this->sql .= ") ENGINE='$this->engine' DEFAULT CHARSET=$this->charset COMMENT='$this->comment' AUTO_INCREMENT=1";
		return $this->execute($this->sql);
	}

	/**
	 * 创建主键
	 * @param string $name 主键名称
	 * @param string $type 主键类型
	 * @param int $len 主键长度
	 * @param boolen $auto_increment 主键是否自动增长
	 */
	public function key($name = 'id' , $type = 'int' , $len = 11 , $auto_increment = true , $separator = null){
		$this->sql .= "`$name` $type($len) not null primary key ";
		$this->sql .= $auto_increment ? "AUTO_INCREMENT " : ' ';
		$this->sql .= $separator;
	}

	/**
	 * 创建字段
	 * @param string $fields 字段属性数组
	 * @param 格式为 array('字段名' , '字段类型' , '字段长度' , '是否为空' , '是否有默认值' , '字段注释' , '额外属性')
	 * @author Colin <15070091894@163.com>
	 */
	public function createFields($fields = array()){
		if(empty($this->tables)){
			throw new \system\MyError('请先选择数据库。在进行操作');
		}
		$this->attr = $fields;
		$this->_parse_fieldinfo();
		return $this->execute($this->sql);
	}

	/**
	 * 删除字段
	 * @param string $field 字段名
	 * @author Colin <15070091894@163.com>
	 * @return boolen 删除状态
	 */
	public function dropField($field){
		$this->sql = "ALTER TABLE `$this->tables` DROP `$field`;";
		$this->execute($this->sql);
		return true;
	}
	
	/**
	 * 清空表
	 * @param string $table 清空的表名
	 * @author Colin <15070091894@163.com>
	 */
	public function truncate($table = null){
		$this->sql = "TRUNCATE $table";
		$this->execute($this->sql);
	}

	/**
	 * 执行数据库操作
	 * @param string $sql 被执行的sql语句
	 * @author Colin <15070091894@163.com>
	 */
	public function execute($sql = null){
		if(!empty($sql)){
			$this->sql = $sql;
		}
		$separator = array_filter(explode(';', $this->sql));
		foreach ($separator as $key => $value) {
			$query = $this->db->query($value);
			if(!$query){
				throw new \system\MyError('sql解析错误：' . $this->db->showerror());
			}
		}
		return $query;
	}

	/**
	 * 解析创建字段信息
	 * @return null
	 * @author Colin <15070091894@163.com>
	 */
	protected function _parse_fieldinfo($separator = ';'){
		//sql 创建字段语句
		// ALTER TABLE `表名` ADD `字段名` int(10) not null default 0 comment '字段备注';
		//array('字段名' , '字段类型' , '字段长度' , '是否为空' , '是否有默认值' , '字段注释' , '额外属性')
		if(is_array($this->attr)){
			if(is_array($this->attr[0])){
				//解析多个字段
				foreach ($this->attr as $key => $value) {
					$this->merge_sql($value[0] , $value[1] , $value[2] , $value[3] , $value[4] , $value[5] , $value[6] , $separator);
				}
			}else{
				$this->merge_sql($this->attr[0] , $this->attr[1] , $this->attr[2] , $this->attr[3] , $this->attr[4] , $this->attr[5] , $this->attr[6] , $separator);
			}
		}
	}

	/**
	 * 组装sql语句
	 * @param string $field 字段名
	 * @param string $type 字段名
	 * @param int $length 字段长度
	 * @param string $null 字段是否为空，如果为空，则为null
	 * @param string $default 字段默认值，如果没有默认值，则为null
	 * @param string $comment 字段备注
	 * @param string $extra 额外值。例如unsigned
	 * @param string $separator 是否拥有分隔符例如,
	 * @author Colin <15070091894@163.com>
	 */
	protected function merge_sql($field = 'id' , $type = 'int'  , $length = 11 , $null = 'NULL' , $default = null , $comment = null , $extra = null , $separator = null){
		//检查字段是否存在
		if(!$this->db->CheckFields($this->tables , $field)){
			throw new \system\MyError($this->tables . '表中已存在该字段' . $field);
		}
		//解析null值
		if(!is_null($null)){
			$null = 'NOT NULL';
		}
		//解析default值
		if(!is_null($default)){
			$default = 'DEFAULT ' . is_string($default) && $default ? "'$default'" : $default;
		}
		//解析comment值
		if(!is_null($comment)){
			$comment = "COMMENT '$comment'";
		}
		//解析额外参数
		if(!is_null($extra)){
			$length = $length . ' ' . $extra;
		}
		//解析length参数
		if(!is_null($length)){
			$length = '(' . $length . ')';
		}
		//解析分隔符
		if($separator == ','){
			$this->sql .= "`$field` $type$length $null $default $comment$separator";
		}else{
			$this->sql .= "ALTER TABLE `$this->tables` ADD `$field` $type$length $null $default $comment$separator";
		}
	}
}