<?php
// header('Content-Type: text/plain; charset="UTF-8"');
require_once('model/DB_class.php');


function getCustomerList_v2($data)
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
        echo 'db error';
        die();
    }

    $db2 = clone $db;

    $json_data = array(
			'result'=> ""
			,'action'=>"getCustomerList"
			,'data'=>array()
			,"message"=> ""
			,"message_code"=> ""
    );
    
    $salespersonid = $data['salesperson_id'];
    if($salespersonid != 0){
        $lookup = $salespersonid;
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
                    $salespersonid = 0;
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
                    $salespersonid = $append_sp;
                }
            }
        }
    }

    if($salespersonid!='0'){
        $query = "SELECT cms_customer.cust_id,cms_customer.cust_code, cms_customer.cust_company_name FROM cms_customer_salesperson LEFT JOIN cms_customer ON cms_customer_salesperson.customer_id = cms_customer.cust_id WHERE cms_customer.customer_status = 1 AND cms_customer_salesperson.salesperson_id IN (".$salespersonid.") ORDER BY cms_customer.cust_company_name";
    }else{
        $query = "SELECT DISTINCT cms_customer.cust_id,cms_customer.cust_code, cms_customer.cust_company_name FROM cms_customer_salesperson LEFT JOIN cms_customer ON cms_customer_salesperson.customer_id = cms_customer.cust_id WHERE cms_customer.customer_status = 1 ORDER BY cms_customer.cust_company_name";
    }
    
    $db->query($query);

    $customerArr = array();

    $json_data['result'] = '1';

    if($db->get_num_rows() != 0)
    {
        while($result = $db->fetch_array())
        {
            $label = $result['cust_company_name'];
            if($client == 'fuiwah'){
                $db2->query("select * from cms_customer_branch where cust_id = '{$result['cust_id']}' and branch_active = 1 order by branch_id desc;");
                if($db2->get_num_rows() > 1)
                {
                    while($result2 = $db2->fetch_array())
                    {
                        $tmpname = explode(')',$result2['branch_name']);
                        if(count($tmpname)>1){
                            $label = $tmpname[1];
                        }else{
                            $label = $result2['branch_name'];
                        }
                        break;
                    }
                }
            }
            // $customerIDArr[] = $result['customer_id'];
            $customerDetails = array(
                        'value'=>$result['cust_code']
                        ,'label'=>$label
                );

            $customerArr[] = $customerDetails;
        }
    }

    $json_data['data'] = $customerArr;

    //return $json_data;

    return json_encode($json_data,JSON_UNESCAPED_UNICODE);
}
?>
