<?php 
abstract class CommonController extends Controller
{
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
	function initTemplateEngine($templatedir='v/default/', $compile_dir='v/_run')
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

    public function init()
    {
        $this->initDb();
        $this->initTemplateEngine();
        $this->initAssign();
    }
}


