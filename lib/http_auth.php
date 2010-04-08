<?php

function doHttpAuth()
{
    if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']))
    {
        $usr = $_SERVER['PHP_AUTH_USER'];
        $pwd = $_SERVER['PHP_AUTH_PW'];
    
        // TODO: add your username/password check method, if ok then set $ret to true, other wise set it to false
        $ret = true;
    
        if($ret!='PASSWD_OK')
        {
            header('WWW-Authenticate: Basic realm="Top-secret area"');
            header('HTTP/1.0 401 Unauthorized');
    
            // Error message
            print "Sorry, login failed!\n";
            print "<br/>";
            die();
        }
    }
    else
    {
        header('WWW-Authenticate: Basic realm="Top-secret area"');
        header('HTTP/1.0 401 Unauthorized');
    
        // Error message
        print "Sorry, login failed!\n";
        print "<br/>";
        die();
    }
}
