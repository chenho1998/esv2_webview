<?php 
require_once('./model/DB_class.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$settings                       = parse_ini_file('../config.ini',true);

if(isset($_GET['item_code'])){
    $client                     = $_GET['client'];
    $item_code                  = $_GET['item_code'];
    $salesperson                = $_GET['salesperson'];
    $cust_code                  = $_GET['cust_code'];

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
        /**
         * if cust_code is empty, get by item code and salesperson ID. Otherwise item code and cust code
         * */
        $sql = "";
        if(!empty($cust_code)){
            $sql = "SELECT d.*,s.created_date,s.cust_company_name FROM cms_stock_take s LEFT JOIN cms_stock_take_dtl d ON d.st_id = s.st_id AND s.cust_code = '".mysql_real_escape_string($cust_code)."' WHERE product_code = '".mysql_real_escape_string($item_code)."'";
        }else{
            $sql = "SELECT d.*,s.created_date,s.cust_company_name FROM cms_stock_take s LEFT JOIN cms_stock_take_dtl d ON d.st_id = s.st_id AND s.salesperson_id = '".mysql_real_escape_string($salesperson)."' WHERE product_code = '".mysql_real_escape_string($item_code)."'";
        }
        
        $db->query($sql);

        $final  = array();

        if($db->get_num_rows() != 0){

            while($result = $db->fetch_array()){
                $final[] = $result;
            }
        }
        echo json_encode($final);
    }
}
?>