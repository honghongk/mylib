<?php


include __DIR__.'/index.php';


use Filesystem\Zip;

var_dump('테스트');

$src = __DIR__.'/testdir';
$dst = __DIR__.'/zipres.zip';


$res = Zip::compress($src,$dst);
var_dump($res);