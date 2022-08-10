<?php


include __DIR__.'/index.php';


use DB\Mysql\Dump;
use DB\Mysql\Mysql;


// mysql dump 테스트
$config = Config::get(['DB','Mysql']);
$config['db'] = 'module';

new Mysql($config);

Dump::all('testdump/testdump.sql');


exit;


foreach (['table','event','procedure','trigger'] as $v)
{
    echo str_repeat('-',100)."\n";
    var_dump($v);
    $vv = Dump::$v();
    var_dump($vv);
}


