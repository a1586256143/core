<?php
/**
 * 模型操作
 * @author Colin <15070091894@163.com>
 */
namespace system;
class Model{
	protected $db = '';					//数据库句柄
	protected $db_prefix = '';			//获取数据表前缀
	protected $DataName = '';			//获取数据库名
	protected $Tables;					//多表查询数据库名，必须带数据前缀
	protected $TableName = '';
	protected $From;					//From表名
	protected $Fields = '*';			//多表查询字段名
	protected $TrueTables = '';			//数据表的真实名字
	protected $DataNameName = '';		//数据表判断后存放的字段
	protected $Where = array();			//where字段
	protected $value = array();			//where value 
	protected $WhereOR = "AND";			//where 条件的 OR and
	protected $Sql = '';				//sql语句
	protected $ParKey = '';				//解析后存放的字段
	protected $Parvalue = '';			//解析后存放的字段
	protected $Alias = '';				//字符别名
	protected $Limit = '';				//limit
	protected $Order = '';				//order
	protected $auto = array();			//自动完成
	protected $validate = array();		//自动验证
	protected $data = array();			//保存数据
	protected $callback = '';			//回调时使用的操作句柄
	protected $Join = array();			//join
	protected $startTransaction = 0;	//开启事务
	const MODEL_INSERT = 1;				//新增时操作
	const MODEL_UPDATE = 2;				//修改时操作
	const MODEL_BOTH = 3;				//所有操作
	
	/**
	 * 构造方法
	 * @author Colin <15070091894@163.com>
	 */
	final public function __construct($tables = null){
		//设置类成员
		self::setClassMember();
		//数据库信息是否填写
		self::CheckConnectInfo();
		//获取数据库对象
		$this->db = ObjFactory::getIns();
		//如果表名为空，并且TableName为空
		if(empty($tables) && !$this->TableName){
			// 处理是否有实例化类名
			$this->TableName = array_pop(explode('\\' , get_class($this)));
			$currentModelName = array_pop(explode('\\' , __CLASS__));
			if($this->TableName == $currentModelName){
				return $this;
			}
		}
		//是否设置表名
		if($this->TableName){
			$tables = $this->TableName;
		}
		//执行判断表方法
		$this->TablesType($tables);
		//确认表是否存在
		$this->db->CheckTables($this->db_prefix . $this->DataName , $this->db_tabs);
		//初始化回调函数的句柄
		$this->callback = $tables;
		return $this;
	}

	/**
	 * From函数
	 * @param  tables 表名
	 * @author Colin <15070091894@163.com>
	 */
	public function from($tables = null){
		$tables = $tables === null ? $this->TablesName : $tables;
		$tables = $this->_parse_prefix($tables);
		$this->From = ' FROM ' . $tables;
		return $this;
	}
	
	/**
	 * field方法
	 * @param field 字段名
	 * @author Colin <15070091894@163.com>
	 */
	public function field($field){
	    if(!empty($field)){
	        $this->Fields = $field;
	    }
	    return $this;
	}

	/**
	 * create方法
	 * @param  data 创建对象的数据
	 * @author Colin <15070091894@163.com>
	 */
	public function create($data = array()){
		if(!$data) $data = values('post.');
		//获取表所有字段
		$fields = $this->getFields();
		foreach ($fields as $key => $value) {
			if(isset($data[$value])){
				if($data[$value] === null || $data[$value] == ''){
					continue;
				}
				$fieldData[$value] = $data[$value];
			}
		}
		//去除空值
		$this->data['create'] = myclass_filter($fieldData);
		//自动完成
		if(!empty($this->auto)){
			$this->_parse_auto();
			//合并自动完成数据
			$this->data['create'] = myclass_filter(array_merge($this->data['auto'] , $this->data['create']));
		}
		if(!empty($this->validate)){
			$this->_parse_validate();
			//合并自动验证数据
			$this->data['create'] = myclass_filter(array_merge($this->data['validate'] , $this->data['create']));
		}
		return $this->data['create'];
	}

	/**
	 * 条件
	 * @param fuild 字段名称
	 * @param wherevalue 字段值
	 * @param whereor OR和AND
	 * @param sub 操作符号 可以为 =,!=,in,not in,between,not between 
	 * @author Colin <15070091894@163.com>
	 */
	public function where($field , $wherevalue = null , $whereor = null , $sub = '='){
		$fieldlen = count($field);
		if($whereor !== null) $this->WhereOR = $whereor;
		if($field == null) return $this;
		//遍历字段
		if(is_array($field)){
			//判断是否为多条数据
			$fieldlen > 1 ? extract($this->parseWhere($field , true , $sub)) : extract($this->parseWhere($field , false , $sub));
		}else {
			//非数组
			extract($this->parseWhere(array($field => array($sub , $wherevalue)) , false , $sub));
		}
		if($tmp){
			$this->Where[] = $tmp;
		}
		return $this;
	}

	/**
	 * 获取主键
	 * @author Colin <15070091894@163.com>
	 */
	public function getpk(){
		$pk = S('TABLE_PK_FOR_'.$this->DataName);
		if(empty($pk)){
			$pk = $this->execute("SELECT COLUMN_NAME FROM information_schema.`KEY_COLUMN_USAGE` WHERE TABLE_SCHEMA = '$this->db_tabs' AND TABLE_NAME = '$this->db_prefix$this->DataName' LIMIT 1");
			S('TABLE_PK_FOR_' . $this->DataName , $pk);
		}
		return $pk['COLUMN_NAME'];
	}

	/**
	 * 执行源生的sql语句
	 * @param sql sql语句
	 * @author Colin <15070091894@163.com>
	 */
	public function query($sql = null){
		$sql = $sql === null ? $this->Sql : $sql;
		$this->Where = array();
		return $this->getResult($sql);
	}

	/**
	 * 查询函数
	 * @author Colin <15070091894@163.com>
	 */
	public function select(){
	    $this->getSql();
		return $this->getResult(null , true);
	}

	/**
	 * 查询一条数据
	 * @author Colin <15070091894@163.com>
	 */
	public function find($pk = null){
		if($pk){
			$value = !is_numeric($pk) ? "'$pk'" : $pk;
			$this->where($this->getPk() , $value);
		}
		$this->getSql();
		return $this->getResult();
	}

	/**
	 * 获取最后执行的sql语句
	 * @author Colin <15070091894@163.com>
	 */
	public function getLastSql(){
		return $this->Sql;
	}

	/**
	 * 查询数据库条数
	 * @author Colin <15070091894@163.com>
	 */
	public function count(){
		$pk = $this->getpk();
		$result = $this->field('count(' . $pk . ') as count')->find();
		$this->Fields = '*';
		return $result['count'] ? $result['count'] : 0;
	}
	
	/**
	 * 插入数据
	 * @param values   要插入的数据
	 * @author Colin <15070091894@163.com>
	 */
	public function insert($values = null){
		$values = myclass_filter($values);
        if(!$values){
            $values = $this->data['create'];
        }
		$this->ParData('ist' , $values);
		$this->Sql = "INSERT INTO " . $this->TablesName . "(" . $this->ParKey . ") VALUES (" . $this->Parvalue . ")";
		return $this->ADUP($this->Sql , 'ist');
	}
	
	/**
	 * 删除函数
	 * @param field 被删除的字段
	 * @param value 唯一标示符
	 * @author Colin <15070091894@163.com>
	 */
	public function delete($value , $field = null){
		$field = $field === null ? $this->getpk() : $field;
		if($this->Where[0] === null){
			$this->where($field , $value);
		}
		$where = $this->getWhere();
		$this->Sql = "DELETE FROM " . $this->TablesName . $where;
		return $this->ADUP($this->Sql , 'upd');
	}
	
	/**
	 * 修改函数
	 * @param field	要被修改的字段
	 * @param value	要被修改的值
	 * @author Colin <15070091894@163.com>
	 */
	public function update($field , $value = null){
		if(is_string($field)){
			if(!$this->ParKey){
				$this->ParKey = ' SET ' . '`' . $field . '`' . "='" . $value . "'";
			}
		}else if(is_array($field)){
			foreach ($field as $key => $value) {
				if($value === ''){
					continue;
				}
				$data[$key] = addslashes($value);
			}
			$this->ParData('upd',$data);
		}
		$where = $this->getWhere();
		$this->Sql = "UPDATE " . $this->TablesName . $this->ParKey . $where;
		return $this->ADUP($this->Sql , 'upd');
	}

	/**
	 * 左连接
	 * @return [type] [description]
	 */
	public function join($join , $method = 'LEFT'){
		$join = $this->_parse_prefix($join);
		$this->Join[] = ' ' . $method . ' JOIN ' . $join;
		return $this;
	}

	/**
	 * 增加字段值
	 * @return [type] [description]
	 */
	public function incField($field , $num = 1){
		$this->ParKey = ' SET ' . '`' . $field . '`' . "=$field + $num";
		return $this->update($field);
	}

	/**
	 * 减少字段值
	 * @return [type] [description]
	 */
	public function decField($field , $num = 1){
		$this->ParKey = ' SET ' . '`' . $field . '`' . "=$field - $num";
		return $this->update($field);
	}

	/**
	 * limt
	 * @param num 查询结果集的数量 0,10
	 * @author Colin <15070091894@163.com>
	 */
	public function limit($start = 0 , $end = null){
		if(!empty($start) && $end){
			$start = ($start - 1) * $end;
			$str = $start . ',' . $end;
		}else{
			$str = $start;
		}
		$this->Limit = " LIMIT " . $str;
		return $this;
	}

	/**
	 * order
	 * @author Colin <15070091894@163.com>
	 */
	public function order($field , $desc = null){
		$this->Order = " ORDER BY " . $field . " " . $desc . " ";
		return $this;
	}
	
	/**
	 * 别名
	 * @param as 新的别名
	 * @author Colin <15070091894@163.com>
	 */
	public function alias($as = 'alias'){
		$this->Alias = ' AS ' . $as;
		return $this;
	}
	
	/**
	 * 求最大值
	 * @param fuild  要求出最大值的数值
	 * @author Colin <15070091894@163.com>
	 */
	public function max($field){
		$this->setDefaultAs($field);
		return $this->field("MAX($field)$this->Alias")->find();
	}
	
	/**
	 * 最小值
	 * @param field   要被求出最小值的字段
	 * @author Colin <15070091894@163.com>
	 */
	public function min($field){
		$this->setDefaultAs($field);
		return $this->field("MIN($field)$this->Alias")->find();
	}
	
	/**
	 * 某个字段求和
	 * @param field 要被求和的字段
	 * @author Colin <15070091894@163.com>
	 */
	public function sum($field){
		$this->setDefaultAs($field);
		return $this->field("SUM($field)$this->Alias")->find();
	}
	
	/**
	 * 求平均值
	 * @param field 平均值的字段
	 * @author Colin <15070091894@163.com>
	 */
	public function avg($field){
		$this->setDefaultAs($field);
		return $this->field("AVG($field)$this->Alias")->find();
	}

	/**
	 * in
	 * @param field 字段
	 * @param values 值
	 * @author Colin <15070091894@163.com>
	 */
	public function in($field , $values){
		$this->in_common($field , $values , 'in ');
		return $this;
	}

	/**
	 * not in
	 * @param field 字段
	 * @param values 值
	 * @author Colin <15070091894@163.com>
	 */
	public function notin($field , $values){
		$this->in_common($field , $values , 'not in ');
		return $this;
	}

	/**
	 * like
	 * @param field 要被like的字段名
	 * @param value like的值
	 * @author Colin <15070091894@163.com>
	 */
	public function like($field , $value){
		return $this->where($field , $value , null , 'LIKE ');
	}

	/**
	 * between
	 * @param field 要被between的字段名
	 * @param between between的值 格式为 1,2
	 * @author Colin <15070091894@163.com>
	 */
	public function between($field , $between){
		$this->between_common($field , $between , 'BETWEEN');
		return $this;
	}

	/**
	 * between
	 * @param field 要被between的字段名
	 * @param between between的值 格式为 1,2
	 * @author Colin <15070091894@163.com>
	 */
	public function notbetween($field , $between){
		$this->between_common($field , $between , 'NOT BETWEEN');
		return $this;
	}

	/**
     * 执行源生sql语句并返回结果
     * @param sql 要执行的sql语句
     * @author Colin <15070091894@163.com>
     */
	public function execute($sql){
		return $this->db->execute($sql);
	}

	/**
	 * 执行原声sql语句，返回资源类型
	 * @param sql 要执行的sql语句
     * @author Colin <15070091894@163.com>
	 */
	public function execute_resource($sql){
		return $this->ADUP($sql);
	}

	/**
	 * 获取下一条数据
	 * @param  id 获取下一条数据的ID
	 * @param  field 查询字段
	 * @author Colin <15070091894@163.com>
	 */
	public function next($id , $field = '*'){
		return $this->field($field)->where('id' , $id , null , '>')->find();
	}

	/**
	 * 获取上一条数据
	 * @param  id 获取下一条数据的ID
	 * @param  field 查询字段
	 * @author Colin <15070091894@163.com>
	 */
	public function prev($id , $field = '*'){
		return $this->field($field)->where('id' , $id , null , '<')->find();
	}

	/**
     * 开启事务处理
     * @author Colin <15070091894@163.com>
     */
    public function startTransaction(){
    	$this->startTransaction = 1;
        return $this->db->startTransaction();
    }

    /**
     * 回滚事务处理
     * @author Colin <15070091894@163.com>
     */
    public function rollback(){
        return $this->db->rollback();
    }

    /**
     * 提交事务处理
     * @author Colin <15070091894@163.com>
     */
    public function commit(){
        return $this->db->commit();
    }

	/**
	 * 容错处理机制
	 * @author Colin <15070091894@163.com>
	 */
	public function __call($fun , $param=null){
		ShowMessage($fun . '()这个方法不存在！');
	}

	/**
     * 静态方法容错处理机制
     * @author Colin <15070091894@163.com>
     */
	static public function __callStatic($fun , $param=null){
		ShowMessage(__METHOD__ . '()这个方法不存在！');
	}

	/**
	 * invoke方法  处理吧类当成函数来使用
	 * @author Colin <15070091894@163.com>
	 */
	public function __invoke(){
		ShowMessage(__CLASS__ . '这不是一个函数');
	}

	/**
	 * 获取所有字段
	 * @param  tables 表名
	 * @author Colin <15070091894@163.com>
	 */
	protected function getFields($tables = null){
		if(!$tables) $tables = $this->DataName;
		//缓存字段信息
		$fields = S($tables . '_field_cache');
		if(!$fields){
			$fields = $this->db->getFields($tables);
			S($tables . '_field_cache' , $fields);
		}
		return $fields;
	}

	/**
	 * 设置默认别名
	 * @param string $field 别名字段
	 * @author Colin <15070091894@163.com>
	 */
	protected function setDefaultAs($field = null){
		if(!$this->Alias){
			$this->Alias = ' AS ' . $field;
		}
	}

	/**
	 * 得到查询的sql语句
	 * @author Colin <15070091894@163.com>
	 */
	protected function getSql(){
		if($this->Tables != null){
	        $prefix = "SELECT $this->Fields FROM " . $this->Tables . $this->Alias;
	    }else {
	        $prefix = "SELECT $this->Fields " . $this->From . $this->Alias;
	    }
	    $where = $this->getWhere();
	    $this->Sql = $prefix . implode(' ' , $this->Join) . $where . $this->Order . $this->Limit;
	    return $this->Sql;
	}

	/**
	 * 判断类型
	 * @author Colin <15070091894@163.com>
	 */
	protected function TablesType($tables){
		$tables = $this->parTableName($tables);
		//转小写
		$this->DataName = strtolower($tables);
		//空格转换
		$spaceIndex = strpos($this->DataName , ' ');
		if($spaceIndex !== false){
			$as = substr($this->DataName , $spaceIndex);
			$this->DataName = substr($this->DataName , 0 , $spaceIndex);
		}
		$this->TablesName = empty($this->TrueTables) ? '`' . $this->db_prefix . $this->DataName . '`' : '`' . $this->TrueTables . '`';
		$this->from($this->TablesName . $as);
	}

	/**
	 * 解析表名的大写
	 * @author Colin <15070091894@163.com>
	 */
	protected function parTableName($tables){
		$tablename = myclass_filter(preg_split('/(?=[A-Z])/' , $tables));
		$tablename = implode('_' , $tablename);
		return $tablename;
	}

	/**
	 * 解析auto参数
	 * array('字段名' , '完成规则' , '完成条件' , '附加规则(结合完成规则使用)') 
	 * @author Colin <15070091894@163.com>
	 */
	protected function _parse_auto(){
		$fields = $this->getFields();
		$primary = $this->getPk();
		//遍历自动完成属性
		foreach ($this->auto as $key => $value) {
			//查找是否符合字段需求
			if(in_array($value[0], $fields)){
				$value[2] = $value[2] ? $value[2] : self::MODEL_INSERT;
				//解析处理状态
				if(!empty($value[2]) && $value[2] != self::MODEL_BOTH){
					switch($value[2]){
						case self::MODEL_INSERT :
							//查找主键是否存在，存在则是新增，不存在则是更改
							if(array_key_exists($primary , $this->data['create'])){
								$this->data['auto'][$value[0]] = null;
								return null;
							}
							break;
							//解析类型
						case self::MODEL_UPDATE : 
							//查找主键是否存在，存在则是更改，不存在则是新增
							if(!array_key_exists($primary , $this->data['create'])){
								$this->data['auto'][$value[0]] = null;
								return null;
							}
							break;
					}
				}
				//解析类型
				if(!empty($value[3])){
					switch ($value[3]) {
						case 'function':
							//函数方法调用
							$value[1] = $value[1]();
							break;
						case 'callback':
							//回调当前模型的一个方法
							$value[1] = $this->$value[1]();
							break;
						default:
							//默认做字符串处理
							$value[1] = $value[1];
							break;
					}
				}
				//保存自动完成属性
				$this->data['auto'][$value[0]] = $value[1];
			}
		}
	}

	/**
	 * 解析validate参数
	 * array('表单名' , '验证规则' , '错误提示' , '验证类型' , '附加规则'),
	 * @author Colin <15070091894@163.com>
	 */
	protected function _parse_validate(){
		$validate = new Validate();
		foreach ($this->validate as $key => $value) {
			switch ($value[3]) {
				case 'validate':
					$string = $validate->Validate($value[0] , array(array('string' => $value[0] , $value[4] => $value[1] , 'info' => $value[2])));
					break;
			}
			//保存自动验证属性
			$this->data['validate'][$value[0]] = $string;
		}
	}

	/**
	 * 解析@ 替换成表前缀，
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	protected function _parse_prefix($data = null){
		//处理表前缀
		if(strpos($data , '@') !== false){
			$data = str_replace('@' , $this->db_prefix , $data);
		}
		return $data;
	}

	/**
	 * 清理where条件和join
	 * @return [type] [description]
	 */
	protected function _clearThis(){
		$this->Where = [];
		$this->Join = [];
	}

	/**
	 * 执行sql语句函数
	 * @author Colin <15070091894@163.com>
	 */
	protected function ADUP($sql = null , $ist = null){
		$sql = $sql === null ? $this->Sql : $sql;
		$this->_clearThis();
		$query = $this->db->query($sql);
		WriteLog($sql , 'LOG_SQL_FORMAT');
		if(!$query){
			if($this->startTransaction){
				return false;
			}
			E('SQL statement execution error ' . $this->db->showerror());
		}
		if($ist == 'ist'){
			return $this->db->insert_id();
		}else if($ist == 'upd'){
			return $this->db->affected_rows($query);
		}
		return $query;
	}

	/**
	 * 解析函数
	 * @param type 解析的类型
	 * @param array 要被解析的数据
	 * @author Colin <15070091894@163.com>
	 */
	protected function ParData($type , $array){
		$setKey = '';
		$setValue = '';
		switch ($type) {
			//如果是新增操作
			case 'ist':
				if(is_array($array)){
					foreach ($array as $key => $value) {
						$setKey .= '`' . $key . '`' . ',';
						$setValue .= "'" . addslashes($value) . "',";
					}
					$this->ParKey = substr($setKey, 0 , -1);
					$this->Parvalue = substr($setValue, 0 , -1);
				}else if(is_string($array)){
					E('Resolve failure!' . $this->Sql);
				}
				break;
			//如果是更新操作
			case 'upd':
				$pk = $this->getpk();
				foreach ($array as $key => $value){
					if($key == $pk){
						continue;
					}
					$setKey .= '`' . $key . '`=\'' . addslashes($value) . "',";
				}
				//解析主键
				if($this->Where[0] === null){
					$this->where($pk , $array[$pk]);
				}
				$this->ParKey = ' SET '.substr($setKey , 0 , -1);
				break;
		}
	}

	/**
	 * 解析where参数
	 * @param array 	$field 	字段数组
	 * @param boolean 	$showOr 是否显示and 或 $this->whereOr
	 * @param string 	$sub 	条件操作符
	 * @return [type] [description]
	 */
	protected function parseWhere($field = null , $showOr = false , $sub = null){
		$field = myclass_filter($field);
		//遍历字段
		foreach ($field as $key => $value){
			//处理数组传递区间符号
			if(is_array($value)){
				//得到 $value[0] 和 $value[1];
				list($sub , $tmpValue) = $value;
				//增加一个空格
				$sub = ' ' . $sub . ' ';
				$value = $tmpValue;
			}
			if(strpos($key , '.') !== false){
				//处理使用array('p.name' => '工' , 'p.id' => 111) 中文''问题
				if($value){
					$value = is_numeric($value) && !empty($value) ? $value : "'$value'";
				}
				$tmp .= $key . $sub . $value . ' ' . $this->WhereOR . ' ';
			}else{
				//处理between，in操作
				$tmp .= $value !== '' && $value !== null ? "`$key` $sub '$value' $this->WhereOR " : "`$key` $sub $this->WhereOR ";
			}
		}
		$end = strlen($this->WhereOR) + 1;
		//截取掉$this->WhereOr
		$tmp = mb_substr($tmp , 0 , -$end , 'utf-8');
		return array('tmp' => $tmp , 'value' => $value , 'sub' => $sub);
	}

	/**
	 * 获取where条件
	 * @return [type] [description]
	 */
	protected function getWhere(){
		$where = '';
	    $whereCount = count($this->Where);
	    //如果是字符串，则直接返回
	    if(is_string($this->Where)){
	    	$where = $this->Where;
	    	//否则处理后返回
	    }else if(is_array($this->Where) && $whereCount > 0){
	    	$where = ' WHERE ' . implode(' ' . $this->WhereOR . ' ' , $this->Where);
	    }
	    return $where;
	}

	/**
	 * 获取结果集
	 * @param sql sql语句
	 * @param is_more 是否为获取多条数据
	 * @author Colin <15070091894@163.com>
	 */
	protected function getResult($sql = null , $is_more = false){
		$sql = $sql === null ? $this->Sql : $sql;
		$result = $this->ADUP($sql);
		$data = array();
		if($is_more){
			while ($rows = $this->db->fetch_array($result)){
				$data[] = $rows;
			}
		}else{
			$data = $this->db->fetch_array($result);
		}
		return $data;
	}

	/**
	 * between公共模块
	 * @param field 要被between的字段名
	 * @param between between的值 格式为 1,2
	 * @param keyword 关键词 BETWEEN 或者 NOT BETWEEN 
	 * @author Colin <15070091894@163.com>
	 */
	protected function between_common($field , $between , $keyword){
		list($betweenleft , $betweenright) = explode( ',' , $between);
		$between = $keyword . ' ' . $betweenleft . ' AND ' . $betweenright . ' ';
		$this->where($field , null , null , $between);
	}

	/**
	 * in操作公共方法
	 * @return [type] [description]
	 */
	protected function in_common($field , $in , $keyword){
		if(is_array($in)){
			$in = implode(',' , $in);
		}
		$this->where($field , null , null , $keyword . '(' . $in . ')');
	}

	/**
	 * 验证数据库信息是否填写
	 * @author Colin <15070091894@163.com>
	 */
	protected static function CheckConnectInfo(){
		if(!Config('DB_TYPE') || !Config('DB_HOST') || !Config('DB_USER') || !Config('DB_TABS')){
			E('Please set up the database connection information!');
		}
	}

	/**
     * 设置类成员
     * @param tables 要验证的表名
     * @author Colin <15070091894@163.com>
     */
	protected function setClassMember(){
		$patten = '/(DB\_.*)/';
		foreach (Config() as $key => $value) {
			if(!preg_match($patten , $key , $match)){
				continue;
			}
			$member = strtolower($match[0]);
			$this->$member = $value;
			// eval('$this->' . $member . ' = "' . $value . '";');
		}
	}
}
?>