<?php
class mysql
{
	var $link;
	
	var $db;
	var $prefix;
	
	var $count;
	
	var $error_info;
	var $sql;
	
	var $query = array();
	
	var $last_sql = "";

	var $logfile  = '';
    /**
     * Parameter must be an array, defined as:
     *  server['host']
     *  server['port']
     *  server['username']
     *  server['password']
     *  server['charset']
     */
	function __construct($server)
	{
		$server['port']		= isset($server['port'])?$server['port']:'3306';
		$server['charset']	= $server['charset']?$server['charset']:'utf8';

		$this->logfile  = isset($server['logfile'])?$server['logfile']:'php://output';
		$this->db		= $server;
		$this->prefix	= $server['prefix'];
		$this->count 	= 0;
	}
	
	function setLogfile($logfile='php://output')
	{
		$this->logfile = $logfile;
	}

	function connect()
	{
		$this->link = mysql_connect(
			$this->db['host'].':'.$this->db['port'],
			$this->db['username'],
			$this->db['password']);

		if(!$this->link)
		{
			$this->show_error("Connect failed!");
		}
		else
        {
		    //$this->select_db($this->db['database']);
		    $this->query("SET NAMES '".$this->db['charset']."';");
        }
	}
	
	//mysql_select_db
	function select_db($database)
	{
		$result = mysql_select_db($database,$this->link);

		if(!$result)
		{
			$this->show_error("Can't select database!");
		}
	}
	
    function affected_rows()
    {
        if($this->link)
            return mysql_affected_rows($this->link);
        else
            return false;
    }

	//mysql_query
	function query($sql)
	{
		$this->count ++;

		if(!$this->link)
		{
			$this->connect();
		}

		$this->sql = $sql;

		$this->select_db($this->db['database']);
        $this->last_sql = $sql;
		$result = mysql_query($sql,$this->link);

		if(!$result)
		{
			$this->show_error("Mysql query failed!");
		}else{
			return $result;
		}
	}
	
	//mysql_insert_id
	function insert_id()
	{
		return mysql_insert_id($this->link);
	}
	
	//get a value from result of the sql for special field
	function fetch_value($sql,$fieldname)
	{
		$list 		= $this->query($sql);
		$list_array = $this->fetch_array($list);
		return $list_array[$fieldname];
	}
	
	function fetch_one_value($sql)
	{
		$list 		= $this->query($sql);
		$list_array = $this->fetch_array($list);
		return $list_array[0];
	}	
	
	//mysql_num_rows
	function num_rows($query)
	{
		return mysql_num_rows($query);
	}
	
	//get the array from a sql
	function fetch_one_array($sql)
	{
		$list 		= $this->query($sql);
		$list_array = $this->fetch_array($list);
		return $list_array;
	}
	
	function fetch_one_assoc($sql)
	{
		$list 		= $this->query($sql);
		$list_array = $this->fetch_assoc($list);
		return $list_array;
	}	
	
	//mysql_fetch_array
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

	//mysql_fetch_array
	function fetch_all_assoc($sql,$max=0)
	{
		if(is_string($sql))	
			$query = $this->query($sql);
		else
			$query = $sql;

		while($list_item = $this->fetch_assoc($query))
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

	//mysql_fetch_array
	function fetch_array($query)
	{
		return mysql_fetch_array($query);
	}
	
	//mysql_fetch_assoc
	function fetch_assoc($query)
	{
		return mysql_fetch_assoc($query);
	}

	//mysql_fetch_row
	function fetch_row($query)
	{
		return mysql_fetch_row($query);
	}
	
	//get the number id and description of error
	function get_error()
	{
		if($this->link)
		{
            $error['sql']           = $this->last_sql;
			$error['number']		= mysql_errno($this->link);
			$error['description']	= mysql_error($this->link);
		}
		else 
		{
            $error['sql']           = $this->last_sql;
			$error['number']		= mysql_errno();
			$error['description']	= mysql_error();			
		}
		return $error;
	}	

	//display the error information to client browsers
	function show_error($title)
	{
		$content = "--------------------\n";
		$content.= $title."\n";
		$content.= $this->link?mysql_error($this->link):mysql_error();
		$content.= "\n";
		$content.= "$this->last_sql\n";

		error_log($content, 3, $this->logfile);

		return $content;
	}
		
		//mysql_fetch_object
	function fetch_all_object($sql,$max=0)
	{
		$query = $this->query($sql);
		while($list_item = $this->fetch_object($query))
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


	//mysql_fetch_object
	function fetch_object($query)
	{
		return mysql_fetch_object($query);
	}

} // end class
		
