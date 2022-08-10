<?php

namespace DB\Mysql;


/**
 * IO 테스트 느낌으로
 * 
 * 미완
 */
class Test extends Mysql
{

    function __construct($config)
    {
        parent::__construct($config);
    }

    /**
     * @param string 테이블 이름
     * @param string 데이터베이스 이름
     * @return array
     */
    function info( $table, $db = 'DATABASE()' )
    {
        $sql = 'SELECT'
            . ' TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME,'
            . ' COLUMN_DEFAULT, IS_NULLABLE, DATA_TYPE, COLUMN_TYPE,'
            . ' CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, NUMERIC_SCALE,'
            . ' DATETIME_PRECISION, EXTRA'
            . ' FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '.$db.' AND TABLE_NAME = \''.$table.'\'';
        return self::query($sql);
    }


    /**
     * @param string 데이터타입
     * @param array 데이터 길이 범위 0 => start length, 1 => end length
     * @param boolean 빈값되는지
     * @return mixed 랜덤값
     */
    function random ( $type, $range, $nullable )
    {

    }


    /**
     * @param string 테이블 이름
     * @param int 데이터 수
     */
    function insert ( $table , $count = 1000 )
    {
        $info = $this->info($table);
        var_dump($info);
        foreach ($info as $v)
        {
            var_dump($v['COLUMN_NAME'],$v['DATA_TYPE']);
            $nullable = $v['IS_NULLABLE'] == 'YES' || ! empty ( $v['COLUMN_DEFAULT'] ) || $v['EXTRA'] == 'auto_increment' ;
            $lenght = $v['CHARACTER_MAXIMUM_LENGTH'] || $v['NUMERIC_PRECISION'] || $v['DATETIME_PRECISION'] ;
        }

    }
}
