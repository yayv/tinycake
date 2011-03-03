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
	var $db;
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
        	$this->$mname->target->init($this->config, isset($this->db)?$this->db:false);
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
		$this->db 	= new mysql($dbsrv);
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
			$this->tpl->template_dir = $templatedir; //THEMES_DIR.$this->_config['site']['theme'];
		if($compile_dir)
			$this->tpl->compile_dir  = $compile_dir; //COMPILE_DIR.'/'.$this->config['site']['theme'];
	}

	function __call($name, $params)
	{
		echo '<pre>';
		debug_print_backtrace();
		echo 'the action:'. $name .' you called is not implemented<br/>';
	}
	
	abstract function index();

}

