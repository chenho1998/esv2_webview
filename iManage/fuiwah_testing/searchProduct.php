<?php
session_start();
header('Content-Type: text/plain; charset="UTF-8"');
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once('./model/DB_class.php');

function searchProduct($data){

	$config = parse_ini_file('../config.ini',true);

	$client = $data['client'];

	$settings                   = $config[$client];
    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
	$db_name                    = $settings['db'];
	$db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

	$db = new DB();
	$con_1 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);

	$query = "SELECT p.product_name, p.product_code, uom.product_uom, uom.product_std_price, p.product_current_quantity, p.product_desc FROM cms_product p
	LEFT JOIN cms_product_uom_price_v2 uom ON uom.product_code = p.product_code AND uom.product_default_price = 1
	WHERE p.product_status = 1 AND p.product_code NOT IN (SELECT product_code FROM cms_order_item WHERE order_id = '".$data['orderId']."' AND cancel_status = 0)";

	$product_name_query = '';
	$product_code_query = '';
	if($data['text']){
		$text = explode(" ", $data['text']);

		for($i = 0; $i < count($text); $i ++){
			$input_name = $text[$i];
			if($input_name){
				if($i != 0){
					$product_name_query .= " AND p.product_name LIKE '%".$input_name."%'";
					$product_code_query .= " AND p.product_code LIKE '%".$input_name."%'";
				}else{
					$product_name_query .= "p.product_name LIKE '%".$input_name."%'";
					$product_code_query .= "p.product_code LIKE '%".$input_name."%'";
				}

			}
		}
	}

	if($product_name_query && $product_code_query){
		$query .= " AND (".$product_name_query.") OR (".$product_code_query.")";
	}

	$db->query($query);

	if($db->get_num_rows() != 0)
	{
		$view = "";
		$i = 1;
		while($result = $db->fetch_array())
		{
			/* <tr>
					<td>
						
						<p style="color:#000;font-weight:bold"> (1) BAWAL PUTIH (L)</p>
						
						
						<p class="packing-status">  </p>
						<p class="packing-status"><span><i>Waiting for Packing</i></span></p>
						
						<div class="row">
							<div class="column" height="auto">
								<center style="color:black">RM66/KG</center>
							</div>
							<div class="column" style="padding-right:5px;width:24%">
								<center style="color:#147efb;font-weight:bold;">QTY 1</center>
							</div>
							<div class="column">
								<center style="color:black;font-weight:bold;">RM79.20</center>
							</div>
						</div>
					</td>
				</tr> */

			$view .= '<tr><td style="width:80%;"><p style="color:#000;font-weight:bold">('.$i.')'.$result['product_name'].'</p><p><i>'.$result['product_code'].'</i></p>';

			if($result['product_desc']){
				$view .= '<p>'.$result['product_desc'].'</p>';
			}

			$result['product_std_price'] = number_format((float)$result['product_std_price'], 2, '.', '');

			if($result['product_uom']){
				$view .= '<p style="color:black;">RM'.$result['product_std_price'].'/'.$result['product_uom'].'</p>';
			}

			if($result['product_current_quantity']!=''){
				$view .= '<p style="color:#147efb;font-weight:bold;">Aval Qty '.$result['product_current_quantity'].'</p>';
			}

			$view .= '<td style="width:20%;"><button onclick="addProduct(\''.$result['product_code'].'\')" style="min-width:100%;min-height:100%;" class="radius non_important_text buttons text_white"> ADD </button></td></tr><tr><td colspan="2" style="border-top:1px solid #eee;"></td></tr>';

			$i ++;
		}
	}

	$json_data['view'] = $view;

	return json_encode($json_data);
}

function addProduct($data){

	$config = parse_ini_file('../config.ini',true);

	$client = $data['client'];

	$settings                   = $config[$client];
    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
	$db_name                    = $settings['db'];
	$db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

	$db = new DB();
	$con_1 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);

	$number = mt_rand(100000000,999999999);

	$query = "SELECT p.product_id, p.product_name, p.product_code, uom.product_uom, uom.product_std_price, p.product_current_quantity, p.product_desc FROM cms_product p
	LEFT JOIN cms_product_uom_price_v2 uom ON uom.product_code = p.product_code AND uom.product_default_price = 1
	WHERE p.product_code = '".$data['productCode']."'";

	$db->query($query);

	if($db->get_num_rows() != 0)
	{
		while($result = $db->fetch_array())
		{
			$sql = "INSERT into cms_order_item (order_id,ipad_item_id,product_code,product_id,product_name,unit_uom,uom_id,sequence_no,packing_status,cancel_status,quantity,disc_1,disc_2,disc_3,discount_method,unit_price,isParent,parent_code) SELECT '" . $data['orderId'] . "','" . $number . "','" . $result['product_code'] . "','" . $result['product_id'] . "','" . $result['product_name'] . "','" . $result['product_uom'] . "','0', MAX(sequence_no) + 1 AS sequence_no, '0','0','0','0','0','0','PercentDiscountType','" . $result['product_std_price'] . "','0','' FROM cms_order_item WHERE order_id = '" . $data['orderId'] . "'";
		}
	}

	if($db->query($sql)){
		$json_data = array(
					'message'=>'Product added, please adjust the quantity and price.'
					,'success'=>'1'
				);
		
	}
	else
	{
		$json_data = array(
					'message'=>'Product failed to add.'
					,'success'=>'0'
				);
	}

	return json_encode($json_data);
}

?>

