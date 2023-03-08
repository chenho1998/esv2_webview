<?php
session_start();
//header('Content-Type: text/plain; charset="UTF-8"');
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once('./model/DB_class.php');

function updateCuttingDate($data){

    $config = parse_ini_file('../config.ini',true);
    $client = $data['client'];
    $settings                   = $config[$client];
    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
    $db_name                    = $settings['db'];
    $db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

    $constructor_job = $config['Constructor_Job'];
	$allow_constructor = in_array($client,$constructor_job['constructor_job']);

    $db = new DB();
    $con_1 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);

    if(!$allow_constructor){
        $cutting_table = 'cms_order_cutting_date';
    }else{
        $cutting_table = 'cms_acc_order_cutting_date';
    }

    if($data['action']=='delete'){

        $delete_query = "UPDATE ".$cutting_table." SET active_status ='0' WHERE id =".$data['id'];
        $db->query($delete_query);

    }else if($data['action']=='insert'){
        $insert_query = "INSERT INTO ".$cutting_table."(task_date,cutting_title,cutting_status,order_id,cutting_remark,cutting_date,create_login_id) VALUES ('".$data['date']."','".$data['title']."','".$data['status']."','".$data['order_id']."','".$data['remark']."',NOW(),'".$data['userId']."')" ;
        
        $db->query($insert_query);
        $cuttingId_query = "select max(id) as id from ".$cutting_table."";
        $db->query($cuttingId_query);
        while($result = $db->fetch_array()){
            $inserted_order_id = $result['id'];
        }
        $json_data['order_id'] = $inserted_order_id;
    }else if($data['action'] == 'update'){
        $insert_query = "UPDATE ".$cutting_table." SET cutting_title = '{$data['title']}',cutting_status = '{$data['status']}',active_status='{$data['active']}',cutting_remark='{$data['remark']}',task_date='{$data['date']}',edit_login_id='{$data['userId']}' WHERE id = '{$data['rec_id']}'" ;

        $db->query($insert_query);

        $json_data['order_id'] = $data['order_id'];
    }

    $json_data['connection'] = json_encode(array(
        "user"=>$db_user,
        "password"=>$db_pass,
        "db"=>$db_name,
        "client"=>$client,
        "con_success"=>$con_1,
    ));
    $json_data['message'] = $delete_query;

    return json_encode($json_data);
}
?>
