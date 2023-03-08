<?php
header('Content-Type: text/plain; charset="UTF-8"');
require_once('./model/DB_class.php');

function getOrderStatusAndOrderDetailInOneTime($data)
{
	$client = "easysale_".$data['client'];
	$db = new DB();
	$db->connect_db_with_db_config($client);

	$db2 = new DB();
	$db2->connect_db_with_db_config($client);
	
    $query = "select cms_order.*,cms_customer_branch.branch_name from cms_order left join cms_customer_branch on cms_order.branch_code = cms_customer_branch.branch_code where order_id='" . mysql_real_escape_string($data['orderId']) . "'";

	$config = parse_ini_file(dirname(__FILE__).'/../../config.ini');
	$decimal = $config['decimal_point'];
	$accounting_software = $config['accounting_software'];
	$gst_rate = floatval($config['gst_rate']);
	
	$db->query($query);
	
	$orderList = array();
	
	if($db->get_num_rows() != 0)
	{
		while($result = $db->fetch_array())
		{
			$total_amount = $db2->query("SELECT COALESCE(SUM(sub_total),0) AS sub_total FROM cms_order_item WHERE cancel_status = 0 AND (packing_status = 1 OR packing_status = 0) AND order_id = '" . mysql_real_escape_string($data['orderId']) . "'");
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
					// $orderData['sales_agent'] = $result2['staff_code'];
				}
			}
			$orderData = array(
							"order_id"=>$result["order_id"]
                            ,"order_date"=>$result["order_date"]
                            ,"cust_company_name"=>$result["cust_company_name"]
                            ,"cust_incharge_person"=>$result["cust_incharge_person"]
                            ,"delivery_date"=>$result["delivery_date"]
                            ,"grand_total"=>$total_amount
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
			    			,"order_remark"=>$result["order_remark"]
			    			,"order_validity"=>$result["order_validity"]
                            ,"order_payment_type"=>$result["order_payment_type"]
                            ,"order_reference"=>$result["order_reference"]
                            ,"order_delivery_note"=>$result["order_delivery_note"]
                            ,"cancel_status"=>$result["cancel_status"]
                            ,"packing_status"=>$result["packing_status"]
                            ,"decimal_point"=>$decimal
                            ,"accounting_software"=>$accounting_software
                            ,"gst_rate"=>$gst_rate
                            ,"branch_code"=>$result["branch_code"]
                            ,"branch_name"=>$result["branch_name"]
                            ,'orderItemArr'=>array()
					);

			
			$orderList[] = $orderData;
		}


		
		// $json_data['data'] = $orderList;
	}
	else 
	{
		$json_data = array(
                   "success"=>'0'
				  ,'message'=>"No orders found"		
				  ,"query"=>$query
		);
	}
	
	
	
	
	// order item details
	
	$returnArr = array();
	
	foreach($orderList as $value)
    {
    	// get product name as well
    	/*
    	$db->query(
                "SELECT * FROM `cms_order_item` WHERE `order_id` = '" . $value['order_id']. "'"
			);
			*/
		$disc_query = '';
		
		if ($data['client'] == 'aim'){
			$disc_query = '`cms_order_item`.`disc_1`, 
						`cms_order_item`.`disc_2`, 
						`cms_order_item`.`disc_3`,';
		}

		$db->query(
                "SELECT `cms_order_item`.`order_id`, 
						`cms_order_item`.`product_id`, 
						`cms_order_item`.`order_item_id`, 
						`cms_order_item`.`salesperson_remark`, 
						`cms_order_item`.`quantity`, 
						`cms_order_item`.`editted_quantity`,
						`cms_order_item`.`unit_price`, " 
						. $disc_query . "
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
						`cms_product_uom_price`.`product_price`, 
						`cms_product`.`product_code`,
						`cms_product`.`product_remark`,
						`cms_product`.`product_name` FROM `cms_order_item`
						LEFT JOIN `cms_product` ON `cms_order_item`.`product_id` = `cms_product`.`product_id`
						LEFT JOIN  `cms_product_uom_price` ON (`cms_order_item`.`uom_id` = `cms_product_uom_price`.`product_uom_id` AND `cms_order_item`.`product_id` = `cms_product_uom_price`.`product_id`)
						WHERE  
						`cms_order_item`.`order_id` = '" . $value['order_id']. "'
						GROUP BY cms_order_item.order_item_id
						ORDER BY cancel_status, packing_status"
					);
		
		if($db->get_num_rows() != 0)
        {
            $itemArr = array();
            
            while($result = $db->fetch_array())
            {
            	$date = date_create($result["updated_at"]);
			    $date = date_format($date,"d F g:iA");

				$remark = "";
				if($result['product_remark']){
					$remark = " (".$result['product_remark'].")";
				}

            	$orderItemArr = array(
                                'order_id'=>$result['order_id']
                                ,'product_id'=>$result['product_id']
                                ,'product_name'=>$result['product_name'].$remark
                                ,'product_code'=>$result['product_code']
                                ,'product_remark'=>$result['product_remark']
								,'order_item_id'=>$result['order_item_id']
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
				
				$itemArr[] = $orderItemArr;
            }
				
			$value['orderItemArr'] = $itemArr;

			$returnArr[] = $value;
		}
		else
		{
			$returnArr[] = $value;
		}
    }
	
	 $json_data['order_data'] = $returnArr;
	
	
	
	// $db->query("SELECT * FROM cms_order_status WHERE status = 1");
	
	// $orderStatusList = array();
	
	// if($db->get_num_rows() != 0)
	// {
	// 	while($result = $db->fetch_array())
	// 	{
	// 		$statusData = array(
	// 						"order_status_id"=>$result["order_status_id"]
 //                            ,"order_status_name"=>$result["order_status_name"]
	// 				);
			
	// 		$orderStatusList[] = $statusData;
	// 	}
		
	// 	$json_data['orderStatus_data'] = $orderStatusList;
	// }
	
	
	return json_encode($json_data);
}

?>
