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
		
		$body = file_get_contents('data/todo.txt');
		$body = strtr($body,array("\n"=>'<br/>',' '=>'&nbsp;', "\t"=>'&nbsp;&nbsp;&nbsp;&nbsp;'));
		
		$menu = $this->getModel('mmenu')->getMenu();
		$this->tpl->assign('menuarr', $menu);
		$menustr = $this->tpl->fetch('right.menu.tpl.html');
		
		
		$this->tpl->assign('body', '<p>'.$body.'</p>');
		$this->tpl->assign('menu', $menustr);	
		$this->tpl->display('index.tpl.html');
	}
}
