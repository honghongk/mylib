<?php

spl_autoload_register(function($class){
    static $dir;
    $dir =  __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'php' . DIRECTORY_SEPARATOR ;
    $file = $dir . str_replace ( '\\' , DIRECTORY_SEPARATOR , $class ) . '.php' ;
    $class = realpath($file) ;
    if( ! $class || ! is_readable ( $class ) )
        throw new exception('없는 클래스 : '.$file);
    include_once $class ;
});

set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext){
    var_dump('에러 핸들러');
    var_dump($errno, $errstr, $errfile, $errline, $errcontext);
});

// 시작 시간
define('START', time());

/**
 * declare ticks(현재 파일의 프로그램 실행 줄)마다 실행
 * 하위 프로세스도 영향을 받는다
 */
declare(ticks=10);
register_tick_function(function(){
    $m = 'memory: ' . memory_get_usage() . 'B';
    $m .= ' time: '. time() - START . ' sec';
    echo '++++++++++++++++ ' . $m .' ++++++++++++++++';
});

/**
 * 실행된 프로그램(파일) 마다 종료시 실행됨
 */
register_shutdown_function(function(){
    var_dump('프로그램 종료');
});


/**
 * 테스트 익명 클래스
 */
new class
{
    function __construct(){

        // 테스트용 변수
        $this->arr = [1=>[1,2,3=>[3,4,5,6=>[7,8,9=>[11,22,33]]]]];

        // 메서드 전부 실행
        $method = get_class_methods($this);
        foreach ( $method as $m )
        {
            if ( $m == __FUNCTION__ )
                continue;
            var_dump('-------------------------- ' . $m . ' 시작 --------------------------');
            ob_start();
            $res = $this->$m();
            $o = ob_get_clean();
            var_dump('출력 값', $o);
            var_dump('리턴 값',$res);

            var_dump('-------------------------- ' . $m . ' 끝 --------------------------');
            sleep(1);
        }
    }

    /**
     * mysql sql문 만들기
     */
    function mysql_statement()
    {
        // 테스트데이터
        $arr = [
            'id'=>1,
            'test' => 2
        ];

        $class = new DB\Mysql\Statement;
        foreach ( get_class_methods($class) as $m )
        {
            var_dump('---------------------------------');
            var_dump($m);
            var_dump($class::$m($arr));
        }
    }

    /**
     * 프로세스 병렬실행
     */
    function multi_process()
    {
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
            return CLI\Process::run($multiworks);
        } catch (\Throwable $th) {
            var_dump($th->getMessage());
        }
    }


    /**
     * 다차원 배열 1차원으로 만들기
     */
    function arr_flat()
    {
        return Arr::flat($this->arr);
    }


    /**
     * 배열 순회하기 array_walk_recursive랑 다름
     */
    function arr_loop()
    {
        return Arr::loop($this->arr, function($k,$v){
            var_dump($k,$v);
        });
    }


    /**
     * 배열의 최고 깊이 얻기
     */
    function arr_depth()
    {
        return Arr::depth($this->arr);
    }


    /**
     * IP 서브넷 마스크 범위 얻기
     */
    function ip_range()
    {
        var_dump(IP::range('123.123.123.123/8'));
        var_dump(IP::range('123.123.123.123/16'));
        var_dump(IP::range('123.123.123.123/24'));
        var_dump(IP::range('123.123.123.123/32'));
    }


    /**
     * 용량 단위 붙은 숫자 계산
     */
    function calc_bytes()
    {
        var_dump(Calc::human2Bytes('123021G'));
        var_dump(Calc::bytes2Human(12345487451));

        var_dump(Calc::human2Bytes('123021GB',1024));
        var_dump(Calc::bytes2Human(12345487451,1024));
    }


    /**
     * x를 n개로 나누는데 등차수열로
     * 숫자 사이의 gap은 아직없음
     */
    function calc_apint()
    {
        $x = 100;
        $ap = Calc::APint($x,5);
        var_dump($ap,'나뉜 수를 합하면 x 나옴',array_sum($ap),$x);
        return $ap;
    }


    /**
     * 최대공약수와 비율 얻기
     */
    function calc_gcd_ratio()
    {
        var_dump(Calc::gcd(-5885,11877,535));
        var_dump(Calc::ratio(-5885,11877,535));
    }


    /**
     * 랜덤 문자열 얻기
     */
    function str_random()
    {
        var_dump(Str::random());
        var_dump(Str::random(rand(5,30)));
    }


    /**
     * 문자 섞기
     */
    function str_mix()
    {
        var_dump(Str::mix(Str::random()));
        var_dump(Str::mix('aaaaabbbbbbcccc'));
    }


    /**
     * 마스킹
     * 현재는 가운데 기준으로 *로 치환하게 되어있음
     * 이모지 같은 4바이트 문자열은 제대로 안될 수 있음
     */
    function str_masking()
    {
        var_dump(Str::masking('가나다'));
        var_dump(Str::masking('010-1234-1234'));
        var_dump(Str::masking('가나 다라마 바사'));
        var_dump(Str::masking('가나다 라마  바사',7));
    }


    /**
     * 웹에서 접속한 도메인 얻기
     * 프로토콜 붙여서
     */
    function url_domain()
    {
        try {
            var_dump(URL::domain());
        } catch (\Throwable $th) {
            var_dump($th->getMessage());
        }
    }


    /**
     * url 파싱하기
     * 서브도메인 쪽에 .을 추가해서 서브서브도메인인척 만들 수 있는 부분 생각해서 만들음
     * 그때문에 tld는 미리 해당 함수에서 따로 써놓은거만 감지하게 되어있음
     */
    function url_parse()
    {
        var_dump(URL::parse('https://ssub.sub.test.co.kr'));
    }
};