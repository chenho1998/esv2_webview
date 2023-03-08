<?php
header('Content-Type: text/plain; charset="UTF-8"');
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once('./model/DB_class.php');

function updateOrderItem($data)
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

    $discount_method = $data['discount_method'];
    $order_id = $data['order_id'];

    if($discount_method == 'PercentDiscountType'){
        $discount = 0;
        $sub_total = floatval($data['quantity']) * floatval($data['price']);

        if($data['disc_1']){
            $sub_total =  $sub_total - (($sub_total * floatval($data['disc_1']))/100);
        }

        if($data['disc_2']){
            $sub_total =  $sub_total - (($sub_total * floatval($data['disc_2']))/100);
        }

        if($data['disc_3']){
            $sub_total =  $sub_total - (($sub_total * floatval($data['disc_3']))/100);
        }

        $discount = (floatval($data['quantity']) * floatval($data['price'])) - $sub_total;
    }else{
        $sub_total = floatval($data['quantity']) * floatval($data['price']);
        $discount = floatval($data['disc_1']);
        $sub_total = $sub_total - $discount;
    }

    $net_total = $data['quantity'] + $data['new_qty'] - $data['exchanged_qty'] - $data['rejected_qty'] - $data['less_qty'];

    $query = "UPDATE cms_order_item SET editted_quantity= CASE WHEN editted_quantity = 0 THEN quantity ELSE editted_quantity END, unit_price = ".$data['price'].", unit_uom = '".$data['uom']."', quantity = ".$net_total.", disc_1 = '".$data['disc_1']."', disc_2 = '".$data['disc_2']."', disc_3 = '".$data['disc_3']."',salesperson_remark = '".$data['remark']."',cancel_status = ".$data['status'].", sub_total = '".$sub_total."', discount_amount = '".$discount."', discount_method ='".$discount_method."', exchanged_qty = ".$data['exchanged_qty'].", rejected_qty = ".$data['rejected_qty'].", less_qty = ".$data['less_qty'].", new_qty = ".$data['new_qty']." WHERE order_item_id = ".$data['ipad_item_id'] ." AND order_id = '{$order_id}' ";

    $db->query($query);
    if($db->get_affected_rows() > 0)
    {
        $json_data['msg'] = array("msg"=>"Success Updated.");
    }else{
        $json_data['msg'] = array("msg"=>"Did Not Update.");
    }

    return json_encode($json_data);
}