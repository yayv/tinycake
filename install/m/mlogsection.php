<?php
class mlogsection
{
    public $_logs;
    public $_endsection;
    public $_times;
    public $_isBadCallStack;
    public $_callstack ;
    public $_url_starttime;
    public $_url_endtime;

    function __construct()
    {
        $this->_logs = array();
        $this->_endsection = false;
        $this->_times = array();
        $this->_isBadCallStack = false;
        $this->_callstack = "";
    }

	function diffmicrotime($startmsec, $startsec, $endmsec, $endsec)
	{
		$diffsec  = $endsec - $startsec;
		$diffmsec = intval($endmsec) - intval($startmsec); 

		if($diffmsec<0) $diffmsec = 100000000 + $diffmsec;

		$diff =  $diffsec.substr('00000000'.$diffmsec, -8);

		return intval($diff);
	}

    function getUrl()
    {
    }

    function getModules()
    {
    }

    function getSectionTime()
    {
    }

    function getDetail()
    {
		 echo $this->_urltime," : ", $this->_method," ",$this->_url,"<br/>";
    }

    function addLine($logline)
    {
        if(0===strpos($logline, "URL:") && count($this->_logs)>0)
        {
            // should be a new section
            $this->_endsection = true;
            return true;
        }
        else
        {
            array_push($this->_logs,$logline);
        }

        switch(true)
        {
            case 0===strpos($logline, 'URL:'):
                $this->_url = substr($logline,4);
                break;
            case 0===strpos($logline, 'METHOD:'):
                $this->_method = substr($logline,7);
                break;
            case 0===strpos($logline, 'start(url):'):
					// get the time
					//preg_match('/start\(url\):0\.(\d*) (\d*)/', $logline, $matches);
					//intval($matches[1]);
					//intval($matches[2]);
					$this->_url_starttime = substr($logline, 11);
                break;
            case 0===strpos($logline, 'end(url):'):
					$time = trim(substr($logline, 9));
					$this->_url_endtime = $time;
					preg_match('/0\.(\d*) (\d*)/', $time, $end);
					preg_match('/0\.(\d*) (\d*)/', $this->_url_starttime, $start);
					$this->_urltime = $this->diffmicrotime($start[1], $start[2], $end[1], $end[2]);
                break;
            case 0===strpos($logline, 'start('):
                break;
            case 0===strpos($logline, 'end('):
                break;
            case 0===strpos($logline, 'start_controller('):
                break;
            case 0===strpos($logline, 'end_controller('):
                break;
            case 0===strpos($logline, 'CallStack is NOT empty'):
                break;
            default:
                
                break;
        }

        // start do push
        // end do pop
        // start(url) do special
        // end(url) do special
        // start_controller do push
        // end_controller do pop 
        return $this->_endsection; // end section
    }

    function calc()
    {
        
    }
}

