<?php 
class Core
{
	static private $instance ; 

	public $_config;
	private $_log;
	private $_callstack;
	private $_logpath;
	
	function __construct()
	{
		$this->_logpath = realpath('./');

		$this->_log = array();

		$this->_callstack = array();
		register_shutdown_function ( array(&$this,'shutdown') );
	}
	
	function RegisterShutdown($funcname)
	{
		// TODO: push a flag
		array_push($this->_callstack, $funcname);
	}
	
	function UnregisterShutdown($funcname)
	{
		// TODO: pop a flag
		$oldfuncname = array_pop($this->_callstack);
		if($funcname != $oldfuncname)
		{
			$this->pushLog("WARNING: wrong sequence of UnregisterShutdown\n");
			array_push($this->_callstack, $oldfuncname);	
			array_push($this->_callstack, $funcname);
		}
	}
	
	function shutdown()
	{
		#Core::getInstance()->writelog();
		if(count($this->_callstack)>0)
		{
			$this->pushLog("CallStack is NOT empty\n");
			foreach($this->_callstack as $v)
			{
				$this->pushLog($v);
			}
            $this->pushLog("\n");
			$this->writeLog();
		}
	}
	
	function loadConfig($host)
	{
		if(is_file('configs/'."cfg.$host.php")) 
			require_once('configs/'."cfg.$host.php");
		else
			require_once('configs/'."cfg.default.php");

		$this->_config = $CONFIG;		

        // 载入controller map
        if(is_file('configs/controller_map.php'))
            include_once('configs/controller_map.php');

        $this->_controller_map = isset($cmap)?$cmap:array();
	}

	function UrlMap($url)
	{
		foreach($this->_url_map as $k=>$v)
		{
			$ret = preg_match($v, $url, $matches);
			if($ret)
			{
				return $v;
			}
		}
		return array();
	}

    function ControllerMap($c, $a)
    {
		if(array_key_exists($c.'/'.$a, $this->_controller_map))
        	return $this->_controller_map[$c.'/'.$a] ;
        else if(array_key_exists($c, $this->_controller_map))
            return array($this->_controller_map[$c], 'index');
        else
            return array($c, $a);
    }
	
	function getConfig($key, $subkey=false)
	{
		if(isset($this->_config) && array_key_exists($key, $this->_config))
		{
			if(false===$subkey || !array_key_exists($subkey, $this->_config[$key]))
				return $this->_config[$key];
			else
				return $this->_config[$key][$subkey];
		}
			
		else
			return false;
	}

	function getAllConfig()
	{
		return $this->_config;
	}

	function pushLog($log)
	{
		array_push($this->_log, $log);
	}
	
	function writeLog()
	{
		foreach($this->_log as $line)
		{
			error_log($line, 3, $this->_logpath.'/logs/crumbs.'.date('Y-m-d').'.txt');
		}
	}
	
	/**
	 * TODO: 让 loadController 兼容原来的 index.php 所做的事情吗？
	 * 		还是让 index.main 兼容原来的index.php所做的事情吧，那原来的 index.main做的事情呢？
	 * 		class_alias 需要在这里支持
	 * @param unknown_type $classname
	 */
	function loadController($classname)
	{
		if(is_file('c/'.$classname.'.php'))
			include_once('c/'.$classname.'.php');
		else
		{
			require_once('c/defaultcontroller.php');
			$classname = 'defaultcontroller';
		}

		return new $classname;
	}
	
	function clickLog($filename)
    {		
		$sessionid  = session_id();
		$company_id = (int)$_GET["from_company_id"];
		$ip         = $_SERVER["REMOTE_ADDR"];
		$time       = time();
		$agent      = $_SERVER["HTTP_USER_AGENT"];
			
		$content    = $sessionid."|".$company_id."|hotel|".$_REQUEST['cityname'].','.$_REQUEST['key']."|".$_SERVER['REQUEST_URI']."|".$_SERVER["HTTP_REFERER"]."|".$ip."|".$time."|".$agent."\r\n";
		$open = @fopen($filename,"a");
		@fwrite($open,$content);
		@fclose($open);
    }

    /**
     * TODO: 这个代码的完善，还需要把柯志的代码全部调整一遍才能完全确认
     * 
     * @param $uri
     */
    function rebuildUrl($uri, $base='/')
	{  
	    /*
	    url example: /controller/action/param1-value1/param2-value2/param3-value3?exparams
	    => $_GET=> array(
	        'controller' => 'controller'
	        'action' => 'action'
	        'param2' => 'value2'
	        ...
	    )
	    
	    */

		if(0===strpos($uri, $base))
		{
			$uri = substr($uri, strlen($base));
			#echo $suri, "<br>";
		}

	    #$exparams = explode('?', $_SERVER['REQUEST_URI']);
		// TODO: match base URI
		
		$tail 		= strstr($uri, $base);
	    $exparams 	= explode('?', $uri);
	    $params 	= explode("/",$exparams[0]);

		$_GET['controller']	='';
		$_GET['action']		='';
        $_GET['method']		='';
	    foreach( $params as $p => $v )
	    {
	        #$kv = explode('-', $v);
	        $kv = strstr($v,'-', true);
	        $vv = '';

	        if($kv===false)
	        {
	        	$_GET['params'.$p] = $v;
	        	$vv = $v;
	        }
	        else
	        {
	        	$_GET[$kv] = substr(strstr($v, '-'), 1);
	        	$vv = substr(strstr($v, '-'), 1);
	        }
	        
			if(count($kv)===1)
			    switch($p)
			    {
			        #case 0: continue;break;
			        case 0:$_GET['controller']=$vv;	break;
			        case 1:$_GET['action']=$vv;		break;
				    case 2:$_GET['method']=$vv;		break;
			        default: break;
			    }
	    }

	    if($_GET['controller']=='') $_GET['controller'] = 'defaultcontroller';
	    if($_GET['action']=='') $_GET['action'] = 'index';
 		if($_GET['method']=='') $_GET['method'] = 'index';

	    return array($_GET['controller'], $_GET['action'],$_GET['method']);
	}
    	
	function loadSession()
	{
		session_start();
	}
		
    // Prevent users to clone the instance
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }
    	
	public static function getInstance()
	{
		try {
	        if (!isset(self::$instance)) {
	            $c = __CLASS__;
	            #debug_print_backtrace();
	            self::$instance = new $c;
	        }

	        return self::$instance;

		} catch (Exception $e) {        // Skipped
			debug_print_backtrace();
		    echo "Caught Default Exception\n", $e;
		}

	} 
}
