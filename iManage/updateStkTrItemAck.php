<?php
header('Content-Type: text/plain; charset="UTF-8"');
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once('./model/DB_class.php');

function updateStkTrItemAck($data)
{
	$client = $data['client'];
    $config = parse_ini_file('../config.ini',true);
	$settings                   = $config[$client];

	$db_user                    = $settings['user'];
	$db_pass                    = $settings['password'];
	$db_name                    = $settings['db'];
    $db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

	$db = new DB();
    $con_1 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);
    $json_data = array();

    
    $query = "update cms_stock_transfer_dtl set acknowledged = '{$data['acknowledged']}' where id = '{$data['id']}'";

    $db->query($query);
    if($db->get_affected_rows() > 0)
    {
        $json_data['msg'] = array("msg"=>"Success Updated.");
    }else{
        $json_data['msg'] = array("msg"=>"Did Not Update.");
    }

    return json_encode($json_data);
}