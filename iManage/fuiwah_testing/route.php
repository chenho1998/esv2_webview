<?php
/* header('Content-Type: text/plain; charset="UTF-8"');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
header('Access-Control-Allow-Credentials: True');
error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);

date_default_timezone_set('Asia/Kuala_Lumpur');
ini_set('display_errors', TRUE);
error_reporting(E_ALL);
ini_set('display_errors', 0); */


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

if ($_POST['action'] == 'clearSession')
{
    $json_object = json_decode($_POST['data'],true);

    $data = $json_object["clearSession_data"];

    if(check_data_type_Iscorrect($data, "clearSession"))
    {
        echo clearSession($data);
    }
}

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


?>
