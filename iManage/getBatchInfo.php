<?php 
require_once('./model/MySQL.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
date_default_timezone_set('Asia/Kuala_Lumpur');
$settings = parse_ini_file('../config.ini',true);

if(isset($_GET['client'])){
    $client = $_GET['client'];
    $settings = $settings[$client];
    $mysql = new MySQL($settings);
    $batches = array();
    $check = $mysql->Execute("select * from information_schema.TABLES where TABLE_NAME = 'cms_project'");
    if(count($check) > 0){
        $batches = $mysql->Execute("select * from cms_product_batch;");
    }

    echo json_encode(array('data'=>$batches));
}
?>