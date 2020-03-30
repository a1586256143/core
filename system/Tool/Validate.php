<?php
/**
 * 数据验证
 * @author Colin <15070091894@163.com>
 */

namespace system\Tool;

class Validate {
    //是否必填
    protected $required = 0;
    //配置信息
    protected $config = [];
    //最大长度
    protected $maxlength = '';
    //最小长度
    protected $minlength = '';
    //名称
    protected $name = '';
    //字段的值
    protected $value = '';
    //提示消息
    protected $info = '';
    //正则
    protected $pattern = '';
    //长度判断编码
    protected $charset = 'utf-8';
    //验证正则
    protected $validate_patten = '';
    //提示使用的函数
    protected $type = 'default';
    //返回值
    protected $returnValue = '';

    /**
     * 初始化方法
     *
     * @param array $config 配置值
     * @param null  $type   类型
     */
    public function __construct($config = null, $type = null) {
        $this->config = $config;
        if (!empty($type)) {
            $this->type = $type;
        }
    }

    /**
     * 开始验证
     *
     * @param string $name 要处理的字段名
     * @param array config 自定义配置
     *
     * @author Colin <15070091894@163.com>
     * @return string
     * @throws
     */
    public function Validate($name = null, $config = null) {
        $this->name = $name;
        //系统配置 为空
        $config = !empty($this->config) ? $this->config : $config;
        //遍历
        foreach ($config as $key => $value) {
            //解析配置信息
            $this->_parseConfig($value);
            //解析函数。开始验证
            $this->_parseFunction($value);
        }

        return $this->returnValue();
    }

    /**
     * 解析配置信息
     *
     * @param      $config
     *
     * @author Colin <15070091894@163.com>
     */
    protected function _parseConfig($config) {
        if (!empty($config)) {
            if (is_array($config)) {
                foreach ($config as $key => $value) {
                    $this->setKey($key, $value);
                }
            }
        }
    }

    /**
     * 设置值
     *
     * @param null $key
     * @param null $value
     *
     * @author Colin <15070091894@163.com>
     */
    protected function setKey($key = null, $value = null) {
        if (isset($this->$key)) {
            $this->$key = $value;
        }
    }

    /**
     * isset
     *
     * @param string $key
     *
     * @author Colin <15070091894@163.com>
     * @return string
     */
    public function __isset($key) {
        if (isset($this->$key)) {
            return $this->$key;
        }

        return '';
    }

    /**
     * 解析函数体内的方法
     *
     * @param array $rules  解析的规则列表
     * @param null  $string 解析的值
     *
     * @author Colin <15070091894@163.com>
     *
     * @throws \system\MyError
     */
    protected function _parseFunction($rules, $string = null) {
        $method  = '';
        $methods = ['required', 'minlength', 'maxlength', 'pattern'];
        foreach ($rules as $key => $value) {
            if (in_array($key, $methods)) {
                $method = $key;
            }
        }
        $string = empty($string) ? $this->value : $string;
        if (method_exists($this, $method)) {
            $this->$method($string);
        } else {
            E($method . '此方法不存在！');
        }
    }

    /**
     * 正则校验
     *
     * @param $value
     */
    public function pattern($value) {
        $pattern = $this->pattern;
        if (!preg_match($pattern, $value)) {
            $this->error($this->info ? $this->info : '匹配不正确');
        }
        $this->setreturnValue($this->name, $value);
    }

    /**
     * 内置函数 email
     *
     * @param string $str 验证的字符串
     *
     * @throws
     */
    public function email($str = null) {
        if (empty($this->validate_patten)) {
            $this->validate_patten = '/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/';
        }
        if (!preg_match($this->validate_patten, $str)) {
            $this->error($this->info ? $this->info : '邮箱格式不正确');
        }
        $this->setreturnValue($this->name, $str);
    }

    /**
     * 验证是否为空
     *
     * @param string $value 要处理的值
     * @param string $name  处理的字段名
     *
     * @throws
     * @author Colin <15070091894@163.com>
     */
    public function required($value, $name = null) {
        $name = empty($name) ? $this->name : $name;
        if ($this->required) {
            if (empty($string) && strlen($value) == 0) {
                $this->error($this->info ? $this->info : $name . '不能为空！');
            }
        }
        $this->setreturnValue($this->name, $value);
    }

    /**
     * 验证最大长度
     *
     * @param string $string    要处理的值
     * @param int    $maxlength 最大长度
     * @param int    $name      字段名
     *
     * @throws
     * @author Colin <15070091894@163.com>
     */
    public function maxlength($string, $maxlength = null, $name = null) {
        $maxlength = empty($maxlength) ? $this->maxlength : $maxlength;
        $name      = empty($name) ? $this->name : $name;
        if (mb_strlen($string, $this->charset) > $maxlength) {
            $this->error($this->info ? $this->info : $name . '的长度超过' . $maxlength . '位');
        }
        $this->setreturnValue($this->name, $string);
    }

    /**
     * 验证最小长度
     *
     * @param  string 要处理的值
     * @param int $minlength 最小长度
     * @param int $name      字段名
     *
     * @throws
     *
     * @author Colin <15070091894@163.com>
     */
    public function minlength($string, $minlength = null, $name = null) {
        $minlength = empty($minlength) ? $this->minlength : $minlength;
        $name      = empty($name) ? $this->name : $name;
        if (mb_strlen($string, $this->charset) < $minlength) {
            $this->error($this->info ? $this->info : $name . '的长度不能低于' . $minlength . '位');
        }
        $this->setreturnValue($this->name, $string);
    }

    /**
     * 返回值设置
     *
     * @param string $name   字段名
     * @param string $string 字段值
     */
    public function setreturnValue($name, $string = null) {
        $this->returnValue[ $name ] = htmlspecialchars(trim($string));
    }

    /**
     * 返回值
     */
    public function returnValue() {
        return $this->returnValue;
    }

    /**
     * 错误信息
     *
     * @param string $info 要显示的消息
     *
     * @throws
     * @author Colin <15070091894@163.com>
     * @return string|mixed
     */
    public function error($info = null) {
        $this->info = $info;
        switch ($this->type) {
            case 'default':
                E($this->info);
                break;
            case 'ajaxReturn':
                return ajaxReturn(['info' => $this->info, 'url' => null, 'status' => 0]);
                break;
        }
    }
}
