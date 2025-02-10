<?php 
include_once('commoncontroller.php');

class defaultcontroller extends CommonController
{
	public function initParams()
	{
	}

	public function index()
	{
		parent::initTemplateEngine('v/default/','v/_run/');
		parent::initAssign();
		
		$body = file_get_contents('../doc/todo.md');
		$body = strtr($body,array("\n"=>'<br/>',' '=>'&nbsp;', "\t"=>'&nbsp;&nbsp;&nbsp;&nbsp;'));
		

		$navigatebar = $this->tpl->fetch('navigatebar.tpl.html');		
		
		$this->tpl->assign('body', '<p>'.$body.'</p>');
		$this->tpl->assign('navigatebar', $navigatebar);	
		$this->tpl->display('index.tpl.html');
	}

	public function example()
	{
		// NOTE: 如果此 action 不需要用到数据库或者模板引擎，请注释掉相应的代码，以提高速度
		parent::initDb(Core::getInstance()->getConfig('database'));
		parent::initTemplateEngine(
                Core::getInstance()->getConfig('theme'),
                Core::getInstance()->getConfig('compiled_template')
		);

		// TODO: 请在下面实现您的action所要实现的逻辑
		$this->tpl->display('example.html');	
	}
	
}

