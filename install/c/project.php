<?php
class project extends Controller
{
	public function __construct()
	{
	}
	
	public function index()
	{
		// NOTE:如果此 action 不需要用到数据库或者模板引擎，请注释掉相应的代码，以提高速度
		parent::initDb(Core::getInstance()->getConfig('database'));

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

    function listall()
    {
	    // NOTE: 如果此 action 不需要用到数据库或者模板引擎，请注释掉相应的代码，以提高速度
	    parent::initDb(Core::getInstance()->getConfig('database'));
	    parent::initTemplateEngine(
                        Core::getInstance()->getConfig('theme'),
                        Core::getInstance()->getConfig('compiled_template'));
	
        // 列出全部管理中的项目
        $list = $this->getModel('mprojectlist')->getList();

        $body = $this->tpl->fetch('left.projectlist.html');

        $this->tpl->assign('body', $body);

		$menu = $this->getModel('mmenu')->getMenu();
		$this->tpl->assign('menuarr', $menu);
		$menustr = $this->tpl->fetch('right.menu.tpl.html');
        $this->tpl->assign('menu', $menustr);

	    // TODO: 请在下面实现您的action所要实现的逻辑
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

