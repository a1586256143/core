<?php
/**
 * 模板
 * @author Colin <15070091894@163.com>
 */
namespace system\Templates\CMS;
use system\MyError;
use system\ObjFactory;
use system\Url;
use system\Templates\extendTemplate;
class CMS implements extendTemplate{

	protected static $tpl;

	/**
	 * 初始化标签
	 * @return [type] [description]
	 */
	public static function initTags(){
		$tags = array(
					'list' => array(
						'name' 	=> array('default' => '$name' , 'isvar'  => 1) , 
						'key' 	=> array('default' => '$key' , 'isvar'  => 1) , 
						'table' => array('default' => 'document' , 'isvar'  => 0) ,
						'id' 	=> array('default' => '$value' , 'isvar'  => 1) , 
						'field' => array('default' => '*' , 'isvar'  => 0) , 
						'limit' => array('default' => 10 , 'isvar'  => 0) , 
					)
				);
		return $tags;
	}

	/**
	 * 解析方法
	 * @param  [type] $tpl 模板内容
	 * @return [type]      [description]
	 */
	public function parse($tpl){
		self::$tpl = $tpl;
		$tags = self::initTags();
		//解析标签方法
		foreach ($tags as $key => $value) {
			$patten = '/\{cms:' . $key . '(.*?)\}/';
			$endpatten = '/\{\/cms:' . $key . '\}/';
			if(preg_match($patten , self::$tpl , $match)){
				//匹配属性
				list( , $preg_attr) = $match;
				//解析属性
				$items = self::_parse_attr($preg_attr);
				//自动填充数据，得到属性值
				$items = self::_auto_append($value , $items);
				switch ($key) {
					case 'list':
						self::_parse_list($patten , $endpatten , $items);
						break;
				}
			}
			
		}
		return self::$tpl;
	}

	/**
	 * 解析列表
	 * @return [type] [description]
	 */
	public static function _parse_list($patten , $endpatten , $items){
		$string = '<?php $_list_ = M(' . ucfirst($items['table']) . ')';
		$string .= '->field(\'' . $items['field'] . '\')';
		$string .= '->limit(' . $items['limit'] . ')';
		$string .= '->select();?>';
		$string .= '{foreach($_list_ as ' . $items['key'] . ' => ' . $items['id'] . ')}';
		$endstring = '{/foreach}';
		//替换
		self::$tpl = preg_replace($patten , $string , self::$tpl);
		//替换
		self::$tpl = preg_replace($endpatten, $endstring , self::$tpl);
	}

	/**
	 * 解析属性值
	 * @return [type] [description]
	 */
	protected static function _parse_attr($attr_tpl = null){
		$attrPatten = '/([a-z\s]+)=[\"\'\s]+([\$\w\,]+)/';
		preg_match_all($attrPatten , $attr_tpl , $mts);
		list( , $keys , $value) = $mts;
		$tmpCombine = array_combine($keys , $value);
		$combine = array();
		//清除空格
		foreach ($tmpCombine as $key => $value) {
			$combine[trim($key)] = htmlspecialchars(addslashes(trim($value)));
		}
		return $combine;
	}

	/**
	 * 自动验证数据，并填充默认值
	 * @return [type] [description]
	 */
	protected static function _auto_append($default , $items){
		foreach($default as $key => $value){
			if(!isset($items[$key])){
				$items[$key] = $value['default'];
			}else{
				if($value['isvar'] == 1){
					//如果是变量 则加入 $符号
					$items[$key] = '$' . $items[$key];
				}
				$items[$key] = $items[$key];
			}
		}
		return $items;
	}
}