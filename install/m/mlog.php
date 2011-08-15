<?php 

class mlog extends model
{
    public $_logfile;

    /*
    需要记录的数据包括:
    URL 的执行时间
    */

    public function setLogfile($file)
    {
        $this->_logfile = $file;
    }

    public function parseFile($file)
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
                    $this->appendToAnalyse($log);
                    $log->getDetail();echo "<br/>";print_r($log);
                    unset($log); break;
                    $log = new mlogsection();
                    $log->addLine($line);
                }
            }
        fclose($fp);
    }

    function appendToAnalyse($logobj)
    {
        
    }

    public function showDetail()
    {

    }
}
 
