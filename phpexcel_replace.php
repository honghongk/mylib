<?php

/*

php7.4 부터 PHPExcel deprecated 에러

배열 중괄호로 접근하는거를 대괄호로 바꿔줘야함
$arr{$index}  >>  $arr[$index]



현재 한방에 처리는 안되고
한 5번 실행한 다음 에러메세지 따라가서 수정 ㄱㄱ

*/

$dir = realpath(__DIR__.'/../system/PHPExcel');

if ( ! is_dir($dir) )
    throw new Exception('디렉토리 없음', 1);

function recursive ( $dir, $closure )
{
    $s = scandir($dir);
    $s = array_diff($s,['.','..']);

    foreach ($s as $v)
    {
        $d = $dir.'/'.$v;
        if ( is_dir ( $d ) )
            recursive($d,$closure);
        else
            $closure($d);
    }

}

recursive($dir,function($v){
    $info = pathinfo($v);
    if ( ! isset($info['extension']) )
        return;
    if ( $info['extension'] != 'php')
        return;
    $f = fopen($v,'r');

    $res = [];
    while($r = fgets($f))
    {
        // 배열 변수 아이템 접근이 중괄호인것을 대괄호로 변경
        preg_match('/\$[a-zA-Z]+\d{0,}(\-\>[a-zA-Z]+\d{0,}){0,}(\{(\d+|\$[a-zA-Z]+)(\s{0,}(\+|\-)\s{0,}(\d+|\$[a-zA-Z]+))?\})/',$r,$m);
        if ( ! empty ( $m ) )
        {
            $replace = str_replace($m[2],'['.$m[3].($m[4] ?? '').']',$m[0]);
            $row = str_replace($m[0],$replace,$r);
            $res[] = $row;
        }
        else
        {
            $res[] = $r;
        }
    }

    fclose($f);
    file_put_contents($v,implode('',$res));
});
