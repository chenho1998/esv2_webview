<?php
header('Content-Type: text/plain; charset="UTF-8"');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
header('Access-Control-Allow-Credentials: True');
error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
// session_start();
date_default_timezone_set('Asia/Kuala_Lumpur');
ini_set('display_errors', TRUE);
error_reporting(E_ALL);
ini_set('display_errors', 0);


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
if ($_POST['action'] == 'updateOrderApprovalStatus')
{

    $json_object = json_decode($_POST['data'], true);

    $data = $json_object;

    require_once('updateOrderApprovalStatus.php');

    if(check_data_type_Iscorrect($data, "updateOrderApprovalStatus"))
    {   
	   echo updateOrderApprovalStatus($data);
    }
}
if ($_POST['action'] == 'loadCustomers_v2')
{

    $json_object = json_decode($_POST['data'], true);

    $data = $json_object;

    require_once('getCustomerList_v2.php');

    if(check_data_type_Iscorrect($data, "getCustomerList_v2"))
    {   
	   echo getCustomerList_v2($data);
    }
}
if ($_POST['action'] == 'getPaymentWithView')
{
    $json_object = json_decode($_POST['data'],true);

    $data = $json_object["searchPayments_data"];

    require_once('getPaymentWithView.php');

    if(check_data_type_Iscorrect($data, "getPaymentWithView"))
    {
        echo getPaymentWithView($data);
    }
}
//setAcknowledgeStatus
if ($_POST['action'] == 'setAcknowledgeStatus')
{
    $json_object = json_decode($_POST['data'],true);

    $data = $json_object["setAcknowledgeStatus_data"];

    require_once('setAcknowledgeStatus.php');

    if(check_data_type_Iscorrect($data, "getNoStockWithView"))
    {
        echo setAcknowledgeStatus($data);
    }
}

if ($_POST['action'] == 'getDeliveryStatusView')
{
    $data = json_decode($_POST['getDeliveryStatusView_data'],true);

    require_once('getDeliveryStatusView.php');

    if(check_data_type_Iscorrect($data, "getDeliveryStatusView"))
    {
        echo getDeliveryStatusView($data);
    }
}

if ($_POST['action'] == 'getNoStockWithView')
{
    $json_object = json_decode($_POST['data'],true);

    $data = $json_object["searchStock_data"];

    require_once('getNoStockWithView.php');

    if(check_data_type_Iscorrect($data, "getNoStockWithView"))
    {
        echo getNoStockWithView($data);
    }
}

if($_POST['action'] == 'updateStkTrItemAck'){
    $json_object = json_decode($_POST['data'],true);

    $data = $json_object["updateStkTrItemAck_data"];

    require_once('updateStkTrItemAck.php');

    if(check_data_type_Iscorrect($data, "updateStkTrItemAck"))
    {
        echo updateStkTrItemAck($data);
    }
}

if ($_POST['action'] == 'getOrderWithView')
{
    $json_object = json_decode($_POST['data'],true);

    $data = $json_object["searchOrders_data"];

    require_once('getOrderWithView.php');

    if(check_data_type_Iscorrect($data, "getOrderWithView"))
    {
        echo getOrderWithView($data);
    }
}

if ($_POST['action'] == 'getCashRollingView')
{
    $json_object = json_decode($_POST['data'],true);

    $data = $json_object["searchRollingCash_data"];

    require_once('getCashRollingView.php');

    if(check_data_type_Iscorrect($data, "getCashRollingView"))
    {
        echo getCashRollingView($data);
    }
}

if ($_POST['action'] == 'getStockWithView')
{
    $json_object = json_decode($_POST['data'],true);

    $data = $json_object["searchStock_data"];

    require_once('getStockWithView.php');

    if(check_data_type_Iscorrect($data, "getStockWithView"))
    {
        echo getStockWithView($data);
    }
}

if ($_POST['action'] == 'getStockTransferWithView')
{
    $json_object = json_decode($_POST['data'],true);

    $data = $json_object["searchStockTransfer_data"];

    require_once('getStockTransferWithView.php');

    if(check_data_type_Iscorrect($data, "getStockTransferWithView"))
    {
        echo getStockTransferWithView($data);
    }
}

if ($_POST['action'] == 'getConsignmentWithView')
{
    $json_object = json_decode($_POST['data'],true);

    $data = $json_object["searchConsignments_data"];

    require_once('./getConsignmentWithView.php');

    if(check_data_type_Iscorrect($data, "getConsignmentWithView"))
    {   
        echo getConsignmentWithView($data);
    }
}
if ($_POST['action'] == 'clearSession')
{
    $json_object = json_decode($_POST['data'],true);

    $data = $json_object["clearSession_data"];

    if(check_data_type_Iscorrect($data, "clearSession"))
    {
        echo clearSession($data);
    }
}
if ($_POST['action'] == 'updateCuttingDate')
{
    $json_object = json_decode($_POST['data'], true);

    $data = $json_object;

    if(check_data_type_Iscorrect($data, "updateCuttingDate"))
    {
        require_once('updateCuttingDate.php');
        echo updateCuttingDate($data);
    }
}
/* Savvy Added (START)*/
if ($_POST['action'] == 'getCheckInWithView')
{
    $json_object = json_decode($_POST['data'],true);

    $data = $json_object["searchCheckIns_data"];

    require_once('getCheckInWithView.php');

    if(check_data_type_Iscorrect($data, "getCheckInWithView"))
    {
        echo getCheckInWithView($data);
    }
}
/* Savvy Added (END)*/

if ($_POST['action'] == 'loadCustomers')
{

    $json_object = json_decode($_POST['data'], true);

    $data = $json_object;

    require_once('getCustomerList.php');

    if(check_data_type_Iscorrect($data, "getCustomerList"))
    {   
	   echo getCustomerList($data);
    }
}

if ($_POST['action'] == 'updateOrderApproval')
{

    $json_object = json_decode($_POST['data'], true);

    $data = $json_object;

    require_once('updateOrderApproval.php');

    if(check_data_type_Iscorrect($data, "updateOrderApproval"))
    {   
	   echo updateOrderApproval($data);
    }
}

if ($_POST['action'] == 'checkedPayment')
{
    $json_object = json_decode($_POST['data'],true);

    $data = $json_object["payment_data"];

    require_once('getPaymentWithView.php');

    if(check_data_type_Iscorrect($data, "checkedPayment"))
    {
        echo checkedPayment($data);
    }
}

if ($_POST['action'] == 'uncheckedPayment')
{
    $json_object = json_decode($_POST['data'],true);

    $data = $json_object["payment_data"];

    require_once('getPaymentWithView.php');

    if(check_data_type_Iscorrect($data, "uncheckedPayment"))
    {
        echo uncheckedPayment($data);
    }
}

if ($_POST['action'] == 'openModal')
{
    $json_object = json_decode($_POST['data'],true);

    $data = $json_object["openModal_data"];

    require_once('openModal.php');

    if(check_data_type_Iscorrect($data, "openModal"))
    {
        echo openModal($data);
    }
}

if ($_POST['action'] == 'updateOrderItem')
{
    $json_object = json_decode($_POST['data'],true);

    $data = $json_object["updateOrderItem_data"];

    require_once('updateOrderItem.php');

    if(check_data_type_Iscorrect($data, "updateOrderItem"))
    {
        echo updateOrderItem($data);
    }
}
if ($_POST['action'] == 'searchProduct')
{

    $json_object = json_decode($_POST['data'], true);

    $data = $json_object["searchProduct_data"];

    require_once('searchProduct.php');

    if(check_data_type_Iscorrect($data, "searchProduct"))
    {   
	   echo searchProduct($data);
    }
}

if ($_POST['action'] == 'addProduct')
{

    $json_object = json_decode($_POST['data'], true);

    $data = $json_object["addProduct_data"];

    require_once('searchProduct.php');

    if(check_data_type_Iscorrect($data, "addProduct"))
    {   
	   echo addProduct($data);
    }
}

if ($_POST['action'] == 'getJobWithView')
{
    $json_object = json_decode($_POST['data'],true);

    $data = $json_object["searchJobs_data"];

    require_once('../../warehouse/getJobWithView.php');

    if(check_data_type_Iscorrect($data, "getJobWithView"))
    {
        echo getJobWithView($data);
    }
}

if ($_POST['action'] == 'updateJobStatus')
{
    $json_object = json_decode($_POST['data'], true);

    $data = $json_object;

    if(check_data_type_Iscorrect($data, "updateJobStatus"))
    {
        require_once('../../warehouse/updateJobStatus.php');
        echo updateJobStatus($data);
    }
}

if ($_POST['action'] == 'deleteJobImg')
{
    $json_object = json_decode($_POST['data'],true);

    $data = $json_object["deleteJobImg_data"];

    require_once('deleteJobImg.php');

    if(check_data_type_Iscorrect($data, "deleteJobImg"))
    {
        echo deleteJobImg($data);
    }
}

if ($_POST['action'] == 'openWHModal')
{
    $json_object = json_decode($_POST['data'],true);

    $data = $json_object["openWHModal_data"];

    require_once('openModal.php');

    if(check_data_type_Iscorrect($data, "openWHModal"))
    {
        echo openWHModal($data);
    }
}

if ($_POST['action'] == 'updateWarehouse')
{
    $json_object = json_decode($_POST['data'],true);

    $data = $json_object["updateWarehouse_data"];

    require_once('updateWarehouse.php');

    if(check_data_type_Iscorrect($data, "updateWarehouse"))
    {
        echo updateWarehouse($data);
    }
}

if ($_POST['action'] == 'updateOrderDate')
{
    $json_object = json_decode($_POST['data'],true);

    $data = $json_object["updateOrderDate_data"];

    require_once('updateOrderDate.php');

    if(check_data_type_Iscorrect($data, "updateOrderDate"))
    {
        echo updateOrderDate($data);
    }
}

if ($_POST['action'] == 'updateChopStatus')
{
    $json_object = json_decode($_POST['data'],true);

    $data = $json_object["updateChopStatus_data"];

    require_once('API/updateChopStatus.php');

    if(check_data_type_Iscorrect($data, "updateChopStatus"))
    {
        echo updateChopStatus($data);
    }
}

if ($_POST['action'] == 'updateGrn')
{
    $json_object = json_decode($_POST['data'],true);

    $data = $json_object["updateGrn_data"];

    require_once('API/updateGrn.php');

    if(check_data_type_Iscorrect($data, "updateGrn"))
    {
        echo updateGrn($data);
    }
}

if ($_POST['action'] == 'updatePONo')
{
    $json_object = json_decode($_POST['data'],true);

    $data = $json_object["updatePONo_data"];

    require_once('API/updatePONo.php');

    if(check_data_type_Iscorrect($data, "updatePONo"))
    {
        echo updatePONo($data);
    }
}
?>
