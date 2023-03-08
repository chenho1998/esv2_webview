<?php
header('Content-Type: text/plain; charset="UTF-8"');
require_once('./model/MySQL.php');

if(isset($_GET['client'])){
    $config = parse_ini_file('../config.ini',true);

	$client                     = $_GET['client'];

	$settings                   = $config[$client];

    $mysql = new MySQL($settings);

    $cust_code = MySQL::sanitize($_POST['cust_code']);
    $product_list = json_decode($_POST['productList'],true);

    $result = array();

    for ($i=0; $i < count($product_list); $i++) { 
        $product_code = MySQL::sanitize($product_list[$i]);
        
        $invoice_details = $mysql->Execute("select cid.*, i.cust_code, i.invoice_date from cms_invoice i left join cms_invoice_details cid on i.invoice_code = cid.invoice_code where cust_code = '{$cust_code}' and item_code = '{$product_code}' and active_status = 1 order by invoice_date desc limit 1;");

        if (count($invoice_details) > 0){
            $result[] = $invoice_details[0];
        }
    }

    echo json_encode($result);
}
?>