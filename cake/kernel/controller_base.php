<?php 
abstract class Controller
{
	var $db;
	var $tpl;
	
	/**
      * 通用的调取模型类的函数
      */    
	function getModule($mname)
	{
		if(!isset($this->$mname))
		{
			include_once('m/'.$mname.'.php');
			$this->$mname = new $mname($this->config, isset($this->db)?$this->db:false);
		}

		return $this->$mname;
	}

	/**
	 * 初始化数据库对象，考虑增加一层，用于实现用户的基类
	 * 
	 */
	function initDb()
	{
		// override it , if you want own database object 
		//assign the object for db,tpl
		$this->db 	= new mysql_db;
		
		//connect to the mysql server
		$this->db->connect($this->config['server']);
	}
	
	/**
	 * 初始化模版引擎，提供用户覆盖此方法的机会
	 * 
	 */
	function initTemplateEngine()
	{
		$this->tpl 	= new Smarty;
		$this->tpl->template_dir = THEMES_DIR.$this->config['site']['theme'];
		$this->tpl->compile_dir  = COMPILE_DIR.'/'.$this->config['site']['theme'];
	}

	function __call($name, $params)
	{
		echo '<pre>';
		debug_print_backtrace();
		echo 'the action:'. $name .' you called is not implemented<br/>';
	}
	
	abstract function main();

}

