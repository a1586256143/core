<?php
/**
 * 表单生成
 * @author Colin <15070091894@163.com>
 */
namespace system;
class Form {
	public static $form;

	/**
	 * 打开表单
	 * @param string $action 表单提交地址
	 * @param string $method 表单方法
	 * @param  array $attr  表单的属性，可以为任意属性
	 * @author Colin <15070091894@163.com>
	 */
	public static function openForm($action = null , $method = 'post' , $attr = array()){
		$attr = walkFormAttr($attr);
		self::$form = "<form method='$method' action='$action' $attr>\n";
		self::echoForm();
		self::security();
	}

	/**
	 * 生成form校验码
	 * @param boolean $token 是否返回token
	 * @author Colin <15070091894@163.com>
	 */
	public static function security($token = false){
		for($i = 0; $i < 30; $i ++){
			$str .= dechex(mt_rand(0 , 15));
		}
		session('_token' , $str);
		if($token){
			return $str;
		}
		self::inputHidden('_token' , $str);
	} 

	/**
	 * 普通表单
	 * @param  string $name  input的名称
	 * @param  string $value input的值
	 * @param  array  $attr  表单的属性，可以为任意属性
	 * @author Colin <15070091894@163.com>
	 */
	public static function inputText($name = null , $value = null , $attr = array()){
		self::input('text' , $name , $value , $attr);
	}

	/**
	 * 隐藏表单
	 * @param  string $name  input的名称
	 * @param  string $value input的值
	 * @param  array  $attr  表单的属性，可以为任意属性
	 * @author Colin <15070091894@163.com>
	 */
	public static function inputHidden($name = null , $value = null , $attr = array()){
		self::input('hidden' , $name , $value , $attr);
	}

	/**
	 * 密码表单
	 * @param  string $name  input的名称
	 * @param  string $value input的值
	 * @param  array  $attr  表单的属性，可以为任意属性
	 * @author Colin <15070091894@163.com>
	 */
	public static function inputPass($name = null , $value = null , $attr = array()){
		self::input('password' , $name , $value , $attr);
	}

	/**
	 * input操作,所有操作的方法集合
	 * @param  string $type  input的类型，支持的有text、hidden、button、submit
	 * @param  string $name  input的名称
	 * @param  string $value input的值
	 * @param  array  $attr  表单的属性，可以为任意属性
	 * @author Colin <15070091894@163.com>
	 */
	public static function input($type = null , $name = null , $value = null , $attr = array()){
		$attr = walkFormAttr($attr);
		self::$form = "\t\t<input type='$type' name='$name' value='$value' $attr>\n";
		self::echoForm();
	}

	/**
	 * select下拉框
	 * @param  array  $args 	select 框的下拉选择项
	 * @param  string $name 	select的名称
	 * @param  string $value  	select的值
	 * @param  array  $attr  	表单的属性，可以为任意属性
	 * @author Colin <15070091894@163.com>
	 */
	public static function select($args = array() , $name = null , $value = null , $attr = array()){
		$attr = walkFormAttr($attr);
		self::$form = "\t\t<select name='$name' $attr>\n";
		foreach ($args as $k => $v) {
			$selected = $k == $value ? " selected='selected' " : '';
			self::$form .= "\t\t\t\t<option value='$k' $selected>$v</option>\n";
		}
		self::$form .= "\t\t</select>\n";
		self::echoForm();
	}

	/**
	 * textarea文本域
	 * @param  string $name  textarea的名称
	 * @param  string $value textarea的值
	 * @param  array  $attr  表单的属性，可以为任意属性
	 * @author Colin <15070091894@163.com>
	 */
	public static function textarea($name = null , $value = null , $attr = array()){
		$attr = walkFormAttr($attr);
		self::$form = "\t\t<textarea name='$name' $attr>$value</textarea>\n";
		self::echoForm();
	}

	/**
	 * 按钮
	 * @param  string $type  	button的类型，支持的有button、submit、reset
	 * @param  string $name 	button的名称
	 * @param  string $value  	button的值
	 * @param  array  $attr  	表单的属性，可以为任意属性
	 * @author Colin <15070091894@163.com>
	 */
	public static function button($type = null , $name = null , $value = '确定' , $attr = array()){
		$attr = walkFormAttr($attr);
		self::$form = "\t\t<button type='$type' name='$name' $attr>$value</button>\n";
		self::echoForm();
	}

	/**
	 * 提交按钮
	 * @param  string $name  button的名称
	 * @param  string $value button的值
	 * @param  array  $attr  表单的属性，可以为任意属性
	 * @author Colin <15070091894@163.com>
	 */
	public static function submitButton($name = null , $value = null , $attr = array()){
		self::button('submit' , $name , $value , $attr);
	}

	/**
	 * 普通按钮，无事件
	 * @param  string $name  button的名称
	 * @param  string $value button的值
	 * @param  array  $attr  表单的属性，可以为任意属性
	 * @author Colin <15070091894@163.com>
	 */
	public static function generalButton($name = null , $value = null , $attr = array()){
		self::button('button' , $name , $value , $attr);
	}
	
	/**
	 * 重置按钮
	 * @param  string $name  button的名称
	 * @param  string $value button的值
	 * @param  array  $attr  表单的属性，可以为任意属性
	 * @author Colin <15070091894@163.com>
	 */
	public static function resetButton($name = null , $value = null , $attr = array()){
		self::button('reset' , $name , $value , $attr);
	}

	/**
	 * 关闭表单
	 * @author Colin <15070091894@163.com>
	 */
	public static function closeForm(){
		self::$form = "\t\t</form>\n";
		self::echoForm();
	}

	/**
	 * 输出表单
	 * @author Colin <15070091894@163.com>
	 */
	protected function echoForm(){
		echo self::$form;
	}
}