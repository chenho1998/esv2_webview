<?php
header('Content-Type: text/plain; charset="UTF-8"');
require_once('./model/MySQL.php');

if(isset($_GET['client'])){
    $config = parse_ini_file('../config.ini',true);

    $client = $_GET['client'];
    $sched_id = $_GET['id'];

    $settings = $config[$client];
    $mysql = new MySQL($settings);

    $res = $mysql->Execute("select * from cms_visit_report where json_extract(schedule,'$.id') = {$sched_id} and status > 0;");
    for ($i=0; $i < count($res); $i++) { 
        $checkin_id = $res[$i]['mobile_checkin_id'];
        $res[$i]['uploads'] = $mysql->Execute("select * from cms_salesperson_uploads where upload_bind_id = '{$checkin_id}'");
    }
    echo json_encode($res);
}
?>