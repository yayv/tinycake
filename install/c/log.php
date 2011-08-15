<?php
class log extends Controller
{
	public function __construct()
	{
	}
	
	public function index()
	{
	    // NOTE: 如果此 action 不需要用到数据库或者模板引擎，请注释掉相应的代码，以提高速度
	    parent::initDb(Core::getInstance()->getConfig('database'));
	    parent::initTemplateEngine(
                       'v/'.Core::getInstance()->getConfig('theme'),
                       'v/_run/');
	
	    // TODO: 请在下面实现您的action所要实现的逻辑
	    $this->tpl->display('index.tpl.html');	
	}

    function analyse()
    {
	    // NOTE: 如果此 action 不需要用到数据库或者模板引擎，请注释掉相应的代码，以提高速度
	    parent::initDb(Core::getInstance()->getConfig('database'));
	    parent::initTemplateEngine(
                        Core::getInstance()->getConfig('theme'),
                        Core::getInstance()->getConfig('compiled_template'));

        $path = "/Data/tinycake/install/logs/";
        $file = "crumbs.2011-07-06.txt";

        $this->getModel('mlog')->parseFile($path.$file);

	    // TODO: 请在下面实现您的action所要实现的逻辑
	    $this->tpl->display('index.tpl.html');
    }

}

