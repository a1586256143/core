<?php

namespace system\Model\Select;

use system\Factory;

class DynamicQuery extends Factory {
    protected $sql;
    // 查询转换
    protected $selectMap = [
        'eq'  => '=',
        'gt'  => '>',
        'lt'  => '<',
        'egt' => '>=',
        'neq' => '<>',
        'elt' => '<=',
    ];

    /**
     * 获取单例对象
     * @return \system\Model\Select\DynamicQuery
     */
    public static function getInstance() {
        return self::applyIns(self::class, new self);
    }

    protected function filterData(&$data) {
        return $data;
    }

    /**
     * IN操作
     *
     * @param string $field 字段名
     * @param array  $value between的值
     */
    public function in($field, $value) {
        $this->sql .= sprintf('%s in (%s)', $field, implode(',', $value));
    }

    /**
     * NotIN操作
     *
     * @param string $field 字段名
     * @param array  $value between的值
     */
    public function notIn($field, $value) {
        $this->sql .= sprintf('%s not in (%s)', $field, implode(',', $value));
    }

    /**
     * 区间操作
     *
     * @param string $field 字段名
     * @param array  $value between的值
     */
    public function between($field, $value) {
        $this->sql .= sprintf('%s between %s and %s', $field, $value[0], $value[1]);
    }

    /**
     * Like操作
     *
     * @param string       $field 字段名
     * @param array|string $value 搜索的值
     */
    public function like($field, $value) {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $this->like($field, $v);
            }
        } else {
            if (strpos($value, '%') !== 0) {
                $value = '\'%' . $value . '%\'';
            }
            // TODO 多条[1,2]时会出问题
            $this->sql = sprintf('%s LIKE %s', $field, $value);
        }
    }

    /**
     * 自动绑定条件
     *
     * @param string $field
     * @param mixed  $value
     * @param string $oper
     */
    public function autoBind($field, $value, $oper) {
        if (!method_exists(self::class, $oper)) {
            $map       = $this->selectMap[ $oper ];
            $oper      = $map ? $map : $oper;
            $value     = $this->getValue($value);
            $this->sql = sprintf('%s %s %s', $field, $oper, $value);
        } else {
            $this->$oper($field, $value);
        }
    }

    protected function getValue($value) {
        if (is_numeric($value)) {
            return $value;
        } else {
            return '\'' . $value . '\'';
        }
    }

    /**
     * 是否可绑定
     *
     * @param string $oper
     *
     * @return bool
     */
    public function isBind($oper) {
        return $this->selectMap[ $oper ] ? true : false;
    }

    /**
     * 格式化
     * @return mixed
     */
    public function fetch() {
        return $this->sql;
    }
}