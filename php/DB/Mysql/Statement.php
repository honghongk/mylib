<?php

namespace DB\Mysql;

/**
 * 쿼리문 만들기
 */
class Statement
{
    /**
     * 전달받은 데이터들을 이스케이프 한다
     * @param array 데이터
     * @return array 이스케이프된 데이터
     */
    protected static function escape(array $data):array
    {
        $res = [];
        // 이스케이프
        foreach($data as $k => $v)
        {
            $k = addslashes($k);
            if ( is_numeric($v) )
            {
                if ( strpos($v, '.') === false )
                    $res[$k] = intval($v);
                else
                    $res[$k] = floatval($v);
            }
            else
                $res[$k] = addslashes($v);
        }
        return $res;
    }


    /**
     * 키 = 값 만들기
     * @param array 데이터  [ k => v, k => v, ...]
     * @param string 키값합치고 연결
     * @return string       k = ? , k = ? , k = ?
     */
    static function kv(array $data, string $kvglue = '=', string $arrglue = ','):string
    {
        $data = self::escape($data);
        $tmp = [];
        foreach ( $data as $k => $v )
            $tmp[] = '`' . $k . '` ' . $kvglue . ' \'' . $v . '\' ';
        $res = ' ' . implode($arrglue, $tmp);
        return $res;
    }


    /**
     * IN() 만들기
     * @param array 데이터 [v,v,v, ...]
     * @return string
     */
    static function in(array $data):string
    {
        $data = self::escape($data);
        $res = ' IN(';
        $tmp = [];
        foreach ($data as $k => $v)
            $tmp[] = $v;
        $res .= implode(',', $tmp) . ')';
        return $res;
    }


    /**
     * NOT IN() 만들기
     * @param array [v,v,v ...]
     * @return string
     */
    static function notin(array $data):string
    {
        $data = self::escape($data);
        return ' NOT ' . self::in($data);
    }


    /**
     * SET절 만들기
     * @param array 데이터
     * @return string SET절
     */
    static function set(array $data):string
    {
        $data = self::escape($data);
        return ' SET ' . self::kv($data);
    }


    /**
     * WHERE 만들기
     * @param array 데이터
     * @param string 논리연산자 키값 사이
     * @param string 논리연산자 별개 요소 사이
     * @return string WHERE절
     */
    static function where(array $data, $o1 = '=', $o2 = 'AND' ):string
    {
        $data = self::escape($data);
        return ' WHERE ' . self::kv ( $data, $o1, ' ' . $o2 . ' ');
    }
}
