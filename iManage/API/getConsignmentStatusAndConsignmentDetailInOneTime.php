<?php
header('Content-Type: text/plain; charset="UTF-8"');
require_once('./model/DB_class.php');

function getConsignmentStatusAndConsignmentDetailInOneTime($data)
{
	$client = "easysale_".$data['client'];
	$db = new DB();
	$db->connect_db_with_db_config($client);

	$db2 = new DB();
	$db2->connect_db_with_db_config($client);

	$query = "SELECT * FROM cms_consignment where consign_id ='" . mysql_real_escape_string($data['consignmentId']) . "'";

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
			$total_amount = $db2->query("SELECT COALESCE(SUM(sub_total),0) AS sub_total FROM cms_consignment_item WHERE cancel_status = 0 AND (packing_status = 1 OR packing_status = 0) AND consign_id = '" . mysql_real_escape_string($data['consignmentId']) . "'");
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
			$consignmentData = array(
							"consignment_id"=>$result["consign_id"]
                            ,"consignment_date"=>$result["consign_date"]
                            ,"cust_company_name"=>$result["cust_company_name"]
                            ,"cust_incharge_person"=>$result["cust_incharge_person"]
                            ,"delivery_date"=>$result["delivery_date"]
                            ,"grand_total"=>$total_amount
                            ,"consignment_status"=>$result["consign_status"]
                            ,"others_consignment_status"=>$result["others_consign_status"]
                            ,"consignment_status_last_update_date"=>$result["consign_status_last_update_date"]
                            ,"consignment_status_last_update_by"=>$result["consign_status_last_update_by"]
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
			    								  ,"consignment_remark"=>$result["consign_remark"]
			    								  //,"order_validity"=>$result["order_validity"]
                            //,"order_payment_type"=>$result["order_payment_type"]
                            ,"consignment_reference"=>$result["consign_reference"]
                            ,"consignment_delivery_note"=>$result["consign_delivery_note"]
                            ,"cancel_status"=>$result["cancel_status"]
                            ,"packing_status"=>$result["packing_status"]
                            ,"decimal_point"=>$decimal
                            ,"accounting_software"=>$accounting_software
                            ,"gst_rate"=>$gst_rate
                            ,'consignmentItemArr'=>array()
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

		if ($accounting_software == 'abswin')
		{
			$disc_query = '`cms_consignment_item`.`disc_1`,
						`cms_consignment_item`.`disc_2`,
						`cms_consignment_item`.`disc_3`,';
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
						LEFT JOIN `cms_product` ON `cms_consignment_item`.`product_id` = `cms_product`.`product_id`
						LEFT JOIN  `cms_product_uom_price` ON (`cms_consignment_item`.`uom_id` = `cms_product_uom_price`.`product_uom_id` AND `cms_consignment_item`.`product_id` = `cms_product_uom_price`.`product_id`)
						WHERE
						`cms_consignment_item`.`consign_id` = '" . $value['consignment_id']. "'
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

            	$consignmentItemArr = array(
                                'consign_id'=>$result['consign_id']
                                ,'product_id'=>$result['product_id']
                                ,'product_name'=>$result['product_name']
                                ,'product_code'=>$result['product_code']
                                ,'product_remark'=>$result['product_remark']
																,'consignment_item_id'=>$result['consign_item_id']
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

				$itemArr[] = $consignmentItemArr;
            }

			$value['consignmentItemArr'] = $itemArr;

			$returnArr[] = $value;
		}
		else
		{
			$returnArr[] = $value;
		}
    }

	 $json_data['consignment_data'] = $returnArr;

	return json_encode($json_data);
}

?>
