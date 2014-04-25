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

    public function analyse()
    {
		$name = $_GET['name'];
		$logdate	 = urldecode($_GET['date']);

        $proj = $this->getModel('mprojectlist')->getProject($name);

        set_time_limit(0);
	    // NOTE: 如果此 action 不需要用到数据库或者模板引擎，请注释掉相应的代码，以提高速度
	    // parent::initDb(Core::getInstance()->getConfig('database'));
	    parent::initTemplateEngine(
                        Core::getInstance()->getConfig('theme'),
                        Core::getInstance()->getConfig('compiled_template'));

        $path = $proj["path"].'/logs';
        $logfile = "/crumbs.".$logdate.".txt";
		$resultfile = '/pickedcrumbs.'.$logdate.'.php';
        if(is_file($path.$resultfile))
        {
            $this->getModel('mlog')->loadFromFile($path.$resultfile);
        }
        else
        {
        	$this->parseText();
        }

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
        			array('href'=>'/project/info/name-'.$name,'title'=>"【".$proj['showname']."】"),
        			array('href'=>'/project/checkdir/name-'.$name, 'title'=>'项目目录检查'),
        			array('href'=>'/project/logmanage/name-'.$name, 'title'=>'日志管理'),
        			array('href'=>'/project/codeanalyse/name-'.$name, 'title'=>'代码分析'),
        			array('href'=>'/project/config/name-'.$name, 'title'=>'配置管理'),
					array('href'=>'/project/todo/name-'.$name, 'title'=>'重新扫描')
        	));
        $nav  = $this->tpl->fetch('navigatebar.tpl.html');
        $this->tpl->assign('navigatebar',$nav);

	    $this->tpl->display('index.tpl.html');
    }

    public function parseText()
    {
		$name = $_GET['name'];
		$logdate	 = urldecode($_GET['date']);

		$proj = $this->getModel('mprojectlist')->getProject($name);

        $path = $proj["path"].'/logs';
        $logfile = "/crumbs.".$logdate.".txt";
		$resultfile = '/pickedcrumbs.'.$logdate.'.php';

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

        if('parseText'==$_GET['action'])
        {
        	$pos = strpos( $_SERVER["HTTP_ACCEPT"], 'application/json');

        	if($pos===false)
        	{
        		header('X-Debug:false');
	        	// call this function from url
	        	header('location:/project/logmanage/name-'.$name);        		
        	}
        	else
        	{
        		header('X-Debug:true');
        		header('Content-Type:application/json');
        		echo json_encode(array('ret'=>0,'msg'=>'parse done'));
        	}
        	return ;
        }
        else
        {
        	// call this function from another function
        	return ;
        }
    }

    public function mergePhp()
    {
		// WARNING: 这个功能是一个大坑，如果有人按照规则写了php文件进来，则可能引发整个系统的漏洞
		$name = $_GET['name'];
		$logdate	 = urldecode($_GET['date']);

		$proj = $this->getModel('mprojectlist')->getProject($name);
        $path = $proj["path"].'/logs';

        $tmpphp = $path.'/pickedcrumbs.tmp.php';
        $tmptxt = $path.'/crumbs.tmp.txt';

        if(is_file($tmpphp) || is_file($tmptxt))
        {
        	header("Content-Type:application/json");

        	echo json_encode(array('ret'=>0, 'msg'=>'请等待'));
        	return ;
        }

		$dates = explode(',', $logdate);

		foreach($dates as $k=>$v)
		{
			if('0'==$v)
			{
				unset($dates[$k]);
				continue;
			}

			$thatday = date('Y-m-d',strtotime($v));
			$dates[$k] = $thatday;

	        $logfile = "/crumbs.".$thatday.".txt";
			$resultfile = '/pickedcrumbs.'.$thatday.'.php';
		}

		rsort($dates);

		$targetdate = $dates[0];

		// TODO: 根据 $targetdate 来判断模式
		$mphp = is_file($path."/pickedcrumbs.".$targetdate.".php");
		$mtxt = is_file($path."/crumbs.".$targetdate.".txt");

		if($mtxt)
		{
			$ftxt = fopen($tmptxt,'a');
		}

		if($mphp)
		{
			$ophp = $this->getModel('mlog');
		}

		foreach($dates as $k=>$v)
		{
			// merge to $tmptxt , $tmpphp 
			$iphp  = $path."/pickedcrumbs.".$v.".php";
			$itxt  = $path."/crumbs.".$v.".txt";

			$maphp = is_file($iphp);
			$matxt = is_file($itxt);
			$buffer = "";
			#print_r(array($maphp,$mphp,$matxt,$mtxt));
			if($maphp==$mphp && $matxt==$mtxt)
			{
				if($mtxt)
				{
					$fotxt = fopen($itxt,'r');
				    while (($buffer = fgets($fotxt, 409600)) !== false) 
				    {
				        fputs($ftxt, $buffer);
				    }
				    fclose($fotxt);
				    unlink($itxt);
				}
			    
			    if($mphp)
			    {
			    	$this->getModel('mlog')->mergeAnotherFile($iphp);
			    	unlink($iphp);
			    }
			}
			else
			{

				continue;
			}
		}

		if($mtxt)
		{
			fclose($ftxt);
			rename($tmptxt, $path."/crumbs.".$targetdate.".txt");
		}

		if($mphp)
		{
			$this->getModel('mlog')->dumpToFile($tmpphp);
			rename($tmpphp, $path."/pickedcrumbs.".$targetdate.".php");
		}

		header('Content-Type:application/json');
		echo json_encode(array('ret'=>1,'msg'=>'ok'));
    }

    public function removeText()
    {
		$name = $_GET['name'];
		$logdate	 = urldecode($_GET['date']);

		$proj = $this->getModel('mprojectlist')->getProject($name);

        $path = $proj["path"].'/logs';
        $logfile = "/crumbs.".$logdate.".txt";
		$resultfile = '/pickedcrumbs.'.$logdate.'.php';

        $this->getModel('mlog')->removeFile($path.$logfile);

        header("location:/project/logmanage/name-".$name);
        return ;
    }

    public function removePhp()
    {
		$name = $_GET['name'];
		$logdate	 = urldecode($_GET['date']);

		$proj = $this->getModel('mprojectlist')->getProject($name);

        $path = $proj["path"].'/logs';
        $logfile = "/crumbs.".$logdate.".txt";
		$resultfile = '/pickedcrumbs.'.$logdate.'.php';

        $this->getModel('mlog')->removeFile($path.$resultfile);

        header("location:/project/logmanage/name-".$name);
        return ;
    }
}

