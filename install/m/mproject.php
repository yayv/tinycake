<?php 

class mproject extends model
{
    var $_dirs = array(
            '/m/',
            '/v/',
            '/v/default/',
            '/v/default/css/',
            '/v/default/image/',
            '/v/_run/',
            '/c/',
            '/configs/',
            '/logs/',
            '/data/',        
		);

    var $_files = array(
        # target => source
        '/.htaccess'               => '../cake/templates/htaccess.template',
        '/index.php'               => '../cake/templates/index.php.template',
        #$t = strtr($template, array('{$name}'=>'defaultcontroller'));
        '/c/defaultcontroller.php' => '../cake/templates/controller.php.template',
        '/configs/cfg.default.php' => '../cake/templates/cfg.default.php.template',
        '/configs/controller_map.php' => '../cake/templates/controller_map.php.template',
        '/v/default/index.tpl.html'=> '../cake/templates/index.tpl.html.template',
        '/data/todo.txt'           => '/cake/templates/todo.txt.template',
        '/data/history.txt'        => '/cake/templates/history.txt.template',
        '/m/mmenu.php'             => '../cake/templates/mmenu.php.template',
        );

	// SOME PRIVATE DATA
	var $_path;
	var $_keyname;
	var $_name;
	var $_url;

	function setProject($proj)
	{
		$this->_path 	= $proj['path'];
		$this->_keyname	= $proj['keyname'];
		$this->_name	= $proj['name'];
		$this->_url		= $proj['url'];

		unset($this->loglist);
	}

	function getLogList()
	{
		if(isset($this->_loglist)) 
			return $this->_loglist;

		$path = $this->_path. '/logs/';
		$d = dir($path);

		$patterns = array(
			'txt' => '/crumbs\.(\d{4}-\d{2}-\d{2})\.txt/',
			'php' => '/parsedcrumbs\.(\d{4}-\d{2}-\d{2})\.php/',
		);

		$loglist = array();
		while (false !== ($entry = $d->read())) 
		{
			if(preg_match($patterns['txt'],$entry, $date))
			{
				$loglist[$date[1]]['txt']  = 1;
				$loglist[$date[1]]['date'] = $date[1];
			}
			else if(preg_match($patterns['txt'],$entry, $date))
			{
				$loglist[$date[1]]['php']  = 1;
				$loglist[$date[1]]['date'] = $date[1];
			}

		}

		$d->close();

		$this->_loglist = $loglist;
		return $loglist;
	}

    function createDirectories($home, $dirs)
    {
        $ret = true;
        if(!is_dir($home))
            $ret = mkdir($home);
        else
            $ret = is_writable($home);

        if(!$ret)
        {
            $this->pushError(array('home'=>$home), '不能创建根目录:'.$home);
            return false;
        }

        $ret = true;
        foreach($dirs as $k=>$v)
            $ret = $ret && mkdir($v);

        return $ret;
    }

    function checkDirectoriesExists($home)
    {
        $tocreate = array();

        foreach($this->_dirs as $k=>$v)
            if(!is_dir($home.$v))
            {
                $tocreate[] = $home.$v;
            }

        return $tocreate;
    }

    function checkDirectoriesMode($dirs)
    {
        $tochmod = array();

        foreach($dirs as $k=>$v)
            if(!is_writable($v))
                $tochmod[] = $v;

        return $tochmod;
    }

    function checkFilesMode($home)
    {
        $togenerate = array();

        foreach($this->_files as $k=>$v)
            if(!is_file($home.$k))
            {
                $togenerate[$k] = $v;
            }

        return $togenerate;

    }    

    function createFiles($home, $files)
    {
        $templatedir = $this->_config;

        // 1. TODO: 用实际的参数替换模板中的变量, 构造参数列表
        $template_values = array('{$name}'=>'defaultcontroller');

        // 2. 生成文件
        foreach($files as $k=>$v)
        {
            $template = strtr(file_get_contents($v), $template_values);
            $ret = file_put_contents($home.$k, $template);
        }
         
        // 3. create md5 for .htaccess and index.php
    }
}

