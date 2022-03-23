<?php

namespace Filesystem;

class Video extends File
{
	function __construct()
	{

	}

	function download()
	{
		#parent::download();
	}

	/**
	 * 분할 안된 파일은 버퍼링 문제가 많음
	 * 비디오 파일 버퍼재생
	 */
	function play()
	{
		#header_remove ( 'X-Frame-Options' ) ;
		#header_remove ( 'Content-Security-Policy' ) ;
		#header_remove ( 'X-Content-Type-Options' ) ;
		header ( 'Accept-Ranges:bytes' ) ;
		header ( 'Content-Transfer-Encoding: binary' ) ;

		$origin = array_pop ( $origin ) ;
		$origin = Folder::filePath ( $origin ) ;
		if ( ! $origin )
			return '파일없음';

		$mime = mime_content_type ( $origin ) ;
		// 브라우저에서 지원안함 mkv파일
		if ( in_array ( $mime , array ( 'video/x-matroska' ) ) )
			$mime = 'video/mp4' ;
		header ( 'Content-Type: ' . $mime . ';' ) ;
		$start = 0 ;
		$size = filesize ( $origin ) ;
		$end = $size ;
		// start 0이면 조금 이상이면 많이
		if ( isset ( $_SERVER['HTTP_RANGE'] ) && preg_match ( '/(?<=bytes=).*/i' , $_SERVER['HTTP_RANGE'] , $match ) )
		{
			// 65536 브라우저 요청 시작 바이트 최대공약수
			$bytes = 65536 ;
			$match = array_pop ( $match ) ;
			$match = explode ( '-' , $match ) ;
			$start = intval ( array_shift ( $match ) ) ;
			$end = intval ( array_shift ( $match ) ) ;
			if ( ! empty ( $start ) )
				$bytes = $bytes * 100 ; // 소스요청 수 조절
			if ( empty ( $end ) )
				$end = $start + $bytes < $size ? $start + $bytes - 1 : $size - 1 ;
		}
		if ( $end == $size )
			header ( $_SERVER['SERVER_PROTOCOL'] . ' 200 OK' ) ;
		else
		{
			header ( $_SERVER['SERVER_PROTOCOL'] . ' 206 Partial Content' ) ;
			header ( "Content-Range: bytes $start-$end/$size" ) ;
		}
		header ( 'Content-Length:' . ( $end - $start + 1 ) ) ;

		$origin = fopen ( $origin , 'rb' ) ;
		fseek ( $origin , $start ) ;
		while ( $r = fread ( $origin , $end ) )
			echo $r;
		fclose ( $origin ) ;
	}
}
					

					
