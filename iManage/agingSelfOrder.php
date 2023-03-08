<?php
/**
 * Created by PhpStorm.
 * User: julfikar
 * Date: 2019-01-15
 * Time: 14:05
 */

////http://easysales.asia/julfitest/webview/iManage/invoice_details.php?client=toysworldtest&custCode=301-M004

require_once('./model/DB_class.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

define("INVOICE","Invoice");
define("CREDIT","Credit Note");
define("RECEIPT","Receipt");
define("DEBIT","Debit");
define("TYPE","type");
define("KEY","key");

date_default_timezone_set('Asia/Kuala_Lumpur');

$date = date('d/m/Y');

$statement_row['default'] = '
<tr>
    <td>
        <label class="row-text">@row_date</label>
    </td>
    <td>
        <label class="row-text">@row_reference</label>
    </td>
    <td>
        <label class="row-text">@row_description</label>
    </td>
    <td>
        <label class="row-text">@row_debit</label>
    </td>
    <td class="text-right row-text">
        <label>@row_credit</label>
    </td>
    <td class="text-right row-text">
        <label>@row_balance</label>
    </td>
</tr>
';
$statement_ko_row['default'] = '
<tr>
    <td>
        <label class="row-text knockoff-row">@row_date</label>
    </td>
    <td>
        <label class="row-text knockoff-row">@row_reference</label>
    </td>
    <td>
        <label class="row-text knockoff-row">@row_description</label>
    </td>
    <td>
        <label class="row-text knockoff-row">@row_debit</label>
    </td>
    <td class="text-right row-text knockoff-row">
        <label>@row_credit</label>
    </td>
    <td class="text-right row-text knockoff-row">
        <label>@row_balance</label>
    </td>
</tr>
';
$statement_cheque_row['default'] = '
<tr>
    <td>@cheque_date</td>
    <td>@cheque_no</td>
    <td>@or_no</td>
    <td class="text-right">@cheque_amount</td>
    <td class="text-right">@cheque_balance</td>
</tr>
';
$statement['default'] = '
<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Invoice</title>
      <style>
        p { padding: 0px; margin: 0px; }
        div.int-box { display: table-cell; vertical-align: middle; }
        .small-title { font-size: 13px; line-height: 13px; font-weight: 700; }
        .small-title-nobold { font-size: 13px; line-height: 13px; }
        .summary-box { width: 350px; height: 150px; border: 1px solid black; float: right; }
        .header-table { border-top: 2px solid black; border-bottom: 2px solid black; }
        .termbox { border-left: 2px solid black;border-bottom: 1px solid black;border-top: 1px solid black;padding-left:5px; }
        .datebox { border-right: 2px solid black;border-bottom: 1px solid black;border-top: 1px solid black;text-align:right;padding-right:5px; }
        .text-right { text-align: right; }
        .pad-left { padding-left: 10px; }
        .sub-box1 { height: 20%;border-bottom:1px solid black; width:100%; display: table; }
        .sub-box2 { height: 60%;border-bottom:1px solid black; }
        .sub-box3 { height: 20%; }
        .row-text { font-size: 17px; line-height: 17px; }
        .sub-box1-text { font-weight: 700; text-align: center; }
        .text-bold { font-weight: 700; }
        .post-dated-title { padding: 0px; margin: 0px; margin-top:20px; padding-bottom: 5px; }
        .outline { outline: thin solid; text-align: right; padding-right: 10px; }
        .no-padding { padding:0px; margin:0px; }
        .knockoff-row { color:grey; font-style:italic }
        .img-with-text {
            text-align: justify;
            width: 150px;
            float:right;
        }
        .img-with-text img {
            display: block;
            margin: 0 auto;
        }
      </style>
   </head>
   <body>
      <table width="100%">
         <col width="20%">
         <col width="80%">
         <tr>
            <td class="pad-left">  <img src="@logo_link" width="150px" /> </td>
            <td>
               <h2 class="no-padding">@client_name<p class="small-title-nobold">@client_sub_name</p></h2>
               <p>@client_addr</p>
            </td>
         </tr>
      </table>
      <div>
        <hr>
        <table width="100%">
            <col width="70%">
            <col width="30%">
            <tr>
                <td>
                    <p class="small-title">Billing Address</p>
                    <p>@debtor_name</p>
                    <p class="small-title-nobold">@debtor_addr</p>
                    <p>Tel: @debtor_tel</p>
                </td>
                <td>
                    <div class="summary-box">
                        <div class="sub-box1">
                            <div class="int-box">
                                <p class="sub-box1-text">Statement Of Account</p>
                            </div>
                        </div>
                        <div class="sub-box2">
                            <table width="100%">
                                <col width="50%">
                                <col width="50%">
                                <tr>
                                    <td>
                                        <p style="margin-top:2px">Total Debit(@debit_count)</p>
                                    </td>
                                    <td class="text-right">
                                        <p style="margin-top:2px">@debit_sum</p>
                                    </td>
                                </tr>
                            </table>
                            <table width="100%">
                                <col width="50%">
                                <col width="50%">
                                <tr>
                                    <td>
                                        <p style="margin-top:2px">Total Credit(@credit_count)</p>
                                    </td>
                                    <td class="text-right">
                                        <p style="margin-top:2px">@credit_sum</p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="sub-box3">
                            <table width="100%">
                                <col width="50%">
                                <col width="50%">
                                <tr>
                                    <td>
                                        <p style="margin-top:2px">Closing Balance</p>
                                    </td>
                                    <td class="text-right">
                                        <p style="margin-top:2px">@closing_balance</p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <table width="100%" class="header-table" cellspacing="0">
            <col width="35%">
            <col width="30%">
            <col width="10%">
            <col width="10%">
            <col width="15%">
            <tr>
                <td>
                    <label class="small-title-nobold">Attention</label>
                    <p class="text-bold">@attention</p>
                </td>
                <td>
                    <label class="small-title-nobold">Customer Account</label>
                    <p class="text-bold">@debtor_code</p>
                </td>
                <td>
                    <label class="small-title-nobold">Currency</label>
                    <p class="text-bold">RM</p>
                </td>
                <td class="termbox">
                    <label class="small-title-nobold">Terms</label>
                    <p class="text-bold">@debtor_term</p>
                </td>
                <td class="datebox">
                    <label class="small-title-nobold">Date</label>
                    <p class="text-bold">@to_date</p>
                </td>
            </tr>
        </table>
        <table width="100%" cellspacing="0">
            <col width="15%">
            <col width="15%">
            <col width="40%">
            <col width="10%">
            <col width="10%">
            <col width="10%">
            <tr>
                <td>
                    <label class="small-title">Date</label>
                </td>
                <td>
                    <label class="small-title">Reference</label>
                </td>
                <td>
                    <label class="small-title">Transaction Description</label>
                </td>
                <td>
                    <label class="small-title">Debit</label>
                </td>
                <td class="text-right">
                    <label class="small-title">Credit</label>
                </td>
                <td class="text-right">
                    <label class="small-title">Balance</label>
                </td>
            </tr>
            @document_row
        </table>
        <h4 class="post-dated-title">Post Dated Cheque Received</h4>
        <table width="100%" cellspacing="0">
            <col width="20%">
            <col width="20%">
            <col width="20%">
            <col width="20%">
            <col width="20%">
            <tr>
                <td class="small-title">Date</td>
                <td class="small-title">Cheque Number</td>
                <td class="small-title">OR No.</td>
                <td class="small-title text-right">Amount</td>
                <td class="small-title text-right">Balance</td>
            </tr>
            <tr>
                <td colspan="5" style="border-top: 1px solid black;"></td>
            </tr>
            @cheque_row
        </table>
        <hr/>
        <table width="100%">
            <col width="50%">
            <col width="50%">
            <tr>
                <td>
                    <p style="margin-top:2px">RINGGIT MALAYSIA: @in_word</p>
                </td>
                <td class="text-right">
                    <p style="margin-top:2px">RM @amount</p>
                </td>
            </tr>
        </table>
        <table width="100%" style="margin-top: 20px;">
            <col width="16%">
            <col width="16%">
            <col width="16%">
            <col width="16%">
            <col width="16%">
            <col width="16%">
            <tr class="outline">
                <td class="outline">Current Month</td>
                <td class="outline">1 Month</td>
                <td class="outline">2 Months</td>
                <td class="outline">3 Months</td>
                <td class="outline">4 Months</td>
                <td class="outline">5 Months & abv</td>
            </tr>
            <tr class="outline">
                <td class="outline">
                  @current_month
                </td>
                <td class="outline">
                  -
                </td>
                <td class="outline">
                  -
                </td>
                <td class="outline">
                  -
                </td>
                <td class="outline">
                  -
                </td>
                <td class="outline">
                  -
                </td>
            </tr>
        </table>
        <p style="margin-top: 5px;">
            We shall be grateful if you will let us have payment as soon as possible. Any discrepancy in this statement must be reported to us in writing within 10 days.
        </p>
        <div style="display:none;margin-bottom:20px;">
            <div class="img-with-text">
                <img style="float:right;" src="@signature" width="150px" height="100px"/>
                <p style="float:center">Date: '.$date.'</p>
            </div>
        </div>
      </div>
   </body>
</html>
';

$settings                       = parse_ini_file('../config.ini',true);

$custCode                       = '';
$client                         = '';
$connection                     = NULL;

if(isset($_GET['custCode']) && isset($_GET['client'])){

    $custCode                   = $_GET['custCode'];
    $dateFrom                   = $_GET['dateFrom'];
    $dateTo                     = $_GET['dateTo'];
    $client                     = $_GET['client'];
    $postDated                  = '0';//$_GET['postDated'];
    $isPayment                  = '0';//isset($_GET['payment']) ? $_GET['payment'] : 0;
    $isSelfOrder                = '1';//isset($_GET['selforder']) ? $_GET['selforder'] : 0;

    $users_SQLAccounting        = $settings['SQL']['sql_client'];
    $users_abswin               = $settings['ABSWIN']['abswin_client'];

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
        // do all data manipulation

        $main_html              = $statement['default'];
        $main_row               = $statement_row['default'];
        $main_ko_row            = $statement_ko_row['default'];
        $main_cheque_row        = $statement_cheque_row['default'];

        $is_SQLAccounting       = in_array($client,$users_SQLAccounting);
        $is_Abswin              = in_array($client,$users_abswin);

        $positive               = 0;
        $negative               = 0;

        $sql_positive           = 0;
        $sql_negative           = 0;

        $abswin_calc            = 0;

        $total_outstanding      = 0;

        $store                  = array();
        $final                  = array();

        $sql                    = "";

        $inv_date_condition     = "DATE(invoice_date) <= CURRENT_DATE()";
        $dn_date_condition      = "DATE(dn_date) <= CURRENT_DATE()";
        $cn_date_condition      = "DATE(cn_date) <= CURRENT_DATE()";
        $rcp_date_condition     = "DATE(receipt_date) <= CURRENT_DATE()";
        
        if($postDated == '1'){
            // yes
            $inv_date_condition     = "DATE(invoice_date) > CURRENT_DATE()";
            $dn_date_condition      = "DATE(dn_date) > CURRENT_DATE()";
            $cn_date_condition      = "DATE(cn_date) > CURRENT_DATE()";
            $rcp_date_condition     = "DATE(receipt_date) > CURRENT_DATE()";
        }

        $dbClone = clone $db;

        $dbClone->query("SELECT logo_Url from cms_setting");
        $logo_Url = '';
        while($row = $dbClone->fetch_array()){
            $logo_Url = $row['logo_Url'];
        }

        $rcpKnockOff = "SELECT 'r' AS type,doc_ko_ref, doc_amount AS 'CR',doc_amount as receipt_knockoff_amount,DATE(receipt_date) AS 'Date','OR' AS 'Type', receipt_code AS 'Ref',
        IF(CONCAT(IFNULL(receipt_desc,''),IFNULL(cheque_no,'')) = '','RECEIPT',CONCAT(IFNULL(receipt_desc,''),IF(cheque_no = '','','\nCHEQUE NO:'),IFNULL(cheque_no,''))) AS description,
        '' AS 'DR',(receipt_amount-doc_amount) AS 'OUTSTANDING' FROM cms_customer_ageing_ko AS ko LEFT JOIN cms_receipt r ON r.receipt_code = ko.doc_code
        WHERE doc_type = 'OR' AND cancelled = 'F' AND cust_code = '{$custCode}'";


        $cnKnockOff = "SELECT 'c' AS type,doc_ko_ref, doc_amount AS 'CR', doc_amount as cn_knockoff_amount,DATE(cn_date) AS 'Date','CN' AS 'Type',cn_code AS 'Ref','CREDITNOTE' AS description, '' AS 'DR',
        (cn_amount-doc_amount) AS 'OUTSTANDING' FROM cms_customer_ageing_ko AS ko LEFT JOIN cms_creditnote
            c ON c.cn_code = ko.doc_code WHERE doc_type = 'CN' AND cancelled = 'F' AND cust_code = '{$custCode}'";

        $rcpCnKnockOff = array();

        $koDocDtl = array();

        $dbClone->query($rcpKnockOff);

        if($dbClone->get_num_rows() != 0){
          while($rcpRes = $dbClone->fetch_array()){
            $ko_ref = $rcpRes['doc_ko_ref'];
            $rcpRes['knockoff_amount'] = $rcpRes['receipt_knockoff_amount'];
            $rcpCnKnockOff[$ko_ref][] = $rcpRes;

            if(isset($koDocDtl[$rcpRes['Ref']])){
                $existing = $koDocDtl[$rcpRes['Ref']];
                $existing[] = $ko_ref;
                $koDocDtl[$rcpRes['Ref']] = $existing;
            }else{
                $koDocDtl[$rcpRes['Ref']][] = $ko_ref;
            }
          }
        }

        $dbClone->query($cnKnockOff);

        if($dbClone->get_num_rows() != 0){
          while($cnRes = $dbClone->fetch_array()){
            $ko_ref = $cnRes['doc_ko_ref'];
            $cnRes['knockoff_amount'] = $cnRes['cn_knockoff_amount'];
            $rcpCnKnockOff[$ko_ref][] = $cnRes;

            if(isset($koDocDtl[$cnRes['Ref']])){
                $existing = $koDocDtl[$cnRes['Ref']];
                $existing[] = $ko_ref;
                $koDocDtl[$cnRes['Ref']] = $existing;
            }else{
                $koDocDtl[$cnRes['Ref']][] = $ko_ref;
            }
          }
        }

        $debtor_code = ''; $debtor_name = ''; $debtor_addr = ''; $debtor_term = ''; $debtor_tel = ''; $debtor_incharge = ''; 

        $cust_query             = "SELECT c.*, CONCAT(IFNULL(c.billing_address1,''),' ',IFNULL(c.billing_address2,''),' ',IFNULL(c.billing_address3,''),' ',IFNULL(c.billing_address4,''),' ',IFNULL(c.billing_city,''),' ',IFNULL(c.billing_state,'')) AS address FROM cms_customer AS c WHERE cust_code = '{$custCode}' LIMIT 1";

        $db->query($cust_query);

        if($db->get_num_rows() != 0){
            while($result = $db->fetch_array()){
                $debtor_code    = $result['cust_code'];
                $debtor_name    = $result['cust_company_name'];
                $debtor_addr    = $result['address'];
                $debtor_term    = $result['termcode'];
                $debtor_tel     = $result['cust_tel'];
                $debtor_incharge= $result['cust_incharge_person'];
            }
        }

        $debit_count = 0; $debit_sum = 0; $credit_count = 0; $credit_sum = 0;

        $client_name = '';
        $client_info = '';

        $db->query("SELECT * FROM cms_mobile_module WHERE module = 'app_client' OR module = 'app_client_info'");
        if($db->get_num_rows() != 0){
            while($result = $db->fetch_array()){
                if($result['module'] == 'app_client'){
                    $client_name = $result['status'];
                }
                if($result['module'] == 'app_client_info'){
                    $client_info = $result['status'];
                }
            }
        }
        if(!empty($client_info)){
            $client_info = json_decode($client_info,true);
        }

        $paymentKoData              = array();
        if($isPayment){
            $db->query("SELECT * FROM cms_payment WHERE cancel_status = 0 AND payment_status <> 2 AND knockoff_inv IS NOT NULL");
            if($db->get_num_rows() != 0){
                while($result = $db->fetch_array()){
                    $knockoffInvoices = $result['knockoff_inv'];
                    if($knockoffInvoices){
                        $knockoffInvoices = json_decode($knockoffInvoices,true);
                        for ($i=0,$size = count($knockoffInvoices); $i < $size; $i++) { 
                            $_inv = $knockoffInvoices[$i];
                            $ko_amount = $_inv['knockoff_amount'];
                            if(isset($paymentKoData[$_inv['doc_code']])){
                                $prev_amount = $paymentKoData[$_inv['doc_code']];
                                $paymentKoData[$_inv['doc_code']] = $prev_amount + $ko_amount;
                            }else{
                                $paymentKoData[$_inv['doc_code']] = $ko_amount;
                            }
                        }
                    }
                }
            }
        }

        $postDatedData              = array();


        $result                 = $db->query("SHOW COLUMNS FROM `cms_invoice` LIKE 'invoice_due_date'");
		$exists                 = (mysql_num_rows($result))?TRUE:FALSE;
		if($exists){
			$sql                = "SELECT inv.*, IF(DATE(invoice_due_date) <= CURRENT_DATE() AND outstanding_amount > 0,'T','F') AS 'overdue' FROM cms_invoice AS inv WHERE cust_code = '".mysql_real_escape_string($custCode)."' AND cancelled = 'F' AND {$inv_date_condition} ORDER BY invoice_date";
		}else{
			$sql                = "SELECT * FROM cms_invoice WHERE cust_code = '".mysql_real_escape_string($custCode)."'
                                    AND cancelled = 'F' AND {$inv_date_condition} ORDER BY invoice_date";
		}

        $db->query($sql);

        if($db->get_num_rows() != 0){
            while($result = $db->fetch_array()){
                $obj = array(
                    TYPE                => INVOICE,
                    "identifier"        => $result['invoice_id'],
                    "code"              => $result['invoice_code'],
                    "date"              => dateOnly($result['invoice_date']),
                    "datetime"          => $result['invoice_date'],
                    "amount"            => $result['invoice_amount'],
                    "balance"           => $is_SQLAccounting ? 
                                                (($result['outstanding_amount'] - $result['invoice_amount']) * -1) : 
                                            $result['outstanding_amount'],
                    "cancelled"         => $result['cancelled'],
                    "overdue"           => $result['overdue'],
                    "overdueDate"       => dateOnly($result['invoice_due_date']),
                    "checked"           => isset($result['approved']) ? $result['approved'] : 0,
                    KEY                 => monthAndYear($result['invoice_date'])
                );

                if($isPayment){
                    if(isset($paymentKoData[$result['invoice_code']])){
                        $ko = $paymentKoData[$result['invoice_code']];
                        $obj['is_knockedoff'] = $ko > 0;
                        $obj['knockoff_amount'] = $ko;
                        $obj['from_api'] = 1;
                    }
                }

                array_push($store,$obj);

                $sql_positive += floatval($result['invoice_amount']);
                $sql_negative += floatval($result['outstanding_amount']);

                $abswin_calc  += floatval($result['outstanding_amount']);

                $positive += floatval($result['outstanding_amount']);
            }
        }

        $sql                    = "SELECT * FROM cms_debitnote WHERE cust_code = '".mysql_real_escape_string($custCode)."'
                                    AND cancelled = 'F' AND {$dn_date_condition} ORDER BY dn_date";

        $db->query($sql);
        if($db->get_num_rows() != 0){
            while ($result = $db->fetch_array()){
                array_push($store,array(
                    TYPE                => DEBIT,
                    "identifier"        => $result['dn_id'],
                    "code"              => $result['dn_code'],
                    "date"              => dateOnly($result['dn_date']),
                    "datetime"          => $result['dn_date'],
                    "amount"            => $result['dn_amount'],
                    "balance"           => $result['outstanding_amount'],
                    "cancelled"         => $result['cancelled'],
                    "checked"           => isset($result['approved']) ? $result['approved'] : 0,
                    KEY                 => monthAndYear($result['dn_date'])
                ));

                $positive += floatval($result['outstanding_amount']);
            }
        }

        $sql                    = "SELECT * FROM cms_creditnote WHERE cust_code = '".mysql_real_escape_string($custCode)."' AND {$cn_date_condition} AND cancelled = 'F' ORDER BY cn_date";

        $db->query($sql);
        if($db->get_num_rows() != 0){
            while ($result = $db->fetch_array()){
                array_push($store,array(
                    TYPE                 => CREDIT,
                    "identifier"         => $result['cn_id'],
                    "code"               => $result['cn_code'],
                    "date"               => dateOnly($result['cn_date']),
                    "datetime"          => $result['cn_date'],
                    "balance"            => $result['cn_knockoff_amount'],
                    "amount"             => $result['cn_amount'],
                    "cancelled"          => $result['cancelled'],
                    "checked"           => isset($result['approved']) ? $result['approved'] : 0,
                    KEY                  => monthAndYear($result['cn_date'])
                ));
                $negative += floatval($result['cn_amount']) - floatval($result['cn_knockoff_amount']);

                $sql_negative += floatval($result['cn_amount']) - floatval($result['cn_knockoff_amount']);
            }
        }

        $sql                    = "SELECT * FROM cms_receipt WHERE cust_code = '".mysql_real_escape_string($custCode)."' AND cancelled = 'F' AND {$rcp_date_condition} ORDER BY receipt_date";
        
        $db->query($sql);
        if($db->get_num_rows() != 0){
            while ($result = $db->fetch_array()){
                array_push($store,array(
                    TYPE                 => RECEIPT,
                    "identifier"         => $result['receipt_id'],
                    "code"               => $result['receipt_code'],
                    "date"               => dateOnly($result['receipt_date']),
                    "datetime"          => $result['receipt_date'],
                    "balance"            => $result['receipt_knockoff_amount'],
                    "amount"             => $result['receipt_amount'],
                    "cancelled"          => $result['cancelled'],
                    "description"        => $result['receipt_desc'],
                    "cheque_no"          => $result['cheque_no'],
                    "checked"           => isset($result['approved']) ? $result['approved'] : 0,
                    KEY                  => monthAndYear($result['receipt_date'])
                ));
                if($postDated == 1){
                    array_push($postDatedData,array(
                        TYPE                 => RECEIPT,
                        "identifier"         => $result['receipt_id'],
                        "code"               => $result['receipt_code'],
                        "date"               => dateOnly($result['receipt_date']),
                        "balance"            => $result['receipt_knockoff_amount'],
                        "amount"             => $result['receipt_amount'],
                        "cancelled"          => $result['cancelled'],
                        "description"        => $result['receipt_desc'],
                        "cheque_no"          => $result['cheque_no'],
                        KEY                  => monthAndYear($result['receipt_date'])
                    ));
                }
                $negative += floatval($result['receipt_amount']) - floatval($result['receipt_knockoff_amount']);
                $sql_negative += floatval($result['receipt_amount']) - floatval($result['receipt_knockoff_amount']);
            }
        }

        $sql = "SELECT * FROM cms_receipt WHERE cust_code = '".mysql_real_escape_string($custCode)."' AND cancelled = 'F' AND receipt_date > '".$dateTo."' ORDER BY receipt_date";

        $db->query($sql);
        if($db->get_num_rows() != 0){
            while ($result = $db->fetch_array()){
                array_push($postDatedData,array(
                    TYPE                 => RECEIPT,
                    "identifier"         => $result['receipt_id'],
                    "code"               => $result['receipt_code'],
                    "date"               => dateOnly($result['receipt_date']),
                    "balance"            => $result['receipt_knockoff_amount'],
                    "amount"             => $result['receipt_amount'],
                    "cancelled"          => $result['cancelled'],
                    "description"        => $result['receipt_desc'],
                    "cheque_no"          => $result['cheque_no'],
                    "checked"           => isset($result['approved']) ? $result['approved'] : 0,
                    KEY                  => monthAndYear($result['receipt_date'])
                ));
            }
        }

        if(count($store) === 0){
            echo "0";
            return false;
        }

        $ageing                          = array();

        $store                           = group_by_key($store);

        $amount                          = 0;

        $carry_amount                    = 0;

        ksort($store);

        // here we go
        if($is_SQLAccounting){
            
            $total_outstanding               = ($sql_positive-$sql_negative);

            foreach ($store as $date=>$data){
                $temp                        = array();

                //one month calculation

                $invoice_amt                 = 0;
                $invoice_outstanding         = 0;

                $monthlyAmount               = 0;
                $balance                     = 0;

                $credit                      = 0;

                foreach ($data as $key=>$value){
                    // each note
                    $afterKnockOff           = 0;

                    if($value[TYPE] === INVOICE){
                        $monthlyAmount          += floatval($value['amount']);
                        $amount                 += floatval($value['amount']);
                        $invoice_amt            += floatval($value['amount']);
                        $outstanding            = (floatval($value['balance']) + floatval($value['amount']));
                        $invoice_outstanding    += $outstanding;

                        if($value['balance'] > 0){
                            $ageing[]                = $value;
                        }
                    }
                    if($value[TYPE] === CREDIT){
                        $credit                 += floatval($value['amount']);
                        $monthlyAmount          += floatval($value['amount']);
                        $amount                 += floatval($value['amount']);
                        $invoice_amt            += floatval($value['amount']);
                        $outstanding            = (floatval($value['amount']) - floatval($value['balance']));

                        $value['afterKnockOff']     = $outstanding;

                        $invoice_outstanding    -= $outstanding;

                        if($outstanding > 0){
                            $ageing[]           = $value;
                        }
                    }

                    if($value[TYPE] === RECEIPT){
                        $monthlyAmount          += floatval($value['amount']);
                        $amount                 += floatval($value['amount']);
                        $invoice_amt            += floatval($value['amount']);
                        $outstanding            = (floatval($value['amount']) - floatval($value['balance']));

                        $value['afterKnockOff']     = $outstanding;

                        $invoice_outstanding    -= $outstanding;

                        if($outstanding > 0){
                            $ageing[]           = $value;
                        }
                    }

                    array_push($temp,$value);
                }
                $balance                        = $invoice_amt - $invoice_outstanding;

                $carry_amount                   += $balance;

                array_push($final,array(
                        "month"                 =>name($date),
                        "key"                   =>$date,
                        "amount"                =>money($monthlyAmount),
                        "outstanding"           =>$isSelfOrder ? money($credit) : money($balance * -1),
                        "accm_amount"           =>money($amount),
                        "accm_outstanding"      =>money($carry_amount * -1),
                        "data"                  =>$temp
                    )
                );
            }
        } else if($is_Abswin) {
            $total_outstanding               = $abswin_calc;

            foreach ($store as $date=>$data){
                $temp                        = array();

                //one month calculation

                $invoice_amt                 = 0;
                $invoice_outstanding         = 0;

                $monthlyAmount               = 0;
                $balance                     = 0;

                foreach ($data as $key=>$value){
                    // each note
                    $afterKnockOff           = 0;

                    if($value[TYPE] === INVOICE){
                        $monthlyAmount          += floatval($value['amount']);
                        $amount                 += floatval($value['amount']);
                        $invoice_amt            += floatval($value['amount']);
                        $invoice_outstanding    += floatval($value['balance']);

                        if(floatval($value['balance']) > 0){
                            $ageing[]           = $value;
                        }
                    }
                    $value['afterKnockOff']      = floatval($value['balance']);

                    array_push($temp,$value);
                }
                $balance                        = $invoice_outstanding;

                $carry_amount                   += $balance;

                array_push($final,array(
                        "month"                 =>name($date),
                        "key"                   =>$date,
                        "amount"                =>money($monthlyAmount),
                        "outstanding"           =>money($balance),
                        "accm_amount"           =>money($amount),
                        "accm_outstanding"      =>money($carry_amount),
                        "data"                  =>$temp
                    )
                );
            }
        } else{
            $total_outstanding               = ($positive-$negative);

            foreach ($store as $date=>$data){

                $temp                        = array();

                //one month calculation

                $invoice                     = 0;
                $debit                       = 0;
                $credit                      = 0;
                $receipt                     = 0;

                $monthlyAmount               = 0;
                $balance                     = 0;

                foreach ($data as $key=>$value){

                    // each note

                    $afterKnockOff           = 0;

                    $shouldAdd = false;

                    if($value[TYPE] === INVOICE){
                        $monthlyAmount          += floatval($value['amount']);
                        $amount                 += floatval($value['amount']);
                        $invoice                += floatval($value['balance']);
                        if(floatval($value['balance']) > 0){
                            $shouldAdd = true;
                        }
                    }
                    if ($value[TYPE] === DEBIT){
                        $monthlyAmount          += floatval($value['amount']);
                        $amount                 += floatval($value['amount']);
                        $debit                  += floatval($value['balance']);
                        if(floatval($value['balance']) > 0){
                            $shouldAdd = true;
                        }
                    }
                    if($value[TYPE] === CREDIT){
                        $monthlyAmount          -= floatval($value['amount']);
                        $afterKnockOff          = floatval($value['amount']) - floatval($value['balance']);
                        $credit                 += $afterKnockOff;
                    }
                    if($value[TYPE] === RECEIPT){
                        $afterKnockOff          = floatval($value['amount']) - floatval($value['balance']);
                        $receipt                += $afterKnockOff;
                    }

                    $value['afterKnockOff']     = $afterKnockOff;

                    if($shouldAdd || $afterKnockOff != 0){
                        $ageing[] = $value;
                    }

                    array_push($temp,$value);

                }

                $balance                        = ($invoice+$debit) - ($credit+$receipt);

                $carry_amount                   += $balance;

                array_push($final,array(
                        "month"                 =>name($date),
                        "key"                   =>$date,
                        "amount"                =>money($monthlyAmount),
                        "outstanding"           =>money($balance),
                        "accm_amount"           =>money($amount),
                        "accm_outstanding"      =>money($carry_amount),
                        "data"                  =>$temp
                    )
                );
            }
        }

        $overDue = 0;
        $accm = 0;
        for($i = 0, $len = count($ageing); $i < $len; $i++){
            $value = $ageing[$i];
            if($value['overdue'] == 'T' && $value[TYPE] == INVOICE){
                $overDue += $value['balance'];
            }
            if($value[TYPE] == INVOICE || $value[TYPE] == DEBIT){
                $accm += $value['balance'];
            }else{
                $accm -= $value['afterKnockOff'];
            }
            $value['accm'] = $accm; //bring forward
            $ageing[$i] = $value;
        }

        $ageing = array_reverse($ageing);
        usort($ageing, 'date_compare');

        $monthlyAgeing = array();
        for ($i=0; $i < count($final); $i++) { 
            $obj = $final[$i];
            if(check_date_in_range($dateFrom,$dateTo,$obj['key'].'-01')){
                $monthlyAgeing[] = $obj['data'];
            }
        }

        $cloneMonthly = $monthlyAgeing[0];
        
        for ($i=0; $i < count($monthlyAgeing[0]); $i++) { 
            $main_doc = $monthlyAgeing[0][$i];
            if(isset($rcpCnKnockOff[$main_doc['code']])){
                $ko_data = $rcpCnKnockOff[$main_doc['code']];
                for ($j=0; $j < count($ko_data); $j++) { 
                    $ko_doc = $ko_data[$j];
                    $cloneMonthly = removeDataFromSetByCode($cloneMonthly,$ko_doc['Ref']);
                }
            }
        }
        
        $distinct_ko = array();
        $row_view = '';
        $accumulated_balance = 0;

        for ($i=0; $i < count($cloneMonthly); $i++) { 
            $main_doc = $cloneMonthly[$i];
            if($main_doc['type'] == INVOICE || $main_doc['type'] == DEBIT){
                $debit_count++;
                $debit_sum += $main_doc['amount'];
                $accumulated_balance += $main_doc['amount'];
                if($main_doc['balance'] == 0){
                    $credit_sum += $main_doc['amount'];
                }
            }
            if($main_doc['type'] == CREDIT || $main_doc['type'] == RECEIPT){
                $credit_count++;
            }
            if($main_doc['type'] == CREDIT){
                $accumulated_balance -= $main_doc['amount'];
                $credit_sum += $main_doc['balance'];
            }
            
            if($main_doc){
                $temp_row = str_replace(
                    array(
                        '@row_date',
                        '@row_reference',
                        '@row_description',
                        '@row_debit',
                        '@row_credit',
                        '@row_balance'
                    ),
                    array(
                        date("d/m/Y",strtotime($main_doc['date'])),
                        $main_doc['code'],
                        $main_doc['type'],
                        ($main_doc['type'] == INVOICE || $main_doc['type'] == DEBIT) ? money($main_doc['amount']) : '',
                        ($main_doc['type'] == INVOICE || $main_doc['type'] == DEBIT) ? '' : money($main_doc['amount']),
                        switchNegative($accumulated_balance)
                    ),
                    $main_row
                );
                $row_view .= $temp_row;
            }
            if(isset($rcpCnKnockOff[$main_doc['code']])){
                foreach ($koDocDtl as $koKey => $koArr) {
                    if(in_array($main_doc['code'], $koArr)){
                        $tmp = array();
                        for ($oo=0; $oo < count($koArr); $oo++) { 
                            $oj = $koArr[$oo];
                            if($main_doc['code'] != $oj){
                                $tmp[] = $oj;
                            }
                        }
                        $koDocDtl[$koKey] = $tmp;
                    }
                }
                $ko_data = $rcpCnKnockOff[$main_doc['code']];
                if($ko_data){
                    for ($j=0; $j < count($ko_data); $j++) { 
                        $ko_doc = $ko_data[$j];

                        // $credit_sum += $ko_doc['balance'];
                        $distinct_ko[] = $ko_doc['Ref'];

                        $actual_knockoff = $ko_doc['knockoff_amount'] > $main_doc['amount'] ? $main_doc['amount'] : $ko_doc['knockoff_amount'];
                        $accumulated_balance -= $actual_knockoff;
                        $credit_sum += $actual_knockoff;

                        $temp_row = str_replace(
                            array(
                                '@row_date',
                                '@row_reference',
                                '@row_description',
                                '@row_debit',
                                '@row_credit',
                                '@row_balance'
                            ),
                            array(
                                date("d/m/Y",strtotime($ko_doc['Date'])),
                                $ko_doc['Ref'],
                                $ko_doc['description'],
                                '',
                                money($actual_knockoff),
                                switchNegative($accumulated_balance)
                            ),
                            $main_ko_row
                        );
                        
                        $row_view .= $temp_row;
                    }
                }
            }
        }
        $tmpKoDocDtl = $koDocDtl;
        $koDocDtl = array();
        for ($kk=0; $kk < count($distinct_ko); $kk++) { 
            $ko = $distinct_ko[$kk];
            if(isset($tmpKoDocDtl[$ko]) && !empty($tmpKoDocDtl[$ko])){
                $koDocDtl[$ko] = $tmpKoDocDtl[$ko];
            }
        }

        foreach ($koDocDtl as $koCode => $docArr) {
            for ($i=0; $i < count($docArr); $i++) { 
                $doc_code = mysql_real_escape_string($docArr[$i]);

                $db->query("select ko.doc_amount as invoice_amount,invoice_date, invoice_code
                from cms_customer_ageing_ko as ko left join cms_invoice inv on inv.invoice_code = ko.doc_ko_ref
         where doc_ko_ref = '{$doc_code}' AND doc_code = '{$koCode}'");

                if($db->get_num_rows()){
                    while ($result = $db->fetch_array()){
                        $temp_row = str_replace(
                            array(
                                '@row_date',
                                '@row_reference',
                                '@row_description',
                                '@row_debit',
                                '@row_credit',
                                '@row_balance'
                            ),
                            array(
                                date("d/m/Y",strtotime($result['invoice_date'])),
                                $result['invoice_code'],
                                'Previously Knockedoff Invoice',
                                money($result['invoice_amount']),
                                '',
                                switchNegative($accumulated_balance + floatval($result['invoice_amount']))
                            ),
                            $main_ko_row
                        );
                        
                        $row_view .= $temp_row;
                        $credit_sum += $result['invoice_amount'];
                    }
                }
            }
        }

        $pm_balance = 0;
        $cheque_view = '';
        for ($i=0; $i < count($postDatedData); $i++) { 
            $pm = $postDatedData[$i];
            $pm_balance += $pm['amount'];
            $temp = str_replace(
                array(
                    '@cheque_date',
                    '@cheque_no',
                    '@or_no',
                    '@cheque_amount',
                    '@cheque_balance'
                ),
                array(
                    date("d/m/Y",strtotime($pm['date'])),
                    $pm['cheque_no'],
                    $pm['code'],
                    money($pm['amount']),
                    money($pm_balance)
                ),
                $main_cheque_row
            );
            $cheque_view .= $temp;
        }

        $distinct_ko = array_unique($distinct_ko);
        $credit_count += count($distinct_ko);

        $balance = $debit_sum - $credit_sum;
        $balance_in_word = moneyInWord($balance < 0 ? $balance * -1 : $balance);
        $balance = switchNegative($balance);

        $client_addr = $client_info['address']
                        .', '
                        .$client_info['city']
                        .($client_info['zipcode']?', ':'')
                        .$client_info['zipcode']
                        .($client_info['state'] ? ', ':'')
                        .$client_info['state'];

        $main_html = str_replace('@in_word',$balance_in_word,$main_html);
        $main_html = str_replace('@balance',$balance,$main_html);
        $main_html = str_replace('@amount',$balance,$main_html);
        $main_html = str_replace('@document_row',$row_view,$main_html);
        $main_html = str_replace('@debtor_term',$debtor_term,$main_html);
        $main_html = str_replace('@debtor_tel',$debtor_tel,$main_html);
        $main_html = str_replace('@statement_date',date("d/m/Y h:i A"),$main_html);
        $main_html = str_replace('@debtor_addr',$debtor_addr,$main_html);
        $main_html = str_replace('@debtor_name',$debtor_name,$main_html);
        $main_html = str_replace('@debtor_code',$debtor_code,$main_html);
        $main_html = str_replace('@attention',$debtor_incharge,$main_html);
        $main_html = str_replace('@client_website',$client_info['website'],$main_html);
        $main_html = str_replace('@client_phone',$client_info['phone'],$main_html);
        $main_html = str_replace('@client_email',$client_info['email'],$main_html);
        $main_html = str_replace('@client_addr',$client_addr,$main_html);
        $main_html = str_replace('@client_sub_name',$client_info['sub_name'],$main_html);
        $main_html = str_replace('@client_name',$client_name,$main_html);
        $main_html = str_replace('@to_date',date_format(date_create($dateTo),'d/m/Y'),$main_html);
        $main_html = str_replace('@debit_count',$debit_count,$main_html);
        $main_html = str_replace('@debit_sum',money($debit_sum),$main_html);
        $main_html = str_replace('@credit_count',$credit_count,$main_html);
        $main_html = str_replace('@credit_sum',money($credit_sum),$main_html);
        $main_html = str_replace('@closing_balance',$balance,$main_html);
        $main_html = str_replace('@cheque_row',$cheque_view,$main_html);
        $main_html = str_replace('@current_month',$balance,$main_html);

        $main_html = str_replace('@logo_link',$logo_Url,$main_html);

        echo json_encode(array('statement'=>base64_encode($main_html)));
    }
}else{
    echo "0";//unauthorized access
}
function switchNegative($balance){
    return $balance < 0 ? "(".money(($balance * -1)).")" : money($balance);
}
function removeDataFromSetByCode($data,$code){
    $clone = $data;
    for ($i=0; $i < count($data); $i++) { 
        $obj = $data[$i];
        if($obj['code'] == $code){
            unset($clone[$i]);
        }
    }
    return $clone;
}
function date_compare($a, $b){
    return strtotime($a['date']) - strtotime($b['date']);
}  
function nonnull($val,$type='string'){
    return empty($val) ? $type === 'string' ? "N/A" : 0 : $val;
}
function monthAndYear($date){
    return substr(dateOnly($date),0,-3);
}
function dateOnly($date){
    return empty($date) ? "" : explode(" ",$date)[0];
}
function group_by_key($array, $key = 'key') {
    $return                     = array();
    foreach($array as $val) {
        $return[$val[$key]][]   = $val;
    }
    return $return;
}
function name($date){
    $year                       = explode("-",$date)[0];
    $month                      = explode("-",$date)[1];
    $month                      = intval($month);
    $month_names                = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    return $month_names[$month-1].", ".$year;
}
function money($amount, $force = false){
    $amount = floatval($amount);
    if($force && !$amount) return 'RM0.00';
    if(!$amount) return 0;
    return number_format($amount, 2, '.', ',');
}
function check_date_in_range($start_date, $end_date, $date_from_user){
  $start_ts = strtotime($start_date);
  $end_ts = strtotime($end_date);
  $user_ts = strtotime($date_from_user);
  
  return (($user_ts >= $start_ts) && ($user_ts <= $end_ts));
}
function moneyInWord($number){
    $decimal = round($number - ($no = floor($number)), 2) * 100;
    $hundred = null;
    $digits_length = strlen($no);
    $i = 0;
    $str = array();
    $words = array(0 => '', 1 => 'one', 2 => 'two',
        3 => 'three', 4 => 'four', 5 => 'five', 6 => 'six',
        7 => 'seven', 8 => 'eight', 9 => 'nine',
        10 => 'ten', 11 => 'eleven', 12 => 'twelve',
        13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen',
        16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen',
        19 => 'nineteen', 20 => 'twenty', 30 => 'thirty',
        40 => 'forty', 50 => 'fifty', 60 => 'sixty',
        70 => 'seventy', 80 => 'eighty', 90 => 'ninety');
    $digits = array('', 'hundred','thousand','lakh', 'crore');

    while( $i < $digits_length ) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += $divider == 10 ? 1 : 2;
        if ($number) {
            $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
            $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
            $str [] = ($number < 21) ? $words[$number].' '. $digits[$counter]. $plural.' '.$hundred:$words[floor($number / 10) * 10].' '.$words[$number % 10]. ' '.$digits[$counter].$plural.' '.$hundred;
        } else $str[] = null;
    }

    $ringgit = implode('', array_reverse($str));
    $cents = '';

    if ($decimal) {
        $cents = 'and ';
        $decimal_length = strlen($decimal);

        if ($decimal_length == 2) {
            if ($decimal >= 20) {
                $dc = $decimal % 10;
                $td = $decimal - $dc;
                $ps = ($dc == 0) ? '' : '-' . $words[$dc];

                $cents .= $words[$td] . $ps;
            } else {
                $cents .= $words[$decimal];
            }
        } else {
            $cents .= $words[$decimal % 10];
        }

        $cents .= ' CENTS ONLY';
    }

    return strtoupper(($ringgit ? 'RINGGIT MALAYSIA '.$ringgit : '') . $cents) ;
}
?>

