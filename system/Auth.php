<?php

namespace system;

use system\Route\Route;
use system\Tool\Doc;

/**
 * 权限trait
 * 需要自行实现auth_info和auth_group表，需包含以下字段信息
 * auth_info {uid,group_id}
 * auth_group {id,rules}
 * 快速开发，则复制以下sql，如有配置db_prefix信息，则以下的表名需要手动增加db_prefix信息
 *
 * CREATE TABLE `auth_info` (
 * `id` int(11) NOT NULL AUTO_INCREMENT,
 * `uid` int(11) DEFAULT '0' COMMENT '绑定的用户ID',
 * `group_id` int(11) DEFAULT '0' COMMENT '分组ID',
 * PRIMARY KEY (`id`),
 * UNIQUE KEY `uid` (`uid`)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8
 *
 * CREATE TABLE `auth_group` (
 * `id` int(11) NOT NULL AUTO_INCREMENT,
 * `name` varchar(40) DEFAULT NULL COMMENT '分组名',
 * `rules` text COMMENT '权限规则',
 * `create_time` int(10) DEFAULT NULL COMMENT '添加时间',
 * PRIMARY KEY (`id`)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8
 * @package system
 */
trait Auth {
    // 读取权限的目录、命名空间
    protected static $authNameSpace;
    // 命名空间
    protected static $nameSpace;
    // 权限名称
    protected static $name;
    // 权限规则
    protected static $rules = [];
    // 分组
    protected static $group = [];
    // 白名单，可用@white指定，将不受权限控制
    protected static $whiteList = [];
    // 是否超级管理员
    protected static $supper = false;
    // 记录分组的字段
    protected static $groupField;
    // 记录用户和分组绑定的字段
    protected static $groupUidField;
    // 记录分组对应的权限字段
    protected static $groupRulesField;
    protected static $childrenName = 'children';

    /**
     * 权限初始化方法
     * Auth constructor.
     */
    public final function __construct() {
        parent::__construct();
        $this->checkAuth();
        $this->onConstruct();
    }

    /**
     * 初始化方法
     */
    protected function onConstruct() {

    }

    /**
     * 获取命名空间
     * @return string
     */
    protected static function getNameSpace() {
        return '';
    }

    /**
     * 获取权限的名称
     * @return string
     */
    protected static function getName() {
        return 'auth';
    }

    /**
     * 获取分组的字段
     * @return string
     */
    protected static function getGroupField() {
        return 'group_id';
    }

    /**
     * 获取用户和分组绑定的字段
     * @return string
     */
    protected static function getGroupUidField() {
        return 'uid';
    }

    /**
     * 获取分组对应的权限字段
     * @return string
     */
    protected static function getGroupRulesField() {
        return 'rules';
    }

    /**
     * 获取子集名称
     * @return string
     */
    protected static function getChildrenName() {
        return 'children';
    }

    /**
     * 获取当前用户的id
     * @return int
     */
    protected function getAuthUid() {
        return 0;
    }

    /**
     * 当权限校验失败时会执行此方法
     */
    protected function onDied() {

    }

    /**
     * 当权限校验成功后会执行此方法
     */
    protected function onSuccess() {

    }

    /**
     * 白名单地址，进入该地址时将不进行权限校验
     * @return array
     */
    protected function whiteList() {
        return [];
    }

    /**
     * 已授权权限列表
     * @see ['User' => ['edit' => 1 , 'index' => 1]]
     *      以上数据代表着，用户具有User的index、edit方法的访问权限
     * @return array
     */
    protected function authorizedRules() {
        self::$groupField      = self::getGroupField();
        self::$groupUidField   = self::getGroupUidField();
        self::$groupRulesField = self::getGroupRulesField();
        $uid                   = $this->getAuthUid();
        self::$childrenName    = $this->getChildrenName();
        if (!$uid) {
            return [];
        }
        $group = M('AuthInfo')->getFind([self::$groupUidField => $uid], self::$groupField);
        if (!$group) {
            return [];
        }
        if ($group == 1) {
            self::$supper = true;

            return [];
        }
        $rules = M('AuthGroup')->getFind($group, self::$groupRulesField);
        if (!$rules) {
            return [];
        }
        $rules = json_decode($rules, true);

        return $rules;
    }

    /**
     * 检验权限
     * @return bool
     */
    protected final function checkAuth() {
        $className = static::class;
        $spaces    = explode('\\', self::class);
        array_pop($spaces);
        self::$authNameSpace = implode('\\', $spaces);
        self::$nameSpace     = self::getNameSpace();
        if (self::$nameSpace) {
            self::$authNameSpace .= '\\' . self::$nameSpace;
        }
        $authRules = self::getAuthRules();
        $auth      = $this->authorizedRules();
        $authOther = Config('AUTH_OTHER');
        // 使用白名单
        $whiteList       = $this->whiteList();
        self::$whiteList = array_merge(self::$whiteList, $whiteList);
        if (self::$whiteList) {
            $route = Route::getRoute();
            if (in_array($route, self::$whiteList)) {
                return true;
            }
            foreach (self::$whiteList as $key => $val) {
                if (is_array($val)) {
                    if ($val['namespace'] == $className && $val['name'] == ACTION_NAME) {
                        return true;
                    }
                }
            }
        }
        // 没有权限规则并且不是超级管理员
        if (!$auth && !$this->isSupper()) {
            $authOther ? $this->onDied() : $this->onSuccess();

            return false;
        }
        // 超级管理员直接成功
        if ($this->isSupper()) {
            $this->onSuccess();

            return true;
        }
        foreach ($authRules as $key => $val) {
            if ($val['namespace'] == $className && count($val[ self::$childrenName ]) > 0) {
                $useAuth = $auth[ $val['name'] ];
                // 校验子集方法
                foreach ($val[ self::$childrenName ] as $sk => $sv) {
                    if ($sv['name'] == ACTION_NAME) {
                        if (!$useAuth[ self::$childrenName ][ $sv['name'] ]) {
                            $this->onDied();

                            return false;
                        } else {
                            $this->onSuccess();

                            return true;
                        }
                        break;
                    }
                }
            }
        }
        $authOther ? $this->onDied() : $this->onSuccess();

        return false;
    }

    /**
     * 获取权限规则列表
     * @return array
     */
    protected final static function getAuthRules() {
        self::$name                 = self::getName();
        $basePath                   = APP_PATH . '/' . str_replace('\\', '/', self::$authNameSpace);
        self::$rules[ self::$name ] = [];
        foreach (scandir($basePath) as $val) {
            if ($val == '.' || $val == '..') {
                continue;
            }
            $path      = $basePath . '/' . $val;
            $pathInfo  = pathinfo($path);
            $className = $pathInfo['filename'];
            try {
                $class = new \ReflectionClass('\\' . self::$authNameSpace . '\\' . $className);
            } catch (\ReflectionException $e) {
                continue;
            }
            $docInfo = Doc::getDocInfo($class->getDocComment());
            if ($docInfo['auth'] || isset($docInfo['needAuth'])) {
                $rule = [
                    'name'              => $className,
                    'title'             => $docInfo['auth'],
                    'namespace'         => $class->getName(),
                    self::$childrenName => []
                ];
                // 是否设置分组
                if (isset($docInfo['group']) && $docInfo['group']) {
                    if (!isset(self::$group[ $docInfo['group'] ])) {
                        self::$group[ $docInfo['group'] ] = [];
                    }
                    array_push(self::$group[ $docInfo['group'] ], $class->getName());
                }
                // 解析方法
                foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                    if ($method->isConstructor()) {
                        continue;
                    }
                    if ($method->getDeclaringClass()->name != $class->getName()) {
                        continue;
                    }
                    $methodDocInfo = Doc::getDocInfo($method->getDocComment());
                    if (isset($methodDocInfo['white'])) {
                        array_push(self::$whiteList, [
                            'name'      => $method->getName(),
                            'namespace' => $class->getName(),
                        ]);
                    }
                    if (isset($methodDocInfo['auth']) || isset($docInfo['needAuth'])) {
                        array_push($rule[ self::$childrenName ], [
                            'name'  => $method->getName(),
                            'title' => $methodDocInfo['auth'],
                        ]);
                    }
                }
                self::$rules[ self::$name ][] = $rule;
            }
        }

        return self::$rules[ self::$name ];
    }

    /**
     * 获取授权的菜单列表
     *
     * @param array $data
     * [
     *    [
     *      'name' => '系统设置' ,
     *      'list' => [
     *          'name' => '网站设置' ,
     *          'auth_name' => 'Config' , // 权限的类名，假设是Config类
     *          'action' => 'index' // 权限的方法名，对应Config -> index()
     *      ]
     *    ]
     * ]
     *
     * @return array
     */
    protected function getAuthNavs($data = []) {
        $auths = self::authorizedRules();
        $navs  = [];
        foreach ($data as $key => $val) {
            $count = count($val['list']);
            if ($val['list'] && $count > 0) {
                $children = [];
                foreach ($val['list'] as $k => $v) {
                    $authName = ucfirst($v['auth_name']);
                    $action   = $v['action'] ? $v['action'] : 'index';
                    if ($auths[ $authName ]) {
                        $useAuths = $auths[ $authName ]['children'];
                        if ($useAuths[ $action ]) {
                            $children[] = $v;
                        }
                    }
                }
                $val['list'] = $children;
            }
            if (count($val['list']) > 0 || !isset($val['list'])) {
                $navs[] = $val;
            }
        }
        if ($this->isSupper()) {
            $navs = $data;
        }

        return $navs;
    }

    /**
     * 获取可以授权的权限列表
     * @return array
     */
    protected static function getRules() {
        $auth  = self::$rules[ self::$name ];
        $rules = [];
        foreach ($auth as $key => $val) {
            $rules[] = [
                'name'              => $val['name'],
                'title'             => $val['title'],
                self::$childrenName => $val[ self::$childrenName ]
            ];
        }

        return $rules;
    }

    /**
     * 是否超级管理员
     * @return bool
     */
    protected function isSupper() {
        return self::$supper;
    }
}