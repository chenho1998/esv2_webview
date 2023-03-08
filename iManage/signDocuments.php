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

if(isset($_GET['client'])){
    $client                     = $_GET['client'];

    $db                         = new DB();

    $settings                   = $settings[$client];

    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
    $db_name                    = $settings['db'];
    $db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

    $connection                 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);

    $docList                    = json_decode($_POST['docList'],true);

    $username                   = $_POST['approver'];

    for ($i=0; $i < count($docList); $i++) { 
        $obj = $docList[$i];
        $doc_code = mysql_real_escape_string($obj['code']);
        $type = strtolower($obj['type']);
        $sqlQuery = '';
        switch ($type) {
            case 'invoice':{
                $sqlQuery = "UPDATE cms_invoice SET approved = 1, approver = '{$username}', approved_at = NOW() WHERE invoice_code = '{$doc_code}'";
                break;
            }
            case 'credit':{
                $sqlQuery = "UPDATE cms_creditnote SET approved = 1, approver = '{$username}', approved_at = NOW() WHERE cn_code = '{$doc_code}'";
                break;
            }
            case 'debit':{
                $sqlQuery = "UPDATE cms_debitnote SET approved = 1, approver = '{$username}', approved_at = NOW() WHERE dn_code = '{$doc_code}'";
                break;
            }
            case 'receipt':{
                $sqlQuery = "UPDATE cms_receipt SET approved = 1, approver = '{$username}', approved_at = NOW() WHERE receipt_code = '{$doc_code}'";
                break;
            }
            default:
                break;
        }
        if($db->query($sqlQuery)){
            echo json_encode(array(
                "updated"=>"1"
            ));
        }else{
            echo json_encode(array(
                "updated"=>"0"
            ));
        }
    }
}
?>