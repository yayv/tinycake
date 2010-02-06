<?php
/**
 * the syscheck tool
 * 
 *
 * TODO: check directory for write
 * TODO: create .htaccess file
 * TODO:  
 */
include('c/common.php');
class check extends common
{	
	public $smarty;
	public $theme;
	public $config;

	function __construct()
	{
		// init smarty , THEME, config etc.
		parent::initConfig($this);

		// init module
		include('m/mcheck.php');

		$this->mcheck = new mcheck();
	}

	function view()
	{
		$this->smarty->display('check.html');
	}

    function main()
    {
		// TODO: load menu module
		// TODO: load index view
		// TODO: show all
		$checklist = $this->mcheck->doCheck();
		$this->smarty->assign('checklist',$checklist);

		$this->view();
	}
	function __construct()
	{
		// TODO: 检查目录可写
		$this->writedir = array(
			'/templates_c',
			'/cache',
			'/upload',
		);

		$this->checklist = array();
	}

	// 检查目录
	function checkdir()
	{
		GLOBAL $CONFIG;

		foreach($this->writedir as $k=>$v)
		{
			$dirpath = ROOT_DIR.$v; 
			if(is_dir($dirpath))
			{
				if(is_writable($dirpath))
					$this->setMsg('目录'.$v, '检查通过');
				else
				{
					if(chmod($dirpath, '0777'))
						$this->setMsg('目录'.$v, '修改为可写');
					else
						$this->setMsg('目录'.$v, '不可写');
				}
			}
			else
			{
				if(mkdir($dirpath,'0777'))
					$this->setMsg('目录'.$v, '创建成功');
				else
					$this->setMsg('目录'.$v, '创建失败', 'bad');
			}
		}
	}

	// 更新状态
	function setMsg($key, $msg, $cls='ok')
	{
		$this->checklist[$key] = array('class'=>$cls, 'msg'=>$msg);
	}

	function doCheck()
	{
		$this->checkdir();
		$this->setMsg('<hr>','<hr>','');
		$this->setMsg('test','test','ok');

		return $this->checklist;
	}
};

