<?php


class Str
{
	/**
	 * 랜덤문자 대소문자 숫자 같은 수 만큼
	 * $len int 문자 수
	 * return string
	 */
	static function random ( int $len = 32 )
	{
		$loop = round($len/3);
		$res = '';
		for ($i=0; $i < $loop ; $i++)
			$res .= chr(rand(48,57))	// 숫자
				.chr(rand(65,90))		// 대문자
				.chr(rand(97,122));		// 소문자

		return substr(str_shuffle($res),0,$len);
	}


	/**
	 * 받은 인수 문자들 섞기
	 * $args string
	 * return string
	 */
	static function mix ()
	{
		$str = implode('',func_get_args());
		$arr = array();
		foreach(str_split($str) as $k => $v)
		{
			if ( $k % 2 == 0 )
				array_push ( $arr , $v ) ;
			else
				array_unshift ( $arr , $v ) ;
		}
		return implode('',$arr) ;
	}

	/**
	 * 문자 마스킹하기
	 * 이름 가운데 가리는 용도 등
	 * printable 아닌 문자는 2바이트를 하나로 보기
	 * $str string 문자
	 * $div int 1/div 만큼 가리기
	 * return string
	 */
	static function masking ( string $str , int $div = 3 )
	{
		$len = strlen($str) ;
		$start = intval($len/$div);
		$length = intval($len/$div);
		$masking = str_repeat('*',$length);

		$str = str_split($str) ;

		static $printable;
		if ( empty ( $printable ) )
			$printable = array_merge ( range ( 9,13 ) , range ( 32 , 126 ) ) ;

		$res = array () ;
		$nonprintable = 0;
		foreach ( $str as $k => $v )
		{
			if ( in_array ( ord ( $v ) , $printable ) )
			{
				if ( $start <= $k && $k < ($start + $length) )
				{
					array_push ( $res , '*' );
					continue;
				}
				array_push ( $res , $v ) ;
				continue;
			}

			$nonprintable++;
			if ( $nonprintable % 2 != 0 )
				continue;
			if ( $start <= $k && $k < ( $start + $length ) )
			{
				array_push ( $res , '*' );
				continue;
			}
			array_push ( $res , $str[$k-1].$v ) ;
		}

		return implode('',$res);
	}

}
