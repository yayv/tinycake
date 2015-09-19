<?php

// download.php
$file = false ;
if(!is_file('smarty3.zip'))
	$file = file_get_contents('https://github.com/smarty-php/smarty/archive/v3.1.27.zip') ;

if($file)
	file_put_contents('smarty3.zip',$file) ;

// unzip smarty3.zip

// ln -s smarty-3xxx/libs/ smarty3


// --------------
// download bootstrap3
$file = false ;
if(!is_file('bootstrap3.zip'))
	$file = file_get_contents('https://github.com/twbs/bootstrap/releases/download/v3.3.5/bootstrap-3.3.5-dist.zip') ;

if($file)
	file_put_contents('bootstrap3.zip', $file) ;

// unzip bootstrap3

// ln -s 

