#MyClassPHP
MyClassPHP是一个开源、免费的学习框架，也被称之为类库包，属于MVC框架成品
官方交流群，提交BUG群  438695935
##主要更新
###1.目录不在多样化，而是传统的web结构
###2.简单MVC实现多样控制器
例如：在/controllers 建立Admin目录，并建立Index.class.php，代码如下
```
namespace controllers\Admin;
use system\Controller;
class Index extends Controller{
    public function index(){
        echo 'Admin';
    }
}
```
然后配置路由 globals/routes.php，追加一条路由
```
'/hello' => '\controllers\Admin\Index@index'
```
最后完成如下
```
Route::add(array(
    '/' => '\controllers\Index@index' , 
    '/hello' => '\controllers\Admin\Index@index'
))
```

访问格式为
```
http://域名.com/index.php/hello
```
##主要功能
###1.模块化  
    A. 多模块化开发，功能更细化
###2.路由  
    A. index.php/hello
    B. index.php/hello/world
###3.控制器   
    A. 轻松几步搭建一个属于自己的MVC  
###4.模型  
    A. 模型CURD，提供强大的数据库操作类  
    B. 目前支持mysql , mysqli , pdo 方法连接数据库 , 其他后续更新  
###5.验证码  
    A. 快速创建验证码。省去配置  
###6.验证  
    A. 数据验证，提供内置强大验证方法  
###7.上传  
    A. 支持单文件多文件上传。自定义配置  
###8.表单  
    A. 快速创建表单，以及验证，表单提供安全验证  
###9.数据分页  
    A. 数据分页  
###10.缓存  
    A. 对数据进行缓存。读取。删除。清空  
###11.权限  
    A. 强大权限类  
 等一系列功能

##声明

MyClassPHP是一个开源免费的学习框架，免费开源