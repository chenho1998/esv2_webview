<?php
header('Content-Type: text/plain; charset="UTF-8"');
require_once('model/DB_class.php');

function getAllSalespersonCustomerList($data)
{
    $client = "easysale_".$data['client'];

	$db = new DB();
    $check = $db->connect_db_with_db_config($client);

    if(!$check){
        echo 'db error';
        die();
    }
    
    $json_data = array(
			'result'=> ""
			,'action'=>"getCustomerList"
			,'data'=>array()
			,"message"=> ""
			,"message_code"=> ""
	);
    
    $db->query(
        "SELECT cms_customer.cust_id, cms_customer.cust_company_name FROM cms_customer_salesperson LEFT JOIN cms_customer ON cms_customer_salesperson.customer_id = cms_customer.cust_id WHERE cms_customer.customer_status = 1 AND cms_customer_salesperson.salesperson_id = ". $data['salespersonid'] ." ORDER BY cms_customer.cust_company_name"
        );

    $customerArr = array();
    
    $json_data['result'] = '1';

    if($db->get_num_rows() != 0)
    {
        while($result = $db->fetch_array())
        {
            // $customerIDArr[] = $result['customer_id'];
            $customerDetails = array(
                        'value'=>$result['cust_id']
                        ,'label'=>$result['cust_company_name']
                );
                                
                $customerArr[] = $customerDetails;   
        }        
    }

    $json_data['data'] = $customerArr;
    
    return json_encode($json_data,JSON_UNESCAPED_UNICODE);

}
?>