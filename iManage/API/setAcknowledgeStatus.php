<?php
header('Content-Type: text/plain; charset="UTF-8"');
require_once('./model/DB_class.php');

function setAcknowledgeStatus($data){
	$client = "easysale_".$data['client'];
	$db = new DB();
	$db->connect_db_with_db_config($client);

	$query = "UPDATE cms_order LEFT JOIN cms_order_item ON cms_order.order_id = cms_order_item.order_id
		SET cms_order_item.packing_status = 3 
		WHERE cms_order_item.packing_status = 2 
		AND cms_order.salesperson_id = '" . $data['userId']. "' AND cms_order.cust_code = '" . $data['custCode']. "'";
		
	// by default not only show today pending task, but show all pending task, thus need to update all pending task
	// DATE(cms_order.order_date) = CURDATE() AND

	$db->query($query);

	$query = "UPDATE cms_order LEFT JOIN cms_order_item ON cms_order.order_id = cms_order_item.order_id
		LEFT JOIN cms_order_item_message ON cms_order_item.order_item_id = cms_order_item_message.order_item_id
		SET  cms_order_item_message.read_status = 1 
		WHERE cms_order.salesperson_id = '" . $data['userId']. "' AND cms_order.cust_code = '" . $data['custCode']. "'";
		
	// by default not only show today pending task, but show all pending task, thus need to update all pending task
	// DATE(cms_order.order_date) = CURDATE() AND 

	$db->query($query);

	$query = "SELECT COUNT(*) AS amount FROM cms_order LEFT JOIN cms_order_item ON cms_order.order_id = cms_order_item.order_id
		WHERE cms_order_item.packing_status = 2 
		AND cms_order.salesperson_id = '" . $data['userId']. "' AND cms_order.cust_code = '" . $data['custCode']. "'";

	// by default not only show today pending task, but show all pending task, thus need to select all pending task
	// DATE(cms_order.order_date) = CURDATE()  AND
	
	$db->query($query);
	$result = $db->fetch_array();

	$json = array();
	$json['custCode'] = $data['custCode'];
	$json['result'] = $result['amount'];
	
	// if($db->get_num_rows() != 0)
	// {
	// 	while($result = $db->fetch_array())
	// 	{
	// 		$itemArr = array(
	// 					'product_name'=>$result['product_name'],
	// 					'product_code'=>$result['product_code'],
	// 					'cust_code'=>$result['cust_code'],
	// 					'quantity'=>$result['quantity']);
	// 		$values[$result['cust_company_name']][] = $itemArr;
	// 	}
	// }
	// // return 123;
	
	return json_encode($json);
}

?>