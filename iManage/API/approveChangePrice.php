<?php
header('Content-Type: text/plain; charset="UTF-8"');
require_once('model/DB_class.php');
require_once('./config/config.php');
// require_once ("class.phpmailer.php");
// require_once('PHPMailer-master/PHPMailerAutoload.php');
date_default_timezone_set('Asia/Kuala_Lumpur');

function approveChangePrice($data){
	// return $data['answer'];
	$db = new DB();
    $db->connect_db_with_db_config();

    $db2 = new DB();
    $db2->connect_db_with_db_config();

	if($data['answer'] == 1){
	    $db->query("UPDATE cms_request_bosspw SET  status = 1 WHERE request_id = '" . $data['requestId'] . "' AND request_pw = '" . $data['request_pw'] . "' AND status = 0"
    	);
	}
	else if($data['answer'] == 2){
		$db->query("UPDATE cms_request_bosspw SET  status = 2 WHERE request_id = '" . $data['requestId'] . "' AND request_pw = '" . $data['request_pw'] . "' AND status = 0"
    	);
	}
	else if($data['answer'] == 3){
		if (is_numeric( $data['price'])) {
			$db->query("UPDATE cms_request_bosspw SET  status = 3, boss_price = '". $data['price'] . "' WHERE request_id = '" . $data['requestId'] . "' AND request_pw = '" . $data['request_pw'] . "' AND status = 0"
	    	);
		}
		else{
			return 'The price entered is not a number';
		}
	}

	$db->query("SELECT * FROM cms_request_bosspw WHERE request_id = '" . $data['requestId'] . "' AND request_pw = '" . $data['request_pw'] . "' AND status <> 0"
    	);

	if($db->get_num_rows() != 0)
    {
    	while($result = $db->fetch_array())
        {

            $custcode= $db2->query("SELECT cust_code FROM cms_customer WHERE cust_id = '" . $result['customer_id'] ."'"
            );
            $row = mysql_fetch_row($custcode);
            $custcode = $row[0];

        	if($result['status'] == 1){
                $message = "Boss approved your asking price RM" . $result['asking_price'] . " for product = " . $result['product_code'] . " and customer = " . $custcode . ".";
                sendPushNotification($message,$result['device_token']);
                // $return["allresponses"] = $response;
                // $return = json_encode( $return);
                // return $return;
        		return 'approved';
        	}
        	else if ($result['status'] == 2){
                $message = "Boss rejected your asking price RM" . $result['asking_price'] . " for product = " . $result['product_code'] . " and customer = " . $custcode . ".";
                sendPushNotification($message,$result['device_token']);
        		return 'rejected';
        	}
        	else if ($result['status'] == 3){
                $message = "Boss decide the best price is " . $result['boss_price'] . " for product = " . $result['product_code'] . " and customer = " . $custcode . ".";
                sendPushNotification($message,$result['device_token']);
        		return 'approved with a best price RM'.$result['boss_price'] ;
        	}
            // $customerDetails = array(
            //             'salesperson_customer_id'=>$result['salesperson_customer_id']
            //             ,'customer_id'=>$result['customer_id']
            // );
        
            // $customerArr[] = $customerDetails;
        }
    }
}

function sendPushNotification($message,$user){

    // global $_config;
    $config = parse_ini_file(dirname(__FILE__).'/../../config.ini');
    $onesignal_id = $config['onesignal_id'];;
    $onesignal_key = $config['onesignal_key'];
    $content = array(
            "en" => 'English Message'
            );
 
    $fields = array(
        'app_id' => $onesignal_id,
        'include_player_ids' => array($user),
        "contents" => array("en" => $message),
        "headings" => array("en" => "Boss reply to your request"),
        'data' => array("foo" => "bar")
    );
    
    $fields = json_encode($fields);
    // print("\nJSON sent:\n");
    // print($fields);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
                                               'Authorization: Basic OWQ2OTY1YjYtNWMwYi00YTRlLTllNGYtNjcyMzY5ZDE0NDc4'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}
    
?>