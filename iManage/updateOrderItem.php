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

    $round_up = $config['Show_Round_Up'];
	$show_round_up = in_array($client,$round_up['round_up']);

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

    $result = $db->query("SHOW COLUMNS FROM `cms_order_item` LIKE 'pick_changes'");
	$pick_changes_setting = (mysql_num_rows($result))?TRUE:FALSE;

    if($pick_changes_setting){
        $db->query("SELECT pick_changes, unit_uom, quantity, unit_price FROM cms_order_item WHERE ipad_item_id = ".$data['ipad_item_id'] ." AND order_id = '{$order_id}'");
        while($result = $db->fetch_array())
        {
            if($result['pick_changes']){
                $pick_changes = $result['pick_changes'] . ' | ' . $result['quantity'] . $result['unit_uom'] . ' RM' . $result['unit_price'];
            }else{
                $pick_changes = $result['quantity'] . $result['unit_uom'] . ' RM' . $result['unit_price'];
            }
        }
    }

    $query = "UPDATE cms_order_item SET unit_price = ".$data['price'].", unit_uom = '".$data['uom']."', quantity = ".$data['quantity'].", disc_1 = '".$data['disc_1']."', disc_2 = '".$data['disc_2']."', disc_3 = '".$data['disc_3']."',salesperson_remark = '".$data['remark']."',cancel_status = ".$data['status'].", sub_total = '".$sub_total."', discount_amount = '".$discount."', discount_method ='".$discount_method."' WHERE ipad_item_id = ".$data['ipad_item_id'] ." AND order_id = '{$order_id}' ";

    $db->query($query);
    if($db->get_affected_rows() > 0)
    {
        if($pick_changes_setting){
            $db->query("UPDATE cms_order_item SET pick_changes = '{$pick_changes}' WHERE ipad_item_id = ".$data['ipad_item_id'] ." AND order_id = '{$order_id}' ");
        }

        if($show_round_up){
            $db->query("UPDATE cms_order SET gst_amount = (SELECT IF(SUM(sub_total) IS NULL, 0, SUM(sub_total)) FROM cms_order_item WHERE order_id = '".$order_id."' AND cancel_status = 0), grand_total = (SELECT IF(SUM(sub_total) IS NULL, 0, CEIL(SUM(sub_total)*20 - 0.5)/20) FROM cms_order_item WHERE order_id = '".$order_id."' AND cancel_status = 0) WHERE order_id = '".$order_id."'");
        }else{
            $db->query("UPDATE cms_order SET grand_total = (SELECT IF(SUM(sub_total) IS NULL, 0, SUM(sub_total)) FROM cms_order_item WHERE order_id = '".$order_id."' AND cancel_status = 0) WHERE order_id = '".$order_id."'");
        }
        $json_data['msg'] = array("msg"=>"Success Updated.");
    }else{
        $json_data['msg'] = array("msg"=>"Did Not Update.");
    }

    return json_encode($json_data);
}