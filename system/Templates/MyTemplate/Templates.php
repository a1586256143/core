<?php
/**
 * 模板
 * @author Colin <15070091894@163.com>
 */

namespace system\Templates\MyTemplate;

use system\MyError;
use system\ObjFactory;
use system\Url;
use system\Route\Route;

class Templates {
    //模板目录
    public $template_dir;
    public $compile_dir;
    public $cache_dir;

    /**
     * assign()方法  用于注入变量
     *
     * @param var 模板中的变量名
     *
     * @author Colin <15070091894@163.com>
     * @throws
     */
    public function assign($var, $value) {
        if (isset($var) && !empty($var)) {
            Route::$vars[ $var ] = $value;
        } else {
            E('请设置模板变量名！');
        }
    }

    /**
     * display()方法 载入文件。生成编译文件和缓存文件
     *
     * @param file 文件名
     *
     * @author Colin <15070091894@163.com>
     * @return string
     * @throws
     */
    public function display($file) {
        //获取模板名
        //$filename = $this->getTemplateName($file);
        //默认控制器和默认方法
        $default_modules2 = defined('CURRENT_MODULE') ? CURRENT_MODULE : $default_modules;
        $modules          = empty($default_modules2) ? $modules : $default_modules2;
        //设置路径
        $dirname = APP_DIR . $this->template_dir;
        //编译文件目录
        $dircname = $this->compile_dir;
        //判断编译文件夹和缓存文件夹是否存在
        $dir = array($this->compile_dir, $this->compile_dir . $modules, $dircname);
        //生成文件夹
        outdir($dir);
        //判断方法目录是否存在
        if (!is_dir($dirname)) {
            E($dirname . '目录不存在');
        }
        //判断模板文件是否存在
        if (!file_exists($file)) {
            E($file . '模板文件不存在！');
        }
        //生成编译文件
        $parFile = $dircname . md5($file) . '.php';
        //判断编译文件是否存在 如果存在那么就直接调用编译文件 如果不存在 那么久重新编译生成
        if (!file_exists($parFile) || (filemtime($parFile) < filemtime($file))) {
            //编译文件的修改时间<tpl模板文件的修改时间
            //实例化解析类
            $_parser = ObjFactory::CreateTemplatesParse('tpl', $file);
            //调用解析类里面的公共方法
            $_parser->comile($parFile);
        }

        return $parFile;
    }

    /**
     * 获取模板名
     *
     * @param path 目录路径
     *
     * @author Colin <15070091894@163.com>
     * @return string
     */
    protected function getTemplateName($path = null) {
        $explode     = explode('/', $path);
        $filearray[] = array_pop($explode);
        $filearray[] = array_pop($explode);
        asort($filearray);
        $filename = implode('/', $filearray);

        return $filename;
    }

    /**
     * 载入layout方法
     *
     * @param file 文件名
     *
     * @author Colin <15070091894@163.com>
     * @throws
     */
    public function Layout($file) {
        list($controller, $method) = URL::getCurrentUrl();

        $tplFile = $file;
        //判断是否写了目录名支持持一级
        $_patten = '/(.*)\/(.*)/';
        $path    = APP_DIR . '/' . CURRENT_MODULE;
        if (preg_match($_patten, $file, $_match)) {
            $tplFile = $path . Config('LAYOUT_DIR') . '/' . $_match[2];
        }

        //设置路径
        $dircname = Config('TPL_C_DIR') . $controller . '/' . METHOD_NAME . '/';

        //判断模板文件是否存在
        if (!file_exists($tplFile)) {
            E($tplFile . '视图文件不存在！');
        }
        $name = $this->getTemplateName($file);
        $name = str_replace('/', '_', $name);
        //生成编译文件
        $parFile = $dircname . md5($name) . $name . '.php';
        //判断编译文件是否存在 如果存在那么就直接调用编译文件 如果不存在 那么久重新编译生成
        if (!file_exists($parFile) || (filemtime($parFile) < filemtime($tplFile))) {
            //编译文件的修改时间<tpl模板文件的修改时间
            //实例化解析类
            $parser = ObjFactory::CreateTemplatesParse('tpl', $tplFile);
            //调用解析类里面的公共方法
            $parser->comile($parFile);
        }
        //引入编译文件
        include $parFile;
    }
}