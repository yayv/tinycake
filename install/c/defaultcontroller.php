<?php 
class defaultcontroller extends Controller
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
