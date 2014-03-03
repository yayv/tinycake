<?php 

class mprojectinfo extends model
{
    var $_path;
    var $_url;
    var $_keyname;
    var $_showname;

    function __construct()
    {
    }

    function __destruct()
    {
    }

    public function initProj($path, $url, $keyname, $showname)
    {
    	$this->_path = $path;
    	$this->_url  = $url;
    	$this->_keyname  = $keyname;
    	$this->_showname = $showname;
    }

	function findTodoList()
	{
		$this->todo = array();
		$this->browseProjectTree("testcall");

		return $this->todo;
	}

	function testcall($filename)
	{
		// echo "FILE:$filename<br/><pre>";
		$f = file($filename);
		foreach($f as $k=>$v)
		{
			$ret = preg_match('/ TODO:(.*)/',$v,$matches);
			if($ret)
			{
				if(!array_key_exists($filename, $this->todo))
					$this->todo[$filename] = array();

				$this->todo[$filename][] = $k.':'.$matches[1];
			}
		}

		return ;
	}

    function browseProjectTree($callback, $home='')
    {
    	$escapedirs = array('.','..','.svn','.git','logs','_run','.DS_Store');

    	// 遍历全部目录树，按照要求调用函数
    	if($home=='') $home=rtrim($this->_path,'/');

		$d = dir($home);
		if($d)
		{
			while (false !== ($entry = $d->read())) 
			{
				if(in_array($entry,$escapedirs)) continue;

				if(is_file($home.'/'.$entry))
					$this->$callback($home.'/'.$entry);
				else
					$this->browseProjectTree($callback,$home.'/'.$entry);
			}
			$d->close();			
		}
		else
		{
			print_r($d);
			die('dfd');
		}
    }
}

