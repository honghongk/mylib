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
        if ( ! is_file ( $path ) )
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
        $ext = ucfirst(pathinfo($file)['extension'] ?? '');
        $class = __NAMESPACE__.'\\'.$ext;
        $ext = strtoupper($ext);
        $class2 = __NAMESPACE__.'\\'.$ext;
        if ( class_exists ( $class ) || class_exists ( $class2 ) )
            return new $class($file);
        return new self ( $file ) ;
    }

    static function tmp ( string $dir = NULL, string $prefix = NULL)
    {
        if ( empty ( $dir ) )
            $dir = sys_get_temp_dir();

        $f = tempnam ( $dir , $prefix ) ;
        if ( ! is_file ( $f ) )
            throw new Exception ( '임시파일 생성 실패 : 디렉토리 : ' . $dir ) ;
        return new self ( $f ) ;
    }

    function remove()
    {
        return unlink($this->realpath);
    }


    /**
     * 파일읽기
     * @param int 읽어낼 길이
     */
    function read ( $length = 0 )
    {
        if ( ! is_readable ( $this->realpath ) )
            throw new Exception('읽기권한이 없음', 1);
        $f = fopen($this->realpath , 'r');
        if ( empty ( $length ) )
        {
            while ( $r = fgets($f) )
                yield $r;
        }
        else
        {
            while ( ($r = fread($f, $length)) !== '' )
                yield $r;
        }

        fclose($f);
        return $this ;
    }

    function write ( string $contents )
    {
        // 데이터 크기 기준
        static $div;
        $div = 4096;

        if ( ! is_writable ( $this->realpath ) )
            throw new Exception("쓰기권한이 없음", 1) ;
            
        $f = fopen ( $this->realpath , 'w' ) ;
        flock ( $f , LOCK_EX ) ;

        // 쓰기 용량
        $w = 0;

        $len = strlen($contents);
        if ( $len <= $div )
            $w = fwrite ( $f , $contents ) ;
        else // 데이터가 클때 잘라서 입력
        {
            $quotient = floor($len / $div);
            $remainder = $len % $div;
            for ( $i = 0; $i <= $quotient; $i ++ )
            {
                $w += $i == $quotient
                    ? fwrite ( $f , substr ( $contents , $i * $div , $remainder ) )
                    : fwrite ( $f , substr ( $contents , $i * $div , $div ) ) ;
            }
        }
            
        
        flock ( $f , LOCK_UN ) ;
        fclose ( $f ) ;

        // 용량 덮어쓰기
        $this->bytes = $w;
        return $this;
    }

    function append ( string $contents )
    {
        $f = fopen ( $this->realpath , 'a' ) ;
        flock ( $f , LOCK_EX ) ;
        $w = fwrite ( $f , $contents ) ;
        flock ( $f , LOCK_UN ) ;
        fclose ( $f ) ;

        // 용량 추가
        $this->bytes += $w;
        return $this;
    }


    /**
     * 파라미터와 include
     * 파라미터 이외의 변수는 안넘어가게
     * php만 되도록 ???
     * @param array 파라미터
     * @return mixed
     */
    function inc( array $data = [])
    {
        $instance = $this;
        $class = new class($data ,$this->realpath , $instance)
        {
            public $data;
            function __construct($data, $file, $instance)
            {
                $this->data = $data;
                $this->file = $file;
                $this->instance = $instance;
            }

            function inc()
            {
                ob_start();
                if ( ! empty ( $this->data ) )
                    extract($this->data, EXTR_SKIP);
                include $this->file;
                return ob_get_clean();
            }
        };
        return $class->inc();
    }
}
