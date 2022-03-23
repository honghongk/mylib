<?php

namespace Filesystem;

use Filesystem\File;
use Filesystem\FilesystemException;


class Folder
{
	// 절대경로 루트
	protected string $dir ;

	// 절대경로
	function __construct ( string $dir )
	{
		if ( ! is_dir ( $dir ) )
			throw new FilesystemException ( '없는 폴더' ) ;
		$this->dir = $dir;
	}

	function __get ( $p )
	{
		if ( property_exists ( $this , $p ) )
			return $this->$p ;
	}

	static function touch( string $dir )
	{
		mkdir($dir);
		return new self($dir);
	}

	static function mkdir ( string $dir )
	{
		if ( ! is_dir ( $dir ) )
			mkdir($dir);
		return new self($dir);
	}

	static function mkdirp ( string $dir )
	{
		$d = explode ( DIRECTORY_SEPARATOR, $dir ) ;
		$path = '';
		foreach ($d as $v)
		{
			$path .= $v . DIRECTORY_SEPARATOR;
			self::mkdir($path);
		}
		return new self($path);
	}

	// 상대경로 폴더만
	function scan ()
	{
		$d = opendir($this->dir);
		while ( $i = readdir($d) )
		{
			if ( in_array ( $i, array('.', '..') ) )
				continue;
			yield $i;
		}
		closedir($d);
		return $this;
	}
}
