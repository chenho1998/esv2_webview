<?php
/**
 * Created by PhpStorm.
 * User: julfikar
 * Date: 2020-02-02
 * Time: 01:55
 */
require_once('./model/DB_class.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
date_default_timezone_set('Asia/Kuala_Lumpur');

$settings                       = parse_ini_file('../config.ini',true);

if(isset($_GET['client'])){
    $client                     = $_GET['client'];

    $db                         = new DB();

    $settings                   = $settings[$client];

    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
    $db_name                    = $settings['db'];
    $db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

    $connection                 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);

    $query = "SELECT NOW() - INTERVAL 1 HOUR AS mysql_time";
    
    $db->query($query);

    $mysql_time = date("Y-m-d H:i:s");

    if($db->get_num_rows() != 0){
        while($result = $db->fetch_array()){
            $mysql_time = $result['mysql_time'];
        }
    }

    echo json_encode(array(
        "mysql_time"=>$mysql_time
    ));
}
?>