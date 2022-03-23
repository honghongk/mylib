<?php

class URL
{
    /**
     * 웹 현재 도메인
     * return string 도메인
     */
    static function domain()
    {
        if (php_sapi_name() =='cli')
            throw new Exception("cli 환경 에서는 실행할 수 없습니다", 1);

        return ( isset ( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http' )
            . '://'. $_SERVER['HTTP_HOST'];
    }

    /**
     * url 파싱하기
     * $url string url 주소
     * return array
     */
    static function parse ( $url )
    {
        static $tld ;
        if( empty ( $tld ) )
            $tld = array(
                '.co.kr','.com','.net','.kr'
            );
        $parse = parse_url($url);
        $host = ! isset ( $parse['scheme'] ) ? $parse['path'] : $parse['host'] ;

        $res = $parse;
        foreach ($tld as $v)
        {
            if ( strpos(strrev($host),strrev($v)) !== 0 )
                continue;
            $parse = array_filter ( explode ( '.' , substr ( $host , 0 , strpos ( $host , $v ) ) ) ) ;
            $res['root'] = array_pop ( $parse ) . $v ;
            if ( ! empty ( $parse ) )
                $res['sub'] = implode('.',$parse) ;
            $res['tld'] = implode('.',array_filter(explode('.',$v)));
            break;
        }
        return $res ;
    }
}
