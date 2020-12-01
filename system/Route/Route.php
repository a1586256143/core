<?php
/**
 * 路由类
 * @author Colin <15070091894@163.com>
 */

namespace system\Route;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use system\Base;
use system\IO\File\Log;
use system\MyError;
use system\Url;

class Route {
    //路由规则
    protected static $routes = [];
    // 默认匹配的请求类型
    protected static $method = '*';
    // 路由前缀
    protected static $prefix = '';
    // 当前级信息
    protected static $group = [];

    /**
     * 初始化路由
     * @author Colin <15070091894@163.com>
     * @throws MyError
     */
    public static function init() {
        self::setRunMethod();
        if (!PHP_CLI) {
            self::parseRoutes();
        }
    }

    /**
     * 添加路由规则
     *
     * @param array  $item 路由 array('/' => 'Index@index')
     * @param string $type 类型，GET、POST、PUT、DELETE、*
     * @param string $middleware 中间件
     *
     * @author Colin <15070091894@163.com>
     */
    public static function add($item, $type = '' , $middleware = '') {
        foreach ($item as $key => $value) {
            self::parseRules($key, $value, $type ? $type : self::$method , $middleware);
        }
    }

    /**
     * GET方法
     *
     * @param string $name
     * @param string $item
     * @param string $middleware
     */
    public static function get($name, $item, $middleware = '') {
        self::parseRules($name, $item, 'GET', $middleware);
    }

    /**
     * Any方法
     *
     * @param string $name
     * @param string $item
     * @param string $middleware
     */
    public static function any($name, $item, $middleware = '') {
        self::parseRules($name, $item, '*', $middleware);
    }

    /**
     * POST方法
     *
     * @param string $name
     * @param string $item
     * @param string $middleware
     */
    public static function post($name, $item, $middleware = '') {
        self::parseRules($name, $item, 'POST', $middleware);
    }

    /**
     * PUT方法
     *
     * @param string $name
     * @param string $item
     * @param string $middleware
     */
    public static function put($name, $item, $middleware = '') {
        self::parseRules($name, $item, 'PUT', $middleware);
    }

    /**
     * DELETE方法
     *
     * @param string $name
     * @param string $item
     * @param string $middleware
     */
    public static function delete($name, $item, $middleware = '') {
        self::parseRules($name, $item, 'PUT', $middleware);
    }

    /**
     * 解析控制器中的方法映射路由
     *
     * @param string          $name
     * @param string|Closure $item
     * @param string          $middleware
     *
     * @throws
     */
    public static function controller($name, $item, $middleware = '') {
        self::parseController($name, $item, $middleware);
    }

    /**
     * 分组
     *
     * @param                $prefix
     * @param array|Closure $item
     * @param string         $middleware
     *
     * @throws
     */
    public static function group($prefix, $item, $middleware = '') {
        if (is_string($item)) {
            E('Router Fail : group set fail');
        }
        // 第一次进来，是一个null，setGroupName('admin')，第二次进来router是user，prefix是一个admin，setGroupName('admin/user')
        $group = self::getGroupName('name');
        if ($group) {
            $prefix = $group . '/' . ltrim($prefix, '/');
        }
        self::$prefix = self::getPrefix($prefix);
        self::setGroupName($prefix);
        switch (true) {
            case ($item instanceof Closure) :
                call_user_func($item);
                break;
            case is_array($item) :
                self::add($item , self::$method , $middleware);
                break;
        }
        self::setGroupName($group);
        self::$prefix = '';
    }

    /**
     * 获取当前分组信息
     *
     * @param string $type
     *
     * @return mixed|null
     */
    protected static function getGroupName($type = '') {
        if (isset(self::$group[ $type ]) && self::$group[ $type ]) {
            return self::$group[ $type ];
        }

        return 'name' == $type ? null : [];
    }

    /**
     * 设置当前分组信息
     *
     * @param $name
     */
    protected static function setGroupName($name) {
        self::$group['name'] = $name;
    }

    /**
     * 解析路由
     * @author Colin <15070091894@163.com>
     * @throws MyError
     */
    protected static function parseRoutes() {
        $parse_url = self::getRoute();
        // 验证访问的是否是一个文件
        $routeKey = self::getRouteName($parse_url);
        $routeAll = self::getRouteNameAll($parse_url);
        //寻找路由
        if (array_key_exists($routeKey, self::$routes)) {
            self::execRoute(self::$routes[ $routeKey ]);
        } else {
            if (array_key_exists($routeAll, self::$routes)) {
                self::execRoute(self::$routes[ $routeAll ]);

                return;
            }
            //没有找到路由，开始找{}参数
            $parse_url_array = explode('/', rtrim(ltrim($routeKey, '/'), '/'));
            $currentRoute    = (implode('/', $parse_url_array));
            foreach (self::$routes as $key => $value) {
                if ($value['type'] != '*') {
                    if ($value['type'] != REQUEST_METHOD) {
                        continue;
                    }
                }
                $oldKey = explode('/', $key);
                // 把*替换成当前请求的方法名
                $key = str_replace('*', REQUEST_METHOD, $key);
                // 查找出带变量名的路由格式 /xxx/xxx/{id}
                if (preg_match_all('/{([\w_]+)}/', $key, $matchs)) {
                    // matchs[0] = ['{id}']
                    foreach ($matchs[0] as $val) {
                        // 把变量替换成正则表达式 [\w]+
                        $key = str_replace($val, '[\w\-]+', $key);
                    }
                    // 开始正则匹配 $currentRoute = GET-/api/article/list/28
                    // 正则表达式为 #^GET-/api/article/list/[\w]+$#
                    preg_match('#^' . $key . '$#', $currentRoute, $match);
                    if ($match[0]) {
                        $urls = explode('/', $parse_url);
                        array_map('maps', array_slice($urls, 1), array_slice($oldKey, 1));
                        self::execRoute(self::$routes[ implode('/', $oldKey) ]);
                    }
                }
            }
            self::faildRoute($parse_url);
        }
    }

    /**
     * 失败的路由
     *
     * @param $url
     *
     * @throws MyError
     */
    protected static function faildRoute($url) {
        // 验证是否为一个文件
        $info = pathinfo($url);
        if ($info['extension']) {
            http_response_code(404);
            exit;
        }
        self::execRouteByUrl();
    }

    /**
     * 执行路由
     *
     * @param array $route 当前执行的路由
     *
     * @author Colin <15070091894@163.com>
     * @throws
     */
    protected static function execRoute($route) {
        if ($route['route'] instanceof Closure) {
            $data = call_user_func($route['route']);
            self::showView($data);
        }
        $controllerOrAction = explode('@', $route['route']);
        list($namespace, $method) = $controllerOrAction;
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
        $controller = new $namespace;
        //控制器方法是否存在
        if (!method_exists($controller, $method)) {
            E($method . '() 这个方法不存在');
        }
        //执行中间件
        if (isset($route['middleware']) && $route['middleware']) {
            /**
             * @var $middleware Middleware
             */
            $middleware = new $route['middleware'];
            $middleware->execMiddleware($controller, new self());
        }
        //处理跨站访问，或者cx攻击
        CSRF::execCSRF();
        //反射
        self::reflection($controller, $method);
    }

    /**
     * 反射类
     *
     * @param object $controller 被执行的控制器实体类
     * @param string $method     被执行的控制器方法名
     *
     * @throws MyError
     */
    public static function reflection($controller, $method) {
        try {
            //反射
            $ReflectionMethod = new ReflectionMethod($controller, $method);
        } catch (ReflectionException $e) {
            throw new MyError($e->getMessage());
        }
        $method_params = $ReflectionMethod->getParameters();
        //处理参数返回
        $get   = values('get.');
        $post  = values('post.');
        $param = $get ?: [];
        $post  = $post ?: [];
        $param = array_merge($param, $post);
        if (!empty($param)) {
            if (!empty($method_params)) {
                $var = [];
                foreach ($method_params as $key => $value) {
                    $var[ $value->name ] = $param[ $value->name ];
                }
                self::showView($ReflectionMethod->invokeArgs($controller, $var));
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
     *
     * @param mixed $result
     */
    protected static function showView($result = '') {
        Log::generator();
        switch (true) {
            case is_array($result) || is_object($result) :
                echo ajaxReturn($result);
                break;
            default:
                if (AJAX) {
                    echo(success($result));
                    exit;
                }
                echo($result === null ? '' : $result);
                break;
        }
        exit;
    }

    /**
     * 获取路由url
     *
     * @param        $key
     * @param string $type
     *
     * @return string
     */
    protected static function getRouteName($key, $type = '') {
        $type = $type ?: REQUEST_METHOD;
        $key  = self::getPrefix($key);

        return $type . '-' . self::$prefix . $key;
    }

    /**
     * 获取*路由url
     *
     * @param $key
     *
     * @return string
     */
    protected static function getRouteNameAll($key) {
        $key = self::getPrefix($key);

        return '*-' . self::$prefix . $key;
    }

    /**
     * 获取路由名
     *
     * @param $key
     *
     * @return string
     */
    protected static function getPrefix($key) {
        $key = DIRECTORY_SEPARATOR . ltrim($key, DIRECTORY_SEPARATOR);

        return $key;
    }

    /**
     * 解析一个路由规则
     *
     * @param string          $name       规则名
     * @param string|Closure $item       规则元素
     * @param string          $type       访问类型
     * @param string          $middleware 中间件
     */
    protected static function parseRules($name, $item, $type = '', $middleware = '') {
        $key                  = self::getRouteName($name, $type);
        $route                = $item instanceof Closure ? $item : self::getNameSpace($item);
        self::$routes[ $key ] = [
            'route'      => $route,
            'middleware' => $middleware,
            'type'       => $type,
        ];
    }

    /**
     * 解析Controller方法
     *
     * @param        $name
     * @param        $item
     * @param string $middleware
     *
     * @throws MyError
     */
    protected static function parseController($name, $item, $middleware = '') {
        $route = $item instanceof Closure ? $item : self::getNameSpace($item);
        if (!$item instanceof Closure) {
            $route = explode('@', $route);
            array_pop($route);
            $class = $route[0];
        } else {
            $class = call_user_func($item);
        }
        try {
            $reflect = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new MyError($e->getMessage());
        }
        $items   = [];
        $methods = $reflect->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $value) {
            if (!$value->isConstructor() && $value->isPublic()) {
                $items[ $value->name ] = '\\' . $reflect->name . '@' . $value->name;
            }
        }
        self::group($name, $items, $middleware);
    }

    /**
     * 获取命名空间
     *
     * @param null $url        访问URL
     * @param bool $middleware 中间件
     *
     * @return string|null
     */
    protected static function getNameSpace($url = null, $middleware = false) {
        $layer  = Config('DEFAULT_CONTROLLER_LAYER');
        $prefix = '\\' . ltrim($layer, '\\');
        $url    = '\\' . ltrim($url, '\\');
        if (strpos($url, $prefix) === 0) {
            return $url;
        }
        $url = ltrim($url, '\\');
        // 解析有没有@字符
        if (strpos($url, '@') === false && !$middleware) {
            $url .= '@' . Config('DEFAULT_METHOD');
        }

        return $prefix . '\\' . $url;
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
        define('REQUEST_METHOD', strtoupper($_SERVER["REQUEST_METHOD"]));
        define('POST', REQUEST_METHOD == 'POST');
        //定义get和post常量
        define('GET', REQUEST_METHOD == 'GET');
        $httpRequest = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? $_SERVER['HTTP_X_REQUESTED_WITH'] : '';
        define('AJAX', $httpRequest == 'XMLHttpRequest');
        header_remove('X-Powered-By');
    }

    /**
     * 根据URL执行路由
     * @throws MyError
     */
    protected static function execRouteByUrl() {
        $route  = Url::parseUrl();
        $routes = explode('/', $route);
        list($controller_path, $classname) = self::getControllerPath($routes);
        $defaultMethod = Config('DEFAULT_METHOD');
        $controller    = false;
        if (file_exists($controller_path)) {
            // 解析className
            // construct中使用ACTION_NAME无法被解析
            $method = $defaultMethod;
            self::setFields(array_pop($routes), $method);
            $controller = new $classname;
        } else {
            $method = array_pop($routes);
            list($controller_path, $classname) = self::getControllerPath($routes);

            //是否存在控制器
            if (!file_exists($controller_path)) {
                E($classname . ' 控制器不存在！');
            }
            self::setFields(array_pop($routes), $method);
        }
        /**
         * @var $controller Base
         */
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
     * @param array $routes
     *
     * @return array
     */
    protected static function getControllerPath($routes) {
        // 是不是模块
        $addonsUrlVar = Config('ADDONS_URL_VAR') ? Config('ADDONS_URL_VAR') : 'a';
        if (count(array_filter($routes)) == 0) {
            $routes = ['', ''];
        }
        $isAddons = $routes[1] == $addonsUrlVar;
        $layer    = $isAddons ? Config('DEFAULT_ADDONS_LAYER') : Config('DEFAULT_CONTROLLER_LAYER');
        if ($isAddons) {
            unset($routes[1]);
        }
        $routes = array_filter($routes);
        // 尝试解析index方法
        // 拼接路径，并自动将路由中的index转换成Index
        $controller_path   = _getFileName(APP_DIR . $layer . '/' . ltrim(implode('/', $routes), '/'));
        $extraRoute        = $routes;
        $defaultController = Config('DEFAULT_CONTROLLER');
        array_push($extraRoute, $defaultController);
        $extraRoutePath = _getFileName(APP_DIR . $layer . '/' . ltrim(implode('/', $extraRoute), '/'));
        $classname      = implode('\\', $routes);
        // 如果文件不存在，尝试加载目录下的默认文件
        if (!file_exists($controller_path) && $routes[ count($routes) - 1 ] != strtolower($defaultController)) {
            $controller_path = $extraRoutePath;
            $classname       = implode('\\', $extraRoute);
        }
        $classname = '\\' . $layer . '\\' . $classname;

        return [$controller_path, $classname];
    }
}
