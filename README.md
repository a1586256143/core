# MyClassPHP
MyClassPHP是一个开源、免费的学习框架。官方交流群 [438695935](https://shang.qq.com/wpa/qunwpa?idkey=1331030787e315dd0026359c55c757b439562acd0f1ee51855b709faf0e4652d)

## composer
git clone完成后，执行 
```
composer install
```

例如：在app/controllers 建立Index.php，代码如下
```
namespace controllers;
use system\Base;
class Hello extends Base{
    public function index(){
        return 'Hello MyClassPHP';
    }
}
```
打开 globals/routes.php，追加一条路由
```
'/hello' => '\controllers\Hello@index'
```
配置完成如下
```
Route::add(array(
    '/' => '\controllers\Index@index' , 
    '/hello' => '\controllers\Hello@index'
))
```

最后打开浏览器，进行访问
```
http://域名/index.php/hello
```

## 在线文档
[点我](http://lebook.me/book/142769)

## 声明

MyClassPHP是一个开源免费的学习框架，免费开源
