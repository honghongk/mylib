<?php


use HTTP\Request as Request;

class Middleware
{

    /**
     * 기본 경로
     */
    protected $dir;

    /**
     * 핸들러 인스턴스 설정
     */
    protected $throttle;

    /**
     * 검사할 설정
     */
    protected $check = [];

    function __construct ( $config )
    {
        foreach ($config as $k => $v)
        {
            // if ( property_exists ( $this, ucfirst ( $k ) ) )
            // {
            //     $k = ucfirst($k);
            //     $class = '\\'.__CLASS__.'\\'.ucfirst($k);
            //     $v = new $class($v);
            // }
            $this->$k = $v;
        }
    }


    /**
     * @return array
     */
    function getConfig ( $file )
    {
        $res = [];
        
        // @ uri 길어지면 수정해야함
        $p = $this->dir.'/'.$file;
        $dir = realpath ( dirname($p) );

        // 설정 폴더 없음
        if ( ! $dir )
            return $res;
        
        // 파일 찾기
        // 확장자 빼고 같을 경우
        $filename = basename($p);
        $scan = array_diff(scandir($dir),['.','..']);
        foreach ($scan as $k => $v)
        {
            $info = pathinfo($v);
            if ( $info['filename'] == $filename )
            {
                $f = $dir .'/'. $info['basename'];
                break;
            }
        }

        // 설정파일 없음
        if ( ! isset ( $f ) || ! is_file($f) )
            return $res ;

        switch (strtolower($info['extension'])) {
            case 'json':
                $res = json_decode(file_get_contents($f),TRUE);
                break;
            case 'php':
                $res = (function($f){
                    return include $f;
                })($f);
                if ( is_string ( $res ) )
                    $res = json_decode ( $res, true );
                if ( ! is_array ( $res ) )
                    throw new Exception('php는 array|json return 만 가능합니다', 1);
                break;
            
            default:
                throw new Exception('지원하지 않는 데이터 포맷입니다.', 1);
                break;
        }

        return $res;
    }

    /**
     * 파일 파싱해서 가져오기
     * uri prefix 제외 첫번째는 경로
     */
    function setConfig ( $file )
    {
        $this->check = $this->getConfig($file);
        return $this;
    }


    /**
     * 네임스페이스 Middleware에 있는 모든 클래스로 검사
     * 
     * config을 uri로 얻거나 regex로 얻어야함
     */
    function check ( Request $req )
    {
        $res = NULL;

        $ns = __CLASS__;
        $check = $this->check;
        unset($this->check);

        if ( empty ( $check ) )
            throw new Exception(
                '유효성 검사 설정이 없습니다 : ' . $req->method . ' ' . $req->uri
            );

        // middleware class =>
        foreach ($check as $c => $v)
        {
            // 인스턴트에 request 데이터 세팅
            $class = $ns.'\\'.ucfirst($c);
            $i = new $class ( $this->$c ?? null );
            $i->setRequest($req);

            // property => args
            foreach ( $v as $p => $vv )
            {
                $m = 'set'.ucfirst($p);
                $i->$m($vv);
            }
            if ( ! empty ( $res = $i->check() ) )
                return $res;
        }
        return $res;
    }
}
