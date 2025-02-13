<?php 

class mmenu extends model
{

	public function getMenu()
	{
		$menu = array(
			"新建项目" => array('/install/create', ''),
			"导入项目" => array('/install/importproject', ''),
			"项目列表" => array('/install/listall', ''),
			"代码升级" => array('/install/upgrade', ''),
		);
		
		return $menu;
	}

	public function getProjectMenu($prj)
	{
		$menu = array();

        $menu = array(
            '项目目录检查' => array("/project/checkpermission/name-$prj"),
            '日志管理' => array("/project/logmanage/name-$prj"),
            '代码分析' => array("/project/codeanalystics/name-$prj"),
            '无效代码列表' => array("/project/codeanalyze/name-$prj"),
            'TODO列表' => array("/project/todo/name-$prj"),
            '' => array(),
            '项目列表' => array("/install/listall"),
            '返回首页' => array("/"),
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
