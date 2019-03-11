<?php
/**
 * 路由类
 * @author Colin <15070091894@163.com>
 */
namespace system\Route;
use system\Url;
use system\Route\CSRF;
class Route{
    //路由规则
    protected static $routes = array();

    /**
     * 初始化路由
     * @author Colin <15070091894@163.com>
     * @return [type] [description]
     */
    public static function init(){
        self::enableRoute();
    }

    /**
     * 设置路由
     * @param [type] $item [description]
     */
    protected static function setRoutes($key , $value){
        if(is_array($value)){
            self::$routes[$key] = array('middleware' => $value['middleware'] , 'route' => $value['route']);
        }else{
            self::$routes[$key] = array('route' => $value);
        }
    }

    /**
     * 合并控制器
     * @return [type] [description]
     */
    protected static function mergeController($controller){
        return '\\' . Config('DEFAULT_CONTROLLER_LAYER') . '\\' . $controller;
    }

    /**
     * 添加路由规则
     * @author Colin <15070091894@163.com>
     */
    public static function add($item){
        foreach ($item as $key => $value) {
            self::setRoutes($key , $value);
        }
    }

    /**
     * 路由分组
     * @param string $groupName 组名
     * @param array $attr 属性 array('middleware' => '中间件' , 'routes' => array('/index'));
     * @author Colin <15070091894@163.com>
     * @return [type] [description]
     */
    public static function group($groupName , $attr = array()){
        $parse_url = Url::parseUrl();
        //处理attr路由规则
        if(!$attr || !$attr['routes']){
            E("Please set $groupName the properties of the routing group");
        }
        //给$groupName增加/
        $groupName = '/' . ltrim($groupName , '/');
        foreach ($attr['routes'] as $key => $value) {
            //给key 增加 /
            $route = '/' . ltrim($key , '/');
            //处理根
            if($key == '/'){
                $route = '';
            }
            if(!isset($value['middleware'])){
                //是否中间件
                if(isset($attr['middleware'])){
                    is_array($value) ? $value['middleware'] = $attr['middleware'] : $value = array('route' => $value , 'middleware' => $attr['middleware']);
                }
            }
            self::setRoutes($groupName . $route , $value);
        }
    }

    /**
     * 开启Route
     * @return [type] [description]
     */
    public static function enableRoute(){
        self::parseRoutes();
    }

    /**
     * 解析路由
     * @author Colin <15070091894@163.com>
     * @return [type] [description]
     */
    public static function parseRoutes(){
        $parse_url = Url::parseUrl();
        //处理方法
        $request_method = $_SERVER["REQUEST_METHOD"];
        $request_with = $_SERVER["HTTP_X_REQUESTED_WITH"];
        define('POST' , $request_method == 'POST' ? true : false);
        //定义get和post常量
        define('GET' , $request_method == 'GET' ? true : false);
        //定义ajax
        define('AJAX' , strtolower($request_with) == 'xmlhttprequest' ? true : false);
        //寻找路由
        if(array_key_exists($parse_url , self::$routes)){
            self::execRoute(self::$routes[$parse_url]);
            //没有找到路由，开始找{}参数
        }else{
            $parse_url_array = explode('/' , rtrim(ltrim($parse_url , '/') , '/'));
            foreach( self::$routes as $key => $value ){
                $paramPatten = '/([\{\w\_\}]+)+/';
                if(preg_match_all($paramPatten , $key , $match)){
                    $preg_replace_param = preg_replace('/\/{([\w\_]+)}/' , '' , $key);
                    $key_array = explode('/' , rtrim(ltrim($key , '/') , '/'));
                    //位数一样
                    if(count($match[1]) == count($parse_url_array)){
                        //去除没有{}的
                        if(preg_match_all('/{([\w\_]+)}/' , implode('/' , $match[1]) , $matches)){
                            $equalLength[] = $match[1];
                        }
                        continue;
                    }
                }
            }
            //没有找到
            if(count($equalLength) == 0){
                E('An undefined route');
            }
            $isFind = false;
            //处理获取的长度数组
            foreach ($equalLength as $key => $value) {
                //拼装成 /hello/admin/{uid}
                $items = '/' . implode('/' , $value);
                //是否找到，找到直接停止允许
                if($isFind){
                    break;
                }
                //获取{的起始位置
                if($start = strpos($items , '{')){
                    //截取{后的位置，得到 /hello/admin/
                    $item = substr($items , 0 , $start);
                    //当前地址一样截取
                    $parse_url_item = substr($parse_url , 0 , $start);
                    //是否相等
                    if($item == $parse_url_item){
                        array_map('maps' , $parse_url_array , $value);
                        //执行
                        if(array_key_exists($items , self::$routes)){
                            $isFind = true;
                            self::execRoute(self::$routes[$items]);
                        }else{
                            E('An undefined route');
                        }
                    }
                }else{
                    E('An undefined route');
                }
            }
            if(!$isFind){
                E('An undefined route');
            }
        }
    }

    /**
     * 执行路由
     * @param array $route 当前执行的路由
     * @author Colin <15070091894@163.com>
     * @return [type] [description]
     */
    public static function execRoute($route){
        $route['route'] = self::mergeController($route['route']);
        $controllerOrAction = explode('@' , $route['route']);
        list($namespace , $method) = $controllerOrAction;
        $controller = new $namespace;
        //分割数组
        $class_name_array = explode('\\' , $namespace);
        //得到controllers\index 中的 index
        $get_class_name = array_pop($class_name_array);
        //拼接路径，并自动将路由中的index转换成Index
        $controller_path = APP_PATH . ltrim(implode('/' , $class_name_array) , '/') . '/' . ucfirst($get_class_name) . '.class.php';
        //是否存在控制器
        if(!file_exists($controller_path)){
            E($get_class_name . ' The controller does not exist!');
        }
        //控制器方法是否存在
        if(!method_exists($controller , $method)){
            E($method.'() This method does not exist');
        }
        //执行中间件
        if(!!isset($route['middleware'])){
            $middleware = new $route['middleware'];
            $middleware->execMiddleware();
        }
        //处理跨站访问，或者cx攻击
        CSRF::execCSRF();
        //反射
        $ReflectionMethod = new \ReflectionMethod($controller , $method);
        $method_params = $ReflectionMethod->getParameters($method);
        $get = values('get.');
        //处理参数返回
        $param = array_filter($get ? $get : array());
        if(!empty($param)){
            if(!empty($method_params)){
                foreach ($method_params as $key => $value) {
                    $var[$value->name] = $param[$value->name];
                }
                return $ReflectionMethod->invokeArgs($controller , array_filter($var));
            }
        }
        return $controller->$method();
    }
}
?>