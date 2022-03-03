<?php

spl_autoload_register(function($class){
    static $dir;
    $dir = __DIR__ . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR ;
    $file = $dir . str_replace ( '\\' , DIRECTORY_SEPARATOR , $class ) . '.php' ;
    $class = realpath($file) ;
    if( ! $class || ! is_readable ( $class ) )
        throw new exception('없는 클래스 : '.$file);
    include_once $class ;
});



var_dump('-------------------------- 멀티 프로세스 테스트 시작 --------------------------');
$multiworks = [
    function(){
        var_dump('1 시작');
        sleep(rand(3,5));
        var_dump('1 끝');
    },
    function(){
        var_dump('2 시작');
        sleep(rand(3,5));
        var_dump('2 끝');
    },
    function(){
        var_dump('3 시작');
        sleep(rand(3,5));
        var_dump('3 끝');
    },
    function(){
        var_dump('4 시작');
        sleep(rand(3,5));
        var_dump('4 끝');
    },
];
try {
    CLI\Process::run($multiworks);
} catch (\Throwable $th) {
    var_dump($th->getMessage());
}

var_dump('-------------------------- 멀티 프로세스 테스트 끝 --------------------------');


# 테스트용 배열
$arr = [1=>[1,2,3=>[3,4,5,6=>[7,8,9=>[11,22,33]]]]];

var_dump('-------------------------- 배열 1차원으로 만들기 --------------------------');
$flat = Arr::flat($arr);
var_dump( $flat);

var_dump('-------------------------- 배열 순회 시작 --------------------------');
Arr::loop($arr, function($k,$v){
    var_dump($k,$v);
});
var_dump('-------------------------- 배열 순회 끝 --------------------------');

var_dump('-------------------------- 배열 깊이 얻기 --------------------------');
var_dump(Arr::depth($arr));


var_dump('-------------------------- IP 서브넷 마스크 범위 얻기 --------------------------');
var_dump(IP::range('123.123.123.123/8'));
var_dump(IP::range('123.123.123.123/16'));
var_dump(IP::range('123.123.123.123/24'));
var_dump(IP::range('123.123.123.123/32'));

var_dump('-------------------------- 숫자 계산 --------------------------');

var_dump(Calc::human2Bytes('123021G'));
var_dump(Calc::bytes2Human(12345487451));

var_dump(Calc::human2Bytes('123021GB',1024));
var_dump(Calc::bytes2Human(12345487451,1024));

$ap = Calc::APint(100,5);
var_dump($ap,array_sum($ap));

var_dump(Calc::gcd(-5885,11877,535));
var_dump(Calc::ratio(-5885,11877,535));


var_dump('-------------------------- 문자열 --------------------------');

var_dump(Str::random());
var_dump(Str::random(rand(5,30)));


var_dump(Str::mix(Str::random()));
var_dump(Str::mix('aaaaabbbbbbcccc'));

var_dump(Str::masking('가나다'));
var_dump(Str::masking('010-1234-1234'));
var_dump(Str::masking('가나다라마바사'));
var_dump(Str::masking('가나다라마바사',7));


var_dump('-------------------------- URL --------------------------');
try {
    var_dump(URL::domain());
} catch (\Throwable $th) {
    var_dump($th->getMessage());
}

var_dump(URL::parse('https://ssub.sub.test.co.kr'));

