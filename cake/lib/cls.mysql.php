<?php
//Last Modified By Thatday
//2010.01.21

class mysql
{
	var $link;
	
	var $db;
	var $prefix;
	
	var $count;
	
	var $error_info;
	var $sql;
	
	var $query = array();
	
	function __construct($server)
	{
		$server['port']		= $server['port']?$server['port']:'3306';
		$server['charset']	= $server['charset']?$server['charset']:'utf8';
		
		$this->db		= $server;
		$this->prefix	= $server['prefix'];
	}
	
	function connect()
	{
		$this->link = @mysql_connect(
			$this->db['host'].':'.$this->db['port'],
			$this->db['username'],
			$this->db['password']);
		
		if(!$this->link)
		{
			$this->show_error("Connect failed!");
		}
		
		//$this->select_db($this->db['database']);
		$this->query("SET NAMES '".$this->db['charset']."';");
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
	
	//mysql_query
	function query($sql)
	{
		//$this->querys[] = $sql;
		$this->count ++;
		#debug_print_backtrace();
		if(!$this->link)
		{
			$this->connect();
		}
		
		$this->sql = $sql;

		$this->select_db($this->db['database']);
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
	
	//delete
	function delete($table_name,$where,$returnids = false)
	{
		$table_name = str_replace('#',$this->db['prefix'],$table_name);
		
		$sql_where = " WHERE ".$where;
		
		if($returnids)
		{
			$sql		= "SELECT ".$returnids." FROM ".$table_name.$sql_where;
			$query		= $this->query($sql);
			
			while($list_item = $this->fetch_array($query))
			{
				$all_array[] = $list_item[0];
			}
			
			if(is_array($all_array))
			{
				$strIds	= implode(',',$all_array);
			}
			
			$sql		= "DELETE FROM ".$table_name.$sql_where;
			return $strIds;
		}
		else 
		{
			$sql		= "DELETE FROM ".$table_name.$sql_where;
			return $this->query($sql);
		}

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
	function fetch_all_array($query,$max=0)
	{
		
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
		$query = $this->query($sql);
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
	
	//Add By Thatday 2007-02-24
	function insert_array($table_name,$arr_item,$is_ignore = false)
	{
		
		$table_name = str_replace('#',$this->db['prefix'],$table_name);
		
		//重组sql语句
		foreach($arr_item as $key=>$value)
		{
			$sql_item[] = $key."='".addslashes($value)."'";
		}
		
		$sql_set = implode($sql_item,",");
		
		if($is_ignore)
		{
			$sql_ignore = ' IGNORE ';
		}
		
		$sql	= "INSERT ".$sql_ignore." INTO ".$table_name." SET ".$sql_set;
		
		$query	= $this->query($sql);
		
		$id		= $this->insert_id();
		
		//file_put_contents('c:/aa.sql',$sql);
		
		return $id;
	}
	
	function replace_array($table_name,$arr_item)
	{
		$table_name = str_replace('#',$this->db['prefix'],$table_name);

		//重组sql语句
		foreach($arr_item as $key=>$value)
		{
			$sql_item[] = $key."='".addslashes($value)."'";
		}
		
		$sql_set = implode($sql_item,",");
		$sql	= "REPLACE INTO ".$table_name." SET ".$sql_set;
		
		return $this->query($sql);
	}		

	//Add By Thatday 2007-02-24
	function update_array($table_name,$arr_item,$where)
	{
		
		$table_name = str_replace('#',$this->db['prefix'],$table_name);
		
		//重组sql语句
		foreach($arr_item as $key=>$value)
		{
			$sql_item[] = $key."='".addslashes($value)."'";
		}
		
		if($where<>'')
		{
			$sql_where = " WHERE ".$where;
		}
		
		$sql_set 	= implode($sql_item,",");
		
		$sql		= "UPDATE ".$table_name." SET ".$sql_set." ".$sql_where;
		
		return $this->query($sql);
	
	}

	//get the number id and description of error
	function get_error()
	{
		if($this->link)
		{
			$error['number']		= mysql_errno($this->link);
			$error['description']	= mysql_error($this->link);
		}
		else 
		{
			$error['number']		= mysql_errno();
			$error['description']	= mysql_error();			
		}
		return $error;
	}	

	//display the error information to client browsers
	function show_error($title)
	{
		$this->error_info['info'] = $this->get_error();
		$this->error_info['title'] = $title;
				
		return $this->error_info;
	}
		

} // end class
		
