<?php

namespace Filesystem;

// use Filesystem\FilesystemException;



abstract class AbstractFS
{
	/**
	 * 프로퍼티 세팅
	 */
    function __constructor ( string $path )
    {
		// var_dump('asdfasd');
        foreach (self::info($path) as $k => $v)
			$this->$k = $v;
    }


    /**
     * property readonly
	 * @param string 프로퍼티 이름
     */
    function __get ( $p )
	{
		if ( property_exists ( $this , $p ) )
			return $this->$p ;
	}


    /**
     * 파일 또는 디렉토리의 정보
	 * @param string 경로
     */
	static function info ( string $path )
	{
		return array_merge(array(
			'mime' => mime_content_type ( $path ) ,
			'mtime' => filemtime ( $path ) ,
			'type' => filetype ( $path ) ,
            'bytes' => filesize ( $path ) ,
			'realpath' => realpath ( $path ) ,
			'group' => filegroup ( $path ) ,
			'owner' => fileowner ( $path ) ,
			'permission' => self::permission ( $path ) ,
		) , pathinfo ( $path ) ) ;
	}


	/**
	 * 경로를 연결한다
	 * . .. 만있는경로도 정리한다
	 */
	static function join ()
	{
		$args = func_get_args();
		$path = '';
		array_walk_recursive($args,function($v)use(&$path){
			if ( ! empty ( $path ) )
				$v = '/'.$v;
			$path .= $v;
		});

		$arr = explode('/',$path);
		$res = [];
		foreach ($arr as $k => $v) {
			// 첫번째는 그대로 입력하고 스킵
			if ( $k == 0 )
			{
				$res[] = $v;
				continue;
			}

			if ( $v === '.' )
				continue;
			elseif ( $v === '..' )
			{
				// 상위로 가는거라 빼기
				if ( ! empty ( $res ) )
					array_pop($res);
			}
			$res[] = $v;
		}
		return implode('/',$res);
	}


    /**
	 * https://www.php.net/fileperms
	 * 2번째 예제
	 * 리눅스에서 읽는거는 맞는데 압축하면 뭔가 안맞음
	 * @param string 경로
	 */
	static function permission ( string $path )
	{
		$p = fileperms($path);
		switch ($p & 0xF000) {
			case 0xC000: // socket
				$info = 's';
				break;
			case 0xA000: // symbolic link
				$info = 'l';
				break;
			case 0x8000: // regular
				$info = 'r';
				break;
			case 0x6000: // block special
				$info = 'b';
				break;
			case 0x4000: // directory
				$info = 'd';
				break;
			case 0x2000: // character special
				$info = 'c';
				break;
			case 0x1000: // FIFO pipe
				$info = 'p';
				break;
			default: // unknown
				$info = 'u';
		}
		$info .= (($p & 0x0100) ? 'r' : '-');
		$info .= (($p & 0x0080) ? 'w' : '-');
		$info .= (($p & 0x0040) ?
            (($p & 0x0800) ? 's' : 'x' ) :
            (($p & 0x0800) ? 'S' : '-'));

		// Group
		$info .= (($p & 0x0020) ? 'r' : '-');
		$info .= (($p & 0x0010) ? 'w' : '-');
		$info .= (($p & 0x0008) ?
            (($p & 0x0400) ? 's' : 'x' ) :
            (($p & 0x0400) ? 'S' : '-'));

		// World
		$info .= (($p & 0x0004) ? 'r' : '-');
		$info .= (($p & 0x0002) ? 'w' : '-');
		$info .= (($p & 0x0001) ?
            (($p & 0x0200) ? 't' : 'x' ) :
            (($p & 0x0200) ? 'T' : '-'));
		
		return $info;
	}

    
}

