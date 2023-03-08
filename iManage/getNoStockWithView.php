<?php

session_start();
header('Content-Type: text/plain; charset="UTF-8"');
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once('./model/DB_class.php');

function getNoStockWithView($data)
{
	$client                     = $data['client'];
	


    $config = parse_ini_file('../config.ini',true);

	$settings                   = $config[$client];

    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
	$db_name                    = $settings['db'];
	$db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

	$db = new DB();
	$con_1 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);

	$json_data['connection'] = json_encode(array(
		"user"=>$db_user,
		"password"=>$db_pass,
		"db"=>$db_name,
		"client"=>$client,
		"con_success"=>$con_1
	));

	$_SESSION["_sdate_from"] 	= empty($data['dateFrom'])?'': $data['dateFrom'];
    $_SESSION["_sdate_to"] 		= empty($data['dateTo'])?'': $data['dateTo'];

	$date_query = '';

	$decider = 0;

	// check here
	$db->query("SHOW COLUMNS FROM `cms_order_item` LIKE 'packed_qty'");
	if($db->get_num_rows() == 0){
		$db->query("ALTER TABLE `cms_order_item` ADD `packed_qty` int(10) NOT NULL default '0';");
	}

	if($data['dateTo'] == ''){
		$date_query = "cms_order.order_date BETWEEN '" . $data['dateFrom'] . " 00:00:00' AND '". $data['dateFrom'] . " 23:59:59' ";
		// $query .= " order_date >= '" . $data['dateFrom'] . " 00:00:00' AND order_date <= '". $data['dateFrom'] . " 23:59:59'";
	}
	else{
		// $query .= " order_date >= '" . $data['dateFrom'] . " 00:00:00' AND order_date <= '" . $data['dateTo'] . " 23:59:59'";
		$date_query = "cms_order.order_date BETWEEN '" . $data['dateFrom'] . " 00:00:00' AND '". $data['dateTo'] . " 23:59:59' ";
	}

	$query = "SELECT cms_order.cust_company_name, cms_order_item.product_name, cms_product.product_desc, cms_order_item.product_code, cms_order.cust_code,cms_order_item.packed_qty, cms_order_item.quantity, cms_order.order_date, cms_order_item.packing_status,'N/A' AS packer_note, max_message.message FROM cms_order LEFT JOIN cms_order_item ON cms_order.order_id = cms_order_item.order_id
			LEFT JOIN
			(
              SELECT    MAX(id) message_id,order_item_id,MAX(message) message,MAX(read_status) read_status
              FROM      cms_order_item_message
              GROUP BY  order_item_id
        	) max_message
			ON cms_order_item.order_item_id = max_message.order_item_id
			 LEFT JOIN cms_product on cms_order_item.product_code = cms_product.product_code  
			WHERE " . $date_query . "   
			AND (cms_order_item.packing_status = 2 OR max_message.read_status = 0 OR max_message.read_status = 1 OR (cms_order_item.packing_status = 1 AND cms_order_item.packed_qty <> cms_order_item.quantity)) AND cms_order.cancel_status = 0
			AND cms_order.salesperson_id = '" . $data['salespersonId']. "' ORDER BY FIELD(cms_order_item.packing_status,'2','1','3')
			";

	/*$query = "SELECT cms_order.cust_company_name, cms_order_item.product_name, cms_product.product_desc, cms_order_item.product_code, cms_order.cust_code,cms_order_item.packed_qty, cms_order_item.quantity, cms_order.order_date, cms_order_item.packing_status,'N/A' AS packer_note, max_message.message FROM cms_order LEFT JOIN cms_order_item ON cms_order.order_id = cms_order_item.order_id
	LEFT JOIN
	(
		SELECT    MAX(id) message_id,order_item_id,MAX(message) message,MAX(read_status) read_status
		FROM      cms_order_item_message
		GROUP BY  order_item_id
	) max_message
	ON cms_order_item.order_item_id = max_message.order_item_id
		LEFT JOIN cms_product on cms_order_item.product_code = cms_product.product_code  
	WHERE " . $date_query . "   
	AND (cms_order_item.packing_status = 2 OR cms_order_item.packing_status = 3 OR max_message.read_status = 0 OR max_message.read_status = 1 OR (cms_order_item.packing_status = 1 AND cms_order_item.packed_qty <> cms_order_item.quantity)) AND cms_order.cancel_status = 0
	AND cms_order.salesperson_id = '" . $data['salespersonId']. "' ORDER BY FIELD(cms_order_item.packing_status,'2','1','3')
	";*/

	$db->query($query);
	$values = array();

	if($db->get_num_rows() != 0)
	{
		while($result = $db->fetch_array())
		{
			$itemArr = array(
						'product_name'=>$result['product_name'],
						'product_code'=>$result['product_code'],
						'product_desc'=>$result['product_desc'],
						'cust_code'=>$result['cust_code'],
						'quantity'=>$result['quantity'],
						'order_date'=>$result['order_date'],
						'packing_status'=>$result['packing_status'],
						'message'=>$result['message']);

			// $values[$result['cust_company_name']][] = $itemArr;

			$packingStatus='';
			$color = '#147efb';
			$hidden = '';
			$noStockColor = 'black';
			if($result['packing_status'] == 1 || $result['packing_status'] == 3){
				$hidden = 'hidden';
				$color = 'rgba(128,128,128,0.6)';
			}

			$separator = '';

			if($result['packing_status'] == 1 && $result['packed_qty'] != 0 && $result['quantity'] <> $result['packed_qty']){
				$packingStatus = "Partial No Stock ({$result['packed_qty']}) ";
				$hidden = '';
				$noStockColor = 'rgb(255,85,45)';
				$separator = '| ';
			}
			
            if ($result['packing_status'] == 2 || $result['packing_status'] == 3) {
				$packingStatus = 'No Stock ';
				$noStockColor = 'rgb(255,85,45)';
				$separator = '| ';
			}

            $label ='<div style="min-height: 30px; overflow: hidden;">
                <p style="background-color:'.$color.';text-align:center; line-height: 25px" class="dropdown-div text_white">'.$result[cust_company_name].'
                        <label '.$hidden.' style="float:right; padding-right:1em; line-height:30px">
        	                <input type="checkbox" id="showCancelBox" onclick="informedCustomer(\''.$result["cust_code"].'\',\''.$data['salespersonId'].'\',\''.$client.'\')">
        	                <div class="checkbox_hover"></div>
                     	</label>
                    </p>

             	</div>';


            $view  = '
			    <div style="cursor: pointer;padding-left:5px">
					<p class="title" >'.$result['product_code'].' : '.$result['product_name'].'</p>';

			if($result['product_desc']!=""){
				$view  .= '<p class="description" >'.$result['product_desc'].'</p>';
			}

			$result['picker_note'] = trim($result['picker_note']);
			$result['packer_note'] = trim($result['packer_note']);

			$picker_note = '';/*$result['picker_note'] ? 
								'<p class="description" >Picker note : '.$result['picker_note'].'</p>'
								: '';*/
			$checker_note = ''; /*$result['packer_note'] ? 
								'<p class="description" >Checker note : '.$result['packer_note'].'</p>'
								: '';*/

			
			$view  .='
					'.$picker_note.'
					'.$checker_note.'
                    <p class="dates" > QTY : <strong>'.$result['quantity'].'</strong> </p>
					<p class="dates" style="color:'.$noStockColor.';font-size:15px;font-weight:bold" > '.$packingStatus.'<font style="color:black;font-size:16px;font-weight:normal">'.$separator.$result["order_date"].' </font></p>
					<p class="message non_important_text" > '.$result['message'].' </p>
				</div><hr>';

			$values[$label][] = $view;
			//$values['query'] = $query;
			
		}
	}

	return json_encode($values);
}
?>
