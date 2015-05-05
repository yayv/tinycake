<?php
	error_reporting(E_ALL & ~E_NOTICE);
    ini_set('display_errors','on');

    $DS = PATH_SEPARATOR;
	set_include_path("./${DS}libraries/$DS../cake/develop$DS../cake/libraries");

	include_once('core.php');
	include_once('controller.php');
	include_once('model.php');

    header('Content-Type:text/html;charset=utf-8');
	$core = Core::getInstance();
	
	// TODO： 1. 统一配置文件
	$host = &$_SERVER['HTTP_HOST'];
	$core->loadConfig($host);
	$core->pushLog('URL:'.$_SERVER['REQUEST_URI']."\n");
	$core->pushLog('start(url):'.microtime()."\n");
	
    // TODO： 2. 统一解析URL, 如果 $_GET['act'] 有设置，则外面的rewrite规则还在生效
    // TODO: 3. in debug mode, this program will scan user's controller's directory 

    list($controller, $action) = $core->rebuildUrl($_SERVER['REQUEST_URI']);
    list($controller, $action) = $core->ControllerMap($controller, $action);

	$c = $core->loadController($controller);
	
	$core->loadSession();

	// 构造自定义日志
	if(isset($core->getConfig['clicklog']))
		$core->clickLog($core->getConfig['clicklog'].'/clicklog.'.date('Y-m-d'));		
	
	$core->pushLog('start_controller('.$controller.'->'.$action.'):'.microtime()."\n");

    /* maybe, here is for release template
    */

	if(is_a($c,$controller))
    {
	    if(method_exists($c, $action))
	    {
		    $core->RegisterShutdown("$controller"."->"."$action");
		    $c->$action();
		    $core->UnregisterShutdown("$controller"."->"."$action");
	    }
	    else
        {
       		$core->RegisterShutdown("defaultcontroller->missing");
		    $core->loadController('defaultcontroller')->missing($controller, $action); // action is missing
	        $core->UnregisterShutdown("defaultcontroller->missing");
	    }
    }
    else
    {
		$core->RegisterShutdown("$controller"."->missing");
		$c->missing($controller, ''); // controller is missing
	    $core->UnregisterShutdown("$controller"."->missing");
	}

	$core->pushLog('end_controller('.$controller.'->'.$action."):".microtime()."\n");
	$core->pushLog('end(url):'.microtime()."\n");
	$core->writelog();

