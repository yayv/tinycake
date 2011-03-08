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
		// NOTE: 如果此 action 不需要用到数据库或者模板引擎，请注释掉相应的代码，以提高速度
		parent::initDb(Core::getInstance()->getConfig('database'));
		parent::initTemplateEngine('./v/def/','./v/_run/');
		
		// TODO: 1. 显示框架路径，提示输入项目代号(英文)，项目名称(中文)
		// TODO: 2. 提示输入目标路径
		// TODO: 3. 确认目标路径是否可写
		// TODO: 4. 创建目录树，创建默认文件，用模板生成 .htaccess 和 index.php
		// TODO: 5. 在 data 目录下，记录此项目及相关md5信息
		$this->tpl->assign('home', '/v/def');
		$this->tpl->display('index.tpl.html');
	}
	
}
