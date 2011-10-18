<?php 

class mmenu extends model
{

	public function getMenu()
	{
		$menu = array(
			"新建项目" => array('/install/install', ''),
			"导入项目" => array('/install/importproject', ''),
			"项目列表" => array('/install/listall', ''),
			"代码升级" => array('/install/upgrade', ''),
		);
		
		return $menu;
	}

	public function getSubMenu($ctrl)
	{
		$submenu = array();

        $submenu['project'] = array(
            '生成配置文件' => array('/project/genconfig'),
            '生成配置文件' => array('/project/genconfig'),
            '生成配置文件' => array('/project/genconfig'),
            '生成配置文件' => array('/project/genconfig'),
			"日志快放" => array('/log/recall', ''),
			"项目配置文件生成" => array('/project/listall', ''),
			"日志分析" => array('/log/analyse', ''),
        );
		
		if(isset($submenu[$ctrl]))
			return $submenu[$ctrl];
		else
			return array();
	}
}
