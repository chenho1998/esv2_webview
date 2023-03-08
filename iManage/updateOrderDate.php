<?php
header('Content-Type: text/plain; charset="UTF-8"');
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once('./model/DB_class.php');

function updateOrderDate($data)
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

    $order_date = $data['order_date'];
    $order_id = $data['order_id'];

    $query = "UPDATE cms_order SET order_date = '".$order_date."' WHERE order_id = '{$order_id}' ";
    $json_data['query'] = $query;
    $db->query($query);
    if($db->get_affected_rows() > 0)
    {
        $json_data['msg'] = array("msg"=>"Success Updated.");

    }else{
        $json_data['msg'] = array("msg"=>"Did Not Update.");
    }

    return json_encode($json_data);
}