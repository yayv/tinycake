<?php
include_once('config.php');

$path = pathinfo($_SERVER['SCRIPT_FILENAME']);
define('HOST', $_SERVER['HTTP_HOST']);
define('ROOT_DIR',$path['dirname']);
define('CTL_DIR', ROOT_DIR.'/c/');


$CONFIG = $config[HOST];
$HOME   = $CONFIG['baseurl'];
$THEME  = $CONFIG['theme'];
$_      = $CONFIG['include_separator'];
set_include_path(get_include_path() .$_
                . $CONFIG['compile_dir']. $_
                 $_SERVER['DOCUMENT_ROOT'].$CONFIG['basedir'].$_.
                 $_SERVER['DOCUMENT_ROOT'].$CONFIG['basedir'].'/lib/'
);

include_once('lib/http_auth.php');
include_once('lib/public_dbclass.php');
include_once('lib/rebuild_url.php');
include_once('Smarty.class.php');


// TODO: 恢复身份认证部分
doHttpAuth();


header('Content-Type: text/html;charset=UTF-8');

session_start();

// 排除URL的目录问题
$pos  = strpos('http://'.HOST.$_SERVER['REQUEST_URI'], $HOME);
if($pos===0)
    $rurl = substr('http://'.HOST.$_SERVER['REQUEST_URI'], strlen($CONFIG['baseurl']));
else
	die('配置文件错误.<br/> <a href="./install/">修改配置</a>');

rebuildURL($rurl);

$DB_Mailsys = new DB_Sql(
    $CONFIG['dbserver'],
    $CONFIG['database'],
    $CONFIG['dbuser'],
    $CONFIG['dbpass'],    
    'DB_MAILSYS');

$themepath = $CONFIG['smarty'].'/'.$CONFIG['theme'];

$topicdir  = $CONFIG['topicdir'];
$topicurl  = $CONFIG['topicurl'];


$strClassFileName = CTL_DIR.$_GET['class'].".php";

if(is_file($strClassFileName))
{
    require_once($strClassFileName);
	
    $objMain = new $_GET['class'];
    if(method_exists($objMain,$_GET['method']))
    {
        $objMain->$_GET['method']();
    }
	else
	{
		echo 'The Controller ',$_GET['class'],' need the method:', $_GET['method'];
		die();
	}
}
else
{
  	//echo 'Class Not Found:'.$_SERVER['QUERY_STRING'];
	header('Status: 404 Not found');

	if(is_file("./v/$THEME/error.html"))
	{
	    header("HTTP/1.0 404 Not Found");
		echo file_get_contents("./v/$THEME/error.html");
	}
	else
		echo 'error 404<br/>and theme file: error.html not exists';


	// TODO: this line for debug, remove this line before release
	echo '<pre>REQUET:';
	print_r($_GET);
	die();
}
