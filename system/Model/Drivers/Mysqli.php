<?php
/**
 * mysqli操作
 * @author Colin <15070091894@163.com>
 */
namespace system\Model\Drivers;
use system\Db;

class Mysqli extends Db{
    protected $_db;
    public $affected_rows;
    
    /**
     * 连接数据库操作
     * @author Colin <15070091894@163.com>
     */
    public function connect(){
        $host = Config('DB_HOST') . ':' . Config('DB_PORT');
        $this->_db = new \mysqli($post,Config('DB_USER'),Config('DB_PASS'));
        if(mysqli_connect_errno()){
            throw new \system\MyError(mysqli_connect_error());
        }
    }

    /**
     * query方法
     * @author Colin <15070091894@163.com>
     */
    public function query($sql){
        return $this->_db->query($sql);
    }

    /**
     * 选择数据库方法
     * @author Colin <15070091894@163.com>
     */
    public function select_db($tables){
        return $this->_db->select_db($tables);
    }

    /**
     * 获取结果集方法
     * @param query 数据库执行后的操作句柄
     * @author Colin <15070091894@163.com>
     */
    public function fetch_array($query = null){
        return $query->fetch_assoc();
    }

    /**
     * 取得上一步 INSERT 操作产生的 ID 
     * @author Colin <15070091894@163.com>
     */
    public function insert_id(){
        return $this->_db->insert_id;
    }

    /**
     *  MySQL 操作所影响的记录行数 
     * @author Colin <15070091894@163.com>
     */
    public function affected_rows(){
        return $this->_db->affected_rows;
    }

    /**
     * close方法
     * @author Colin <15070091894@163.com>
     */
    public function close(){
        $this->_db->close($this->_db);
    }

    /**
     * 返回上一个操作所产生的错误信息
     * @author Colin <15070091894@163.com>
     */
    public function showerror(){
        return $this->_db->error;
    }

    /**
     * 获取表所有字段
     * @param string $table [表名]
     * @author Colin <15070091894@163.com>
     */
    public function getFields($table){
        $prefix = Config('DB_PREFIX') . $table;
        $query = $this->_db->query("select COLUMN_NAME from information_schema.COLUMNS where table_name = '$prefix'");
        $result = $this->getResult($query);
        foreach ($result as $key => $value) {
            $fields[] = $value['COLUMN_NAME'];
        }
        $result = array_values($fields);
        return $result;
    }
}
?>