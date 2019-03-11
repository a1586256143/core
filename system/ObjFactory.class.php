<?php
/**
 * 工厂处理对象
 * @author Colin <15070091894@163.com>
 */
namespace system;

class ObjFactory{
    /**
     * 创建数据库对象
     * @author Colin <15070091894@163.com>
     */
    public static function getIns(){
        return Db::getIns();
    }

    /**
     * 创建缓存类
     * @author Colin <15070091894@163.com>
     */
    public static function CreateCache(){
        return new Cache();
    }

    /**
     * 创建模板类对象
     * @author Colin <15070091894@163.com>
     */
    public static function CreateTemplates($type = null , $config = array()){
        if($type == 'tpl'){
            $templateobject = new \system\Templates\MyTemplate\Templates();
        }else{
            //实例化第三方模板类
            $template = ucfirst($type);
            $name = '\system\Templates\\'.$template.'\\'.$template;
            $templateobject = new $name();
        }
        if(!empty($templateobject)){
            foreach ($config as $key => $value) {
               $templateobject->$key = Config(strtoupper($value));
            }
        }
        return $templateobject;
    }

    /**
     * 创建模板解析类对象
     * @param tplFile 解析的文件名
     * @author Colin <15070091894@163.com>
     */
    public static function CreateTemplatesParse($type = null , $tplFile){
        if($type == 'tpl'){
            return new \system\Templates\MyTemplate\Parser($tplFile);
        }else{
            //实例化第三方解析类
        }
    }

    /**
     * 创建控制器类
     * @param name 控制器名称
     * @author Colin <15070091894@163.com>
     */
    public static function CreateController($name){
        return new $name();
    }

    /**
     * 创建模型类
     * @param name 控制器名称
     * @author Colin <15070091894@163.com>
     */
    public static function CreateModel($name){
        $modelexplode = explode('\\', $name);
        //得到最后一个值
        $modelname = array_pop($modelexplode);
        return new $name($modelname);
    }

    /**
     * 创建系统模型类
     * @param tables 表名
     * @author Colin <15070091894@163.com>
     */
    public static function CreateSystemModel($tables = null){
        return new Model($tables);
    }

    /**
     * 创建视图类
     * @param name 为视图文件名称
     * @author Colin <15070091894@163.com>
     */
    public static function CreateView(){
        $obj = '\\system\\View';
        return new $obj();
    }

    /**
     * 创建分页类
     * @param total 总数
     * @param pagesize 分页数
     * @author Colin <15070091894@163.com>
     */
    public static function CreatePage($total , $pagesize){
        return new Page($total , $pagesize);
    }

    /**
     * 创建时间类
     * @author Colin <15070091894@163.com>
     */
    public static function CreateDate(){
        return new Date();
    }

     /**
     * 验证码类
     * @author Colin <15070091894@163.com>
     */
    public static function CreateCode(){
        return new Code();
    }
}
?>