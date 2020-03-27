<?php
/**
 * 模型操作
 * @author Colin <15070091894@163.com>
 */

namespace system;

use system\Model\Select\DynamicQuery;
use system\Tool\Validate;

class Model implements \ArrayAccess {
    //数据库句柄
    protected $db = '';
    //获取数据表前缀
    protected $db_prefix = '';
    //获取数据库名
    protected $DataName = '';
    //多表查询数据库名，必须带数据前缀
    protected $Tables;
    //数据表的真实名字，可指定模型的表名
    protected $TableName = '';
    //From表名
    protected $From;
    //多表查询字段名
    protected $Fields = '*';
    //where字段
    protected $Where = [];
    //where value
    protected $value = [];
    //where 条件的 OR and
    protected $WhereOR = [];
    //sql语句
    protected $Sql = '';
    //解析后存放的字段
    protected $ParKey = '';
    //解析后存放的字段
    protected $Parvalue = '';
    //字符别名
    protected $Alias = '';
    //limit
    protected $Limit = '';
    //order
    protected $Order = '';
    //自动完成
    protected $auto = [];
    //自动验证
    protected $validate = [];
    //保存数据
    protected $data = [];
    //join
    protected $Join = [];
    // 查询锁
    protected $Lock = false;
    //开启事务
    protected $startTransaction = 0;
    // 查询时设置此值可跨表查询
    protected $dbName = '';
    // 查询的结果
    protected $result = [];
    //新增时操作
    const MODEL_INSERT = 1;
    //修改时操作
    const MODEL_UPDATE = 2;
    //所有操作
    const MODEL_BOTH = 3;

    /**
     * 构造方法
     *
     * @param null $tables 被使用的表名或类名
     *
     * @author Colin <15070091894@163.com>
     * @throws
     */
    final public function __construct($tables = null) {
        //设置类成员
        self::setClassMember();
        //数据库信息是否填写
        self::CheckConnectInfo();
        //获取数据库对象
        $this->db = Factory::getIns();
        //如果表名为空，并且TableName为空
        if (empty($tables) && !$this->TableName) {
            // 处理是否有实例化类名
            $explode          = explode('\\', get_class($this));
            $tables           = array_pop($explode);
            $tables           = str_replace('Model', '', $tables);
            $className        = explode('\\', __CLASS__);
            $currentModelName = array_pop($className);
            if ($tables == $currentModelName) {
                return $this;
            }
        }
        //是否设置表名
        if ($this->TableName) {
            $tables = $this->TableName;
        }
        //执行判断表方法
        $this->TablesType($tables);
        //确认表是否存在
        $this->db->CheckTables($this->dbName ? $this->DataName : $this->db_prefix . $this->DataName, $this->dbName);

        return $this;
    }

    /**
     * From函数
     *
     * @param string tables 表名
     *
     * @author Colin <15070091894@163.com>
     * @return \system\Model
     */
    public function from($tables = null) {
        $tables     = $tables === null ? $this->TablesName : $tables;
        $tables     = $this->dbName ? $this->dbName . '.' . $tables : $this->_parse_prefix($tables);
        $this->From = ' FROM ' . $tables;

        return $this;
    }

    /**
     * field方法
     *
     * @param string field 字段名
     *
     * @author Colin <15070091894@163.com>
     * @return \system\Model
     */
    public function field($field = '*') {
        if (!empty($field)) {
            $this->Fields = $field;
        }

        return $this;
    }

    /**
     * create方法
     *
     * @param array $data 创建对象的数据
     *
     * @author Colin <15070091894@163.com>
     * @return array
     */
    public function create($data = []) {
        if (!$data) $data = values('post.');
        //获取表所有字段
        $fields    = $this->getFields();
        $fieldData = [];
        foreach ($fields as $key => $value) {
            if (isset($data[ $value ])) {
                if ($data[ $value ] === null || $data[ $value ] == '') {
                    continue;
                }
                $fieldData[ $value ] = $data[ $value ];
            }
        }
        //去除空值
        $this->data['create'] = myclass_filter($fieldData);
        //自动完成
        if (!empty($this->auto)) {
            $this->_parse_auto();
            //合并自动完成数据
            $this->data['create'] = myclass_filter(array_merge($this->data['create'], $this->data['auto']));
        }
        if (!empty($this->validate)) {
            $this->_parse_validate();
            //合并自动验证数据
            $this->data['create'] = myclass_filter(array_merge($this->data['create'], $this->data['validate']));
        }

        return $this->data['create'];
    }

    /**
     * 条件
     *
     * @param string|array $field 字段名称
     * @param string       $value 字段值
     * @param string       $or    OR和AND
     *
     * @author Colin <15070091894@163.com>
     * @return \system\Model
     */
    public function where($field = '', $value = null, $or = null) {
        if ($or !== null) $this->WhereOR = $or;
        if ($field == null) return $this;
        $this->parseWhere($field, $value);

        return $this;
    }

    /**
     * 获取主键
     * @author Colin <15070091894@163.com>
     */
    public function getpk() {
        $pk = S('TABLE_PK_FOR_' . $this->DataName);
        if (empty($pk)) {
            $sql = "SELECT COLUMN_NAME FROM information_schema.`KEY_COLUMN_USAGE` WHERE TABLE_SCHEMA = '$this->db_tabs' AND TABLE_NAME = '$this->db_prefix$this->DataName' LIMIT 1";
            $pk  = $this->execute($sql);
            S('TABLE_PK_FOR_' . $this->DataName, $pk);
        }

        return $pk['COLUMN_NAME'];
    }

    /**
     * 执行源生的sql语句
     *
     * @param string $sql sql语句
     *
     * @author Colin <15070091894@163.com>
     * @return array
     */
    public function query($sql = null) {
        $sql         = $sql === null ? $this->Sql : $sql;
        $this->Where = [];

        return $this->getResult($sql);
    }

    /**
     * 查询函数
     * @author Colin <15070091894@163.com>
     *
     * @param bool $each 是否自行遍历
     *
     * @return array|\system\Model
     */
    public function select($each = false) {
        $this->getSql();
        $data = $this->getResult(null, true);
        if ($each) {
            return $this;
        }

        return $data;
    }

    /**
     * 遍历数组
     *
     * @param \Closure $callback
     *
     * @return array
     */
    public function each(\Closure $callback) {
        foreach ($this->result as $key => &$value) {
            if ($return = $callback($value, $key)) {
                $value = $return;
            }
        }

        return $this->result;
    }

    /**
     * 转换数组
     *
     * @param string $key 新数组下标
     * @param string $var 新数组的值
     *
     * @return array
     */
    public function toArray($key = '', $var = '') {
        if (!$var && !$key) {
            return $this->result;
        }
        if ($key && !$var) {
            return array_column($this->result, $key);
        }
        $array = [];
        foreach ($this->result as $k => $value) {
            $array[ $value[ $key ] ] = $var ? $value[ $var ] : $value;
        }

        return $array;
    }

    /**
     * 查询一条数据
     *
     * @param string $id 主键值
     *
     * @author Colin <15070091894@163.com>
     * @return array
     */
    public function find($id = null) {
        if ($id) {
            $value = !is_numeric($id) ? "'$id'" : $id;
            $this->where($this->getPk(), $value);
        }
        $this->getSql();

        return $this->getResult();
    }

    /**
     * 执行之后
     *
     * @param array $data 原封不动的数据
     *
     * @return bool
     */
    protected function afterFind(&$data) {
        return true;
    }

    /**
     * 执行之前
     * @return bool
     */
    protected function beforeFind() {
        return true;
    }

    /**
     * 获取最后执行的sql语句
     * @author Colin <15070091894@163.com>
     */
    public function getLastSql() {
        return $this->Sql;
    }

    /**
     * 查询数据库条数
     * @author Colin <15070091894@163.com>
     */
    public function count() {
        $pk           = $this->getpk();
        $result       = $this->field('count(' . $pk . ') as count')->find();
        $this->Fields = '*';

        return $result['count'] ? $result['count'] : 0;
    }

    /**
     * 插入数据
     *
     * @param array $datas 要插入的数据
     *
     * @author Colin <15070091894@163.com>
     * @return int
     */
    public function insert($datas = null) {
        $values = myclass_filter($datas);
        if (!$values) {
            $values = $this->data['create'];
        }
        $this->ParData('ist', $values);
        $this->Sql = "INSERT INTO " . $this->TablesName . "(" . $this->ParKey . ") VALUES (" . $this->Parvalue . ")";

        return $this->ADUP($this->Sql, 'ist');
    }

    /**
     * 删除函数
     *
     * @param string $field 被删除的字段
     * @param string $value 唯一标示符
     *
     * @author Colin <15070091894@163.com>
     * @return int
     */
    public function delete($value = '', $field = null) {
        $field = $field === null ? $this->getpk() : $field;
        if ($this->Where[0] === null) {
            $this->where($field, $value);
        }
        $where     = $this->getWhere();
        $this->Sql = "DELETE FROM " . $this->TablesName . $where;

        return $this->ADUP($this->Sql, 'upd');
    }

    /**
     * 修改函数
     *
     * @param string|array $field 要被修改的字段
     * @param string|array $value 要被修改的值
     *
     * @author Colin <15070091894@163.com>
     * @return int
     */
    public function update($field, $value = null) {
        if (is_string($field)) {
            if (!$this->ParKey) {
                $this->ParKey = ' SET ' . '`' . $field . '`' . "='" . $value . "'";
            }
        } else if (is_array($field)) {
            $data = [];
            foreach ($field as $key => $value) {
                if ($value === '') {
                    continue;
                }
                $data[ $key ] = addslashes($value);
            }
            $this->ParData('upd', $data);
        }
        $where     = $this->getWhere();
        $this->Sql = "UPDATE " . $this->TablesName . $this->ParKey . $where;

        return $this->ADUP($this->Sql, 'upd');
    }

    /**
     * 左连接
     *
     * @param string $table  表名
     * @param string $on     关联条件
     * @param string $method 连接名
     *
     * @return \system\Model
     */
    public function join($table, $on = '', $method = 'LEFT') {
        $join         = $this->_parse_prefix($table);
        $this->Join[] = ' ' . $method . ' JOIN ' . $join . ' ON ' . $on;

        return $this;
    }

    /**
     * 对某一字段自增
     *
     * @param string $field 字段名
     * @param int    $num   自增值
     *
     * @return int
     */
    public function incField($field, $num = 1) {
        $this->ParKey = ' SET ' . '`' . $field . '`' . "=$field + $num";

        return $this->update($field);
    }

    /**
     * 对某一字段递减
     *
     * @param string $field 字段名
     * @param int    $num   递减值
     *
     * @return int
     */
    public function decField($field, $num = 1) {
        $this->ParKey = ' SET ' . '`' . $field . '`' . "=$field - $num";

        return $this->update($field);
    }

    /**
     * 限制查询N条记录
     *
     * @param int $start 查询结果集的数量 0
     * @param int $end   10
     *
     * @author Colin <15070091894@163.com>
     * @return \system\Model
     */
    public function limit($start = 0, $end = null) {
        if (!$start && !$end) {
            return $this;
        }
        if (!empty($start) && $end) {
            $start = ($start - 1) * $end;
            $str   = $start . ',' . $end;
        } else if ($start == 0 && $end > 0) {
            $str = $start . ',' . $end;
        } else if ($start && !$end) {
            $str = 0 . ',' . $start;
        }
        $this->Limit = " LIMIT " . $str;

        return $this;
    }

    /**
     * order
     *
     * @param string $field 字段名
     * @param string $desc  排序方式
     *
     * @author Colin <15070091894@163.com>
     * @return \system\Model
     */
    public function order($field, $desc = null) {
        $this->Order = " ORDER BY " . $field . " " . $desc . " ";

        return $this;
    }

    /**
     * 查询锁 FOR UPDATE
     *
     * @param bool $lock
     *
     * @return \system\Model
     */
    public function lock($lock = false) {
        $this->Lock = $lock;

        return $this;
    }

    /**
     * 别名
     *
     * @param string $as 新的别名
     *
     * @author Colin <15070091894@163.com>
     * @return \system\Model
     */
    public function alias($as = 'alias') {
        $this->Alias = ' AS ' . $as;

        return $this;
    }

    /**
     * 求最大值
     *
     * @param string $field 要求出最大值的数值
     *
     * @author Colin <15070091894@163.com>
     * @return array
     */
    public function max($field) {
        $this->setDefaultAs($field);

        return $this->field("MAX($field)$this->Alias")->find();
    }

    /**
     * 最小值
     *
     * @param string $field 要被求出最小值的字段
     *
     * @author Colin <15070091894@163.com>
     * @return array
     */
    public function min($field) {
        $this->setDefaultAs($field);

        return $this->field("MIN($field)$this->Alias")->find();
    }

    /**
     * 某个字段求和
     *
     * @param string $field 要被求和的字段
     *
     * @author Colin <15070091894@163.com>
     * @return array
     */
    public function sum($field) {
        $this->setDefaultAs($field);

        return $this->field("SUM($field)$this->Alias")->find();
    }

    /**
     * 求平均值
     *
     * @param string $field 平均值的字段
     *
     * @author Colin <15070091894@163.com>
     * @return array
     */
    public function avg($field) {
        $this->setDefaultAs($field);

        return $this->field("AVG($field)$this->Alias")->find();
    }

    /**
     * 执行源生sql语句并返回结果
     *
     * @param string $sql 要执行的sql语句
     *
     * @author Colin <15070091894@163.com>
     * @return bool
     */
    public function execute($sql) {
        return $this->db->execute($sql);
    }

    /**
     * 执行原声sql语句，返回资源类型
     *
     * @param string $sql 要执行的sql语句
     *
     * @author Colin <15070091894@163.com>
     * @return int
     */
    public function execute_resource($sql) {
        return $this->ADUP($sql);
    }

    /**
     * 获取下一条数据
     *
     * @param string $id    获取下一条数据的ID
     * @param string $field 查询字段
     *
     * @author Colin <15070091894@163.com>
     * @return array
     */
    public function next($id, $field = '*') {
        $map = [
            'id >' => $id,
        ];

        return $this->field($field)->where($map)->find();
    }

    /**
     * 获取上一条数据
     *
     * @param string $id    获取下一条数据的ID
     * @param string $field 查询字段
     *
     * @author Colin <15070091894@163.com>
     * @return array
     */
    public function prev($id, $field = '*') {
        $map = [
            'id <' => $id,
        ];

        return $this->field($field)->where($map)->find();
    }

    /**
     * 开启事务处理
     * @author Colin <15070091894@163.com>
     */
    public function startTransaction() {
        $this->startTransaction = 1;

        return $this->db->startTransaction();
    }

    /**
     * 回滚事务处理
     * @author Colin <15070091894@163.com>
     */
    public function rollback() {
        return $this->db->rollback();
    }

    /**
     * 提交事务处理
     * @author Colin <15070091894@163.com>
     */
    public function commit() {
        return $this->db->commit();
    }

    /**
     * 获取单条数据
     *
     * @param null|array $id
     * @param string     $field
     * @param string     $desc
     *
     * @return array|string|int
     */
    public function getFind($id = null, $field = '*', $desc = '') {
        if (is_array($id)) {
            $map = $id;
        } else {
            $map = [$this->getpk() => $id];
        }
        $find = $this->field($field)->where($map);
        if ($desc) {
            $find->order($desc . ' DESC');
        }
        $find = $find->find();
        if ($field !== '*') {
            $fields = explode(',', $field);
            if (count($fields) == 1) {
                return $find[ $field ];
            }
        }

        return $find;
    }

    /**
     * 容错处理机制
     *
     * @param string $fun
     * @param string $param
     *
     * @throws
     * @author Colin <15070091894@163.com>
     */
    public function __call($fun, $param = null) {
        E(get_called_class() . '->' . $fun . '()这个方法不存在！');
    }

    /**
     * 静态方法容错处理机制
     *
     * @param string $fun
     * @param string $param
     *
     * @throws
     *
     * @author Colin <15070091894@163.com>
     */
    static public function __callStatic($fun, $param = null) {
        E(get_called_class() . '->' . __METHOD__ . '()这个方法不存在！');
    }

    /**
     * invoke方法  处理把类当成函数来使用
     * @author Colin <15070091894@163.com>
     * @throws
     */
    public function __invoke() {
        E(__CLASS__ . '这不是一个函数');
    }

    /**
     * 获取所有字段
     *
     * @param string $tables 表名
     *
     * @author Colin <15070091894@163.com>
     * @return array
     */
    protected function getFields($tables = null) {
        if (!$tables) $tables = $this->DataName;
        //缓存字段信息
        $fields = S($tables . '_field_cache');
        if (!$fields) {
            $fields = $this->db->getFields($tables);
            S($tables . '_field_cache', $fields);
        }

        return $fields;
    }

    /**
     * 设置默认别名
     *
     * @param string $field 别名字段
     *
     * @author Colin <15070091894@163.com>
     */
    protected function setDefaultAs($field = null) {
        if (!$this->Alias) {
            $this->Alias = ' AS ' . $field;
        }
    }

    /**
     * 得到查询的sql语句
     * @author Colin <15070091894@163.com>
     */
    protected function getSql() {
        if ($this->Tables != null) {
            $prefix = "SELECT $this->Fields FROM " . $this->Tables . $this->Alias;
        } else {
            $prefix = "SELECT $this->Fields " . $this->From . $this->Alias;
        }
        $where     = $this->getWhere();
        $this->Sql = $prefix . implode(' ', $this->Join) . $where . $this->Order . $this->Limit;
        if ($this->Lock) {
            $this->Sql .= ' FOR UPDATE';
        }

        return $this->Sql;
    }

    /**
     * 判断类型
     *
     * @param string $tables 表名
     *
     * @author Colin <15070091894@163.com>
     */
    protected function TablesType($tables) {
        $tables = $this->parTableName($tables);
        //转小写
        $this->DataName = strtolower($tables);
        //空格转换
        $spaceIndex = strpos($this->DataName, ' ');
        $as         = '';
        if ($spaceIndex !== false) {
            $as             = substr($this->DataName, $spaceIndex);
            $this->DataName = substr($this->DataName, 0, $spaceIndex);
        }
        $this->TablesName = empty($this->TableName) ? '`' . $this->db_prefix . $this->DataName . '`' : '`' . $this->TableName . '`';
        $this->from($this->TablesName . $as);
    }

    /**
     * 解析表名的大写
     *
     * @param string $tables 表名
     *
     * @author Colin <15070091894@163.com>
     * @return string
     */
    protected function parTableName($tables) {
        $tablename = myclass_filter(preg_split('/(?=[A-Z])/', $tables));
        $tablename = implode('_', $tablename);

        return $tablename;
    }

    /**
     * 解析auto参数
     * array('字段名' , '完成规则' , '完成条件' , '附加规则(结合完成规则使用)')
     * @author Colin <15070091894@163.com>
     */
    protected function _parse_auto() {
        $fields   = $this->getFields();
        $primary  = $this->getPk();
        $isUpdate = array_key_exists($primary, $this->data['create']);
        //遍历自动完成属性
        foreach ($this->auto as $key => $value) {
            //查找是否符合字段需求
            if (in_array($value[0], $fields)) {
                $value[2] = $value[2] ? $value[2] : self::MODEL_INSERT;
                //解析处理状态
                if (!empty($value[2]) && $value[2] != self::MODEL_BOTH) {
                    if ($isUpdate) {
                        if ($value[2] != self::MODEL_UPDATE) continue;
                    } else {
                        if ($value[2] != self::MODEL_INSERT) continue;
                    }
                }
                if (!isset($value[3])) {
                    $value[3] = 'function';
                }
                //解析类型
                if (!empty($value[3])) {
                    switch ($value[3]) {
                        case 'function':
                            //函数方法调用
                            $value[1] = $value[1]();
                            break;
                        case 'callback':
                            $name = $value[1];
                            //回调当前模型的一个方法
                            $value[1] = $this->$name();
                            break;
                        default:
                            //默认做字符串处理，$value[1] = $value[1]; 等于本身，不处理
                            break;
                    }
                }
                //保存自动完成属性
                $this->data['auto'][ $value[0] ] = $value[1];
            }
        }
    }

    /**
     * 解析validate参数
     * array('表单名' , '验证方法' , '错误提示' , '自定义正则' , '验证方式 validate和callback'),
     *  ['age', 'required', '年龄必须填写'],
     * ['name', 'pattern', '姓名只能为英文', '/[0-9]+/'],
     * 或['name', 'pattern', '姓名只能为英文', '/[0-9]+/','callback'],
     * @author Colin <15070091894@163.com>
     */
    protected function _parse_validate() {
        $validate = new Validate();
        foreach ($this->validate as $key => $value) {
            $string = '';
            !isset($value[3]) && $value[3] = 1;
            !isset($value[4]) && $value[4] = 'validate';
            $createValue = $this->data['create'][ $value[0] ];
            switch ($value[4]) {
                case 'validate':
                    $string = $validate->Validate($value[0], [
                        [
                            'name'    => $value[0],
                            $value[1] => $value[3],
                            'info'    => $value[2],
                            'value'   => $createValue
                        ]
                    ]);
                    break;
                case 'callback':
                    $name = $value[1];
                    //回调当前模型的一个方法
                    if (!$this->$name($createValue, $value[3])) {
                        E($value[2] ? $value[2] : '此字段为必填');
                    }
                    $string = $createValue;
                    break;
            }
            //保存自动验证属性
            $this->data['validate'][ $value[0] ] = $string;
        }
    }

    /**
     * 解析@ 替换成表前缀，
     *
     * @param  [type] $data [description]
     *
     * @return string
     */
    protected function _parse_prefix($data = null) {
        //处理表前缀
        if (strpos($data, '@') !== false) {
            $data = str_replace('@', $this->db_prefix, $data);
        }

        return $data;
    }

    /**
     * 清理where条件和join
     */
    protected function _clearThis() {
        $this->Where = [];
        $this->Join  = [];
        $this->Limit = '';
        $this->Order = '';
    }

    /**
     * 执行sql语句函数
     *
     * @param string $sql 执行的SQL语句
     * @param string $ist insert和update
     *
     * @author Colin <15070091894@163.com>
     * @return int
     * @throws
     */
    protected function ADUP($sql = null, $ist = null) {
        $sql = $sql === null ? $this->Sql : $sql;
        $this->_clearThis();
        $query = $this->db->query($sql);
        Config('LOG_SQL') && WriteLog($sql);
        if (!$query) {
            if ($this->startTransaction) {
                return false;
            }
            E('SQL statement execution error ' . $this->db->showerror());
        }
        if ($ist == 'ist') {
            return $this->db->insert_id();
        } else if ($ist == 'upd') {
            return $this->db->affected_rows($query);
        }

        return $query;
    }

    /**
     * 解析函数
     *
     * @param string $type  解析的类型
     * @param array  $array 要被解析的数据
     *
     * @author Colin <15070091894@163.com>
     * @throws
     */
    protected function ParData($type, $array) {
        $setKey   = '';
        $setValue = '';
        switch ($type) {
            //如果是新增操作
            case 'ist':
                if (is_array($array)) {
                    foreach ($array as $key => $value) {
                        $setKey   .= '`' . $key . '`,';
                        $setValue .= $this->filter($value) . ',';
                    }
                    $this->ParKey   = substr($setKey, 0, -1);
                    $this->Parvalue = substr($setValue, 0, -1);
                } else if (is_string($array)) {
                    E('Resolve failure!' . $this->Sql);
                }
                break;
            //如果是更新操作
            case 'upd':
                $pk = $this->getpk();
                foreach ($array as $key => $value) {
                    if ($key == $pk) {
                        continue;
                    }
                    $setKey .= '`' . $key . '`=' . $this->filter($value) . ',';
                }
                //解析主键
                if ($this->Where[0] === null) {
                    $this->where($pk, $array[ $pk ]);
                }
                $this->ParKey = ' SET ' . substr($setKey, 0, -1);
                break;
        }
    }

    /**
     * 解析where条件值
     *
     * @param string $field
     * @param string $val
     * @param int    $index 第$index次执行parse
     */
    protected function parseWhere($field, $val = null, $index = 0) {
        $template = '%s = %s';
        // 是否为单个的字段，直接赋值的，假如 where('id' , 1)
        if (!is_array($field)) {
            if (!is_array($val)) {
                $this->Where[ $index ][] = sprintf($template, $this->parseJoinField($field), $this->filter($val));
            }
        } else {
            foreach ($field as $key => $value) {
                if (is_numeric($key)) {
                    $index += 1;
                    $this->parseWhere($value, null, $index);
                    continue;
                }
                if ($key == '_logic') {
                    $this->WhereOR[ $index ] = $value;
                    continue;
                }
                $query = new DynamicQuery();
                // 解析key中的操作符号
                list($explodeField, $explode) = explode(' ', $key);
                // 解析字段为 'name >' => '2'
                if ($explode) {
                    // 有操作符号
                    $query->autoBind($explodeField, $value, $explode);
                    $this->Where[ $index ][] = $query->fetch();
                } else {
                    if (is_array($value)) {
                        // 数组操作
                        list($oper, $val) = $value;
                        // 验证是否在dynamic中
                        if (is_string($oper)) {
                            if (method_exists($query, $oper)) {
                                $query->autoBind($key, $val, $oper);
                            } else {
                                // 尝试查找是否可绑定
                                if ($query->isBind($oper)) {
                                    $query->autoBind($key, $val, $oper);
                                }
                            }
                            $this->Where[ $index ][] = $query->fetch();
                            continue;

                        }
                    }
                    $this->Where[ $index ][] = sprintf($template, $this->parseJoinField($key), $this->filter($value));
                }
            }
        }
    }

    /**
     * 解析join的字段信息
     *
     * @param $field
     *
     * @return string
     */
    protected function parseJoinField($field) {
        if (strpos($field, '.') !== false) {
            return $field;
        }

        return '`' . $field . '`';
    }

    /**
     * 过滤数据
     *
     * @param $value
     *
     * @return string
     */
    protected function filter($value) {
        if (is_numeric($value) || is_int($value)) {
            return $value;
        }
        if (is_string($value)) {
            return '\'' . addslashes($value) . '\'';
        }

        return '';
    }

    /**
     * 获取where条件
     * @return string
     */
    protected function getWhere() {
        $where      = '';
        $whereCount = count($this->Where);
        //如果是字符串，则直接返回
        if (is_string($this->Where)) {
            $where = $this->Where;
            //否则处理后返回
        } else if (is_array($this->Where) && $whereCount > 0) {
            $format = [];
            foreach ($this->Where as $key => $value) {
                $logic    = isset($this->WhereOR[ $key ]) ? $this->WhereOR[ $key ] : 'AND';
                $format[] = '(' . implode(' ' . $logic . ' ', $value) . ')';
            }
            if (count($this->Where) == 1) {
                $format[0] = mb_substr($format[0], 1, -1);
            }
            $where = ' WHERE ' . implode(' OR ', $format);
        }

        return $where;
    }

    /**
     * 获取结果集
     *
     * @param string $sql     sql语句
     * @param bool   $is_more 是否为获取多条数据
     *
     * @author Colin <15070091894@163.com>
     * @return array
     */
    protected function getResult($sql = null, $is_more = false) {
        $sql    = $sql === null ? $this->Sql : $sql;
        $result = $this->ADUP($sql);
        $data   = [];
        $this->beforeFind();
        if ($is_more) {
            while ($rows = $this->db->fetch_array($result)) {
                $this->afterFind($rows);
                $data[] = $rows;
            }
        } else {
            $data = $this->db->fetch_array($result);
            $this->afterFind($data);
        }
        $this->result = $data;

        return $data;
    }

    /**
     * 验证数据库信息是否填写
     * @author Colin <15070091894@163.com>
     * @throws
     */
    protected static function CheckConnectInfo() {
        if (!Config('DB_TYPE') || !Config('DB_HOST') || !Config('DB_USER') || !Config('DB_TABS')) {
            E('Please set up the database connection information!');
        }
    }

    /**
     * 设置类成员
     * @author Colin <15070091894@163.com>
     */
    protected function setClassMember() {
        $patten = '/(DB\_.*)/';
        foreach (Config() as $key => $value) {
            if (!preg_match($patten, $key, $match)) {
                continue;
            }
            $member        = strtolower($match[0]);
            $this->$member = $value;
        }
    }

    public function offsetExists($offset) {
    }

    public function offsetGet($offset) {
        return $this->result[ $offset ];
    }

    public function offsetSet($offset, $value) {
    }

    public function offsetUnset($offset) {
    }
}
