<?php
header('Content-Type: text/plain; charset="UTF-8"');
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once('model/DB_class.php');

function searchCheckIns($data)
{
	$client = "easysale_".$data['client'];
	$db = new DB();
	$db->connect_db_with_db_config($client);
	$db2 = new DB();
	$db2->connect_db_with_db_config($client);
	
	
	// Search condition 
	$where = "";
	
	if(isset($data['salespersonId']) && $data['salespersonId'] != '') {
		$where .= ($where != "" ? " AND " : " WHERE ") . " salesperson_id = '" . mysql_real_escape_string($data['salespersonId']) . "' ";
	}
	
	if(isset($data['dateFrom']) && $data['dateFrom'] != '') {
		$dateTime = date('Y-m-d' , strtotime($data['dateFrom'])) . " 00:00:00";
		$where .= ($where != "" ? " AND " : " WHERE ") . " checkin_time >= '" . mysql_real_escape_string($dateTime) . "' ";
	}
	
	if(isset($data['dateTo']) && $data['dateTo'] != '') {
		$dateTime = date('Y-m-d' , strtotime($data['dateTo'])) . " 23:59:59";
		$where .= ($where != "" ? " AND " : " WHERE ") . " checkin_time <= '" . mysql_real_escape_string($dateTime) . "' ";
	}
	
	if(isset($data['check_in_status']) && $data['check_in_status'] != '') {
		$where .= ($where != "" ? " AND " : " WHERE ") . " status = '" . mysql_real_escape_string($data['check_in_status']) . "'";
	}
	
	if(isset($data['customer_status']) && $data['customer_status'] != '') {
		$where .= ($where != "" ? " AND " : " WHERE ") . " customer_id = '" . mysql_real_escape_string($data['customer_status']) . "'";
	}
	
	$sql = "
		SELECT 
			* 
		FROM `savvy_visit_report`
	" . $where . " ORDER BY `checkin_time` DESC";
	
	$db->query($sql);

	//`savvy_visit_report_remark`

	$checkInList = array();
	$checkin_info = array();
	$ids = array();

	if($db->get_num_rows() != 0)
	{
		while($result = $db->fetch_array())
		{
			$ids[] = $result["id"];
			
			$date = date_create($result["checkin_time"]);
			$result["checkin_time"] = date_format($date,"d F g:iA");
			
			$date = date_create($result["checkin_time_last_update"]);
			$result["checkin_time_last_update"] = date_format($date,"d F g:iA");
			
			$date = date_create($result["create_date"]);
			$result["create_date"] = date_format($date,"d F g:iA");
			
			$date = date_create($result["update_date"]);
			$result["update_date"] = date_format($date,"d F g:iA");
			
			$checkInList[] = $result;
		}
		
		// Get the remarks
		$sql = "SELECT * FROM `savvy_visit_report_remark` WHERE `visit_id` IN (" . implode(", ", $ids) . ")";
		$db2->query($sql);
		
		if($db2->get_num_rows() != 0) {
			while($result = $db2->fetch_array()) {
				if(!isset($checkin_info[$result["visit_id"]])) {
					$checkin_info[$result["visit_id"]] = array(); 
				}
				$date = date_create($result["create_date"]);
				$result["create_date"] = date_format($date,"d F g:iA");
				
				$date = date_create($result["update_date"]);
				$result["update_date"] = date_format($date,"d F g:iA");
				
				$checkin_info[$result["visit_id"]] = $result;
			}
		}

		$json_data['data'] = $checkInList;
		$json_data['checkin_info'] = $checkin_info;
	}
	else
	{
		$json_data = array(
                   "success"=>'1'
                  ,'message'=>"No check in found"
		);
	}

	return json_encode($json_data);
}

function getCheckInStatusAndCheckInDetailInOneTime_data($data)
{
	$client = "easysale_".$data['client'];
	$db = new DB();
	$db->connect_db_with_db_config($client);

	$db2 = new DB();
	$db2->connect_db_with_db_config($client);

	$query = "SELECT * FROM `savvy_visit_report` WHERE `id` ='" . mysql_real_escape_string($data['checkInId']) . "'";
	$db->query($query);

	$checkInList = array();
	$checkin_info = array();
	$ids = array();

	if($db->get_num_rows() != 0)
	{
		while($result = $db->fetch_array())
		{
			$ids[] = $result["id"];
			$date = date_create($result["checkin_time"]);
			$result["checkin_time"] = date_format($date,"d F g:iA");
			
			$date = date_create($result["checkin_time_last_update"]);
			$result["checkin_time_last_update"] = date_format($date,"d F g:iA");
			
			$date = date_create($result["create_date"]);
			$result["create_date"] = date_format($date,"d F g:iA");
			
			$date = date_create($result["update_date"]);
			$result["update_date"] = date_format($date,"d F g:iA");
			
			$result["customer_name"] = $result["customer_id"];
			
			// Customer Company Name
			$sql = "SELECT * FROM `cms_customer` WHERE `cust_id` = '" . mysql_real_escape_string($result["customer_id"]) . "'";
			$db2->query($sql);
			
			if($db2->get_num_rows() != 0) {
				while($result2 = $db2->fetch_array()) {
					$result["customer_name"] = $result2["cust_company_name"];
				}
			}
			
			$checkInList[] = $result;
		}
		
		// Get the remarks
		$sql = "SELECT * FROM `savvy_visit_report_remark` WHERE `visit_id` IN (" . implode(", ", $ids) . ")";
		$db2->query($sql);
		
		if($db2->get_num_rows() != 0) {
			while($result = $db2->fetch_array()) {
				if(!isset($checkin_info[$result["visit_id"]])) {
					$checkin_info[$result["visit_id"]] = array(); 
				}
				$date = date_create($result["create_date"]);
				$result["create_date"] = date_format($date,"d F g:iA");
				
				$date = date_create($result["update_date"]);
				$result["update_date"] = date_format($date,"d F g:iA");
				
				$checkin_info[$result["visit_id"]][] = $result;
			}
		}
		
		$json_data['data'] = $checkInList;
		$json_data['checkin_info'] = $checkin_info;
	}
	else
	{
		$json_data = array(
				   "success"=>'0'
				  ,'message'=>"No check in found"
		);
	}

	return json_encode($json_data);
}
?>
