<?php 

class model 
{
	var $_config;
	var $_db;

    static $_error = array();	

	function init($config, $db=false)
	{
		$this->_config = $config;
		$this->_db     = $db;
	}

    function pushError($params, $errmsg)
    {
        self::$_error[] = array(
                'params' => $params,
                'msg'    => $errmsg,
                'callstack' => debug_backtrace(),
        );
    }

    static function popError()
    {
        return array_pop(self::$_error);
    }
}

