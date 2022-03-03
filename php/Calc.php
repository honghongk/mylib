<?php


class Calc
{
	/**
	 * 용량 붙은 단위를 바이트로 변경
	 * $format string 단위붙은 용량
	 * $coefficient 계수
	 * return int 바이트
	 */
	static function human2Bytes ( string $format , int $coefficient = 1000 )
	{
		// 바이트는 단위없거나 다른거는 B붙을수있음
		static $units ;
		if ( empty ( $units ) )
			$units = array(
				1000 => array('B','K','M','G','T','P'),
				1024 => array('B','KB','MB','GB','TB','PB')
			) ;
		if ( ! isset ( $units[$coefficient] ) )
			throw new Exception("지정되지 않은 계수 입니다", 1);

		preg_match('/([0-9.]+)(\w+)/',$format ,$match);
		$pow = array_search ( strtoupper ( $match[2] ) , $units[$coefficient]);
		if ( ! $pow )
			return false ;
		return $match[1] * pow ( $coefficient , $pow ) ;
	}


	/**
	 * 숫자로 된 바이트를 단위 붙이기
	 * $bytes int 바이트
	 * $coefficient int 계수
	 * $decimals int 반올림 자릿수
	 * return string 단위 붙은 용량
	 */
	static function bytes2Human ( int $bytes , int $coefficient = 1000 , int $decimals = 2 )
	{
		// 바이트는 단위없거나 다른거는 B붙을수있음
		static $units ;
		if ( empty ( $units ) )
			$units = array(
				1000 => array('B','K','M','G','T','P'),
				1024 => array('B','KB','MB','GB','TB','PB')
			) ;
		if ( ! isset ( $units[$coefficient] ) )
			throw new Exception("지정되지 않은 계수 입니다", 1);

		if ( ! is_int ( $bytes ) )
			return FALSE ;
		$i = floor ( log ( $bytes , $coefficient ) ) ;
		$po = array(0,0,2,2,3) ;
		return round ( $bytes / pow ( $coefficient , $i ) , $po[$i] ) . $units[$coefficient][$i];
	}

	/**
	 * div 수 만큼 숫자를 나눠서 정수로 등차수열
	 * $num int 전체숫자
	 * $div int 나눌숫자
	 * return array 나누어진 등차 배열
	 */
	static function APint ( int $num , int $div )
	{
		$d = intval ( round ( $num / $div ) ) ;
		$rem = $num % $div ;
		if ( $rem == 0 )
			$rem = 2 ;

		$arr = array();
		foreach ( array_pad ( $arr , $div , $d ) as $k => $v)
			$arr[$k] = $v + ( $k * $rem ) ;

		$sum = array_sum ( $arr ) ;
		foreach ( $arr as $k =>$v )
			$arr[$k] = intval ( round ( $v / ( $sum / $num ) ) ) ;

		$b = array_sum ( $arr ) - $num ;
		if ( $b != 0 )
		{
			$k = array_keys ( $arr , max ( $arr ) ) ;
			$k = array_pop ( $k ) ;
			$arr[$k] += $b ;
		}

		return $arr ;
	}

	/**
	 * 인수 간 최대 공약수 얻기
	 * ... $args int
	 * return int
	 */
	static function gcd ()
	{
		$args = array () ;
		foreach ( func_get_args () as $v )
		{
			$v = abs ( $v ) ;
			if ( empty ( $v ) )
				return FALSE ;
			array_push ( $args , $v ) ;
		}
		rsort ( $args ) ;
		$min = array_pop ( $args ) ;
		foreach ( $args as $k => $v )
		{
			$rem = $v % $min ;
			if ( $rem > 0 )
				$args[$k] = $rem ;
			else
				unset($args[$k]);
		}
		if ( empty ( $args ) )
			return $min ;
		array_push ( $args , $min ) ;
		return call_user_func_array ( array ( __CLASS__ , 'gcd' ) , $args ) ;
	}


	// 숫자 비율 a:b:c:d
	/**
	 * 인수 간 비율 얻기
	 * $args int
	 * return array
	 */
	static function ratio ()
	{
		$div = call_user_func_array ( array ( __CLASS__ , 'gcd' ) , func_get_args() ) ;
		if ( ! $div )
			return FALSE ;
		$res = array () ;
		foreach ( func_get_args () as $k => $v )
			$res[$k] = $v / $div ;
		return $res ;
	}

}
