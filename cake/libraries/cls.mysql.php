<?php
/**
  * Note: 这个类是为了对之前所用的mysql类进行过度而进行封装的。将来，有可能在tinycake类中直接使用mysqli
  * 
  */
class mysql
{
	// mysqli 对象
	var $mi;
	// 日志文件句柄
	var $logfile;
	// 链接mysql服务器的参数
	var $db;

    function __construct($server)
    {
		// 如果$server有配置，则用server,如果没有，则使用defcfg的默认配置
		$defcfg = array(
				"host"=>'127.0.0.1',
				"port"=>"3306",
				'username'=>'', 
				'password'=>'', 
				'database'=>'', 
				'socket'=>'', 
				'charset'=>'utf8',

			);

		$res = array_diff_key($defcfg, $server);
		$this->db = array_merge($res, $server);

		// 建立链接
    	$this->mi = new mysqli($this->db['host'], $this->db['username'], $this->db['password'], $this->db['database'], $this->db['port'],$this->db['socket']);

    	$this->mi->set_charset($this->db['charset']);
    	$this->logfile = "php://output";
    }

    function setLogfile($logfile='php://output')
    {
		$this->logfile = $logfile;
    }

    function connect()
    {
		$ret = $this->mi->real_connect(
					$this->db['host'], 
					$this->db['username'], 
					$this->db['password'], 
					$this->db['database'], 
					$this->db['port'], 
					$this->db['socket']
			);

        if($ret)
        {
            $this->mi->set_charset($this->db['charset']);
        }
        else
        {
        	$this->show_error($this->mi->connect_error);
        }		
    }

    function select_db($database)
    {
    	// inherent
    	$this->mi->select_db($database);
    }


    function get_error()
    {
        if($this->mi->connect_errno)
        {
            $error['sql']           = $this->mi->escape_string;
            $error['number']        = $this->mi->connect_errno;
            $error['description']    = $this->mi->connect_error;
        }
        else 
        {
            $error['sql']           = $this->mi->escape_string;
            $error['number']        = $this->mi->errno;
            $error['description']    = $this->mi->error;
        }
        return $error;
    }

    function show_error($title)
    {
        $content = "--------------------\n";
        $content.= $title."\n";
        $content.= $this->mi->connect_errno?$this->mi->connect_error:$this->mi->error;
        $content.= "\n";
        $content.= "$this->last_sql\n";

        error_log($content, 3, $this->logfile);

        return $content;
    }

    function affected_rows()
    {
    	// inherent
		return $this->mi->affected_rows;
    }

    function query($sql)
    {
		$this->lastresult = $this->mi->query($sql);
		return $this->lastresult;
    }

    function insert_id()
    {
    	// inherent
    	return $this->mi->insert_id;
    }

    function fetch_value($sql,$fieldname)
    {
        $list         = $this->query($sql);
        if($list!=false)
        {
            $list_array = $this->fetch_array($list);
            return $list_array[$fieldname];        
        }
        else
            return false;
    }

    function fetch_one_value($sql)
    {
        $list         = $this->query($sql);
        if($list!=false)
        {
            $list_array = $this->fetch_array($list);
            return $list_array[0];
        }
        else
            return false;
    }

    function num_rows($result)
    {
		return $result->num_rows;
    }

    function fetch_one_array($sql)
    {
        $list         = $this->query($sql);
        if($list!=false)
        {
            $list_array = $this->fetch_array($list);
            return $list_array;
        }
        else
            return false;
    }

    function fetch_one_assoc($sql)
    {
        $list         = $this->query($sql);
        if($list!=false)
        {
            $list_array = $this->fetch_assoc($list);
            return $list_array;
        }
        else
            return false;
    }

    function fetch_all_array($sql,$max=0)
    {
        if(is_string($sql))    
            $query = $this->query($sql);
        else
            $query = $sql;

        $all_array = array();
        while($list_item = $this->fetch_array($query))
        {
            $current_index ++;
            
            if($current_index > $max && $max != 0)
            {
                break;
            }
            
            $all_array[] = $list_item;
            
        }
        
        return $all_array;    
    }

    function fetch_all_assoc($sql,$max=0)
    {
        $current_index = 0;
        $all_array = array();

        if(is_string($sql)){
            $query = $this->mi->query($sql);
            print_r($query);print_r($this->mi);
        }    
        else{
            $query = $sql;
        }

        while($list_item = $query->fetch_assoc())
        {

            $current_index ++;
            
            if($current_index > $max && $max != 0)
            {
                break;
            }
            
            $all_array[] = $list_item;
            
        }
        
        return $all_array;
    }

    function fetch_array($result)
    {
    	$ret = $result->fetch_array();
		return $ret;
    }

    function fetch_assoc($result)
    {
		$ret = $result->fetch_assoc();
		return $ret;
    }

    function fetch_row($result)
    {
		$row = $result->fetch_row();
		return $row;
    }

    function fetch_all_object($sql,$max=0)
    {
        $current_index = 0;
        $result = $this->query($sql);

        if ($result) 
        {
            /* fetch object array */
            while ($obj = $result->fetch_object()) 
            {
                $current_index ++;
                
                if($current_index > $max && $max != 0)
                {
                    break;
                }
                
                $all_array[] = $obj;
            }

            /* free result set */
            $result->close();
            return $all_array;
        }
        else
            return false;
    }

    function fetch_one_object($sql,$max=0)
    {
        $query = $this->query($sql);

        return $this->fetch_object($query);
    }

    function fetch_object($result)
    {
        $ret = $result->fetch_object();
        return $ret;
    }

}
