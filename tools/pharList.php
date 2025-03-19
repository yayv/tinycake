<?php
$now = time();
$phar = new Phar('tinycake.phar', 0, 'tinycake.phar');
$phar->extractTo("tinycake_$now");

