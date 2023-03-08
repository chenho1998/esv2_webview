<?php
session_start();
header('Content-Type: text/plain; charset="UTF-8"');
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once('./model/DB_class.php');

function _isNull ($value){
	if($value == 'null' || $value == 'undefined' || !$value){
		return '';
	}
	return $value;
}

function getOrderWithView($data){

	$_SESSION["_search_input"] 	= _isNull($data['searchinput']);
	$_SESSION["_odate_from"] 	= _isNull($data['dateFrom']);
    $_SESSION["_odate_to"] 		= _isNull($data['dateTo']);
    $_SESSION["_ddate_from"] 	= _isNull($data['deliveryDateFrom']);
    $_SESSION["_ddate_to"] 		= _isNull($data['deliveryDateTo']);
    $_SESSION["_order_status"] 	= $data['order_status'];
    $_SESSION["_view_cancel"] 	= _isNull($data['showCancel']);
	$_SESSION["_cust_id"] 		= _isNull($data['customer_status']);
	$_SESSION["_backlink"]		= _isNull($data['backlink']);
	$_SESSION["_salesperson_id"]= _isNull($data['salesperson_id']);	
	$_SESSION['_customer_name']	= _isNull($data['customer_name']);
	$_SESSION['_case_name']		= _isNull($data['case_name']);
	$_SESSION['_status_name']	= _isNull($data['status_name']);
	$_SESSION['_title_name']	= _isNull($data['title_name']);
	$_SESSION['_ship_via']		= _isNull($data['ship_via']);
	
	$config = parse_ini_file('../config.ini',true);
	$delivery_info = $config['delivery_info'];
	$enable_deliver_date = $config['enable_deliver_date'];
	$delivery_module = $config['API']['delivery_module'];
	$order_asc = $config['Order_ASC'];
	$order_by_column =  $config['Order_By_Column'];

	$gst = floatval($config['gst_rate']);
	$client = $data['client'];
	$isAdmin = ($data['salespersonId'] == '0') || !is_numeric($data['salespersonId'] || $data['salespersonId'] == 0);

	$product_group_config = $config['Product_Group'];
	$allow_product_group = in_array($client,$product_group_config['product_group']);

	$constructor_job = $config['Constructor_Job'];
	$allow_constructor = in_array($client,$constructor_job['constructor_job']);

	$sql_accounting = $config['SQL'];
	$sql_client = in_array($client,$sql_accounting['sql_client']);

	$issearchinput			    = !empty($data['searchinput']);
	$isCustomer    = !empty($data['customer_code']);
	$settings                   = $config[$client];
    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
	$db_name                    = $settings['db'];
	$db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

	$currency					= isset($settings['currency']) ? $settings['currency'] : 'RM';

	$db = new DB();
	$con_1 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);

	$db2 = clone $db;

	$db3 = clone $db2;

	$json_data['connection'] = json_encode(array(
		"user"=>$db_user,
		"password"=>$db_pass,
		"db"=>$db_name,
		"client"=>$client,
		"con_success"=>$con_1
	));

	$cart_module = array();
	$order_list_module = array();
	$module_settings = array();

	$result = mysql_query("SHOW COLUMNS FROM `cms_order` LIKE 'warehouse_code'");
	$exists = (mysql_num_rows($result))?TRUE:FALSE;
	if(!$exists){
		$db->query("alter table cms_order add warehouse_code varchar(100) null;");
	}

	$db->query("SELECT status FROM cms_mobile_module WHERE module = 'app_restrictions'");
	while($result = $db->fetch_array()){
		$module_settings = $result['status'];
	}
	$module_settings = json_decode($module_settings,true);
	$cart_module = isset($module_settings['cart']) ? $module_settings['cart'] : array();
	// $order_list_module = isset($module_settings['order_list']) ? $module_settings['order_list'] : array();

	$doc_type					= $data['doc'];

	$order_list_module = isset($module_settings[$doc_type]) ? $module_settings[$doc_type] : array();
	if($order_list_module){
		$order_list_module = $order_list_module['cart'];
	}

	if(!empty($doc_type)){
		$result = $db->query("SHOW COLUMNS FROM `cms_order` LIKE 'doc_type'");
		$exists = (mysql_num_rows($result))?TRUE:FALSE;
		if($exists){
			$doc_type				= " AND doc_type = '{$doc_type}'";
		}else{
			$doc_type = '';
		}
	}

	if($client == 'fuiwah' && $data['doc'] == 'quotation'){
		$doc_type = " AND doc_type IN ('invoice','sales','quotation') ";
	}

	$select_order_approval = '';
	$result = $db->query("SHOW COLUMNS FROM `cms_order` LIKE 'order_approved'");
	$order_approved = (mysql_num_rows($result))?TRUE:FALSE;
	if($order_approved){
		$select_order_approval	= " cms_order.order_approved, cms_order.order_approver, cms_order.order_comment,";
	}

	$result = $db->query("SHOW COLUMNS FROM `cms_order` LIKE 'order_from'");
	$order_from_exists = (mysql_num_rows($result))?TRUE:FALSE;
	$order_from_query = $order_from_exists?'cms_order.order_from,':'';

	$result = $db->query("SELECT * FROM cms_order_item LIMIT 1");
	$shouldMergeTables = (mysql_num_rows($result))?TRUE:FALSE;
	$branch_query_select = "";
	$branch_query_join = "";

	$result = $db->query("SHOW COLUMNS FROM `cms_order` LIKE 'branch_code'");
	$branch_exists = (mysql_num_rows($result))?TRUE:FALSE;

	if($branch_exists){
		$branch_query_select = "cms_order.branch_code,cms_customer_branch.branch_name,";
		$branch_query_join = "LEFT JOIN cms_customer_branch on cms_customer_branch.branch_code = cms_order.branch_code AND cms_order.cust_code = cms_customer_branch.cust_code";
	}

	$result = $db->query("SHOW COLUMNS FROM `cms_order` LIKE 'packed_by'");
	$packing_exists = (mysql_num_rows($result))?TRUE:FALSE;
	$packing_select = '';
	// if($packing_exists){
	// 	$packing_select = 'cms_order.packing_status,cms_order.packed_by,cms_order.pack_confirmed_by,';
	// }

	$result = $db->query("SHOW TABLES LIKE '%cms_statement_signature%'");
	$statement_tbl_exists = (mysql_num_rows($result))?TRUE:FALSE;
	$statement_tbl_cond = "";
	$statement_tbl_select = "";
	// if($statement_tbl_exists){
	// 	$statement_tbl_cond = " LEFT JOIN cms_statement_signature sg ON sg.doc_id = cms_order.order_id ";
	// 	$statement_tbl_select = " sg.id, sg.is_signed, ";
	// }

	$result = $db->query("SHOW TABLES LIKE '%cms_order_cutting_date%'");
	$order_cutting_date_exists = (mysql_num_rows($result))?TRUE:FALSE;
	$order_cutting_date_query = "";
	if($order_cutting_date_exists){
		$order_cutting_date_query =",(SELECT cutting_remark FROM cms_order_cutting_date WHERE order_id = cms_order.order_id ORDER BY updated_at DESC LIMIT 1) AS cutting_remark, (SELECT cutting_status FROM cms_order_cutting_date WHERE order_id = cms_order.order_id ORDER BY updated_at DESC LIMIT 1) AS cutting_status,(SELECT cutting_title FROM cms_order_cutting_date WHERE order_id = cms_order.order_id ORDER BY updated_at DESC LIMIT 1) AS cutting_title, cms_order.order_reference";
	}

	$result = mysql_query("SHOW COLUMNS FROM `cms_order` LIKE 'order_approver'");
	$exists = (mysql_num_rows($result))?TRUE:FALSE;
	if(!$exists){
		$db->query("alter table cms_order add order_approver varchar(20) null;");
	}

	$result = mysql_query("SHOW COLUMNS FROM `cms_order` LIKE 'order_comment'");
	$exists = (mysql_num_rows($result))?TRUE:FALSE;
	if(!$exists){
		$db->query("alter table cms_order add order_comment varchar(200) null;");
	}

	$result = $db->query("SHOW TABLES LIKE '%cms_salesperson_uploads%'");
	$salesperson_uploads_exists = (mysql_num_rows($result))?TRUE:FALSE;

	
	$query = "SELECT {$statement_tbl_select} {$select_order_approval} {$packing_select} cms_order.cust_code,cms_order.cust_id,cms_login.name, ".$order_from_query."cms_order.order_id,cms_order.grand_total,cms_order.last_print,".$branch_query_select."cms_order.salesperson_id,order_date,delivery_date,grand_total,order_status,cms_order.cancel_status,order_status_last_update_by,order_status_last_update_date,internal_updated_at,cust_remark,cms_customer.cust_company_name,order_delivery_note,staff_code, cms_order.others_order_status".$order_cutting_date_query." FROM cms_order LEFT JOIN cms_order_item ON (cms_order.order_id = cms_order_item.order_id ) LEFT JOIN cms_customer on cms_customer.cust_code = cms_order.cust_code LEFT JOIN cms_login ON cms_order.salesperson_id = cms_login.login_id ".$branch_query_join."  {$statement_tbl_cond}
	where ";
	$order_table = "cms_order";
	if($allow_constructor){
		$query = " SELECT cms_acc_existing_order.*, 
		(SELECT cutting_remark FROM cms_acc_order_cutting_date WHERE order_id = cms_acc_existing_order.order_id ORDER BY updated_at DESC LIMIT 1) AS cutting_remark, 
		(SELECT cutting_status FROM cms_acc_order_cutting_date WHERE order_id = cms_acc_existing_order.order_id ORDER BY updated_at DESC LIMIT 1) AS cutting_status,
		(SELECT cutting_title FROM cms_acc_order_cutting_date WHERE order_id = cms_acc_existing_order.order_id ORDER BY updated_at DESC LIMIT 1) AS cutting_title FROM cms_acc_existing_order LEFT JOIN cms_customer_branch ON cms_customer_branch.cust_code = cms_order.cust_code WHERE ";

		$order_table = "cms_acc_existing_order";
	}

	if($issearchinput){
		$query .= "(cms_order.cust_company_name LIKE '%{$data['searchinput']}%' OR cms_customer_branch.branch_name LIKE '%{$data['searchinput']}%') AND";
	}
	
	$decider = 0;
	
	if($isCustomer){
		$query .= " ".$order_table.".cust_code ='" .$data['customer_code']."'";
		$decider = 1;
	}else if($data['salesperson_id'] != '' && $data['salesperson_id'] != '0'){
		$query .= " ".$order_table.".salesperson_id IN (" . $data['salesperson_id'].")";
		$decider = 1;
	}

	if($data['dateFrom'] == ''){
		$data['dateFrom'] = '1900-01-01';
		$data['dateTo'] = date("Y-m-d");
	}

	/* if($data['dateTo'] == ''){
		if($decider == 1)
		{
			$query .= " AND order_date >= '" . $data['dateFrom'] . " 00:00:00' AND order_date <= '". $data['dateFrom'] . " 23:59:59'";
		}
		else
		{
			$query .= " order_date = '" . $data['dateFrom'] . " 00:00:00' AND order_date <= '". $data['dateFrom'] . " 23:59:59'";
			$decider = 1;
		}
	}else{
		if($decider == 1)
		{
			$query .= " AND order_date >= '" . $data['dateFrom'] . "  00:00:00' AND order_date <= '" . $data['dateTo'] . " 23:59:59'";
		}
		else
		{
			$query .= " order_date >= '" . $data['dateFrom'] . "  00:00:00' AND order_date <= '" . $data['dateTo'] . " 23:59:59'";
			$decider = 1;
		}
	} */

	if($data['order_status'] != '')
	{
		if($decider == 1)
		{
			$query .= " AND ".$order_table.".order_status =" . $data['order_status'];
		}
		else
		{
			$query .= " ".$order_table.".order_status =" . $data['order_status'];
			$decider = 1;
		}
	}
	$cancel_and = '';
	if($decider == 1){
		$cancel_and = ' and ';
	}
	if($data['showCancel'] == 0){
		$query .= " {$cancel_and} ".$order_table.".cancel_status = 0";
		$decider = 1;
	}
	elseif($data['showCancel'] == 1){
		$query .= " {$cancel_and} ".$order_table.".cancel_status > 0";
		$decider = 1;
	}

	if($data['customer_status'] != '')
	{
		if($decider == 1)
		{
			$query .= " AND ".$order_table.".cust_code ='" . $data['customer_status']."'";
		}
		else
		{
			$query .= " ".$order_table.".cust_code ='" . $data['customer_status']."'";
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

	if($data['deliveryDateFrom'] == '')
	{
		$and = '';
		if($decider == 1){
			$and = ' AND ';
		}
		$query .= " {$and} date(delivery_date) >= current_date() AND date(delivery_date) <= current_date() ";
		$decider = 1;
	}

	if($data['case_name'] != '')
	{
		if($decider == 1)
		{
			$query .= " AND (".$order_table.".order_id LIKE '%" . $data['case_name'] ."%' OR ".$order_table.".order_reference LIKE '%". $data['case_name'] ."%' OR ".$order_table.".order_delivery_note LIKE '%". $data['case_name'] ."%')";
		}
		else
		{
			$query .= " (".$order_table.".order_id LIKE '%" . $data['case_name'] ."%' OR ".$order_table.".order_reference LIKE '%". $data['case_name'] ."%' OR ".$order_table.".order_delivery_note LIKE '%". $data['case_name'] ."%')";
			$decider = 1;
		}
	}

	if($data['wh_code'] != ''){
		if($decider == 1)
		{
			$query .= " AND ".$order_table.".warehouse_code LIKE '%".$data['wh_code']."%'";
		}
		else
		{
			$query .= " ".$order_table.".warehouse_code LIKE '%".$data['wh_code']."%'";
			$decider = 1;
		}
	}

	$query .= " {$doc_type} GROUP BY ".$order_table.".order_id ORDER BY";

	$query .= in_array($data['client'],$order_by_column['order_id']) ? " ".$order_table.".order_id" : " ".$order_table.".delivery_date";

	$query .= in_array($data['client'],$order_asc['order_asc']) ? " ASC" : " DESC";

	file_put_contents('query.log',$query);

	$db->query($query);

	$orderList = array();

	$t_view='<p style="text-align:center; line-height: 25px;" class="dropdown-div text_white"> Transferred orders </p>';
	$c_view='<p style="text-align:center; line-height: 25px;" class="dropdown-div text_white"> Confirmed orders </p>';
	$a_view='<p style="text-align:center; line-height: 25px;" class="dropdown-div text_white"> Active orders </p>';
	$p_view= '<p style="text-align:center; line-height: 25px;" class="dropdown-div text_white"> Posted orders </p>';
	$j_view='<p style="text-align:center; line-height: 25px;" class="dropdown-div text_white"> Active Jobs </p>';

	$check = 0;
	$checkActiveCart = 0;
	$checkConfirmed = 0;
	$checkTransferred = 0;
	$checkPosted = 0;
	$checkJob = 0;
	$total_sales=0;

	while($result = $db->fetch_array())
	{
		$label_customer_name = $result['cust_company_name'].("<br><p style='font-size:12px;font-weight:bold;line-height:1em;color:grey;'>" . $result['cust_remark']."</p>");
		/* if($client == 'fuiwah'){
			$db3->query("select * from cms_customer_branch where cust_id = '{$result['cust_id']}' and branch_active = 1 order by branch_id desc;");
			if($db3->get_num_rows() > 0)
			{
				while($result2 = $db3->fetch_array())
				{
					$tmpname = '';
					$tmpnameArr = explode(')',$result2['branch_name']);
					if(count($tmpnameArr)>1){
						$tmpname = $tmpnameArr[1];
					}else{
						$tmpname = $result2['branch_name'];
					}
					if(strtolower(trim($tmpname)) != 'billing'){
						$label_customer_name = $tmpname;
						break;
					}
				}
			}
		} */
		// $db3->query("select trim(replace(branch_name,concat('(',branch_code,')'),'')) as branch_name from cms_customer_branch where cust_id = '{$result['cust_id']}' and instr(branch_name,'billing') = 0 and branch_active = 1 order by branch_id desc;");
		// if($db3->get_num_rows() > 0)
		// {
		// 	while($result2 = $db3->fetch_array())
		// 	{
		// 		//$result2['branch_name'] = trim(str_replace('('.$result2['branch_code'].')','',$result2['branch_name']));
		// 		if(!str_compare($result2['branch_name'],$result['cust_remark'])){
		// 			$label_customer_name .= ("<br><p class='title2' style='font-size:12px;font-weight:bold;line-height:1em;font-style:italic'>" . $result2['branch_name']."</p>");
		// 			break;
		// 		}
		// 	}
		// }
		if($order_cutting_date_exists){

			$data['status_name'] = $data['status_name'] == 'ALL' ? '' : $data['status_name'];
			if($data['status_name']){

				if($data['status_name'] == 'PENDING'){
					if(strtoupper($result['cutting_status']) != '' && strtoupper($result['cutting_status']) != $data['status_name']){
						continue;
					}
				}else{
					if(strtoupper($result['cutting_status']) != $data['status_name']){
						continue;
					}
				}
			}

			if($data['title_name']){
				if(strtoupper($result['cutting_title']) != $data['title_name']){
					continue;
				}
			}

			if($data['ship_via']){
				if($data['ship_via'] == 'NONE'){
					if(strtoupper($result['order_delivery_note']) != ''){
						continue;
					}
				}else{
					if(strtoupper($result['order_delivery_note']) != $data['ship_via']){
						continue;
					}
				}
			}

		}

		$picker_name = '';
		$picking_status = 0;
		if($packing_exists){
			$picker_name = strtoupper($result['packed_by']);
			$picking_status = $result['packing_status'];
			if($picking_status != 1){
				$picker_name = '';
			}else{
				$picker_name = "({$picker_name})";
			}
		}

		$statement_pdf_btn = '';

		if($statement_tbl_exists){
			if($result['is_signed']){
				$statement_pdf_btn = 
				'<button id="pdf_button" class="img_button" style="margin-left:5px;font-width: 10vw;" onclick="window.ReactNativeWebView.postMessage(`statement_id:'.$result['id'].'`)">Reconcl.</button>';
			}else{
				$statement_pdf_btn = '';
				// $statement_pdf_btn = '<button class="radius non_important_text no-message" style="margin-left:5px;font-width: 10vw;"> Reconcl. </button>';
			}
		}

		if($isCustomer){
			$statement_pdf_btn = '';
		}

		/* if($shouldMergeTables){
			$totalQuery="SELECT COALESCE(SUM(sub_total),0) AS sub_total FROM cms_order_item WHERE cancel_status = 0 AND (packing_status = 1 OR packing_status = 0) AND order_id = '" . mysql_real_escape_string($result['order_id']) . "'";
			$real_total = $db2->query($totalQuery);
			$row = mysql_fetch_row($real_total);
			$real_total = $row[0];
		}else{
			$real_total = $result['grand_total'];
		} */
		$totalQuery="SELECT sub_total, quantity, parent_code FROM cms_order_item WHERE cancel_status = 0 AND (packing_status = 1 OR packing_status = 0) AND order_id = '" . mysql_real_escape_string($result['order_id']) . "' ORDER BY order_item_id";

		$db2->query($totalQuery);
		$parents = array('CATALOG','PACKAGE','FOC','GROUP');
		$real_total = 0;
		if($db2->get_num_rows() != 0)
		{
			while($obj = $db2->fetch_array())
			{
				if(in_array($obj['parent_code'],$parents)){
					$parent_code = $obj['parent_code'];
					$parent_quantity = $parent_code == 'PACKAGE'  && !$sql_client ? $obj['quantity'] : 1;
					$real_total +=  $parent_code == 'FOC' ? $obj['sub_total'] : 0;
				}else{
					if($obj['parent_code'] == ''){
						$real_total += $obj['sub_total'] * 1;

					}else{
						$real_total += $obj['sub_total'] * $parent_quantity;
					}
				}
			}
		}
		

		/* if($allow_product_group){
			$check_isParent = "SELECT parent_code,sub_total FROM cms_order_item WHERE cancel_status = 0 AND (packing_status = 1 OR packing_status = 0) AND order_id = '" . mysql_real_escape_string($result['order_id']) . "' AND isParent = 1";
			$db2->query($check_isParent);
			if($db2->get_num_rows()>0){
				while($isParent = $db2->fetch_array())
				{
					$db3->query("SELECT * FROM cms_order_item WHERE cancel_status = 0 AND (packing_status = 1 OR packing_status = 0) AND order_id = '" . mysql_real_escape_string($result['order_id']) . "' AND isParent = 0 AND parent_code = '".$isParent['parent_code']."'");
					if($db3->get_num_rows()<=0){
						$real_total += $isParent['sub_total'];
					}
				}
			}
		} */

		$date = date_create($result["order_status_last_update_date"]);
		$date = date_format($date,"d F g:iA");
		$internal_date = date_create($result["internal_updated_at"]);
		$internal_date = date_format($internal_date,"d F g:iA");
		$total_amount = $result['sub_total'];
		$total_amount = $total_amount*(1 + $gst);

		$packing_status = $result['notpacked'];
		$nostock_status = $result['nostock'];
		$last_print = $result['last_print'];

		$result["order_date"] = date_create($result["order_date"]);
		$result["order_date"] = date_format($result["order_date"],"d M Y g:iA");
		
		$result["delivery_date"] = date_create($result["delivery_date"]);
		$result["delivery_date"] = date_format($result["delivery_date"],"d M Y g:iA");

		if($order_cutting_date_exists){
			$real_total = $result['grand_total'];
			$result["order_date"] = date_create($result["order_date"]);
			$result["order_date"] = date_format($result["order_date"],"d M Y");
		}else{
			$result["delivery_date"] = date_create($result["delivery_date"]);
			$result["delivery_date"] = date_format($result["delivery_date"],"d M Y");
		}

		$total_sales+=$real_total;

		if ($packing_status > 0){
			$packing_status = 0;
		}
		else
		{
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

		if($branch_exists){
			$branch_code = $result['branch_code'];
			$branch_name = trim($result['branch_name']);
			$branch_name = str_replace(array($branch_code,'(',')'),'',$branch_name);
		}

		$packing_status = intval($packing_status);
		if($packing_status == 1){
			$packing_status = "Completed";
		}else if($packing_status == 2){
			$packing_status = "Completed (Partially No Stock)";
		}else{
			$packing_status = "Not Picked";
		}

		if($order_cutting_date_exists){
			$result['order_delivery_note'] = $result['order_delivery_note'] ? $result['order_delivery_note'] : '-' ;
			$result['delivery_date'] = $result['order_delivery_note'] . ' | ' . $result['delivery_date'] ;
			$result['order_delivery_note'] = '';
			$packing_status = '';
		}


		$color = "black";
		if(intval($result['cancel_status']) == 2){
			$packing_status = "Cancelled";
			$color = "rgb(255,85,45)";
		}

		if($packing_status){
			$packing_status = ' | '.$packing_status;
		}

		/* if($picking_status == 1){
			$packing_status = "{$packing_status} {$picker_name}";
			$color = "green";
			$color .= ";font-weight:bold;font-size:12px;";
		} */

		$createTaskJSON = '{ "data": {"so_no":"@order", "client":"@client", "customer_name":"@cust", "customer_code":"@cust_code", "delivery_address":"@delivery", "isAdmin":"1", "redirect_id":"@userId", "redirect_client":"@client" }}';

		$viewTaskJSON = '{ "data": {"so_no":"@order", "client":"@client", "customer_name":"", "customer_code":"", "delivery_address":"", "isAdmin":"0", "redirect_id":"@userId", "redirect_client":"@client" }}';

		$cust = $result["cust_company_name"];
		$cust_code = $result['cust_code'];
		$shipping = $result['shipping'];
		$order = $result['order_id'];
		$orderFrom = $result["order_from"]? " | ".$result["order_from"] : "";
		$isMsgValid = true;

		$cutting_title = $result['cutting_title'] ? $result['cutting_title'] : "";
		$cutting_status = $result['cutting_status'] ? " | ".$result['cutting_status'] : " | -";
		$cutting_remark = $result['cutting_remark'] ? " | ".$result['cutting_remark'] : " | -";
		$cutting_remark = $result['cutting_title'] ? "<p style='color:grey;font-weight: bold;font-style: italic;'>".$cutting_title.$cutting_status.$cutting_remark : "";
		$order_reference = $result['order_reference'] ? " <p style='color:black;'>MST Number : ".$result['order_reference']."</p>" : "";

		// if ($result["warehouse_code"] != "--" && $result["warehouse_code"] != "----" && $result["warehouse_code"] != "") {
		// 	$warehouse_icon = '&ensp; <i class="fas fa-warehouse"></i> '.$result["warehouse_code"];
		// }else{
		// 	$warehouse_icon = '&ensp; <i class="fas fa-warehouse"></i> HQ';

		// }

		// if(trim() == '--'){
		// 	$warehouse_icon = '&ensp; <i class="fas fa-warehouse"></i> HQ';
		// }else{
		// 	$warehouse_icon = '&ensp; <i class="fas fa-warehouse"></i> '.$result["warehouse_code"];
		// }

		if($order_from_exists && $isCustomer ){
			if(strtolower($result["order_from"]) != 'c'){
				$isMsgValid = false;
			}			
		}

		if(empty($shipping)){
			$shipping = '';
		}

		$createTaskJSON = str_replace('@cust',$cust,$createTaskJSON);
		$createTaskJSON = str_replace('@cust_code',$cust_code,$createTaskJSON);
		$createTaskJSON = str_replace('@delivery',$shipping,$createTaskJSON);
		$createTaskJSON = str_replace('@userId',$data['salespersonId'],$createTaskJSON);
		$createTaskJSON = str_replace('@client',$data['client'],$createTaskJSON);
		$createTaskJSON = str_replace('@order',$order,$createTaskJSON);
		$createTaskJSON = htmlspecialchars($createTaskJSON);
		$viewTaskJSON	= str_replace("@client",$data['client'],$viewTaskJSON);
		$viewTaskJSON	= str_replace("@userId",$data['salespersonId'],$viewTaskJSON);
		$viewTaskJSON	= str_replace("@order",$order,$viewTaskJSON);
		$viewTaskJSON	= htmlspecialchars($viewTaskJSON);

		$display_company_name = $result['cust_code']. ' | '. $label_customer_name;//$result['cust_company_name'];

		if($salesperson_uploads_exists){
			$db2->query("SELECT * FROM cms_salesperson_uploads WHERE upload_bind_id ='".$result['order_id']."'");
			$img_div = '';
			if($db2->get_num_rows() > 0){
				$img_button = '<button  class="img_button" style="font-width: 10vw;" onclick="document.getElementById(\'img_'.$result['order_id'].'\').style.display=\'block\'">Attachment</button>';
				$img_div = '<div style="min-width:100%;min-height:100%;text-align:center;overflow-x: auto;" class="order_img" id ="img_'.$result['order_id'].'"><span class="close" onclick="document.getElementById(\'img_'.$result['order_id'].'\').style.display=\'none\'">&times;</span>';
				while($img = $db2->fetch_array())
				{
					$order_img = $img['upload_image'];
					if($img['upload_type_name'] == ''){ $img['upload_type_name'] = "-"; } 
					if($img['upload_remark'] == ''){ $img['upload_remark'] = "-"; } 
					$img_div .= '<a class="attachment"  title="' . str_replace('"', '\"', $img["upload_remark"]) . '"><img style="max-width:100%;max-height:100%;padding:5px;" src="'.$order_img.'"/></a>';
					
					$img_div .='<p style="font-weight:bold;color:white;">'.$img['upload_type_name'].'</p>';
					$img_div .='<p style="color:white;">'.$img['upload_remark'].'</p>';
				}
				$img_div .='</div>';
			}else{
				// $img_button = '<button class="radius non_important_text no-message" style="font-width: 10vw;"> Attachment </button>';
			}
		}else{
			// $img_button = '<button class="radius non_important_text no-message" style="font-width: 10vw;"> Attachment </button>';
		}

		$redRound = '';
		if($result['order_approved'] == '2'){
			$redRound = '<span class="line" style="background-color:red;margin-left:5px;margin-right:5px;"></span><b style="color:red;">BLOCKED</b>';
		}

		if($result['order_status']=='0'){

			$a_view .= '
			<div style="cursor: pointer;" class="parent-div">
				<div onclick="window.location=\'s_order_details.php?orderId='.$result["order_id"].'&client='.$client.'\'">
					<p class="title" style="color:grey;font-size:13px;line-height:0;margin-top:5px"> '.$result["order_id"].$orderFrom.'<strong style="font-size:14px;float:right;"> '.(in_array('grand_total',$order_list_module) ? '' : $currency.number_format($real_total,2).'').' </strong></p>
					<p class="title" > '.$display_company_name.(!empty($branch_name) ? " ({$branch_name})" : "").' </p>'.$order_reference.$cutting_remark.'
					<p class="dates" ><i class="fas fa-clipboard-list"></i> '.$result["order_date"].' &ensp;<i class="fas fa-shipping-fast"></i> '.$result["delivery_date"].$warehouse_icon.'</p>';

			if(strlen($result["order_delivery_note"])!=0){
				$a_view .= '<p class="message non_important_text" > '.$result["order_delivery_note"].' </p>';
			}
			
			if($isAdmin || strpos($data['salesperson_id'],',') !== false && $data['doc'] != 'credit'){
				$a_view .='<p class="message non_important_text" > '.$result["name"].$packing_status.'</p>
				</div>';
			} else {
				$a_view .= $packing_status.'</div>';
			}

			if(in_array($data['client'],$delivery_module)){
				/* $a_view .='<button data-json="'.$createTaskJSON.'" class="radius non_important_text delivery-buttons create-button" style={width:25%;font-width: 10vw;}> Create </button>';

				$a_view .='<button data-json="'.$viewTaskJSON.'" class="radius non_important_text delivery-buttons view-button" style={width:25%;font-width: 10vw;}> View </button>'; */

				$a_view  .='<p class="non_important_text" style="display:inline; color:'.$color.'"> <strong> '.(in_array('grand_total',$order_list_module) ? '' : $currency.number_format($real_total,2).'').' </strong></p>';
			}else{
				if($result["message"] && $isMsgValid){
					$a_view .='<button onclick="showMessage(\''.$result["message"].'\')" class="radius non_important_text delivery-buttons" style="font-width: 10vw; margin-right:5px;"> Delivery Info </button>';
				}else{
					$a_view .='<button data-message="'.$result["message"].'" class="radius non_important_text no-message" style="font-width: 10vw; margin-right:5px;"> Delivery Info </button>';
				}

				$a_view .= $img_button;
				$a_view .= $statement_pdf_btn;

				/* $a_view  .='<p class="non_important_text" style="display:inline; color:'.$color.'"> '.$packing_status.' | <strong style="font-size:14px"> '.(in_array('grand_total',$order_list_module) ? '' : $currency.number_format($real_total,2).'').' </strong></p>'; */

			}

			if(!empty($result["order_status_last_update_date"])){
				if(empty($result["order_status_last_update_by"])){
					$a_view .= '<p class="message non_important_text" >updated on '.$result["order_status_last_update_date"].' </p>';
				}else{
					$a_view .= '<p class="message non_important_text" > '.$result["order_status_last_update_by"].' updated on '.displaydate($result["order_status_last_update_date"]).' </p>';
				}				

			}

			if(!empty($last_print)){
				$a_view .= '<p class="message non_important_text"  style="color:green"> last printed at '.$last_print.' </p>';			
			}
			$a_view .= $img_div;
			$a_view .='
					<div class="divider"></div>
						<hr>
					</div>
				';
			$checkActiveCart++;

		}elseif($result['order_status']=='1'){

			$c_view .= '
			<div style="cursor: pointer;"  class="parent-div">
				<div onclick="window.location=\'s_order_details.php?orderId='.$result["order_id"].'&client='.$client.'\'">
					<p class="title" style="color:grey;font-size:13px;line-height:0;margin-top:5px"> '.$result["order_id"].$orderFrom.$redRound.' <strong style="font-size:14px;float:right;"> '.(in_array('grand_total',$order_list_module) ? '' : $currency.number_format($real_total,2).'').' </strong></p>
					<p class="title" > '.$display_company_name.(!empty($branch_name) ? " ({$branch_name})" : "").' </p>
					<p class="dates" ><i class="fas fa-clipboard-list"></i> '.$result["order_date"].' &ensp;<i class="fas fa-shipping-fast"></i> '.$result["delivery_date"].$warehouse_icon.'</p>';
			
			if(strlen($result["order_delivery_note"])!=0){
				$c_view .= '<p class="message non_important_text" > '.$result["order_delivery_note"].' </p>';
			}
			$break_p = '<br>';
			if($isCustomer){
				if(isset($result['order_approved']) || $result['order_approved'] == 0){
					$result['order_approved'] = intval($result['order_approved']);
					if($result['order_approved'] == 0){
						$c_view .= '
							<div class="load-wrapp">
								<div class="child_div">Approval Pending</div>
								<div class="load-2 child_div">
									<div class="line"></div>
									<div class="line"></div>
									<div class="line"></div>
								</div>
							</div>
							';
					}
					if($result['order_approved'] == 1){
						$c_view .= '
							<div class="load-wrapp">
								<div class="child_div">In progress</div>
								<div class="load-2 child_div">
									<div class="line" style="background-color:#3CB371"></div>
									<div class="line" style="background-color:#3CB371"></div>
									<div class="line" style="background-color:#3CB371"></div>
								</div>
							</div>
							';
					}
					if($result['order_approved'] == 2){
						$c_view .= '
							<div class="load-wrapp">
								<div class="child_div">Rejected</div>
								<div class="child_div">
									<div class="line" style="background-color:rgb(255,85,45)"></div>
									<div class="line" style="background-color:rgb(255,85,45)"></div>
									<div class="line" style="background-color:rgb(255,85,45)"></div>
								</div>
							</div>
							';
							if($result["order_comment"]){
								$c_view .= '<br><p class="message non_important_text" style="color:rgb(255,85,45)">Reason: '.$result["order_comment"].' </p>';
							}
						$break_p = '';
					}
				}
			}
			$assigned = '';
			$name = isset($result['name']) ? $result['name'].$packing_status : '';
			if($isCustomer){
				$label = 'Assigned To';
				if($result['order_approved'] == 2){
					$label = 'Commented By';
					$name = $result['order_approver'];
				}
				$assigned = '<label style="font-style:normal;color:grey;">'.$label.': </label>';
			}
			if($isAdmin && $data['doc'] != 'credit'){
				$c_view .= $break_p.'<p style="color:black;" class="non_important_text">'.$assigned.$name.' </p>
				</div>';
			}else {
				$c_view .='</div>';
			}

			if(in_array($data['client'],$delivery_module)){
				$c_view .='<button  data-json="'.$createTaskJSON.'" class="radius non_important_text delivery-buttons create-button" style={width:25%;font-width: 10vw;}> Create </button>';
				$c_view .='<button  data-json="'.$viewTaskJSON.'" class="radius non_important_text delivery-buttons view-button" style={width:25%;font-width: 10vw;}> View </button>';
				$c_view  .='<p class="non_important_text" style="display:inline; color:'.$color.'"> <strong> '.(in_array('grand_total',$order_list_module) ? '' : $currency.number_format($real_total,2).'').' </strong></p>';
			}else{
				if(!empty($select_order_approval) && !$isCustomer && $result['order_approved'] == 0 && $result["order_from"] == 'C'){
					$c_view .='<button data-client="'.$client.'" data-userid="'.$data['salesperson_id'].'"  data-orderid="'.$result['order_id'].'" class="radius non_important_text approve-button-st approve-button" style={width:25%;font-width: 10vw;}> Approve </button>';
					$c_view .='<button data-client="'.$client.'" data-userid="'.$data['salesperson_id'].'" data-orderid="'.$result['order_id'].'" class="radius non_important_text reject-button-st reject-button" style={width:25%;font-width: 10vw;}> Reject </button>';
				}else{
					if($result["message"] && $isMsgValid){
						$c_view .='<button onclick="showMessage(\''.$result["message"].'\')" class="radius non_important_text delivery-buttons" style="font-width: 10vw; margin-right:5px;"> Delivery Info </button>';
					}else{
						$c_view .='<button data-message="'.$result["message"].'" class="radius non_important_text no-message" style="font-width: 10vw; margin-right:5px;"> Delivery Info </button>';
					}
				}

				$c_view .= $img_button;
				$c_view .= $statement_pdf_btn;

				/* $c_view  .='<br><p class="non_important_text" style="display:inline; color:'.$color.'"> '.$packing_status.' | <strong style="font-size:14px"> '.(in_array('grand_total',$order_list_module) ? '' : $currency.number_format($real_total,2).'').' </strong></p>'; */
			}

			if(!empty($result["order_status_last_update_date"])){
				if(empty($result["order_status_last_update_by"])){
					$c_view .= '<p class="message non_important_text" >updated on '.$result["order_status_last_update_date"].' </p>';
				}else{
					$c_view .= '<p class="message non_important_text" > '.$result["order_status_last_update_by"].' updated on '.displaydate($result["order_status_last_update_date"]).' </p>';
				}				
			}

			if(!empty($last_print)){
				$c_view .= '<p class="message non_important_text"  style="color:green"> last printed at '.$last_print.' </p>';	
			}
			$c_view .= $img_div;
			$c_view .='
					<div class="divider"></div>
						<hr>
					</div>
				';
			$checkConfirmed++;
		}elseif(floatval($result['order_status']) == 2)	{
			$greenRound = '';
			/* if($result['order_status'] == '3'){
				$greenRound = '<span class="line" style="background-color:#3CB371;margin-left:5px;"></span>';
			} */
			$t_view .= '
				<div style="cursor: pointer;"  class="parent-div">
					<div onclick="window.location=\'s_order_details.php?orderId='.$result["order_id"].'&client='.$client.'\'">
						<p class="title" style="color:grey;font-size:13px;line-height:0;margin-top:5px"> '.$result["order_id"].$orderFrom.$greenRound.'<strong style="font-size:14px;float:right;"> '.(in_array('grand_total',$order_list_module) ? '' : $currency.number_format($real_total,2).'').' </strong> </p>
						<p class="title" > '.$display_company_name.(!empty($branch_name) ? " ({$branch_name})" : "").' </p>
						<p class="dates" ><i class="fas fa-clipboard-list"></i> '.$result["order_date"].' &ensp;<i class="fas fa-shipping-fast"></i> '.$result["delivery_date"].$warehouse_icon.'</p>';
			if($result["order_delivery_note"]){
				$t_view .= '<p class="message non_important_text" > '.$result["order_delivery_note"].' </p>';
			}
			if($isAdmin && $data['doc'] != 'credit'){
				$t_view .='<p class="message non_important_text" > '.$result["name"].$packing_status.' </p>
				</div>';
			}else {
				$t_view .='</div>';
			}

			if(in_array($data['client'],$delivery_module)){
				$t_view .='<button  data-json="'.$createTaskJSON.'" class="radius non_important_text delivery-buttons create-button" style={width:25%;font-width: 10vw;}> Create </button>';
				$t_view .='<button  data-json="'.$viewTaskJSON.'" class="radius non_important_text delivery-buttons view-button" style={width:25%;font-width: 10vw;}> View </button>';
				$t_view  .='<p class="non_important_text" style="display:inline; color:'.$color.'"> <strong> '.(in_array('grand_total',$order_list_module) ? '' : $currency.number_format($real_total,2).'').' </strong></p>';
			}else{
				if($result["message"] && $isMsgValid){
					$t_view .='<button onclick="showMessage(\''.$result["message"].'\')" class="radius non_important_text delivery-buttons" style="font-width: 10vw; margin-right:5px;"> Delivery Info </button>';
				}else{
					$t_view .='<button data-message="'.$result["message"].'" class="radius non_important_text no-message" style="font-width: 10vw; margin-right:5px;"> Delivery Info </button>';
				}
				
				$t_view .= $img_button;
				$t_view .= $statement_pdf_btn;

				/* $t_view  .='<p class="non_important_text" style="display:inline; color:'.$color.'"> '.$packing_status.' | <strong style="font-size:14px"> '.(in_array('grand_total',$order_list_module) ? '' : $currency.number_format($real_total,2).'').' </strong></p>'; */
			}

			if(!empty($result["order_status_last_update_date"])){
				if(empty($result["order_status_last_update_by"])){
					$t_view .= '<p class="message non_important_text" >updated on '.$result["order_status_last_update_date"].' </p>';
				}else{
					$t_view .= '<p class="message non_important_text" > '.$result["order_status_last_update_by"].' updated on '.displaydate($result["order_status_last_update_date"]).' </p>';
				}				
			}

			if(!empty($last_print)){
				$t_view .= '<p class="message non_important_text"  style="color:green"> last printed at '.$last_print.' </p>';	
			}
			$t_view .= $img_div;
			$t_view  .='
					<div class="divider"></div>
					<hr>
				</div>
			';
			$checkTransferred++;
		}elseif(floatval($result['order_status']) == 3)	{
			$greenRound = '';
			if($result['order_status'] == '3'){
				$greenRound = '<span class="line" style="background-color:#3CB371;margin-left:5px;"></span>';
			}
			$p_view .= '
				<div style="cursor: pointer;"  class="parent-div">
					<div onclick="window.location=\'s_order_details.php?orderId='.$result["order_id"].'&client='.$client.'\'">
						<p class="title" style="color:grey;font-size:13px;line-height:0;margin-top:5px"> '.$result["order_id"].$orderFrom.$greenRound.'<strong style="font-size:14px;float:right;"> '.(in_array('grand_total',$order_list_module) ? '' : $currency.number_format($real_total,2).'').' </strong> </p>
						<p class="title" > '.$display_company_name.(!empty($branch_name) ? "({$branch_name})" : "").' </p>
						<p class="dates" ><i class="fas fa-clipboard-list"></i> '.$result["order_date"].' &ensp;<i class="fas fa-shipping-fast"></i> '.$result["delivery_date"].$warehouse_icon.'</p>';
			if($result["order_delivery_note"]){
				$p_view .= '<p class="message non_important_text" > '.$result["order_delivery_note"].' </p>';
			}
			if($isAdmin && $data['doc'] != 'credit'){
				$p_view .='<p class="message non_important_text" > '.$result["name"].$packing_status.' </p>
				</div>';
			}else {
				$p_view .='</div>';
			}

			if(in_array($data['client'],$delivery_module)){
				$p_view .='<button  data-json="'.$createTaskJSON.'" class="radius non_important_text delivery-buttons create-button" style={width:25%;font-width: 10vw;}> Create </button>';
				$p_view .='<button  data-json="'.$viewTaskJSON.'" class="radius non_important_text delivery-buttons view-button" style={width:25%;font-width: 10vw;}> View </button>';
				$p_view  .='<p class="non_important_text" style="display:inline; color:'.$color.'"> <strong> '.(in_array('grand_total',$order_list_module) ? '' : $currency.number_format($real_total,2).'').' </strong></p>';
			}else{
				if($result["message"] && $isMsgValid){
					$p_view .='<button onclick="showMessage(\''.$result["message"].'\')" class="radius non_important_text delivery-buttons" style="font-width: 10vw; margin-right:5px;"> Delivery Info </button>';
				}else{
					$p_view .='<button data-message="'.$result["message"].'" class="radius non_important_text no-message" style="font-width: 10vw; margin-right:5px;"> Delivery Info </button>';
				}
				
				$p_view .= $img_button;

				/* $p_view  .='<p class="non_important_text" style="display:inline; color:'.$color.'"> '.$packing_status.' | <strong style="font-size:14px"> '.(in_array('grand_total',$order_list_module) ? '' : $currency.number_format($real_total,2).'').' </strong></p>'; */
			}

			if(!empty($result["order_status_last_update_date"])){
				if(empty($result["order_status_last_update_by"])){
					$p_view .= '<p class="message non_important_text" >updated on '.$result["order_status_last_update_date"].' </p>';
				}else{
					$p_view .= '<p class="message non_important_text" > '.$result["order_status_last_update_by"].' updated on '.displaydate($result["order_status_last_update_date"]).' </p>';
				}				
			}

			if(!empty($last_print)){
				$p_view .= '<p class="message non_important_text"  style="color:green"> last printed at '.$last_print.' </p>';	
			}
			$p_view .= $img_div;
			$p_view  .='
					<div class="divider"></div>
					<hr>
				</div>
			';
			$checkPosted++;
		}

		if($allow_constructor){
			$result["order_date"] = date_create($result["order_date"]);
			$result["order_date"] = date_format($result["order_date"],"d M Y");
			
			$j_view .= '
			<div style="cursor: pointer;"  class="parent-div">
				<div onclick="window.location=\'s_order_details.php?orderId='.$result["order_id"].'&client='.$client.'\'">
					<p class="title" style="color:grey;font-size:13px;line-height:0;margin-top:5px"> '.$result["order_id"].$orderFrom.$greenRound.'<strong style="font-size:14px;float:right;"> </strong></p>
					<p class="title" > '.$display_company_name.(!empty($branch_name) ? "({$branch_name})" : "").' </p>
					<p class="dates" ><i class="fas fa-clipboard-list"></i> '.$result["order_date"].'</p>';

			if($cutting_remark){
				$j_view .= $cutting_remark;
			}
						
			if($result["order_delivery_note"]){
				$j_view .= '<p class="message non_important_text" > '.$result["order_delivery_note"].' </p>';
			}
			

			if(!empty($result["order_status_last_update_date"])){
				if(empty($result["order_status_last_update_by"])){
					$j_view .= '<p class="message non_important_text" >updated on '.$result["order_status_last_update_date"].' </p>';
				}else{
					$j_view .= '<p class="message non_important_text" > '.$result["order_status_last_update_by"].' updated on '.displaydate($result["order_status_last_update_date"]).' </p>';
				}				
			}

			if(!empty($last_print)){
				$j_view .= '<p class="message non_important_text"  style="color:green"> last printed at '.$last_print.' </p>';	
			}
			$j_view .= $img_div;
			$j_view  .='
					<div class="divider"></div>
					<hr>
				</div>
			';
			$checkJob++;
		}
		$check++;
	}

	$checkConfirmed === 0 ? $c_view = '' : $c_view;
	$checkTransferred === 0 ? $t_view = '' : $t_view;
	$checkActiveCart === 0 ? $a_view = '' : $a_view;
	$checkPosted === 0 ? $p_view = '' : $p_view;
	$checkJob === 0 ? $j_view = '' : $j_view;
	$view= !$allow_constructor ? $a_view.$c_view.$t_view.$p_view : $j_view;


	if(!$data['showCancel'] && ($checkConfirmed>0 || $checkTransferred>0 || $checkActiveCart>0 || $checkPosted>0) && !$data['isCustomerView'] && !$allow_constructor){
		$view.= '<center class="total-sales radius"> Total Sales: '.$currency.number_format($total_sales,2).'  </center>';
		/* $view.= '<button class="radius non_important_text buttons text_white" style="margin-top:2px;3" onclick="window.location=\'s_order_summary.php?client='.$client.'&doc='.$data['doc'].'&userId='.$data['salesperson_id'].'\'"> Order Summary </button>'; */
	}

	if($check !== 0){
		$orderList[] = $view;
		$json_data['no_result'] = 0;
	}else{
		$json_data['no_result'] = 1;
	}

	$json_data['data'] = $orderList;
	// $json_data['delivery_info'] = $delivery_info;
	// $json_data["enable_deliver_date"]=$enable_deliver_date;
	// $json_data['query'] = $query;

	$db->query(
                "SELECT * FROM `cms_setting`"
			);

	$currencyList = array();

	if($db->get_num_rows() != 0)
    {
		while($result = $db->fetch_array())
        {
           	$currencyData = array("currency"=>$result["currency"]);

			$currencyList[] = $currencyData;
        }
		$json_data['currency_data'] = $currencyList;
	}

	$json_data['show_order_approval'] = !empty($select_order_approval) ? 1 : 0;

	return json_encode($json_data);
}

function displaydate($datetime){
    $datetime = strval($datetime);
    $month_names = array(
		'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec'
    );
    
    $splitted = explode(' ',$datetime);
    
    $date = $splitted[0];
    $time = $splitted[1];
    
    $splitted = explode('-',$date);
    $year = $splitted[0];
    $month = intval($splitted[1])-1;
    $day = $splitted[2];
    
    $month = $month_names[$month];
  
    if($time != '00:00:00' && !empty($time)){
        $timeSplit = explode(':',$time);

        $hr          = intval($timeSplit[0]);
        $min         = intval($timeSplit[1]);
        $zz          = 'AM';

        if($min < 10){
            $min         = "0".$min;
        }

        if ($hr > 12) {
            $hr -= 12;
            $zz = 'PM';
        }
        $time = '';
        $time = ' at '. $hr . ':' . $min . $zz;
        }else{
           $time = '';
        }
    
    $display = $day." ".$month." ".$year.$time;
    
    return $display;
}
function str_compare ($a, $b){
    $a = str_replace(array(' ','  '),'',strtolower(trim($a)));
    $b = str_replace(array(' ','  '),'',strtolower(trim($b)));

    return $a == $b;
}
?>

