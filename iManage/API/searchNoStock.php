<?php
header('Content-Type: text/plain; charset="UTF-8"');
require_once('model/DB_class.php');

function searchNoStock($data)
{
	$client = "easysale_".$data['client'];
	$db = new DB();
	$db->connect_db_with_db_config($client);

	$db2 = new DB();
	$db2->connect_db_with_db_config($client);
	
	$query;
	
	$decider = 0;
	
		
	// if($data['year'] != '')
	// {
	// 	if($decider == 1)
	// 	{
	// 		$query .= " AND YEAR(order_date) =" . $data['year'];
	// 	}
	// 	else 
	// 	{
	// 		$query .= " YEAR(order_date) =" . $data['year'];
			
	// 		$decider = 1;
	// 	}
	// }
	
	// if($data['month'] != '')
	// {
	// 	if($decider == 1)
	// 	{
	// 		$query .= " AND MONTH(order_date) =" . $data['month'];
	// 	}
	// 	else 
	// 	{
	// 		$query .= " MONTH(order_date) =" . $data['month'];
			
	// 		$decider = 1;
	// 	}
	// }
	
	// if($data['day'] != '')
	// {
	// 	if($decider == 1)
	// 	{
	// 		$query .= " AND DAY(order_date) =" . $data['day'];
	// 	}
	// 	else 
	// 	{
	// 		$query .= " DAY(order_date) =" . $data['day'];
			
	// 		$decider = 1;
	// 	}
	// }
	if($data['dateTo'] == ''){
		$query .= " order_date >= '" . $data['dateFrom'] . " 00:00:00' AND order_date <= '". $data['dateFrom'] . " 23:59:59'";
	}
	else{
		$query .= " order_date >= '" . $data['dateFrom'] . " 00:00:00' AND order_date <= '" . $data['dateTo'] . " 23:59:59'";
	}

	$query = "SELECT cms_order.cust_company_name, cms_order_item.product_name, cms_order_item.product_code, cms_order.cust_code, cms_order_item.quantity, cms_order.order_date, cms_order_item.packing_status, max_message.message FROM cms_order LEFT JOIN cms_order_item ON cms_order.order_id = cms_order_item.order_id
			LEFT JOIN 
			(
              SELECT    MAX(id) message_id,order_item_id,MAX(message) message,MAX(read_status) read_status
              FROM      cms_order_item_message
              GROUP BY  order_item_id
        	) max_message
			ON cms_order_item.order_item_id = max_message.order_item_id
			WHERE " . $query . "  AND 
			(cms_order_item.packing_status = 2 OR cms_order_item.packing_status = 3 OR max_message.read_status = 0 OR max_message.read_status = 1)
			AND cms_order.salesperson_id = '" . $data['salespersonId']. "'";

	// $query = "SELECT * FROM cms_order LEFT JOIN cms_order_item ON cms_order.order_id = cms_order_item.order_id
	// 	WHERE " . $query . " AND (cms_order_item.packing_status = 2 OR cms_order_item.packing_status = 3 )
	// 	AND cms_order.salesperson_id = '" . $data['salespersonId']. "'";
			// return $query;
	$db->query($query);
	$values = array();

	if($db->get_num_rows() != 0)
	{
		while($result = $db->fetch_array())
		{
			$itemArr = array(
						'product_name'=>$result['product_name'],
						'product_code'=>$result['product_code'],
						'cust_code'=>$result['cust_code'],
						'quantity'=>$result['quantity'],
						'order_date'=>$result['order_date'],
						'packing_status'=>$result['packing_status'],
						'message'=>$result['message']);
			$values[$result['cust_company_name']][] = $itemArr;
		}
	}
	// return 123;
	return json_encode($values);
}
?>