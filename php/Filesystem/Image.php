<?php


/**
 * 웹전용 이미지 webp로 변환 등
 * 이거는 의미상? 다른데다 놔야할듯 일단 여기놓기
 */
class Image
{
    protected $resource;
    function __construct ( string $file )
    {
        if ( ! is_file ( $file ) )
            throw new Exception('없는 파일', 1);

        $mime = mime_content_type($file);
        // $mime[1] ext
        $mime = explode ( '/' , $mime ) ;
        if ( ! is_int ( strpos ( $mime[0] , 'image' ) ) )
            throw new Exception("이미지 아님", 1);
        // jpg -> jpeg
        $ext = explode ( '.' , $file ) ;
        $ext = array_pop ( $ext ) ;
        if ( $ext == 'jpg' )
            $ext = 'jpeg';

        $func = 'imagecreatefrom'.$ext;
        if ( ! function_exists ( $func ) )
            throw new Exception("함수 지원하지않는 확장자 ".$func, 1);
            
        $this->resource = call_user_func( $func , $file );
    }


    /**
     * 웹전용 이미지
     * 단점은 아이폰 안되고 일부 기기? 에서 압축해제같은게 느리다고함
     * 
     * IE는 지원안됨
     */
    function webp ( \resource|string $name )
    {
        return imagewebp ( $this->resource , $name . '.' . __FUNCTION__ ) ;
    }
}
