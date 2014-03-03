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

		#$menu = $this->getModel('mmenu')->getProjectMenu($_GET['name']);

		$proj = $this->getModel('mprojectlist')->getProject($_GET['name']);
		// TODO: 1. 获得项目根目录
		// TODO: 2. 获取 php 文件列表
		// TODO: 3. 获取 js 文件列表
		// TODO: 4. 获取 html 文件列表
		// TODO: 5. 获取 css 文件列表
		// TODO: 6. 获取 SQL 文件列表
		// TODO: 7. 获取 txt 文件列表
		// TODO: 8. TODO要入库吗？
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
		#$this->tpl->assign('menu', $menustr);
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
		
		$body = $this->tpl->fetch('left.projectform.tpl.html');
		
		#$body = print_r($proj,true);
		//$body = 'show project"s details here';

	
		// TODO: 请在下面实现您的action所要实现的逻辑
		$menu = $this->getModel('mmenu')->getProjectMenu($_GET['name']);
		$this->tpl->assign('menuarr', $menu);
		$menustr = $this->tpl->fetch('right.menu.tpl.html');
		
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

        $proj = $this->getModel('mprojectlist')->getProject($_GET['name']);

		$this->getModel('mproject')->setProject($proj);
		$this->tpl->assign('projectinfo', $proj);

		$logs = $this->getModel('mproject')->getLogList();

		foreach($logs as $k=>$v)
		{
			$logs[$k]['url'] = strtr($v['date'], array('-'=>'%2D'));
		}

		$this->tpl->assign('loglist', $logs);

		$body = $this->tpl->fetch('left.loglist.tpl.html');


		// TODO: 请在下面实现您的action所要实现的逻辑
		$menu = $this->getModel('mmenu')->getProjectMenu($_GET['name']);
		$this->tpl->assign('menuarr', $menu);
		$menustr = $this->tpl->fetch('right.menu.tpl.html');
		
		$this->tpl->assign('body', '<p>'.$body.'</p>');
		$this->tpl->assign('menu', $menustr);	

		// TODO: 请在下面实现您的action所要实现的逻辑
		$this->tpl->display('index.tpl.html');	
	}

	public function index()
	{
		// NOTE:如果此 action 不需要用到数据库或者模板引擎，请注释掉相应的代码，以提高速度
		// parent::initDb(Core::getInstance()->getConfig('database'));

		parent::initTemplateEngine(
                Core::getInstance()->getConfig('theme'),
                Core::getInstance()->getConfig('compiled_template')
        );
		
		// TODO: 请在下面实现您的默认action
		$body = file_get_contents('data/todo.txt');
		$body .= file_get_contents('data/history.txt');
		$body = strtr($body,array("\n"=>'<br/>',' '=>' '));
		
		$menu = $this->getModel('mmenu')->getMenu();
		$this->tpl->assign('menuarr', $menu);
		$menustr = $this->tpl->fetch('right.menu.tpl.html');
		
		$this->tpl->assign('body', '<p>'.$body.'</p>');
		$this->tpl->assign('menu', $menustr);	
		$this->tpl->display('index.tpl.html');
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

