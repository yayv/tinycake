<?php 

class mproject extends model
{
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

    function checkDirectoriesExists($dirs)
    {
        $tocreate = array();

        foreach($dirs as $k=>$v)
            if(!is_dir($v))
                $tocreate[] = $v;
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

    function createFiles($home, $url)
    {
        $templatedir = $this->_config;

        // 1. .htaccess
        $ret = file_put_contents($home.'/.htaccess', "RewriteEngine On\nRewriteBase /\n");

        // 2. index.php

        $ret = file_put_contents($home.'/index.php', '<?php echo "Hello,World!";');

        // 3. defaultcontroller.php
        // 4. cfg.default.php 
        // 5. index.tpl.html
        // 6. main.css

        // 7. create md5 for .htaccess and index.php
    }


}
