<?php
function httpauth_callback_example($user, $password)
{
	if($user!=$password)
		return true;
	else
		return false;

}


function doHttpAuthWithDie($authfunc=httpauth_callback_example, $realm='Basic HTTP AUTH')
{
    if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']))
    {
        $usr = $_SERVER['PHP_AUTH_USER'];
        $pwd = $_SERVER['PHP_AUTH_PW'];
    
        if(is_string($authfunc) && function_exists($authfunc))
            $ret = $authfunc($usr, $pwd);
        else if(is_array($authfunc) && method_exists($authfunc[0], $authfunc[1]))
            $ret = $authfunc[0]->$authfunc[1]($usr, $pwd);
        else
            $ret = true;

        if(!$ret)
        {
            header("WWW-Authenticate: Basic realm='$realm'");
            header('HTTP/1.0 401 Unauthorized');
    
            // Error message
            print "Sorry, login failed!\n";
            print "<br/>";
            die();
        }
    }
    else
    {
        header("WWW-Authenticate: Basic realm='$realm'");
        header('HTTP/1.0 401 Unauthorized');
    
        // Error message
        print "Sorry, login failed!\n";
        print "<br/>";
        die();
    }
}
