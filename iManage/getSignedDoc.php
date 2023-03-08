<?php
require_once('./model/MySQL.php');
date_default_timezone_set('Asia/Kuala_Lumpur');

if(isset($_GET['client']) && isset($_GET['cust_code']) && isset($_GET['username'])){
    $client = MySQL::sanitize($_GET['client']);
    $cust_code = MySQL::sanitize($_GET['cust_code']);
    $username = MySQL::sanitize($_GET['username']);

    $settings = parse_ini_file('../config.ini',true);
    $config = $settings[$client];

    $db = new MySQL($config);
    $signedInvoice = $db->Execute("SELECT * FROM cms_invoice AS iv LEFT JOIN cms_salesperson_uploads AS up ON iv.invoice_code = up.upload_bind_id WHERE DATEDIFF(current_date(),approved_at) < 31 AND cust_code = '{$cust_code}' AND approver = '{$username}' GROUP BY invoice_code ORDER BY iv.invoice_date DESC");

    $signedCredit = $db->Execute("SELECT * FROM cms_creditnote AS cn LEFT JOIN cms_salesperson_uploads AS up ON cn.cn_code = up.upload_bind_id WHERE DATEDIFF(current_date(),approved_at) < 31 AND cust_code = '{$cust_code}' AND approver = '{$username}' GROUP BY cn_code ORDER BY cn.cn_date DESC");

    $signedDebit = $db->Execute("SELECT * FROM cms_debitnote AS dn LEFT JOIN cms_salesperson_uploads AS up ON dn.dn_code = up.upload_bind_id WHERE DATEDIFF(current_date(),approved_at) < 31 AND cust_code = '{$cust_code}' AND approver = '{$username}' GROUP BY dn_code  ORDER BY dn.dn_date DESC");
    
    $signedReceipt = $db->Execute("SELECT * FROM cms_receipt AS rcp LEFT JOIN cms_salesperson_uploads AS up ON rcp.receipt_code = up.upload_bind_id WHERE DATEDIFF(current_date(),approved_at) < 31 AND cust_code = '{$cust_code}' AND approver = '{$username}' GROUP BY receipt_code ORDER BY rcp.receipt_date DESC;");

    echo json_encode(array(
        "invoice"=>removeReactKeys($signedInvoice),
        "credit"=>removeReactKeys($signedCredit),
        "debit"=>removeReactKeys($signedDebit),
        "receipt"=>removeReactKeys($signedReceipt)
    ));
}

function removeReactKeys($arr){
    for ($i=0; $i < count($arr); $i++) { 
        $obj = $arr[$i];
        foreach($obj as $key=>$value){
            if(is_numeric($key)){
                unset($obj[$key]);
            }
        }
        if($obj){
            $arr[$i] = $obj;
        }
    }
    return $arr;
}
?>