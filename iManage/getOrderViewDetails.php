<?php
header('Content-Type: text/plain; charset="UTF-8"');
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once('./model/DB_class.php');

function getOrderViewDetails($data)
{
	$user_settings = json_decode($data['settings'],true);
	$user_settings = $user_settings['fields'];

	$view='';
	$grand_total=0;
	$config = parse_ini_file('../config.ini',true);

	$client = $data['client'];

	$product_code_qrcode = $config['QR_CODE']['product_code_qrcode'];
	$product_code_qrcode = in_array($client,$product_code_qrcode);

	$approval_config = $config['Request_Approval'];
	$allow_cust_edit = in_array($client,$approval_config['client_approval']);

	$product_group_config = $config['Product_Group'];
	$allow_product_group = in_array($client,$product_group_config['product_group']);

	$constructor_job = $config['Constructor_Job'];
	$allow_constructor = in_array($client,$constructor_job['constructor_job']);

	$sql_accounting = $config['SQL'];
	$sql_client = in_array($client,$sql_accounting['sql_client']);

	$item_order_config = $config['Item_Order'];
	$orderby_item_id = in_array($client,$item_order_config['order_item_id']);

	$order_by_packing_status = $config['OrderBy_Packing_Status'];
	$order_by_packing_status = in_array($client,$order_by_packing_status['order_by_packing_status']);

	$hide_packing_status = $config['Hide_Packing_Status'];
	$hide_packing_status = in_array($client,$hide_packing_status['hide_packing_status']);

	$round_up = $config['Show_Round_Up'];
	$show_round_up = in_array($client,$round_up['round_up']);

	$show_udf = $config['Show_UDF'];
	$show_udf_po_edit = in_array($client,$show_udf['po_no_edit']);

	$payment = $config['PAYMENT_DISPLAY'];
	$payment_display = in_array($client, $payment['payment_display']);

	$info_exclude				= $config['INFO_EXCLUDE'];
	
	if(isset($info_exclude[$client])){
		for ($i=0; $i < count($info_exclude[$client]); $i++) { 
			if (($key = array_search($info_exclude[$client][$i], $user_settings)) !== false) {
				unset($user_settings[$key]);
			}
		}
	}

	$order_items_edit_exclue	= $config['ORDER_ITEMS_EDIT'];
	
	$allowToEdit				= !in_array($client,$order_items_edit_exclue['edit_exclude']);

	$settings                   = $config[$client];

	$db_user                    = $settings['user'];
	$db_pass                    = $settings['password'];
	$db_name                    = $settings['db'];
	$db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

	$currency 					= isset($settings['currency']) ? $settings['currency'] : 'RM';

	$db = new DB();
	$con_1 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);

	$db2 = new DB();
	$con_2 = $db2->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);

	$json_data['connection'] = json_encode(array(
		"user"=>$db_user,
		"password"=>$db_pass,
		"db"=>$db_name,
		"client"=>$client,
		"con1_success"=>$con_1,
		"con2_success"=>$con_2
	));

	$cart_module = array();
	$order_list_module = array();
	$module_settings = array();

	$db->query("SELECT status FROM cms_mobile_module WHERE module = 'app_restrictions'");
	while($result = $db->fetch_array()){
		$module_settings = $result['status'];
	}
	$module_settings = json_decode($module_settings,true);
	// $cart_module = isset($module_settings['cart']) ? $module_settings['cart'] : array();
	$order_list_module = isset($module_settings['order_list']) ? $module_settings['order_list'] : array();

	$db->query("SELECT status FROM cms_mobile_module WHERE module = 'app_add_product_rule'");
	while($result = $db->fetch_array()){
		$tax_settings = $result['status'];
	}
	$tax_settings = json_decode($tax_settings,true);
	$tax_module = isset($tax_settings['has_tax']) ? $tax_settings['has_tax'] : array();

	$checkTable = $db->query("show tables like '%cms_customer_branch%'");
	$customer_branch_exists = $db->get_num_rows()?TRUE:FALSE;

	if($customer_branch_exists) {
		$query = "select cms_order.*,cms_customer_branch.branch_name,cms_customer_branch.branch_code, cms_customer_branch.shipping_address1 as ship_address1, cms_customer_branch.shipping_address2 as ship_address2,cms_customer_branch.shipping_address3 as ship_address3, cms_customer_branch.shipping_address4 as ship_address4 from cms_order 
		left join cms_customer_branch on cms_order.branch_code = cms_customer_branch.branch_code 
		where order_id='" . mysql_real_escape_string($data['orderId']) . "'";
	}else{
		$query = "select cms_order.* from cms_order  
			where order_id='" . mysql_real_escape_string($data['orderId']) . "'";
	}

	$db->query("SHOW TABLES LIKE '%cms_order_cutting_date%'");
	$order_cutting_date_exists = $db->get_num_rows()?TRUE:FALSE;

	$db->query("SHOW TABLES LIKE '%cms_package%'");
	$order_package_exists = $db->get_num_rows()?TRUE:FALSE;

	if($order_cutting_date_exists || $allow_constructor){
		
		$order_cutting_record_exists		    = false;

		$cancelStatus							= $data['cancelled_cutting_date'];
		$cancelQuery							= "AND active_status = '".$cancelStatus."'";
		if($cancelStatus == '0'){
			$cancelQuery = '';
		}


		$order_cutting_query   					= "select active_status,cutting_status,DATE(task_date) as task_date,updated_at,cutting_title,id,cust_company_name,cutting_date,cutting_remark,create_login_id, edit_login_id, (SELECT name FROM cms_login WHERE cms_order_cutting_date.create_login_id = cms_login.login_id) AS create_name, (SELECT name FROM cms_login WHERE cms_order_cutting_date.edit_login_id = cms_login.login_id) AS edit_name from cms_order_cutting_date join cms_order on cms_order.order_id=cms_order_cutting_date.order_id where cms_order_cutting_date.order_id = '" .$data['orderId']. "' {$cancelQuery}";

		if($allow_constructor){
			$order_cutting_query = "SELECT active_status,cutting_status,DATE(task_date) as task_date,updated_at,cutting_title,id,cust_company_name,cutting_date,cutting_remark,create_login_id, edit_login_id, (SELECT name FROM cms_login WHERE cms_acc_order_cutting_date.create_login_id = cms_login.login_id) AS create_name, (SELECT name FROM cms_login WHERE cms_acc_order_cutting_date.edit_login_id = cms_login.login_id) AS edit_name FROM cms_acc_order_cutting_date JOIN cms_acc_existing_order on cms_acc_existing_order.order_id=cms_acc_order_cutting_date.order_id WHERE cms_acc_order_cutting_date.order_id = '" .$data['orderId']. "' {$cancelQuery}";
		}
		$checkRecord 		   					= $db->query($order_cutting_query);

		$cutting_dates_array   = array();
		while($result = $db->fetch_array()){
			$image = array();
			if($allow_constructor){
				$db2->query("SELECT upload_id,upload_image FROM cms_salesperson_uploads WHERE upload_bind_id = '".$data['orderId']."' AND upload_remark = '".$result['id']."' AND upload_status = 1");
				while($result2 = $db2->fetch_array()){
					$img_array = array(
						"id"=>$result2['upload_id'],
						"img_url"=>$result2['upload_image']
					);
					$image[] = $img_array;
				}
			}

			$order_cutting_record_exists 	 			= true;
			$row = array(
				"id"					=>  $result['id'],
				"cutting_remark"		=>  $result['cutting_remark'],
				"cutting_title"			=>	$result['cutting_title'],
				"cutting_status"		=>	$result['cutting_status'],
				"cutting_edit_date"		=>	displaydate($result['updated_at']),
				"cutting_date"			=>  displaydate($result['cutting_date']),
				"task_date"				=>	displaydate($result['task_date']),
				"cust_company_name"		=>  $result['cust_company_name'],
				"active_status"			=>	$result['active_status'],
				"create_login_id"		=> $result['create_login_id'],
				"create_name"			=> $result['create_name'],
				"edit_name"				=> $result['edit_name'],
				"edit_login_id"			=> $result['edit_login_id'],
				"image"					=> $image
			);

			array_push($cutting_dates_array,$row);
		}


		$json_data['order_cutting_date'] = $cutting_dates_array;

		
		$udf_query = "SELECT `name` FROM cms_module WHERE `name` LIKE '%title%'";

		$db->query($udf_query);

		$udf_fields = array();

		while($result = $db->fetch_array()){
			$udf_fields = json_decode($result['name'],true);
		}

		$json_data['udf_fields'] = $udf_fields;
	}else {
		$json_data['order_cutting_date'] = false;
	}



	$config = parse_ini_file(dirname(__FILE__).'/../../config.ini');
	$decimal = $config['decimal_point'];
	$accounting_software = $config['accounting_software'];
	$gst_rate = floatval($config['gst_rate']);

	if($allow_constructor){
		$query = "select cms_acc_existing_order.* from cms_acc_existing_order WHERE order_id='" . mysql_real_escape_string($data['orderId']) . "'";
	}
	$payment_data = array();
	$payment_view = '';
	if($payment_display){

		$sql = "SELECT `pmd`.*, `pm`.`description`,`pm`.`payment_id`,`pm`.`doc_id` as `with_doc_id`, `pm`.`payment_amount` FROM `cms_payment` AS pm LEFT JOIN `cms_payment_detail` AS pmd ON `pmd`.`payment_id` = `pm`.`payment_id` WHERE `doc_id` = '".$data['orderId']."'";
		
		$db->query($sql);
		if($db->get_num_rows() != 0)
		{
			while($result2 = $db->fetch_array()) {
				$payment_data = $result2;
				$payment_data["unapplied_amount"] = 0;
				$payment_data["payment_dtl"] = array(
					$result2
				);
				$payment_view = 
				'<table class="data">
					<tr><td>
					<p><span style="font-weight:bold;">Payment ID: </span><span>'.$result2['payment_id'].'</span></p>
					<p><span style="font-weight:bold;">Payment Method: </span><span style="color:#147efb;font-weight:bold">'.$result2['payment_method'].'</span></p>
					<p><span style="font-weight:bold;">Bank Name: </span><span>'.$result2['payment_by'] . '</span></p>
					<p><span style="font-weight:bold;">Cheque No: </span><span>'.$result2['cheque_no'].'</span></p>
					<p><span style="font-weight:bold;">Payment Amount: </span><span>RM '.number_format($result2['payment_amount'],2).'</span></p>
					<p><span style="font-weight:bold;">Remark: </span><span>'.$result2['description'].'</span></p>
					</td></tr>
				</table>';
			}
		}
	}
	
	$db->query($query);
	//file_put_contents("datadetails.log",$query);
	$orderList = array();

	if($db->get_num_rows() != 0)
	{
		while($result = $db->fetch_array())
		{
			if($result['order_from'] == 'C' && $allow_cust_edit && $result['order_approved'] == 0){
				$allow_cust_edit = true;
			}else{
				$allow_cust_edit = false;
			}
			$doc_type = $result['doc_type'];
			$cart_module = isset($module_settings[$doc_type]) ? $module_settings[$doc_type] : array();
			if($cart_module){
				$cart_module = $cart_module['cart'];
			}

			$totalQuery="SELECT COALESCE(SUM(sub_total),0) AS sub_total FROM cms_order_item WHERE cancel_status = 0 AND (packing_status = 1 OR packing_status = 0) AND order_id = '" . mysql_real_escape_string($result['order_id']) . "' AND parent_code != 'PACKAGE' AND parent_code != 'CATALOG'";

			/* if($allow_product_group){
				$totalQuery .=" AND isParent = 0 AND parent_code IN (SELECT DISTINCT parent_code FROM cms_order_item 
				WHERE (packing_status = 1 OR packing_status = 0) AND order_id = '" . mysql_real_escape_string($result['order_id']) . "' AND isParent = 1 AND cancel_status = 0)";
			} */

			$real_total = $db->query($totalQuery);
			$row = mysql_fetch_row($real_total);
			$total_amount = $row[0];

			$db2->query(
				"SELECT `cms_login`.`staff_code`
					FROM `cms_login` WHERE
					`login_id` = " . $result['salesperson_id']. ""
			);

			if($db2->get_num_rows() != 0)
			{
				$json_data['success'] = true;

				while($result2 = $db2->fetch_array())
				{
					$staff_code = $result2['staff_code'];
				}
			}

			$view .='<div class="divider"></div>
						<div class="limit-text-length text_white company-title" style="max-width:100%;height:25px;background-color:#f4f5f7">
							<center style="height:25px;margin-left:5px;margin-right:5px;color:grey"> '.$result["cust_company_name"].' </center>
						</div>
						<div class="divider"></div>';
					
			if($result["order_status"] == 1 && $allowToEdit && !$data['isWarehouse']){
				$view .= '<div class="btn-group" style="width:100%">
				<button style="width:100%;" onclick="window.location.href=\'s_order_addItem.php?client='.$client.'&orderId='.$data['orderId'].'\'">Add New Product</button>
			</div>';
			}
			if($allow_cust_edit && !$data['isWarehouse']){
				$view .= '<div class="btn-group" style="width:100%">
					<button style="width:100%;" onclick="window.location.href=\'s_order_addItem.php?client='.$client.'&orderId='.$data['orderId'].'\'">Add New Product</button>
				</div>';
			}		

			$view .='<table class="data">';

			$po_input = '';
			if($result['order_status'] == 1 && $result['cancel_status'] == 0 && $show_udf_po_edit){
				$po_no = '';
				if($result['order_udf']){
					$order_udf = json_decode($result['order_udf'],true);
					for ($b=0; $b < count($order_udf); $b++) { 
						if($order_udf[$b]['code'] == 'po_no'){
							$po_no = $order_udf[$b]['value'];
						}
					}
				}

				$po_input = '<div style="width:100%;padding:10px;">
					<label for="po_no">PO No:</label>
					<input id="po_no" type="text" style="border-radius:5px;padding:5px;border:1px solid black;" value="'.$po_no.'"/><br>
					<button style="width:100%;border-radius:5px;padding:10px;" onclick="updatePONo(\''.$result['order_id'].'\')">UPDATE PO NO</button>
				</div>';
			}

			$order_udf = $result['order_udf'];
			$result['order_udf'] = str_replace(array("\n", "\r"), '', $order_udf);
			$result["branch_code"] = $result["branch_code"] ? $result["branch_code"] : '';
			$result["branch_name"] = $result["branch_name"] ? $result["branch_name"] : '';
			if (count($payment_data) > 0){
				$payment_data["unapplied_amount"] = $total_amount - $payment_data["payment_amount"];
			}
			$orderData = array(
				"order_id"=>$result["order_id"]
				,"order_date"=>$result["order_date"]
				,"cust_company_name"=>sanitize(str_replace('.',' ',$result["cust_company_name"]))
				,"cust_incharge_person"=>sanitize($result["cust_incharge_person"])
				,"delivery_date"=>$result["delivery_date"]
				,"grand_total"=>$total_amount
				,"grand_total_tax"=>$result['gst_amount']
				,"order_status"=>$result["order_status"]
				,"others_order_status"=>$result["others_order_status"]
				,"order_status_last_update_date"=>$result["order_status_last_update_date"]
				,"order_status_last_update_by"=>$result["order_status_last_update_by"]
				,"billing_address1"=>$result["billing_address1"]
				,"billing_address2"=>$result["billing_address2"]
				,"billing_address3"=>$result["billing_address3"]
				,"billing_address4"=>$result["billing_address4"]
				,"billing_city"=>$result["billing_city"]
				,"billing_state"=>$result["billing_state"]
				,"billing_zipcode"=>$result["billing_zipcode"]
				,"billing_country"=>$result["billing_country"]
				,"shipping_address1"=>$result["shipping_address1"] //? $result["ship_address1"] : $result["shipping_address1"] 
				,"shipping_address2"=>$result["shipping_address2"] //? $result["ship_address2"] : $result["shipping_address2"]
				,"shipping_address3"=>$result["shipping_address3"] //? $result["ship_address3"] : $result["shipping_address3"]
				,"shipping_address4"=>$result["shipping_address4"] //? $result["ship_address4"] : $result["shipping_address4"]
				,"shipping_city"=>$result["shipping_city"]
				,"shipping_state"=>$result["shipping_state"]
				,"shipping_zipcode"=>$result["shipping_zipcode"]
				,"shipping_country"=>$result["shipping_country"]
				,"salesperson_id"=>$result["salesperson_id"]
				,"sales_agent"=>$staff_code
				,"cust_reference"=>$result["cust_reference"]
				,"cust_email"=>$result["cust_email"]
				,"cust_tel"=>$result["cust_tel"]
				,"cust_fax"=>$result["cust_fax"]
				,"cust_code"=>$result["cust_code"]
				,"total_discount"=>$result["total_discount"]
				,"discount_method"=>$result["discount_method"]
				,"shippingfee"=>$result["shippingfee"]
				,"tax"=>$result["tax"]
				,"order_remark"=>$result["order_remark"]
				,"order_validity"=>$result["order_validity"]
				,"order_payment_type"=>$result["order_payment_type"]
				,"order_reference"=>$result["order_reference"]
				,"order_delivery_note"=>sanitize($result["order_delivery_note"])
				,"cancel_status"=>$result["cancel_status"]
				,"packing_status"=>$result["packing_status"]
				,"decimal_point"=>$decimal ? $decimal : ''
				,"accounting_software"=>$accounting_software ? $accounting_software : ''
				,"gst_rate"=>$gst_rate
				,"branch_code"=>$client == 'megadis' || $client =='chillm_kk' ? ($result["cust_code"].'|'.$result["branch_code"]) : $result["branch_code"]
				,"branch_name"=>$result["branch_name"]
				,'order_udf'=>$result['order_udf']
				,'orderItemArr'=>array()
				,'payment'=>$payment_data
			);
			$orderList[] = $orderData;
		}
	}
	else
	{
		$json_data = array(
			"success"=>'0'
		,'message'=>"No orders found"
		);
	}

	$returnArr = array();

	$disc_exists		= $db->query("SHOW COLUMNS FROM `cms_order_item` LIKE 'disc_1'");
	$disc_exists		= (mysql_num_rows($disc_exists))?TRUE:FALSE;
	$disc_query = '`cms_order_item`.`disc_1`,
					`cms_order_item`.`disc_2`,
					`cms_order_item`.`disc_3`,';
	if($disc_exists){
		// $disc_query = '`cms_order_item`.`disc_1`,
		// 			`cms_order_item`.`disc_2`,
		// 			`cms_order_item`.`disc_3`,';
	}else{
		// $disc_query = '';
		$db2->query("ALTER TABLE cms_order_item ADD disc_1 double;");
		$db2->query("ALTER TABLE cms_order_item ADD disc_2 double;");
		$db2->query("ALTER TABLE cms_order_item ADD disc_3 double;");
	}

	$qr_exists		= $db->query("SHOW COLUMNS FROM `cms_product_uom_price_v2` LIKE 'QR_Code'");
	$qr_exists		= (mysql_num_rows($qr_exists))?TRUE:FALSE;
	
	if($qr_exists){
		
	}else{
		$db2->query("ALTER TABLE cms_product_uom_price_v2 ADD QR_Code varchar(30);");
	}

	$packed_by_exists		= $db->query("SHOW COLUMNS FROM `cms_order_item` LIKE 'pack_confirmed_by'");
	$packed_by_exists		= (mysql_num_rows($packed_by_exists))?TRUE:FALSE;
	
	if($packed_by_exists){
		
	}else{
		$db2->query("ALTER TABLE cms_order_item ADD pack_confirmed_by varchar(30);");
	}

	$result = $db->query("SHOW COLUMNS FROM `cms_order_item` LIKE 'pack_confirmed_by'");
	$packing_exists = (mysql_num_rows($result))?TRUE:FALSE;
	$packing_select = '';
	if($packing_exists){
		$packing_select = '`cms_order_item`.`packed_by`,`cms_order_item`.`pack_confirmed_by`,`cms_order_item`.`packed_qty`,';
	}

	$result = $db->query("SHOW COLUMNS FROM `cms_order_item` LIKE 'packed_qty'");
    $packed_qty_exists = (mysql_num_rows($result)) ? TRUE : FALSE;
	if (!$packed_qty_exists){
		$db2->query("ALTER TABLE cms_order_item ADD packed_qty double;");
	}

	$result = $db->query("SHOW COLUMNS FROM `cms_order_item` LIKE 'picker_note'");
	$picker_note_exists = (mysql_num_rows($result))?TRUE:FALSE;
	$picker_note_select = '';
	if($picker_note_exists){
		$picker_note_select = '`cms_order_item`.`picker_note`,';
	}

	$result = $db->query("SHOW COLUMNS FROM `cms_order_item` LIKE 'isParent'");
    $parent_exists = (mysql_num_rows($result)) ? TRUE : FALSE;

	$result = $db->query("SHOW COLUMNS FROM `cms_order_item` LIKE 'proj_no'");
    $proj_no_exists = (mysql_num_rows($result)) ? TRUE : FALSE;
	if (!$proj_no_exists){
		$db2->query("ALTER TABLE cms_order_item ADD proj_no blob;");
	}

	$result = $db->query("SHOW COLUMNS FROM `cms_order_item` LIKE 'is_exchange'");
    $is_exchange = (mysql_num_rows($result)) ? TRUE : FALSE;
	if (!$is_exchange){
		$db2->query("ALTER TABLE cms_order_item ADD is_exchange int NOT NULL DEFAULT 0;");
	}

	$result = $db->query("SHOW COLUMNS FROM `cms_order_item` LIKE 'unit_length'");
    $unit_length = (mysql_num_rows($result)) ? TRUE : FALSE;
	if (!$unit_length){
		$db2->query("ALTER TABLE cms_order_item ADD unit_length blob");
	}
	
	foreach($orderList as $value)
	{

		$package_cond_query = '';
		$package_query = '';
		if($order_package_exists){
			$package_query = "IF(`cms_order_item`.`parent_code` = 'PACKAGE',pkg.pkg_name,'') AS package_name,IF(`cms_order_item`.`parent_code` = 'PACKAGE',pkg.pkg_code,'') AS package_code,IF(`cms_order_item`.`parent_code` = 'PACKAGE', pkg.pkg_desc,'') AS package_desc,";
			$package_cond_query = "LEFT JOIN cms_package pkg ON cms_order_item.product_code = pkg.pkg_code";
		}

        $parent_cond_query = '';
        if ($parent_exists) {
			/* if($allow_product_group){
				$parent_cond_query = ' AND `cms_order_item`.`product_code` = `cms_order_item`.`parent_code` AND `cms_order_item`.isParent = 1';
			}else{
				$parent_cond_query = 'AND ((`cms_order_item`.`parent_code` is null OR `cms_order_item`.`parent_code` = "") OR (`cms_order_item`.`product_code` = `cms_order_item`.`parent_code` AND `cms_order_item`.isParent = 1))';
			} */
			$parent_cond_query = " AND ((`cms_order_item`.`parent_code` is null OR `cms_order_item`.`parent_code` = '' AND `cms_order_item`.isParent = 0 ) OR (`cms_order_item`.`parent_code` != '' AND `cms_order_item`.isParent = 1))";
		}

		/* if($allow_product_group){
			$group_column = 'parent_code';
			$group_active_status = ' AND cpg.active_status = 1';
			$remove_foc = " AND `salesperson_remark` !='FOC'";
		}else{
			$group_column = 'order_item_id';
			$group_active_status = '';
			$remove_foc = '';
		} */

		if($allow_product_group){
			$group_active_status = ' AND cpg.active_status = 1';
		}else{
			$group_active_status = '';
		}
		$jsonQuery = "SELECT IF(`cms_order_item`.`parent_code` = 'CATALOG',cpg.name,`cms_product`.`product_name`) AS product_name,
		IF(`cms_order_item`.`parent_code` = 'CATALOG', cpg.description,`cms_product`.`product_desc`) AS product_desc,
		".$package_query."
		`cms_order_item`.`order_id`,
		`cms_order_item`.`product_id`,
		`cms_order_item`.`order_item_id`,
		IF(`cms_order_item`.`salesperson_remark` = 'undefined','',`cms_order_item`.`salesperson_remark`) AS salesperson_remark,
		`cms_order_item`.`quantity`,
		`cms_order_item`.`editted_quantity`,
		`cms_order_item`.`unit_price`, 
			" . $disc_query . "
			" . $packing_select . "
			" . $picker_note_select . "
		`cms_order_item`.`unit_uom`,
		`cms_order_item`.`attribute_remark`,
		`cms_order_item`.`optional_remark`,
		`cms_order_item`.`discount_method`,
		`cms_order_item`.`discount_amount`,
		`cms_order_item`.`sub_total`,
		`cms_order_item`.`sequence_no`,
		`cms_order_item`.`packing_status`,
		`cms_order_item`.`cancel_status`,
		`cms_order_item`.`packed_by`,
		`cms_order_item`.`updated_at`,
		`cms_product_uom_price_v2`.`product_std_price`,
		`cms_product`.`product_code`,
		`cms_product`.`product_remark`,
		`cms_order_item`.`ipad_item_id`,
		`cms_order_item`.`isParent`,
		`cms_order_item`.`parent_code`,
		`cms_order_item`.`proj_no`,
		`cms_order_item`.`is_exchange`,
		`cms_order_item`.`unit_length`,
		IF(`cms_product_uom_price_v2`.`QR_Code`='',`cms_product`.`QR_code`,`cms_product_uom_price_v2`.`QR_Code`) AS QR_code,
		`cms_product`.`category_id`
		FROM `cms_order_item`
		LEFT JOIN cms_product_group cpg on cms_order_item.product_code = cpg.product_code ".$group_active_status."
		".$package_cond_query."
		LEFT JOIN `cms_product` ON `cms_order_item`.`product_code` = `cms_product`.`product_code`
		LEFT JOIN  `cms_product_uom_price_v2` ON (`cms_order_item`.`unit_uom` = `cms_product_uom_price_v2`.`product_uom` AND `cms_order_item`.`product_code` = `cms_product_uom_price_v2`.`product_code` AND `cms_product_uom_price_v2`.`active_status` = 1)
		WHERE
		`cms_order_item`.`order_id` = '" . $value['order_id']. "'
		GROUP BY `cms_order_item`.`ipad_item_id`
		ORDER BY cms_order_item.order_item_id asc";

		$db->query($jsonQuery);
		
		if($db->get_num_rows() != 0)
		{
			$json_data = array();
			$childrenArray2 = array();
			$parent_code = '';
			while($result = $db->fetch_array())
			{
				$json_result[] = $result;
			}
		}
		$view = '';
		$grand_total = 0;

		$view .='<div class="divider"></div>
						<div class="limit-text-length text_white company-title" style="max-width:100%;height:25px;background-color:#f4f5f7">
							<center style="height:25px;margin-left:5px;margin-right:5px;color:grey"> '.$value["cust_company_name"].' </center>
						</div>
						<div class="divider"></div>';

		if($value["order_status"] == 1 && $allowToEdit && !$data['isWarehouse']){
			$view .= '<div class="btn-group" style="width:100%">
			<button style="width:100%;" onclick="window.location.href=\'s_order_addItem.php?client='.$client.'&orderId='.$data['orderId'].'\'">Add New Product</button>
		</div>';
		}
		if($allow_cust_edit && !$data['isWarehouse']){
			$view .= '<div class="btn-group" style="width:100%">
			<button style="width:100%;" onclick="window.location.href=\'s_order_addItem.php?client='.$client.'&orderId='.$data['orderId'].'\'">Add New Product</button>
		</div>';
		}	

		$view .= $payment_view;
		$view .='<table class="data">';



		$parents = array('CATALOG','PACKAGE','FOC','GROUP');
		$f = array();
		$itemCounter  = 1;
		for ($i=0; $i < sizeof($json_result); $i++) { 
			$result = $json_result[$i];

			$date = date_create($result["updated_at"]);
			$date = date_format($date,"d F g:iA");
			$sp_remark = $result['salesperson_remark'];
			if(json_decode($sp_remark,true)){
				$result['salesperson_remark'] = json_decode($sp_remark,true)['str'];
			}
			$percentDiscount = floatval($result['disc_1']) != floatval($result['discount_amount']) && floatval($result['disc_1']) != 0;
			$discountType = $percentDiscount ? 'PercentDiscountType' : $result['discount_method'];

			if (is_string($result['proj_no']) && !empty($result['proj_no'])){
				$arr = explode(",",$result['proj_no']);
				for ($p = 0; $p < count($arr); $p++){
					$splitted = $arr[$p];
					if (strpos($splitted, 'project_code') !== false) {
						$splitted = explode(":",$splitted);
						if (count($splitted) > 1){
							$project_code = str_replace('"','',$splitted[1]);
							$db2->query("SELECT * FROM cms_project WHERE project_code = '{$project_code}'");
							$orderList = array();

							if($db2->get_num_rows() != 0)
							{
								$result['proj_no'] = array();
								while($proj_res = $db2->fetch_array()){
									foreach($proj_res as $k => $v){
										if ($k == "project_details"){
											$project_details = json_decode($proj_res['project_details'],true);
											foreach($project_details as $k => $v){
												$result['proj_no'][$k] = $v;
											}
										} else {
											$result['proj_no'][$k] = $v;
										}
									}
								}
							}
						}
					}
				}
			}
			$disc_split = explode("+",$result['discount_amount']);
			$new_disc = "";
			for($d = 0; $d < count($disc_split); $d++){
				if (floatval($disc_split[$d]) > 0){
					if ($d != 0 && !empty($new_disc)){
						$new_disc .= "+";
					}
					$new_disc .= $disc_split[$d];
				}
			}
			$result['QR_code'] = $result['QR_code'] ? $result['QR_code'] : '';
			
			$obj = array(
				'order_id'=>$result['order_id']
				,'product_id'=>$result['product_id']
				,'product_name'=> $result['package_name'] ? sanitize($result['package_name']) : sanitize($result['product_name'])
				,'product_code'=>$result['package_code'] ? sanitize($result['package_code']) : sanitize($result['product_code'])
				,'product_desc'=>$result['package_desc'] ? sanitize($result['package_desc']) :sanitize($result['product_desc'])
				,'product_remark'=>sanitize($result['product_remark'])
				,'order_item_id'=>$result['order_item_id']
				,'salesperson_remark'=>sanitize($result['salesperson_remark'])
				,'category_id'=>$result['category_id']
				,'quantity'=>$result['quantity']
				,'editted_quantity'=>$result['editted_quantity']
				,'unit_price'=>$result['unit_price']
				,'disc_1'=>floatval($result['disc_1'])
				,'disc_2'=>floatval($result['disc_2'])
				,'disc_3'=>floatval($result['disc_3'])
				,'unit_uom'=>$result['unit_uom']
				,'attribute_remark'=>$result['attribute_remark']
				,'optional_remark'=>$result['optional_remark']
				,'discount_method'=>$discountType
				,'discount_amount'=>$new_disc//number_format(,2)
				,'sub_total'=>$result['sub_total']
				,'product_price'=> 0 //$result['product_price']
				,'sequence_no'=>$result['sequence_no']
				,'packing_status'=>$result['packing_status']
				,'cancel_status'=>$result['cancel_status']
				,'packed_by'=>sanitize($result['packed_by'])
				,'updated_at'=>$date
				,'children'=>''
				,'ipad_item_id'=>$result['ipad_item_id']
				,'picker_note'=>$result['picker_note']
				,'parent_code'=>$result['parent_code']
				,'parent'=>$result['parent_code']
				,'QR_code'=>$product_code_qrcode ? sanitize($result['product_code']) : $result['QR_code']
				,'isPackage'=>$result['parent_code'] == 'PACKAGE' ? 1 : 0
				,'project_no'=>$result['proj_no']
				,'is_exchange'=>intval($result['is_exchange'])
				,'unit_length'=>$result['unit_length']
			);

			if($result['cancel_status'] == 0 || $order_by_packing_status){
				$packing_status=$result['packing_status'];
				if ($packing_status==1) {
					if(isset($result['packed_qty']) && $result['packed_qty'] > 0){
						$obj['quantity'] = $result['packed_qty'];
						$obj['sub_total'] = $result['quantity'] * $result['unit_price'];
					}
				}
				if(in_array($obj['parent_code'],$parents)){
					$parent_code = $obj['parent_code'];
					$parent_quantity = $parent_code == 'PACKAGE' && !$sql_client ? $obj['quantity'] : 1;
					$unique_key = $i.'-'.$obj['product_code'].'-'.$obj['parent_code'];
					$f[$unique_key] = $obj;
				}else{
					if($obj['parent_code'] == ''){
						$unique_key = 'ind-'.$i;
						$f[$unique_key] = $obj;
					}else{
						$tmp = $f;
						end($tmp);
						$last_key = key($tmp);

						$hasFoc = $result['salesperson_remark'] == 'FOC' ? 1 : 0;

						$newChildrenArray = array(
							"child_name" =>sanitize($result['product_name']),
							"child_code"=>sanitize($result['product_code']),
							"child_desc"=>sanitize($result['product_desc']),
							"child_remark"=>sanitize($result['salesperson_remark']),
							"child_unit_price"=>$result['unit_price'],
							"child_uom"=>sanitize($result['unit_uom']),
							"child_total"=>$parent_code == 'PACKAGE' && !$sql_client ? $result['sub_total'] * $parent_quantity : $result['sub_total'],
							"child_disc_1"=>$result['disc_1'] ? $result['disc_1'] : 0,
							"child_disc_2"=>$result['disc_2'] ? $result['disc_2'] : 0,
							"child_disc_3"=>$result['disc_3'] ? $result['disc_3'] : 0,
							"child_qty"=> $parent_code == 'PACKAGE' && !$sql_client ? $result['quantity'] * $parent_quantity : $result['quantity']
						);
						if($f[$last_key]['cancel_status'] == '0'){
							$f[$last_key]['children'][] = $newChildrenArray;
							$f[$last_key]['hasFoc'] = $hasFoc;
						}
					}
				}
			}

				$status = 'Picking Completed';
				$pack_checked_by = $result['pack_confirmed_by'];
				$packing_status=$result['packing_status'];

				$picker_name = '';
				if($packing_exists){
					$picker_name = strtoupper($result['packed_by']);
					if($packing_status != 1){
						$picker_name = '';
					}else{
						if(!empty($result['packed_by'])){
							$picked_qty = $result['packed_qty'];
							$status = "{$picked_qty} Picked By";
							$picker_name = "({$picker_name})";
							if(!empty($pack_checked_by)){
								$picker_name .= " | Checked By ({$pack_checked_by})";
							}
						}
					}
				}

				if($packing_status==0){
					$Picked="Waiting for Packing";
				}elseif ($packing_status==1) {
					if(isset($result['packed_qty']) && $result['packed_qty'] > 0){
						$result['quantity'] = $result['packed_qty'];
						$obj['quantity'] = $result['packed_qty'];
						$result['sub_total'] = $result['quantity'] * $result['unit_price'];
					}
					$Picked="{$status} {$picker_name}";
				}elseif ($packing_status==2 || $packing_status==3) {
					$result['quantity'] = 0;
					$result['sub_total'] = 0;
					$Picked='<span style="color:rgb(255,85,45)">No Stock</span>';
				}

				if($hide_packing_status){
					$Picked = '';
				}

				$item_cancelled = $result['cancel_status'];
				if($item_cancelled){
					$Picked='<span style="color:rgb(255,85,45)">Item Removed</span>';
				}

				// view for product information
				$item_remark = ' ('.$result['product_remark'].')';
				if(empty($result['product_remark'])){
					$item_remark = '';
				}

				$item_desc = ' ('.$result['product_desc'].')';
				if(empty($result['product_desc'])){
					$item_desc = '';
				}

				if($result['discount_method'] == 'MixDiscount'){
					$discount = $result['discount_amount'] ? ' - ('.$result['discount_amount'].')' : '';
				}else{
					$disc1 = floatval($result['disc_1']); $disc2 = floatval($result['disc_2']); $disc3 = floatval($result['disc_3']);
					$discount = $disc1 != '0' ? ($percentDiscount ? '':$currency).$disc1.($percentDiscount ? '%' : '') : '';
					if($disc2){
						$discount .= '+'.$disc2.'%';
					}
					if($disc3){
						$discount .= '+'.$disc3.'%';
					}
					if($discount == '0%'){
						$discount = '';
					}
					if(!empty($discount)){
						$discount = " - ({$discount})";
					}	
				}
				
			$openModal = '';
				
			if(($allow_cust_edit) || ($allowToEdit && $value['order_status'] < 2)){
					$openModal = 'onclick="openModal(\''.$result['ipad_item_id'].'\',\''.htmlSafe($result['product_code']).'\',\''.$result['unit_uom'].'\',\''.$result['quantity'].'\',\''.$result['unit_price'].'\',\''.$result['disc_1'].'\',\''.$result['disc_2'].'\',\''.$result['disc_3'].'\',\''.getSpRemark($result['salesperson_remark']).'\',\''.$discountType.'\',\''.htmlSafe($result['product_name']).'\')"';
			}
			$attribute_view = '';
			if($result['parent_code'] == ''){
							
				$subtotal = sprintf('%0.2f',$result['sub_total']);
				$parent_quantity = $result['parent_code'] == 'PACKAGE' && !$sql_client ? $result['quantity'] : 1;

				$is_exchange_view = $result['is_exchange'] == 1 ? '<span style="color:red;"> [EXCHANGE]</span>' : '';
				
				$product_code = in_array("product_code",$user_settings) ? '<p style="color:#147efb;font-weight:bold"> '."({$itemCounter}) ".$result['product_code'].$is_exchange_view.'</p>' : '';
				$product_desc = in_array("product_desc",$user_settings) ? '<p style="color:#000;font-size:14px"> '.$result['product_desc'].'</p>' : '';
				$product_remark = in_array("product_remark",$user_settings) ? '<p style="color:grey;font-size:14px"> '.$result['product_remark'].'</p>' : '';
				$optional_remark = $result['optional_remark'] && $result['optional_remark'] != 'N/A' ? '<p style="color:red;font-size:14px"> '.$result['optional_remark'].'</p>' : '';

				$product_price = $result['unit_price'].'/'.$result['unit_uom'].$discount;


				if($result['attribute_remark']){
					$attribute_remark = json_decode($result['attribute_remark'],true);
					for ($o=0; $o < count($attribute_remark); $o++) { 
						$each = $attribute_remark[$o];

						$attribute_view .='	
							<div style="width:30%;padding:5px;float:left;height:45px;border:2px solid grey;border-radius:5px;;margin:5px;">
								<p style="color:black;font-size:13px;margin-bottom:5px;margin:0px;">'.strtoupper($each['attribute_name']).'</center>
								<p style="width:50%;float:left;margin:0px;font-weight:bold;font-size:13px;">'.$each['attribute_value'].'</p>
								<p style="width:50%;float:left;margin:0px;color:#147efb;text-align:right;font-size:13px;">x'.$each['attribute_quantity'].'</p>
							</div>					
						';
					}
				}

				$view .='
				<tr>
					<td '.$openModal.'>
						'.$product_code.'
						<p style="color:#000;font-weight:bold"> '.(empty($product_code) ? "({$itemCounter}) " : "").$result['product_name'].'</p>
						'.$product_desc.'
						'.$product_remark.'
						'.$optional_remark.'
						<p class="packing-status"> '.getSpRemark($result['salesperson_remark']).' </p>
						<p class="packing-status"><span><i>'.$Picked.'</i></span></p>
						'.$picker_note.'
						<div class="row" style="margin:0px;">
						'.$attribute_view.'
						</div>
						<div class="row">
							<div class="column" height="auto">
								<center style="color:black">'.$currency.$product_price.'</center>
							</div>
							<div class="column" style="padding-right:5px;width:24%">
								<center style="color:#147efb;font-weight:bold;">QTY '.floatval($result['quantity']).'</center>
							</div>
							<div class="column">
								'.( in_array('sub_total',$cart_module) ? '' : '<center style="color:black;font-weight:bold;">'.$currency.number_format($subtotal,2).'</center>' ).'
							</div>
						</div>
					</td>
				</tr>
				';
				
				if($result['cancel_status'] == 0){
					$grand_total2+=$subtotal;
				}

				$itemCounter ++;
			}else{
				
				if($result['parent_code'] == 'PACKAGE' || $result['parent_code'] == 'FOC' || $result['parent_code'] == 'CATALOG' || $result['parent_code'] == 'GROUP'){

					$result['product_name'] = $result['parent_code'] == 'PACKAGE' ? $result['package_name'] : $result['product_name'];
					$result['product_code'] = $result['parent_code'] == 'PACKAGE' ? $result['package_code'] : $result['product_name'];
					$result['product_desc'] = $result['parent_code'] == 'PACKAGE' ? $result['package_desc'] : $result['product_desc'];	

					$product_code = in_array("product_code",$user_settings) ? '<p style="color:#147efb;font-weight:bold"> '."({$itemCounter}) ".$result['product_code'].'</p>' : '';
					$product_desc = in_array("product_desc",$user_settings) ? '<p style="color:#000;font-size:14px"> '.$result['product_desc'].'</p>' : '';
					$product_remark = in_array("product_remark",$user_settings) ? '<p style="color:grey;font-size:14px"> '.$result['product_remark'].'</p>' : '';
					$optional_remark = $result['optional_remark'] && $result['optional_remark'] != 'N/A' ? '<p style="color:red;font-size:14px"> '.$result['optional_remark'].'</p>' : '';

					$product_price = $result['unit_price'].'/'.$result['unit_uom'].$discount;
					$parent_quantity = $result['parent_code'] == 'PACKAGE' && !$sql_client ? $result['quantity'] : 1;
					$subtotal = $result['parent_code'] != 'FOC' ? 0 : $result['sub_total'];
					if($result['cancel_status'] == 0){
						$grand_total2 += $result['parent_code'] == 'FOC' ? $result['sub_total'] : 0;
					}
					
					$childQty = 0;
					$childQty += $result['parent_code'] == 'FOC' ? intval($result['quantity']) : 0;
					$even = 0;
					$view .='
					<tr>
						<td '.$openModal.'>
							'.$product_code.'
							<p style="color:#000;font-weight:bold"> '.(empty($product_code) ? "({$itemCounter}) " : "").$result['product_name'].'</p>
							'.$product_desc.'
							'.$product_remark.'
							'.$optional_remark.'
							<p class="packing-status"> '.getSpRemark($result['salesperson_remark']).' </p>
							<p class="packing-status"><span><i>'.$Picked.'</i></span></p>
							'.$picker_note;
					
					$last_parent_code = $result['parent_code'];
					$itemCounter ++;							
				}else{

					$result['quantity'] = $result['quantity'] * $parent_quantity;
					$result['sub_total'] = $result['sub_total'] * $parent_quantity;

					if($result['cancel_status'] == 0 && $result['quantity'] != 0){
						$subtotal += $result['sub_total'];
						$grand_total2 += $result['sub_total'];
					}

					$childQty += intval($result['quantity']);

					$child_packing_status = '';
					if($result2['packing_status']==0){
						$child_packing_status="Waiting for Packing";
					}elseif ($result2['packing_status']==1) {
						$child_packing_status="Packing Completed By (".$result2['packed_by'].")";
					}elseif ($result2['packing_status']==2 || $result2['packing_status']==3) {
						$child_packing_status="No Stock";
					}

					$cdisc1 = floatval($result['disc_1']); $cdisc2 = floatval($result['disc_2']); $cdisc3 = floatval($result['disc_3']);
					$cdiscount = $cdisc1.'%';
					if($cdisc2){
						$cdiscount .= '+'.$cdisc2.'%';
					}
					if($cdisc3){
						$cdiscount .= '+'.$cdisc3.'%';
					}
					if($cdiscount == '0%'){
						$cdiscount = '';
					}
					if(!empty($cdiscount)){
						$cdiscount = " - ({$cdiscount})";
					}

					$backgroundColor = ($even % 2) == 0 ? '#f4f5f7' : 'white';

					$child_details = '<p style="color:black;font-weight:bold;font-size:13px">'.$currency.number_format($result['unit_price'],2).$cdiscount.' x '.$result['quantity'].' '.$result['unit_uom'].' = '.$currency.number_format($result['sub_total'],2).'</p>';

					if($result['cancel_status']){
						$child_packing_status='<span style="color:rgb(255,85,45)">Item Removed</span>';
						$child_details = '';
					}

					$j = $i + 1;
					
					$view .='	
					<div style="background-color:'.$backgroundColor.';">
						<div class="row" style="padding:5px;padding-left:15px">
							'.$c_code.'
							<p style="color:black;font-size:13px">'.$result['product_name'].'</p>
						</div>
						'.(
							$result['salesperson_remark'] ? 
							'<p style="font-size:13px;color:grey"> <u>'.getSpRemark($result['salesperson_remark']).'</u> </p>':''
							).'
						<p class="packing-status"><span><i>'.$child_packing_status.'</i></span></p>
						'.$child_details.'
						<hr style="margin-top:0px;margin-bottom:0px">
					</div>					
					';

					$even++;

					if($json_result[$j]['parent_code'] != $result['parent_code']){
						$view .= ' <div class="row">
										<div class="column" height="auto">';

						$view .= $result['parent_code'] == 'FOC' ? '<center style="color:black">'.$currency.$product_price.'</center>' : '';

						$view .= '		</div>
										<div class="column" style="padding-right:5px;width:24%">
											<center style="color:#147efb;font-weight:bold;">QTY '.($childQty ? $last_parent_code == 'PACKAGE' ? $parent_quantity : $childQty  : floatval($result['quantity'])).'</center>
										</div>
										<div class="column">
											'.( in_array('sub_total',$cart_module) ? '' : '<center style="color:black;font-weight:bold;">'.$currency.number_format($subtotal,2).'</center>' ).'
										</div>
									</div>
								</td>
							</tr>
							';
						$last_parent_code = '';
					}
				}
			}
		}

		
		// file_put_contents('f.log',json_encode($f));
		if($order_by_packing_status){
			usort($f, function($a, $b)
			{
				return (($a["packing_status"] > $b["packing_status"]) ? -1 : 1);
			});	
			$grand_total2 = 0;
			$itemCounter = 1;
			$view ='<div class="divider"></div>
						<div class="limit-text-length text_white company-title" style="max-width:100%;height:25px;background-color:#f4f5f7">
							<center style="height:25px;margin-left:5px;margin-right:5px;color:grey"> '.$value["cust_company_name"].' </center>
						</div>
						<div class="divider"></div>';
			$view .='<table class="data">';

			for ($i=0; $i < count($f) ; $i++) { 
				$result = $f[$i];

				$date = date_create($result["updated_at"]);
				$date = date_format($date,"d F g:iA");

				$status = 'Picking Completed';
				$pack_checked_by = $result['pack_confirmed_by'];
				$packing_status=$result['packing_status'];

				$picker_name = '';
				if($packing_exists){
					$picker_name = strtoupper($result['packed_by']);
					if($packing_status != 1){
						$picker_name = '';
					}else{
						if(!empty($result['packed_by'])){
							$picked_qty = $result['packed_qty'];
							$status = "{$picked_qty} Picked By";
							$picker_name = "({$picker_name})";
							if(!empty($pack_checked_by)){
								$picker_name .= " | Checked By ({$pack_checked_by})";
							}
						}
					}
				}

				if($packing_status==0){
					$Picked="Waiting for Packing";
				}elseif ($packing_status==1) {
					if(isset($result['packed_qty']) && $result['packed_qty'] > 0){
						$result['quantity'] = $result['packed_qty'];
						$result['sub_total'] = $result['quantity'] * $result['unit_price'];
					}
					$Picked="{$status} {$picker_name}";
				}elseif ($packing_status==2 || $packing_status==3) {
					$result['quantity'] = 0;
					$result['sub_total'] = 0;
					$Picked='<span style="color:rgb(255,85,45)">No Stock</span>';
				}

				$item_cancelled = $result['cancel_status'];
				if($item_cancelled){
					$Picked='<span style="color:rgb(255,85,45)">Item Removed</span>';
				}

					// view for product information
				$item_remark = ' ('.$result['product_remark'].')';
				if(empty($result['product_remark'])){
					$item_remark = '';
				}

				$item_desc = ' ('.$result['product_desc'].')';
				if(empty($result['product_desc'])){
					$item_desc = '';
				}

				$disc1 = floatval($result['disc_1']); $disc2 = floatval($result['disc_2']); $disc3 = floatval($result['disc_3']);
				$discount = ($percentDiscount ? '':$currency).$disc1.($percentDiscount ? '%' : '');
				if($disc2){
					$discount .= '+'.$disc2.'%';
				}
				if($disc3){
					$discount .= '+'.$disc3.'%';
				}
				if($discount == '0%'){
					$discount = '';
				}
				if(!empty($discount)){
					$discount = " - ({$discount})";
				}
				
				$openModal = '';
					
				if(/* $result['cancel_status'] != '1' && */ $allowToEdit && $value['order_status'] != 2){
						$openModal = 'onclick="openModal(\''.$result['ipad_item_id'].'\',\''.htmlSafe($result['product_code']).'\',\''.$result['unit_uom'].'\',\''.$result['quantity'].'\',\''.$result['unit_price'].'\',\''.$result['disc_1'].'\',\''.$result['disc_2'].'\',\''.$result['disc_3'].'\',\''.getSpRemark($result['salesperson_remark']).'\',\''.$discountType.'\',\''.htmlSafe($result['product_name']).'\')"';
				}
				
				if($result['children'] == ''){
								
					$subtotal = sprintf('%0.2f',$result['sub_total']);
					$parent_quantity = $result['parent_code'] == 'PACKAGE' && !$sql_client ? $result['quantity'] : 1;
					
					$product_code = in_array("product_code",$user_settings) ? '<p style="color:#147efb;font-weight:bold"> '."({$itemCounter}) ".$result['product_code'].'</p>' : '';
					$product_desc = in_array("product_desc",$user_settings) ? '<p style="color:#000;font-size:14px"> '.$result['product_desc'].'</p>' : '';
					$product_remark = in_array("product_remark",$user_settings) ? '<p style="color:grey;font-size:14px"> '.$result['product_remark'].'</p>' : '';
					$optional_remark = $result['optional_remark'] && $result['optional_remark'] != 'N/A' ? '<p style="color:red;font-size:14px"> '.$result['optional_remark'].'</p>' : '';

					$product_price = $result['unit_price'].'/'.$result['unit_uom'].$discount;

					$view .='
					<tr>
						<td '.$openModal.'>
							'.$product_code.'
							<p style="color:#000;font-weight:bold"> '.(empty($product_code) ? "({$itemCounter}) " : "").$result['product_name'].'</p>
							'.$product_desc.'
							'.$product_remark.'
							'.$optional_remark.'
							<p class="packing-status"> '.getSpRemark($result['salesperson_remark']).' </p>
							<p class="packing-status"><span><i>'.$Picked.'</i></span></p>
							'.$picker_note.'
							<div class="row">
								<div class="column" height="auto">
									<center style="color:black">'.$currency.$product_price.'</center>
								</div>
								<div class="column" style="padding-right:5px;width:24%">
									<center style="color:#147efb;font-weight:bold;">QTY '.floatval($result['quantity']).'</center>
								</div>
								<div class="column">
									'.( in_array('sub_total',$cart_module) ? '' : '<center style="color:black;font-weight:bold;">'.$currency.number_format($subtotal,2).'</center>' ).'
								</div>
							</div>
						</td>
					</tr>
					';
					
					if($result['cancel_status'] == 0){
						$grand_total2+=$subtotal;
					}

				}else{
					
					if($result['parent_code'] == 'PACKAGE' || $result['parent_code'] == 'FOC' || $result['parent_code'] == 'CATALOG' || $result['parent_code'] == 'GROUP'){

						$result['product_name'] = $result['parent_code'] == 'PACKAGE' ? $result['package_name'] : $result['product_name'];
						$result['product_desc'] = $result['parent_code'] == 'PACKAGE' ? $result['package_desc'] : $result['product_desc'];	

						$product_code = in_array("product_code",$user_settings) ? '<p style="color:#147efb;font-weight:bold"> '."({$itemCounter}) ".$result['product_code'].'</p>' : '';
						$product_desc = in_array("product_desc",$user_settings) ? '<p style="color:#000;font-size:14px"> '.$result['product_desc'].'</p>' : '';
						$product_remark = in_array("product_remark",$user_settings) ? '<p style="color:grey;font-size:14px"> '.$result['product_remark'].'</p>' : '';
						$optional_remark = $result['optional_remark'] && $result['optional_remark'] != 'N/A' ? '<p style="color:red;font-size:14px"> '.$result['optional_remark'].'</p>' : '';

						$product_price = $result['unit_price'].'/'.$result['unit_uom'].$discount;
						$parent_quantity = $result['parent_code'] == 'PACKAGE' && !$sql_client ? $result['quantity'] : 1;
						$subtotal = $result['parent_code'] != 'FOC' ? 0 : $result['sub_total'];
						if($result['cancel_status'] == 0){
							$grand_total2 += $result['parent_code'] == 'FOC' ? $result['sub_total'] : 0;
						}
						
						$childQty = 0;
						$childQty += $result['parent_code'] == 'FOC' ? intval($result['quantity']) : 0;
						$even = 0;
						$view .='
						<tr>
							<td '.$openModal.'>
								'.$product_code.'
								<p style="color:#000;font-weight:bold"> '.(empty($product_code) ? "({$itemCounter}) " : "").$result['product_name'].'</p>
								'.$product_desc.'
								'.$product_remark.'
								'.$optional_remark.'
								<p class="packing-status"> '.getSpRemark($result['salesperson_remark']).' </p>
								<p class="packing-status"><span><i>'.$Picked.'</i></span></p>
								'.$picker_note;
						
						
					}

					for ($j=0; $j < count($result['children']) ; $j++) { 
	
						$result['quantity'] = $result['quantity'] * $parent_quantity;
						$result['sub_total'] = $result['sub_total'] * $parent_quantity;

						if($result['cancel_status'] == 0){
							$subtotal += $result['sub_total'];
							$grand_total2 += $result['sub_total'];
						}

						$childQty += intval($result['quantity']);

						$child_packing_status = '';
						if($result2['packing_status']==0){
							$child_packing_status="Waiting for Packing";
						}elseif ($result2['packing_status']==1) {
							$child_packing_status="Packing Completed By (".$result2['packed_by'].")";
						}elseif ($result2['packing_status']==2 || $result2['packing_status']==3) {
							$child_packing_status="No Stock";
						}

						$cdisc1 = floatval($result['disc_1']); $cdisc2 = floatval($result['disc_2']); $cdisc3 = floatval($result['disc_3']);
						$cdiscount = $cdisc1.'%';
						if($cdisc2){
							$cdiscount .= '+'.$cdisc2.'%';
						}
						if($cdisc3){
							$cdiscount .= '+'.$cdisc3.'%';
						}
						if($cdiscount == '0%'){
							$cdiscount = '';
						}
						if(!empty($cdiscount)){
							$cdiscount = " - ({$cdiscount})";
						}

						$backgroundColor = ($even % 2) == 0 ? '#f4f5f7' : 'white';

						$child_details = '<p style="color:black;font-weight:bold;font-size:13px">'.$currency.$result['unit_price'].$cdiscount.' x '.$result['quantity'].' '.$result['unit_uom'].' = '.$currency.$result['sub_total'].'</p>';

						if($result['cancel_status']){
							$child_packing_status='<span style="color:rgb(255,85,45)">Item Removed</span>';
							$child_details = '';
						}

						$j = $i + 1;
						
						$view .='	
						<div style="background-color:'.$backgroundColor.';">
							<div class="row" style="padding:5px;padding-left:15px">
								'.$c_code.'
								<p style="color:black;font-size:13px">'.$result['product_name'].'</p>
							</div>
							'.(
								$result['salesperson_remark'] ? 
								'<p style="font-size:13px;color:grey"> <u>'.getSpRemark($result['salesperson_remark']).'</u> </p>':''
								).'
							<p class="packing-status"><span><i>'.$child_packing_status.'</i></span></p>
							'.$child_details.'
							<hr style="margin-top:0px;margin-bottom:0px">
						</div>					
						';

						$even++;
						
					}

					$view .= ' <div class="row">
									<div class="column" height="auto"></div>
									<div class="column" style="padding-right:5px;width:24%">
										<center style="color:#147efb;font-weight:bold;">QTY '.($childQty ? $childQty : floatval($result['quantity'])).'</center>
									</div>
									<div class="column">
										'.( in_array('sub_total',$cart_module) ? '' : '<center style="color:black;font-weight:bold;">'.$currency.number_format($subtotal,2).'</center>' ).'
									</div>
								</div>
							</td>
						</tr>';

					
				}

				$itemCounter ++;	
			}

			$f = array_filter($f,function($item){ return $item['cancel_status'] == 0; });
		}

		$final = array();
		foreach($f as $key=>$newValue){
			if(isset($newValue['packed_qty']) && $newValue['packed_qty'] > 0){
				$newValue['quantity'] = $newValue['packed_qty'];
			}

			if($client == 'easwari'){
				$newValue['quantity'] = $newValue['quantity'].' |';
			}

			if($newValue['packing_status'] != 2 && $newValue['packing_status'] != 3){
				$final[] = $newValue;
			}
		}
		
		if($final){
			$value['orderItemArr'] = $final;
		}
		
		$returnArr[] = $value;
	}

	if(!empty($final)){
		$returnArr[0]['grand_total'] = $grand_total2;
		
		if($show_round_up){
			$round_up_total = ceil($grand_total2*20 - 0.5)/20;
		}

		$round_up_view = $show_round_up ? '<p style="text-align:right"> Rounded Total: <span style="color:#147efb"> '.$currency.sprintf('%0.2f', $round_up_total) .' </span> </p>' : '';

		if($returnArr[0]['tax'] && $tax_module){
			$tax = $returnArr[0]['tax'];
			$sum = $grand_total2 + $tax;
			$returnArr[0]['grand_total_tax'] = $sum;
			$view.='<tr>
				<td style="font-weight:bold; box-shadow:0px 0px; border-bottom:1px solid white; padding-top:5px;">
					<p style="text-align:right"> Tax: <span style="color:#147efb"> '.$currency.sprintf('%0.2f', $tax) .' </span> </p>
				</td>
			</tr>';
			
			$view.='<tr>
					<td style="font-weight:bold">
						<p style="text-align:right"> '. ( in_array('grand_total',$cart_module) ? '' : 'Grand Total: <span style="color:#147efb"> '.$currency.sprintf('%0.2f', $grand_total2).'' ) .' </span> </p>
					</td>
				</tr>
			</table>';
			
			$view.='<tr>
				<td style="font-weight:bold">
					<p style="text-align:right">Sum: <span style="color:#147efb"> '.$currency.sprintf('%0.2f', $sum ) .' </span> </p>
				</td>
			</tr>';
		}else{

			$view.='<tr>
					<td style="font-weight:bold">
						<p style="text-align:right"> '. ( in_array('grand_total',$cart_module) ? '' : 'Grand Total: <span style="color:#147efb"> '.$currency.sprintf('%0.2f', $grand_total2).'' ) .' </span> </p>
						'.$round_up_view.'
					</td>
				</tr>
			</table>';
		}

		$view .= $po_input;


	}else if($order_cutting_record_exists){

		return $json_data;

	}else{
		$view .= '<p class="no_orders"> No Available Details </p>';
	}

	$json_data['views'] = $view;
	$json_data['order_data'] = $returnArr;
	
	return $json_data;
}
function getSpRemark($remark){
	$decoded_remark = json_decode($remark,true);
	if($decoded_remark){
		return $decoded_remark['str'];
	}
	return $remark;
}
function sanitize($inp){
	return str_replace(array("#",'"'),array("","''"),preg_replace("/&#?[a-z0-9]+;/i","",strip_tags($inp)));
}
function htmlSafe($inp){
	/* $inp = htmlspecialchars($inp);
	$inp = htmlentities($inp); */
	return str_replace(array("#",'"',"'"),array("","''",""),preg_replace("/&#?[a-z0-9]+;/i","",strip_tags($inp))); 
}
function displaydate($datetime){
    $datetime = strval($datetime);
    $month_names = array(
       'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
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
?>
