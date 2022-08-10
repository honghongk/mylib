<?php


include __DIR__.'/index.php';


use DB\Mysql\Builder;

function divide ( $arr )
{
    $col = [];
    $other = [];
    foreach ($arr as $k => $v)
    {
        if ( is_int($k) )
            $col[] = $v;
        else
            $other[$k] = $v;
    }
    return [$col,$other];
}



$select = [
    // 최상위는 테이블|뷰|프로시저 오브젝트
    'route' => [
        // 숫자키는 컬럼
        'id','name','uri',
        'where' => [
            ['id', 1]
        ],
        'limit' => [1,2],
        // 병렬
        'join' => [
            'route_module' => [
                'module','method',
                'on' => [ 'route.id','route_module.route_id' ],
                'type' => 'inner'
            ]
        ],
        'orderby' => [
            ['route.id','desc'],
        ],
        'groupby' => ['route.id','route_module.id'],
        'having' => ['route.id',1],
    ]
];









// 이거는 지정해줘야함
// + 엔드포인트에서 권한 지정, validate
$insert = [
    'table' => [[
        'id' => 'asdf',
        'name' => 'asdf',
        'v' => 'asdf',
    ]],
    'table2' => [
        ['id','name','v'],
        ['asdf','asdf','asdf']
    ],

    'table3' => [
        ['id','name','v'],
        [
            ['asdf','asdf','asdf'],
            ['asdf','asdf','asdf'],
            ['asdf','asdf','asdf'],
        ]
    ]
];

foreach ($insert as $t => $v)
{
    $b = Builder::table($t);
    $b = call_user_func_array([$b,'insert'],$v);
    // var_dump($b->sql);
}



$update = [
    'table' => [
        ['id','name','v'],
        ['asdf','asdf','asdf'],
        'where' => [
            ['id',1],
            ['id', '>', 1]
        ]
    ]

];

foreach ($update as $k => $v)
{
    [$set, $option] = divide($v);
    $b = Builder::table($k);
    call_user_func_array([$b,'update'], $set);
    foreach ($option as $kk => $vv)
    {
        if ( $kk === 'where' )
            foreach ($vv as $vvv)
                call_user_func_array([$b,$kk],$vvv);
    }
    // var_dump($b->sql);
}


$delete = [
    'table' => [
        'where' => [
            ['id',1],
            ['id', '>', 1]
        ],
    ],
];

foreach ($delete as $k => $v)
{
    $b = Builder::table($k)->delete();
    foreach ($v as $kk => $vv)
        foreach ($vv as $vvv)
            call_user_func_array([$b,$kk],$vvv);
}




exit;



// SELECT 테스트
$q = Builder::table('route')->select(['COUNT(id)','name','uri']);

$q
->where('route.id',1)
->join('route_module',[
    'route.id','route_module.route_id'
])
->where('route_module.id','>','1')
->orderby('route.id','desc')
// 숫자도 되긴하는데 컬럼만이어야 정확함
->groupby('route.id')
->having('route.id',1)
->limit(1,2);

// var_dump($q);
var_dump($q->sql);


// INSERT 테스트
$qq = Builder::table('route')->insert([
    'id' => 'asdf',
    'name' => 'asdf',
    'v' => 'asdf',
]);
var_dump($qq->sql);

$qq = Builder::table('route')->insert(
    ['id','name','v'],
    ['asdf','asdf','asdf']
);
var_dump($qq->sql);


$qq = Builder::table('route')->insert(
    ['id','name','v'],
    [
        ['asdf','asdf','asdf'],
        ['asdf','asdf','asdf'],
        ['asdf','asdf','asdf'],
    ]
);
var_dump($qq->sql);


// UPDATE 테스트
$qq = Builder::table('route')->update(
    ['id','name','v'],
    ['asdf','asdf','asdf']
)->where('id' , '1')->where('id' ,'>','1');
var_dump($qq->sql);


// DELETE 테스트
$qq = Builder::table('route')->delete()
->where('id' , '1')->where('id' ,'>','1');
var_dump($qq->sql);


