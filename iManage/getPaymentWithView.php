<?php
session_start();
header('Content-Type: text/plain; charset="UTF-8"');
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once('./model/DB_class.php');

function checkNull ($value){
	if($value == 'null' || $value == 'undefined' || !$value){
		return '';
	}
	return $value;
}

function getPaymentWithView($data){
	
	$_SESSION["_date_from"] 	= checkNull($data['dateFrom']);
    $_SESSION["_date_to"] 		= checkNull($data['dateTo']);
	$_SESSION["_cust_id"] 		= checkNull($data['customer_code']);
    $_SESSION["_payment_status"] 	= checkNull($data['payment_status']);
    //$_SESSION["_view_cancel"] 	= checkNull($data['showCancel']);
	//$_SESSION["_backlink"]		= _isNull($data['backlink']);
	$_SESSION["_salesperson_id"]= checkNull($data['salespersonId']);	
	// $_SESSION['_customer_name']	= _isNull($data['customer_name']);

	$config = parse_ini_file('../config.ini',true);
	
	$client = $data['client'];
	$userId = $data['userId'];

	$settings                   = $config[$client];

    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
	$db_name                    = $settings['db'];
	$db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

	$db = new DB();
	$con_1 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);


	// Search condition 
	$where = "";
	if($userId == '0'){
		if(isset($data['salespersonId']) && $data['salespersonId'] != ''&& $data['salespersonId'] != '0') {
			$where .= ($where != "" ? " AND " : " WHERE ") . " salesperson_id = '" . mysql_real_escape_string($data['salespersonId']) . "' ";
		}
	}else{
		if(isset($userId) && $userId != '') {
			$where .= ($where != "" ? " AND " : " WHERE ") . " salesperson_id = '" . mysql_real_escape_string($userId) . "' ";
		}
	}

	
	if(isset($data['dateFrom']) && $data['dateFrom'] != '') {
		$dateTime = date('Y-m-d' , strtotime($data['dateFrom'])) . " 00:00:00";
		$where .= ($where != "" ? " AND " : " WHERE ") . " payment_date >= '" . mysql_real_escape_string($dateTime) . "' ";
	}
	
	if(isset($data['dateTo']) && $data['dateTo'] != '') {
		$dateTime = date('Y-m-d' , strtotime($data['dateTo'])) . " 23:59:59";
		$where .= ($where != "" ? " AND " : " WHERE ") . " payment_date <= '" . mysql_real_escape_string($dateTime) . "' ";
	}
	
	//$where .= ($where != "" ? " AND " : " WHERE ") . " (payment_status = '1' or payment_status = '2')";
	
	if(isset($data['customer_code']) && $data['customer_code'] != '') {
		$where .= ($where != "" ? " AND " : " WHERE ") . " p.cust_code = '" . mysql_real_escape_string($data['customer_code']) . "'";
	}

	$where .= ($where != "" ? " AND " : " WHERE ") . " p.cancel_status = 0";
	
	$sql = "
		SELECT *, c.cust_company_name FROM cms_payment p LEFT JOIN cms_customer c ON p.cust_code = c.cust_code
	" . $where . " ORDER BY p.`payment_status_last_update_date` DESC";
	// file_put_contents("payment.log",$sql,FILE_APPEND);
	$db->query($sql);
	
	if($db->get_num_rows() != 0) {
		//$view = '<p style="text-align:center; line-height: 25px;" class="dropdown-div text_white"> Confirmed Payments </p>';
		$view = '';
		while($result = $db->fetch_array())
		{
			$view .= '
			<div style="cursor: pointer;display:inline-block;width:100%;">';

			$view .= '
			<div onclick="window.location=\'newviewPaymentPage.php?paymentId='.$result["payment_id"].'&client='.$client.'&userId='.$data['userId'].'\'">
					
				<p class="title" style="color:grey;font-size:13px;line-height:0;margin-top:5px"> '.$result["payment_id"].' </p>
				<p class="title" > '.$result["cust_company_name"].' </p>
				<p class="dates" > '.date('d/m/Y h:i A',strtotime($result["payment_transfer_received_date"])).'</p>
				<p class="dates" style="font-weight:bold;">RM '.number_format($result["payment_amount"],2).' </p>
				</div>';
				if($userId=='0' && $result['payment_status'] == '1'){
			
					$view .= '<div class="check" style="float:left;width:23%;">
						<label class="checkbox_rounded">';
						
						if($result['checked'] == '1'){
							$view .='<input type="checkbox" id="checkedPayment" checked onclick="uncheckedPayment(\''.$result["payment_id"].'\',\''.$client.'\',\''.$userId.'\')">';
						}else{
							$view .='<input type="checkbox" id="checkedPayment" onclick="checkedPayment(\''.$result["payment_id"].'\',\''.$client.'\',\''.$userId.'\')">';
						}
	
						$view .= '<div class="checkbox_hover"></div>
						</label>
						<p class="non_important_text" style="margin-left:5px;">Checked</p>
					</div>';
					
					$transferMessage = array(
						"action"=>"transfer",
						"id"=>$result["payment_id"]
					);
					
					$transferMessage = json_encode($transferMessage);
					$transferMessage = str_replace('"','\"',$transferMessage);

					$deleteMessage = array(
						"action"=>"delete",
						"id"=>$result["payment_id"]
					);

					$deleteMessage = json_encode($deleteMessage);
					$deleteMessage = str_replace('"','\"',$deleteMessage);

					$viewRow = '
						<div style="width:25%;float:left;">
							<input 
								onclick="this.disabled=true;parent.postMessage(\'@transfer\',\'*\')"  
								type="button" 
								value="Transfer" 
								style="width:100%; border: 1px solid #147efb;text-align: center;box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);transition: 0.3s;background: #147efb;color: #e8ebef;border-radius: 3px;padding:5px;" data-id="'.$result["payment_id"].'">
						</div>
						<div style="width:25%;float:left;margin-left:5px">
							<input 
								onclick="this.disabled=true;parent.postMessage(\'@edit\',\'*\')"  
								type="button" 
								value="Edit" 
								style="width:100%; border: 1px solid #ffae42;text-align: center;box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);transition: 0.3s;background: #ffae42;color: white;border-radius: 3px;padding:5px;" data-id="'.$result["payment_id"].'">
						</div>
						<div style="width:25%;float:left;">
							<input 
								onclick="this.disabled=true;parent.postMessage(\'@delete\',\'*\')" 
								type="button" 
								value="Delete" 
								style="width:100%; border: 1px solid rgb(255,85,45);text-align: center;box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);transition: 0.3s;background: rgb(255,85,45);color: #e8ebef;border-radius: 3px;padding:5px;margin-left:5px;" 
								data-id="'.$result["payment_id"].'">
						</div>';

					$viewRow = str_replace('@transfer',"T|".$result["payment_id"],$viewRow);
					$viewRow = str_replace('@delete',"D|".$result["payment_id"],$viewRow);
					$viewRow = str_replace('@edit',"E|".$result["payment_id"],$viewRow);

					$view .= $viewRow;
					
				}
				if($result['payment_status'] == 2){
					$view .= '<div style="width:33%;float:left;margin-left:-5px"><input type="button" value="Successful" style="width:100%; border: 1px solid #3CB371;text-align: center;box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);transition: 0.3s;background: #3CB371;color: #e8ebef;border-radius: 3px;padding:5px;margin-left:5px;" data-id="'.$result["payment_id"].'"></div>';
				}
				if($result['payment_status'] == 0){
					$view .= '<div style="width:33%;float:left;margin-left:-5px"><input disabled type="button" value="Active" style="width:100%; border: 1px solid grey;text-align: center;box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);transition: 0.3s;background: grey;color: white;border-radius: 3px;padding:5px;margin-left:5px;" data-id="'.$result["payment_id"].'"></div>';
				}
			$view .='<div class="divider"></div>
			</div><hr>
			';
		}
	}

	$json_data['data'] = array();
	if(isset($view)) {
		$json_data['data'][] = $view;
	}
	$json_data['sql'] = $sql;
	return json_encode($json_data);
}

function checkedPayment($data){

	$config = parse_ini_file('../config.ini',true);
	
	$client = $data['client'];
	$userId = $data['userId'];

	$settings                   = $config[$client];

    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
	$db_name                    = $settings['db'];
	$db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

	$db = new DB();
	$con_1 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);

	$paymentId = $data['payment_id'];
	$update = "UPDATE cms_payment SET checked = '1' WHERE payment_id = '".$paymentId."'";

	if($paymentId){
		$db->query($update);
	}
	if($db->get_affected_rows()>0){
		$json_data['data'] = $paymentId;
	}
	return json_encode($json_data);
}

function uncheckedPayment($data){
	$config = parse_ini_file('../config.ini',true);
	
	$client = $data['client'];
	$userId = $data['userId'];

	$settings                   = $config[$client];

    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
	$db_name                    = $settings['db'];
	$db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

	$db = new DB();
	$con_1 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);

	$paymentId = $data['payment_id'];
	$update = "UPDATE cms_payment SET checked = '0' WHERE payment_id = '".$paymentId."'";
	if($paymentId){
		$db->query($update);
	}
	if($db->get_affected_rows()>0){
		$json_data['data'] = $paymentId;
	}
	return json_encode($json_data);
}
?>