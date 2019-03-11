<?php
/**
 * pdo操作
 * @author Colin <15070091894@163.com>
 */
namespace system\DataBase;
use system\Db;

class PDO extends Db{
    protected static $link;

    /**
     * 连接数据库操作
     * @author Colin <15070091894@163.com>
     */
    public function connect(){
        $string = "mysql:host=%s;dbname=%s";
        self::$link = new \PDO(sprintf($string , Config('DB_HOST') , Config('DB_TABS')) , Config('DB_USER') , Config('DB_PASS'));
    }

    /**
     * query
     * @param  [string] $sql [要执行的sql语句]
     * @author Colin <15070091894@163.com>
     */
    public function query($sql){
        $this->query = self::$link->query($sql);
        return $this->query;
    }

    /**
     * 选择数据库方法
     * @param  [string] $tables [数据库名]
     * @author Colin <15070091894@163.com>
     */
    public function select_db($tables){
        if(self::$link){
            return true;
        }
    }

    /**
     * 获取结果集 以数组格式获取
     * @param  [string] $query [query后的结果集]
     * @author Colin <15070091894@163.com>
     */
    public function fetch_array($query = null){
        if($query){
            return $query->fetch_array();
        }
        return $this->query->fetch_array();
    }

    /**
     * 获取新增的ID
     * @author Colin <15070091894@163.com>
     */
    public function insert_id(){
        return self::$link->lastInsertId();
    }

    /**
     * 获取执行影响的记录数
     * @author Colin <15070091894@163.com>
     */
    public function affected_rows($prepare = null){
        return $prepare->rowCount();
    }

    /**
     * 关闭数据库
     * @author Colin <15070091894@163.com>
     */
    public function close(){
        self::$link = null;
    }

    /**
     * 返回最近的一条sql语句错误信息
     * @author Colin <15070091894@163.com>
     */
    public function showerror(){
        $info = self::$link->errorInfo();
        return $info[2];
    }

    /**
     * 获取数据库所有字段信息
     * @param  [string] $table [表名]
     * @author Colin <15070091894@163.com>
     */
    public function getFields($table){

    }
}
?>