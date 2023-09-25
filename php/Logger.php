<?php

/**
 * 파일 로그를 남긴다
 */
class Logger
{
    /**
     * @var string 기본 최상위 경로
     */
    static protected $dir;

    /**
     * @var int 만료일
     */
    static protected $expire;


    /**
     * 기본 설정을 세팅한다
     * @param array 설정값
     */
    function __construct ( $config )
    {
        /**
         * config 키 => 
         *      check 조건함수
         *      set 세팅함수
         *      msg 에러시 메세지
         */
        $c = [
            'dir' => [
                'check' => 'realpath',
                'set' => 'realpath',
                'msg' => '로그 경로 없음',
            ],
            'expire' => [
                'check' => 'is_numeric',
                'set' => 'intval',
                'msg' => '숫자만 입력가능',
            ],
        ];

        foreach ($c as $k => $v)
        {
            $t = $config[$k];
            if ( ! $v['check']($t) )
                throw new Exception ( $v['msg'].' : ' . $t ) ;
            self::$$k = $v['set']($t) ;
        }
    }

    function __destruct ()
    {
        $this->expire();
    }


    // ?? 
    static function __callStatic ( $call, $arg )
    {

        var_dump(__FUNCTION__);
        var_dump($call,$arg);

    }

    /**
     * 경로 안에 만료된 파일들 삭제
     */
    function expire()
    {
        // 0 이하면 무제한
        if ( self::$expire < 1 )
            return;

        $scan = array_diff(scandir(self::$dir),['.','..']);
        foreach ($scan as $f)
        {
            $file = self::$dir.DIRECTORY_SEPARATOR.$f;
            if ( ! is_file ( $file ) )
                continue;

            // 시간계산 초 -> 일
            $t = (microtime(true) - filemtime($file)) / 86400 ;

            // 초과시 삭제
            if ( $t > self::$expire )
                unlink($file);
        }
    }


    /**
     * 로그파일 이름, 경로를 반환
     * @param string 파일이름 prefix
     * @return string 파일 풀경로
     */
    public static function setFile ( $file = 'log' )
    {
        return self::$dir . DIRECTORY_SEPARATOR . $file . '_' . date ( 'Ymd' ) ;
    }


    /**
     * 에러
     * @param string|array 메세지 
     */
    public static function error ( $msg )
    {
        // 파일 세팅
        $file = self::setFile(__FUNCTION__);
        self::write($file, $msg);
    }


    /**
     * 확인
     * @param string|array 메세지
     */
    public static function check ( $msg )
    {
        // 파일 세팅
        $file = self::setFile(__FUNCTION__);
        self::write($file, $msg);
    }


    /**
     * 로그파일에 쓰기
     */
     protected static function write ( $file , $msg )
     {
        // 파일 세팅
        if ( ! is_file ( $file ) )
            touch ( $file ) ;

        // 메세지 변환
        if ( is_string ( $msg ) )
            $msg = explode("\n" , $msg) ;

        // 메세지 쓰기
        $f = fopen ( $file , 'a' ) ;
        fwrite ( $f , date ( 'Y-m-d H:i:s' ) . "\n" ) ;
        array_walk_recursive($msg,function($r)use($f){
            fwrite ( $f , $r."\n" ) ;
        });
        fwrite ( $f , "\n\n" ) ;
        fclose ( $f ) ;
     }
}
