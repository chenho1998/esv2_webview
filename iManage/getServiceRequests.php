<?php 
require_once('./model/MySQL.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
date_default_timezone_set('Asia/Kuala_Lumpur');

if(isset($_GET['salesperson_id']) && isset($_GET['client'])){
    $salesperson_id = intval($_GET['salesperson_id']);
    $client = $_GET['client'];
    $sched_id = isset($_GET['sched_id']) ? $_GET['sched_id'] : 0;

    $settings = parse_ini_file('../config.ini',true);
    $settings = $settings[$client];
    $mysql = new MySQL($settings);

    $mobile_module = $mysql->Execute("select * from cms_mobile_module where module = 'app_technicians'");
    $mobile_module = $mobile_module[0];
    $technician_grp = json_decode($mobile_module['status'],true);
    
    $isTechnician = false;
    foreach ($technician_grp as $key => $value) {
        $lookupId = intval($key);
        if($lookupId === $salesperson_id){
            $isTechnician = true;
            break;
        }
        if(is_array($value)){
            for ($i=0; $i < count($value); $i++) { 
                $lookupId = intval($value[$i]);
                if($lookupId === $salesperson_id){
                    $isTechnician = true;
                    break;
                }
            }
        }
    }

    $customerList = array();

    /**
     * If salesman, get service request where created_by = salesperon_id
     * If technician, get service request where tech_assignee OR tech_assigned = salesperson_id
     */
    $sql = "select distinct cust_code from cms_customer_visit_sched where created_by = '{$salesperson_id}' and sched_datetime > (current_date()-interval 3 day); ";
    if($isTechnician){
        $sql = "select distinct cust_code from cms_customer_visit_sched where (tech_assignee = '{$salesperson_id}' OR tech_assigned ='{$salesperson_id}') and sched_datetime > (current_date()-interval 3 day); ";
    }
    if($sched_id){
        $sql = "select cust_code from cms_customer_visit_sched where id = '{$sched_id}'";
    }
    $service_request = $mysql->Execute($sql);

    for ($i=0; $i < count($service_request); $i++) { 
        $cust_code = MySQL::sanitize($service_request[$i]['cust_code']);

        $sql = "select * from cms_customer_visit_sched where created_by = '{$salesperson_id}' and sched_datetime > (current_date()-interval 3 day); ";
        if($isTechnician){
            $sql = "select * from cms_customer_visit_sched where (tech_assignee = '{$salesperson_id}' OR tech_assigned ='{$salesperson_id}') and sched_datetime > (current_date()-interval 3 day); ";
        }
        if($sched_id){
            $sql = "select * from cms_customer_visit_sched where id = '{$sched_id}'";
        }

        $scheduleList = $mysql->Execute($sql);
        for ($j=0; $j < count($scheduleList); $j++) { 
            $obj = safe($scheduleList[$j]);
            if($obj){
                $obj['unique_key'] = $obj['id'];
                $scheduleList[$j] = $obj;
            }
        }
        $attachments = $mysql->Execute("select * from cms_customer_atch where cust_code = '{$cust_code}'");
        for ($j=0; $j < count($attachments); $j++) { 
            $obj = safe($attachments[$j]);
            if($obj){
                $attachments[$j] = $obj;
            }
        }
        $result = $mysql->Execute("select * from cms_customer where cust_code = '{$cust_code}'");
        $result = safe($result[0]);
        $customerDetails = array(
            'cust_id'=>$result['cust_id']
            ,'created_date'=>$result['created_date']
            ,'cust_code'=>$result['cust_code']
            ,'cust_company_name'=>$result['cust_company_name']
            ,'cust_incharge_person'=>$result['cust_incharge_person']
            ,'cust_reference'=>$result['cust_remark']
            ,'cust_email'=>$result['cust_email']
            ,'cust_tel'=>$result['cust_tel']
            ,'cust_fax'=>$result['cust_fax']
            ,'cust_remark'=>$result['cust_remark']
            ,'billing_address1'=>$result['billing_address1']
            ,'billing_address2'=>$result['billing_address2']
            ,'billing_address3'=>$result['billing_address3']
            ,'billing_address4'=>$result['billing_address4']
            ,'billing_city'=>$result['billing_city']
            ,'billing_state'=>$result['billing_state']
            ,'billing_zipcode'=>$result['billing_zipcode']
            ,'billing_country'=>$result['billing_country']
            ,'shipping_address1'=>$result['shipping_address1']
            ,'shipping_address2'=>$result['shipping_address2']
            ,'shipping_address3'=>$result['shipping_address3']
            ,'shipping_address4'=>$result['shipping_address4']
            ,'shipping_city'=>$result['shipping_city']
            ,'shipping_state'=>$result['shipping_state']
            ,'shipping_zipcode'=>$result['shipping_zipcode']
            ,'shipping_country'=>$result['shipping_country']
            ,'selling_price_type'=>$result['selling_price_type']
            ,'customer_status'=>$result['customer_status']
            ,'termcode'=>$result['termcode']
            ,'current_balance'=>$result['current_balance']
            ,'schedule_list'=>$scheduleList
            ,'latitude'=>$result['latitude']
            ,'longitude'=>$result['longitude']
            ,'appointments'=>array()
            ,'attachments'=>$attachments
        );
        $customerList[] = $customerDetails;
    }
    $mysql->Close();

    echo json_encode(array('customers'=>$customerList),JSON_UNESCAPED_UNICODE);
}
function safe($obj){
    foreach ($obj as $key => $value) {
        if($value == null){
            $obj[$key] = '';
        }
    }
    return $obj;
}
?>