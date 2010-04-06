<?php
/*
 用简单的单文件来实现配置设置和更新
 
 检查项 :
    1. .htaccess
    2. index.php
    3. config.php
    4. host
    5. baseurl
    
    
    6. 可自动生成系统必须的配置项
*/

require('config.php');

$HOST = $_SERVER["HTTP_HOST"];
if(isset($config[$HOST]))
    print_r($config[$HOST]);
else
    echo "对应主机$HOST 的配置项不存在";

#phpinfo();

