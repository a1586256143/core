<?php
/**
 * pdo操作
 * @author Colin <15070091894@163.com>
 */

namespace system\Model\Drivers;

use system\Model\Db;
use system\Model\DBInterface;

class Pdo extends Db implements DBInterface {
    /**
     * @var \PDO
     */
    protected $_db;
    protected $query;

    /**
     * 连接数据库操作
     * @author Colin <15070091894@163.com>
     */
    public function connect() {
        $string    = "mysql:host=%s;dbname=%s";
        $this->_db = new \PDO(sprintf($string, Config('DB_HOST'), Config('DB_TABS')), Config('DB_USER'), Config('DB_PASS'));
    }

    /**
     * query
     *
     * @param string $sql 执行的sql语句
     *
     * @author Colin <15070091894@163.com>
     * @return mixed
     */
    public function query($sql) {
        $this->query = $this->_db->query($sql);

        return $this->query;
    }

    /**
     * 选择数据库方法
     *
     * @param string $tables 数据库名
     *
     * @author Colin <15070091894@163.com>
     * @return mixed
     */
    public function select_db($tables) {
        if ($this->_db) {
            return true;
        }
    }

    /**
     * 获取结果集 以数组格式获取
     *
     * @param mixed $query query后的结果集
     *
     * @author Colin <15070091894@163.com>
     * @return mixed
     */
    public function fetch_array($query = null) {
        if ($query) {
            return $query->fetch(\PDO::FETCH_ASSOC);
        }

        return $this->query->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * 获取新增的ID
     * @author Colin <15070091894@163.com>
     * @return mixed
     */
    public function insert_id() {
        return $this->_db->lastInsertId();
    }

    /**
     * 获取执行影响的记录数
     *
     * @param mixed $prepare
     *
     * @author Colin <15070091894@163.com>
     * @return mixed
     */
    public function affected_rows($prepare = null) {
        return $prepare->rowCount();
    }

    /**
     * 关闭数据库
     * @author Colin <15070091894@163.com>
     * @return mixed
     */
    public function close() {
        $this->_db = null;

        return true;
    }

    /**
     * 返回最近的一条sql语句错误信息
     * @author Colin <15070091894@163.com>
     * @return mixed
     */
    public function showerror() {
        $info = $this->_db->errorInfo();

        return $info[2];
    }
}
