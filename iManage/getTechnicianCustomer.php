<?php
require_once('./model/MySQL.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
date_default_timezone_set('Asia/Kuala_Lumpur');

if(isset($_GET['client']) && isset($_GET['technician_id'])){
    $client = $_GET['client'];
    $settings = parse_ini_file('../config.ini',true);

    $settings = $settings[$client];
    $mysql = new MySQL($settings);

    $technician_id = intval($_GET['technician_id']);

    $sp_group = $mysql->Execute("select * from cms_mobile_module where ");

    $mysql->Close();
}
?>