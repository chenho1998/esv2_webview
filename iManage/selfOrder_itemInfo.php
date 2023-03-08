<?php
require_once('./model/MySQL.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
date_default_timezone_set('Asia/Kuala_Lumpur');

if(isset($_GET['client']) && isset($_GET['product_code'])){
    $empty_arr = json_encode(array());
    if(empty($_GET['product_code'])){
        echo $empty_arr;
        return;
    }
    $client = $_GET['client'];
    $product_code = MySQL::sanitize($_GET['product_code']);
    $settings = parse_ini_file('../config.ini',true);

    $settings = $settings[$client];
    $mysql = new MySQL($settings);

    $product_attachment = $mysql->Execute("select * from cms_product_atch where product_code = '{$product_code}' and active_status = 1");
    $product = $mysql->Execute("select * from cms_product where product_code = '{$product_code}'");
    $product = $product[0];
    $product_id = $product['product_id'];
    $product_image = $mysql->Execute("select * from cms_product_image where product_id = '{$product_id}' and active_status = 1");
    $mysql->Close();
    echo json_encode(
        array(
            'image'=>$product_image,
            'attachment'=>$product_attachment
        )
    );
}