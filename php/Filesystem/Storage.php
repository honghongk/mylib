<?php

namespace Filesystem;


use Filesystem\FilesystemException;
use Filesystem\Folder;

class Storage
{
    // 이름 => 절대경로
    static protected array $config ;
    function __construct ( array|string $config )
    {
        if ( is_string ( $config ) )
            $config = array ( $config ) ;

        foreach ( $config as $k => $v )
        {
            if ( ! is_dir ( $v ) || ! is_readable ( $v ) )
                throw new FilesystemException ( '없거나 읽기 권한없는 경로 : ' . $dir ) ;
        }
        if ( empty ( self::$config ) )
            self::$config = $config ;
    }

    static function disk ( $k )
    {
        return new Folder ( self::$config[$k] ) ;
    }
}
