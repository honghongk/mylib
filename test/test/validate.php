<?php
include __DIR__.'/index.php';

use Middleware\Validate;




$json = [
    '{"table":[{"tree_id":"0","parent_id":"","route_id":"71"},{"tree_id":"1","parent_id":"","route_id":"58"},{"tree_id":"2","parent_id":"","route_id":"43"}]}'
    ,'[]'
    ,'{"page":1,"per":10}'
];


$rule = [
    [
        'table' => [
            'tree_id' => [ 'int', 'min' => 0],
            'parent_id' => [ 'optional' , 'default' => 2, 'int', 'min' => 1],
            'route_id' => [ 'int', 'min' => 1],
        ],
    ],
    [
        'page' => [
            'min' => 1,'int',
            'default' => 1,
            'optional',
        ],
        'per' => [
            'min' => 10,'int',
            'default' => 10,
        ],
    ],
    [
        'page' => [
            'default' => 1,
            'min' => 1,'int'
        ],
        'per' => [
            'default' => 10,
            'min' => 10,'int'
        ],
    ],
];
$resp = [
    [
        'table' => [
            'tree_id' => [ 'message' => '오류가 발생했습니다' ],
            'parent_id' => [ 'message' => '오류가 발생했습니다' ],
            'route_id' => [ 'message' => '오류가 발생했습니다' ],
        ],
    ],
    [
        'page' => [
            'message' => '최소 1의 정수'
        ],
        'per' => [
            'message' => '최소 10의 정수'
        ],
    ],
    [
        'page' => [
            'message' => '최소 1의 정수'
        ],
        'per' => [
            'message' => '최소 10의 정수'
        ],
    ],
];

$toggle = [true,false];

foreach ($json as $k => $r)
{
    if ( !is_array($r))
        $r = json_decode($r,true);
        // $r = json_decode($r,$toggle[array_rand($toggle)]);
    $v = new Validate();
    // $checked = [];
    
    echo str_repeat('-',80)."\n";
    // var_dump($r);
    // var_dump($rule[$k]);
    $res = $v->recursive($r,$rule[$k],$resp[$k]);
    // var_dump($r,$rule[$k],$resp[$k]);
    var_dump($res);

    var_dump($r);


}