<?php
header('Content-Type: text/plain; charset="UTF-8"');
require_once('./model/MySQL.php');

if(isset($_GET['client'])){
    $config = parse_ini_file('../config.ini',true);
	$client                     = $_GET['client'];
	$settings                   = $config[$client];
    $mysql = new MySQL($settings);

    $sched_id = $_GET['id'];
    $sched_in_progress = $_GET['status'];

    $mysql->Execute("alter table cms_customer_visit_sched add in_progress int default 0 null comment '0 - default 1 - in progress 2 - done / completed';");
    $mysql->Execute("update cms_customer_visit_sched set in_progress = '{$sched_in_progress}' where id = '{$sched_id}'");

    if($mysql->AffectedRows()){
        echo "1";
    }else{
        echo "0";
    }
}
?>