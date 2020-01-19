<?php

namespace system\Model\Select;

use system\Factory;

class FieldQuery extends Factory {
    protected $field;

    /**
     * 获取单例对象
     * @return \system\Model\Select\DynamicQuery
     */
    public static function getInstance() {
        return self::applyIns(self::class, new self);
    }

    public function __construct($field = '') {
        $this->field = $field;
    }

    public function getField() {
        return $this->field;
    }
}
