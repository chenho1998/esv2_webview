<?php 
session_start();
header('Content-Type: text/plain; charset="UTF-8"');
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once('./model/DB_class.php');
//sub_client
function getDeliveryStatusView($data){

    $final_view = '';
    
	$config = parse_ini_file('../config.ini',true);

	$client = $data['client'];

    $from_invoice = $config['DO_JOB']['from_invoice'];
	$from_invoice = in_array($client,$from_invoice);

	$settings                   = $config[$client];

	$db_user                    = $settings['user'];
	$db_pass                    = $settings['password'];
    $db_name                    = $settings['db'];
    $db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

    $client_host                = isset($settings['client_host']) ? $settings['client_host'] : false;

	$db = new DB();
    $con_1 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);

    $is_sage = $data['is_sage'] == '1';

    $result = mysql_query("SHOW COLUMNS FROM `cms_do` LIKE 'do_type'");
	$do_type_exists = (mysql_num_rows($result))?TRUE:FALSE;

    $self_collect = $from_invoice ? mysql_query("SHOW COLUMNS FROM `cms_invoice` LIKE 'self_collect'") : mysql_query("SHOW COLUMNS FROM `cms_do` LIKE 'self_collect'");
    $self_collect_exists = (mysql_num_rows($self_collect))?TRUE:FALSE;

    $chop_status = mysql_query("SHOW COLUMNS FROM `cms_do_job` LIKE 'chop_status'");
    $chop_status_exists = (mysql_num_rows($chop_status))?TRUE:FALSE;

    $hasCustCode = false;
    $condition = "";
    $cust_code = mysql_real_escape_string($data['cust_code']);
    if($cust_code){
        $condition = " AND do.cust_code = '{$cust_code}'";
        $hasCustCode = true;
    }

    $job_status = $data["delivery_status"];

    $salesperson_id = $data['salesperson_id'];

    if($salesperson_id != 0){
        $lookup = $salesperson_id;
        $db->query("SELECT * FROM cms_mobile_module WHERE module = 'app_sp_group'");
        $salesperson_group = null; 
        while($result = $db->fetch_array()){
            $salesperson_group = $result['status'];
        }
        if(!empty($salesperson_group)){
            $salesperson_group = json_decode($salesperson_group,true);
            if(isset($salesperson_group[$lookup])){
                $status = $salesperson_group[$lookup];
                if($status == 1){
                    $salesperson_id = 0;
                }else if($status == -1){
                    
                }else{
                    $append_sp = $lookup.',';
                    for ($i=0; $i < count($status); $i++) { 
                        $obj = $status[$i];
                        $append_sp .= $obj['id'];
                        if($i != count($status) - 1){
                            $append_sp .= ',';
                        }
                    }
                    $salesperson_id = $append_sp;
                }
            }
        }
    }

    $_SESSION['d_cust_code'] = $data['cust_code'];
    $_SESSION['d_status'] = $job_status;
    $_SESSION['d_date_from'] = $data['date_from'];
    $_SESSION['d_date_to'] = $data['date_to'];

    $typeNstatus = explode('-',$job_status);

    if($job_status){
        if($job_status == 'pending'){
            $job_status = 'Pending Delivery';
        }
        if($job_status == 'progress'){
            $job_status = 'Delivery In Progress';
        }
        if($job_status == 'completed'){
            $job_status = 'Delivery Completed';
        }
        if($job_status == 'cancelled'){
            $job_status = 'Delivery Cancelled';
        }

        $job_status = strtolower($job_status);
        $job_cond = " AND LOWER(job.job_status) = '{$job_status}'";

        if($job_status == 'self'){
            $job_cond = " AND do.self_collect = 1";
        }
        if($job_status == 'waiting'){
            $job_cond = 'AND job.job_status IS NULL';
        }
        if(count($typeNstatus) > 1){
            $job_status = strtolower($typeNstatus[1]);
            $job_cond = " AND LOWER(job.job_status) = '{$job_status}' AND job.do_type = '{$typeNstatus[0]}' ";
        }
        $condition .= $job_cond;
    }

    if($data['date_from'] && $data['date_to']){
        $date_from = $data['date_from']." 00:00:00";
        $date_to = $data['date_to']." 23:59:59";

        $condition .= $from_invoice ?  " AND do.invoice_date BETWEEN '{$date_from}' AND '{$date_to}' " :  " AND do.do_date BETWEEN '{$date_from}' AND '{$date_to}' ";
    }
    $salesperson_cond = "";
    if($salesperson_id){
        $salesperson_cond = " AND do.salesperson_id IN ({$salesperson_id}) ";
    }

    $chop_status_query = "";
    if($chop_status_exists){
        $chop_status_query .= " ,chop_status ,chop_remark";
    }
    
    if($client == 'tomauto'){
        $query = "SELECT DISTINCT job.do_code AS transport_no, do.do_amount,do.do_code,do.do_date,do.cust_code,do.self_collect,cs.cust_company_name,job.job_id, job.start_time, job.end_time, job.rider_name, job.job_status, cs.billing_address1,cs.billing_address2, cs.billing_address3, cs.billing_city {$chop_status_query} FROM cms_do AS `do` LEFT JOIN cms_customer AS cs ON do.cust_code = cs.cust_code LEFT JOIN cms_do_job AS job ON do.transport_no = SUBSTRING(job.do_code,POSITION(do.transport_no IN job.do_code),LENGTH(do.transport_no)) WHERE do.cancelled = 'F' {$salesperson_cond} {$condition} ORDER BY do.do_date DESC";
    }else{

        if($from_invoice){

            if($self_collect_exists){
                $query = "SELECT DISTINCT do.invoice_amount AS do_amount,do.invoice_code AS do_code,do.invoice_date AS do_date,do.cust_code,do.self_collect,cs.cust_company_name,job.job_id, job.start_time, job.end_time, job.rider_name, job.job_status, cs.billing_address1,cs.billing_address2, cs.billing_address3, cs.billing_city, job_remark, job.active_status {$chop_status_query} FROM cms_invoice AS `do` LEFT JOIN cms_customer AS cs ON do.cust_code = cs.cust_code LEFT JOIN cms_do_job AS job ON do.invoice_code = SUBSTRING(job.do_code,POSITION(do.invoice_code IN job.do_code),LENGTH(do.invoice_code)) WHERE do.cancelled = 'F' {$salesperson_cond} {$condition} ORDER BY do.invoice_date DESC";
            }else{
                $query = "SELECT DISTINCT do.invoice_amount AS do_amount,do.invoice_code AS do_code,do.invoice_date AS do_date,do.cust_code,IF(sc.self_collect,sc.self_collect,0) AS self_collect, sc.created_date, cs.cust_company_name,job.job_id, job.start_time, job.end_time, job.rider_name, job.job_status, cs.billing_address1,cs.billing_address2, cs.billing_address3, cs.billing_city, job_remark, job.active_status{$chop_status_query}  FROM cms_invoice AS `do` LEFT JOIN cms_customer AS cs ON do.cust_code = cs.cust_code LEFT JOIN cms_do_job AS job ON do.invoice_code = SUBSTRING(job.do_code,POSITION(do.invoice_code IN job.do_code),LENGTH(do.invoice_code)) LEFT JOIN cms_self_collect AS sc ON sc.doc_no = do.invoice_code WHERE do.cancelled = 'F' {$salesperson_cond} {$condition} ORDER BY do.invoice_date DESC";
            }

        }else{

            if($self_collect_exists){
                $query = "SELECT DISTINCT do.do_amount,do.do_code,do.do_date,do.cust_code,do.self_collect,cs.cust_company_name,job.job_id, job.start_time, job.end_time, job.rider_name, job.job_status, cs.billing_address1,cs.billing_address2, cs.billing_address3, cs.billing_city, job_remark, job.active_status {$chop_status_query} FROM cms_do AS `do` LEFT JOIN cms_customer AS cs ON do.cust_code = cs.cust_code LEFT JOIN cms_do_job AS job ON do.do_code = SUBSTRING(job.do_code,POSITION(do.do_code IN job.do_code),LENGTH(do.do_code)) WHERE do.cancelled = 'F' {$salesperson_cond} {$condition} ORDER BY do.do_date DESC";
            }else{
                $query = "SELECT DISTINCT do.do_amount,do.do_code,do.do_date,do.cust_code,IF(sc.self_collect,sc.self_collect,0) AS self_collect, sc.created_date, cs.cust_company_name,job.job_id, job.start_time, job.end_time, job.rider_name, job.job_status, cs.billing_address1,cs.billing_address2, cs.billing_address3, cs.billing_city, job_remark, job.active_status{$chop_status_query}  FROM cms_do AS `do` LEFT JOIN cms_customer AS cs ON do.cust_code = cs.cust_code LEFT JOIN cms_do_job AS job ON do.do_code = SUBSTRING(job.do_code,POSITION(do.do_code IN job.do_code),LENGTH(do.do_code)) LEFT JOIN cms_self_collect AS sc ON sc.doc_no = do.do_code WHERE do.cancelled = 'F' {$salesperson_cond} {$condition} ORDER BY do.do_date DESC";
            }
        }

        
        
    }

    //$query = "SELECT DISTINCT do.do_amount,do.do_code,do.do_date,do.cust_code,do.self_collect,cs.cust_company_name,job.job_id, job.start_time, job.end_time, job.rider_name, job.job_status, cs.billing_address1,cs.billing_address2, cs.billing_address3, cs.billing_city FROM cms_do AS `do` LEFT JOIN cms_customer AS cs ON do.cust_code = cs.cust_code LEFT JOIN cms_do_job AS job ON do.do_code = SUBSTRING(job.do_code,POSITION(do.do_code IN job.do_code),LENGTH(do.do_code)) WHERE do.cancelled = 'F' {$salesperson_cond} {$condition} ORDER BY do.do_date DESC";

    //echo $query;return;
    file_put_contents('del_query.log',$query);

    $db->query($query);

    $final = array();
    $finalDoAmount = 0;
	if($db->get_num_rows() != 0)
	{
		while($each = $db->fetch_array()){

            $do_code = $each['do_code'];
            $do_obj = array(
                "do_code" =>$each['do_code'],
                "cust_code"=>$each['cust_code'],
                "cust_name"=>$each['cust_company_name'],
                "address1"=>$each['billing_address1'],
                "address2"=>$each['billing_address2'],
                "address3"=>$each['billing_address3'],
                "city"=>$each['billing_city'],
                "quantity"=>$each['quantity'],
                "item_count"=>$each['item_count'],
                "do_date"=>$each['do_date'],
                "do_amount"=>$each['do_amount'],
                "self_collect"=>$each['self_collect'],
                "created_date"=>$each['created_date'] ? $each['created_date'] : ''
            );

            

            $job_obj = $each['job_id'] ? array(
                "job_id"=>$each['job_id'] ? $each['job_id'] : '',
                "job_status"=>$each['job_status'] ? $each['job_status'] : '-',
                "start_time"=>$each['start_time'] ? $each['start_time'] : '',
                "end_time"=>$each['end_time'] ? $each['end_time'] : '',
                "rider_name"=>$each['rider_name'] ? $each['rider_name'] : '-',
                "transport_no" => $each['transport_no'] ? $each['transport_no'] : '',
                "job_remark" => $each['job_remark'] ? $each['job_remark'] : '',
                "chop_status"=>$each['chop_status'] ? $each['chop_status'] : 0,
                "chop_remark"=>$each['chop_remark'] ? $each['chop_remark'] : '',
                "active_status"=>$each['active_status'] 
            ) : array("job_status"=>$each['job_status']);


            if($final[$do_code]){
                $current_arr = $final[$do_code]['jobs'];
                array_push($current_arr,$job_obj);
                $final[$do_code]['jobs'] = $current_arr;
            }else{
                $final[$do_code] = $do_obj;
                $final[$do_code]['jobs'][] = $job_obj;
            }
        }
        //echo json_encode($final);return;

        foreach ($final as $key => $result) {
            $jobs = $result['jobs'];
            
            $jobs = $result['jobs'];
            
            $doc_id = $result['do_code'];

            $finalDoAmount += floatval($result['do_amount']);

            $do_dtl_link = "https://easysales.asia/esdelivery/webview/delivery_details.php?client={$client}&do_code={$doc_id}";

            $date_view = $from_invoice ? '<p class="dates" ><strong>INV Date</strong>: '.displaydate($result["do_date"]).' </p>' : '<p class="dates" ><strong>DO Date</strong>: '.displaydate($result["do_date"]).' </p>';

            $final_view .= '
                <div style="  box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);margin-bottom:10px;border-radius:5px;">
				<div onclick="redirectTo(\''.$do_dtl_link.'\')" style="cursor: pointer;padding:5px;">
					<p class="title" style="color:grey;font-size:13px;line-height:0;margin-top:5px"> '.$result['do_code'].' </p>
                    <p class="title" > '.$result["cust_name"].' </p>
                    '. $date_view .'
                    <p class="dates" >RM'.number_format($result["do_amount"],2).' </p>';
            if($result['self_collect']){
                if($result['created_date']){

                    $sc_created_date = DateTime::createFromFormat('Y-m-d H:i:s',$result['created_date']);
                    $sc_created_date = $sc_created_date->format('d M Y h:i a');

                    $final_view .= '<p class="job-row-text" style="color:green;font-style:italic;"><u>Self Collected ('.$sc_created_date.')</u></p>';
                }else{
                    $final_view .= '<p class="job-row-text" style="color:green;font-style:italic;"><u>Self Collected</u></p>';
                }
            }
            $final_view .= '</div>';
            if(count($jobs) != 0){
                //job-row
                $oddEven = 0;
                for ($i=0; $i < count($jobs); $i++) { 
                    $eachJob = $jobs[$i];

                    if($eachJob['job_id']){
                        $backgroundColor = '#e8ebef';
                        if($oddEven % 2 != 0){
                            $backgroundColor = 'white';
                        }
                        
                        $job_no = $eachJob['job_id'];
                        if($client_host){
                            $link = "https://easysales.asia/esdelivery/webview/getJobDetailsView.php?client={$client_host}&sub_client={$client}&job_no={$job_no}&doc_id={$doc_id}_{$client}";
                        }else{
                            $link = "https://easysales.asia/esdelivery/webview/getJobDetailsView.php?client={$client}&sub_client={$client}&job_no={$job_no}&doc_id={$doc_id}";
                        }

                        $transport_no_display = $eachJob['transport_no'] ? '<p class="job-row-text" style="color:black;font-weight:bold;">'.$eachJob['transport_no'].'</p>' : '';

                        $link = str_replace("\\","\\\\",$link);

                        $job_remark_view = $eachJob['job_remark'] ? ' <p class="dates job-row-text">Remark: '.$eachJob['job_remark'].'</p>' : '';

                        $chop_status_view = $eachJob['active_status'] == 1 ? $eachJob['chop_status'] == 1 ? ' <p class="dates job-row-text" style="color:green;"><b>Chopped</b></p>' : '<button onclick="openChopModal(\''.$eachJob['job_id'].'\')" style="border-radius:5px;background-color:orange;color:#FFF;border:0px solid black;padding:5px;font-size:10px;">UPDATE CHOP</button>' : '';

                        $chop_status_view .=  $eachJob['chop_status'] == 1 && $eachJob['active_status'] == 1 ? $eachJob['chop_remark'] ? '<p class="dates job-row-text">Chop Remark: <b>'.$eachJob['chop_remark'].'</b></p>' : '<p class="dates job-row-text">Chop Remark: -</p>' : '';

                        $final_view .= 
                        '
                        <div data-id="'.$eachJob['job_id'].'" class="job-row" style="background-color:'.$backgroundColor.';cursor: pointer;"> '.$transport_no_display.'
                            <div onclick="redirectTo(\''.$link.'\')" >
                            <p class="job-row-text" style="color:black">'.$eachJob['job_status'].'</p>
                            <p class="job-row-text">Rider: <strong>'.strtoupper($eachJob['rider_name']).'</strong></p>
                            <p class="dates job-row-text">Start time: '.displaydate($eachJob['start_time']).'</p>
                            <p class="dates job-row-text">End time: '.displaydate($eachJob['end_time']).'</p>
                            '.$job_remark_view.'
                            </div>
                            '.$chop_status_view.'
                        </div></div>
                        ';
                        $oddEven++;
                    }else{
                        $final_view .= 
                        '
                        <div class="job-row" style="background-color:#e8ebef">
                            <p class="job-row-text" style="color:black">'.$jobs[0]['job_status'].'</p>
                        </div></div>
                        ';
                    }
                }
            }
        }

        $finalDoAmount = number_format($finalDoAmount,2);

        $final_view .='

        <div style="height:50px;"></div>
                <div class="divider"></div>
                    <hr>
                    <div class="footer">
            <p>Total Amount : RM'.$finalDoAmount.'</p>
        </div>
            ';
    }

    return json_encode(array(
        "data"=>$final_view,
        "query"=>$query
    ));
}
function displaydate($datetime){
    if(!$datetime){
        return "-";
    }
    $datetime = strval($datetime);
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
    
    return $display;
}
?>