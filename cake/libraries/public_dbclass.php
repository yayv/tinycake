<?php
/*
* 一个对mysql的简单封装
* 一个实例只能同时做一个select/update/insert操作
* 要同时对多个表格或者纪录集操作必须创建多个实例
* 命名变量名和函数名规则不统一
* 没有翻页功能
* 获得最后添加的ID的方法(如果需要则要求添加)
*
*/
class DB_Sql {
	/* public: connection parameters */
	var $Host = "";
	var $Database = "";
	var $User = "";
	var $Password = "";
	var $LinkName = ""; ## 对于同一个数据库服务器,可以设置相同联结名
	var $Charset  = "UTF8"; ## set names使用
	
	/* public: configuration parameters */
	var $Auto_Free = 0; ## Set to 1 for automatic mysql_free_result()
	## 类是否需要注销纪录集

    // 报错　"yes" (halt with message),
	// 不报错误信息　"no" (ignore errors quietly),
	// 不报错误信息,中断程序　?report" (ignore errror,but spit a warning)
	var $Halt_On_Error = "no"; 
	
	var $Seq_Table = "db_sequence"; //序列表

	/* public: result array and current row number */
	var $Record = array(); //一条纪录信息
	var $Row; // 当前行号
	var $AbsolutePage = 1; // 当前页码
	var $PageSize = 0; // 一页行数
	var $PageCount = 0; // 总共页数
	var $RecordCount = 0; // 总行数
	
	/* public: current error number and error text */
	var $Errno = 0; //错误编号
	var $Error = ""; //错误信息
	
	var $type = "mysql"; //数据库类型

	/* private: link and query handles */
	var $Query_ID = 0; //查询结果句柄
	
    // construct for php4
    function DB_Sql($Host, $Database, $User, $Password, $LinkName, $Charset='UTF8')
    {
        $this->Host     = $Host; //"dbserver_zoldb";
        $this->Database = $Database; //"zoldb";
        $this->User     = $User; //"root";
        $this->Password = $Password;
        $this->LinkName = $LinkName; //"conn_product";
        $this->Charset = $Charset; //"conn_product";
    }
  
  
    function __construct($Host, $Database, $User, $Password, $LinkName, $Charset='UTF8')
    {
        // 注: php5兼容php4的构造函数模式，只有当解释器找不到 __construct构造函数的时候，php才会寻找php4风格的构造函数
        $this->DB_Sql($Host, $Database, $User, $Password, $LinkName, $Charset);
    }
  
    function __destruct()
    {
        $this->close_link();      
    }
  
	
	function query_id() { //返回查询结果,得到结果句柄
		return $this->Query_ID;
	}
	
	/* public: connection management */
	// 创建连结
	function connect($Database = "", $Host = "", $User = "", $Password = "", $linkName="") {
		/* Handle defaults */
		if ("" == $Database)
			$Database = $this->Database;
		if ("" == $Host)
			$Host = $this->Host;
		if ("" == $User)
			$User = $this->User;
		if ("" == $Password)
			$Password = $this->Password;
		if ("" == $linkName)
			$LinkName = $this->LinkName;
		global $$LinkName;
		/* establish connection, select database */
		if ($$LinkName) {
			return 1;
		} else {
			$$LinkName=@mysql_connect($Host, $User, $Password);
			if ($$LinkName) {
			  @mysql_query("set names $this->Charset");
				return 1;
			} else {
				$this->halt("connect($Host, $User, \$Password) failed.");
				return 0;
			}
		}
	}
	
	/* public: discard the query result */
	// 释放纪录集内存
	function free() {
		@mysql_free_result($this->Query_ID);
		$this->Query_ID = 0;
	}
	
	/* private: 初始化与页面数相关的信息, writed by Ge Chuanqing*/
	function init_pagesize() {
		$this->AbsolutePage = 1;
		$this->PageSize     = 0;
		$this->PageCount    = 0;
		$this->RecordCount  = 0;
	}
	
	/* private: parse a query, writed by Ge Chuanqing*/
	function parse_query_string($Query_String) {
		$LinkName=$this->LinkName;
		global $$LinkName;
		if (($this->PageSize>0) && ($this->AbsolutePage>0) && eregi("select ",$Query_String)) {
			$strsql=ereg_replace ("select .+ from", "select count(*) as sumrows from", $Query_String);
			$rec=@mysql_db_query($this->Database,$strsql,$$LinkName);
			if (!rec) {
				$this->Errno = mysql_errno();
				$this->Error = mysql_error();
				$this->halt("Invalid SQL(count): ".$Query_String);
				$this->init_pagesize();
				return 0;
			} else {
				$this->RecordCount=mysql_result($rec,0,"sumrows");
				$this->PageCount=ceil($this->RecordCount/$this->PageSize);
				return "$Query_String limit ".
				($this->AbsolutePage-1)*$this->PageSize.",".$this->PageSize;
			}
		} else {
			$this->init_pagesize();
			return 0;
		}
	}
	
	
	/* public: perform a query */
	// 执行SQL
	function query($Query_String) {
		$LinkName=$this->LinkName;
		global $$LinkName;
		/* No empty queries, please, since PHP4 chokes on them. */
		if ($Query_String == "")
			/* The empty query string is passed on from the constructor,
			* when calling the class without a query, e.g. in situations
			* like these: '$db = new DB_Sql_Subclass;'
			*/
			return 0;
			
		if ($this->connect()==0) {//如果链接失败
			return 0; /* we already complained in connect() about that. */
		};
		
		# New query, discard previous result.
		if ($this->Query_ID) {
			$this->free();
		}
		
		# 如果没有设置PageSize,就应该不能设置AbsolutePage
		if (($this->PageSize<1) && ($this->AbsolutePage>1)) {
			echo "<b>";
			echo "每一页的行数(".$this->PageSize.")，";
			echo "或者页码(".$this->AbsolutePage.")，";
			echo "设置错误!";
			echo "</b><br>";
			return 0;
		}

		if ($this->parse_query_string($Query_String)) {
			$Query_String=$this->parse_query_string($Query_String);
		}
		
		$this->Query_ID = @mysql_db_query($this->Database,$Query_String,$$LinkName);
		$this->Row = 0;
		$this->Errno = mysql_errno();
		$this->Error = mysql_error();
		if (!$this->Query_ID) {
			$this->init_pagesize();
			$this->halt("Invalid SQL: ".$Query_String);
		} else {
			if ($this->RecordCount <= 0) {
				$this->RecordCount=@mysql_num_rows($this->Query_ID);
			}
		}
		# Will return nada if it fails. That's fine.
		return $this->Query_ID;
	}
	
	/* public: walk result set */
	/* 获取当前纪录,如果为空则清除纪录集 */
	// 参数: 无
	// 返回: 成功或者失败
	function next_record() {
		if (!$this->Query_ID) {
			$this->halt("next_record called with no query pending.");
			return 0;
		}
		$this->Record = @mysql_fetch_array($this->Query_ID);
		$this->Row += 1;
		$this->Errno = mysql_errno();
		$this->Error = mysql_error();
		$stat = is_array($this->Record);
		if (!$stat && $this->Auto_Free) {//如果没有得到结果集
			$this->free();
		}
		return $stat;
	}
	
	/* public: position in result set */
	// 设置纪录集指针位置$pos
	// 当超过范围时,则报错,并且自动返回到最后一行，并且返回0
	//成功返回1，并且把纪录集指针定位到$pos
	function seek($pos = 0) {
		$status = @mysql_data_seek($this->Query_ID,$pos);
		if ($status)
			$this->Row = $pos;
		else {
			$this->halt("seek($pos) failed: result has ".$this->num_rows()." rows");
			/* half assed attempt to save the day,
			* but do not consider this documented or even
			* desireable behaviour.
			*/
			@mysql_data_seek($this->Query_ID, $this->num_rows());
			$this->Row = $this->num_rows();
			return 0;
		}
		return 1;
	}
	
	/* public: table locking */
	// 将一个表以某种方式锁死
	// $table可以是数组,如:array("read"=>"table1","table2","table3")
	// 设置为读锁死的表,最多只能有一个
	function lock($table, $mode="write") {
		$this->connect();
		$query="lock tables ";
		if (is_array($table)) {
			while (list($key,$value)=each($table)) {
				if ($key=="read" && $key!=0) { //测试表明,无法读锁死表
					$query.="$value read, ";
				} else {
					$query.="$value $mode, ";
				}
			}
			$query=substr($query,0,-2); //删除", "
		} else {
			$query.="$table $mode";
		}
		$res = @mysql_db_query($this->Database,$query,$$LinkName);
		if (!$res) {
			$this->halt("lock($table, $mode) failed.");
			return 0;
		}
		return $res;
	}
	
	//解锁
	function unlock() {
		$this->connect();
		$res = @mysql_query("unlock tables");
		if (!$res) {
			$this->halt("unlock() failed.");
			return 0;
		}
		return $res;
	}
	
	/* public: evaluate the result (size, width) */
	// 最后一次SQL操作,影响的数据库行数,比如对成功执行了delete,insert 等，select则和num_rows()一致
	// 如果是select,则返回0
	// 如果没有where字句,返回也是0
	function affected_rows() {
		$LinkName=$this->LinkName;
		global $$LinkName;
		return @mysql_affected_rows($$LinkName);
	}
	
	//当前结果集的行数
	function num_rows() {
		return $this->RecordCount;
	}
	//取得返回结果集的列数（也就是字段的数目）
	function num_fields() {
		return @mysql_num_fields($this->Query_ID);
	}
	
	//返回最后插入的记录ID
	function last_insert_id(){
		$LinkName=$this->LinkName;
		global $$LinkName;
		return @mysql_insert_id($$LinkName);
	}
	
	/* public: shorthand notation */
	//当前纪录集的行数
	function nf() {
		return $this->num_rows();
	}
	
	//当前纪录集的行数
	function np() {
		print $this->num_rows();
	}
	
	//获取Record中的纪录
	function f($Name) {
		return $this->Record[$Name];
	}
	
	//获取Record中的纪录
	function p($Name) {
		print $this->Record[$Name];
	}
	
	//返回一个变量，用于结果只有一行一列的SQL //卓军辉添加
	//例：$var = $DB_Product->get_var("SELECT count(*) FROM users");
	function get_var($query){
		$this->query($query); //执行SQL
		$this->next_record(); //取一条记录
		if($this->Record && $this->Record[0])
		    return $this->Record[0]; //返回记录的第一列
		else
		    return false;
	}
	
	//返回包含一行记录的数组或对象(默认为数组)，用于结果只有一行的SQL //卓军辉添加
	//例：$user = $DB_Product->get_row("SELECT name,email FROM users WHERE id = 2");
	function get_row($query , $oMethod = 'A'){
		$this->query($query); //执行SQL
		//SQL异常处理
		if (!$this->Query_ID) {
			$this->halt("next_record called with no query pending.");
			return 0;
		}
		if($oMethod=='A'){ //返回数组
			if($t = @mysql_fetch_array($this->Query_ID)){
				$Results=$t;
				$this->Row += 1;
			}
		}else if($oMethod=='O'){ //返回对象
			if($t = @mysql_fetch_object($this->Query_ID)){
				$Results=$t;
				$this->Row += 1;
			}
		}else{
			$this->free();
			return false;
		}
		//如果没有得到结果集
		if($this->Row == 0){
			$this->free();
			return false;
		}
		return $Results;
	}
	
	//返回包含两行记录的二维数组或对象型数组(默认为数组) //卓军辉添加
	//例：$user = $DB_Product->get_row("SELECT name,email FROM users");
	function get_results($query , $oMethod = 'A'){
		$this->query($query); //执行SQL
		//SQL异常处理
		if (!$this->Query_ID) {
			$this->halt("next_record called with no query pending.");
			return 0;
		}
		if($oMethod=='A'){ //返回二维数组
			while($t = @mysql_fetch_array($this->Query_ID)){
				$Results[]=$t;
				$this->Row += 1;
			}
		}else if($oMethod=='O'){ //对象型数组
			while($t = @mysql_fetch_object($this->Query_ID)){
				$Results[]=$t;
				$this->Row += 1;
			}
		}else{
			$this->free();
			return false;
		}
		$this->Errno = mysql_errno();
		$this->Error = mysql_error();
		//如果没有得到结果集
		if($this->Row == 0){
			$this->free();
			return false;
		}
		return $Results;
	}
	
	/* public: sequence numbers, edit by Ge Chuanqing */
	//获取一个表格的序列号并且加一
	function nextid($seq_name) {
	    $LinkName=$this->LinkName;

	    global $$LinkName;

	    $this->connect();

	    if ($this->lock($this->Seq_Table)) {

	    /* get sequence number (locked) and increment */
	    //
	    $q = sprintf("select nextid from %s where seq_name = '%s'", $this->Seq_Table, $seq_name);

	    $id = @mysql_db_query($this->Database,$q, $$LinkName);

	    $res = @mysql_fetch_array($id);

	    /* No current value, make one */
	    if (!is_array($res)) {
    	    $currentid = 0;

	        //add by ge, 注意：在加锁前，自动解开以前的锁．
	        if ($this->lock("$seq_name")) {
	            $strsql="select max(id) as maxid from $seq_name";
	            $rec=@mysql_db_query($this->Database,$strsql,$$LinkName);
	            $currentid=@mysql_result($rec,0,"maxid");
	        } 
            else 
            {
	            $this->halt("cannot lock ".$seq_name);
	        }

	        if ($this->lock($this->Seq_Table))
	        {
	            $q = sprintf("insert into %s values('%s', %s)",
	            $this->Seq_Table,
	            $seq_name,
	            $currentid);
	            $id = @mysql_db_query($this->Database,$q,$$LinkName);
	        } 
            else 
            {
	            $this->halt("cannot lock ".$this->Seq_Table);
	        }
	    //add end
	    } 
        else 
        {
	        $currentid = $res["nextid"];
	    }

	    $nextid = $currentid + 1;

	    $q = sprintf("update %s set nextid = '%s' where seq_name = '%s'",
	                $this->Seq_Table,
	                $nextid,
	                $seq_name);

	    $id = @mysql_db_query($this->Database,$q,$$LinkName);

	    $this->unlock();
	    } 
        else 
        {
	        $this->halt("cannot lock ".$this->Seq_Table." - has it been created?");
	        return 0;
	    }
	    return $nextid;
	}
	
	
	/* public: return table metadata */
	//获取某个表结构信息
	function metadata($table='',$full=false) {
	$count = 0;
	$id = 0;
	$res = array();
	/*
	* Due to compatibility problems with Table we changed the behavior
	* of metadata();
	* depending on $full, metadata returns the following values:
	*
	* - full is false (default):
	* $result[]:
	* [0]["table"] table name
	* [0]["name"] field name
	* [0]["type"] field type
	* [0]["len"] field length
	* [0]["flags"] field flags
	*
	* - full is true
	* $result[]:
	* ["num_fields"] number of metadata records
	* [0]["table"] table name
	* [0]["name"] field name
	* [0]["type"] field type
	* [0]["len"] field length
	* [0]["flags"] field flags
	* ["meta"][field name] index of field named "field name"
	* The last one is used, if you have a field name, but no index.
	* Test: if (isset($result['meta']['myfield'])) { ...
	*/
	// if no $table specified, assume that we are working with a query
	// result
	if ($table) {
	$this->connect();
	$id = @mysql_list_fields($this->Database, $table);
	if (!$id)
	$this->halt("Metadata query failed.");
	} else {
	$id = $this->Query_ID;
	if (!$id)
	$this->halt("No query specified.");
	}
	$count = @mysql_num_fields($id);
	// made this IF due to performance (one if is faster than $count if's)
	if (!$full) {
	for ($i=0; $i<$count; $i++) {
	$res[$i]["table"] = @mysql_field_table ($id, $i);
	$res[$i]["name"] = @mysql_field_name ($id, $i);
	$res[$i]["type"] = @mysql_field_type ($id, $i);
	$res[$i]["len"] = @mysql_field_len ($id, $i);
	$res[$i]["flags"] = @mysql_field_flags ($id, $i);
	}
	} else { // full
	$res["num_fields"]= $count;
	for ($i=0; $i<$count; $i++) {
	$res[$i]["table"] = @mysql_field_table ($id, $i);
	$res[$i]["name"] = @mysql_field_name ($id, $i);
	$res[$i]["type"] = @mysql_field_type ($id, $i);
	$res[$i]["len"] = @mysql_field_len ($id, $i);
	$res[$i]["flags"] = @mysql_field_flags ($id, $i);
	$res["meta"][$res[$i]["name"]] = $i;
	}
	}
	// free the result only if we were called on a table
	if ($table) @mysql_free_result($id);
	return $res;
	}

	//显示所有表名称
	function table_names() {
		$this->query("SHOW TABLES");
		$i=0;
		while ($info=mysql_fetch_row($this->Query_ID)){
			$return[$i]["table_name"]= $info[0];
			$return[$i]["tablespace_name"]=$this->Database;
			$return[$i]["database"]=$this->Database;
			$i++;
		}
		return $return;
	}
	
	//实例注销前需要释放相应的内存空间
	function close() {
		free();
	}
	
	//关闭数据库联结
	function close_link() {
		$LinkName=$this->LinkName;
		global $$LinkName;
		if ($mylink) {
			if (mysql_close($$LinkName)) {
				$mylink=false;
				return true;
			}else{
				return false;
			}
		}else{
			return true;
		}
	}
	
    //得到数据库db_name下的所有表的名字的字符串
	function get_db_tables(){
		$db_name=$this->Database;
		$results=mysql_list_tables($db_name);
		$table_num=mysql_affected_rows();
		$table_name="";
		$table_str="";
		for($i=0;$i<$table_num;$i++){
			$table_name=mysql_tablename($results,$i);
			$table_str.=$table_name.",";
		}
		if(trim($table_str)!=""){
			$table_str=",".$table_str;
		}
		return $table_str;
	}



	/* private: error handling */
	//错误处理方法
	function halt($msg) {
		$this->Error = @mysql_error($$LinkName);
		$this->Errno = @mysql_errno($$LinkName);
		if ($this->Halt_On_Error == "no")
		{
		    include_once('config.php'); // 补丁，写法很恶劣
		    if(is_file(LOG_PATH.'/applog/dberror.log'))
		    {
		    $flog = fopen(LOG_PATH.'/applog/dberror.log','a');
		    fwrite($flog, $msg);
		    fwrite($flog, '<br/><hr>');
		    fclose($flog);
		    }
			return;
		}
		$this->haltmsg($msg);
		if ($this->Halt_On_Error != "report")
		echo "数据库查询错误！！！";
			//die("Session halted.");
	}
	
	//显示错误信息
	function haltmsg($msg) {
		printf("</td></tr></table><b>Database error:</b> %s<br>\n", $msg);
		printf("<b>MySQL Error</b>: %s (%s)<br>\n",
		$this->Errno,
		$this->Error);
	}

}//end of class


