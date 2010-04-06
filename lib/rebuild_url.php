<?php

function rebuildURL($requesturl)
{
    /*
    url example: /class/method/param1-value1/param2-value2/param3-value3?exparams
    => $_GET=> array(
        'class' => 'class'
        'method' => 'method'
        'param1' => 'value1'
        ...
    )
    
    */

    
    #$exparams = explode('?', $_SERVER['REQUEST_URI']);
    $exparams = explode('?', $requesturl);
    $params = explode("/",$exparams[0]);

	$_GET['class']=='';
	$_GET['method']=='';
    foreach( $params as $p => $v )
    {
        $kv = explode('-', $v);

        if(count($kv)>1)
        {
            $_GET[$kv[0]] = $kv[1];
        }
        else
        {
            $_GET['params'.$p] = $kv[0];
        }

		if(count($kv)===1)
		    switch($p)
		    {
		        case 0: continue;break;
		        case 1:$_GET['class']=$v;break;
		        case 2:	$_GET['method']=$v;	break;
		        default: break;
		    }
    }

    if($_GET['class']=='') $_GET['class'] = 'index';
    if($_GET['method']=='') $_GET['method'] = 'main';
}

