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

if(isset($_GET['cust_code'])){
    $client                     = $_GET['client'];
    $cust_code                  = $_GET['cust_code'];
    $offset                     = $_GET['offset'];
    $selforder                  = isset($_GET['selforder']) ? $_GET['selforder'] : 0;

    $db                         = new DB();

    $price_history_do           = $settings['PRICE_HISTORY_DO']['client'];
    $searchInDo                 = in_array($client,$price_history_do);

    $settings                   = $settings[$client];

    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
    $db_name                    = $settings['db'];
    $db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

    $connection                 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);

    $query                      = mysql_real_escape_string($query);
    $cust_code                  = mysql_real_escape_string($cust_code);

    if(!$connection){
        echo "0";//unauthorized access
    }else{
        if($client == 'bigbathxil'){
            $query = "select oi.*,o.order_date from cms_order as o left join cms_order_item oi on oi.order_id = o.order_id and oi.cancel_status = 0
            where cust_code = '{$cust_code}' and order_status > 0 and o.cancel_status = 0 order by order_date desc;";

            $invoice = array();
            $db->query($query);

            if($db->get_num_rows() != 0){

                while($result = $db->fetch_array()){
    
                    array_push($invoice,array(
                        "type"=>'invoice',
                        "lastSelling_id"=>$result['order_id'],
                        "lastSelling_product"=>$result['product_name'],
                        "lastSelling_price"=>f($result['unit_price']),
                        "lastSelling_quantity"=>intval($result['quantity']),
                        "lastSelling_uom"=>$result['unit_uom'],
                        "lastSelling_date"=>$result['order_date'],
                        "lastSelling_discount"=>0,
                        "lastSelling_total"=>f($result['sub_total']),
                        "lastSelling_product_code"=>$result['product_code']
                    ));
                }
            }

            echo json_encode(array("invoice_dtl" => $invoice,"cn_dtl"=>array()),JSON_UNESCAPED_UNICODE);
            die();
        }
        $limit_cond = " LIMIT {$offset},50;";
        if($client == 'abaro'){
            $limit_cond ='';
        }
        if($searchInDo){
            $query = "SELECT MAX(j.invoice_date) AS term,j.* FROM (
                SELECT inv.do_code as doc_id,inv.do_date AS invoice_date, dtl.* FROM cms_do AS inv LEFT JOIN cms_do_details dtl ON dtl.do_code = inv.do_code WHERE inv.cust_code = '{$cust_code}' AND item_code IS NOT NULL AND item_code <> '' ORDER BY inv.do_date DESC
            )j GROUP BY j.item_code ORDER BY term DESC LIMIT {$offset},50";
        }else{
            $query = "SELECT MAX(j.invoice_date) AS term,j.* FROM (
                SELECT inv.invoice_code as doc_id,inv.invoice_date, dtl.* FROM cms_invoice AS inv LEFT JOIN cms_invoice_details dtl ON dtl.invoice_code = inv.invoice_code WHERE inv.cust_code = '{$cust_code}' AND item_code IS NOT NULL AND item_code <> '' ORDER BY inv.invoice_date DESC
            )j GROUP BY j.item_code ORDER BY term DESC {$limit_cond}";


$query = "SELECT inv.invoice_code as doc_id,inv.invoice_date, dtl.* FROM cms_invoice AS inv LEFT JOIN cms_invoice_details dtl ON dtl.invoice_code = inv.invoice_code WHERE inv.cust_code = '{$cust_code}' AND item_code IS NOT NULL AND item_code <> '' ORDER BY inv.invoice_date DESC {$limit_cond}";
		

            file_put_contents('searchnm.log',$query);
        }

        $db->query($query);

        $invoice  = array();
        $item_codes = array();
        if($db->get_num_rows() != 0){

            while($result = $db->fetch_array()){

                array_push($invoice,array(
                    "type"=>'invoice',
                    "lastSelling_id"=>$result['doc_id'],
                    "lastSelling_product"=>$result['item_name'],
                    "lastSelling_price"=>f($result['item_price']),
                    "lastSelling_quantity"=>intval($result['quantity']),
                    "lastSelling_uom"=>$result['uom'],
                    "lastSelling_date"=>$result['invoice_date'],
                    "lastSelling_discount"=>$result['discount'] == null ? 0 : f($result['discount']),
                    "lastSelling_total"=>f($result['total_price']),
                    "lastSelling_product_code"=>$result['item_code']
                ));
            }
            $item_codes[] = mysql_real_escape_string($result['item_code']);
        }
        

        $credit  = array();

        $result = $db->query("SHOW TABLES LIKE 'cms_creditnote_details';");
        $cn_dtl_exists = (mysql_num_rows($result))?TRUE:FALSE;
        
        if($cn_dtl_exists){
            $query = "SELECT MAX(j.cn_date) AS term,j.* FROM (
                SELECT cn.cn_code as doc_id,cn.cn_date, dtl.* FROM cms_creditnote AS cn LEFT JOIN cms_creditnote_details dtl ON dtl.cn_code = cn.cn_code WHERE cn.cust_code = '{$cust_code}' AND item_code IS NOT NULL AND item_code <> '' ORDER BY cn.cn_date DESC
            )j GROUP BY j.item_code ORDER BY term DESC {$limit_cond}";

            $db->query($query);

            if($db->get_num_rows() != 0){

                while($result = $db->fetch_array()){
                    if($result['item_code']){
                        array_push($invoice,array(
                            "type"=>'credit',
                            "lastSelling_id"=>$result['doc_id'],
                            "lastSelling_product"=>$result['item_name'],
                            "lastSelling_price"=>f($result['item_price']),
                            "lastSelling_quantity"=>intval($result['quantity']),
                            "lastSelling_uom"=>$result['uom'],
                            "lastSelling_date"=>$result['cn_date'], 
                            "lastSelling_discount"=>$result['discount'] == null ? 0 : f($result['discount']),
                            "lastSelling_total"=>f($result['total_price']),
                            "lastSelling_product_code"=>$result['item_code']
                        ));
                    }
                }
            }
        }

        usort($invoice, function($a1, $a2) {
            $v1 = strtotime($a1['lastSelling_date']);
            $v2 = strtotime($a2['lastSelling_date']);
            return $v2 - $v1; // $v2 - $v1 to reverse direction
        });

        echo json_encode(array("invoice_dtl" => $invoice,"cn_dtl"=>$credit),JSON_UNESCAPED_UNICODE);
    }
}

function f($num){
    if(empty($num)){
        return 0;
    }
    //$num = number_format(floatval($num),2);
    return strval($num > 0 ? $num : 0);
}