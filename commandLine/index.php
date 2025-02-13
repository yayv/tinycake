<?php
error_reporting(E_ALL & ~E_NOTICE);

function isConsole()
{
	return isset($_SERVER['argv']);
}

function cmdParse()
{
	$console = [];

	// $console['pwd'] = $_SERVER['PWD'];

	$path = pathinfo(realpath($_SERVER['SCRIPT_FILENAME']));

	chdir($path['dirname']);

	$console['script'] = $_SERVER['argv'][0];

	if(isset($_SERVER['argv'][1]))
		$console['controller'] = $_SERVER['argv'][1];
	else
		$console['controller'] = 'defaultcontroller';

	if(isset($_SERVER['argv'][2]))
		$console['action']  	 = $_SERVER['argv'][2];
	else
		$console['action']  	 = 'index';

	if(isset($_SERVER['argv'][3]))
		$console['method']  	 = $_SERVER['argv'][3];	
	else	
		$console['method']  	 = 'do';

	return $console;
}

if(isConsole())
	$console = cmdParse();
else{
	echo "The App Only Run In Command Line\n";
	return ;
}

set_include_path("./".PATH_SEPARATOR.
		         "./libs".PATH_SEPARATOR.
		         "../cake/kernel".PATH_SEPARATOR.
		         "../cake/libraries"
);

include_once('core.php');
include_once('controller.php');
include_once('model.php');

$core = Core::getInstance();
$core->setConsole( $console );

// TODO： 1. 自动生成配置文件
$host = 'win';
$core->loadConfig($host);
$core->pushLog('URL:'.$console['controller'].'/'.$console['action'].'/'.$console['method']."\n");
$core->pushLog("METHOD:console\n");


list($controller, $action) = $core->mapController($console['controller'], $console['action']);

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


