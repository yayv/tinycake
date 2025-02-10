<?php
$urlfile = './d.lvren.cn_access_log.2011-09-27';

// 只读打开文件
$f = fopen($urlfile, 'r');

// 请求信号灯
$sem = sem_get(0xEF0000);
sem_acquire($sem);

// 获得偏移量
$shm = shm_attach(0xEF0001);
$offset = shm_get_var($shm, 1);
if(!$offset)
    $offset= 0;
fseek($f, $offset);
$url = fgets($f);
$offset = ftell($f);
shm_put_var($shm, 1, $offset);
shm_detach($shm);

// 释放信号灯
sem_release($sem);

// 关闭文件
fclose($f);

$uri = trim($url);
echo "http://d.office.lvren.cn".$uri;
//file_get_contents("http://d.office.lvren.cn".$uri);



