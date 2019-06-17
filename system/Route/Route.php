<?php
/**
 * 路由类
 * @author Colin <15070091894@163.com>
 */

namespace system\Route;

use system\Log;
use system\Url;
use system\Route\CSRF;

class Route {
    //路由规则
    protected static $routes = [];

    /**
     * 初始化路由
     * @author Colin <15070091894@163.com>
     */
    public static function init() {
        self::setRunMethod();
        if (!PHP_CLI) {
            Config('ROUTE_STATUS') ? self::enableRoute() : self::execRouteByUrl();
        }
    }

    /**
     * 设置路由
     *
     * @param [type] $item [description]
     */
    protected static function setRoutes($key, $value, $group = false) {
        if (is_array($value)) {
            self::$routes[ $key ] = [
                'middleware' => self::parseClassName($key, $value['middleware']),
                'route'      => self::compleNamespace($key, $value['route']),
                'group'      => $group
            ];
        } else {
            self::$routes[ $key ] = [
                'route' => self::compleNamespace($key, $value),
                'group' => $group
            ];
        }
    }

    /**
     * 组装明名空间
     * @return [type] [description]
     */
    protected static function compleNamespace($key, $url = null) {
        $prefix = '\\' . Config('DEFAULT_CONTROLLER_LAYER');

        if (strpos($url, $prefix) === 0) {
            return $url;
        }
        if (strpos($url, '\\') === 0) {
            $url = ltrim($url, '\\');
        }
        // 解析有没有@字符
        if (strpos($url, '@') === false) {
            $url .= '@' . Config('DEFAULT_METHOD');
        }
        $url = self::parseClassName($key, $url);

        return $prefix . '\\' . $url;
    }

    /**
     * 解析命名空间
     *
     * @param null $key
     * @param null $url
     */
    protected static function parseClassName($key = null, $url = null) {
        // 解析#号
        if (strpos($url, '#') !== false) {
            $prefix = array_shift(array_filter(explode('/', $key)));
            $url    = str_replace('#', $prefix, $url);
        }
        if (strpos($url, '\\') === 0) {
            $url = ltrim($url, '\\');
        }

        return $url;
    }

    /**
     * 添加路由规则
     * @author Colin <15070091894@163.com>
     */
    public static function add($item) {
        foreach ($item as $key => $value) {
            self::setRoutes($key, $value);
        }
    }

    /**
     * 路由分组
     *
     * @param string $groupName 组名
     * @param array  $attr      属性 array('middleware' => '中间件' , 'routes' => array('/index'));
     *
     * @author Colin <15070091894@163.com>
     * @throws
     */
    public static function group($groupName, $attr = []) {
        $parse_url = Url::parseUrl();
        //处理attr路由规则
        if (!$attr || !$attr['routes']) {
            E("请设置 $groupName 路由组的属性");
        }
        //给$groupName增加/
        $groupName = '/' . ltrim($groupName, '/');
        foreach ($attr['routes'] as $key => $value) {
            //给key 增加 /
            $route = '/' . ltrim($key, '/');
            //处理根
            if ($key == '/') {
                $route = '';
            }
            if (!isset($value['middleware'])) {
                //是否中间件
                if ($attr['middleware']) {
                    is_array($value) ? $value['middleware'] = $attr['middleware'] : $value = [
                        'route'      => $value,
                        'middleware' => $attr['middleware']
                    ];
                }
            }
            self::setRoutes($groupName . $route, $value, true);
        }
    }

    /**
     * 是否是一个组
     * @return boolean [description]
     */
    protected static function isGroup($route = null) {
        if (isset(self::$routes[ $route ]['group'])) {
            $route = array_filter(explode('/', $route));

            return array_shift($route);
        }

        return false;
    }

    /**
     * 开启Route
     */
    public static function enableRoute() {
        self::parseRoutes();
    }

    /**
     * 解析路由
     * @author Colin <15070091894@163.com>
     */
    public static function parseRoutes() {
        $parse_url   = self::getRoute();
        $equalLength = [];
        //寻找路由
        if (array_key_exists($parse_url, self::$routes)) {
            self::execRoute(self::$routes[ $parse_url ]);
            //没有找到路由，开始找{}参数
        } else {
            $parse_url_array = explode('/', rtrim(ltrim($parse_url, '/'), '/'));
            foreach (self::$routes as $key => $value) {
                $paramPatten = '/([\{\w\_\}]+)+/';
                if (preg_match_all($paramPatten, $key, $match)) {
                    //位数一样
                    if (count($match[1]) == count($parse_url_array)) {
                        //去除没有{}的
                        if (preg_match_all('/{([\w\_]+)}/', implode('/', $match[1]), $matches)) {
                            $equalLength[] = $match[1];
                        }
                        continue;
                    }
                }
            }
            //没有找到
            if (count($equalLength) == 0) {
                E('一个未定义的路由');
            }
            $isFind = false;
            //处理获取的长度数组
            foreach ($equalLength as $key => $value) {
                //拼装成 /hello/admin/{uid}
                $items = '/' . implode('/', $value);
                //是否找到，找到直接停止允许
                if ($isFind) {
                    break;
                }
                //获取{的起始位置
                if ($start = strpos($items, '{')) {
                    //截取{后的位置，得到 /hello/admin/
                    $item = substr($items, 0, $start);
                    //当前地址一样截取
                    $parse_url_item = substr($parse_url, 0, $start);
                    //是否相等
                    if ($item == $parse_url_item) {
                        array_map('maps', $parse_url_array, $value);
                        //执行
                        if (array_key_exists($items, self::$routes)) {
                            $isFind = true;
                            self::execRoute(self::$routes[ $items ]);
                        } else {
                            E('一个未定义的路由');
                        }
                    }
                } else {
                    E('一个未定义的路由');
                }
            }
            if (!$isFind) {
                E('一个未定义的路由');
            }
        }
    }

    /**
     * 执行路由
     *
     * @param array $route 当前执行的路由
     *
     * @author Colin <15070091894@163.com>
     * @return string
     * @throws
     */
    public static function execRoute($route) {
        $controllerOrAction = explode('@', $route['route']);
        list($namespace, $method) = $controllerOrAction;
        $controller = new $namespace;
        //分割数组
        $class_name_array = explode('\\', $namespace);
        //得到controllers\index 中的 index
        $get_class_name = array_pop($class_name_array);
        self::setFields($get_class_name, $method);
        //拼接路径，并自动将路由中的index转换成Index
        $controller_path = _getFileName(APP_DIR . ltrim(implode('/', $class_name_array), '/') . '/' . ucfirst($get_class_name));
        //是否存在控制器
        if (!file_exists($controller_path)) {
            E($get_class_name . ' 控制器不存在！');
        }
        //控制器方法是否存在
        if (!method_exists($controller, $method)) {
            E($method . '() 这个方法不存在');
        }
        //执行中间件
        if (!!$route['middleware']) {
            $middleware = new $route['middleware'];
            $middleware->execMiddleware($controller, new self());
        }
        //处理跨站访问，或者cx攻击
        CSRF::execCSRF();
        //反射
        self::reflection($controller, $method);
    }

    /**
     * 根据URL执行路由
     * @throws \system\MyError
     */
    protected static function execRouteByUrl() {
        $route  = Url::parseUrl();
        $routes = explode('/', $route);
        list($controller_path, $classname) = self::getControllerPath($routes);
        $defaultMethod = Config('DEFAULT_METHOD');
        $controller    = false;
        if (file_exists($controller_path)) {
            // 解析className
            $controller = new $classname;
            $isExists   = method_exists($controller, $defaultMethod);
            $method     = $defaultMethod;
        } else {
            $method = array_pop($routes);
            list($controller_path, $classname) = self::getControllerPath($routes);
            //是否存在控制器
            if (!file_exists($controller_path)) {
                E($get_class_name . ' 控制器不存在！');
            }
        }
        self::setFields(array_pop($routes), $method);
        !$controller && $controller = new $classname;
        //控制器方法是否存在
        if (!method_exists($controller, $method)) {
            E($method . '() 这个方法不存在');
        }
        self::reflection($controller, $method);
    }

    /**
     * 获取控制器路径和控制器类名
     *
     * @param $routes
     *
     * @return array
     */
    protected static function getControllerPath($routes) {
        // 是不是模块
        $addonsUrlVar = Config('ADDONS_URL_VAR') ? Config('ADDONS_URL_VAR') : 'a';
        $isAddons     = $routes[1] == $addonsUrlVar ? true : false;
        $layer        = $isAddons ? Config('DEFAULT_ADDONS_LAYER') : Config('DEFAULT_CONTROLLER_LAYER');
        if ($isAddons) {
            unset($routes[1]);
        }
        // 尝试解析index方法
        //拼接路径，并自动将路由中的index转换成Index
        $controller_path = APP_DIR . $layer . '/' . ltrim(implode('/', $routes), '/') . Config('DEFAULT_CLASS_SUFFIX');
        $classname       = '\\' . $layer . implode('\\', $routes);

        return [$controller_path, $classname];
    }

    /**
     * 设置常量
     *
     * @param [type] $controller [description]
     * @param [type] $method     [description]
     */
    protected static function setFields($controller, $method) {
        define('CONTROLLER_NAME', $controller);
        define('ACTION_NAME', $method);
    }

    /**
     * 设置运行方式
     */
    protected static function setRunMethod() {
        //处理方法
        $request_method = $_SERVER["REQUEST_METHOD"];
        define('POST', $request_method == 'POST' ? true : false);
        //定义get和post常量
        define('GET', $request_method == 'GET' ? true : false);
        $httpRequest = $_SERVER['HTTP_X_REQUESTED_WITH'];
        define('AJAX', $httpRequest == 'XMLHttpRequest' ? true : false);
    }

    /**
     * 反射类
     *
     * @param object $controller 被执行的控制器实体类
     * @param string $method     被执行的控制器方法名
     *
     * @throws \ReflectionException
     */
    protected static function reflection($controller, $method) {
        //反射
        $ReflectionMethod = new \ReflectionMethod($controller, $method);
        $method_params    = $ReflectionMethod->getParameters($method);
        //处理参数返回
        $param = array_filter(values('get.'));
        $post  = array_filter(values('post.'));
        if (!$param) {
            $param = [];
        }
        if (!$post) {
            $post = [];
        }
        $param = array_merge($param, $post);
        if (!empty($param)) {
            if (!empty($method_params)) {
                foreach ($method_params as $key => $value) {
                    $var[ $value->name ] = $param[ $value->name ];
                }
                self::showView($ReflectionMethod->invokeArgs($controller, array_filter($var)));
            }
        }
        self::showView($controller->$method());
    }

    /**
     * 获取当前路由地址
     * @return mixed|string
     */
    public static function getRoute() {
        return Url::parseUrl();
    }

    /**
     * 显示视图
     */
    protected static function showView($result = '') {
        switch (true) {
            case is_array($result) || is_object($result) :
                ajaxReturn($result);
                break;
            default:
                if (AJAX) {
                    echo(success($result));
                    exit;
                }
                echo($result === null ? '' : $result);
                exit;
                break;
        }
    }
}