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

        // TODO: 用实际的参数替换模板中的变量

        // 1. .htaccess
        $template = file_get_contents('../cake/templates/htaccess.template');
        $ret = file_put_contents($home.'/.htaccess', $template);

        // 2. index.php
        $template = file_get_contents('../cake/templates/index.php.template');
        $ret = file_put_contents($home.'/index.php', $template);

        // 3. defaultcontroller.php
        // 2. index.php
        $template = file_get_contents('../cake/templates/controller.php.template');
        $t = strtr($template, array('{$name}'=>'defaultcontroller'));
        $ret = file_put_contents($home.'/c/defaultcontroller.php', $t);

        // 4. cfg.default.php 
        $template = file_get_contents('../cake/templates/cfg.default.php.template');
        $ret = file_put_contents($home.'/configs/cfg.default.php', $template);

        // 5. controller_map.php 
        $template = file_get_contents('../cake/templates/controller_map.php.template');
        $ret = file_put_contents($home.'/configs/controller_map.php', $template);

        // 6. index.tpl.html
        $template = file_get_contents('../cake/templates/index.tpl.html.template');
        $ret = file_put_contents($home.'/v/default/index.tpl.html', $template);

        // 7. main.css
        

        // 8. create md5 for .htaccess and index.php
    }


}
