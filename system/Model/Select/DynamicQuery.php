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
     * IN操作
     *
     * @param string $field 字段名
     * @param array  $value between的值
     *
     * @return bool
     */
    public function in($field, $value) {
        $value     = is_string($value) ? [$value] : $value;
        $this->sql .= sprintf('%s in (%s)', $field, implode(',', $value));

        return true;
    }

    /**
     * NotIN操作
     *
     * @param string $field 字段名
     * @param array  $value between的值
     *
     * @return bool
     */
    public function notIn($field, $value) {
        $value     = is_string($value) ? [$value] : $value;
        $this->sql .= sprintf('%s not in (%s)', $field, implode(',', $value));

        return true;
    }

    /**
     * 区间操作
     *
     * @param string $field 字段名
     * @param array  $value between的值
     *
     * @return true
     */
    public function between($field, $value) {
        $this->sql .= sprintf('%s between %s and %s', $field, $value[0], $value[1]);

        return true;
    }

    /**
     * Like操作
     *
     * @param string       $field 字段名
     * @param array|string $value 搜索的值
     *
     * @return bool
     */
    public function like($field, $value) {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $this->like($field, $v);
            }
        } else {
            if (strpos($value, '%') !== 0) {
                $value = '\'%' . ltrim(rtrim($value, '\''), '\'') . '%\'';
            }
            // TODO 多条[1,2]时会出问题
            $this->sql = sprintf('%s LIKE %s', $field, $value);
        }

        return true;
    }

    /**
     * 自动绑定条件
     *
     * @param string $field
     * @param mixed  $value
     * @param string $oper
     *
     * @return bool  true需要field，false不需要field
     */
    public function autoBind($field, $value, $oper) {
        // 普通的查询
        $normal = true;
        if ($value instanceof FieldQuery) {
            $value  = $value->getField();
            $normal = false;
        }
        if (!method_exists(self::class, $oper)) {
            $map       = $this->selectMap[ $oper ];
            $oper      = $map ? $map : $oper;
            $value     = $normal ? $this->getValue($value) : $value;
            // 解决使用field()函数的时候，出现0,1,2这种无字段的条件值
            if (is_numeric($field)) {
                $field = '';
            }
            $this->sql = sprintf('%s %s %s', $field, $oper, $value);

            return true;
        } else {
            if (is_array($value)) {
                foreach ($value as $key => &$val) {
                    $val = self::getValue($val);
                }
            } else {
                $value = self::getValue($value);
            }

            return $this->$oper($field, $value);
        }
    }

    /**
     * find_in_set骚操作
     *
     * @param string $field 字段名
     * @param string $value 字段值
     *
     * @return bool
     */
    public function find_in_set($field, $value) {
        $this->sql = sprintf('find_in_set(%s , %s)', $value, $field);

        return false;
    }

    /**
     * 获取值
     *
     * @param $value
     *
     * @return string
     */
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
