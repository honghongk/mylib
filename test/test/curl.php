<?php

include __DIR__.'/index.php';

use HTTP\Curl;


/**
 * 사이트 두개 확인해서 차이확인
 * 로그인 적용
 * 왠만한것 디폴트옵션으로 ㄱ
 */



$sidebar = '["\/nmes\/product\/","\/nmes\/member\/","\/nmes\/company\/","\/nmes\/user\/","\/nmes\/select_menu\/","\/nmes\/alarm\/","\/nmes\/bom\/","\/nmes\/store\/","\/nmes\/order\/","\/nmes\/order_delivery\/","\/nmes\/claim\/","\/nmes\/material_order\/","\/nmes\/process_chase\/","\/nmes\/mrp\/","\/nmes\/process\/","\/nmes\/produce_plan\/","\/nmes\/work_order\/","\/nmes\/work_load\/","\/nmes\/reject\/","\/nmes\/adc_machine\/","\/nmes\/trust_order\/","\/nmes\/trust_order_delivery\/","\/nmes\/trust_claim\/","\/nmes\/trust_order_material\/","\/nmes\/import\/","\/nmes\/import_stat\/","\/nmes\/import_order\/","\/nmes\/export\/","\/nmes\/spedition\/","\/nmes\/export_spedition_stat\/","\/nmes\/reservation\/","\/nmes\/stock\/","\/nmes\/stock_real\/","\/nmes\/stock_log\/","\/nmes\/unique_barcode\/","\/nmes\/barcode_link\/","\/nmes\/qrcode\/","\/nmes\/press\/","\/nmes\/mold\/","\/nmes\/equipment\/","\/nmes\/repair\/","\/nmes\/tool\/","\/nmes\/tool_using\/","\/nmes\/main\/monitoring","\/nmes\/equipment_stat\/","\/nmes\/load_moitor\/","\/nmes\/adc_machine_monitor\/","\/nmes\/qrcode_monitor\/","\/nmes\/load_monitor\/","\/nmes\/qrcode_quantity\/","\/nmes\/adc_machine_quantity\/","\/nmes\/qr_barcode_link\/","\/nmes\/main\/tablet","\/nmes\/equipment_stat\/tablet","\/nmes\/load_moitor\/","\/nmes\/qrcode_monitor\/tablet","\/nmes\/application\/lists","\/nmes\/application\/step2_equipment_trouble","\/nmes\/test_case\/","\/nmes\/first_medium_last_result\/","\/nmes\/test_report\/","\/nmes\/quality_confirmations_file\/","\/nmes\/import_inspection\/","\/nmes\/export_inspection\/"]';
$link = $sidebar = json_decode($sidebar,true);


// $cookie = 'cookie';
// touch($cookie);



// 로그인
$host = 'http://116.125.140.66';
$curl = new Curl($host.'/nmes/auth/login');
$curl->post([
    'id' => 'admin',
    'pw' => 'admin'
]);
$res = $curl->send();

$diffhost = 'http://211.37.179.64';
$diffcurl = new Curl($diffhost.'/kdh_kiseon/auth/login');
$diffcurl->post([
    'id' => 'admin',
    'pw' => 'admin'
]);
$res = $diffcurl->send();


$resfile = 'checkpagediff';
touch($resfile);

ob_start();

$error = [];
foreach ($link as $v)
{
    $url = $host.$v;
    $curl->setOption(CURLOPT_URL, $url);
    $res = $curl->send();
    echo str_repeat('-',100)."\n";
    echo $res['http_code'].'  '.$res['url'] ."\n";
    preg_match('/<pre>.*/',$res['response'],$match);
    if ( ! empty ($match) )
        $error[] = $v;

    $diffv = explode('/',$v);
    $diffv[1] = 'kdh_kiseon';
    $diffv = implode('/',$diffv);
    $diffurl = $diffhost.$diffv;
    $diffcurl->setOption(CURLOPT_URL,$diffurl);
    $diffres = $diffcurl->send();
    
    echo $diffres['http_code'].'  '.$diffres['url'] ."\n";


    $d1 = $diffres['response'];
    $d2 = $res['response'];


    $d1 = array_filter(array_map('trim',explode("\n",$d1)));
    $d2 = array_filter(array_map('trim',explode("\n",$d2)));

    var_dump(array_diff($d1,$d2));
    var_dump(array_diff($d2,$d1));


}


echo str_repeat('-',100)."\n";
var_dump($error);
var_dump('에러목록');


$ttt = ob_get_clean();
file_put_contents($resfile,$ttt);