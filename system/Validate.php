<?php
/**
 * 数据验证
 * @author Colin <15070091894@163.com>
 */

namespace system;
class Validate {
    protected $string          = '';        //要处理的字符串
    protected $require         = 0;        //是否必填
    protected $parstring       = array();//处理后的字符串
    protected $config          = array();    //配置信息
    protected $maxlength       = '';        //最大长度
    protected $minlength       = '';        //最小长度
    protected $info            = '';            //提示消息
    protected $charset         = 'utf-8';    //长度判断编码
    protected $validate_patten = '';//验证正则
    protected $type            = 'showMessage';//提示使用的函数
    protected $returnValue     = '';    //返回值

    public function __construct($config = null, $type = null) {
        $this->config = $config;
        if (!empty($type)) {
            $this->type = $type;
        }
    }

    /**
     * 开始验证
     *
     * @param string 要处理的字符串
     * @param config 自定义配置
     *
     * @author Colin <15070091894@163.com>
     * @return string
     */
    public function Validate($string = null, $config = null) {
        $this->string = $string;
        //系统配置 为空
        $config = !empty($this->config) ? $this->config : $config;
        //遍历
        foreach ($config as $key => $value) {
            //解析配置信息
            $this->_parseConfig($value);
            //解析字符串
            $this->_parstring($value['string']);
            //解析函数。开始验证
            $this->_parsefunction($value);
        }

        return $this->returnValue();
    }

    /**
     * 解析函数体内的方法
     * @author Colin <15070091894@163.com>
     */
    protected function _parsefunction($name, $string = null) {
        $method = '';
        foreach ($name as $key => $value) {
            switch ($key) {
                case 'require':
                    $method = 'required';
                    break;
                case 'minlength':
                    $method = 'minlength';
                    break;
                case 'maxlength':
                    $method = 'maxlength';
                    break;
                case 'validate':
                    $method = $value;
                    break;
                case 'validate_patten':
                    $this->validate_patten = $value;
                    break;
            }
        }
        $string = empty($string) ? $this->string : $string;
        if (method_exists($this, $method)) {
            $this->$method($string);
        } else {
            E($method . '此方法不存在！');
        }
    }

    /**
     * 内置函数 email
     *
     * @param  str 验证字符串
     * @param  patten 验证规则
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
     * 解析配置信息
     * @author Colin <15070091894@163.com>
     */
    protected function _parseConfig($config, $name = null, $savevalue = null) {
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
     * @author Colin <15070091894@163.com>
     */
    protected function setKey($key = null, $value = null) {
        if (isset($this->$key)) {
            $this->$key = $value;
        }
    }

    /**
     * isset
     * @author Colin <15070091894@163.com>
     */
    public function __isset($key) {
        if (isset($this->$key)) {
            return $this->$key;
        }
    }

    /**
     * 解析字符串
     *
     * @param  string 要处理的值
     *
     * @author Colin <15070091894@163.com>
     */
    protected function _parstring($string) {
        if (empty($string)) {
            $string = $this->string;
        }
        //获取值
        $this->string = values('request', $string);
        //设置name
        $this->name = $string;
    }

    /**
     * 验证是否为空
     *
     * @param  string 要处理的值
     *
     * @author Colin <15070091894@163.com>
     */
    public function required($string, $name = null) {
        $name = empty($name) ? $this->name : $name;
        if ($this->require) {
            if (empty($string) && strlen($string) == 0) {
                $this->error($this->info ? $this->info : $name . '不能为空！');
            }
        }
        $this->setreturnValue($this->name, $string);
    }

    /**
     * 验证最大长度
     *
     * @param  string 要处理的值
     *
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
     * @param  info 要显示的消息
     *
     * @author Colin <15070091894@163.com>
     */
    public function error($info = null) {
        $this->info = $info;
        switch ($this->type) {
            case 'showMessage':
                showMessage($this->info);
                break;
            case 'ajaxReturn':
                ajaxReturn(array('info' => $this->info, 'url' => null, 'status' => 0));
                break;
            default :
                $this->type($this->info);
                break;
        }
    }
}