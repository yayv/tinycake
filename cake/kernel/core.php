<?php 
class Core
{
	static private $instance ; 

	public $_config;
	private $_log;
	
	function __construct()
	{
		$this->_log = array();

		#register_shutdown_function ( array('core','shutdown') );
	}
	
	static function shutdown()
	{
		#Core::getInstance()->writelog();
	}
	
	function loadConfig($host)
	{
		if(is_file('configs/'."cfg.$host.php")) 
			require_once('configs/'."cfg.$host.php");
		else
			require_once('configs/'."cfg.default.php");

		$this->_config = $CONFIG;		
	}
	
	function getConfig($key)
	{
		if(isset($this->_config))
			return $this->_config[$key];
		else
			return false;
	}

	function pushLog($log)
	{
		array_push($this->_log, $log);
	}
	
	function writeLog()
	{
		foreach($this->_log as $line)
		{
			error_log($line, 3, './logs/controllerlog.txt');
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
			include('c/'.$classname.'.php');
		else
		{
			require('c/defaultcontroller.php');
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
     * a patch for old kezhi's code
     */
    function rebuildUrl_patch_forLIUKEZHI($uri)
    {
		$arrAction = explode('.',strtolower($_REQUEST['act']));
		return $arrAction;
    }
    
    /**
     * TODO: 这个代码的完善，还需要把柯志的代码全部调整一遍才能完全确认
     * 
     * @param $uri
     */
    function rebuildUrl($uri)
	{
		// do nothing
		if(isset($_REQUEST['act']))
		{
			return $this->rebuildUrl_patch_forLIUKEZHI($uri);
		}
		else
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
	    #$exparams = explode('?', $_SERVER['REQUEST_URI']);
	    $exparams = explode('?', $uri);
	    $params = explode("/",$exparams[0]);

		$_GET['controller']='';
		$_GET['action']='';
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
			        case 1:$_GET['controller']=$v;break;
			        case 2:	$_GET['action']=$v;	break;
			        default: break;
			    }
	    }
	
	    if($_GET['controller']=='') $_GET['controller'] = 'defaultcontroller';
	    if($_GET['action']=='') $_GET['action'] = 'index';

	    return array($_GET['controller'], $_GET['action']);
		}
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
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }

        return self::$instance;
	} 
}
