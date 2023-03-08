<?php
require_once('./model/MySQL.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
date_default_timezone_set('Asia/Kuala_Lumpur');

$settings = parse_ini_file('../config.ini',true);
$client = isset($_GET['client']) ? $_GET['client'] : '';
$message = isset($_GET['message']) ? $_GET['message'] : '';

if($client && $message){
    $settings = $settings[$client];
    $mysql = new MySQL($settings);

    $salesperson_devices = $mysql->Execute("SELECT device_token FROM cms_salesperson_device WHERE login_id <> device_token AND device_token <> ''");

    if(count($salesperson_devices) == 0){
        $salesperson_devices = $mysql->Execute("SELECT device_token FROM cms_login WHERE login_id <> device_token AND device_token <> ''");
    }

    $players = array();

    for ($i=0; $i < count($salesperson_devices); $i++) { 
        $players[] = $salesperson_devices[$i]['device_token'];
    }

    if(count($players) == 0){
        echo json_encode($salesperson_devices);
        die();
    }

    $content = array(
		"en" => $message
	);
	$newTitle = array(
		"en"=>'❗️ Announcement ❗️'
    );
    
    $fields = array(
		'app_id' => "aae0f2d5-28b8-4ccd-ae18-a18eb092d1ee",
		'include_player_ids' => $players,
		'data' => array("foo" => "bar"),
        'contents' => $content,
        'headings' => $newTitle
    );
    
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
    
    echo json_encode($response).' -- RESP --';

}else{
    echo "404";
}
?>