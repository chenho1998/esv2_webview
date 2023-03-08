<?php
/**
 * Created by PhpStorm.
 * User: julfikar
 * Date: 2019-06-25
 * Time: 14:05
 */
ini_set('memory_limit', '-1');
require_once('./model/DB_class.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$settings                       = parse_ini_file('../config.ini',true);

if(isset($_GET['client'])){
    $client                     = $_GET['client'];

    $db                         = new DB();
    $db2                        = new DB();

    $cn_module                  = $settings['CN_LAST_DOC']['cn_module'];
    $distinct_module            = $settings['DISTINCT_LAST_DOC']['distinct_module'];
    $dateOf_last_doc            = $settings['DATE_OF_LAST_DOC'];
    $dateOf_last_doc            = isset($dateOf_last_doc[$client]) ? $dateOf_last_doc[$client] : '2021-10-01';

    $isDistinct                 = in_array($client,$distinct_module);
    $isCN                       = in_array($client,$cn_module);

    $limitCount                 = 5;
    if($isCN){
        $limitCount             = 100000; 
    }

    $settings                   = $settings[$client];

    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
    $db_name                    = $settings['db'];
    $db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

    $connection                 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);
    $connection2                = $db2->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);

    $custList                   = json_decode($_POST['custList'],true);

    $final                      = array();

    if (in_array($client,array('wbcoconut','wellb','sinaran','sunchuan','kahwah'))){
        for ($i=0; $i < count($custList); $i++) { 

            $cust_code = $custList[$i];
            
            $distinctInvoices = array();
    
            $query = "";
            $query = "SELECT dtl.*, inv.cust_code, inv.invoice_amount, inv.invoice_date FROM cms_invoice AS inv LEFT JOIN cms_invoice_details dtl ON dtl.invoice_code = inv.invoice_code 
            WHERE item_code IS NOT NULL AND cust_code = '{$cust_code}' AND item_code IS NOT NULL AND item_code <> '' AND invoice_date >= '{$dateOf_last_doc}' ORDER BY invoice_date DESC";
    
            $db->query($query);
    
            if($db->get_num_rows() != 0){
    
                while($result = $db->fetch_array()){
                    if($isDistinct){
                        $result['invoice_code'] = $cust_code.'*';
                        $unique = $result['cust_code'].$result['item_code'];
    
                        if(!in_array($unique,$distinctInvoices)){
                            $final[] = $result;
                            $distinctInvoices[] = $unique;
                        }
                    }else{
                        $final[] = $result;
                        if(count($distinctInvoices) > $limitCount){
                            break;
                        }
                        if(!in_array($result['invoice_code'],$distinctInvoices)){
                            $distinctInvoices[] = $result['invoice_code'];
                        }
                    }
                }
            }
        }
        echo json_encode($final);
        return;
    }

    $in_cust_code = "'".implode("','",$custList)."'";

    $query = "SELECT * FROM cms_invoice WHERE cust_code IN ({$in_cust_code}) AND cancelled = 'F' AND invoice_date >= '{$dateOf_last_doc}'";
    file_put_contents("getCustDocTmpl.log",$query);
    $db->query($query);

    $invoice_codes = array();
    $invoices = array();

    if($db->get_num_rows() != 0){
        while($result = $db->fetch_array()){
            $invoice_codes[] = str_replace("'","\'",$result['invoice_code']);
            $invoices[$result['invoice_code']] = $result;
        }
    }

    $invoice_codes = array_chunk($invoice_codes,500);
    for ($i = 0; $i < count($invoice_codes); $i++){
        $_invoice_code_chunk = $invoice_codes[$i];

        $in_invoice_code = "'".implode("','",$_invoice_code_chunk)."'";

        $query = "SELECT * FROM cms_invoice_details WHERE invoice_code IN ({$in_invoice_code}) AND active_status = 1 AND item_code IS NOT NULL AND item_code <> ''";
        
        $db->query($query);
        $distinctInvoices = array();

        if($db->get_num_rows() != 0){
            while($result = $db->fetch_array()){
                $invoice_code = $result['invoice_code'];
                if (isset($invoices[$invoice_code])){
                    $row = array_merge($result,$invoices[$invoice_code]);

                    if($isDistinct){
                        $row['invoice_code'] = $row['cust_code'].'*';
                        $unique = $row['cust_code'].$row['item_code'];

                        if(!in_array($unique,$distinctInvoices)){
                            $final[] = $row;
                            $distinctInvoices[] = $unique;
                        }
                    }else{
                        $final[] = $row;
                        /* if(count($distinctInvoices) > $limitCount){
                            break;
                        } */
                        if(!in_array($row['invoice_code'],$distinctInvoices)){
                            $distinctInvoices[] = $row['invoice_code'];
                        }
                    }
                }
            }
        }
    }

    echo json_encode($final);
}
?>