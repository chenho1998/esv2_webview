<?php
session_start();
//header('Content-Type: text/plain; charset="UTF-8"');
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once('./model/DB_class.php');

function updateOrderApproval($data){

    $config                     = parse_ini_file('../config.ini',true);
    $client                     = $data['client'];
    $settings                   = $config[$client];
    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
    $db_name                    = $settings['db'];

    $db     = new DB();
    $con_1  = $db->connect_with_given_connection($db_user,$db_pass,$db_name);


    if($data['action']=='approve'){

        $approve_query = "UPDATE cms_order_item SET order_item_validity = '2' WHERE order_item_id = '".$data['item_id']."'";
        $db->query($approve_query);

        return $json_data['connection'] = json_encode(array(
            "query"=>$approve_query,
        ));

    }else if($data['action']=='reject'){

        $reject_query = "UPDATE cms_order_item SET order_item_validity = '0' WHERE order_item_id = '".$data['item_id']."'";
        $db->query($reject_query);

        return $json_data['connection'] = json_encode(array(
            "query"=>$reject_query,
        ));
    
    }else if($data['action']=='undo'){

        // $get_status_query = "select order_item_validity from cms_order_cutting_date";
        // $db->query($get_status_query);

        // while($result = $db->fetch_array()){
        //       $status = $result['order_item_validity'];
        // }

        // if($status=='0'){
        //     $undo_query = "UPDATE cms_order_item SET order_item_validity = '2' WHERE order_item_id = '".$data['item_id']."'";
        // }else{
        //     $undo_query = "UPDATE cms_order_item SET order_item_validity = '0' WHERE order_item_id = '".$data['item_id']."'";
        // }

        $undo_query = "UPDATE cms_order_item SET order_item_validity = '1' WHERE order_item_id = '".$data['item_id']."'";
        $db->query($undo_query);

        return $json_data['connection'] = json_encode(array(
            "query"=>$undo_query,
        ));
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
