<?php

namespace Filesystem;

use Filesystem\AbstractFS;
use Exception;
// use Filesystem\File;
// use Filesystem\FilesystemException;


class Folder extends AbstractFS
{

	/**
	 * 디렉토리 있는지 확인 후 프로퍼티 세팅
	 * @param string 경로
	 */
	function __construct ( string $path )
	{
		if ( ! is_dir ( $path ) )
			throw new Exception ( '없는 디렉토리' . $path ) ;
		// 왜안됨
		// parent::__construct($path);

		foreach ($this->info($path) as $k => $v)
			$this->$k = $v;
	}


	/**
	 * 없으면 생성
	 * @param string 경로
	 * @return Folder
	 */
	static function touch( string $path )
	{
		if ( ! is_dir ( $path ) )
			return self::mkdir($path);
		return new self($path);
	}


	/**
	 * 디렉토리 생성
	 * @param string 경로
	 * @return Folder
	 */
	static function mkdir ( string $path )
	{
		mkdir($path);
		return new self($path);
	}


	/**
	 * 디렉토리 재귀적으로 생성
	 * @param string 경로
	 * @return Folder
	 */
	static function mkdirp ( string $path )
	{
		$d = explode ( DIRECTORY_SEPARATOR, $path ) ;
		$res = '';
		foreach ($d as $v)
		{
			$res .= $v . DIRECTORY_SEPARATOR;
			self::mkdir($res);
		}
		return new self($res);
	}


	/**
	 * 디렉토리 내부 읽기
	 * @return yield
	 */
	function scan ()
	{
		$d = opendir($this->realpath);
		while ( $i = readdir($d) )
		{
			if ( in_array ( $i, array('.', '..') ) )
				continue;
			yield $i;
		}
		closedir($d);
		return $this;
	}


	/**
	 * find 명령어 처럼 출력
	 * @return array
	 */
	function find()
	{
		$res = [];
		$this->recursive(function($i)use(&$res){
			// 앞에 한번만 상대경로로 변경
			$res[] = substr_replace( $i, '.' , strpos ( $i, $this->realpath  ) , strlen($this->realpath));
		});
		return $res;
	}


	/**
	 * 재귀 스캔
	 * @param closure
	 * @return this
	 */
	function recursive ( $act )
	{
		foreach ($this->scan() as $v)
		{
			$i = $this->realpath.'/'.$v;
			$act($i);
			if ( is_dir ( $i ) )
				$this->touch($i)->recursive($act);
		}
		return $this;
	}


	/**
	 * 디렉토리 복사
	 * @param string 경로
	 * @return Folder 복사된 경로의 폴더 인스턴스
	 */
	function copy($dst)
	{
		$dst = $this->touch($dst)->realpath;
		foreach ( $this->scan() as $v )
		{
			$s = $this->realpath .'/' . $v;
			$d = $dst.'/'.$v;
			if ( is_dir ( $s ) )
				$this->touch($s)->copy($d);
			else
				copy($s,$d);
		}
		return $dst;
	}


	static function remove ( $path )
	{
		if ( ! is_dir ( $path ) )
			throw new Exception('디렉토리 아님', 1);

		self::touch($path)->recursive(function($i){
			if ( is_dir ( $i ) )
				self::remove($i);
			else
				unlink($i);
		});
		rmdir($path);
	}
}