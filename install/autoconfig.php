<?php
/*
 自动配置功能

 1.载入config.php
 
 2.确认当前主机的配置是否存在，不存在则用 default 复制生成一个
    2.1 修改几个默认配置项的参数
 
 3.载入.htaccess
 
 4.更正 rewritebase
 
*/

function updateHTACCESS($newrewritebase)
{
        
}

require('config.php');

$HOST = $_SERVER["HTTP_HOST"];
if(isset($config[$HOST]))
    print_r($config[$HOST]);
else
    echo "对应主机$HOST 的配置项不存在";

#phpinfo();

