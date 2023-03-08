<?php 
require_once('./model/MySQL.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
date_default_timezone_set('Asia/Kuala_Lumpur');

if(isset($_GET['client'])){
    $client = $_GET['client'];
    $settings = parse_ini_file('../config.ini',true);
    $settings = $settings[$client];
    $mysql = new MySQL($settings);

    $latitude = $_GET['latitude'];
    $longitude = $_GET['longitude'];
    $salesperson_id = $_GET['salesperson_id'];
    $sql = "insert into cms_user_location(latitude, longitude, salesperson_id, updated_at) values ('{$latitude}','{$longitude}','{$salesperson_id}',now());";
    $mysql->Execute(
        $sql
    );
    $mysql->Close();

    file_put_contents('location.log',$sql.PHP_EOL,FILE_APPEND);
}
?>