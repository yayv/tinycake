<?php
//---------[ 本段为服务器环境配置 ]---------------------------------------------

// 本地开发环境需要的配置
$config['active.lvren.cn'] = array(
	// ---[  系统相关  ]---
	'include_separator' => ':',

	// 专题系统在本虚拟主机下的目录，问题:如果是alias的目录怎么办？
	'basedir'  => '/topicsys',   

	// 系统的根URL
	'baseurl'  => 'http://active.lvren.cn/topicsys', 

	// 数据库
	'dbserver' => 'dbserver_sns',
	'database' => 'topicsys',
	'dbuser'   => 'LVRN',
	'dbpass'   => 'aCim3)J9n$M',

);


// 配置参数说明
$config['default'] = array(
	// ---[  系统相关  ]---
	'include_separator' => ':',

	// 专题系统在本虚拟主机下的目录，问题:如果是alias的目录怎么办？
	'basedir'  => '/topicsys',   

	// 系统的根URL
	'baseurl'  => 'http://active.lvren.cn/topicsys', 

	// 数据库
	'dbserver' => 'dbserver_sns',
	'database' => 'topicsys',
	'dbuser'   => 'LVRN',
	'dbpass'   => 'aCim3)J9n$M',
);



