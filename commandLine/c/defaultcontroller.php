<?php 
include_once('commoncontroller.php');

class defaultcontroller extends CommonController
{
	public function index()
	{
		$core = Core::getInstance();
		// SHOW HELP
		echo "VERSION:". $core->getVersion();
	}

}

