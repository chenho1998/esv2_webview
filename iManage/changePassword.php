<?php
require_once('./model/MySQL.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
date_default_timezone_set('Asia/Kuala_Lumpur');

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['client'])){
    $settings = parse_ini_file('../config.ini',true);

    $client = $_POST['client'];
    $username = $_POST['username'];
    $login_id = $_POST['login_id'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $is_selforder = isset($_POST['is_selforder']) && $_POST['is_selforder'] == 1;

    $client_config = $settings[$client];
    $login_config = $settings['selforder'];

    $mysql = new MySQL($login_config);

    $checkSql = "SELECT * FROM api_login WHERE username = '{$username}' AND pass = '{$old_password}' AND client_company = '{$client}'";

    $checkRes = $mysql->Execute($checkSql);

    if(count($checkRes) > 0){
        $updateSql = "UPDATE api_login SET pass = '{$new_password}' WHERE username = '{$username}' AND pass = '{$old_password}' AND client_company = '{$client}'";

        $res = $mysql->Execute($updateSql);
        if($res){
            if($is_selforder){
                echo json_encode(
                    array(
                        'result'=>'1',
                        'message'=>'username & password update successful'
                    )
                );
                return;
            }
            $mysql = new MySQL($client_config);

            $cms_login = "UPDATE cms_login SET password = '{$new_password}' WHERE login_id = '{$login_id}'";
            $res = $mysql->Execute($cms_login);

            if($res){
                echo json_encode(
                    array(
                        'result'=>'1',
                        'message'=>'username & password update successful'
                    )
                );
            }else{
                $updateSql = "UPDATE api_login SET pass = '{$old_password}' WHERE username = '{$username}' AND pass = '{$new_password}' AND client_company = '{$client}'";
                $mysql->Execute($updateSql);

                echo json_encode(
                    array(
                        'result'=>'0',
                        'message'=>'username & password update failed !!'
                    )
                );
            }
        }else{
            echo json_encode(
                array(
                    'result'=>'0',
                    'message'=>'username & password update failed'
                )
            );
        }
    }else{
        echo json_encode(
            array(
                'result'=>'0',
                'message'=>'username & password did not match'
            )
        );
    }
}
?>