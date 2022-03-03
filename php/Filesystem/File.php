<?php

namespace Filesystem;

use Filesystem\FilesystemException;


class File
{
	protected $file;
	function __construct ( string $file )
	{
		if ( ! is_file ( $file ) || ! is_readable ( $file ) || ! is_writable ( $file ) )
			throw new FilesystemException ( '파일이 없거나 읽기쓰기 권한없음 : ' . $file ) ;

		$this->file = $file;
	}

    function __get ( $p )
	{
		if ( property_exists ( $this , $p ) )
			return $this->$p ;
	}

    static function touch ( string $file )
    {
        touch($file);
        return new self ( $file ) ;
    }

    static function tmp ( string $dir, string|NULL $prefix = NULL)
    {
        $f = tempnam ( $dir , $prefix ) ;
        if ( ! is_file ( $f ) )
            throw new FilesystemException ( '임시파일 생성 실패 : 디렉토리 : ' . $dir ) ;
        return new self ( $f ) ;
    }

    function remove()
    {
        return unlink($this->file);
    }

	function info()
	{
		return array(
			'mime' => mime_content_type ( $this->file ) ,
			'mtime' => filemtime ( $this->file ) ,
			'type' => filetype ( $this->file ) ,
            'bytes' => filesize ( $this->file ) ,
		);
	}

	function read ()
	{
		$f = fopen($this->file , 'r');
		while ( $r = fgets($f) )
            yield $r;
		fclose($f);
		return $this ;
	}

	function write ( string $contents )
	{
		$f = fopen ( $this->file , 'w' ) ;
		flock ( $f , LOCK_EX ) ;
		$w = fwrite ( $f , $contents ) ;
		flock ( $f , LOCK_UN ) ;
		fclose ( $f ) ;
		return $this;
	}

	function append ( string $contents )
	{
		$f = fopen ( $this->file , 'a' ) ;
		flock ( $f , LOCK_EX ) ;
		$w = fwrite ( $f , $contents ) ;
		flock ( $f , LOCK_UN ) ;
		fclose ( $f ) ;
		return $this;
	}


	function parse()
	{
		$file = $this->file;
		$ext = explode('.',$file);
		$ext = array_pop($ext);
		if ($ext == 'json')
			return json_decode(file_get_contents($file),TRUE);
		elseif($ext == 'yaml')
			return yaml_parse_file($file);
		elseif($ext == 'yml')
			return yaml_parse_file($file);
		elseif($ext == 'xml')
			return simplexml_load_file($file);
		elseif($ext == 'ini')
			return parse_ini_file($file,TRUE);
	}
}
