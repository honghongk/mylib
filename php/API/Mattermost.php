<?php


/**
 * 매터모스트 API
 * 봇 메세지 보내기
 */
class Mattermost
{
    // https://nttest.cloud.mattermost.com /api /v3
    protected static $config;
    function __construct( array $config )
    {
        self::$config = $config;
    }

    /**
     * 메세지 보내기
     * @param string $msg 메세지
     * @return bool
     */
    function send( string $msg ): boolean
    {
        
    }
}

$m = new Mattermost([
    'url' => 'https://nttest.cloud.mattermost.com/api',
    'version' => 'v3',

]);