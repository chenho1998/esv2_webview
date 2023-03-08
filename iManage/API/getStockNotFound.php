<?php
header('Content-Type: text/plain; charset="UTF-8"');
require_once('./model/DB_class.php');

function getStockNotFoundPage($data)
{
	$client = "easysale_".$data['client'];

	$db = new DB();
	$db->connect_db_with_db_config($client);

	$config = parse_ini_file(dirname(__FILE__).'/../../config.ini');

	$clientsWithBranches = $config['branch_module'];

	$branch_query_select = "";
	$branch_query_join = "";

	if(in_array($data['client'],$clientsWithBranches)){
		$branch_query_select = "cms_consignment.branch_code,cms_customer_branch.branch_name,";
		$branch_query_join = "LEFT JOIN cms_customer_branch on cms_customer_branch.branch_code = cms_consignment.branch_code";
	}

	$query = "SELECT ".$branch_query_select." cms_consignment.cust_company_name, cms_consignment_item.product_name, cms_consignment_item.product_code,
				cms_consignment.cust_code, cms_consignment_item.quantity, cms_consignment_item.packing_status,
				max_message.message FROM cms_consignment LEFT JOIN cms_consignment_item ON cms_consignment.consign_id = cms_consignment_item.consign_id
			LEFT JOIN
			(
               SELECT    id,consign_item_id, message, read_status
				FROM      cms_consignment_item_message
        	) max_message
			ON cms_consignment_item.consign_item_id = max_message.consign_item_id
			".$branch_query_join."
			WHERE
			(cms_consignment_item.packing_status = 2 OR read_status = 0)
			AND cms_consignment.salesperson_id = '" . $data['userId']. "'";

	$db->query($query);
	$values = array();

	if($db->get_num_rows() != 0)
	{
		while($result = $db->fetch_array())
		{
			$branch_code = "";
			$branch_name = "";

			if(in_array($data['client'],$clientsWithBranches)){
				$branch_code = $result['branch_code'];
				$branch_name = $result['branch_name'];
			}

			$key = $result['cust_company_name'];
			if($branch_name){
				$key = $key.' ('.$branch_name.')';
			}

			$itemArr = array(
						'product_name'=>$result['product_name'],
						'product_code'=>$result['product_code'],
						'cust_code'=>$result['cust_code'],
						'quantity'=>$result['quantity'],
						'packing_status'=>$result['packing_status'],
						'message'=>$result['message']);
			$values[$key][] = $itemArr;
		}
	}

	return json_encode($values);
}

function getStockNotFoundNotification($data)
{
	$client = "easysale_".$data['client'];
	$db = new DB();
	$db->connect_db_with_db_config($client);

	$json_data = array(
			'result'=> "0"
			,'noStockCount'=>""
	    );

	$query = "SELECT COUNT(*) AS nostock_amt  FROM cms_consignment LEFT JOIN cms_consignment_item ON cms_consignment.consign_id = cms_consignment_item.consign_id
		LEFT JOIN
		(
            SELECT    id,consign_item_id, message, read_status
			FROM      cms_consignment_item_message
        ) max_message
		ON cms_consignment_item.consign_item_id = max_message.consign_item_id
		WHERE
		(cms_consignment_item.packing_status = 2 OR read_status = 0)
		AND cms_consignment.salesperson_id = '" . $data['salespersonid']. "'";

	$db->query($query);

	if($db->get_num_rows() != 0)
	{
		while($result = $db->fetch_array())
		{
			$json_data['result'] = "1";
			$json_data['stockNotFoundCount'] = $result['nostock_amt'];
		}
	}

	return json_encode($json_data);
}

?>
