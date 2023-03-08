<?php
// header('Content-Type: text/plain; charset="UTF-8"');
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once('./model/DB_class.php');


function getConsignmentViewDetails($data)
{
	
	$view='';
	$grand_total=0;
	$config = parse_ini_file('../config.ini',true);

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
		"con1_success"=>$con_1,
		"con2_success"=>$con_2
	));

    $query = "select cms_consignment.*,cms_customer_branch.branch_name from cms_consignment  left join cms_customer_branch on cms_consignment.branch_code = cms_customer_branch.branch_code where consign_id='" . mysql_real_escape_string($data['consignment_id']) . "'";

	$config = parse_ini_file(dirname(__FILE__).'/../../config.ini');
	$decimal = $config['decimal_point'];
	$accounting_software = $config['accounting_software'];
	$gst_rate = floatval($config['gst_rate']);

	$db->query($query);

	$consignmentList = array();

	if($db->get_num_rows() != 0)
	{
		while($result = $db->fetch_array())
		{
			$total_amount = $db2->query("SELECT COALESCE(SUM(sub_total),0) AS sub_total FROM cms_consignment _item WHERE cancel_status = 0 AND (packing_status = 1 OR packing_status = 0) AND consign_id = '" . mysql_real_escape_string($data['consignment_id']) . "'");
		    $row = mysql_fetch_row($total_amount);
		    $total_amount = $row[0];

			$db2->query(
				"SELECT `cms_login`.`staff_code`
					FROM `cms_login` WHERE
					`login_id` = " . $result['salesperson_id']. ""
			);

			if($db2->get_num_rows() != 0)
			{
				while($result2 = $db2->fetch_array())
				{
					$staff_code = $result2['staff_code'];
				}
            }

            $view .='
					<div class="divider"></div>
						<div class="limit-text-length text_white company-title" style="max-width:100%;height:25px;">
							<center style="height:25px;margin-left:5px;margin-right:5px;"> '.$result["cust_company_name"].' </center>
						</div>
						<div class="divider"></div>
					<table class="data">';


			$consignmentData = array(
							"consign_id"=>$result["consign_id"]
                            ,"consign_date"=>$result["consign_date"]
                            ,"cust_company_name"=>$result["cust_company_name"]
                            ,"cust_incharge_person"=>$result["cust_incharge_person"]
                            ,"delivery_date"=>$result["delivery_date"]
                            ,"grand_total"=>$total_amount
                            ,"consign_status"=>$result["consign_status"]
                            // ,"others_consign_status"=>$result["others_consign_status"]
                            ,"consign_status_last_update_date"=>$result["consign_status_last_update_date"]
                            ,"consign_status_last_update_by"=>$result["consign_status_last_update_by"]
                            ,"billing_address1"=>$result["billing_address1"]
                            ,"billing_address2"=>$result["billing_address2"]
                            ,"billing_address3"=>$result["billing_address3"]
                            ,"billing_address4"=>$result["billing_address4"]
                            ,"billing_city"=>$result["billing_city"]
                            ,"billing_state"=>$result["billing_state"]
                            ,"billing_zipcode"=>$result["billing_zipcode"]
                            ,"billing_country"=>$result["billing_country"]
                            ,"shipping_address1"=>$result["shipping_address1"]
                            ,"shipping_address2"=>$result["shipping_address2"]
                            ,"shipping_address3"=>$result["shipping_address3"]
                            ,"shipping_address4"=>$result["shipping_address4"]
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
			    			,"consign_remark"=>$result["consign_remark"]
			    			// ,"consign_validity"=>$result["consign_validity"]
                            // ,"consign_payment_type"=>$result["consign_payment_type"]
                            ,"consign_reference"=>$result["consign_reference"]
                            ,"consign_delivery_note"=>$result["consign_delivery_note"]
                            ,"cancel_status"=>$result["cancel_status"]
                            ,"packing_status"=>$result["packing_status"]
                            ,"decimal_point"=>$decimal
                            ,"accounting_software"=>$accounting_software
                            ,"gst_rate"=>$gst_rate
                            ,"branch_code"=>$result["branch_code"]
                            ,"branch_name"=>$result["branch_name"]
                            ,'ItemArr'=>array()
					);
			$consignmentList[] = $consignmentData;
		}

	}
	else
	{
		$json_data = array(
                   "success"=>'0'
                  ,'message'=>"No consignments found"
		);
	}

	$returnArr = array();

	foreach($consignmentList as $value)
    {
		$disc_query = '';

		if ($data['client'] == 'aim'){
			$disc_query = '`cms_consignment_item`.`disc_1`,
						`cms_consignment_item`.`disc_2`,
						`cms_consignment_item`.`disc_3`,';
		}

		$result = $db->query("SHOW COLUMNS FROM `cms_consignment_item` LIKE 'isParent'");
		$exists = (mysql_num_rows($result))?TRUE:FALSE;
		$parent_cond_query = '';
		if($exists) {
			$parent_cond_query = ' AND (isParent = 0 OR isParent IS NULL OR isParent = "" ) ';
		}

		$db->query(
                "SELECT `cms_consignment_item`.`consign_id`,
						`cms_consignment_item`.`product_id`,
						`cms_consignment_item`.`consign_item_id`,
						`cms_consignment_item`.`salesperson_remark`,
						`cms_consignment_item`.`quantity`,
						`cms_consignment_item`.`editted_quantity`,
						`cms_consignment_item`.`unit_price`, "
						. $disc_query . "
						`cms_consignment_item`.`unit_uom`,
						`cms_consignment_item`.`attribute_remark`,
						`cms_consignment_item`.`optional_remark`,
						`cms_consignment_item`.`discount_method`,
						`cms_consignment_item`.`discount_amount`,
						`cms_consignment_item`.`sub_total`,
						`cms_consignment_item`.`sequence_no`,
						`cms_consignment_item`.`packing_status`,
						`cms_consignment_item`.`cancel_status`,
						`cms_consignment_item`.`packed_by`,
						`cms_consignment_item`.`updated_at`,
						`cms_product_uom_price`.`product_price`,
						`cms_product`.`product_code`,
						`cms_product`.`product_remark`,
						`cms_product`.`product_name` FROM `cms_consignment_item`
						LEFT JOIN `cms_product` ON `cms_consignment_item`.`product_code` = `cms_product`.`product_code`
						LEFT JOIN  `cms_product_uom_price` ON (`cms_consignment_item`.`uom_id` = `cms_product_uom_price`.`product_uom_id` AND `cms_consignment_item`.`product_id` = `cms_product_uom_price`.`product_id`)
						WHERE
						`cms_consignment_item`.`consign_id` = '" . $value['consign_id']. "'
						".$parent_cond_query."
						GROUP BY cms_consignment_item.consign_item_id
						ORDER BY cancel_status, packing_status"
					);

		if($db->get_num_rows() != 0)
        {
            $itemArr = array();

            while($result = $db->fetch_array())
            {
            	$date = date_create($result["updated_at"]);
			    $date = date_format($date,"d F g:iA");

            	$ItemArr = array(
                                'consign_id'=>$result['consign_id']
                                ,'product_id'=>$result['product_id']
                                ,'product_name'=>$result['product_name']
                                ,'product_code'=>$result['product_code']
                                ,'product_remark'=>$result['product_remark']
								,'consign_item_id'=>$result['consign_item_id']
                                ,'salesperson_remark'=>$result['salesperson_remark']
                                ,'quantity'=>$result['quantity']
                                ,'editted_quantity'=>$result['editted_quantity']
                                ,'unit_price'=>$result['unit_price']
                                ,'disc_1'=>$result['disc_1']
                                ,'disc_2'=>$result['disc_2']
                                ,'disc_3'=>$result['disc_3']
                                ,'unit_uom'=>$result['unit_uom']
                                ,'attribute_remark'=>$result['attribute_remark']
                                ,'optional_remark'=>$result['optional_remark']
                                ,'discount_method'=>$result['discount_method']
                                ,'discount_amount'=>$result['discount_amount']
                                ,'sub_total'=>$result['sub_total']
                                ,'product_price'=>$result['product_price']
                                ,'sequence_no'=>$result['sequence_no']
                                ,'packing_status'=>$result['packing_status']
                                ,'cancel_status'=>$result['cancel_status']
                                ,'packed_by'=>$result['packed_by']
                                ,'updated_at'=>$date
				);
				
				$result['discount_amount'] = number_format($result['discount_amount']/$result['sub_total'] * 100,2);
				$discount = "({$result['discount_amount']}%)";
				if($result['discount_amount'] == 0){
					$discount = '';
				}
				if(!empty($result['disc_1']) || !empty($result['disc_2']) || !empty($result['disc_3'])){
					$disc1 = floatval($result['disc_1']);
					$disc2 = floatval($result['disc_2']);
					$disc3 = floatval($result['disc_3']);
					$discount = "({$disc1}/{$disc2}/$disc3)";
				}

            	$packing_status=$result['packing_status'];
            	if($packing_status==0){
            		$Picked="Waiting for Packing";
            	}elseif ($packing_status==1) {
            		$Picked="Packing Completed";
            	}elseif ($packing_status==2 || $packing_status==3) {
            		$Picked="No Stock";
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

				$grand_total+=$result['sub_total'];

				$view .='
				<tr style="height:auto">
					<td style="height:auto">
						<p style="color:#147efb;font-weight:bold"> '.$result['product_name'].$item_remark.'</p>
						<p class="packing-status"> '.$result['salesperson_remark'].' </p>
						<p class="packing-status"> <span><i>'.$Picked.'</i></span></p>
						<div class="row">
							<div class="column";">
								<center style="color:black; word-wrap: break-word;">RM'.$result['unit_price'].'/'.$result['unit_uom'];
								
				if($discount){
					$view .=' <br>'.$discount;
				}				
				
				$view .='
								</center>
							</div>
							<div class="column" style="padding-right:5px;width:24%">
								<center style="color:#147efb;font-weight:bold">QTY '.$result['quantity'].'</center>
							</div>
							<div class="column">
								<center style="color:black;font-weight:bold">RM'.sprintf('%0.2f',$result['sub_total']).'</center>
							</div>
						</div>
					</td>
				</tr>
				';
				$itemArr[] = $ItemArr;
            }
			$value['ItemArr'] = $itemArr;
			$returnArr[] = $value;
		}
		else
		{
			$returnArr[] = $value;
		}
    }


    $view.='<tr style:"marginTop:10px">
			    <td style="font-weight:bold">
			        <p style="text-align:right"> Grand Total: <span style="color:#147efb"> RM'.sprintf('%0.2f', $grand_total).' </span> </p>
			    </td>
			</tr>
		</table>';

    $json_data['views'] = $view;

	$json_data['consign_data'] = $returnArr;

	

	return $json_data;
}

?>
