<?php
/**
 * mysql操作
 * @author Colin <15070091894@163.com>
 */

namespace system\Model\Drivers;

use system\Model\Db;
use system\Model\DBInterface;
use system\MyError;

class Mysql extends Db implements DBInterface {
    protected $_db;

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
        if (version_compare(PHP_VERSION, '5.5.0', '>')) {
            throw new MyError('不支持的mysql库，请检查你的PHP版本');
        }
        $this->_db = mysql_connect($host, Config('DB_USER'), Config('DB_PASS'));
        if (!$this->_db) {
            throw new MyError(mysql_error());
        }
    }

    /**
     * 选择数据库方法
     *
     * @param string $tables 表名
     *
     * @author Colin <15070091894@163.com>
     * @return bool
     */
    public function select_db($tables) {
        return mysql_select_db($tables);
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
        $result = mysql_query($sql, $this->_db);

        return $result;
    }

    /**
     * 获取结果集方法
     *
     * @param mixed $query 数据库执行后的操作句柄
     *
     * @author Colin <15070091894@163.com>
     * @return mixed
     */
    public function fetch_array($query = null) {
        $fetch = mysql_fetch_assoc($query);
        mysql_free_result($fetch);

        return $fetch;
    }

    /**
     * 取得上一步 INSERT 操作产生的 ID
     * @author Colin <15070091894@163.com>
     * @return int
     */
    public function insert_id() {
        return mysql_insert_id();
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
        return mysql_affected_rows();
    }

    /**
     * close方法
     * @author Colin <15070091894@163.com>
     * @return mixed
     */
    public function close() {
        return mysql_close($this->_db);
    }

    /**
     * 返回上一个操作所产生的错误信息
     * @author Colin <15070091894@163.com>
     * @return string
     */
    public function showerror() {
        return mysql_error();
    }
}
