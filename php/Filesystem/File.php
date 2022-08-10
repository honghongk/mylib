<?php

namespace Filesystem;

use Filesystem\AbstractFS;
use Exception;
// use Filesystem\FilesystemException;


class File extends AbstractFS
{
	/**
	 * 파일 권한 확인 후 프로퍼티 세팅
	 * @param string 경로
	 */
	function __construct ( string $path )
	{
		if ( ! is_file ( $path ) || ! is_readable ( $path ) || ! is_writable ( $path ) )
			throw new Exception ( '파일이 없거나 읽기쓰기 권한없음 : ' . $path ) ;

		// 왜안됨
		// parent::__construct($path);
		foreach ($this->info($path) as $k => $v)
			$this->$k = $v;
	}


    static function touch ( string $file )
    {
		if ( ! is_file ( $file ) )
        	touch($file);

		// camelcase
		$ext = ucfirst(pathinfo($file)['extension']);
		$class = __NAMESPACE__.'\\'.$ext;
		if ( class_exists ( $class ) )
			return new $class($file);
        return new self ( $file ) ;
    }

    static function tmp ( string $dir = NULL, string $prefix = NULL)
    {
		if ( empty ( $dir ) )
			$dir = sys_get_temp_dir();

        $f = tempnam ( $dir , $prefix ) ;
        if ( ! is_file ( $f ) )
            throw new FilesystemException ( '임시파일 생성 실패 : 디렉토리 : ' . $dir ) ;
        return new self ( $f ) ;
    }

    function remove()
    {
        return unlink($this->realpath);
    }


	function read ()
	{
		$f = fopen($this->realpath , 'r');
		while ( $r = fgets($f) )
            yield $r;
		fclose($f);
		return $this ;
	}

	function write ( string $contents )
	{
		$f = fopen ( $this->realpath , 'w' ) ;
		flock ( $f , LOCK_EX ) ;
		$w = fwrite ( $f , $contents ) ;
		flock ( $f , LOCK_UN ) ;
		fclose ( $f ) ;
		return $this;
	}

	function append ( string $contents )
	{
		$f = fopen ( $this->realpath , 'a' ) ;
		flock ( $f , LOCK_EX ) ;
		$w = fwrite ( $f , $contents ) ;
		flock ( $f , LOCK_UN ) ;
		fclose ( $f ) ;
		return $this;
	}


	// function parse()
	// {
	// 	$file = $this->realpath;
	// 	$ext = explode('.',$file);
	// 	$ext = array_pop($ext);
	// 	if ($ext == 'json')
	// 		return json_decode(file_get_contents($file),TRUE);
	// 	elseif($ext == 'yaml')
	// 		return yaml_parse_file($file);
	// 	elseif($ext == 'yml')
	// 		return yaml_parse_file($file);
	// 	elseif($ext == 'xml')
	// 		return simplexml_load_file($file);
	// 	elseif($ext == 'ini')
	// 		return parse_ini_file($file,TRUE);
	// }
}