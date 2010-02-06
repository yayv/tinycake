<?php
/**
 * the basic class
 * 
 */
include('c/common.php');

class index extends common
{
	public $smarty;
	public $theme;
	public $config;

	public $mmenu;

	function __construct()
	{
		parent::initConfig($this);

		// load menu module
		require_once('m/mmenu.php');
		$this->mmenu = new mmenu($this->config['baseurl']);


	}

    function main()
    {
		// get Menu View
		parent::initSmartyAssign($this);
		$this->smarty->assign('menulist', $this->mmenu->getMenu());
		$menulist = $this->smarty->fetch('menu.html');

		// get Index main view
		parent::initSmartyAssign($this);
		$this->smarty->assign('msg', 
			'<h1 style="margin:0;height:300px;"><br/>欢迎来到绿人专题管理系统</h1>');
		$right = $this->smarty->fetch('right.prjmanage_list.html');

		// show all
		parent::initSmartyAssign($this);
		$this->smarty->assign('menulist',$menulist);
		$this->smarty->assign('rightpad',$right);
		$this->smarty->display('main.html');
    }
};

