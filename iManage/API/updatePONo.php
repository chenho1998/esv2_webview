<?php
header('Content-Type: text/plain; charset="UTF-8"');
require_once('model/DB_class.php');

function updatePONo($data)
{
    $config                     = parse_ini_file('../config.ini',true);
    $client                     = $data['client'];
    $settings                   = $config[$client];
    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
    $db_name                    = $settings['db'];
    $db_host                    = $settings['host'] ?  $settings['host'] : 'easysales.asia';


    $db     = new DB();
    $con_1  = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);
    
    $json_data = array(
			'result'=> ""
			,'action'=>"updatePONo"
			,'data'=>array()
			,"message"=> ""
			,"message_code"=> ""
	);

	$db->query("SELECT order_udf FROM cms_order WHERE order_id = '".$data['order_id']."'");
	if($db->get_num_rows() != 0)
	{
		while($result = $db->fetch_array())
		{
			$order_udf = json_decode($result['order_udf'],true);
			if(count($order_udf) > 0){
				$found = 0;
				for ($i=0; $i < count($order_udf); $i++) { 
					if($order_udf[$i]['code'] == 'po_no'){
						$order_udf[$i]['value'] = $data['po_no'];
						$found = 1;
					}
				}

				if($found){
					$db->query("UPDATE `cms_order` SET `order_udf` = '".json_encode($order_udf)."' WHERE `order_id` = '". $data['order_id'] ."'");
					if($db->get_affected_rows() == 0)
					{
						$json_data['result'] = '0';
						$json_data['message'] = 'Failed update.';
					}
					else
					{
						$json_data['result'] = '1';
						$json_data['message'] = 'Updated successfully.';
					}
					return json_encode($json_data);
				}else{
					$order_udf[] = array(
						"code"=>"po_no",
						"title"=>"PO Number",
						"item"=>array(
							"name"=>"PO Number",
							"required"=>"0",
							"value"=>"1",
						),
						"selected"=>"0",
						"type"=>"input",
						"value"=>$data['po_no']
					);

					$db->query("UPDATE `cms_order` SET `order_udf` = '".json_encode($order_udf)."' WHERE `order_id` = '". $data['order_id'] ."'");
					if($db->get_affected_rows() == 0)
					{
						$json_data['result'] = '0';
						$json_data['message'] = 'Failed update.';
					}
					else
					{
						$json_data['result'] = '1';
						$json_data['message'] = 'Updated successfully.';
					}
					return json_encode($json_data);
				}
			}else{
				$update = '[{"code": "po_no", "title": "PO Number", "item": { "name": "PO Number", "required": "0", "value": "1" }, "selected": 0, "type": "input", "value": "'.$data['po_no'].'"}]';

				$db->query("UPDATE `cms_order` SET `order_udf` = '".$update."' WHERE `order_id` = '". $data['order_id'] ."'");
				if($db->get_affected_rows() == 0)
				{
					$json_data['result'] = '0';
					$json_data['message'] = 'Failed update.';
				}
				else
				{
					$json_data['result'] = '1';
					$json_data['message'] = 'Updated successfully.';
				}
			}
		}
	}

	return json_encode($json_data);
}

?>