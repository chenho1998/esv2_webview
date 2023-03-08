<?php
require_once('./model/MySQL.php');
header('Content-Type: application/json; charset=utf-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
date_default_timezone_set('Asia/Kuala_Lumpur');

if(isset($_GET['stock_id']) && isset($_GET['client'])){

    $client = $_GET['client'];
    $stock_id = $_GET['stock_id'];

    $settings = parse_ini_file('../config.ini',true);
    $settings = $settings[$client];

    $mysql = new MySQL($settings);

    $stock_transfer = $mysql->Execute("select salesperson_id from cms_stock_transfer where st_code = '{$stock_id}'");

    if(count($stock_transfer) > 0){
        $stock_transfer = $stock_transfer[0];
        $salesperson_id = $stock_transfer['salesperson_id'];
        $salesperson_device = $mysql->Execute("select device_token from cms_salesperson_device where login_id = '{$salesperson_id}' and device_token <> login_id and device_token <> '';");

        $device_token = array();
        for ($i=0; $i < count($salesperson_device); $i++) { 
            $obj = $salesperson_device[$i];
            if($obj['device_token']){
                $device_token[] = $obj['device_token'];
            }
        }

        $content = array(
            "en" => "Stock card is updated. Please update product list. Thank you"
        );
        $title = "ESv2 Stock Transfer";
        
        $newTitle = array(
            "en"=>$title
        );
    
        $fields = array(
            'app_id' => "aae0f2d5-28b8-4ccd-ae18-a18eb092d1ee",
            'include_player_ids' => $device_token,
            'data' => array("foo" => "bar"),
            'contents' => $content
        );
        if($title){
            $fields['headings'] = $newTitle;
        }
    
        $fields = json_encode($fields);
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
        'Authorization: Basic ZTIyMzE4YTYtMmE4Mi00NDhlLWJiNDQtMmQwNGRkYzVhZGYw'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    
        $response = curl_exec($ch);
        curl_close($ch);

        echo json_encode($response);
    }
}
?>