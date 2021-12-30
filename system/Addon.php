<?php
// 插件父类
namespace system;
abstract class Addon {

    /**
     * 获取插件名
     * @return mixed
     */
    abstract public function getName();

    /**
     * 获取版本
     * @return mixed
     */
    abstract public function getVersion();

    /**
     * 获取作者
     * @return mixed
     */
    abstract public function getAuthor();

    /**
     * 获取描述
     * @return mixed
     */
    abstract public function getDesc();

    /**
     * 安装前做的事情
     * @return bool
     */
    public function setBefore() {
        return true;
    }

    /**
     * 安装后做的事情
     * @return bool
     */
    public function setAfter() {
        return true;
    }

    /**
     * 安装
     * @return bool
     */
    public function install() {
        return true;
    }

    /**
     * 卸载
     * @return bool
     */
    public function uninstall() {
        return true;
    }


    /**
     * 绑定一些导航菜单
	 * @param array $data
	 * [
        * 'name'  => 'shop',
        * 'title' => '商品管理',
        * 'icon'  => 'layui-icon-set',
        * ],
	 * @return array
     */
    public function onInitMenu(&$data = []){
        return [];
    }

	/**
	 * 升级信息 ['1.0.0' => '升级信息']
	 * @author Colin <amcolin@126.com>
	 * @date 2021-12-30 下午4:17
	 */
    public function upgrade(){
		return [];
	}
}
