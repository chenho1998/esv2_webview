<?php
require_once('model/DB_class.php');

function updateOrderApprovalStatus($data)
{  
    $settings                   = parse_ini_file('../config.ini',true);

    $client = $data['client'];

	$db = new DB();

    $settings                   = $settings[$client];

    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
    $db_name                    = $settings['db'];
    $db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';
    $connection                 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);

    if(!$connection){
        return 'db error';
        die();
    }

    $json_data = array(
			'result'=> ""
			,'action'=>"updateOrderApprovalStatus"
			,'data'=>array()
			,"message"=> ""
			,"message_code"=> ""
    );
    
    $salesperson_id = $data['salesperson_id'];
    $order_id = $data['order_id'];
    $action = $data['action'];
    $comment = str_replace(array("\n","\r"),"",$data['comment']);
    $comment = trim($comment);
    $comment = mysql_real_escape_string($comment);

    $db->query("SELECT staff_code FROM cms_login WHERE login_id = '{$salesperson_id}'");
    $staff_code = '';
    if($db->get_num_rows() != 0)
    {
        while($result = $db->fetch_array())
        {
            $staff_code = $result['staff_code'];
        }
    }
    
    if($client == 'oasisqi' || $client == 'fuiwah'){
        if($action == 'approve'){
            $db->query("update cms_order set order_status = 2 where order_id = '{$order_id}'");
        }
        if($action == 'reject'){
            $db->query("update cms_order set order_status = 0, order_comment = '{$comment}' where order_id = '{$order_id}'");
        }
        $json_data['result'] = 1;
        return json_encode($json_data,JSON_UNESCAPED_UNICODE);
    }
    $customerInfo = getCustomerCodeAndDeviceToken($db,$order_id);
    $messageToCustomer = "";
    if($action == 'approve'){
        $sql = "UPDATE cms_order SET order_approved = 1, order_approver = '{$staff_code}',order_status = 1, delivery_date = CURRENT_DATE() WHERE order_id = '{$order_id}'";
        if($db->query($sql)){
            $json_data['result'] = 1;
            $messageToCustomer = "Your order ({$order_id}) has been approved by {$staff_code}";
        }else{
            $json_data['result'] = 0;
        }
    }
    if($action == 'reject'){
        $sql = "UPDATE cms_order SET order_approved = 2, order_approver = '{$staff_code}', order_comment = '{$comment}' WHERE order_id = '{$order_id}'";
        if($db->query($sql)){
            $json_data['result'] = 1;
            $messageToCustomer = "Your order ({$order_id}) has been rejected by {$staff_code}";
            if($comment){
                $messageToCustomer .= "\n{$staff_code} says: {$comment}";
            }
            $db->query("UPDATE cms_product p LEFT JOIN cms_order_item o ON o.product_code = p.product_code SET product_current_quantity = product_current_quantity + o.quantity WHERE o.order_id = '{$order_id}' AND o.cancel_status = 0;");
        }else{
            $json_data['result'] = 0;
        }
    }
    if($json_data['result'] == 1){
        sendMessage($customerInfo['device_token'],$messageToCustomer);
    }
    return json_encode($json_data,JSON_UNESCAPED_UNICODE);
}
function getCustomerCodeAndDeviceToken($db,$order_id){
    $db->query("SELECT cust_code FROM cms_order WHERE order_id = '{$order_id}'");
    $cust_code = '';
    if($db->get_num_rows() != 0)
    {
        while($result = $db->fetch_array())
        {
            $cust_code = $result['cust_code'];
        }
    }
    return getCustomerDeviceToken($cust_code);
}
function getCustomerDeviceToken($cust_code){
    $db = new DB();
    $db->connect_db_with_selforder();
    $db->query("SELECT device_token FROM api_login WHERE cust_code = '{$cust_code}'");
    $device_token = '';
    if($db->get_num_rows() != 0)
    {
        while($result = $db->fetch_array())
        {
            if($result['device_token']){
                $device_token = $result['device_token'];
            }
        }
    }
    return array(
        "cust_code"=>$cust_code,
        "device_token"=>array($device_token)
    );
}
?>
