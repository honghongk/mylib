<?php



$start = [
    'memory' => memory_get_peak_usage(),
    'time' => microtime(true),
];

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('memory_limit','512M');



// 프로젝트경로
$dir = dirname(__DIR__);

// 오토로더
include_once realpath($dir.'/php/autoloader.php');

// config 세팅
new Config($dir.'/config');



// 에러 핸들러 세팅
set_error_handler(['Handler','error'], E_ALL);

// 종료 핸들러 세팅
register_shutdown_function(function($start){

    echo str_repeat('-',80)."\n";
    $check = 'memory_peak: '.memory_get_peak_usage()."\n"
        . 'memory_diff: '.(memory_get_peak_usage() - $start['memory'])."\n"
        . 'time: '. (microtime(true) - $start['time']);

        var_dump($check);

},$start);


