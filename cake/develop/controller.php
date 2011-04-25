<?php 
class mo
{
	function __call($name, $params)
	{
		$cname = get_class($this->target);

		if(method_exists($this->target, $name))
		{
			$core = core::getInstance();
			$core->pushLog('start('.$cname.'->'.$name.'): '.microtime()."\n");

			ob_start();
			$ret = call_user_func_array(array(&$this->target, $name), $params);
			$contentlen= ob_get_length();
			$content = ob_get_contents();
			ob_end_clean();

			if($contentlen>0)
			{
				$core->pushLog('warning('.$cname.'->'.$name."): Method should not have any output\n");
				echo $content;
			}
			
			$core->pushLog('end('.$cname.'->'.$name.'): '.microtime()."\n");
			
			return $ret; 
		}
		else
		{
			echo "<pre>METHOD <strong style='color:red;'>$cname->$name</strong> DOES NOT EXISTS<br/>";
			debug_print_backtrace();
		}
	}
}

abstract class Controller
{
    /**
     * $_db 不希望controller直接访问，所以，使用了"_"开头的变量名
     */
	var $_db;

    /**
     * $tpl 需要在controller里访问，所以使用了不带"_"头的变量
     */
	var $tpl;
	
	/**
      * 通用的调取模型类的函数
      */    
	function getModel($mname)
	{
        if(!isset($this->$mname))
        {
        	include_once('m/'.$mname.'.php');       
            $this->$mname = new mo;
        	$this->$mname->target = new $mname;
        	$this->$mname->target->init(
                    Core::getInstance()->getAllConfig(), 
                    isset($this->_db)?$this->_db:false
            );
        }
        
        return $this->$mname;
	}
	
	/**
	 * 初始化数据库对象，考虑增加一层，用于实现用户的基类
	 * 
	 */
	function initDb($dbsrv)
	{
		include_once('cls.mysql.php');

		// override it , if you want own database object 
		//assign the object for db,tpl
		$this->_db 	= new mysql($dbsrv);
	}
	
	/**
	 * 初始化模版引擎，提供用户覆盖此方法的机会
	 * 
	 */
	function initTemplateEngine($templatedir='', $compile_dir='')
	{
		include('smarty/Smarty.class.php');

		$this->tpl 	= new Smarty;
		if($templatedir)
			$this->tpl->template_dir = $templatedir; 
		if($compile_dir)
			$this->tpl->compile_dir  = $compile_dir; 
	}

    // TODO: 这个函数这样写不对。assign是针对smarty模板的，这样写等于要求用户必须使用了smarty
    // TODO: 这个需要再考虑代码结构如何组织，把这个initAssign 转移到用户代码里去，同时还要保持调用的方便
    function initAssign()
    {
        if($this->tpl)
        {
            $this->tpl->assign('home', Core::getInstance()->getConfig('baseurl'));
        }
    }

	function __call($name, $params)
	{
		echo '<pre>';
		debug_print_backtrace();
		echo 'the action:'. $name .' you called is not implemented<br/>';
	}

	public function missing($controller, $action)
	{
		#echo "your controller: $controller is MISSING\n";
		#echo "your $action of controller is MISSING";
		
		if($action=='')
		{
			$file = file_get_contents('../cake/templates/controller.template');
			
			echo "控制器 $controller 类程序不存在, 请复制以下代码，并以 $controller.php 为文件名保存在你的 c 目录下。<br/>";
			echo "<textarea style='width:90%;height:60%;'>";
			echo strtr($file, array('{$name}'=>$controller));
			echo "</textarea>";
		}
		else
		{
			$file = file_get_contents('../cake/templates/ctrl.function.template');
			
			echo "控制器方法 $action 不存在, 请复制以下代码，增加到你的 c/$controller.php 文件中。<br/>";
			echo "<textarea style='width:90%;height:60%'>";
			echo strtr($file, array('{$name}'=>$action));
			echo "</textarea>";
		}
	}
	
	abstract function index();

}

