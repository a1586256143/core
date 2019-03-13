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
    //模板变量
    public static $vars = array();

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
            E("请设置 $groupName 路由组的属性");
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
                if($attr['middleware']){
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
        define('POST' , $request_method == 'POST' ? true : false);
        //定义get和post常量
        define('GET' , $request_method == 'GET' ? true : false);
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
                E('一个未定义的路由');
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
                            E('一个未定义的路由');
                        }
                    }
                }else{
                    E('一个未定义的路由');
                }
            }
            if(!$isFind){
                E('一个未定义的路由');
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
        $controllerOrAction = explode('@' , $route['route']);
        list($namespace , $method) = $controllerOrAction;
        $controller = new $namespace;
        //分割数组
        $class_name_array = explode('\\' , $namespace);
        //得到controllers\index 中的 index
        $get_class_name = array_pop($class_name_array);
        //拼接路径，并自动将路由中的index转换成Index
        $controller_path = APP_DIR . ltrim(implode('/' , $class_name_array) , '/') . '/' . ucfirst($get_class_name) . Config('DEFAULT_CLASS_SUFFIX');

        //是否存在控制器
        if(!file_exists($controller_path)){
            E($get_class_name . ' 控制器不存在！');
        }
        //控制器方法是否存在
        if(!method_exists($controller , $method)){
            E($method.'() 这个方法不存在');
        }
        //执行中间件
        if(!!$route['middleware']){
            $middleware = new $route['middleware'];
            $middleware->execMiddleware();
        }
        //处理跨站访问，或者cx攻击
        CSRF::execCSRF();
        //反射
        $ReflectionMethod = new \ReflectionMethod($controller , $method);
        $method_params = $ReflectionMethod->getParameters($method);
        //处理参数返回
        $param = array_filter(values('get.'));
        if(!empty($param)){
            if(!empty($method_params)){
                foreach ($method_params as $key => $value) {
                    $var[$value->name] = $param[$value->name];
                }
                self::showView($ReflectionMethod->invokeArgs($controller , array_filter($var)));
            }
        }
        self::showView($controller->$method());
    }

    /**
     * 显示视图
     * @return [type] [description]
     */
    protected static function showView($result){
        if(!$result){
            return '';
        }
        switch ($result) {
            case is_array($result) || is_object($result) :
                ajaxReturn($result);
                break;
            case is_file($result) : 
                extract(self::$vars);
                require $result;
                exit;
                break;
            default:
                exit($result);
                break;
        }
    }
}
?>