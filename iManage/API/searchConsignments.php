<?php
header('Content-Type: text/plain; charset="UTF-8"');
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once('model/DB_class.php');

function searchConsignments($data)
{
	$config = parse_ini_file(dirname(__FILE__).'/../../config.ini');
	$delivery_info = $config['consignment_delivery_info'];
	$transfer_check = $config['consignment_transfer_check'];
	$enable_deliver_date = $config['consignment_enable_deliver_date'];
	$gst = floatval($config['gst_rate']);

	$client = "easysale_".$data['client'];
	$db = new DB();
	$db->connect_db_with_db_config($client);
	$db2 = new DB();
	$db2->connect_db_with_db_config($client);

	$clientsWithBranches = $config['branch_module'];

	$branch_query_select = "";
	$branch_query_join = "";

	$query;

	if(in_array($data['client'],$clientsWithBranches)){
		$branch_query_select = "cms_consignment.branch_code,cms_customer_branch.branch_name,";
		$branch_query_join = "LEFT JOIN cms_customer_branch on cms_customer_branch.branch_code = cms_consignment.branch_code";
	}

	$query = "SELECT cms_consignment.consign_id,".$branch_query_select."cms_consignment.consign_type,salesperson_id,consign_date,delivery_date,grand_total,consign_status,cms_consignment.cancel_status,consign_status_last_update_by,consign_status_last_update_date,internal_updated_at,cust_company_name,consign_delivery_note,staff_code,cms_delivery_info_consignment.message, COALESCE(SUM(CASE WHEN (cms_consignment_item.cancel_status = 0 AND(cms_consignment_item.packing_status = 1 OR cms_consignment_item.packing_status = 0))
			THEN cms_consignment_item.sub_total ELSE 0 END),0) AS sub_total,
			SUM(cms_consignment_item.packing_status = 0 AND cms_consignment_item.cancel_status = 0) AS notpacked,
			SUM((cms_consignment_item.packing_status = 2 OR cms_consignment_item.packing_status = 3) AND cms_consignment_item.cancel_status = 0 ) AS nostock  FROM cms_consignment LEFT JOIN cms_consignment_item ON (cms_consignment.consign_id = cms_consignment_item.consign_id ) LEFT JOIN cms_login ON cms_consignment.salesperson_id = cms_login.login_id ".$branch_query_join." LEFT JOIN cms_delivery_info_consignment on cms_consignment.consign_id = cms_delivery_info_consignment.consign_id where";
	

	$decider = 0;

 	if($data['salespersonId'] != '')
	{
		$query .= " salesperson_id =" . $data['salespersonId'];

		$decider = 1;
	}

	if($data['dateFrom'] == '')
	{
		$data['dateFrom'] = '1900-01-01';
		$data['dateTo'] = date("Y-m-d");
	}

	if($data['dateTo'] == '')
	{
		if($decider == 1)
		{
			$query .= " AND consign_date >= '" . $data['dateFrom'] . " 00:00:00' AND consign_date <= '". $data['dateFrom'] . " 23:59:59'";
		}
		else
		{
			$query .= " consign_date = '" . $data['dateFrom'] . " 00:00:00' AND consign_date <= '". $data['dateFrom'] . " 23:59:59'";

			$decider = 1;
		}
	}
	else{
		if($decider == 1)
		{
			$query .= " AND consign_date >= '" . $data['dateFrom'] . "  00:00:00' AND consign_date <= '" . $data['dateTo'] . " 23:59:59'";
		}
		else
		{
			$query .= " consign_date >= '" . $data['dateFrom'] . "  00:00:00' AND consign_date <= '" . $data['dateTo'] . " 23:59:59'";

			$decider = 1;
		}
	}


	if($data['consign_status'] != '')
	{
		if($decider == 1)
		{
			$query .= " AND cms_consignment.cancel_status =" . $data['consign_status'];
		}
		else
		{
			$query .= " cms_consignment.cancel_status =" . $data['consign_status'];

			$decider = 1;
		}
	}
	elseif($data['showCancel'] == 0){
		$query .= " AND cms_consignment.cancel_status = 0";
	}

	if($data['customer_status'] != '')
	{
		if($decider == 1)
		{
			$query .= " AND cms_consignment.cust_id =" . $data['customer_status'];
		}
		else
		{
			$query .= " cms_consignment.cust_id =" . $data['customer_status'];

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





	$query .= " GROUP BY cms_consignment.consign_id ORDER BY internal_updated_at ASC";
	//return $query;

	$db->query($query);

	$consignmentList = array();

	if($db->get_num_rows() != 0)
	{
		while($result = $db->fetch_array())
		{
			$date = date_create($result["consign_status_last_update_date"]);
		    $date = date_format($date,"d F g:iA");
		    $internal_date = date_create($result["internal_updated_at"]);
		    $internal_date = date_format($internal_date,"d F g:iA");

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

			$consignmentData = array(
							"consignment_id"=>$result["consign_id"]
							,"branch_code"=>$branch_code
							,"branch_name"=>$branch_name
							,"consignment_type"=>$result['consign_type']
                            ,"consignment_date"=>$result["consign_date"]
                            ,"delivery_date"=>$result["delivery_date"]
                            ,"cust_company_name"=>$result["cust_company_name"]
                            ,"grand_total"=>$total_amount
                            ,"consignment_status"=>$result["consign_status"]
                            ,"packing_status"=>$packing_status
                            ,"cancel_status"=>$result["cancel_status"]
                            ,"sales_agent"=>$result['staff_code']
                            ,"consignment_status_last_update_date"=>$date
                            ,"consignment_status_last_update_by"=>$result["consign_status_last_update_by"]
                            ,"internal_updated_at"=>$internal_date
                            ,"consignment_delivery_note"=>$result["consign_delivery_note"]
							,"delivery_info"=>$result["message"]
					);

			$consignmentList[] = $consignmentData;
		}

		$json_data['data'] = $consignmentList;
		$json_data['delivery_info'] = $delivery_info;
		$json_data["enable_deliver_date"]=$enable_deliver_date;
	}
	else
	{
		$json_data = array(
                   "success"=>'1'
                  ,'message'=>"No consignments found"
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
