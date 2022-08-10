<?php

namespace Filesystem;


use ZipArchive;
use Filesystem\File;
// use Filesystem\FilesystemException;


class Zip extends File
{
	protected $zip;
	protected $entry = [];
	function __construct ( $name = NULL , $mode = ZipArchive::CREATE )
	{
		$zip = new ZipArchive () ;

		if ( ! empty ( $name ) )
			$c = $zip->open($name,$mode);

		$this->zip = $zip;
	}

	function __get ( $p )
	{
		if ( property_exists ( $this , $p ) )
			return $this->$p ;
	}

	function __destruct()
	{
		$this->close();
	}

	function close()
	{
		$this->zip->close();
	}


	/**
	 * 비번걸때
	 * @param string 비번
	 * @return this
	 */
	function password( string $pwd )
	{
		$this->zip->setPassword($pwd);
		return $this;
	}


	/**
	 * @see 테스트 안해봄
	 * 엔트리로 압축
	 * @param array k => v 압축될경로 => 실제파일
	 * @return this
	 */
	function entry ( $entry )
	{
		foreach ($entry as $k => $v)
		{
			$v = realpath($v);
			if ( is_dir ( $v ) )
				$zip->zip->addEmptyDir($k);
			else
				$zip->zip->addFromString($k,file_get_contents($v));
			// 버전마다 권한 먹이는거 다름 ???
			// 권한
			$zip->zip->setExternalAttributesName($k,ZipArchive::OPSYS_UNIX,fileperms($v) << 16);
		}
		return $this;
	}


	/**
	 * 압축하기 단순
	 * @param string|array 경로
	 * @param string 결과
	 */
	static function compress ( $src, $dst )
	{
		// 파라미터 일괄
		if ( ! is_array ( $src ) )
			$src = [$src];

		$zip = new Zip($dst);

		// 디렉토리 스택 쌓아가면서 입력
		foreach ( $src as $s )
		{
			if ( is_dir ( $s ) )
			{
				// 상위경로
				$base = './'.basename($s);
				$zip->zip->addEmptyDir('./'.basename($s));
				$f = new Folder($s);
				$find = $f->find();
				foreach ( $find as $v )
				{
					// 실제경로
					$real = realpath($f->realpath.'/'.$v);

					// 상대경로 엔트리
					$v = self::join($base,$v);
					if ( is_dir ( $real ) )
						$zip->zip->addEmptyDir($v);
					else
						$zip->zip->addFromString($v,file_get_contents($real));
					// 버전마다 권한 먹이는거 다름 ???
					// 권한
					// @fix 현재 디렉토리 권한 적용안됨
					$zip->zip->setExternalAttributesName($v,ZipArchive::OPSYS_UNIX,fileperms($real) << 16,ZipArchive::FL_ENC_UTF_8);
				}
			}
			elseif ( is_file ( $s ) )
				$zip->zip->addFile($s);
			$zip->zip->setExternalAttributesName($v,ZipArchive::OPSYS_UNIX,fileperms($s) << 16);
		}
		return $zip;
	}


	/**
	 * @fix 확인필요
	 * 압축해제
	 */
	static function extract ( $src, $dst )
	{
		$zip = new Zip($src, ZipArchive::RDONLY);
		$zip->zip->extractTo($dst);
		$zip->close();
	}


	/**
	 * @fix 아직
	 * 스트림 읽기
	 * @param array 엔트리 이름
	 */
	function readStream ( $name = [] )
	{
		if ( empty ( $name ) )
			$name = $this->readEntry();
		foreach ($name as $k => $v)
		{
			$f = $this->zip->getStream ( $v );
			while ( $r = fgets($f) )
				yield $r;
			fclose($f);
		}

	}

	function readEntry()
	{
		for ($i = 0; $i < $this->zip->numFiles; $i++)
			yield $this->zip->getNameIndex($i);
	}


}