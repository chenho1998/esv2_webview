<?php

session_start();
//header('Content-Type: text/plain; charset="UTF-8"');
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once('./model/DB_class.php');

$client         	= $_GET['client'];
$doc_type    		= $_GET["doc"];
$userId				= $_GET['userId'];

$goback_link = "https://easysales.asia/esv2/webview/iManage/s_order_view.php?userId={$userId}&roleId=1&client={$client}&doc={$doc_type}";

$salesperson_id 	= $_SESSION["_salesperson_id"];
$orderDateFrom 		= $_SESSION["_odate_from"];
$orderDateTo 		= $_SESSION["_odate_to"];
$deliveryDateFrom	= $_SESSION["_ddate_from"];
$deliveryDateTo		= $_SESSION["_ddate_to"];
$customer_id  		= $_SESSION["_cust_id"];
$viewCancel 		= $_SESSION["_view_cancel"];
$orderStatus 		= $_SESSION["_order_status"];

$config = parse_ini_file('../config.ini',true);
$settings                   = $config[$client];
$db_user                    = $settings['user'];
$db_pass                    = $settings['password'];
$db_name                    = $settings['db'];
$db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

$db = new DB();
$con_1 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);

$db2 = new DB();
$con_2 = $db2->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);


$summary_view = $config['Summary_View'];
$hide_total = in_array($client,$summary_view['hide_total_qty']);

// file_put_contents("s.log",json_encode($summary_view));

if(!empty($doc_type)){

		$result = $db->query("SHOW COLUMNS FROM `cms_order` LIKE 'doc_type'");

		$exists = (mysql_num_rows($result))?TRUE:FALSE;

		if($exists){

			$query = "SELECT order_id, doc_type, order_date, cust_company_name FROM cms_order WHERE ";

			$doc_type				= $doc_type;

		}else{

			$query = "SELECT order_id, order_date, cust_company_name FROM cms_order WHERE ";

			$doc_type = '';

		}

	}


if($salesperson_id != '' && $salesperson_id != '0'){
	$query .= " salesperson_id IN (" . $salesperson_id.")";
	$decider = 1;
}

if($orderDateFrom == ''){
	$orderDateFrom = '1900-01-01';
	$orderDateTo = date("Y-m-d");
}

if($orderDateTo == ''){
	if($decider == 1)
	{
		$query .= " AND order_date >= '" . $orderDateFrom . " 00:00:00' AND order_date <= '". $orderDateFrom . " 23:59:59'";
	}

	else
	{
		$query .= " order_date = '" . $orderDateFrom . " 00:00:00' AND order_date <= '". $orderDateFrom . " 23:59:59'";
		$decider = 1;
	}

}

else{
	if($decider == 1)
	{
		$query .= " AND order_date >= '" . $orderDateFrom . "  00:00:00' AND order_date <= '" . $orderDateTo . " 23:59:59'";
	}
	else
	{

		$query .= " order_date >= '" . $orderDateFrom . "  00:00:00' AND order_date <= '" . $orderDateTo . " 23:59:59'";

		$decider = 1;
	}
}

if($orderStatus != '')
{
	if($decider == 1)
	{
		$query .= " AND cms_order.order_status =" . $orderStatus;
	}

	else
	{
		$query .= " cms_order.order_status =" . $orderStatus;

		$decider = 1;
	}
}

if($viewCancel == 0){
	$query .= " AND cms_order.cancel_status = 0";
}
elseif($viewCancel == 1){
	$query .= " AND cms_order.cancel_status = 2";
}

if($customer_id != '')
{
	if($decider == 1)
	{
		$query .= " AND cms_order.cust_id =" . $customer_id;
	}
	else
	{
		$query .= " cms_order.cust_id =" . $customer_id;

		$decider = 1;
	}
}

if($deliveryDateFrom != '')
{
	if($deliveryDateTo == '')
	{
		if($decider == 1)
		{
			$query .= " AND delivery_date >= '" . $deliveryDateFrom . " 00:00:00' AND delivery_date <= '". $deliveryDateFrom . " 23:59:59'";
		}
		else
		{
			$query .= " delivery_date = '" . $deliveryDateFrom . " 00:00:00' AND delivery_date <= '". $deliveryDateFrom . " 23:59:59'";
			$decider = 1;
		}
	}
	else
	{
		if($decider == 1)
		{
			$query .= " AND delivery_date >= '" . $deliveryDateFrom . "  00:00:00' AND delivery_date <= '" . $deliveryDateTo . " 23:59:59'";
		}
		else
		{
			$query .= " delivery_date >= '" . $deliveryDateFrom . "  00:00:00' AND delivery_date <= '" . $deliveryDateTo . " 23:59:59'";
			$decider = 1;
		}
	}
}

if($doc_type != '')
{
	if($decider == 1)
	{
		$query .= " AND cms_order.doc_type ='" . $doc_type."'";
	}
	else
	{
		$query .= " cms_order.doc_type ='" . $doc_type."'";
		$decider = 1;
	}
}

$db->query($query);

$orderList = array();
$docType ='';
while($result = $db->fetch_array()){
	$orderId = $result['order_id'];

	if($exists){
		if($result['doc_type']){
			$docType = $result['doc_type'];
		}
	}


	$header = 'Sales Order';

	if($docType == 'sales'){
		$header = 'Sales Order';
	}else if($docType == 'cash'){
		$header = 'Cash Sales';
	}else if($docType == 'invoice'){
		$header = 'Invoice';
	}else if($docType == 'consign'){
		$header = 'Consignment';
	}

	$orderDate = $result['order_date'];
	$orderDate = date("Y-m-d H:i:s", strtotime($orderDate));
	$companyName = $result['cust_company_name'];

	$orderData = array(
		"orderId"=>$orderId
		,"docType"=>$header
		,"orderDate"=>$orderDate
		,"companyName"=>$companyName
	);

	$orderList[] = $orderData;
}
	$query2 = "SELECT SUM(dtl.quantity) AS cat_total, if(category_name is null,'General Category',category_name) as category_name FROM cms_order_item dtl INNER JOIN cms_product p ON dtl.product_code = p.product_code LEFT JOIN cms_product_category cat ON p.category_id = cat.category_id WHERE dtl.cancel_status ='0' AND (isParent = 0 OR ( isParent = 1 AND parent_code ='')) AND order_id IN (";

	foreach($orderList as $value){
		$query2 .= "'{$value["orderId"]}',";
	}
	
	$query2 = rtrim($query2, ',');

	$query2 .= ") GROUP BY category_name ORDER BY cat.sequence_no ASC";

	$db2->query($query2);
	$view = '<html><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3pro.css">
	<link href="https://fonts.googleapis.com/css?family=Open Sans" rel="stylesheet">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>EasyTech</title>
	</head>
	<body style="padding:2px;font-family:\'Open Sans\';">';
	$totalQtyForAll = 0;
	while($result2 = $db2->fetch_array()){
		$totalQty = $result2["cat_total"];
		$categoryName = $result2["category_name"];
		$view .="<div style='width:100%;border-radius:3px;z-index:2;border:1px solid #f2f2f2;dispaly:inline-block;margin-bottom:10px;'><div style='line-height:20px;font-weight:bold;padding-left:5px;font-size:14px;border-bottom:1px solid #f2f2f2;'>".$categoryName."</div>";
		$totalQtyForAll += $totalQty;
		$query3 = "SELECT SUM(dtl.quantity) AS total_qty, dtl.product_code, dtl.product_name, category_name, unit_uom FROM cms_order_item AS dtl INNER JOIN cms_product p ON dtl.product_code = p.product_code LEFT JOIN cms_product_category cat ON p.category_id = cat.category_id WHERE dtl.cancel_status ='0' AND (isParent = 0 OR ( isParent = 1 AND parent_code ='')) AND order_id IN (";

		foreach($orderList as $value){
			$query3 .= "'{$value["orderId"]}',";
		}
	
		$query3 = rtrim($query3, ',');

		if($categoryName == 'General Category'){
			$query3 .= ") AND category_name IS NULL ";
		}else{
			$query3 .= ") AND category_name = '".$categoryName."'";
		}

		$query3 .= "GROUP BY dtl.product_code ORDER BY cat.sequence_no ASC ,dtl.product_code";

		$db->query($query3);
		$i = 1;
		while($result3 = $db->fetch_array()){
			$product_code = $result3["product_code"];
			

			$product_name = $result3["product_name"];
			if($product_name ==''){
				$product_name ='<i>No Product Name</i>';
			}
			
			$total_qty = $result3["total_qty"];
			$total_qty .= $result3['unit_uom'] ? " ".$result3['unit_uom'] : '';
			$i++;
			if($i%2==0 && $i != 0){
				$view .= "<p style='line-height:18px;margin-block-start: 0px;margin-block-end: 0px;margin-inline-start: 0px;
				margin-inline-end: 0px;font-size:14px;padding-left:5px;background-color:#F5F5F5;'>".$product_name."</p>";

				$view .= "<p style='line-height:18px;margin-block-start: 0px;margin-block-end: 0px;margin-inline-start: 0px;margin-inline-end: 0px;width:80%; float:left;color:#147efb;padding-left:5px;font-size:14px;background-color:#F5F5F5;'>".$product_code."</p>";

				$view .="<p style='line-height:18px;margin-block-start: 0px;margin-block-end: 0px;margin-inline-start: 0px;margin-inline-end: 0px;color:#147efb;font-size:14px;text-align:right;width:20%;text-align:right;float:left;padding-right:5px;background-color:#F5F5F5;font-weight:bold;'>".$total_qty."</p>";
			}else{
				$view .= "<p style='line-height:18px;margin-block-start: 0px;margin-block-end: 0px;margin-inline-start: 0px;
				margin-inline-end: 0px;font-size:14px;padding-left:5px;background-color:white;'>".$product_name."</p>";

				$view .= "<p style='line-height:18px;margin-block-start: 0px;margin-block-end: 0px;margin-inline-start: 0px;margin-inline-end: 0px;width:80%; float:left;color:#147efb;padding-left:5px;font-size:14px;background-color:white;'>".$product_code."</p>";

				$view .="<p style='line-height:18px;margin-block-start: 0px;margin-block-end: 0px;margin-inline-start: 0px;margin-inline-end: 0px;color:#147efb;font-size:14px;text-align:right;width:20%;text-align:right;float:left;padding-right:5px;background-color:white;font-weight:bold;'>".$total_qty."</p>";
			}
		}

		$totalQty = $hide_total ? '' : $totalQty;		
		$view .="<div style='font-weight:bold; border-top:1px solid #f2f2f2; color:black;font-size:14px; display:inline-block; width:100%;'><span style='width:20%;text-align:right;float:right;padding-right:5px;'>".$totalQty."</span></div></div>";
	

	}
	$view .= "<div style='border:1px solid #147efb; color:#147efb; text-align:center; border-radius:5px; height:30px; line-height: 30px;margin-block-start: 0.5em;margin-block-end: 0em;'>Total Quantity: ".$totalQtyForAll."</div><button style='color:#e8ebef;border: 1px solid #147efb;background: #147efb;width: 100%;display: inline;text-align:center;padding: 5px;box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);transition: 0.3s;z-index: 1;height:35px;margin-top:2px; border-radius:5px;' onclick='location.replace(\"".$goback_link."\")'> Back </button></body></html>";

	// <a href=\"javascript:history.go(-1)\"><button style='color:#e8ebef;border: 1px solid #147efb;background: #147efb;width: 100%;display: inline;text-align:center;padding: 5px;box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);transition: 0.3s;z-index: 1;height:35px;margin-top:2px; border-radius:5px;'> Back </button></a>

	echo $view;

?>

