<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

session_start();
header('Content-Type: text/plain; charset="UTF-8"');
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once('./model/DB_class.php');


function _checkNull ($value){
	if($value == 'null' || $value == 'undefined' || !$value){
		return '';
	}
	return $value;
}

function getConsignmentWithView($data){
	
	$_SESSION["_odate_from"] 	= _checkNull($data['dateFrom']);
    $_SESSION["_odate_to"] 		= _checkNull($data['dateTo']);
    $_SESSION["_ddate_from"] 	= _checkNull($data['deliveryDateFrom']);
    $_SESSION["_ddate_to"] 		= _checkNull($data['deliveryDateTo']);
    $_SESSION["_consign_status"]= _checkNull($data['consign_status']);
    $_SESSION["_view_cancel"] 	= _checkNull($data['showCancel']);
	$_SESSION["_cust_id"] 		= _checkNull($data['customer_status']);

	$_SESSION["_consign_backlink"] 		= _checkNull($data['backlink']);
	

	$config = parse_ini_file('../config.ini',true);
	$delivery_info = $config['delivery_info'];
	$transfer_check = $config['transfer_check'];
	$enable_deliver_date = $config['enable_deliver_date'];
	$gst = floatval($config['gst_rate']);

	$client = $data['client'];

	$settings                   = $config[$client];

    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
	$db_name                    = $settings['db'];

	$db = new DB();
	$con_1 = $db->connect_with_given_connection($db_user,$db_pass,$db_name);

	$db2 = new DB();
	$con_2 = $db2->connect_with_given_connection($db_user,$db_pass,$db_name);

	$json_data['connection'] = json_encode(array(
		"user"=>$db_user,
		"password"=>$db_pass,
		"db"=>$db_name,
		"client"=>$client,
		"con_success"=>$con_1,
		"con2_success"=>$con_2
	));

	$clientsWithBranches = $config['branch_module'];
	$branch_query_select = "";
	$branch_query_join = "";

	if(in_array($data['client'],$clientsWithBranches)){
		$branch_query_select = "cms_consignment.branch_code,cms_customer_branch.branch_name,";
		$branch_query_join = "LEFT JOIN cms_customer_branch on cms_customer_branch.branch_code = cms_consignment.branch_code";
	}

	$query = '';

	$query = "SELECT cms_consignment.consign_id,cms_consignment.grand_total,".$branch_query_select."salesperson_id,consign_date,delivery_date,grand_total,consign_status,cms_consignment.cancel_status,consign_status_last_update_by,consign_status_last_update_date,internal_updated_at,cust_company_name,consign_delivery_note,staff_code,cms_delivery_info_consignment.message, COALESCE(SUM(CASE WHEN (cms_consignment_item.cancel_status = 0 AND(cms_consignment_item.packing_status = 1 OR cms_consignment_item.packing_status = 0))
        THEN cms_consignment_item.sub_total ELSE 0 END),0) AS sub_total,
		SUM( cms_consignment_item.packing_status = 0 AND cms_consignment_item.cancel_status = 0) AS notpacked,
		SUM( (cms_consignment_item.packing_status = 2 OR cms_consignment_item.packing_status = 3) AND cms_consignment_item.cancel_status = 0 ) AS nostock  FROM cms_consignment LEFT JOIN cms_consignment_item ON (cms_consignment.consign_id = cms_consignment_item.consign_id ) LEFT JOIN cms_login ON cms_consignment.salesperson_id = cms_login.login_id ".$branch_query_join." LEFT JOIN cms_delivery_info_consignment on cms_consignment.consign_id = cms_delivery_info_consignment.consign_id where cms_consignment.salesperson_id = '{$data['salespersonId']}' AND ";

	$decider = 0;

	if($data['dateFrom'] == ''){
		$data['dateFrom'] = '1900-01-01';
		$data['dateTo'] = date("Y-m-d");
	}

	if($data['dateTo'] == ''){
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
			$query .= " AND cms_consignment.consign_status =" . $data['consign_status'];
		}
		else
		{
			$query .= " cms_consignment.consign_status =" . $data['consign_status'];

			$decider = 1;
		}
	}

	if($data['showCancel'] == 0){
		$query .= " AND cms_consignment.cancel_status = 0";
	}
	elseif($data['showCancel'] == 1){
		$query .= " AND cms_consignment.cancel_status = 2";
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

	$query .= " GROUP BY cms_consignment.consign_id order BY internal_updated_at ASC";

	$db->query($query);

	$consignmentList = array();

	$t_view='<p style="text-align:center; line-height: 25px;" class="dropdown-div text_white"> Transferred consignments </p>';

	$c_view='<p style="text-align:center; line-height: 25px;" class="dropdown-div text_white"> Confirmed consignments </p>';

	$a_view='<p style="text-align:center; line-height: 25px;" class="dropdown-div text_white"> Active consignments </p>';

	$check = 0;
	$checkActiveCart = 0;
	$checkConfirmed = 0;
	$checkTransferred = 0;

	$total_sales=0;
	while($result = $db->fetch_array())
	{	
		$totalQuery="SELECT COALESCE(SUM(sub_total),0) AS sub_total FROM cms_consignment_item WHERE cancel_status = 0 AND (packing_status = 1 OR packing_status = 0) AND consign_id = '" . mysql_real_escape_string($result['consign_id']) . "'";
		$real_total = $db2->query($totalQuery);
		$row = mysql_fetch_row($real_total);
		$real_total = $row[0];
		$total_sales+=$real_total;

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

		$packing_status = intval($packing_status);
		if($packing_status == 1){
			$packing_status = "Completed";
		}else if($packing_status == 2){
			$packing_status = "Completed (Partially No Stock)";
		}else{
			$packing_status = "Not Picked";
		}

		$color = "black";

		if(intval($result['cancel_status']) == 2){
			$packing_status = "Cancelled";
			$color = "rgb(255,85,45)";
		}

		if($result['consign_status']=='0'){

			$a_view .= '
			<div style="cursor: pointer;">

				<div onclick="window.location=\'c_consignment_details.php?consignmentId='.$result["consign_id"].'&client='.$client.'\'">
					<p class="title" > '.$result["cust_company_name"].' </p>
					<p class="dates" > '.$result["consign_date"].' | '.$result["delivery_date"].' </p>
					<p class="message non_important_text" > '.$result["consign_delivery_note"].' </p>';

			$a_view .='</div>';
			
			if($result["message"]){
				$a_view .='<button onclick="showMessage(\''.$result["message"].'\')" class="radius non_important_text delivery-buttons" style={font-width: 10vw;}> Delivery Info </button>';
			}else{
				$a_view .='<button data-message="'.$result["message"].'" class="radius non_important_text no-message" style={font-width: 10vw;}> Delivery Info </button>';
			}

			$a_view .='<p class="non_important_text" style=" display:inline; color:'.$color.'"> '.$packing_status.' | <strong> RM'.number_format($real_total,2).'</strong></p>
							

					<div class="divider"></div>
						<hr>

					</div>
				';
			$checkActiveCart++;
		}elseif($result['consign_status']=='1'){

			$c_view .= '
			<div style="cursor: pointer;">

				<div onclick="window.location=\'c_consignment_details.php?consignmentId='.$result["consign_id"].'&client='.$client.'\'">
					<p class="title" > '.$result["cust_company_name"].' </p>
					<p class="dates" > '.$result["consign_date"].' | '.$result["delivery_date"].' </p>
					<p class="message non_important_text" > '.$result["consign_delivery_note"].' </p>';
			
			$c_view .='</div>';

			if($result["message"]){
				$c_view .='<button onclick="showMessage(\''.$result["message"].'\')" class="radius non_important_text delivery-buttons" style={font-width: 10vw;}> Delivery Info </button>';
			}else{
				$c_view .='<button data-message="'.$result["message"].'" class="radius non_important_text no-message" style={font-width: 10vw;}> Delivery Info </button>';
			}

			$c_view .='<p class="non_important_text" style=" display:inline; color:'.$color.'"> '.$packing_status.' | <strong> RM'.number_format($real_total,2).'</strong></p>

					<div class="divider"></div>
						<hr>

					</div>
				';
			$checkConfirmed++;
		}elseif($result['consign_status']=='2')	{
				$t_view .= '
				<div style="cursor: pointer;">

					<div onclick="window.location=\'c_consignment_details.php?consignmentId='.$result["consign_id"].'&client='.$client.'\'">
						<p class="title" > '.$result["cust_company_name"].' </p>
						<p class="dates" > '.$result["consign_date"].' | '.$result["delivery_date"].' </p>
						<p class="message non_important_text" > '.$result["consign_delivery_note"].' </p>';

				$t_view .='</div>';

			if($result["message"]){
				$t_view  .='<button onclick="showMessage(\''.$result["message"].'\')" data-message="'.$result["message"].'" class="radius non_important_text delivery-buttons" style={font-width: 10vw;}> Delivery Info </button>';
			}else{
				$t_view  .='<button data-message="'.$result["message"].'" class="radius non_important_text no-message" style={font-width: 10vw;}> Delivery Info </button>';
			}

				$t_view  .='<p class="non_important_text" style=" display:inline; color:'.$color.'"> '.$packing_status.' | <strong> RM'.number_format($real_total,2).'</strong></p>

					<div class="divider"></div>
					<hr>

				</div>
			';
			$checkTransferred++;
		}

		$check++;
	}
	$checkConfirmed === 0 ? $c_view = '' : $c_view;
	$checkTransferred === 0 ? $t_view = '' : $t_view;
	$checkActiveCart === 0 ? $a_view = '' : $a_view;
	
	$view=$a_view.$c_view.$t_view;
	
	if(!$data['showCancel']){
		$view.= '<center class="total-sales radius"> Total Consignments: RM'.number_format($total_sales,2).'  </center>';
	}

	if($check !== 0){
		$consignmentList[] = $view;
		$json_data['no_result'] = 0;
	}else{
		$json_data['no_result'] = 1;
	}

	$json_data['data'] = $consignmentList;
	$json_data['delivery_info'] = $delivery_info;
	$json_data["enable_deliver_date"]=$enable_deliver_date;
	$json_data['query'] = $query;

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

	return json_encode($json_data);
}
?>
