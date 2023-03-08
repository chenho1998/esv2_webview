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

function getCheckInWithView($data){

	$_SESSION["_cdate_from"] 	= checkNull($data['dateFrom']);
    $_SESSION["_cdate_to"] 		= checkNull($data['dateTo']);
	$_SESSION["_cust_id"] 		= checkNull($data['customer_status']);
	

	$config = parse_ini_file('../config.ini',true);
	
	$client = $data['client'];

	$settings                   = $config[$client];

    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
	$db_name                    = $settings['db'];
	$db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

	$db = new DB();
	$con_1 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);


	// Search condition 
	$where = "";
	
	if(isset($data['salespersonId']) && $data['salespersonId'] != '0') {
		$where .= ($where != "" ? " AND " : " WHERE ") . " salesperson_id IN (" . mysql_real_escape_string($data['salespersonId']) . ")";
	}
	
	if(isset($data['dateFrom']) && $data['dateFrom'] != '') {
		$dateTime = date('Y-m-d' , strtotime($data['dateFrom'])) . " 00:00:00";
		$where .= ($where != "" ? " AND " : " WHERE ") . " checkin_time >= '" . mysql_real_escape_string($dateTime) . "' ";
	}
	
	if(isset($data['dateTo']) && $data['dateTo'] != '') {
		$dateTime = date('Y-m-d' , strtotime($data['dateTo'])) . " 23:59:59";
		$where .= ($where != "" ? " AND " : " WHERE ") . " checkin_time <= '" . mysql_real_escape_string($dateTime) . "' ";
	}
	
	// if(isset($data['check_in_status']) && $data['check_in_status'] != '') {
	// 	$where .= ($where != "" ? " AND " : " WHERE ") . " status = '" . mysql_real_escape_string($data['check_in_status']) . "'";
	// }
	$where .= ($where != "" ? " AND " : " WHERE ") . " status <> '0'";
	
	if(isset($data['customer_status']) && $data['customer_status'] != '') {
		$where .= ($where != "" ? " AND " : " WHERE ") . " cust_code = '" . mysql_real_escape_string($data['customer_status']) . "'";
	}
	
	$sql = "SELECT r.*, c.`cust_company_name`, l.`name` FROM `cms_visit_report` r LEFT JOIN `cms_customer` c ON c.`cust_id` = r.`customer_id` LEFT JOIN `cms_login` l ON l.`login_id` = r.`salesperson_id` " . $where . " ORDER BY r.`checkin_time` DESC";
	// file_put_contents("checkin.log",$sql);
	$db->query($sql);
	
	if($db->get_num_rows() != 0) {
		$view = '<p style="text-align:center; line-height: 25px;" class="dropdown-div text_white"> Check Ins </p>';
		
		while($result = $db->fetch_array())
		{
			$remarkView = $result["remark1"] ? '<p class="dates">'.$result["remark1"].' </p>' : '';

			$locationView = $result["checkin_location"] == $result["checkout_location"] ? 
			'<p class="dates" style="color:grey"> Location: '.$result["checkin_location"].' </p>
			<p class="dates" style="color:grey"> Check In: '.displaydate($result["checkin_time"]).' </p>
			<p class="dates" style="color:grey"> Check Out: '.displaydate($result["checkout_time"]).' </p>' : 
			'<p class="dates" style="color:grey"> Check In: '.$result["checkin_location"].' At '.displaydate($result["checkin_time"]).' </p>
			<p class="dates" style="color:grey"> Check Out: '.$result["checkout_location"].' At '.displaydate($result["checkout_time"]).' </p>';

			$salesperson_name =  $data['salespersonId'] == '0' || strpos($data['salespersonId'], ',') !== false ? '<p class="dates">Agent : '.$result["name"].' </p>' : '';
			$view .= '
				<div style="cursor: pointer;">
					<div onclick="window.location=\'newviewCheckInPage.php?checkInId='.$result["id"].'&client='.$client.'&userId='.$data['salespersonId'].'\'">
						<p class="title" > '.$result["cust_company_name"].' </p>
						<p class="dates" style="text-decoration-line:underline"> Meet: '.$result["person_met"].' </p>
						'.$salesperson_name.'
						'.$remarkView.'
						'.$locationView.'
					</div>
					<div class="divider"></div>
					<hr>
				</div>
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

function displaydate($datetime){
	return date('d M y h:i A',strtotime($datetime));
    /* $datetime = strval($datetime);
    $month_names = array(
       'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec'
    );
    
    $splitted = explode(' ',$datetime);
    
    $date = $splitted[0];
    $time = $splitted[1];
    
    $splitted = explode('-',$date);
    $year = $splitted[0];
    $month = intval($splitted[1])-1;
    $day = $splitted[2];
    
    $month = $month_names[$month];
  
    if($time != '00:00:00' && !empty($time)){
        $timeSplit = explode(':',$time);

        $hr          = intval($timeSplit[0]);
        $min         = intval($timeSplit[1]);
        $zz          = 'AM';

        if($min < 10){
            $min         = "0".$min;
        }

        if ($hr > 12) {
            $hr -= 12;
            $zz = 'PM';
        }
        $time = '';
        $time = ' at '. $hr . ':' . $min . $zz;
        }else{
           $time = '';
        }
    
    $display = $day." ".$month." ".$year.$time;
    
    return $display; */
}
?>
