<?php
try {
    $phar = new Phar('tinycake.phar', 0, 'tinycake.phar');

    $pi = pathInfo(__FILE__);
    $treeroot = realpath($pi['dirname']."/../");

    //没生效，如果生效就可以不用下面的rename了
    //$phar->buildFromDirectory($treeroot,"/^(?!(.git.*))(.*)$/");

    rename($treeroot."/.git", $treeroot."/../.git");

    $phar->buildFromDirectory($treeroot,"/^(?!(.git.*))(.*)$/");

    $phar->setDefaultStub('commandLine/index.php', 'siteManager/index.php');
    #$phar->setStub("index.php");

    // finish for move .git back
    $phar = null;

    rename($treeroot."/../.git", $treeroot."/.git");

} catch (Exception $e) {
    // handle errors
    echo "Got Some Error\n";
    print_r($e);
    $pi = pathInfo(__FILE__);
    $treeroot = realpath($pi['dirname']."/../");
    rename($treeroot."/../.git", $treeroot."/.git");
}

