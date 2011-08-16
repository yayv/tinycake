<?php
class mlogsection
{
    public $_logs;
    public $_endsection;
    public $_model_times;
    public $_method_times;
    public $_controller_times;
    public $_action_times;
    public $_callstack ;
    public $_url;
    public $_url_starttime;
    public $_url_endtime;
    public $_urltime;
    public $_notclearexit;
    public $_badcall;

    function __construct()
    {
        $this->_logs = array();
        $this->_url = "";
        $this->_urltime = 0;
        $this->_endsection = false;
        $this->_model_times = array();
        $this->_method_times = array();
        $this->_controller_times = array();
        $this->_action_times = array();
        $this->_callstack = "";
        $this->_notclearexit = false;
        $this->_badcall = "";
    }

    function diffmicrotime($startmsec, $startsec, $endmsec, $endsec)
    {
        $diffsec  = $endsec - $startsec;
        $diffmsec = intval($endmsec) - intval($startmsec); 

        if($diffmsec<0) $diffmsec = 100000000 + $diffmsec;

        $diff =  $diffsec.substr('00000000'.$diffmsec, -8,6);

        return intval($diff);
    }

    function pushModelTime($logline)
    {
        preg_match('/start\((\w*)->(\w*)\): 0\.(\d*) (\d*)/', $logline, $model);
        // TODO: push into model_tims and method_times
        if(!isset($this->_model_times[$model[1]]))
        {
            $this->_model_times[$model[1]] = array(
                'tmp_push'  => array($model[3], $model[4]),
                'times'     => 0,
                'runtime'  => 0,
            );
        }
        else
        {
            $this->_model_times[$model[1]]['tmp_push'] = array($model[3], $model[4]);
        }

        return ;
    }

    function calcModelTime($logline)
    {
        preg_match('/end\((\w*)->(\w*)\): 0\.(\d*) (\d*)/', $logline, $model);
        if(!isset($this->_model_times[$model[1]]))
        {
            // get something wrong, skip it
        }
        else
        {
            $start = &$this->_model_times[$model[1]]['tmp_push'];
            $time = $this->diffmicrotime(
                $start[0], $start[1],
                $model[3], $model[4]
            );
            $this->_model_times[$model[1]]['times'] ++;
            $this->_model_times[$model[1]]['runtime'] += $time;
            unset($this->_model_times[$model[1]]['tmp_push']);
        }

        return ;
    }

    function pushMethodTime($logline)
    {
        preg_match('/start\((\w*)->(\w*)\): 0\.(\d*) (\d*)/', $logline, $model);
        $methodname = $model[1].'->'.$model[2];
        if(!isset($this->_method_times[$methodname]))
        {
            $this->_method_times[$methodname] = array(
                'tmp_push'  => array($model[3], $model[4]),
                'times'     => 0,
                'runtime'  => 0,
            );
        }
        else
        {
            $this->_method_times[$methodname]['tmp_push'] = array($model[3], $model[4]);
        }

        return ;
    }

    function calcMethodTime($logline)
    {
        preg_match('/end\((\w*)->(\w*)\): 0\.(\d*) (\d*)/', $logline, $model);
        $methodname = $model[1].'->'.$model[2];
        if(!isset($this->_method_times[$methodname]))
        {
            // get something wrong, skip it
        }
        else
        {
            $start = &$this->_method_times[$methodname]['tmp_push'];
            $time = $this->diffmicrotime(
                $start[0], $start[1],
                $model[3], $model[4]
            );
            $this->_method_times[$methodname]['times'] ++;
            $this->_method_times[$methodname]['runtime'] += $time;
            unset($this->_method_times[$methodname]['tmp_push']);
        }

        return ;
    }

    function pushControllerTime($logline)
    {
        preg_match('/start_controller\((\w*)->(\w*)\):\s*0\.(\d*) (\d*)/', $logline, $model);
        $ctrl = $model[1];
        if(!isset($this->_controller_times[$ctrl]))
        {
            $this->_controller_times[$ctrl] = array(
                'tmp_push'  => array($model[3], $model[4]),
                'times'     => 0,
                'runtime'  => 0,
            );
        }
        else
        {
            $this->_controller_times[$ctrl]['tmp_push'] = array($model[3], $model[4]);
        }

        return ;
    }

    function calcControllerTime($logline)
    {
        preg_match('/end_controller\((\w*)->(\w*)\):\s*0\.(\d*) (\d*)/', $logline, $model);
        $ctrl = $model[1];
        if(!isset($this->_controller_times[$ctrl]))
        {
            // get something wrong, skip it
        }
        else
        {
            $start = &$this->_controller_times[$ctrl]['tmp_push'];
            $time = $this->diffmicrotime(
                $start[0], $start[1],
                $model[3], $model[4]
            );
            $this->_controller_times[$ctrl]['times'] ++;
            $this->_controller_times[$ctrl]['runtime'] += $time;
            unset($this->_controller_times[$ctrl]['tmp_push']);
        }

        return ;
    }

    function pushActionTime($logline)
    {
        preg_match('/start_controller\((\w*)->(\w*)\):\s*0\.(\d*) (\d*)/', $logline, $model);
        $action = $model[1].'->'.$model[2];
        if(!isset($this->_action_times[$action]))
        {
            $this->_action_times[$action] = array(
                'tmp_push'  => array($model[3], $model[4]),
                'times'     => 0,
                'runtime'  => 0,
            );
        }
        else
        {
            $this->_action_times[$action]['tmp_push'] = array($model[3], $model[4]);
        }

        return ;
    }

    function calcActionTime($logline)
    {
        preg_match('/end_controller\((\w*)->(\w*)\):\s*0\.(\d*) (\d*)/', $logline, $model);
        $action = $model[1].'->'.$model[2];//print_r($model);die($action);
        if(!isset($this->_action_times[$action]))
        {
            // get something wrong, skip it
        }
        else
        {
            $start = &$this->_action_times[$action]['tmp_push'];
            $time = $this->diffmicrotime(
                $start[0], $start[1],
                $model[3], $model[4]
            );
            $this->_action_times[$action]['times'] ++;
            $this->_action_times[$action]['runtime'] += $time;
            unset($this->_action_times[$action]['tmp_push']);
        }

        return ;
    }

    function getDetail()
    {
        echo '<pre>',$this->_urltime," : ", $this->_method," ",$this->_url,"<br/>";
        if(!$this->_notclearexit)
        {
            print_r($this->_model_times);
            print_r($this->_method_times);
            print_r($this->_action_times);
            print_r($this->_controller_times);
        }
        else
            echo $this->_badcall;
        echo "</pre><br/><br/><hr>";
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
                $this->pushModelTime($logline);
                $this->pushMethodTime($logline);
                break;
            case 0===strpos($logline, 'end('):
                $this->calcModelTime($logline);
                $this->calcMethodTime($logline);
                break;
            case 0===strpos($logline, 'start_controller('):
                $this->pushControllerTime($logline);
                $this->pushActionTime($logline);
                break;
            case 0===strpos($logline, 'end_controller('):
                $this->calcActionTime($logline);
                $this->calcControllerTime($logline);
                break;
            case 0===strpos($logline, 'CallStack is NOT empty'):
                $this->_notclearexit = true;
                break;
            default:
                if($this->_notclearexit)
                {
                    $this->_badcall = $logline;
                }
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

}

