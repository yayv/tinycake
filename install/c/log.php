<?php
include_once('commoncontroller.php');

class log extends CommonController
{
	public function __construct()
	{
	}
	
	public function index()
	{
	    // NOTE: 如果此 action 不需要用到数据库或者模板引擎，请注释掉相应的代码，以提高速度
	    // parent::initDb(Core::getInstance()->getConfig('database'));
	    parent::initTemplateEngine(
                       'v/'.Core::getInstance()->getConfig('theme'),
                       'v/_run/');
	
	    // TODO: 请在下面实现您的action所要实现的逻辑
	    $this->tpl->display('index.tpl.html');	
	}

    function analyse()
    {
		$projectname = $_GET['name'];
		$logdate	 = urldecode($_GET['date']);

        $proj = $this->getModel('mprojectlist')->getProject($projectname);

        set_time_limit(0);
	    // NOTE: 如果此 action 不需要用到数据库或者模板引擎，请注释掉相应的代码，以提高速度
	    // parent::initDb(Core::getInstance()->getConfig('database'));
	    parent::initTemplateEngine(
                        Core::getInstance()->getConfig('theme'),
                        Core::getInstance()->getConfig('compiled_template'));

        $path = $proj["path"].'/logs';
        $logfile = "/crumbs.".$logdate.".txt";
		$resultfile = '/parse.'.$logdate.'.php';
        if(is_file($path.$resultfile))
        {
            $this->getModel('mlog')->loadFromFile($path.$resultfile);
        }
        else
        {
        	// TODO: 这里的合并规则，可以写到项目的某个目录下，作为配置来使用
            $this->getModel('mlog')->parseFile($path.$logfile, 
							array(
									'/\/lvyou\/.*/',
									'/\/tupian\/.*/',
									'/\/jiudian\/.*/',
									'/\/ditu\/.*/',
									'/\/gonglue\/.*/',
									'/\/quguo\/.*/',
									'/\/plan\/.*/',
									'/\/youji\/.*/',
									'/\/jingdian\/.*/',
									'/\/jiaotong\/.*/',
									'/\/jianjie\/.*/',
									'/\/dianping\/.*/',
									'/\/menpiao\/.*/',
									'/\/zhusu\/.*/',
									'/\/tianqi\/.*/',
									'/\/fengjing\/.*/',
									'/d.top.js.*/',
									'/d.footer.js.*/',
									'/index.php?.*/',

									'/\/api\/scenic_lite\/id=.*/',
									'/\/api\/scenic\/id=.*/',
									'/\/api\/scenic_simple\/id=.*/',
							)
			);
            $this->getModel('mlog')->calcAvgTime();
            $this->getModel('mlog')->dumpToFile($path.$resultfile);
        }

        #$this->getModel('mlog')->showDetails();die();

        $this->tpl->assign('badcalls', $this->getModel('mlog')->getBadCalls());
        $this->tpl->assign('url_times', $this->getModel('mlog')->getUrlTimes());
        $this->tpl->assign('controller_times', $this->getModel('mlog')->getControllerTimes());
        $this->tpl->assign('action_times', $this->getModel('mlog')->getActionTimes());
        $this->tpl->assign('model_times', $this->getModel('mlog')->getModelTimes());
        $this->tpl->assign('method_times', $this->getModel('mlog')->getMethodTimes());

        $body = $this->tpl->fetch('left.logparse.tpl.html');


        // 处理界面
        $this->tpl->clear_all_assign();

        $this->tpl->assign('body', $body);

        // 定制导航菜单
        $this->tpl->assign('currentItems',
        		array(
        			array('href'=>'###','title'=>'|'),
        			array('href'=>'/project/info/name-'.$name,'title'=>"<span style='font-weight:bold;'>".$proj['showname']."</span>"),
        			array('href'=>'/project/checkdir/name-'.$name, 'title'=>'项目目录检查'),
        			array('href'=>'/project/logmanage/name-'.$name, 'title'=>'日志管理'),
        			array('href'=>'/project/codeanalyse/name-'.$name, 'title'=>'代码分析'),
					array('href'=>'/project/todo/name-'.$name, 'title'=>'重新扫描')
        	));
        $nav  = $this->tpl->fetch('navigatebar.tpl.html');
        $this->tpl->assign('navigatebar',$nav);


	    $this->tpl->display('index.tpl.html');
    }
}

