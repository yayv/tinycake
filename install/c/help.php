<?php
include_once('commoncontroller.php');
include_once('cls.resizeimage.php');

class help extends CommonController
{
	public function __construct()
	{
	}
	
	function index()
	{
		// NOTE:如果此 action 不需要用到数据库或者模板引擎，请注释掉相应的代码，以提高速度
		#parent::initDb(Core::getInstance()->getConfig('database'));
		parent::initTemplateEngine('./v/default/','./v/_run/');
		
		// Do something for test    
		#print_r($this->getModel('mproject')->_config);
		#$this->tpl->assign('');

		// TODO: 这里要搞一个Markdown解释器，好做帮助手册的编写，目录的解析生成和展示
		$navigatebar = $this->tpl->fetch('navigatebar.tpl.html');
		$this->tpl->assign('navigatebar', $navigatebar);	
		$body = "<pre>Hello, World!<br/></pre>";
		$this->tpl->assign('body',$body);
		$this->tpl->display('index.tpl.html');
	}
	
}

