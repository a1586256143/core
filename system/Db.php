<?php
/**
 * 数据库接口
 * @author Colin <15070091894@163.com>
 */
namespace system;
abstract class Db{
    protected static $db;

    /**
     * 获取数据库类
     * @author Colin <15070091894@163.com>
     */
    public static function getIns(){
        if(self::$db){
            return self::$db;
        }else{
            if(strtolower(Config('DB_TYPE')) == 'mysqli'){
                self::$db = new \system\Model\Drivers\Mysqli();
            }else if(strtolower(Config('DB_TYPE')) == 'mysql'){
                self::$db = new \system\Model\Drivers\Mysql();
            }else if(strtolower(Config('DB_TYPE')) == 'pdo'){
                self::$db = new \system\Model\Drivers\PDO();
            }
            self::$db->connect();
            self::CheckDatabase();
            self::$db->query('SET NAMES '.Config('DB_CODE'));
            return self::$db;
        }
    }

    /**
     * 连接数据库
     * @author Colin <15070091894@163.com>
     */
    abstract public function connect();

    /**
     * query
     * @param  [string] $sql [要执行的sql语句]
     * @author Colin <15070091894@163.com>
     */
    abstract public function query($sql);

    /**
     * 选择数据库方法
     * @param  [string] $tables [数据库名]
     * @author Colin <15070091894@163.com>
     */
    abstract public function select_db($tables);

    /**
     * 获取结果集 以数组格式获取
     * @param  [string] $query [query后的结果集]
     * @author Colin <15070091894@163.com>
     */
    abstract public function fetch_array($query);

    /**
     * 获取新增的ID
     * @author Colin <15070091894@163.com>
     */
    abstract public function insert_id();

    /**
     * 获取执行影响的记录数
     * @author Colin <15070091894@163.com>
     */
    abstract public function affected_rows();

    /**
     * 关闭数据库
     * @author Colin <15070091894@163.com>
     */
    abstract public function close();

    /**
     * 返回最近的一条sql语句错误信息
     * @author Colin <15070091894@163.com>
     */
    abstract public function showerror();

    /**
     * 获取数据库所有字段信息
     * @param  [string] $table [表名]
     * @author Colin <15070091894@163.com>
     */
    abstract public function getFields($table);

    /**
     * 确认数据库是否存在
     * @author Colin <15070091894@163.com>
     */
    public static function CheckDatabase(){
        $database = Config('DB_TABS');
        $result = self::$db->select_db($database);
        if(!$result){
            E('数据库不存在或数据库名不正确！'.$database);
        }
    }

    /**
     * 确认表是否存在
     * @param tables 验证表名
     * @param db_tabs 验证数据库
     * @author Colin <15070091894@163.com>
     */
    public function CheckTables($tables = null , $db_tabs = null){
        if(empty($db_tabs)){
            $db_tabs = Config('DB_TABS');
        }
        $result = $this->execute("select `TABLE_NAME` from `INFORMATION_SCHEMA`.`TABLES` where `TABLE_SCHEMA`='$db_tabs' and `TABLE_NAME`='$tables' ");
        if(empty($result)){
            E('数据表不存在！'.$tables);
        }
    }

    /**
     * 确认字段是否存在
     * @param table 查询表名
     * @param field 查询字段
     * @author Colin <15070091894@163.com>
     */
    public function CheckFields($table , $field){
        if(!$this->execute("Describe `$table` `$field`")){
            return true;
        }
    }

    /**
     * 执行源生sql语句并返回结果
     * @param sql 要执行的sql语句
     * @author Colin <15070091894@163.com>
     */
    public function execute($sql){
        $query = $this->query($sql);
        if(!$query){
            return false;
        }
        $result = $this->fetch_array($query);
        return $result;
    }

    /**
     * 获取结果集
     * @param  [string] $query [query执行后结果]
     * @author Colin <15070091894@163.com>
     */
    protected function getResult($query){
        $data = array();
        while ($rows = $this->fetch_array($query)){
            $data[] = $rows;
        }
        return $data;
    }

    /**
     * 关闭数据库方法
     * @author Colin <15070091894@163.com>
     */
    public static function CloseDB(){
        self::$db->close();
    }
    
    /**
     * 开启事务处理
     * @author Colin <15070091894@163.com>
     */
    public function startTransaction(){
        return $this->query('start transaction');
    }

    /**
     * 回滚事务处理
     * @author Colin <15070091894@163.com>
     */
    public function rollback(){
        return $this->query('rollback');
    }

    /**
     * 提交事务处理
     * @author Colin <15070091894@163.com>
     */
    public function commit(){
        return $this->query('commit');
    }
}
?>