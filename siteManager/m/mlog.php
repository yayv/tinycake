<?php 

class mlog extends model
{
    public $_logfile;

    public $_badcalls;
    public $_url_times;
    public $_method_times;
    public $_model_times;
    public $_controller_times;
    public $_action_times;

    public function __construct()
    {
        $this->_badcalls        = array();
        $this->_url_times       = array();
        $this->_method_times    = array();
        $this->_model_times     = array();
        $this->_controller_times= array();
        $this->_action_times    = array();
    }

    public function setLogfile($file)
    {
        $this->_logfile = $file;
    }

    public function parseFile($file, $urlpatterns4merge=false)
    {
        require_once('mlogsection.php');

        $log = new mlogsection();
        $fp = fopen($file, 'r');

        if($fp)
            while(!feof($fp))
            {
                $line = fgets($fp);
                $endsection = $log->addLine($line);

                if($endsection)
                {
                    $this->appendToAnalyse($log, $urlpatterns4merge);
                    unset($log);
                    $log = new mlogsection();
                    $log->addLine($line);
                }
                $line = null;
            }
        fclose($fp);
    }

    function appendToAnalyse($logobj, $urlpatterns4merge=false)
    {

        if(!$logobj->_notclearexit)
        {
			$key = trim($logobj->_url);
			if(is_array($urlpatterns4merge))
			foreach ($urlpatterns4merge as $urlPattern )
			{
				if(preg_match($urlPattern,$key))
				{
					$key = $urlPattern;
				}
            }
            
            if(!isset($this->_url_times[$key]))
            {
                $this->_url_times[$key]['times'] = 0;
                $this->_url_times[$key]['runtime'] = 0;
            }

            $this->_url_times[$key]['times'] += 1;
            $this->_url_times[$key]['runtime'] += $logobj->_urltime;

            // merge logobj's numbers
            foreach($logobj->_method_times as $k=>$v)
            {
                if(!isset($this->_method_times[$k]))
                {
                    $this->_method_times[$k] = $v;
                }
                else
                {
                    $this->_method_times[$k]['times'] += $v['times'];
                    $this->_method_times[$k]['runtime'] += $v['runtime'];
                }
            }

            // ----
            foreach($logobj->_model_times as $k=>$v)
            {
                if(!isset($this->_model_times[$k]))
                {
                    $this->_model_times[$k] = $v;
                }
                else
                {
                    $this->_model_times[$k]['times'] += $v['times'];
                    $this->_model_times[$k]['runtime'] += $v['runtime'];
                }
            }


            // ----
            foreach($logobj->_controller_times as $k=>$v)
            {
                if(!isset($this->_controller_times[$k]))
                {
                    $this->_controller_times[$k] = $v;
                }
                else
                {
                    $this->_controller_times[$k]['times'] += $v['times'];
                    $this->_controller_times[$k]['runtime'] += $v['runtime'];
                }
            }


            // ----
            foreach($logobj->_action_times as $k=>$v)
            {
                if(!isset($this->_action_times[$k]))
                {
                    $this->_action_times[$k] = $v;
                }
                else
                {
                    $this->_action_times[$k]['times'] += $v['times'];
                    $this->_action_times[$k]['runtime'] += $v['runtime'];
                }
            }
        }
        else
        {
            // append for badcalls
            $dieincall = trim($logobj->_badcall);
            if(isset($this->_badcalls[$dieincall]))
            {
                $this->_badcalls[$dieincall] = array();
            }
            $url = trim($logobj->_url);
            $this->_badcalls[$dieincall][$url] += 1;
        }
    }

    function calcAvgTime()
    {
        // foreach 
        foreach($this->_url_times as $k=>$v)
        {
            if(0==$v['times']) 
                $this->_url_times[$k]['avgtime'] = 0;
            else
                $this->_url_times[$k]['avgtime'] = $v['runtime'] / $v['times'];
        }

        foreach($this->_controller_times as $k=>$v)
        {
            if(0==$v['times']) 
                $this->_controller_times[$k]['avgtime'] = 0 ;
            else
                $this->_controller_times[$k]['avgtime'] = $v['runtime'] / $v['times'];
        }

        foreach($this->_action_times as $k=>$v)
        {
            if(0==$v['times']) 
                $this->_action_times[$k]['avgtime'] = 0;
            else
                $this->_action_times[$k]['avgtime'] = $v['runtime'] / $v['times'];
        }

        foreach($this->_model_times as $k=>$v)
        {
            if(0==$v['times']) 
                $this->_model_times[$k]['avgtime'] = 0;
            else
                $this->_model_times[$k]['avgtime'] = $v['runtime'] / $v['times'];
        }

        foreach($this->_method_times as $k=>$v)
        {
            if(0==$v['times']) 
                $this->_method_times[$k]['avgtime'] = 0;
            else
                $this->_method_times[$k]['avgtime'] = $v['runtime'] / $v['times'];
        }
    }

    function dumpToFile($filename)
    {
        $fp = fopen($filename, 'w');
        fwrite($fp, "<?php\n");

        $dump = '$this->_badcalls = ' . var_export($this->_badcalls, true). ";\n";
        fwrite($fp, $dump);

        $dump = '$this->_url_times = ' . var_export($this->_url_times, true). ";\n";
        fwrite($fp, $dump);

        $dump = '$this->_controller_times = ' . var_export($this->_controller_times, true). ";\n";
        fwrite($fp, $dump);

        $dump = '$this->_action_times = ' . var_export($this->_action_times, true). ";\n";
        fwrite($fp, $dump);

        $dump = '$this->_model_times = ' . var_export($this->_model_times, true). ";\n";
        fwrite($fp, $dump);

        $dump = '$this->_method_times = ' . var_export($this->_method_times, true). ";\n";
        fwrite($fp, $dump);

        fclose($fp);
    }

    function loadFromFile($filename)
    {
        include($filename);
    }

    function mergeAnotherFile($file)
    {
        $fno = new mlog();
        $fno->loadFromFile($file);
        $this->mergeAnotherLog($fno);
        unset($fno);
    }

    function mergeAnotherLog($mlogobj)
    {
        foreach($mlogobj->_badcalls as $k=>$v)
        {
            if(isset($this->_badcalls[$k]))
            {
                $this->_badcalls[$k]['times'] += $v['times'];
                $this->_badcalls[$k]['runtime'] += $v['runtime'];
            }
            else
                $this->_badcalls[$k] = $v;
        }

        foreach($mlogobj->_url_times as $k=>$v)
        {
            if(isset($this->_url_times[$k]))
            {
                $this->_url_times[$k]['times'] += $v['times'];
                $this->_url_times[$k]['runtime'] += $v['runtime'];
            }
            else
                $this->_url_times[$k] = $v;
        }

        foreach($mlogobj->_controller_times as $k=>$v)
        {
            if(isset($this->_controller_times[$k]))
            {
                $this->_controller_times[$k]['times'] += $v['times'];
                $this->_controller_times[$k]['runtime'] += $v['runtime'];
            }
            else
                $this->_controller_times[$k] = $v;
        }

        foreach($mlogobj->_action_times as $k=>$v)
        {
            if(isset($this->_action_times[$k]))
            {
                $this->_action_times[$k]['times'] += $v['times'];
                $this->_action_times[$k]['runtime'] += $v['runtime'];
            }
            else
                $this->_action_times[$k] = $v;
        }

        foreach($mlogobj->_model_times as $k=>$v)
        {
            if(isset($this->_model_times[$k]))
            {
                $this->_model_times[$k]['times'] += $v['times'];
                $this->_model_times[$k]['runtime'] += $v['runtime'];
            }
            else
                $this->_model_times[$k] = $v;
        }

        foreach($mlogobj->_method_times as $k=>$v)
        {
            if(isset($this->_method_times[$k]))
            {
                $this->_method_times[$k]['times'] += $v['times'];
                $this->_method_times[$k]['runtime'] += $v['runtime'];
            }
            else
                $this->_method_times[$k] = $v;
        }
    }

    function getBadCalls()
    {
        return $this->_badcalls;
    }

    function getUrlTimes()
    {
        return $this->_url_times;
    }

    function getControllerTimes()
    {
        return $this->_controller_times;
    }

    function getActionTimes()
    {
        return $this->_action_times;
    }

    function getModelTimes()
    {
        return $this->_model_times;
    }

    function getMethodTimes()
    {
        return $this->_method_times;
    }

    public function calc()
    {
        // 计算平均时间 和 最大响应时间
        // 
    }

    // TODO: 这个方法比较危险，考虑一个什么方法来提升安全性吧
    public function removeFile($filename)
    {
        $ret = unlink($filename);
        if(!$ret)
            die($filename);
        return ;
    }

    public function showDetails()
    {
        echo '<pre>';
        print_r($this->_badcalls);
        print_r($this->_url_times);
        print_r($this->_controller_times);
        print_r($this->_action_times);
        print_r($this->_model_times);
        print_r($this->_method_times);
        echo '</pre>';
    }

    public function loadPhpLog($filename)
    {

    }

    public function mergePhpLog($obj1, $obj2)
    {

    }

}

