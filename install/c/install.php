<?php
include_once('commoncontroller.php');

class install extends CommonController
{
	public function __construct()
	{
	}
	
	public function index()
	{
		$this->install();
	}
	
	function update()
	{
		// TODO: update project info
		#print_r($_POST);die();
		if($_POST['old_keyname']!=$_POST['keyname'])
			$this->getModel('mprojectlist')->rmProj($_POST['old_keyname']);
		$this->getModel('mprojectlist')->addProj($_POST['showname'],$_POST['keyname'],$_POST['path'],$_POST['url']);
		$this->getModel('mprojectlist')->saveList();
		$this->getModel('mprojectlist')->loadList();
		header('location:/install/listall');
		die();
	}

    function create()
    {
		parent::initTemplateEngine('v/default','v/_run');
		parent::initAssign();


 		$menu = $this->getModel('mmenu')->getMenu();
		$this->tpl->assign('menuarr', $menu);
		$menustr = $this->tpl->fetch('right.menu.tpl.html');
		$this->tpl->assign('menu', $menustr);

		$this->tpl->assign('installsys',$_SERVER["DOCUMENT_ROOT"]);	
		$this->tpl->assign('action','doinstall');
		$this->tpl->assign('buttonname','创建');	
		$this->tpl->assign('body', $this->tpl->fetch('left.projectform.tpl.html'));

        $this->tpl->display('index.tpl.html');        
    }

	function doinstall()
	{
		// NOTE: 如果此 action 不需要用到数据库或者模板引擎，请注释掉相应的代码，以提高速度
		//parent::initDb(Core::getInstance()->getConfig('database'));
		parent::initTemplateEngine('v/default','v/_run');

        $alldirsisok = false;
		// 1. 显示框架路径，提示输入项目代号(英文)，项目名称(中文)
        $home = $_POST['path'];
        $url  = $_POST['url'];

        $retdirs = $this->getModel('mproject')->checkDirectoriesExists($home);

        $ret = $this->getModel('mproject')->createDirectories($home, $retdirs);
        if(!$ret)
        {
            $errmsg = $this->getModel('mproject')->popError();
            $this->tpl->assign('body', '目录创建创建失败:'.$errmsg['msg']);
        }
        else
        {
        	if(count($retdirs)>0)
            	$chmoddirs = $this->getModel('mproject')->checkDirectoriesMode($retdirs);

            if(count($chmoddirs)==0)
            {
                $this->tpl->assign('body', '目录创建成功<br/>');
                $alldirsisok = true;
            }
            else
            {
                $body = "请设置以下目录权限为可写";
                $body .= implode('<br/>', $chmoddirs);
            }
        }

        if($alldirsisok)
        {
    		// TODO: 2. 创建默认文件，用模板生成 .htaccess 和 index.php
            $files = $this->getModel('mproject')->checkFilesMode($home);
            $ret = $this->getModel('mproject')->createFiles($home, $files);

            // TODO: 记录项目信息
            $this->getModel('mprojectlist')->addProj($_POST['showname'],$_POST['keyname'],$_POST['path'],$_POST['url']);
        }

		// TODO: 3. 在 data 目录下，记录此项目及相关md5信息
        // 每个站一个目录呢？还是每个站一个文件呢？
        // 先用1个文件，放不在再分目录

        $this->tpl->assign('body', $body);            
        $this->tpl->assign('errmsg', $errmsg['msg']);
		$this->tpl->assign('home', '/v/default');
		$this->tpl->display('index.tpl.html');
	}

	function importProject()
	{
		parent::initTemplateEngine(
						Core::getInstance()->getConfig('theme'),
						Core::getInstance()->getConfig('compiled_template')
		);
		parent::initAssign();

		$this->tpl->assign('installsys',$_SERVER["DOCUMENT_ROOT"]);	
		$this->tpl->assign('action','doimport');
		$this->tpl->assign('buttonname','导入');	
		$body = $this->tpl->fetch('left.projectform.tpl.html');

        $this->tpl->assign('body', $body);

		$menu = $this->getModel('mmenu')->getMenu();
		$this->tpl->assign('menuarr', $menu);
		$menustr = $this->tpl->fetch('right.menu.tpl.html');
        $this->tpl->assign('menu', $menustr);

	    // TODO: 请在下面实现您的action所要实现的逻辑
	    $this->tpl->display('index.tpl.html');	
	}

	function doimport()
	{
		$keyname = $_POST['keyname'];
		$name = $_POST['showname'];
		$path = $_POST['path'];
		$url  = $_POST['url'];
		$this->getModel('mprojectlist')->addProj($name, $keyname, $path, $url);
		header("location:$home/install/listall");
	}

    function listall()
    {
	    // NOTE: 如果此 action 不需要用到数据库或者模板引擎，请注释掉相应的代码，以提高速度
	    //parent::initDb(Core::getInstance()->getConfig('database'));
	    parent::initTemplateEngine(
                        Core::getInstance()->getConfig('theme'),
                        Core::getInstance()->getConfig('compiled_template'));
		parent::initAssign();

        // 列出全部管理中的项目
        $list = $this->getModel('mprojectlist')->getList();
        $this->tpl->assign('projectlist', $list);
        $body = $this->tpl->fetch('left.projectlist.html');

        $this->tpl->assign('body', $body);

		$menu = $this->getModel('mmenu')->getMenu();
		$this->tpl->assign('menuarr', $menu);
		$menustr = $this->tpl->fetch('right.menu.tpl.html');
        $this->tpl->assign('menu', $menustr);

	    // TODO: 请在下面实现您的action所要实现的逻辑
	    $this->tpl->display('index.tpl.html');	
    }

    function removeproject()
    {
		// NOTE: 如果此 action 不需要用到数据库或者模板引擎，请注释掉相应的代码，以提高速度
		//parent::initDb(Core::getInstance()->getConfig('database'));
		parent::initTemplateEngine(
			Core::getInstance()->getConfig('theme'),
			Core::getInstance()->getConfig('compiled_template')
		);

		// TODO: 
		$this->getModel('mprojectlist')->rmProj($_GET['name']);

		// TODO: 请在下面实现您的action所要实现的逻辑
	    header('location:/install/listall');
	    die();
    }


}

