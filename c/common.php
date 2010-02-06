<?php
/**
 * the basic class
 * 
 */

abstract class common
{
	function initConfig(&$obj)
	{
		global $THEME;
		global $CONFIG;

		$obj->theme = $THEME;
		$obj->config= $CONFIG;
		$obj->home  = $CONFIG['baseurl'];

		$obj->smarty = new Smarty();
		$obj->smarty->compile_dir  = './templates_c';
		$obj->smarty->template_dir = 'v/'.$THEME.'/';

		$this->initSmartyAssign($obj);
	}

	function initSmartyAssign($obj)
	{
		// 通用参数
		$obj->smarty->assign('baseurl', $obj->config['baseurl']);
		$obj->smarty->assign('themepath', '/v/'.$obj->config['theme']);
	}

    abstract protected function main();
};

