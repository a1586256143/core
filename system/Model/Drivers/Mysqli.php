<?php
/**
 * mysqli操作
 * @author Colin <15070091894@163.com>
 */

namespace system\Model\Drivers;

use system\Model\Db;
use system\Model\DBInterface;
use system\MyError;

class Mysqli extends Db implements DBInterface {
    public $affected_rows;

    /**
     * 连接数据库操作
     * @author Colin <15070091894@163.com>
     * @throws
     */
    public function connect() {
        $port = Config('DB_PORT');
        $host = Config('DB_HOST');
        if ($port) {
            $host .= ':' . $port;
        }
        $this->_db = new \mysqli($host, Config('DB_USER'), Config('DB_PASS'));
        if (mysqli_connect_errno()) {
            throw new MyError(mysqli_connect_error());
        }
    }

    /**
     * query方法
     *
     * @param string $sql 执行的SQL语句
     *
     * @author Colin <15070091894@163.com>
     * @return mixed
     */
    public function query($sql) {
        return $this->_db->query($sql);
    }

    /**
     * 选择数据库方法
     *
     * @param string $tables 表名
     *
     * @author Colin <15070091894@163.com>
     * @return mixed
     */
    public function select_db($tables) {
        return $this->_db->select_db($tables);
    }

    /**
     * 获取结果集方法
     *
     * @param mixed query 数据库执行后的操作句柄
     *
     * @author Colin <15070091894@163.com>
     * @return mixed
     */
    public function fetch_array($query = null) {
        return $query->fetch_assoc();
    }

    /**
     * 取得上一步 INSERT 操作产生的 ID
     * @author Colin <15070091894@163.com>
     * @return mixed
     */
    public function insert_id() {
        return $this->_db->insert_id;
    }

    /**
     *  MySQL 操作所影响的记录行数
     *
     * @param mixed $prepare
     *
     * @author Colin <15070091894@163.com>
     * @return mixed
     */
    public function affected_rows($prepare = null) {
        return $this->_db->affected_rows;
    }

    /**
     * close方法
     * @author Colin <15070091894@163.com>
     * @return mixed
     */
    public function close() {
        return $this->_db->close($this->_db);
    }

    /**
     * 返回上一个操作所产生的错误信息
     * @author Colin <15070091894@163.com>
     * @return mixed
     */
    public function showerror() {
        return $this->_db->error;
    }
}
