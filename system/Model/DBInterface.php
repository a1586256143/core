<?php

namespace system\Model;
interface DBInterface {
    /**
     * 连接数据库
     * @author Colin <15070091894@163.com>
     */
    public function connect();

    /**
     * query
     *
     * @param  string $sql [要执行的sql语句]
     *
     * @author Colin <15070091894@163.com>
     */
    public function query($sql);

    /**
     * 选择数据库方法
     *
     * @param  string $tables [数据库名]
     *
     * @author Colin <15070091894@163.com>
     */
    public function select_db($tables);

    /**
     * 获取结果集 以数组格式获取
     *
     * @param  string $query [query后的结果集]
     *
     * @author Colin <15070091894@163.com>
     */
    public function fetch_array($query);

    /**
     * 获取新增的ID
     * @author Colin <15070091894@163.com>
     */
    public function insert_id();

    /**
     * 获取执行影响的记录数
     * @author Colin <15070091894@163.com>
     */
    public function affected_rows();

    /**
     * 关闭数据库
     * @author Colin <15070091894@163.com>
     */
    public function close();

    /**
     * 返回最近的一条sql语句错误信息
     * @author Colin <15070091894@163.com>
     */
    public function showerror();
}