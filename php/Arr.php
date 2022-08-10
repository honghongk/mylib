<?php



/**
 * 배열 관련 함수 모음
 */
class Arr
{

    /**
     * k => v 인지 확인
     * @param array 배열
     * @return bool
     */
    static function isAssoc($arr)
    {
        return ! empty ($arr) && array_values($arr) !== $arr;
    }


    /**
     * k => v 아닌지 확인
     * @param array 배열
     * @return bool
     */
    static function isList($arr)
    {
        // 8.1 이상
        // return array_is_list($arr);
        return ! empty ($arr) && ! self::isAssoc($arr);
    }


    /**
     * 다차원 배열 1차로 펴기
     * @param array 배열
     * @return array 1차로 펴진 배열
     */
    static function flat ( $arr )
    {
        if (!is_array($arr))
                return $arr;
        $res = array();
        foreach ($arr as $k => $v)
        {
            if ( is_array ( $v ) )
                    $res = array_merge ( $res , self::flat ( $v ) ) ;
            else
                    $res[$k] = $v;
        }

        return $res;
    }


    /**
     * https://stackoverflow.com/questions/8392619/php-function-to-get-recursive-path-keys-with-path
     * @param iterable 배열, 오브젝트
     * @param callable 함수
     * @param array 경로
     * @return array 모든 값 경로 1차
     */
    static function recursive ( $arr , callable $act, $path = [] )
    {
        $res = array();
        foreach ($arr as $k => $v)
        {
            $current = array_merge($path, array($k));
            $act($k,$v,$current);
            if (is_array($v) || is_object($v))
                $res = array_merge($res, self::recursive($v, $act , $current));
            else
                $res[] = join('_', $current);
        }
        return $res;
    }


    /**
     * 배열 깊이 구하기
     * 몇차원 배열인지 구하기
     * @param array 배열
     * @return int 배열 깊이
     */
	static function depth ( $arr )
	{
		if ( ! is_array ( $arr ) )
			return 0 ;
		$res = 1 ;
		foreach ( $arr as $v )
		{
			if ( is_array ( $v ) )
				$depth = self::depth ( $v ) + 1 ;
			if ( isset ( $depth ) && $depth > $res )
				$res = $depth ;
		}
		return $res ;
	}

}
