<?php
header('Content-Type: text/plain; charset="UTF-8"');
require_once('./model/DB_class.php');

function setAcknowledgeStatus($data){
	$client                     = $data['client'];

    $config = parse_ini_file('../config.ini',true);

	$settings                   = $config[$client];

    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
	$db_name                    = $settings['db'];
	$db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

	$db = new DB();
	$con_1 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);

	// $query = "UPDATE cms_order LEFT JOIN cms_order_item ON cms_order.order_id = cms_order_item.order_id
	// 	SET cms_order_item.packing_status = 3
	// 	WHERE (cms_order_item.packing_status = 2 OR cms_order_item.packing_status = 1)
	// 	AND cms_order.salesperson_id = '" . $data['userId']. "' AND cms_order.cust_code = '" . $data['custCode']. "'";

	$query = "UPDATE cms_order LEFT JOIN cms_order_item ON cms_order.order_id = cms_order_item.order_id
	SET cms_order_item.packing_status = 3
	WHERE (cms_order_item.packing_status = 2 OR (cms_order_item.packing_status = 1 AND cms_order_item.packed_qty != 0 AND cms_order_item.quantity <> cms_order_item.packed_qty))
	AND cms_order.salesperson_id = '" . $data['userId']. "' AND cms_order.cust_code = '" . $data['custCode']. "'";


	$db->query($query);

	$query = "UPDATE cms_order LEFT JOIN cms_order_item ON cms_order.order_id = cms_order_item.order_id
		LEFT JOIN cms_order_item_message ON cms_order_item.order_item_id = cms_order_item_message.order_item_id
		SET  cms_order_item_message.read_status = 1
		WHERE cms_order.salesperson_id = '" . $data['userId']. "' AND cms_order.cust_code = '" . $data['custCode']. "'";

	$db->query($query);

	$query = "SELECT COUNT(*) AS amount FROM cms_order LEFT JOIN cms_order_item ON cms_order.order_id = cms_order_item.order_id
		WHERE cms_order_item.packing_status = 2
		AND cms_order.salesperson_id = '" . $data['userId']. "' AND cms_order.cust_code = '" . $data['custCode']. "'";

	$db->query($query);
	$result = $db->fetch_array();

	$json = array();
	$json['custCode'] = $data['custCode'];
	$json['result'] = $result['amount'];


	return json_encode($json);
}

?>
