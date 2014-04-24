<?php
include_once('commoncontroller.php');

class project extends CommonController
{
	public function __construct()
	{
	}

	function todo()
	{
		parent::initTemplateEngine(
				Core::getInstance()->getConfig('theme'),
				Core::getInstance()->getConfig('compiled_template')
			);

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
		$body = $this->tpl->fetch('left.projecttodo.tpl.html');
		$this->tpl->assign('body',$body);
		
		$this->tpl->display('index.tpl.html');
	}

	function info()
	{
		// NOTE: 如果此 action 不需要用到数据库或者模板引擎，请注释掉相应的代码，以提高速度
		parent::initTemplateEngine(
                Core::getInstance()->getConfig('theme'),
                Core::getInstance()->getConfig('compiled_template')
		);

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
        			array('href'=>'/project/info/name-'.$name,'title'=>"【".$proj['showname']."】"),
        			array('href'=>'/project/checkdir/name-'.$name, 'title'=>'项目目录检查'),
        			array('href'=>'/project/logmanage/name-'.$name, 'title'=>'日志管理'),
        			array('href'=>'/project/codeanalyse/name-'.$name, 'title'=>'代码分析'),
        			array('href'=>'/project/config/name-'.$name, 'title'=>'配置管理'),
					array('href'=>'/project/todo/name-'.$name, 'title'=>'重新扫描')
        	));
        $nav  = $this->tpl->fetch('navigatebar.tpl.html');
        $this->tpl->assign('navigatebar',$nav);

		$body = $this->tpl->fetch('left.projectform.tpl.html');
        $this->tpl->assign('body', $body);
		
		$this->tpl->assign('body', '<p>'.$body.'</p>');
		$this->tpl->assign('menu', $menustr);	
		$this->tpl->display('index.tpl.html');	
	}

	function logmanage()
	{
		// NOTE: 如果此 action 不需要用到数据库或者模板引擎，请注释掉相应的代码，以提高速度
		parent::initTemplateEngine(
                Core::getInstance()->getConfig('theme'),
                Core::getInstance()->getConfig('compiled_template')
		);

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
#echo '<pre>';print_r($logs);die();
		$body = $this->tpl->fetch('left.loglist.tpl.html');

        // 定制导航菜单
        $this->tpl->assign('currentItems',
        		array(
        			array('href'=>'###','title'=>'|'),
        			array('href'=>'/project/info/name-'.$name,'title'=>"【".$proj['showname']."】"),
        			array('href'=>'/project/checkdir/name-'.$name, 'title'=>'项目目录检查'),
        			array('href'=>'/project/logmanage/name-'.$name, 'title'=>'日志管理'),
        			array('href'=>'/project/codeanalyse/name-'.$name, 'title'=>'代码分析'),
        			array('href'=>'/project/config/name-'.$name, 'title'=>'配置管理'),
					array('href'=>'/project/todo/name-'.$name, 'title'=>'重新扫描')
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

    function parseLog()
    {
        $project = "/Data/webapps/map.lvren.cn/public/logs/";
        $file    = "crumbs.2011-05-06.txt";

        $f       = fopen($project.$file, 'r');

        while(!feof($f))
        {
            $line = fgets($f);
            #URL:
            if(0===strpos($line, 'URL:'))
            {
                $url = substr($line, 4);
            }
            else if(0===strpos($line, 'start(url):'))
            {
                $time = substr($line, 11);
                list($ms,$s) = explode(' ', $time);
                $ms = doubleval($ms);
                $s  = intval($s);
            }
            else if(0===strpos($line, 'end(url):'))
            {
                $etime = substr($line, 9);
                list($ems,$es) = explode(' ', $etime);
                $ems = doubleval($ems);
                $es  = intval($es);

                $t = $es-$s+$ems-$ms;
                if($t>0.5)
                    echo $t,"|<font color=red>",$url,"</font><br/>";
                else
                    echo $t,"|",$url,"<br/>";
            }
        }

        fclose($f);
    }
}

