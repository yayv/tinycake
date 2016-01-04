<?php
	error_reporting(E_ALL & ~E_NOTICE);

    $DS = PATH_SEPARATOR;
	set_include_path("./${DS}libraries/$DS../cake/develop$DS../cake/libraries");

	include_once('core.php');
	include_once('controller.php');
	include_once('model.php');

	$core = Core::getInstance();

	// TODO： 1. 自动生成配置文件
	$host = &$_SERVER['HTTP_HOST'];
	$core->loadConfig($host);
	$core->pushLog('URL:'.$_SERVER['REQUEST_URI']."\n");
	$core->pushLog('METHOD:'.$_SERVER['REQUEST_METHOD']."\n");
	if('POST'==$_SERVER['REQUEST_METHOD'])
	{
		// TODO: output post body
		$contents = file_get_contents('php://input');
		$core->pushLog('POST_BODY:'.$contents."\n");
	}
	$core->pushLog('start(url):'.microtime()."\n");

    // NOTE: 需要先调用Session, 或许 controller 的初始化需要 session变量
	// TODO: 可以根据配置文件，确认是否需要session
	// TODO： 还是让具体的action自己来决定呢？ 根据配置文件，决定是否需要全局session开启
	// 		对于不需要全局session开启的状态，让action自己去决定就好了，谁用谁知道
	$core->loadSession();

    // TODO： 2. 统一解析URL, 如果 $_GET['act'] 有设置，则外面的rewrite规则还在生效
    // TODO: 3. in debug mode, this program will scan user's controller's directory 
	if(strpos($_SERVER['REQUEST_URI'],$_SERVER['PHP_SELF'])===0)
	{
		// call instant, DO NOT NEED url rebuild	
		$controller = addslashes($_GET['controller']);
		$action = addslashes($_GET['action']);
		$method = addslashes($_GET['method']);
	} 
	else
	{
		list($controller, $action) = $core->rebuildUrl($_SERVER['REQUEST_URI'], strtr($_SERVER["SCRIPT_NAME"], array('/index.php'=>'/')));
		list($controller, $action) = $core->ControllerMap($controller, $action);
	}
	$c = $core->loadController($controller);

	// 构造自定义日志
	// TODO: 点入日志的记录路径应该在配置文件中写明
	// TODO: 还需要考虑 控制器效率日志和模型的效率日志
	if($core->getConfig('clicklog'))
		$core->clickLog($core->getConfig['clicklog'].'/clicklog.'.date('Y-m-d'));
	// 结束写入自定义日志
	
	$core->pushLog('start_controller('.$controller.'->'.$action.'):'.microtime()."\n");

	if(method_exists($c, $action))
	{
		$core->RegisterShutdown("$controller"."->"."$action");
		$c->$action();
		$core->UnregisterShutdown("$controller"."->"."$action");
	}
	else
	{
		$core->RegisterShutdown("$controller"."->index");
		$c->index();
		$core->UnregisterShutdown("$controller"."->index");
	}
	$core->pushLog('end_controller('.$controller.'->'.$action."):".microtime()."\n");
	$core->pushLog('end(url):'.microtime()."\n");
	$core->writelog();

