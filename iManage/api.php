<?php
header('Content-Type: text/plain; charset="UTF-8"');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
header('Access-Control-Allow-Credentials: True');
// error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
session_start();
date_default_timezone_set('Asia/Kuala_Lumpur');
// ini_set('display_errors', TRUE);
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
loaderAllAPI();

if ($_GET['action'] == 'getUserLogin')
{
    // echo $_POST['data'];

    $json_object = json_decode($_POST['data'], true);

    $data = $json_object["getUserLogin_data"];

    if(check_data_type_Iscorrect($data, "getUserLogin"))
    {
    	echo getUserLogin($data);
    }
}

if ($_GET['action'] == 'getSalespersonCredential')
{
    echo getSalespersonCredential($data);
}

if ($_GET['action'] == 'getStockQuantity')
{
    // echo $_POST['data'];

    $json_object = json_decode($_POST['data'], true);

    $data = $json_object["getStockQuantity_data"];

    if(check_data_type_Iscorrect($data, "getStockQuantity"))
    {
	echo getStockQuantity($data);
    }
}

if ($_GET['action'] == 'transferOrder')
{

    $json_object = json_decode($_POST['data'], true);

    $data = $json_object["transferOrder_data"];

    if(check_data_type_Iscorrect($data, "transferOrder"))
    {
    	echo transferOrder($data);
    }
}

if ($_GET['action'] == 'transferOrder_v2')
{

    $json_object = json_decode($_POST['data'], true);

    $data = $json_object["transferOrder_data"];

    if(check_data_type_Iscorrect($data, "transferOrder_v2"))
    {
        echo transferOrder_v2($data);
    }
}

if ($_GET['action'] == 'searchTransferredOrder')
{
    // echo $_POST['data'];

    $json_object = json_decode($_POST['data'], true);

    $data = $json_object["searchTransferredOrder_data"];

    if(check_data_type_Iscorrect($data, "searchTransferredOrder"))
    {
	echo searchTransferredOrder($data);
    }
}

if ($_GET['action'] == 'editOrderPaymentDetails')
{
    // echo $_POST['data'];

    $json_object = json_decode($_POST['data'], true);

    $data = $json_object["editOrderPaymentDetails_data"];

    if(check_data_type_Iscorrect($data, "editOrderPaymentDetails"))
    {
	echo editOrderPaymentDetails($data);
    }
}

if ($_GET['action'] == 'getLastUpdateDate')
{
    // echo $_POST['data'];

    $json_object = json_decode($_POST['data'], true);

    $data = $json_object["getLastUpdateDate_data"];

    if(check_data_type_Iscorrect($data, "getLastUpdateDate"))
    {
	echo getLastUpdateDate($data);
    }
}

if ($_GET['action'] == 'getCustomerList')
{
    // echo $_POST['data'];

    $json_object = json_decode($_POST['data'], true);

    $data = $json_object["getCustomerList_data"];

    if(check_data_type_Iscorrect($data, "getCustomerList"))
    {
	   echo getCustomerList($data);
    }
}

if ($_GET['action'] == 'getProductList')
{
    echo getProductList();
}

if ($_GET['action'] == 'insertSellingPrice')
{
    // echo $_POST['data'];

    $json_object = json_decode($_POST['data'], true);

    $data = $json_object["insertSellingPrice_data"];

    if(check_data_type_Iscorrect($data, "insertSellingPrice"))
    {
	echo insertSellingPrice($data);
    }
}

if ($_GET['action'] == 'getSellingPrice')
{
    // echo $_POST['data'];

    $json_object = json_decode($_POST['data'], true);

    $data = $json_object["getSellingPrice_data"];

    if(check_data_type_Iscorrect($data, "getSellingPrice"))
    {
	echo getSellingPrice($data);
    }
}

if ($_GET['action'] == 'changeOrderStatus')
{
    // echo $_POST['data'];

    $json_object = json_decode($_POST['data'], true);

    $data = $json_object["changeOrderStatus_data"];

    if(check_data_type_Iscorrect($data, "changeOrderStatus"))
    {
	echo changeOrderStatus($data);
    }
}

if ($_GET['action'] == 'getGeneralInfo')
{
    echo getGeneralInfo();
}

if ($_GET['action'] == 'getSalesPersonCustomerlist')
{
    // echo $_POST['data'];

    $json_object = json_decode($_POST['data'], true);

    $data = $json_object["getSalesPersonCustomerlist_data"];

    if(check_data_type_Iscorrect($data, "getSalesPersonCustomerlist"))
    {
	echo getSalesPersonCustomerlist($data);
    }
}

if ($_GET['action'] == 'getAllCategory')
{
    $json_object = json_decode($_POST['data'], true);
    $data = $json_object["date"];
    foreach($json_object as $key => $value) {
      if ($key == "date" && $data == "")
        $data = " ";
    }
    $data = substr($data, 0, 10);
    if(check_data_type_Iscorrect($data, "getAllCategory"))
    {
        echo getAllCategory($data);
        // echo getAllCreditNote($data);
    }
    // echo getAllCategory();
}

if ($_GET['action'] == 'getAllProduct')
{
    $json_object = json_decode($_POST['data'], true);
    $data = $json_object["date"];
    foreach($json_object as $key => $value) {
      if ($key == "date" && $data == "")
        $data = " ";
    }
    $data = substr($data, 0, 10);
    if(check_data_type_Iscorrect($data, "getAllProduct"))
    {
        echo getAllProduct($data);
        // echo getAllCreditNote($data);
    }

}

if ($_GET['action'] == 'getAllProductAttribute')
{
    echo getAllProductAttribute();
}

if ($_GET['action'] == 'getAllProductImage')
{
    $json_object = json_decode($_POST['data'], true);
    $data = $json_object["date"];
    foreach($json_object as $key => $value) {
      if ($key == "date" && $data == "")
        $data = " ";
    }
    $data = substr($data, 0, 10);
    if(check_data_type_Iscorrect($data, "getAllProductImage"))
    {
         echo getAllProductImage($data);
        // echo getAllCreditNote($data);
    }
}

if ($_GET['action'] == 'getAllProductOptionalItem')
{
    echo getAllProductOptionalItem();
}

if ($_GET['action'] == 'getAllProductUOMPrice')
{
    $json_object = json_decode($_POST['data'], true);
    $data = $json_object["date"];
    foreach($json_object as $key => $value) {
      if ($key == "date" && $data == "")
        $data = " ";
    }
    $data = substr($data, 0, 10);
    if(check_data_type_Iscorrect($data, "getAllProductUOMPrice"))
    {
        echo getAllProductUOMPrice($data);
    }
    // echo getAllProductUOMPrice();
}

if ($_GET['action'] == 'getAllUOM')
{
    $json_object = json_decode($_POST['data'], true);
    $data = $json_object["date"];
    foreach($json_object as $key => $value) {
      if ($key == "date" && $data == "")
        $data = " ";
    }
    $data = substr($data, 0, 10);
    if(check_data_type_Iscorrect($data, "getAllUOM"))
    {
        echo getAllUOM($data);
    }
    // echo getAllUOM();
}

if ($_GET['action'] == 'getAllSpecialPrice')
{
    $json_object = json_decode($_POST['data'], true);
    $data = $json_object["date"];
    foreach($json_object as $key => $value) {
      if ($key == "date" && $data == "")
        $data = " ";
    }
    $data = substr($data, 0, 10);
    if(check_data_type_Iscorrect($data, "getAllSpecialPrice"))
    {
        echo getAllSpecialPrice($data);
    }
    // echo getAllUOM();
}

if ($_GET['action'] == 'getAllOptionalItem')
{
    echo getAllOptionalItem();
}

if ($_GET['action'] == 'getAllDocument')
{
    echo getAllDocument();
}

if ($_GET['action'] == 'getAllVideo')
{
    echo getAllVideo();
}

if ($_GET['action'] == 'getAllOrderStatus')
{
    echo getAllOrderStatus();
}

// new function - 2014-7-16

if ($_GET['action'] == 'getOrderStatusForPicker')
{
    echo getOrderStatusForPicker();
}

/* new function 2015-03-19 */

if ($_GET['action'] == 'testConnection')
{
	echo testConnection();
}

/* new function 2017-10-25 */

if ($_GET['action'] == 'testConnection_v2')
{
    $json_object = json_decode($_POST['data'], true);
    $data = $json_object["testConnectionv2_data"];

    // echo testConnection_v2();
    if(check_data_type_Iscorrect($data, "testConnection_v2"))
    {
        echo testConnection_v2($data);
    }
}

/* api to get aging report */
if ($_GET['action'] == 'getAllReceipt')
{
    $json_object = json_decode($_POST['data'], true);
    // $data = $json_object["date"];
    // // if ($data == "")
    // //     $data = " ";
    // foreach($json_object as $key => $value) {
    //   if ($key == "date" && $data == "")
    //     $data = " ";
    // }
    $data = $json_object;
    if(check_data_type_Iscorrect($data, "getAllReceipt"))
    {
        echo getAllReceipt($data);
    }
}

if ($_GET['action'] == 'getAllInvoice')
{
    $json_object = json_decode($_POST['data'], true);
    // $data = $json_object["date"];
    // foreach($json_object as $key => $value) {
    //   if ($key == "date" && $data == "")
    //     $data = " ";
    // }
    // if( $json_object["date"] == ""){
    //     $json_object["date"] = date("Y-m-d");
    // }
    $data = $json_object;
    // echo $data["salespersonid"], " "," a",$data["date"];
    if(check_data_type_Iscorrect($data, "getAllInvoice"))
    {
        echo getAllInvoice($data);
    }
}

if ($_GET['action'] == 'getAllJournal')
{
    $json_object = json_decode($_POST['data'], true);
    // $data = $json_object["date"] ;
    // foreach($json_object as $key => $value) {
    //   if ($key == "date" && $data == "")
    //     $data = " ";
    // }
    $data = $json_object;
    if(check_data_type_Iscorrect($data, "getAllJournal"))
    {
        echo getAllJournal($data);
    }

}

if ($_GET['action'] == 'getAllDebitNote')
{
    $json_object = json_decode($_POST['data'], true);
    // $data = $json_object["date"];
    // foreach($json_object as $key => $value) {
    //   if ($key == "date" && $data == "")
    //     $data = " ";
    // }
    $data = $json_object;

    if(check_data_type_Iscorrect($data, "getAllDebitNote"))
    {
        echo getAllDebitNote($data);
    }
}

if ($_GET['action'] == 'getAllCreditNote')
{
    $json_object = json_decode($_POST['data'], true);
    // $data = $json_object["date"];
    // foreach($json_object as $key => $value) {
    //   if ($key == "date" && $data == "")
    //     $data = " ";
    // }
    $data = $json_object;

    if(check_data_type_Iscorrect($data, "getAllCreditNote"))
    {
        echo getAllCreditNote($data);
    }
}


/* end of new function */

/* api to unzip file */
if ($_GET['action'] == 'uploadExcel')
{

    $docUrl = $_POST['DocURL'];
    $docName = $_POST['DocName'];
    $configPath = $_POST['configPath'];
    $uomName = $_POST['uomName'];
    $optionalName = $_POST['optionalName'];
    $productIdentifierIdName = $_POST['productIdentifierIdName'];
    // echo uploadExcel($docUrl,$docName,$configPath,$uomName,$optionalName,$productIdentifierIdName,$_FILES);
    echo uploadExcel($_FILES);

}

if ($_GET['action'] == 'uploadExcelCategory')
{

    $docUrl = $_POST['DocURL'];
    $docName = $_POST['DocName'];
    $configPath = $_POST['configPath'];
    $uomName = $_POST['uomName'];
    $optionalName = $_POST['optionalName'];
    $productIdentifierIdName = $_POST['productIdentifierIdName'];
    // echo uploadExcel($docUrl,$docName,$configPath,$uomName,$optionalName,$productIdentifierIdName,$_FILES);
    echo uploadExcelCategory($_FILES);

}

/* end of new function */

/* ios get orderpage */
if ($_GET['action'] == 'iosOrderPage')
{
    $json_object = json_decode($_POST['data'], true);

    $data = $json_object["iosOrderPage_data"];

    $_SESSION['userId'] = $data["salespersonid"];
    $_SESSION["roleId"] = 2;
    // $_SESSION['date'] = '';
    // $_SESSION['month'] = date('n');
    // $_SESSION['year'] = date('Y');

    // if(!isset($_SESSION['date'])){
    //   $_SESSION['date'] = date('j');;
    // }

    // if(!isset($_SESSION['month'])){
    //   $_SESSION['month'] = date('n');
    // }

    // if(!isset($_SESSION['year'])){
    //   $_SESSION['year'] = date('Y');
    // }
    if(!isset($_SESSION['dateFrom'])){
      $_SESSION['dateFrom'] = date('Y-m-d');
    }

    if(!isset($_SESSION['dateTo'])){
      $_SESSION['dateTo'] = '';
    }

    if(!isset($_SESSION['customer-select'])){
      $_SESSION['customer-select'] = "";
    }

    // ob_start();
    // ob_clean();
    // flush();
    header('Location: ./ordersPage.php');
    // echo "<script type='text/javascript'>window.location.href = 'ordersPage.php';</script>";
    // exit();
    die('should have redirected by now');
}

if ($_POST['action'] == 'getCurrencyOrderAndSalespersonCustomerInOneTime')
{
    // echo 123;
    $json_object = json_decode($_POST['data'],true);

    $data = $json_object["getCurrencyOrderAndSalespersonCustomerInOneTime_data"];
    // echo $data['userId'];
    if(check_data_type_Iscorrect($data, "getCurrencyOrderAndSalespersonCustomerInOneTime"))
    {
        echo getCurrencyOrderAndSalespersonCustomerInOneTime($data);

    }
}

if ($_POST['action'] == 'searchOrders')
{
    $json_object = json_decode($_POST['data'],true);

    $data = $json_object["searchOrders_data"];

    $_SESSION['dateFrom'] = $data['dateFrom'];
    $_SESSION['dateTo']   = $data['dateTo'];
    $_SESSION['customer-select'] = $data['customer_status'];
	$_SESSION['order-select'] = $data['order_status'];

	$_SESSION['deliveryDateFrom'] = $data['deliveryDateFrom'];
	$_SESSION['deliveryDateTo'] = $data['deliveryDateTo'];

    // echo $data['month'];
    if(check_data_type_Iscorrect($data, "searchOrders"))
    {
        echo searchOrders($data);
    }
}

if ($_POST['action'] == 'searchConsignments')
{
    $json_object = json_decode($_POST['data'],true);

    $data = $json_object["searchConsignments_data"];

    $_SESSION['dateFrom'] = $data['dateFrom'];
    $_SESSION['dateTo']   = $data['dateTo'];
    $_SESSION['customer-select'] = $data['customer_status'];
	  $_SESSION['consignment-select'] = $data['consignment_status'];
	  $_SESSION['deliveryDateFrom'] = $data['deliveryDateFrom'];
	  $_SESSION['deliveryDateTo'] = $data['deliveryDateTo'];

    if(check_data_type_Iscorrect($data, "searchConsignments"))
    {
        echo searchConsignments($data);
    }
}



if ($_POST['action'] == 'getOrderStatusAndOrderDetailInOneTime')
{
    $json_object = json_decode($_POST['data'],true);

    $data = $json_object["getOrderStatusAndOrderDetailInOneTime_data"];

    if(check_data_type_Iscorrect($data, "getOrderStatusAndOrderDetailInOneTime"))
    {
        echo getOrderStatusAndOrderDetailInOneTime($data);
    }
}


if ($_POST['action'] == 'getConsignmentStatusAndConsignmentDetailInOneTime')
{
    $json_object = json_decode($_POST['data'],true);

    $data = $json_object["getConsignmentStatusAndConsignmentDetailInOneTime_data"];

    if(check_data_type_Iscorrect($data, "getConsignmentStatusAndConsignmentDetailInOneTime"))
    {
        echo getConsignmentStatusAndConsignmentDetailInOneTime($data);
    }
}


if ($_GET['action'] == 'approveChangePrice')
{
    // echo 123;
    // $json_object = json_decode($_POST['data'], true);
    //http://localhost:81/fashionbag/iManage/api.php?action=approveChangePrice&requestId=5&request_pw=0622abba&answer=1
    // $data = $json_object["iosOrderPage_data"];
    $data['requestId'] = $_GET['requestId'];
    $data['request_pw'] = $_GET['request_pw'];
    $data['answer'] = $_GET['answer'];

    if(isset($data['requestId']) && isset($data['request_pw']) && isset($data['answer']))
        echo approveChangePrice($data);

    // echo  $_SESSION['year'], "\n",  $_SESSION['month'], "\n", $_SESSION['date'];
    // header("Location: http://localhost:81/fashionbag/iManage/ordersPage.php");

}

if ($_GET['action'] == 'approveBestPrice')
{
    $data['requestId'] = $_POST['requestId'];
    $data['request_pw'] = $_POST['requestPw'];
    $data['price'] = $_POST['price'];
    $data['answer'] = 3;
    if(isset($data['requestId']) && isset($data['request_pw']) && isset($data['price']))
        echo approveChangePrice($data);
        // echo 123;
    // foreach($json_object as $key => $value) {
    //   echo "$key is at $value", "\n";
    // }
    // if(isset($data['requestId']) && isset($data['request_pw']) && isset($data['answer']))
    //     // echo approveChangePrice($data);
    //     echo 123;

    // echo  $_SESSION['year'], "\n",  $_SESSION['month'], "\n", $_SESSION['date'];
    // header("Location: http://localhost:81/fashionbag/iManage/ordersPage.php");

}

/* end of function */

/* send request to boss for lower price */

if ($_GET['action'] == 'requestBossPrice')
{
    // echo 123;
    // echo date('his');
    // $digits = 4;
    // echo rand(pow(10, $digits-1), pow(10, $digits)-1);
    // echo $_SERVER['REQUEST_URI'];
    // foreach($_SERVER as $key => $value) {
    //   echo "$key is at $value", "\n";
    // }
    // echo Math::to_base(date('his'), 62);
    $json_object = json_decode($_POST['data'], true);

    $data = $json_object["requestBossPrice_data"];

    if(check_data_type_Iscorrect($data, "requestBossPrice"))
    {
        echo requestBossPrice($data);
    }

}

/* end of function */

/* mobile setting request */

if ($_GET['action'] == 'requestMobileSetting')
{

    echo requestMobileSetting();

}

/* end of function */

/* check no stock notification */

if ($_GET['action'] == 'iosNoStockPage')
{
    $json_object = json_decode($_POST['data'], true);

    $data = $json_object["iosNoStockPage_data"];

    $_SESSION['userId'] = $data["salespersonid"];
    $_SESSION["roleId"] = 2;
    if(!isset($_SESSION['noStockDateFrom'])){
       $_SESSION['noStockDateFrom'] = date('Y-m-d');
    }

    if(!isset($_SESSION['noStockDateTo'])){
       $_SESSION['noStockDateTo'] = '';
    }

    header('Location: ./noStockPage.php');
    // echo "<script type='text/javascript'>window.location.href = 'ordersPage.php';</script>";
    // exit();
    die('should have redirected by now');
}

/* end of function */

/* get no stock detail */

if ($_POST['action'] == 'getNoStockPage')
{
    $json_object = json_decode($_POST['data'], true);

    $data = $json_object["getNoStockPage_data"];
    // echo 123;
    if(check_data_type_Iscorrect($data, "getNoStockPage"))
    {
        echo getAllNoStockDetails($data);
    }
}

/* end of function */

if ($_POST['action'] == 'getStockNotFoundPage')
{
    $json_object = json_decode($_POST['data'], true);

    $data = $json_object["getStockNotFoundPage_data"];
    // echo 123;
    if(check_data_type_Iscorrect($data, "getStockNotFoundPage"))
    {
        echo getStockNotFoundPage($data);
    }
}







/* set acknowledge status for no stock */

if ($_POST['action'] == 'setAcknowledgeStatus')
{
    $json_object = json_decode($_POST['data'], true);

    $data = $json_object["setAcknowledgeStatus_data"];
    // echo 123;
    if(check_data_type_Iscorrect($data, "setAcknowledgeStatus"))
    {
        echo setAcknowledgeStatus($data);
    }

}

/* end of function */


if ($_POST['action'] == 'setNotFoundAcknowledgeStatus')
{
    $json_object = json_decode($_POST['data'], true);

    $data = $json_object["setNotFoundAcknowledgeStatus_data"];
    // echo 123;
    if(check_data_type_Iscorrect($data, "setNotFoundAcknowledgeStatus"))
    {
        echo setNotFoundAcknowledgeStatus($data);
    }

}




/* search no stock details */

if ($_POST['action'] == 'searchNoStock')
{

    $json_object = json_decode($_POST['data'],true);

    $data = $json_object["searchNoStock_data"];

    // $_SESSION['year'] = $data['year'];
    // $_SESSION['noStockMonth'] = $data['month'];
    // $_SESSION['noStockDate'] = $data['day'];
    $_SESSION['noStockDateFrom'] = $data['dateFrom'];
    $_SESSION['noStockDateTo']   = $data['dateTo'];


    if(check_data_type_Iscorrect($data, "searchNoStock"))
    {
        echo searchNoStock($data);
    }
}

/* end of function */

if ($_POST['action'] == 'searchStockNotFound')
{
    $json_object = json_decode($_POST['data'],true);
    $data = $json_object["searchStockNotFound_data"];

    $_SESSION['noStockDateFrom'] = $data['dateFrom'];
    $_SESSION['noStockDateTo']   = $data['dateTo'];

    if(check_data_type_Iscorrect($data, "searchStockNotFound"))
    {
        echo searchStockNotFound($data);
    }
}

/* get no stock notification */

if ($_GET['action'] == 'getNoStockNotification')
{
    $json_object = json_decode($_POST['data'], true);

    $data = $json_object["getNoStockNotification_data"];
    // echo 123;
    if(check_data_type_Iscorrect($data, "getNoStockNotification"))
    {
        echo getNoStockNotification($data);
    }

}

/* end of function */

/* get salesperson customer list */

if ($_POST['action'] == 'getAllSalespersonCustomerlist')
{

    $json_object = json_decode($_POST['data'], true);

    $data = $json_object["getAllSalespersonCustomerlist_data"];
    // echo 123;
    if(check_data_type_Iscorrect($data, "getAllSalespersonCustomerlist"))
    {
        echo getAllSalespersonCustomerList($data);
    }

}

/* end of function */

/* get owed quantity list */

if ($_GET['action'] == 'getOwedQuantitylist')
{
    $json_object = json_decode($_POST['data'], true);

    $data = $json_object["getOwedQuantitylist_data"];

    if(check_data_type_Iscorrect($data, "getOwedQuantitylist"))
    {
        echo getOwedQuantitylist($data);
    }

}

/* end of function */

/* add category */

if ($_GET['action'] == 'addCategory')
{
    // echo 'abc';

    $json_object = json_decode($_POST['data'],true);
    // echo  $json_object;
    $data = $json_object["addCategory_data"];

    if(check_data_type_Iscorrect($data, "addCategory"))
    {
        echo addCategory($data);
    }
}

/* end of function */

/* edit category */

if ($_GET['action'] == 'editCategory')
{
    // echo 'abc';

    $json_object = json_decode($_POST['data'],true);
    // echo  $json_object;
    $data = $json_object["editCategory_data"];

    if(check_data_type_Iscorrect($data, "editCategory"))
    {
        echo editCategory($data);
    }
}

/* end of function */

/* add product */

if ($_GET['action'] == 'addProduct')
{
    $json_object = json_decode($_POST['data'],true);

    $data = $json_object["addProduct_data"];

    if(check_data_type_Iscorrect($data, "addProduct"))
    {
        echo addProduct($data);
    }
}

/* end of function */

/* edit product */

if ($_GET['action'] == 'updateSelectedProductInformation')
{
    $json_object = json_decode($_POST['data'], true);

    //print_r($json_object);

    $data = $json_object["updateSelectedProductInformation_data"];

    if(check_data_type_Iscorrect($data, "updateSelectedProductInformation"))
    {
        echo updateSelectedProductInformation($data);
    }
}

/* end of function */

function loaderAllAPI()
{
    foreach (glob("./API/*.php") as $filename)
    {
        require_once($filename);
    }
}

function check_data_type_Iscorrect($data, $data_type)
{
    if(empty($data))
    {
	$json_data = array(
			    'fail'=> '1'
			    ,'action'=>$data_type
			    ,"message"=>'data input type is incorrect'
		);

	echo json_encode($json_data);

	return false;
    }
    else
    {
        return true;
    }
}

//added zack 05 May 2018
if ($_GET['action'] == 'ConfirmTransferMultipleOrder')
{

    $json_object = json_decode($_POST['data'], true);

    $data = $json_object["ConfirmTransferMultipleOrder_data"];

    if(check_data_type_Iscorrect($data, "ConfirmTransferMultipleOrder"))
    {
        echo ConfirmTransferMultipleOrder($data);
    }
}

//END OF added zack 05 May 2018

function filter_json_injection($json)
{
    while (list($key, $value) = each($json))
    {
        if(gettype($value) == gettype(array()))
        {
            filter_json_injection($value);
			// 			echo "key ".$key." \r\n";
    	}
        else
        {
			// 			echo gettype($value)."\r\n";
                    //echo $value.mysql_real_escape_string($value)."*****";
            $json[$key] = mysql_real_escape_string($value);
	}
    }

    return $json;
}

/* Get products count  */

if ($_GET['action'] == 'getProductCurrentCount')
{
    $json_object = json_decode($_POST['data'], true);

    $data = $json_object["getProductCurrentCount_data"];

    if(check_data_type_Iscorrect($data, "getProductCurrentCount"))
    {
    	echo getProductCurrentCount($data);
    }
}
/* end of function */

/* Get products group  */

if ($_GET['action'] == 'getProductGroup')
{
    $json_object = json_decode($_POST['data'], true);

    $data = $json_object["date"];

    if(check_data_type_Iscorrect($data, "getProductGroup"))
    {
    	echo getProductGroup($data);
    }
}
/* end of function */

/* Get products group  */

if ($_GET['action'] == 'getProductGroupAdv')
{
    $json_object = json_decode($_POST['data'], true);

      $data = $json_object["date"];

    if(check_data_type_Iscorrect($data, "getProductGroupAdv"))
    {
    	echo getProductGroupAdv($data);
    }
}
/* end of function */
/* Savvy Added (START)*/
require_once 'savvy/checkin.php';
/* Savvy Added (END)*/

?>
