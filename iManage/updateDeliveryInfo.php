<?php
/**
 * Created by PhpStorm.
 * User: julfikar
 * Date: 2019-06-25
 * Time: 14:05
 */

require_once('./model/DB_class.php');

$settings                       = parse_ini_file('../config.ini',true);

    /* {
        'client_name'     : client,
        'so_no'           : so_no,
        'deliveryCost'    : deliveryCost,
        'deliveryDate'    : deliveryDate,
        'startPoint'      : startPoint,
        'destination'     : destination,
        'remark'          : remark,
        'latitude'        : latitudeVal,
        'longitude'       : longitudeVal,
        'riderId'         : riderId
    }; */

$data = array();
$client = "";

if(isset($_POST)){

    $data                       = file_get_contents('php://input');
    $data                       = json_decode($data,true);
    $action                     = str_replace(array("\n"," "),"",$data['action']);
    $action                     = trim(preg_replace('/\s\s+/', ' ', $action));
    $action                     = strtolower($action);

    $client                     = str_replace(array("\n"," "),"",$data['client_name']);
    $client                     = trim(preg_replace('/\s\s+/', ' ', $client));
    
    $db                         = new DB();

    $settings                   = $settings[$client];

    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
    $db_name                    = $settings['db'];
    $db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

    $connection                 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);

    if(!$connection){
        echo json_encode(array("success"=>"0"));
        return;
    }
    $query = "";
    $no_action = true;

    if($action == 'createjob'){
        $job_id                     = $data['job_id'];
        $do_code                    = $data['so_no'];
        $do_code                     = str_replace("\\","\\\\",$do_code);
        $rider_name                 = $data['rider_name'];
        $job_status                 = "Pending Delivery";
        $active_status              = 1;

        $query = "INSERT INTO cms_do_job (job_id, do_code, rider_name, job_status, active_status) VALUES('".$job_id."','".$do_code."', '".$rider_name."', '".$job_status."', '".$active_status."') ON DUPLICATE KEY UPDATE do_code = VALUES(do_code), rider_name = VALUES(rider_name), job_status = VALUES(job_status), active_status = VALUES(active_status), updated_at = VALUES(updated_at)";
        $no_action = false;
    }
    if($action == 'startjob'){
        $job_id                     = $data['job_id'];
        $rider_name                 = $data['rider_name'];
        $job_status                 = "Delivery In Progress";
        $active_status              = 1;
        $date                       = date("Y-m-d H:i:s");

        $query = "INSERT INTO cms_do_job (job_id, rider_name, start_time, job_status, active_status) VALUES('".$job_id."', '".$rider_name."','".$date."', '".$job_status."', '".$active_status."') ON DUPLICATE KEY UPDATE rider_name = VALUES(rider_name), job_status = VALUES(job_status), active_status = VALUES(active_status), updated_at = VALUES(updated_at), start_time = VALUES(start_time)";
        $no_action = false;
    }
    if($action == 'endjob'){
        $job_id                     = $data['job_id'];
        $rider_name                 = $data['rider_name'];
        $job_status                 = "Delivery Completed";
        $active_status              = 1;
        $date                       = date("Y-m-d H:i:s");

        $query = "INSERT INTO cms_do_job (job_id, rider_name, end_time, job_status, active_status) VALUES('".$job_id."', '".$rider_name."','".$date."', '".$job_status."', '".$active_status."') ON DUPLICATE KEY UPDATE rider_name = VALUES(rider_name), job_status = VALUES(job_status), active_status = VALUES(active_status), updated_at = VALUES(updated_at), end_time = VALUES(end_time)";
        $no_action = false;
    }
    if($action == 'canceljob'){
        $job_id                     = $data['job_id'];
        $rider_name                 = $data['rider_name'];
        $job_status                 = "Delivery Cancelled";
        $active_status              = 0;

        $query = "INSERT INTO cms_do_job (job_id, rider_name, job_status, active_status) VALUES('".$job_id."', '".$rider_name."', '".$job_status."', '".$active_status."') ON DUPLICATE KEY UPDATE rider_name = VALUES(rider_name), job_status = VALUES(job_status), active_status = VALUES(active_status), updated_at = VALUES(updated_at)";
        $no_action = false;
    }

    if($no_action){
        echo json_encode(array("success"=>"0"));
        return;
    }
    

    $db->query($query);
    if($db->get_affected_rows()>0){
        echo json_encode(array("success"=>"1"));
        return;
    }
    echo json_encode(array("success"=>"0"));
}
/**
 * 
 */
?>
