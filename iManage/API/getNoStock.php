<?php
header('Content-Type: text/plain; charset="UTF-8"');
require_once('./model/DB_class.php');

function getAllNoStockDetails($data)
{
	$client = "easysale_".$data['client'];
	
	$db = new DB();
	$db->connect_db_with_db_config($client);

	$config = parse_ini_file(dirname(__FILE__).'/../../config.ini');
	$clientsWithBranches = $config['branch_module'];
	$branch_query_select = "";
	$branch_query_join = "";

	if(in_array($data['client'],$clientsWithBranches)){
		$branch_query_select = " cms_order.branch_code,cms_customer_branch.branch_name,";
		$branch_query_join = " LEFT JOIN cms_customer_branch on cms_customer_branch.branch_code = cms_order.branch_code";
	}

	$query = "SELECT ".$branch_query_select." cms_order.cust_company_name,cms_product.product_remark, cms_order_item.product_name, cms_order_item.product_code, 
				cms_order.cust_code, cms_order_item.quantity, cms_order_item.packing_status, 
				max_message.message FROM cms_order LEFT JOIN cms_order_item ON cms_order.order_id = cms_order_item.order_id
			LEFT JOIN 
			(
               SELECT    id,order_item_id, message, read_status
				FROM      cms_order_item_message
        	) max_message
			ON cms_order_item.order_item_id = max_message.order_item_id LEFT JOIN cms_product ON cms_product.product_code = cms_order_item.product_code
			".$branch_query_join."
			WHERE 
			(cms_order_item.packing_status = 2 OR read_status = 0)
			AND cms_order.salesperson_id = '" . $data['userId']. "'";
		
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

			$remark = '';
			if($result['product_remark']){
				$remark = " (".$result['product_remark'].")";
			}

			$itemArr = array(
						'product_name'=>$result['product_name'].$remark,
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

function getNoStockNotification($data)
{	
	$client = "easysale_".$data['client'];
	$db = new DB();
	$db->connect_db_with_db_config($client);
	
	$json_data = array(
			'result'=> "0"
			,'noStockCount'=>""
	    );	

	$query = "SELECT COUNT(*) AS nostock_amt  FROM cms_order LEFT JOIN cms_order_item ON cms_order.order_id = cms_order_item.order_id
		LEFT JOIN 
		(
            SELECT    id,order_item_id, message, read_status
			FROM      cms_order_item_message
        ) max_message
		ON cms_order_item.order_item_id = max_message.order_item_id
		WHERE 
		(cms_order_item.packing_status = 2 OR read_status = 0)
		AND cms_order.salesperson_id = '" . $data['salespersonid']. "'";
		
	// by default not only show today pending task, but show all pending task
	// DATE(cms_order.order_date) = CURDATE()  AND 
	
	/*
	SELECT    MAX(id) message_id,order_item_id,MAX(message) message,MAX(read_status) read_status
              FROM      cms_order_item_message
              GROUP BY  order_item_id
	*/
	
	$db->query($query);
	
	if($db->get_num_rows() != 0)
	{
		while($result = $db->fetch_array())
		{
			$json_data['result'] = "1";
			$json_data['noStockCount'] = $result['nostock_amt'];
		}
	}
	
	return json_encode($json_data);
}

?>