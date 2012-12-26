<?php 

class mprojectlist extends model
{
    var $_listfile;
    var $_projs;

    function __construct()
    {
        // NOTE: 析构函数调用时，就不存在当前路径了，所以使用相对路径会出错。
        // 所以在进行构造时，把相对路径转化为一个绝对路径会变得安全很多
        $this->_listfile = realpath('./').'/data/projectlist.dump';
        $this->loadList();
    }

	function __destruct()
	{
		$this->saveList();
	}

    function loadList()
    {   
        if(is_file($this->_listfile))
            $this->_projs = unserialize(file_get_contents($this->_listfile));
		else
			$this->_projs = array();
    }

    function saveList()
    {
        $ret = file_put_contents($this->_listfile, serialize($this->_projs));
    }
    
    function addProj($projshowname, $projname, $projpath, $projurl)
    {
        $this->_projs[$projname]['showname'] = $projshowname;
        $this->_projs[$projname]['path'] = $projpath;
        $this->_projs[$projname]['keyname'] = $projname;
        $this->_projs[$projname]['name'] = $projname;
        $this->_projs[$projname]['url'] = $projurl;
    }
    
    function getList()
    {
        return $this->_projs;
    }

    function rmProj($projname)
    {
        unset($this->_projs[$projname]);
    }

	function getProject($name)
	{
		if(isset($this->_projs[$name]))
			return $this->_projs[$name];
		else
			return false;
	}
}

