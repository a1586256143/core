## 更新日志

* 2020.5.20
    - 权限控制的重构
    - 新增`Doc`类，读取注释信息
    - 解决存储Json读取时被转义问题

* 2020.4.16
	- 去除`Base`中的success、error方法
	- 优化`Route`并可选controller、get、post、delete、put、any方法
	- 优化`values`方法
	- 新增`envg`，查找配置值，若无则返回默认值
	- 新增`Model`类`group`、`having`、`subQueryCount`方法，优化可对模型单独设置数据库名、前缀，`sort`排序
	- 优化路由中的查找顺序，去除路由开关

* 2020.3.20
	- 新增`addons`插件库
	- 优化错误提示
	- 对返回json友好输出

* 2020.1.19
    - 新增`find_in_set`查询
    - `Model`中的`where`支持多条件查询
    - 优化`Model`的`join`方法
    - 新增`field`函数，让SQL语句更加原生