# MyClassPHP
MyClassPHP是一个开源、免费的学习框架。官方交流群 [438695935](https://shang.qq.com/wpa/qunwpa?idkey=1331030787e315dd0026359c55c757b439562acd0f1ee51855b709faf0e4652d)

## composer
git clone完成后，执行 
```
composer install
```

例如：在controllers 建立Index.php，代码如下
```
namespace controllers;
use system\Base;
class Hello extends Base{
    public function index(){
        return 'Hello MyClassPHP';
    }
}
```
打开 config/routes.php，追加一条路由
```
'/hello' => 'Hello@index'
```
配置完成如下
```
Route::add(array(
    '/' => 'Index@index' , 
    '/hello' => 'Hello@index'
))
```

运行
```
http://域名/hello
```

## 在线文档
[点我](https://www.kancloud.cn/amcolin/myclassphp_3_2_0/1325215)

## 声明

MyClassPHP是一个开源免费的学习框架，免费开源
