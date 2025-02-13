<?php
$CONFIG = array();

// for multi-domain, you can use host:port or $CONFIG['domain']
$CONFIG['domain']  = "localhost";
// end with "/"
$CONFIG['baseUri'] = '/siteManager/';

// 必须设置的3项
$CONFIG['site'] = "localhost";
$CONFIG['sitebase'] = 'http://localhost:8088/siteManager';
$CONFIG['sitename'] = 'Tinycake Site Manager';

$CONFIG['theme'] = 'v/default';
$CONFIG['compiled_template'] = 'v/_run';

$CONFIG['baseurl'] = 'http://localhost:8088';
// $CONFIG['basedir'] = '/Users/liuce/Projects/tinycake/install';

$CONFIG['database'] = array(
	'host' 		=> '127.0.0.1',
	'port' 		=> '3306',
	'username' 	=> 'office',
	'password' 	=> 'officedb',
	'database' 	=> 'topbox',
	);
