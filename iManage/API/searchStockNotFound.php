<?php
header('Content-Type: text/plain; charset="UTF-8"');
require_once('model/DB_class.php');

function searchStockNotFound($data)
{
	$client = "easysale_".$data['client'];
	$db = new DB();
	$db->connect_db_with_db_config($client);

	$db2 = new DB();
	$db2->connect_db_with_db_config($client);

	$query;

	$decider = 0;

	if($data['dateTo'] == '')
	{
		$query .= " consign_date >= '" . $data['dateFrom'] . " 00:00:00' AND consign_date <= '". $data['dateFrom'] . " 23:59:59'";
	}
	else
	{
		$query .= " consign_date >= '" . $data['dateFrom'] . " 00:00:00' AND consign_date <= '" . $data['dateTo'] . " 23:59:59'";
	}

	$query = "SELECT cms_consignment.cust_company_name, cms_consignment_item.product_name, cms_consignment_item.product_code, cms_consignment.cust_code, cms_consignment_item.quantity, cms_consignment.consign_date, cms_consignment_item.packing_status, max_message.message FROM cms_consignment LEFT JOIN cms_consignment_item ON cms_consignment.consign_id = cms_consignment_item.consign_id
			LEFT JOIN
			(
              SELECT    MAX(id) message_id,consign_item_id,MAX(message) message,MAX(read_status) read_status
              FROM      cms_consignment_item_message
              GROUP BY  consign_item_id
        	) max_message
			ON cms_consignment_item.consign_item_id = max_message.consign_item_id
			WHERE " . $query . "  AND
			(cms_consignment_item.packing_status = 2 OR cms_consignment_item.packing_status = 3 OR max_message.read_status = 0 OR max_message.read_status = 1)
			AND cms_consignment.salesperson_id = '" . $data['salespersonId']. "'";

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
						'consign_date'=>$result['consign_date'],
						'packing_status'=>$result['packing_status'],
						'message'=>$result['message']);

			$values[$result['cust_company_name']][] = $itemArr;
		}
	}

	return json_encode($values);
}
?>
