<?php



include __DIR__.'/index.php';



/**
 * 모델 따로만들고 테이블 이름으로 alias
 * 
 * 테이블 정보 빼는거로 캐싱해놓기
 */



use DB\Mysql\Statement;


$sql = Statement::pivot(
    ['DATE(insert_date)'],
    ['method = "GET"','method = "POST"'],
    'route'
);
var_dump($sql);


