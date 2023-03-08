<?php
header('Content-Type: text/plain; charset="UTF-8"');
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once('./model/DB_class.php');

function openModal($data)
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
    $query = "SELECT product_uom FROM cms_product_uom_price_v2 WHERE product_code = '".$data['product_code']."'";

    $db->query($query);
    if($db->get_num_rows() != 0)
    {
        $product_uom = array();

        while($result = $db->fetch_array()){
            $product = array("product_uom"=>$result['product_uom']);
            $product_uom[] = $product;
        }
        $json_data['data'] = $product_uom;
    }

    $query2 = "SELECT status FROM cms_mobile_module WHERE module = 'discount_method'";
    $db->query($query2);
    $discount_method = 0;
    if($db->get_num_rows() != 0)
    {
        while($result2 = $db->fetch_array()){
            $discount_method = $result2['status'];
        }
    }

    $query3 = "SELECT status FROM cms_mobile_module WHERE module = 'app_multi_discount'";
    $db->query($query3);
    $app_multi_discount = 0;
    if($db->get_num_rows() != 0)
    {
        while($result3 = $db->fetch_array()){
            $app_multi_discount = $result3['status'];
        }
    }

    $json_data['discount_method'] = $discount_method;
    $json_data['app_multi_discount'] = $app_multi_discount;
    return json_encode($json_data);
}