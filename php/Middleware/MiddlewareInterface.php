<?php

namespace Middleware;

use HTTP\Request as Request;

/**
 * 동적으로 돌리기 때문에
 * 필수 메서드 있어야함
 */
interface MiddlewareInterface
{
    /**
     * 데이터를 세팅한다
     * @param Request 요청
     * @return this
     */
    function setRequest( Request &$request );

    /**
     * 룰을 세팅한다
     * @param array $config [ 데이터 키 => 데이터 값 확인할 메서드 ]
     * @return this
     */
    function setRule ( array $config );
    /**
     * 응답할 것을 세팅한다
     * @param array $config [ 데이터 키 => 실패시 메세지, 리다이렉트 등 ]
     * @return this
     */
    function setResponse ( array $config );

    /**
     * 검사를 실행한다
     */
    function check();

}