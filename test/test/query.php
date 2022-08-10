<?php


include __DIR__.'/index.php';



/**
 * 모델 따로만들고 테이블 이름으로 alias
 * 
 * 테이블 정보 빼는거로 캐싱해놓기
 */



use DB\Mysql\Builder;


/**
 * SELECT COUNT(id), name, uri FROM `route` 
 * INNER JOIN route_module 
 *  ON(route.id = route_module.route_id route.id = route_module.route_id) 
 * WHERE  route.id = 1
 *  AND route_module.id > 1 
 *  AND route.id IS NOT NULL 
 * GROUP BY route.id 
 * HAVING  route.id = 1 
 * ORDER BY route.id DESC 
 * LIMIT 1 OFFSET 2
 */

// SELECT 테스트
$q = Builder::table('route')->select(['COUNT(id)','name','uri']);

$q
->where('route.id','? ')
->join('route_module',[
    ['route.id','route_module.route_id'],
    ['route.id','=','route_module.route_id']
])
->where('route_module.id','>','?')
->where('route.id','IS NOT',NULL)
->orderby('route.id','desc')
// 숫자도 되긴하는데 컬럼만이어야 정확함
->groupby('route.id')
->having('route.id','?')
->limit(1,2)
->bind(['NULL','zxcv','werfw']);


// var_dump($q);
var_dump($q->sql);


exit;




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
        ['asdf','asdf','2022-03'],
        ['1','asdf','asdf'],
        ['2','asdf','asdf'],
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


