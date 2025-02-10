<?php
include_once('commoncontroller.php');

class project extends CommonController
{
	public function __construct()
	{
	}

	function todo()
	{
		parent::init();

		$name = $_GET['name'];
		$proj = $this->getModel('mprojectlist')->getProject($name);
		// TODO: 获得项目根目录
		// TODO: 获取 php 文件列表
		// TODO: 获取 js 文件列表
		// TODO: 获取 html 文件列表
		// TODO: 获取 css 文件列表
		// TODO: 获取 SQL 文件列表
		// TODO: 获取 txt 文件列表
		// TODO: TODO要入库吗？要入库，而且要记录修改，要记录时间
		// TODO: 这里还要保留这个展示页面吗？还是直接跳回项目首页？
		$this->getModel('mprojectinfo')->initProj(
						$proj['path'],
						$proj['url'],
						$proj['keyname'],
						$proj['showname']
				);

		$todoofproject = $this->getModel('mprojectinfo')->findTodoList();

		$this->tpl->assign('projectinfo',$proj);
		$this->tpl->assign('todo',$todoofproject);
		$body = $this->tpl->fetch('body.projecttodo.tpl.html');
		$this->tpl->assign('body',$body);
		
		$this->tpl->display('index.tpl.html');
	}

	function info()
	{
		// NOTE: 如果此 action 不需要用到数据库或者模板引擎，请注释掉相应的代码，以提高速度
		parent::init();

		$name = $_GET['name'];
		$proj = $this->getModel('mprojectlist')->getProject($_GET['name']);
		
		$this->tpl->assign('installsys',$_SERVER['DOCUMENT_ROOT']);
		$this->tpl->assign('action','update');
		$this->tpl->assign('buttonname','修改');
		$this->tpl->assign('keyname',$proj['keyname']);
		$this->tpl->assign('showname',$proj['showname']);
		$this->tpl->assign('url',$proj['url']);
		$this->tpl->assign('path',$proj['path']);
		
		
        // 定制导航菜单
        $this->tpl->assign('currentItems',
        		array(
        			array('href'=>'###','title'=>'|'),
        			array('href'=>'/siteManager/project/info/name-'.$name,'title'=>"【".$proj['showname']."】"),
        			array('href'=>'/siteManager/project/checkdir/name-'.$name, 'title'=>'项目目录检查'),
        			array('href'=>'/siteManager/project/logmanage/name-'.$name, 'title'=>'日志管理'),
        			array('href'=>'/siteManager/project/codeanalyse/name-'.$name, 'title'=>'代码分析'),
        			array('href'=>'/siteManager/project/config/name-'.$name, 'title'=>'配置管理'),
					array('href'=>'/siteManager/project/todo/name-'.$name, 'title'=>'重新扫描')
        	));
        $nav  = $this->tpl->fetch('navigatebar.tpl.html');
        $this->tpl->assign('navigatebar',$nav);

		$body = $this->tpl->fetch('body.projectform.tpl.html');
        $this->tpl->assign('body', $body);
		
		$this->tpl->assign('body', '<p>'.$body.'</p>');
		$this->tpl->assign('menu', $menustr);	
		$this->tpl->display('index.tpl.html');	
	}

	function logmanage()
	{
		// NOTE: 如果此 action 不需要用到数据库或者模板引擎，请注释掉相应的代码，以提高速度
		parent::init();

		$name = $_GET['name'];
        $proj = $this->getModel('mprojectlist')->getProject($name);

		$this->getModel('mproject')->setProject($proj);
		$this->tpl->assign('projectinfo', $proj);

		$logs = $this->getModel('mproject')->getLogList();

		foreach($logs as $k=>$v)
		{
			$logs[$k]['url'] = strtr($v['date'], array('-'=>'%2D'));
		}

		$this->tpl->assign('loglist', $logs);

		$body = $this->tpl->fetch('body.loglist.tpl.html');

        // 定制导航菜单
        $this->tpl->assign('currentItems',
        		array(
        			array('href'=>'###','title'=>'|'),
        			array('href'=>'/siteManager/project/info/name-'.$name,'title'=>"【".$proj['showname']."】"),
        			array('href'=>'/siteManager/project/checkdir/name-'.$name, 'title'=>'项目目录检查'),
        			array('href'=>'/siteManager/project/logmanage/name-'.$name, 'title'=>'日志管理'),
        			array('href'=>'/siteManager/project/codeanalyse/name-'.$name, 'title'=>'代码分析'),
        			array('href'=>'/siteManager/project/config/name-'.$name, 'title'=>'配置管理'),
					array('href'=>'/siteManager/project/todo/name-'.$name, 'title'=>'重新扫描')
        	));
        $nav  = $this->tpl->fetch('navigatebar.tpl.html');
        $this->tpl->assign('navigatebar',$nav);
		
		$this->tpl->assign('body', $body);

		$this->tpl->display('index.tpl.html');	
	}

	public function index()
	{
		// NOTE:如果此 action 不需要用到数据库或者模板引擎，请注释掉相应的代码，以提高速度
		// parent::initDb(Core::getInstance()->getConfig('database'));
		header('Location:/');
		return ;
	}

    public function codeanalyse()
    {
    	parent::init();
    	
    	// TODO: 这里要实现分层列出关键函数名文件名
    	// TODO: 下一步就要考虑如何呈现MVC直接的调用和支持关系
        $nav  = $this->tpl->fetch('navigatebar.tpl.html');
        $this->tpl->assign('navigatebar',$nav);
		
		$this->tpl->assign('body', $body);

		$this->tpl->display('index.tpl.html');	
    	
    	return ;
    }

	function checkdir()
	{
    	parent::init();

    	// TODO: 这里要实现分层列出关键函数名文件名
    	// TODO: 下一步就要考虑如何呈现MVC直接的调用和支持关系
        $nav  = $this->tpl->fetch('navigatebar.tpl.html');
        $this->tpl->assign('navigatebar',$nav);
		$name = $_GET['name'];

		$proj = $this->getModel('mprojectlist')->getProject($name);		
		$this->tpl->assign('projectinfo', $proj);

		$ret = $this->getModel('mproject')->checkDirectoriesExists($proj['path']);

		$this->tpl->assign('missed', $ret);
/*
    function 
    {
        $tocreate = array();

        foreach($this->_dirs as $k=>$v)
        {
            if(!is_dir($home.$v))
            {
                $tocreate[] = $home.$v;
            }
        }

        return $tocreate;
    }

    function checkDirectoriesMode($dirs)
    {
        $tochmod = array();

        foreach($dirs as $k=>$v)
            if(!is_writable($v))
                $tochmod[] = $v;

        return $tochmod;
    }

    function checkFilesMode($home)
    {
        $togenerate = array();

        foreach($this->_files as $k=>$v)
            if(!is_file($home.$k))
            {
                $togenerate[$k] = $v;
            }

        return $togenerate;

    }    
*/
		$this->tpl->assign('rights', range(1,3));
		$body = $this->tpl->fetch('body.directories.tpl.html');
		$this->tpl->assign('body', "<br/><br/><br/>".$body);

		$this->tpl->display('index.tpl.html');	
    	
    	return ;
	}


    public function config()
    {
    	parent::init();
		$name = $_GET['name'];
        $proj = $this->getModel('mprojectlist')->getProject($name);

        $this->tpl->assign('currentItems',
        		array(
        			array('href'=>'###','title'=>'|'),
        			array('href'=>'/siteManager/project/info/name-'.$name,'title'=>"【".$proj['showname']."】"),
        			array('href'=>'/siteManager/project/checkdir/name-'.$name, 'title'=>'项目目录检查'),
        			array('href'=>'/siteManager/project/logmanage/name-'.$name, 'title'=>'日志管理'),
        			array('href'=>'/siteManager/project/codeanalyse/name-'.$name, 'title'=>'代码分析'),
        			array('href'=>'/siteManager/project/config/name-'.$name, 'title'=>'配置管理'),
					array('href'=>'/siteManager/project/todo/name-'.$name, 'title'=>'重新扫描')
        	));

        $nav  = $this->tpl->fetch('navigatebar.tpl.html');
        $this->tpl->assign('navigatebar',$nav);

		$this->tpl->assign('projectinfo', $proj);

		$files = $this->getModel('mconfig')->getAllConfigFiles($proj['path']);
		
		$this->tpl->assign("domains",$files['domains']);
		$this->tpl->assign("others",$files['others']);
    	$body = $this->tpl->fetch("body.config.tpl.html");

    	$this->tpl->assign('body', $body);
    	$this->tpl->display('index.tpl.html');

    	return ;
    }

    public function showconfig()
    {
    	parent::init();
		$name = $_GET['name'];
        $proj = $this->getModel('mprojectlist')->getProject($name);

        $this->tpl->assign('currentItems',
        		array(
        			array('href'=>'###','title'=>'|'),
        			array('href'=>'/siteManager/project/info/name-'.$name,'title'=>"【".$proj['showname']."】"),
        			array('href'=>'/siteManager/project/checkdir/name-'.$name, 'title'=>'项目目录检查'),
        			array('href'=>'/siteManager/project/logmanage/name-'.$name, 'title'=>'日志管理'),
        			array('href'=>'/siteManager/project/codeanalyse/name-'.$name, 'title'=>'代码分析'),
        			array('href'=>'/siteManager/project/config/name-'.$name, 'title'=>'配置管理'),
					array('href'=>'/siteManager/project/todo/name-'.$name, 'title'=>'重新扫描')
        	));

        $nav  = $this->tpl->fetch('navigatebar.tpl.html');
        $this->tpl->assign('navigatebar',$nav);

		$this->tpl->assign('projectinfo', $proj);

		$files = $this->getModel('mconfig')->getAllConfigFiles($proj['path']);
		
		$this->tpl->assign("domains",$files['domains']);
		$this->tpl->assign("others",$files['others']);
    	$body = $this->tpl->fetch("body.config.tpl.html");

    	$this->tpl->assign('body', $body);
    	$this->tpl->display('index.tpl.html');

    	return ;
    }
}

