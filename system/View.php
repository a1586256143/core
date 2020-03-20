<?php
/**
 * 视图显示
 * @author Colin <15070091894@163.com>
 */

namespace system;
class View {
    /**
     * @var $view \SmartyBC
     */
    public static $view;

    /**
     * 初始化成员信息
     *
     * @param string type 类型
     * @param array $config 视图的配置
     *
     * @author Colin <15070091894@163.com>
     * @throws \SmartyException
     */
    public static function init($type, $config = []) {
        self::$view = Factory::CreateTemplates($type, $config);
        self::register();
    }

    /**
     * 注册smarty解析
     * @return null
     * @throws \SmartyException
     */
    protected static function register() {
        self::$view->register_prefilter('smarty_preFilterConstants');
        self::$view->register_function('constant', 'functionHash');
    }

    /**
     * 设置模板目录
     *
     * @param string $dir
     */
    public function setTemplateDir($dir = '') {
        $dir && self::$view->template_dir = $dir;
    }

    /**
     * 渲染模板
     *
     * @param        $filename
     * @param        $data
     * @param string $class 使用render方法的类
     *
     * @throws \system\MyError
     */
    public function render($filename, $data, $class = '') {
        if ($data) {
            $this->extractVars($data);
        }
        if (is_array($filename)) {
            self::extractVars($filename);
            $filename = null;
        }
        $addons = config('ADDON_PATH');
        $filename = _parseFileName($filename);
        if (strpos($class, $addons . '\\') === 0) {
            $class = str_replace('\\', '/', str_replace(config('DEFAULT_CONTROLLER_LAYER') . '\\', '', $class));
            $class = explode('/', $class);
            $class = [$class[0], $class[1], 'views'];
            self::$view->setTemplateDir(MyClass . DIRECTORY_SEPARATOR . implode('/', $class));
            $filename = ltrim($filename, '/');
        }
        try {
            return self::$view->display($filename);
        } catch (\SmartyException $e) {
            preg_match('/Unable to load template\s+\'file:(.*)\'/', $e->getMessage(), $match);
            if (count($match) > 0) {
                E('模板不存在 ' . $dir . $match[1]);
            } else {
                E($e->getMessage());
            }
        }
    }

    /**
     * 释放变量
     *
     * @param array $data  变量名|变量数组
     * @param mixed $value 变量值
     */
    public function extractVars($data = [], $value = null) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                self::$view->assign($key, $value);
            }
        } else {
            self::$view->assign($data, $value);
        }
    }
}
