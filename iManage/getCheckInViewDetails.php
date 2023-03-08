<?php
header('Content-Type: text/plain; charset="UTF-8"');
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once('./model/DB_class.php');


function getCheckInViewDetails($data)
{
	$mapApiLink = 'https://api.mapbox.com/geocoding/v5/mapbox.places/@long,@lat.json?access_token=pk.eyJ1IjoiZWFzeXRlY2giLCJhIjoiY2p4NGo1dHB2MDV1czN6bXhvOTBlOXQ5OCJ9.eAdkDp1HrwDWdVrfVpgu-Q';
	$view='';
	$grand_total='Not Available';
	$config = parse_ini_file('../config.ini',true);

	$client = $data['client'];

	$settings                   = $config[$client];

    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
	$db_name                    = $settings['db'];
	$db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

	$db = new DB();
	$con_1 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);
	
	$sql = "SELECT r.*, c.`cust_company_name` 
		FROM `cms_visit_report` r 
		LEFT JOIN `cms_customer` c 
		ON c.`cust_id` = r.`customer_id` WHERE r.`id` ='" . mysql_real_escape_string($data['checkInId']) . "'";
		
	$db->query($sql);
	
	$db_data = clone $db;
	
	if($db_data->get_num_rows() != 0) {
		while($result = $db_data->fetch_array())
		{
			$place_name = '';
			if(!isset($result['checkout_location'])){
				$mapApiLink = str_replace('@long',$result['checkout_lng'],$mapApiLink);
				$mapApiLink = str_replace('@lat',$result['checkout_lat'],$mapApiLink);

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_URL, $mapApiLink);
				curl_setopt($ch, CURLOPT_TIMEOUT, 90);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

				$response     = curl_exec($ch);
				$parentResult = json_decode($response, true);
				$parentResult = $parentResult['features'];
				$place_name = $parentResult[0]['place_name'];
			}else{
				$place_name = $result['checkout_location'];
			}

			if($result['location_lng'] && $result['location_lat'] && $result['checkout_lng'] && $result['checkout_lat']){
				$mapDisplay = '<div style="background-color:#f6f6f6;width:99%;height:50%;margin-left:0.5%;text-align:center;margin-top:2%;">
				<div id="map" style="width:100%;height:350px;"></div></div>';
			}else{
				$mapDisplay = '';
			}
			
			$view .='
					<div class="divider"></div>
						<div class="limit-text-length text_grey company-title" style="max-width:100%;height:25px;">
							<center style="height:25px;margin-left:5px;margin-right:5px;"> '.$result["cust_company_name"].' </center>
						</div>
						<div class="divider"></div>
					<table class="data">';
					
			$view .='
				<tr>
					<td>
						<p><span style="font-weight:bold;">Person You Meet: </span><span style="color:#147efb;font-weight:bold">'.$result['person_met'].'</span></p>
					';
			if($result['remark1']){
				$view .='
					<p><span>- '.$result['remark1'].'</span></p>
				';
			}
			if($result['remark2']){
				$view .='
					<p><span>- '.$result['remark2'].'</span></p>
				';
			}
			if($result['remark3']){
				$json_remark = json_decode($result['remark3'],true) ? json_decode($result['remark3'],true) : '';
				$str = $json_remark['str'] ? $json_remark['str'] : $result['remark3'];
				$view .='
					<p><span>- '.$str.'</span></p>
				';
			}
			
			$view .='
						<p><span style="font-weight:bold;">Location: </span><span>'.$place_name . '</span></p>
						<p><span style="font-weight:bold;">Check In: </span><span>'.date('d M Y h:i:sa',strtotime($result['checkin_time'])).'</span></p>
						<p><span style="font-weight:bold;">Check Out: </span><span>'.date('d M Y h:i:sa',strtotime($result['checkout_time'])).'</span></p>
					</td>
				</tr>
				';
			$view.='
			</table>';
			
			// Get attached Images
			$sql = "SELECT up.*,REPLACE(upload_image,'http:','https:') AS image_link FROM `cms_salesperson_uploads` AS up WHERE `upload_status` = '1' AND `upload_bind_id` = '" . mysql_real_escape_string($result['mobile_checkin_id']) . "' ORDER BY `upload_id`";
			$db->query($sql);
			
			$uploaded_image = array();
			while($result2 = $db->fetch_array()) {
				if(!isset($uploaded_image[$result2["upload_type_name"]]))
					$uploaded_image[$result2["upload_type_name"]] = array();
				$uploaded_image[$result2["upload_type_name"]][] = $result2;
			}
			
			$view .='
					<div class="divider"></div>
						<div class="limit-text-length text_white company-title" style="max-width:100%;height:25px;">
							<center style="height:25px;margin-left:5px;margin-right:5px;"> Attachment (' . sizeof($uploaded_image) . ')</center>
						</div>
						<div class="divider"></div>
					<table class="data">';
			

			if(sizeof($uploaded_image) > 0) {
				$view .='<div class="divider"></div>
				<table class="data">';
				
				foreach($uploaded_image as $type => $items) {
					$view .= '
					<tr>
						<td style="border: 0px; padding: 10px 2px;">
							<p><span style="font-weight:bold;">'.$type.'</span></p><div class="divider"></div>
							<div class="popup-gallery">';
					foreach($items as $row) {
						$view .= '<a class="attachment" href="' . str_replace('"', '\"', $row["image_link"]) . '" title="' . str_replace('"', '\"', $row["upload_remark"]) . '">
							<img style="object-fit: contain;" src="' . str_replace('"', '\"', $row["image_link"]) . '" />
						</a>';
					}
					$view .= '
							</div>
						</td>
					</tr>';
				}
				
				$view .='
				</table>
				<div class="divider"></div>';

			}
			
			$view .= '<input id="start_longitude" style="display:none" value="'.$result['location_lng'].'"></input>
			<input id="start_latitude" style="display:none" value="'.$result['location_lat'].'"></input>
			<input id="end_longitude" style="display:none" value="'.$result['checkout_lng'].'"></input>
			<input id="end_latitude" style="display:none" value="'.$result['checkout_lat'].'"></input>';
			
			$view .= $mapDisplay;
		}
	} else {
		$view .='
					<div class="divider"></div>
						<div class="limit-text-length text_white company-title" style="max-width:100%;height:25px;">
							<center style="height:25px;margin-left:5px;margin-right:5px;"> No record found. </center>
						</div>
						<div class="divider"></div>
					';
	}
	
	$json_data['views'] = $view;
	return $json_data;
    
}

?>
