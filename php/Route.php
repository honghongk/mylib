<?php

use HTTP\Request;
use DB\Mysql\Mysql;

/**

딴것보다 mysql에 입력한 route uri 쿼리는 쓸만한듯
라라벨처럼 사용하는거

*/

class Route
{
    /**
     * @var string uri prefix 요청 uri 앞에 없으면 에러 있으면 없애기
     */
    protected $uri;
    /**
     * @var string route 설정
     */
    protected $route;

    /**
     * 인스턴스
     */
    protected $Middleware;

    function __construct ( $config = [] )
    {
        foreach ($config as $k => $v)
        {
            // 맨앞 대문자면 인스턴스
			if ( property_exists ( $this, ucfirst ( $k ) ) )
			{
				$k = ucfirst($k);
				$v = new $k($v);
			}
            $this->$k = $v;
        }
    }

 
    /**
     * symfony router 형식
     * @param string symfony router 정규식
     * @param string uri
     * @param array 정규식에 따라 uri에서 얻을 데이터
     * @return boolean 매치여부
     */
    function regex ( $regex, $uri, &$uridata = [] )
    {
        // 괄호 추출
        preg_match_all('/{([^{}]+)}/',$regex, $match);
        
        // 0 regex 모든문자로 매치
        foreach ($match[0] as $v)
            $regex = str_replace($v,'(.*)',$regex);

        // 치환
        $regex = str_replace('/','\/',$regex);
        preg_match('/^'.$regex.'$/',$uri,$m);

        // 1 매치된 부분 변수로 담기
        foreach ($match[1] as $k => $v)
            $uridata[$v] = $m[$k+1];

        return ! empty ( $m ) ;
    }


    /**
     * @fix 필요할때 수정
     */
    function middleware ( $config )
    {
        echo '<pre>';
        // 공통 미들웨어 regex => check => response
        foreach ($config as $k => $v)
        {
            var_dump($k,$v);
            
        }

        exit;
    

    }


    /**
     * uri method 확인 후 리턴
     * @param Request 요청정보
     * @return array 요청에 매치되는 컨트롤러 또는 템플릿들
     */
    function to ( Request $req , &$uridata = [] )
    {
        // $this->middleware
        if ( is_null ($uridata ) )
            $uridata = [];

        $req->uri = str_replace ( $this->uri['prefix'], '', $req->uri ) ;

        $sql = 'SELECT
        A.id, A.name, A.description, A.uri, A.method AS http_method , B.module, B.method
        FROM route AS A 
        JOIN route_module AS B ON(A.id = B.route_id) 
        WHERE \''.$req->uri.'\' REGEXP CONCAT( \'^\', REGEXP_REPLACE( A.uri, \'{[^{}]+}\' ,\'([^/]*?)\' ), \'$\' )
         AND A.method = \''.$req->method.'\'';

        $match = Mysql::query ( $sql ) ;

        $res = [];
        foreach ( $match as $k => $v )
        {
            if ( $this->regex ( $v['uri'], $req->uri , $uridata ) )
                $res[] = [
                    'module' => $v['module'],
                    'method' => $v['method']
                ];
        }

        return $res;
    }
}
