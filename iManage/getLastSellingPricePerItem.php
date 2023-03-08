<?php
/**
 * Created by PhpStorm.
 * User: julfikar
 * Date: 2019-06-25
 * Time: 14:05
 */
require_once('./model/DB_class.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$settings                       = parse_ini_file('../config.ini',true);

if(isset($_GET['item_code'])){
    $client                     = $_GET['client'];
    $item_code                  = $_GET['item_code'];
    $offset                     = $_GET['offset'];
    $salesperson                = $_GET['salesperson'];

    $price_history_do           = $settings['PRICE_HISTORY_DO']['client'];
    $searchInDo                 = in_array($client,$price_history_do);

    $everyone_can_see           = $settings['PRICE_HISTORY']['equal_rights'];
    $everyone_can_see           = in_array($client,$everyone_can_see);

    $db                         = new DB();

    $settings                   = $settings[$client];

    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
    $db_name                    = $settings['db'];
    $db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

    $connection                 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);

    $query                      = mysql_real_escape_string($query);
    $item_code                  = mysql_real_escape_string($item_code);

    if(!$connection){
        echo "0";//unauthorized access
    }else{
        $sp_group_settings = "";
        $result = $db->query("SELECT status FROM cms_mobile_module WHERE module = 'app_pricehistory_grp'");
        if($db->get_num_rows() != 0){
            while($row = $db->fetch_array()){
                $sp_group_settings = $row['status'];
            }
        }
        if($sp_group_settings){
            $sp_group_settings = json_decode($sp_group_settings,true);
        }
        
        $isLeader = -1;
        if(!empty($sp_group_settings)){
            $isLeader = $sp_group_settings[$salesperson] == 1;
        }//HO3102X

        $spCustomers = array();

        $spCond = " WHERE sp.salesperson_id = '{$salesperson}'";

        if($everyone_can_see){
            $spCond = "";
        }
        if($isLeader == false){
            $spCond = " WHERE sp.salesperson_id = '{$salesperson}'";
            $grp_members = $sp_group_settings[$salesperson];
            if($grp_members && is_array($grp_members)){
                $spCond = "WHERE sp.salesperson_id IN (".implode(',',$grp_members).");";
            }
        }

        $query = "SELECT cust_code FROM cms_customer AS c LEFT JOIN cms_customer_salesperson sp ON sp.customer_id = c.cust_id {$spCond}";
        
        $db->query($query);

        while($result = $db->fetch_array()){
            $spCustomers[] = $result['cust_code'];
        }

        $condition = "";
        $result = $db->query("SHOW COLUMNS FROM `cms_invoice_details` LIKE 'active_status'");
        $exists = (mysql_num_rows($result)) ? TRUE : FALSE;
        if ($exists) {
            $condition = " AND dtl.active_status = 1 ";
        }

        $c_cond_cust = "";$c_cond = "";
        if($spCustomers){
            $c_cond_cust = implode("','",$spCustomers);
            $c_cond = " AND inv.cust_code IN ('".$c_cond_cust."')";
        }

        $query = "SELECT inv.invoice_code as doc_id,inv.invoice_date,inv.cust_code,cust.cust_company_name,dtl.* FROM cms_invoice_details AS dtl
        LEFT JOIN cms_invoice inv ON dtl.invoice_code = inv.invoice_code 
        LEFT JOIN cms_customer cust ON cust.cust_code = inv.cust_code
        WHERE item_code = '{$item_code}' {$condition} AND invoice_date > (current_date()-interval 1 year) ORDER BY inv.invoice_date DESC;";

        if($searchInDo){
            $cond = '=';
            if($client == 'gaido'){
                $item_code = str_replace(' ','%',$item_code);
                $cond = 'like';
            }
            $query = "SELECT inv.do_code as doc_id,inv.do_date as invoice_date ,inv.cust_code,cust.cust_company_name,dtl.* FROM cms_do_details AS dtl
            LEFT JOIN cms_do inv ON dtl.do_code = inv.do_code 
            LEFT JOIN cms_customer cust ON cust.cust_code = inv.cust_code
            WHERE item_code {$cond} '{$item_code}' AND do_date > (current_date()-interval 1 year) ORDER BY inv.do_date DESC;";
        }

        $db->query($query);

        $invoice  = array();

        if($db->get_num_rows() != 0){

            while($result = $db->fetch_array()){

                if(in_array($result['cust_code'],$spCustomers)){
                    array_push($invoice,array(
                        "lastSelling_id"=>$result['doc_id'],
                        "lastSelling_cust_code"=>$result['cust_code'],
                        "lastSelling_cust"=>$result['cust_company_name'],
                        "lastSelling_product"=>$result['item_name'],
                        "lastSelling_price"=>f($result['item_price']),
                        "lastSelling_quantity"=>floatval($result['quantity']),
                        "lastSelling_uom"=>$result['uom'],
                        "lastSelling_date"=>$result['invoice_date'],
                        "lastSelling_discount"=>$result['discount'] == null ? 0 : f($result['discount']),
                        "lastSelling_total"=>f($result['total_price']),
                        "lastSelling_product_code"=>$result['item_code']
                    ));
                }
            }
        }

        echo json_encode(array("price_dtl" => $invoice),JSON_UNESCAPED_UNICODE);
    }
}

function f($num){
    if(empty($num)){
        return 0;
    }
    //$num = number_format(floatval($num),2);
    return strval($num > 0 ? $num : 0);
}