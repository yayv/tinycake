<?php
/**
 * the basic class
 * 
 */
include('c/common.php');

class index extends common
{
	public $smarty;
	public $theme;
	public $config;

	public $mmenu;

	function __construct()
	{
		parent::initConfig($this);

		// load menu module
		require_once('m/mmenu.php');
		$this->mmenu = new mmenu($this->config['baseurl']);


	}

    function main()
    {
		// get Menu View
		echo 'Hi';
    }
};

