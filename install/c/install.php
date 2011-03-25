<?php
class install extends Controller
{
	public function __construct()
	{
	}
	
	public function index()
	{
		$this->install();
	}
	
    function install()
    {
		parent::initTemplateEngine('default','_run');
		parent::initAssign();

 		$menu = $this->getModel('mmenu')->getMenu();
		$this->tpl->assign('menuarr', $menu);
		$menustr = $this->tpl->fetch('right.menu.tpl.html');
		$this->tpl->assign('menu', $menustr);	
		
		
		$this->tpl->assign('body', $this->tpl->fetch('left.projectcreateform.html'));

        $this->tpl->display('index.tpl.html');        
    }

	function doinstall()
	{
		// NOTE: 如果此 action 不需要用到数据库或者模板引擎，请注释掉相应的代码，以提高速度
		parent::initDb(Core::getInstance()->getConfig('database'));
		parent::initTemplateEngine('default','_run');

        $alldirsisok = false;
		// 1. 显示框架路径，提示输入项目代号(英文)，项目名称(中文)
        $home = realpath('..').'/'.$_GET['name'];
        $url  = realpath('..').$_GET['url'];
        $dirs = array(
            $home,
            $home.'/m/',
            $home.'/v/',
            $home.'/v/default/',
            $home.'/v/default/css/',
            $home.'/v/default/image/',
            $home.'/v/_run/',
            $home.'/c/',
            $home.'/configs/',
            $home.'/logs/',
            $home.'/data/',        
		);

        $retdirs = $this->getModel('mproject')->checkDirectoriesExists($dirs);
        $ret = $this->getModel('mproject')->createDirectories($home, $retdirs);
        if(!$ret)
        {
            $errmsg = $this->getModel('mproject')->popError();
            $this->tpl->assign('body', '目录创建创建失败:'.$errmsg['msg']);
        }
        else
        {
            $chmoddirs = $this->getModel('mproject')->checkDirectoriesMode($dirs);
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
            $ret = $this->getModel('mproject')->createFiles($home, $url);
        }

		// TODO: 3. 在 data 目录下，记录此项目及相关md5信息
        // 每个站一个目录呢？还是每个站一个文件呢？
        // 先用1个文件，放不在再分目录

        $this->tpl->assign('body', $body);            
        $this->tpl->assign('errmsg', $errmsg['msg']);
		$this->tpl->assign('home', '/v/default');
		$this->tpl->display('index.tpl.html');
	}
}
