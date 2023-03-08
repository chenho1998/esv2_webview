
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href='https://fonts.googleapis.com/css?family=Open Sans' rel='stylesheet'>
    <style>
        body {
            font-family: 'Open Sans';font-size: 14px;
            line-height:1;
            user-select: none;
            -webkit-user-select: none;
            -ms-user-select: none;
            -webkit-touch-callout: none;
            -o-user-select: none;
            -moz-user-select: none;
        }
        .card {
            box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
            transition: 0.3s;
            width: 100%;
            border-radius: 5px;
            display: block;
            margin-left: auto;
            margin-right: auto;
            margin-top:2px;
        }

        .card:hover {
            box-shadow: 0 8px 16px 0 rgba(0,0,0,0.2);
        }

        .container {
            padding: 5px 5px 5px 5px;
        }
        .price-row {
            width:100%;
        }
        .item-code {
            color:#147efb;
            padding-right:5px;
            font-size:15px;
        }
        td {
            width:33%
        }
        hr {
            display: block;
            height: 1px;
            border: 0;
            border-bottom: 1px solid #ccc;
        }
        p{
            line-height:14px;;
        }
    </style>
</head>
<body>
<?php

//http://easysales.asia/esv2/webview/iManage/invoice_details.php?invoiceCode=IV1800030&client=golden&custCode=300-0004

require_once('./model/DB_class.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0"); 
header("Cache-Control: post-check=0, pre-check=0", false); 
header("Pragma: no-cache");

date_default_timezone_set('Asia/Kuala_Lumpur');

// no external script...lets make it lite

$invoiceCode ='';
$custCode ='';
$client = '';
$connection = NULL;

$settings = parse_ini_file('../config.ini',true);

$db = new DB();

function replace($str){
    return str_replace(array('#','>'),'',$str);
}

if(isset($_GET['invoiceCode']) && isset($_GET['custCode']) && isset($_GET['client'])){
    $invoiceCode = $_GET['invoiceCode'];
    $custCode = $_GET['custCode'];
    $client = $_GET['client'];
    
    $settings = $settings[$client];

    $db_user = $settings['user'];
    $db_pass = $settings['password'];
    $db_name = $settings['db'];
    $db_host = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

    $connection = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);
    if(!$connection){
        $string = $db_user." <> ".$db_pass." <> ".$db_name." <> ".$invoiceCode." <> ".$custCode." <> ".$client;
        // file_put_contents("./invoice_details.log", date('Y-m-d H:i:s')." <FAIL> ".$string.PHP_EOL, FILE_APPEND);
        echo '
            <div style="margin-top:50%">
                <p style="color:grey;width:100%;text-align:center">
                    Something went wrong. Please contact support
                </p> 
            </div>
        ';
    }
}else{
    header('location : Errorpage.php');
}

// customer info
$invoiceData = array();
$invoiceDetailsData = array();

$sql = "SELECT c.cust_company_name, c.billing_address1,c.billing_address2,c.billing_address3,c.billing_address4, c.cust_incharge_person, c.cust_tel, c.cust_fax, c.termcode ,DATE(i.invoice_date) AS invoice_formatted_date, i.* FROM cms_customer AS c, cms_invoice AS i WHERE c.cust_code ='".mysql_real_escape_string($custCode)."' AND i.invoice_code = '".mysql_real_escape_string($invoiceCode)."'";
$db->query($sql);

if($db->get_num_rows() != 0){
	while($result = $db->fetch_array()){
        
        $invoiceData[] = $result;

        echo '
            <div class="card" style="background-color:#147efb">
                <div class="container">
                    <div style="overflow: hidden;">
                        <p style="float: left;color:white">
                            INV : <b class="item-code" style="color:white">'.$invoiceCode.' </b>
                        </p>
                        <p style="float: right;color:white"> Date : '.$result['invoice_formatted_date'].'</p>
                    </div>
                    <p style="color:white;margin-top:-1px">
                        Amount : <b class="item-code" style="color:white">RM'.$result['invoice_amount'].'</b>
                    </p>
                    <p style="color:white;">
                        '.$result["cust_company_name"].'
                    </p>
                </div>
            </div>
        ';
    }
}

// retrieve invoice info

$sql = "select * from cms_invoice_details where invoice_code = '".mysql_real_escape_string($invoiceCode)."' WHERE active_status = 1";

$db->query($sql);
	
if($db->get_num_rows() != 0){
	while($result = $db->fetch_array()){
        if(!empty($result["item_code"])){
            $result["item_code"] = replace($result["item_code"]);
            $result["item_name"] = replace($result["item_name"]);
            $invoiceDetailsData[] = $result;

            $discount = $result['discount'];
            if(!empty($discount)){
                $discount = " - ({$discount})";
            }
            echo '
                <div class="card">
                    <div class="container">
                        <p class="item-code">'.$result["item_code"].' </p>'.'
                        <p style="color:grey;font-size:12px;font-weight:bold">'.$result["item_name"].'</p>
                        <b>
                            <p>
                                RM'.number_format((float)floatval($result["item_price"]), 2, ".", "").'
                                '.$discount.' x '.intval($result["quantity"]).' '.$result["uom"].' = RM'.number_format((float)floatval($result["total_price"]), 2, ".", "").'
                            </p>
                        </b>
                    </div>
                </div>
            ';
        }
    }
}else{
    echo '
            <div style="margin-top:50%">
                <p style="color:grey;width:100%;text-align:center">
                    No invoice record found
                </p> 
            </div>
        ';
}
?>
<div id="invoiceData" hidden><?php 
$data = json_encode(array(
    "invoice"=>$invoiceData,
    "details"=>$invoiceDetailsData
));
echo $data;
?></div>
</body>
</html>
