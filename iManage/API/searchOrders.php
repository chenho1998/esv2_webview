<?php
header('Content-Type: text/plain; charset="UTF-8"');
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once('model/DB_class.php');



function searchOrders($data)
{	
	$config = parse_ini_file(dirname(__FILE__).'/../../config.ini');
	$delivery_info = $config['delivery_info'];
	$transfer_check = $config['transfer_check'];
	$enable_deliver_date = $config['enable_deliver_date'];
	$gst = floatval($config['gst_rate']);

	$client = "easysale_".$data['client'];

	$db = new DB();
	$db->connect_db_with_db_config($client);

	$db2 = new DB();
	$db2->connect_db_with_db_config($client);

	$clientsWithBranches = $config['branch_module'];
	$branch_query_select = "";
	$branch_query_join = "";

	if(in_array($data['client'],$clientsWithBranches)){
		$branch_query_select = "cms_order.branch_code,cms_customer_branch.branch_name,";
		$branch_query_join = "LEFT JOIN cms_customer_branch on cms_customer_branch.branch_code = cms_order.branch_code";
	}
	
	$query;
	
	$query = "SELECT cms_order.order_id,".$branch_query_select."salesperson_id,order_date,delivery_date,grand_total,order_status,cms_order.cancel_status,order_status_last_update_by,order_status_last_update_date,internal_updated_at,cust_company_name,order_delivery_note,staff_code,cms_delivery_info.message, COALESCE(SUM(CASE WHEN (cms_order_item.cancel_status = 0 AND(cms_order_item.packing_status = 1 OR cms_order_item.packing_status = 0))
        THEN cms_order_item.sub_total ELSE 0 END),0) AS sub_total,
		SUM( cms_order_item.packing_status = 0 AND cms_order_item.cancel_status = 0) AS notpacked,
		SUM( (cms_order_item.packing_status = 2 OR cms_order_item.packing_status = 3) AND cms_order_item.cancel_status = 0 ) AS nostock  FROM cms_order LEFT JOIN cms_order_item ON (cms_order.order_id = cms_order_item.order_id ) LEFT JOIN cms_login ON cms_order.salesperson_id = cms_login.login_id ".$branch_query_join." LEFT JOIN cms_delivery_info on cms_order.order_id = cms_delivery_info.order_id where";
	
	$decider = 0;
	
 	if($data['salespersonId'] != '')
	{
		$query .= " salesperson_id =" . $data['salespersonId'];
		
		$decider = 1;
	}
		
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
	
	if($data['dateFrom'] == ''){
		$data['dateFrom'] = '1900-01-01';
		$data['dateTo'] = date("Y-m-d");
	}
	
	if($data['dateTo'] == ''){
		if($decider == 1)
		{
			$query .= " AND order_date >= '" . $data['dateFrom'] . " 00:00:00' AND order_date <= '". $data['dateFrom'] . " 23:59:59'";
			
		}
		else
		{
			$query .= " order_date = '" . $data['dateFrom'] . " 00:00:00' AND order_date <= '". $data['dateFrom'] . " 23:59:59'";

			$decider = 1;
		}
	}
	else{
		if($decider == 1)
		{
			$query .= " AND order_date >= '" . $data['dateFrom'] . "  00:00:00' AND order_date <= '" . $data['dateTo'] . " 23:59:59'";
		}
		else
		{
			$query .= " order_date >= '" . $data['dateFrom'] . "  00:00:00' AND order_date <= '" . $data['dateTo'] . " 23:59:59'";

			$decider = 1;
		}
	}
	
	
	if($data['order_status'] != '')
	{
		if($decider == 1)
		{
			$query .= " AND cms_order.cancel_status =" . $data['order_status'];
		}
		else 
		{
			$query .= " cms_order.cancel_status =" . $data['order_status'];
			
			$decider = 1;
		}
	}
	elseif($data['showCancel'] == 0){
		$query .= " AND cms_order.cancel_status = 0";
	}

	if($data['customer_status'] != '')
	{
		if($decider == 1)
		{
			$query .= " AND cms_order.cust_id =" . $data['customer_status'];
		}
		else 
		{
			$query .= " cms_order.cust_id =" . $data['customer_status'];
			
			$decider = 1;
		}
	}
	
	if($data['deliveryDateFrom'] != '')
	{
		if($data['deliveryDateTo'] == '')
		{
			if($decider == 1)
			{
				$query .= " AND delivery_date >= '" . $data['deliveryDateFrom'] . " 00:00:00' AND delivery_date <= '". $data['deliveryDateFrom'] . " 23:59:59'";
			}
			else
			{
				$query .= " delivery_date = '" . $data['deliveryDateFrom'] . " 00:00:00' AND delivery_date <= '". $data['deliveryDateFrom'] . " 23:59:59'";

				$decider = 1;
			}
		}
		else
		{
			if($decider == 1)
			{
				$query .= " AND delivery_date >= '" . $data['deliveryDateFrom'] . "  00:00:00' AND delivery_date <= '" . $data['deliveryDateTo'] . " 23:59:59'";
			}
			else
			{
				$query .= " delivery_date >= '" . $data['deliveryDateFrom'] . "  00:00:00' AND delivery_date <= '" . $data['deliveryDateTo'] . " 23:59:59'";

				$decider = 1;
			}
		}
	}
	



	
	$query .= " GROUP BY cms_order.order_id ORDER BY internal_updated_at ASC";
	// return $query;
	$db->query($query);
	
	$orderList = array();
	
	if($db->get_num_rows() != 0)
	{
		while($result = $db->fetch_array())
		{
			$date = date_create($result["order_status_last_update_date"]);
		    $date = date_format($date,"d F g:iA");
		    $internal_date = date_create($result["internal_updated_at"]);
		    $internal_date = date_format($internal_date,"d F g:iA");

		 //    $total_amount = $db2->query("SELECT COALESCE(SUM(sub_total),0) AS sub_total FROM cms_order_item WHERE cancel_status = 0 AND (packing_status = 1 OR packing_status = 0) AND order_id = '" . mysql_real_escape_string($result["order_id"]) . "'");
		 //    $row = mysql_fetch_row($total_amount);
		 //    $total_amount = $row[0];

		 //    $packing_status = $db2->query("SELECT SUM( packing_status = 0 AND cancel_status = 0 AND order_id = '" . mysql_real_escape_string($result["order_id"]) . "') AS notpacked, SUM(  (packing_status = 2 OR packing_status = 3) AND cancel_status = 0 AND order_id = '" . mysql_real_escape_string($result["order_id"]) . "') AS nostock FROM cms_order_item"
			// );

			// $row = mysql_fetch_array($packing_status);
			$total_amount = $result['sub_total'];
			$total_amount = $total_amount*(1 + $gst);
		    $packing_status = $result['notpacked'];
		    $nostock_status = $result['nostock'];
		    
		    if ($packing_status > 0){
		    	$packing_status = 0;
		    }
		    else{
		    	if ($nostock_status > 0){
		    		$packing_status = 2;
		    	}
		    	else
		    	{
			    	$packing_status = 1;
			    }
			}
			
			$branch_code = "";
			$branch_name = "";

			if(in_array($data['client'],$clientsWithBranches)){
				$branch_code = $result['branch_code'];
				$branch_name = $result['branch_name'];
			}
		    
			$orderData = array(
							"order_id"=>$result["order_id"]
							,"branch_code"=>$branch_code
							,"branch_name"=>$branch_name
                            ,"order_date"=>$result["order_date"]
                            ,"delivery_date"=>$result["delivery_date"]
                            ,"cust_company_name"=>$result["cust_company_name"]
                            ,"grand_total"=>$total_amount
                            ,"order_status"=>$result["order_status"]
                            ,"packing_status"=>$packing_status
                            ,"cancel_status"=>$result["cancel_status"]
                            ,"sales_agent"=>$result['staff_code']
                            ,"order_status_last_update_date"=>$date
                            ,"order_status_last_update_by"=>$result["order_status_last_update_by"]
                            ,"internal_updated_at"=>$internal_date
                            ,"order_delivery_note"=>$result["order_delivery_note"]
                            ,"delivery_info"=>$result["message"]
					);

			
			
			// $db2->query(
			// 	"SELECT `cms_login`.`staff_code` 
			// 		FROM `cms_login` WHERE 
			// 		`login_id` = " . $result['salesperson_id']. ""
			// );
		
			// if($db2->get_num_rows() != 0)
			// {
			// 	while($result2 = $db2->fetch_array())
			// 	{	
			// 		$orderData['sales_agent'] = $result2['staff_code'];
			// 	}
			// }
					
			// use order status id to get order status name
			
			
				// if($orderData['order_status'] == null || $orderData['order_status'] == 0)
				// {
				// 	$orderData['order_status_name'] = '-';
				// }
				// else
				// {
					
				// }

			
			$orderList[] = $orderData;
		}
		
		$json_data['data'] = $orderList;
		$json_data['delivery_info'] = $delivery_info;
		$json_data["enable_deliver_date"]=$enable_deliver_date;
	}
	else
	{
		$json_data = array(
                   "success"=>'1'
                  ,'message'=>"No orders found"		
		);	
	}
	
	$db->query(
                "SELECT * FROM `cms_setting`"
			);
	
	$currencyList = array();
	
	if($db->get_num_rows() != 0)
    {
		while($result = $db->fetch_array())
        {
           	$currencyData = array(
							"currency"=>$result["currency"]);
			
			$currencyList[] = $currencyData;
        }
		
		$json_data['currency_data'] = $currencyList;
	}
	else 
	{
		/*$json_data = array(
                   "success"=>'0'
                  ,'message'=>"No currency found"		
		);*/
	}

	return json_encode($json_data);
}
?>