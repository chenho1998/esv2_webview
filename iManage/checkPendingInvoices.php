<?php
header('Content-Type: text/plain; charset="UTF-8"');
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once('./model/DB_class.php');

if(isset($_GET['client']) && isset($_GET['cust_code'])){

    $config = parse_ini_file('../config.ini',true);

	$client = $_GET['client'];

	$settings                   = $config[$client];
    
    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
    $db_name                    = $settings['db'];
    $db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

    $custCode                   = $_GET['cust_code'];

	$db = new DB();
    $con_1 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);
    
    $sql = "SELECT inv.*, IF(DATE(invoice_due_date) <= CURRENT_DATE() AND outstanding_amount > 0,'T','F') AS 'overdue' FROM cms_invoice AS inv WHERE cust_code = '".mysql_real_escape_string($custCode)."' AND cancelled = 'F' AND (DATE(invoice_date) >= '2020-09-20' AND DATE(invoice_date) <= CURRENT_DATE()) AND approved = 0 ORDER BY invoice_date DESC";

	$db->query($sql);
    
    $counter = $db->get_num_rows();

    echo json_encode(array("data"=>$counter,"sql"=>$sql));
}
?>