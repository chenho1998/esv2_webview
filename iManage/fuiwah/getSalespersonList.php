<?php
header('Content-Type: text/plain; charset="UTF-8"');
require_once('model/DB_class.php');

//Added by Sakib on 14th March 2019

function getSalespersonList($data)
{
    $settings                   = parse_ini_file('../config.ini',true);

    $client = $data['client'];
    $salespersonid = $data['salespersonid'];
    
	$db = new DB();

    $settings                   = $settings[$client];

    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
    $db_name                    = $settings['db'];
    $db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

    $connection                 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);

    if(!$connection){
        echo 'Server error. Please refresh the page';
        die();
    }

    $json_data = array(
			'result'=> ""
			,'action'=>"getSalespersonList"
            ,'data'=>array()
            ,'role_id'=>""
			,"message"=> ""
			,"message_code"=> ""
    );

    if($salespersonid != 0){
        $query2 = "SELECT role_id FROM cms_login WHERE login_id IN ($salespersonid)";
        $db->query($query2);
        if($db->get_num_rows() != 0)
        {
            while($result = $db->fetch_array())
            {
                $role_id = $result['role_id'];
            }
        }
        
        $json_data['role_id'] = $role_id;
    }else{
        $json_data['role_id'] = 10;
    }

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

    if (strpos($salespersonid,',') !== false){
        $query="SELECT login_id,name FROM cms_login WHERE login_id IN ($salespersonid)";
    }else if($salespersonid!=0 && $role_id != 10){
        $query="SELECT login_id,name FROM cms_login WHERE login_id IN ($salespersonid)";
    }else if($role_id == 10){
        $query = "SELECT login_id,name FROM cms_login WHERE role_id=2 OR role_id=10";
    }else{
        $query="SELECT login_id,name FROM cms_login WHERE role_id=2 AND login_status = 1"; //role_id 2 means salesperson, not admin or anything
    }
    $db->query($query);
    $salespersonArr = array();

    $json_data['result'] = '1';
    
    if($db->get_num_rows() != 0)
    {
        while($result = $db->fetch_array())
        {
            // $customerIDArr[] = $result['customer_id'];
            $salespersonDetails = array(
                        'value'=>$result['login_id']
                        ,'label'=>$result['name']
                );

                $salespersonArr[] = $salespersonDetails;
        }
    }

    $json_data['data'] = $salespersonArr;
    $json_data['salesperson_id'] = $salespersonid;

    return $json_data;

    //return json_encode($json_data,JSON_UNESCAPED_UNICODE);
}
?>
