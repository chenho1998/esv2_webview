<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once('./model/DB_class.php');

// cust_code, cust_company, payment_id, payment_date, DTL[0](payment_cheque_no, payment_received_in), payment_desc, payment_remark, payment_amount

function getPaymentDetails($data)
{
	$view='';
	$dataForApp = array();
	
	$config = parse_ini_file('../config.ini',true);

	$client = $data['client'];

	$settings                   = $config[$client];

    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
	$db_name                    = $settings['db'];
	$db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

	$db = new DB();
	$db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);
	
	$sql = "SELECT p.*, c.`cust_company_name`
		FROM `cms_payment` p 
		LEFT JOIN `cms_customer` c ON c.`cust_code` = p.`cust_code` 
		WHERE p.`payment_id` ='" . mysql_real_escape_string($data['paymentId']) . "'";

	$db->query($sql);
	
	$db_data = clone $db;
	
	if($db_data->get_num_rows() != 0) {
		while($result = $db_data->fetch_array())
		{

			$dataForApp['cust_code'] = $result['cust_code'];
			$dataForApp['cust_company_name'] = $result['cust_company_name'];
			$dataForApp['payment_id'] = $result['payment_id'];
			$dataForApp['payment_date'] = $result['payment_date'];
			$dataForApp['payment_desc'] = $result['description'];
			$dataForApp['payment_remark'] = '';//$result['salesperson_payment_remark'];
			$dataForApp['payment_amount'] = $result['payment_amount'];
			
			if($result['knockoff_inv'] = json_decode($result['knockoff_inv'])){

				$dataForApp['payment_doc'] = array();

				$table = '<div class="limit-text-length company-title" style="max-width:100%;height:25px;">
							<center style="height:25px;margin-left:5px;margin-right:5px;"> Knocked Off Invoice </center>
						</div>
						<table style="width:100%;border:0px solid red;">
							<tr style="text-align:center;font-weight:bold;background-color:#f1f1f1;">
								<th style="width:5%;">No.</th>
								<th style="width:45%;">Trans.</th>
								<th style="width:25%;">Amt.(RM)</th>
								<th style="width:25%;text-align:right;">Bal.(RM)</th>
							</tr>';
				$i = 1;
				foreach($result['knockoff_inv'] as $a){
					$doc_code = $a->{'doc_code'};
					$doc_date = $a->{'doc_date'};
					$doc_amount = $a->{'doc_amount'};
					$doc_outstanding = $a->{'doc_outstanding'};
					$knockoff_amount = $a->{'knockoff_amount'};
					$knockoff_amount == $doc_amount ? $applied_color = "green" : "orange";
					$a->{'doc_amount'} = floatval($a->{'doc_amount'});
					$a->{'doc_outstanding'} = floatval($a->{'doc_outstanding'});
					$a->{'knockoff_amount'} = floatval($a->{'knockoff_amount'});
					$dataForApp['payment_doc'][] = $a;

					$table.='<tr style="text-align:center;">
								<td>'.$i.'.</td>
								<td><font style="font-weight:bold;">'.$doc_code.'</font><br><font style="color:grey;">'.$doc_date.'</font></td>
								<td>'.$doc_amount.'</td>
								<td style="color:red;text-align:right;">'.$doc_outstanding.'</td>
							</tr>';
					if($knockoff_amount || $knockoff_amount != '0'){
						
						$table .=	'<tr style="text-align:right;">
										<td colspan="4" style="color:'.$applied_color.'; background-color:#f1f1f1">Applied: '.$knockoff_amount.'</td>
									<tr>';
					}
					$i++;
				}
				$table .= '</table>';
			}else{
				$dataForApp['payment_doc'] = array();
			}
			$dataForApp['payment_dtl'] = array();
			
			$running = 0;

			$sql = "SELECT * FROM `cms_payment_detail` AS pm LEFT JOIN cms_salesperson_uploads su ON su.upload_bind_id = pm.payment_id AND upload_bind_type = 'PMC' WHERE `payment_id` ='" . mysql_real_escape_string($result['payment_id']) . "' AND cancel_status = '0' ORDER BY upload_id DESC LIMIT 1";
			$db->query($sql);

		while($result2 = $db->fetch_array()) {
			$dtl_remark = explode(" ",$result2['payment_detail_remark']);
			$cheque_date = $dtl_remark[count($dtl_remark)-1];
			$dataForApp['payment_dtl'][$running] = array(
				'payment_cheque_no' => $result2['cheque_no'],
				'payment_received_in' => $result2['payment_by'],
				'payment_cheque_date'=>date('Y-m-d',strtotime(str_replace("/","-",$cheque_date)))
			);

			$view .='
			<div class="divider"></div>
				<div class="limit-text-length company-title" style="max-width:100%;height:25px;">
					<center style="height:25px;margin-left:5px;margin-right:5px;"> '.$result["cust_company_name"].' </center>
				</div>
				<div class="divider"></div>
			<table class="data">';
				
			$view .='
				<tr>
					<td>
						<p><span style="font-weight:bold;">Payment ID: </span><span>'.$result2['payment_id'].'</span></p>
						<p><span style="font-weight:bold;">Payment Method: </span><span style="color:#147efb;font-weight:bold">'.$result2['payment_method'].'</span></p>
						<p><span style="font-weight:bold;">Bank Name: </span><span>'.$result2['payment_by'] . '</span></p>
						<p><span style="font-weight:bold;">Cheque No: </span><span>'.$result2['cheque_no'].'</span></p>
						<p><span style="font-weight:bold;">Payment Amount: </span><span>RM '.number_format($result2['payment_amount'],2).'</span></p>
						<p><span style="font-weight:bold;">Remark: </span><span>'.$result['description'].'</span></p>
					</td>
				</tr>
			</table>
			<div class="divider"></div>'.$table;

			

				if($result2["upload_image"]){
					$view .='
					<div class="divider"></div>
						<div class="limit-text-length company-title" style="max-width:100%;height:25px;">
							<center style="height:25px;margin-left:5px;margin-right:5px;"> Cheque Image </center>
						</div>
						<div class="divider"></div>
					<table class="data">';
					$view .='<div class="divider"></div>
					<table class="data">
					<div class="popup-gallery">
					<a class="attachment" href="' . str_replace('"', '\"', $result2["upload_image"]) . '" title="' . str_replace('"', '\"', $result2["cheque_no"]) . '"><img src="' . str_replace('"', '\"', $result2["upload_image"]) . '" /></a>
								</div>
							</td>
						</tr>
					</table>
					<div class="divider"></div>';
				}else{
					$view .='<div class="divider"></div>
					<div class="limit-text-length company-title" style="max-width:100%;height:25px;">
					<center style="height:25px;margin-left:5px;margin-right:5px;"> No cheque image found. </center>
					</div>
					<div class="divider"></div>';
				}
				$running++;
			}

			$sql = "SELECT * FROM `cms_salesperson_uploads` WHERE upload_bind_type = 'PM' AND `upload_status` = '1' AND `upload_bind_id` = '" . mysql_real_escape_string($result['payment_id']) . "' ORDER BY `upload_id`";

			$db->query($sql);

			$uploaded_image = array();
			while($result3 = $db->fetch_array()) {
				if(!isset($uploaded_image[$result3["upload_image"]]))
					$uploaded_image[$result3["upload_image"]] = array();
				$uploaded_image[$result3["upload_image"]][] = $result3;
			}

			if(sizeof($uploaded_image) > 0) {
				$view .='
					<div class="divider"></div>
						<div class="limit-text-length company-title" style="max-width:100%;height:25px;">
							<center style="height:25px;margin-left:5px;margin-right:5px;"> Other Attachment (' . sizeof($uploaded_image) . ')</center>
						</div>
						<div class="divider"></div>
					<table class="data">';

				$view .='<div class="divider"></div>
				<table class="data">';
				
				foreach($uploaded_image as $type => $items) {
					$view .= '
					<tr>
						<td style="border: 0px; padding: 10px 2px;">
							<div class="divider"></div>
							<div class="popup-gallery">';
					foreach($items as $row) {
						$view .= '<a class="attachment" href="' . str_replace('"', '\"', $row["upload_image"]) . '" title="' . str_replace('"', '\"', $row["upload_remark"]) . '"><img src="' . str_replace('"', '\"', $row["upload_image"]) . '" /></a>';
					}
					$view .= '
							</div>
						</td>
					</tr>';
				}
				
				$view .='
				</table>
				<div class="divider"></div>';
			}else{
				$view .='
							<div class="divider"></div>
								<div class="limit-text-length company-title" style="max-width:100%;height:25px;">
									<center style="height:25px;margin-left:5px;margin-right:5px;"> No other attachment found. </center>
								</div>
								<div class="divider"></div>
							';
			}
			$view .= '<div id="map"></div>';
		}
	}
	$json_data['data'] = $dataForApp;
	$json_data['views'] = $view;

	return $json_data;
}

?>