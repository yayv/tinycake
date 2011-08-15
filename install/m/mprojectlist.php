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

    function loadList()
    {   
        if(is_file($this->_listfile))
            $this->_projs = unserialize(file_get_contents($this->_listfile));
    }

    function saveList()
    {
        $ret = file_put_contents($this->_listfile, serialize($this->_projs));
    }
    
    function addProj($projname, $projpath)
    {
        $this->_projs[$projname] = $projpath;
    }
    
    function getList()
    {
        return $this->_projs;
    }

    function rmProj($projname)
    {
        unset($this->_projs[$projname]);
    }

}

