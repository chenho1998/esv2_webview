<?php 
require_once('./model/DB_class.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$settings                       = parse_ini_file('../config.ini',true);

if(isset($_GET['salesperson_id']) && isset($_GET['client'])){
    $client                     = $_GET['client'];
    $sp_id                      = $_GET['salesperson_id'];

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
        $final = array();

        $result = $db->query("SELECT MAX(checkout_time) AS updated_at, cust_code, person_met AS meeting_person FROM cms_visit_report AS v LEFT JOIN cms_customer c ON c.cust_id = v.customer_id WHERE salesperson_id = '{$sp_id}' GROUP BY customer_id DESC;");

        if($db->get_num_rows() != 0){
            while($row = $db->fetch_array()){
                $final[] = $row;
            }
        }

        echo json_encode(array("last_checkin" => $final),JSON_UNESCAPED_UNICODE);
    }
}
?>