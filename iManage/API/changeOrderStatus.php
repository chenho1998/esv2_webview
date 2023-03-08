<?php
header('Content-Type: text/plain; charset="UTF-8"');
require_once('model/DB_class.php');

function changeOrderStatus($data)
{
    $db = new DB();
    $db->connect_db_with_db_config();
    
    $json_data = array(
			'result'=> ""
			,'action'=>"changeOrderStatus"
			,'data'=>array()
			,"message"=> ""
			,"message_code"=> ""
	);
    

	/*
	if($data['order_status'] == 4)
	{
		$db->query(
                "SELECT `name` FROM `cms_login` WHERE `login_id` = ". $data['salespersonid'] .""
			);
		
		$paymentReceivedBy = '';
		
		if($db->get_num_rows() != 0)
		{
			while($result = $db->fetch_array())
			{
				$paymentReceivedBy = $result['name'];            
			}
		}
		else
		{
			$json_data['result'] = '0';
        
			return json_encode($json_data);
		}
		
		$updateDate = new DateTime();
		$updateDate = $updateDate->format('Y-m-d H:i:s');
		
		$db->query( "UPDATE `cms_order`
			SET 
						`order_status` = " . $data['order_status'] . ",
						`others_order_status` = '". $data['others_order_status']."', 
                        `payment_received_date` = '". $updateDate."',
                        `Payment_received_by` = '". $paymentReceivedBy . "' 
			WHERE `order_id` = '". $data['orderId'] ."'"
                        );
    
		if($db->get_affected_rows() == 0)
		{
			$json_data['result'] = '0';
		}
		else
		{
			$json_data['result'] = '1';
		}    
	}
	else
	{
		$db->query( "UPDATE `cms_order`
			SET 
			`order_status` = " . $data['order_status'] . ",
            `others_order_status` = '". $data['others_order_status']."' 
			WHERE `order_id` = '". $data['orderId'] ."'"
                        );
    
		if($db->get_affected_rows() == 0)
		{
			$json_data['result'] = '0';
		}
		else
		{
			$json_data['result'] = '1';
		}	
	}*/
	
	$db->query(
			"SELECT `name` FROM `cms_login` WHERE `login_id` = ". $data['salespersonid'] .""
		);
	
	$statusUpdateBy = '';
	
	if($db->get_num_rows() != 0)
	{
		while($result = $db->fetch_array())
		{
			$statusUpdateBy = $result['name'];            
		}
	}
	else
	{
		$json_data['result'] = '0';
	
		return json_encode($json_data);
	}
	
	$updateDate = new DateTime();
	$updateDate = $updateDate->format('Y-m-d H:i:s');
	
	$db->query( "UPDATE `cms_order`
		SET 
					`order_status` = " . $data['order_status'] . ",
					`others_order_status` = '". $data['others_order_status']."', 
					`order_status_last_update_date` = '". $updateDate."',
					`order_status_last_update_by` = '". $statusUpdateBy . "' 
		WHERE `order_id` = '". $data['orderId'] ."'"
					);

	if($db->get_affected_rows() == 0)
	{
		$json_data['result'] = '0';
	}
	else
	{
		$json_data['result'] = '1';
	}
	
	
	return json_encode($json_data);
}

?>