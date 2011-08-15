<?php
class mlogsection
{
    public $_logs;
    public $_endsection;
    public $_times;
    public $_isBadCallStack;
    public $_callstack ;

    function __construct()
    {
        $this->_logs = array();
        $this->_endsection = false;
        $this->_times = array();
        $this->_isBadCallStack = false;
        $this->_callstack = "";
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
        print_r($this->_url);
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
                break;
            case 0===strpos($logline, 'end(url):'):
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

