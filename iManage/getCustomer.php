<?php
require_once('./model/DB_class.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

date_default_timezone_set('Asia/Kuala_Lumpur');

$settings                       = parse_ini_file('../config.ini',true);

$custCode                       = '';
$client                         = '';
$connection                     = NULL;

if(isset($_GET['custCode']) && isset($_GET['client'])){
    $custCode                   = $_GET['custCode'];
    $client                     = $_GET['client'];

    $db                         = new DB();

    $settings                   = $settings[$client];

    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
    $db_name                    = $settings['db'];
    $db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

    $connection                 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);
    if(!$connection){
        echo "0";//unauthorized access
    }else{

        $customer               = array();
        $branches               = array();

        $custCode               = mysql_real_escape_string($custCode);
        $sql                    = "SELECT * FROM cms_customer WHERE cust_code = '{$custCode}' LIMIT 1";
        $db->query($sql);

        if($db->get_num_rows() != 0){
            while($result = $db->fetch_array()){
                array_push($customer,array(
                    'cust_id'=>ifNull($result['cust_id'])
                    ,'created_date'=>ifNull($result['created_date'])
                    ,'cust_code'=>ifNull($result['cust_code'])
                    ,'cust_company_name'=>ifNull($result['cust_company_name'])
                    ,'cust_incharge_person'=>ifNull($result['cust_incharge_person'])
                    ,'cust_reference'=>ifNull($result['cust_reference'])
                    ,'cust_email'=>ifNull($result['cust_email'])
                    ,'cust_tel'=>ifNull($result['cust_tel'])
                    ,'cust_fax'=>ifNull($result['cust_fax'])
                    ,'billing_address1'=>ifNull($result['billing_address1'])
                    ,'billing_address2'=>ifNull($result['billing_address2'])
                    ,'billing_address3'=>ifNull($result['billing_address3'])
                    ,'billing_address4'=>ifNull($result['billing_address4'])
                    ,'billing_city'=>ifNull($result['billing_city'])
                    ,'billing_state'=>ifNull($result['billing_state'])
                    ,'billing_zipcode'=>ifNull($result['billing_zipcode'])
                    ,'billing_country'=>ifNull($result['billing_country'])
                    ,'shipping_address1'=>ifNull($result['shipping_address1'])
                    ,'shipping_address2'=>ifNull($result['shipping_address2'])
                    ,'shipping_address3'=>ifNull($result['shipping_address3'])
                    ,'shipping_address4'=>ifNull($result['shipping_address4'])
                    ,'shipping_city'=>ifNull($result['shipping_city'])
                    ,'shipping_state'=>ifNull($result['shipping_state'])
                    ,'shipping_zipcode'=>ifNull($result['shipping_zipcode'])
                    ,'shipping_country'=>ifNull($result['shipping_country'])
                    ,'selling_price_type'=>ifNull($result['selling_price_type'])
                    ,'customer_status'=>ifNull($result['customer_status'])
                    ,'termcode'=>ifNull($result['termcode'])
                    ,'current_balance'=>ifNull($result['current_balance'])
                ));
            }
        }

        $sql                    = "SELECT * FROM cms_customer_branch WHERE cust_code = '{$custCode}'";
        $db->query($sql);

        if($db->get_num_rows() != 0){
            while($result = $db->fetch_array()){
                array_push($branches,array(
                    'branch_id'=>ifNull($result['branch_id']),
                    'cust_id'=>ifNull($result['cust_id']),
                    'agent_id'=>ifNull($result['agent_id']),
                    'branch_code'=>ifNull($result['branch_code']),
                    'branch_attn'=>ifNull($result['branch_attn']),
                    'branch_phone'=>ifNull($result['branch_phone']),
                    'branch_fax'=>ifNull($result['branch_fax']),
                    'branch_area'=>ifNull($result['branch_area']),
                    'branch_remark'=>ifNull($result['branch_remark']),
                    'branch_active'=>ifNull($result['branch_active']),
                    'branch_name'=>ifNull($result['branch_name']),
                    'billing_address1'=>ifNull($result['billing_address1']),
                    'billing_address2'=>ifNull($result['billing_address2']),
                    'billing_address3'=>ifNull($result['billing_address3']),
                    'billing_address4'=>ifNull($result['billing_address4']),
                    'billing_state'=>ifNull($result['billing_state']),
                    'billing_postcode'=>ifNull($result['billing_postcode']),
                    'billing_country'=>ifNull($result['billing_country']),
                    'shipping_address1'=>ifNull($result['shipping_address1']),
                    'shipping_address2'=>ifNull($result['shipping_address2']),
                    'shipping_address3'=>ifNull($result['shipping_address3']),
                    'shipping_address4'=>ifNull($result['shipping_address4']),
                    'shipping_state'=>ifNull($result['shipping_state']),
                    'shipping_postcode'=>ifNull($result['shipping_postcode']),
                    'shipping_country'=>ifNull($result['shipping_country'])
                ));
            }
        }

        echo json_encode(array("customer"=>$customer,"branch"=>$branches));
    }
}
function ifNull($value){
    if($value){
        return $value;
    }else{
        return "";
    }
}
