<?php
require_once('./model/MySQL.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
date_default_timezone_set('Asia/Kuala_Lumpur');
$settings = parse_ini_file('../config.ini',true);

if(isset($_GET['client'])){
    $client = $_GET['client'];
    $salesperson_id = $_GET['salesperson_id'];
    $cust_code = $_GET['cust_code'];

    $settings = $settings[$client];
    $mysql = new MySQL($settings);

    $res = array();
    $final = array();

    $main = "select * from cms_stock_tmplt where active_status = 1";
    if(!empty($cust_code)){
       $main = "select s.* from cms_stock_tmplt s left join cms_stock_tmplt_bind b
       on b.tmplt_id = s.id where cust_code = '{$cust_code}'";
    }
    $res = $mysql->Execute($main);
    
    for ($i=0; $i < count($res); $i++) { 
        $obj = safe($res[$i]);
        $id = $obj['id'];
        $cpObj = $obj;
        $items = $mysql->Execute("select dtl_code as product_code, dtl_name as product_name from cms_stock_tmplt_dtl where tmplt_id = '{$id}' and dtl_type = 'ITEMS'");
        for ($j=0; $j < count($items); $j++) { 
            $iter = safe($items[$j]);
            $is_package = $iter['is_package'];
            $package_code = $iter['package_code'];
            $iter['type'] = '';
            if(empty($is_package) && empty($package_code)){
                $iter['type'] = 'INDIVIDUAL';
            }
            if(!empty($is_package) && !empty($package_code)){
                $iter['type'] = 'PACKAGE';
            }
            $cpObj['items'][] = $iter;
        }
        $final[] = $cpObj;
    }

    echo json_encode($final,JSON_PRETTY_PRINT);
}
function safe($obj){
    foreach ($obj as $key => $value) {
        if($value == null){
            $obj[$key] = '';
        }
    }
    return $obj;
}
?>