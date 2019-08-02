<?php
/**
 * 工厂处理对象
 * @author Colin <15070091894@163.com>
 */

namespace system;

use system\IO\File\File;
use system\IO\Storage\Storage;
use system\Templates\MyTemplate\Templates;
use system\Templates\MyTemplate\Parser;
use system\Model\Db;

class Factory {

    /**
     * 创建数据库对象
     * @author Colin <15070091894@163.com>
     * @return \system\Db
     */
    public static function getIns() {
        return Db::getIns();
    }

    /**
     * 创建缓存类
     * @author Colin <15070091894@163.com>
     * @return \system\IO\Storage\Drivers\RedisStorage
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
     * @return Templates
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
     * 创建模型类
     *
     * @param name 控制器名称
     *
     * @author Colin <15070091894@163.com>
     * @return class
     */
    public static function CreateModel($name) {
        $modelexplode = explode('\\', $name);
        //得到最后一个值
        $modelname = array_pop($modelexplode);

        return new $name($modelname);
    }

    /**
     * 创建系统模型类
     *
     * @param tables 表名
     *
     * @author Colin <15070091894@163.com>
     * @return system\Model
     */
    public static function CreateSystemModel($tables = null) {
        return new Model($tables);
    }

    /**
     * 验证码类
     * @author Colin <15070091894@163.com>
     * @return \system\Code\Code
     */
    public static function CreateCode() {
        return new Tool\Code();
    }

    /**
     * 获取容器
     * @return \system\Container
     */
    public static function Container() {
        return Container::getInstance();
    }

    /**
     * 创建文件类
     * @return \system\IO\File\File
     */
    public static function File() {
        return File::getInstance();
    }

}