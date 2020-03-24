<?php

namespace system\Model\Select;

use system\Factory;

class FieldQuery extends Factory {
    protected $field;

    /**
     * 初始化
     * FieldQuery constructor.
     *
     * @param string $field
     */
    public function __construct($field = '') {
        $this->field = $field;
    }

    /**
     * 获取信息
     * @return string
     */
    public function getField() {
        return $this->field;
    }
}
