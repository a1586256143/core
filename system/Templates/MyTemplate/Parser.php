<?php
/**
 * 模板解析
 * @author Colin <15070091894@163.com>
 */
namespace system\Templates\MyTemplate;
use system\MyError;
class Parser{
	//字段，保存模板内容
	private $_tpl;
	//构造方法用于获取模板内容
	public function __construct($tplFile){
		if(!file_exists($tplFile)){
			E('读取模板文件出错！'.$this->_tpl);
		}
		$this->_tpl = file_get_contents($tplFile);
	}

	/**
     * 解析函数
     * @author Colin <15070091894@163.com>
     */
	private function parFunc(){
		$patten = '/\{:(.*?)\}/';
		if(preg_match($patten,$this->_tpl)){
			$this->_tpl = preg_replace_callback($patten,function($matches){
				//处理函数不存在的时候报错
				preg_match('/([\w\_]+)\(/', $matches[1] , $match);
				if(!function_exists($match[1])){
					throw new \system\MyError($match[1] . '() 这个方法不存在');
				}
				return "<?php echo " . $matches[1] ." ?>";
			},$this->_tpl);
		}
	}
	
	/**
     * 解析普通变量
     * @author Colin <15070091894@163.com>
     */
	private function parVar(){
		$patten = '/\{\$(.*?)\}/';
		if(preg_match($patten,$this->_tpl)){
			$this->_tpl = preg_replace($patten,"<?php echo \$$1; ?>",$this->_tpl);
		}
	}

	/**
     * 解析IF语句
     * @author Colin <15070091894@163.com>
     */
	private function parIF(){
		//if语句开头的正则
		$_ifpatten = '/\{if(.*?)\}/';
		//endif语句的结束
		$_endifpatten = '/\{\/if\}/';
		//else语句查询
		$_elsepatten = '/\{else\}/';
		//elseif语句
		$elseifpatten = '/\{elseif(.*?)}/u';
		//匹配查找
		if(preg_match($_ifpatten,$this->_tpl)){
			//查找是否关闭IF
			if(preg_match($_endifpatten,$this->_tpl)){
				//替换
				$this->_tpl = preg_replace($_ifpatten,"<?php if $1: ?>",$this->_tpl);
				$this->_tpl = preg_replace($_endifpatten,"<?php endif; ?>",$this->_tpl);
				//有else就替换了
				if(preg_match($_elsepatten,$this->_tpl)){
					$this->_tpl = preg_replace($_elsepatten,"<?php else: ?>",$this->_tpl);
				}
				//有elseif就替换了
				if(preg_match($elseifpatten,$this->_tpl)){
					$this->_tpl = preg_replace($elseifpatten,"<?php elseif $1: ?>",$this->_tpl);
				}
			}else{
				E('if语句未关闭！'.$this->_tpl);
			}
		}
	}

	/**
     * 解析foreach语句
     * @author Colin <15070091894@163.com>
     */
	public function parForeach(){
		$patten = '/\{foreach(.*?)\}/';
		$_endpatten = '/\{\/foreach\}/';
		$pattenvar = '/\{\$(.*?)\}/';
		if(preg_match($patten,$this->_tpl)){
			if(preg_match($_endpatten,$this->_tpl)){
				$this->_tpl = preg_replace($patten,"<?php foreach $1: ?>",$this->_tpl);
				$this->_tpl = preg_replace($_endpatten,"<?php endforeach; ?>",$this->_tpl);
				if(preg_match($pattenvar,$this->_tpl)){
					$this->_tpl = preg_replace($pattenvar,"<?php echo \$$1; ?>",$this->_tpl);
				}
			}else {
				E('foreach语句未关闭！'.$this->_tpl);
			}
		}
	}

	/**
     * 解析include语句
     * @author Colin <15070091894@163.com>
     */
	private function parinclude(){
		$patten = '/\{include\s+file=(\"|\')([\w\.\-\/]+)(\"|\')\}/';

		if(preg_match($patten,$this->_tpl,$file)){
			$filename = $file[2];
			$modules = defined('CURRENT_MODULE') ? CURRENT_MODULE : Config('DEFAULT_MODULE');
			$path = APP_PATH . ltrim(Config('TPL_DIR') , '/');
			$filepath = $path.$filename.Config('TPL_TYPE');
			if(!file_exists($filepath) || empty($file)){
				E($filepath.'引入文件出错！请检查！');
			}
			$prefix = Config('TPL_TYPE');
			$this->_tpl = preg_replace($patten,"<?php \$this->display(\"$path$2$prefix\") ?>",$this->_tpl);
		}
	}
	
	/**
     * 解析注释
     * @author Colin <15070091894@163.com>
     */
	private function parCommon(){
		$patten = '/\{#\}(.*)\{#\}/';
		if(preg_match($patten,$this->_tpl)){
			$this->_tpl = preg_replace($patten,"<?php /* $1 */ ?>",$this->_tpl);
		}
	}

	/**
     * 解析__函数
     * @author Colin <15070091894@163.com>
     */
    private function parDefault(){
    	$patten = "/(\_\_[a-zA-z]+\_\_)/";
    	if(preg_match($patten, $this->_tpl)){
    		$this->_tpl = preg_replace($patten , "<?php echo $1; ?>" , $this->_tpl);
    	}
    }

    /**
     * 解析配置信息
     * @author Colin <15070091894@163.com>
     */
    private function parWeb(){
    	$patten = '/\{web(.*?)\}/';
    	if(preg_match($patten, $this->_tpl)){
    		$this->_tpl = preg_replace($patten, "<?php echo Config $1; ?>", $this->_tpl);
    	}
    }

    /**
     * 解析实例化MyClass\libs下的类
     * @author Colin <15070091894@163.com>
     */
    private function parLibs(){
    	$patten = '/\{:libs\(\'(.*?)\' , \'(.*?)\'\)\}/';
    	if(preg_match($patten, $this->_tpl)){
    		$this->_tpl = preg_replace($patten, "<?php \$object_$1 = new \system\\\\$1();echo \$object_$1->$2; ?>", $this->_tpl);
    	}
    }

    /**
     * 解析实例化MyClass\libs下类的静态方法
     * @author Colin <15070091894@163.com>
     */
    public function parLibsStatic(){
    	$patten = '/\{:libsStatic\(\'(.*?)\' , \'(.*?)\'\)\}/';
    	if(preg_match($patten, $this->_tpl , $match)){
    		$this->_tpl = preg_replace($patten, "<?php echo \system\\\\$1::$2; ?>", $this->_tpl);
    	}
    }
	
	/**
     * 对外公开的方法
     * @author Colin <15070091894@163.com>
     */
	public function comile($parFile){
		$this->parDefault();		//解析模板默认常量
		$this->parWeb();			//解析系统变量
		$this->parinclude();
		$this->parLibs();			//解析系统变量
		$this->parLibsStatic();			//解析系统变量
		$this->parFunc();			//解析模板函数
		$this->parIF();				
		$this->parForeach();
		$this->parCommon();
		$this->parVar();			//解析模板变量
		//生成编译文件
		file_put_contents($parFile,$this->_tpl);
	}
}
?>