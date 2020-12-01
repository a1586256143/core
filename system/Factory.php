<?php
/**
 * 工厂处理对象
 * @author Colin <15070091894@163.com>
 */

namespace system;

use system\IO\File\File;
use system\IO\Storage\Drivers\FileStorage;
use system\IO\Storage\Storage;
use system\Model\Db;
use system\Model\Drivers\Mysqli;

class Factory {
    protected static $ins;
    /**
     * @var static
     */
    protected static $instance = [];

    /**
     * 获取一个单例模式
     *
     * @return mixed
     */
    final public static function getInstance() {
        $class = get_called_class();
        if (!self::$instance[ $class ]) {
            self::$instance[ $class ] = new static();
        }

        return self::$instance[ $class ];
    }

    /**
     * 创建数据库对象
     * @author Colin <15070091894@163.com>
     * @return Mysqli
     * @throws
     */
    public static function getIns() {
        return Db::getIns();
    }

    /**
     * 创建缓存类
     * @author Colin <15070091894@163.com>
     * @return FileStorage
     * @throws
     */
    public static function CreateCache() {
        return Storage::getIns();
    }

    /**
     * 创建模板类对象
     *
     * @param string $type   类型
     * @param array  $config 配置
     *
     * @author Colin <15070091894@163.com>
     * @return View
     */
    public static function CreateTemplates($type = null, $config = []) {
        //实例化第三方模板类
        $template       = ucfirst($type);
        $templateobject = new $template;
        if (!empty($templateobject)) {
            foreach ($config as $key => $value) {
                $templateobject->$key = Config(strtoupper($value));
            }
        }

        return $templateobject;
    }

    /**
     * 创建系统模型类
     *
     * @param string tables 表名
     *
     * @author Colin <15070091894@163.com>
     * @return Model
     */
    public static function CreateSystemModel($tables = null) {
        return new Model($tables);
    }

    /**
     * 验证码类
     * @author Colin <15070091894@163.com>
     * @return Tool\Code
     */
    public static function CreateCode() {
        return new Tool\Code();
    }

    /**
     * 获取容器
     * @return Container
     */
    public static function Container() {
        return Container::getInstance();
    }

    /**
     * 创建文件类
     * @return File
     */
    public static function File() {
        return File::getInstance();
    }
}
