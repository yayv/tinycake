<?php 

class model
{
	var $_config;
	var $_db;
	
	function init($config, $db=false)
	{
		$this->_config = $config;
		$this->_db     = $db;
	}
}

