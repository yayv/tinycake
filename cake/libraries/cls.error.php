<?php

class error
{
	
	var $err_info = "";
	var $err_count = 0;
	
	function add($string){
		global $err_info,$err_count;
		
		$err_count ++;
		$err_info .= $err_count.". ".$string."<br>";
		
	}
	
	function show($title="请确认以下项目是否输入正确:")
	{
		
		$info =  "<table width=\"100%\" border=\"0\" cellpadding=\"3\" cellspacing=\"0\" class=\"tbl_error\">\r\n";
		$info .=  "		<tr bgcolor=\"#FFFF99\">\r\n";
		$info .= "		  <td width=\"65%\">".$this->title."</td>\r\n";
		$info .= "		  <td width=\"35%\" align=\"right\">".date("Y-m-d H:i:s",time())."</td>\r\n";
		$info .= "		</tr>\r\n";
		$info .= "		<tr>\r\n";
		$info .= "		  <td colspan=\"2\">\r\n";
		$info .= "			<table width=\"100%\" border=\"0\" cellspacing=\"5\">\r\n";
		$info .= "			<tr>\r\n";
		$info .= "			  <td><font color=#FF6600>".$this->err_info."</font></td>\r\n";
		$info .= "			</tr>\r\n";
		$info .= "			</table></td>\r\n";
		$info .= "	</tr>\r\n";
		$info .= "</table>\r\n";
				
	}
	
	function debug_array($title,$array,$exit=false)
	{	
		//查看数组的值
		echo   "<pre>"; 
		echo   $title."\r\n"; 
		print_r($array); 
		echo   "</pre>"; 
		
		if($exit) exit();
	}
	
	function debug($title,$value,$exit=false)
	{	
		//查看数组的值
		echo "调试信息：[$title]=[$value]<hr>";
		if($exit) exit();
	}
		
		

} // end class
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
?>

