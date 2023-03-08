<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

header('Content-Type: text/plain; charset="UTF-8"');
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once('./model/DB_class.php');

function getOrderApproval($data){
    
	$config = parse_ini_file('../config.ini',true);

	$client = $data['client'];
    
	$settings                   = $config[$client];
    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
	$db_name                    = $settings['db'];

	$db = new DB();
	$con_1 = $db->connect_with_given_connection($db_user,$db_pass,$db_name);
    
	$json_data['connection'] = json_encode(array(
		"user"=>$db_user,
		"password"=>$db_pass,
		"db"=>$db_name,
		"client"=>$client,
		"con_success"=>$con_1,
	));


	$query = "select DISTINCT  order_id,cust_company_name,order_date,delivery_date,grand_total

			from cms_order
			
			WHERE cancel_status=0
				AND order_status=1
				AND order_validity=0 OR order_validity=1";

	$db->query($query);


    $view ='';
	$check=0;

	while($result = $db->fetch_array())

	{ 

			$view .= '

			<div style="cursor: pointer;">
				<div onclick="window.location=\'order_approval_details.php?orderId='.$result["order_id"].'&client='.$client.'\'">
					<p class="title" > '.$result["cust_company_name"].' </p>
					<p class="dates" > '.$result["order_date"].' | '.$result["delivery_date"].' </p>
				</div>

				<p class="non_important_text" style="display:inline;"> <strong> RM'.number_format($result["grand_total"],2).'</strong></p>							

				<div class="divider"></div>

				<hr>

			</div>

				';

		$check++;

	}




	if($check == 0){
		$view .= '<p class="no_orders"> No Available Details </p>';
	}


	echo $view;

}

?>

