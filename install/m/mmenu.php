<?php 

class mmenu
{

	public function getMenu()
	{
		$menu = array(
			"新建项目" => array('/install/install', ''),
			"项目列表" => array('/project/listall', ''),
			"日志分析" => array('/log/analyse', ''),
			"日志快放" => array('/log/recall', ''),
		);
		
		return $menu;
	}

	public function getSubMenu($ctrl)
	{
		$submenu = array();
		
		if(isset($submenu[$ctrl]))
			return $submenu[$ctrl];
		else
			return array();
	}
}
