<?php

class Arr
{
	/**
	 * 다차원 배열을 1차원으로 변환
	 * $arr array
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
	 * 다차원 배열 순회 array_walk_recursive랑 다름
	 */
	static function loop ( $arr , $closure )
	{
		if ( ! is_array ( $arr ) )
			return FALSE ;
		foreach ( $arr as $k => $v )
		{
			$closure ( $k , $v ) ;
			if ( is_array ( $v ) )
				self::loop ( $v , $closure ) ;
		}
	}


	/**
	 * 배열의 깊이 얻기
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
