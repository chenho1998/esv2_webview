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
    $id = $_GET['id'];

    $images = $mysql->Execute("select * from cms_salesperson_uploads where upload_bind_id = '{$id}'");

    $logs = $mysql->Execute("select l.name, l.login_id, date_format(s.sched_seen_at,'%d/%m/%Y %h:%i%p') as time from cms_customer_visit_sched_log s
    left join cms_login l on l.login_id = s.sched_seen_by where sched_id ='{$id}' order by s.sched_seen_at desc");

    $mysql->Close();
    echo json_encode(array('log'=>$logs,'images'=>$images));
}
?>