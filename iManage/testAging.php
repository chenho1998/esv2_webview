<?php
require_once('./model/DB_class.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
//easysales.asia/esv2/webview/iManage/agingReport.php?client=abaro&custCode=300-E002&dateFrom=2018-02-01&dateTo=2019-09-04
date_default_timezone_set('Asia/Kuala_Lumpur');

$statement['zero']  = '
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3pro.css">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <style> 
          p{ line-height: 0.2; } 
          h2,h3,h4{ line-height: 0.4; margin:0px; }
          hr{ height:0px; }
          * {
            box-sizing: border-box;
          }
          .column {
            float: left;
            width: 50%;
            padding: 10px;
          }
          .row:after {
            content: "";
            display: table;
            clear: both;
          }
          table {
            border-collapse: collapse;
          }
          th {
            border-top: 1px solid black;
            border-bottom: 1px solid black;
          }
          td{
              height:30px;
              font-size:14px;
          }
          .Row {
              display: table;
              width: 100%; /*Optional*/
              table-layout: fixed; /*Optional*/
              border-spacing: 1px; /*Optional*/
          }
          .Column {
              display: table-cell;
              border:1px solid grey;
          }
        </style>
    </head>
    <body style="padding:10px">
      <div style="text-align:center;">
          <h3>
            @client_name
          </h3>
          <p style="font-size:15px;line-height:10px;">
             @client_sub_name
          </p>
          <p style="font-size:15px;line-height:10px;">
            @client_addr
          </p>
          <p style="font-size:15px;line-height:10px;">
            <strong>Phone:</strong> 
                @client_phone
            <strong>Email:</strong> 
                @client_email
            <strong>Website:</strong> 
                @client_website
          </p>
          <hr>
          <h3 size="pixels">
            DEBTOR STATEMENT
          </h3>
      </div>
      <div class="row">
        <div class="column">
          <table style="width:100%">
            <tr>
              <td align="right">
                
              </td>
              <td align="left">
                <strong>@debtor_code</strong>
              </td>
            </tr>
            <tr>
              <td align="right">
                <strong> </strong>
              </td>
              <td align="left" valing="center">
                  @debtor_name
              </td>
            </tr>
            <tr>
              <td align="right">
                <strong> </strong>
              </td>
              <td align="left" valing="center">
                  @debtor_addr
              </td>
            </tr>
          </table>
        </div>
        <div class="column">
          <table style="width:100%">
            <tr>
              <td align="right">
                <strong>Statement Date: </strong>
              </td>
              <td align="left">
                  @statement_date
              </td>
            </tr>
            <tr>
              <td align="right">
                <strong>Term: </strong>
              </td>
              <td align="left">
                  @debtor_term
              </td>
            </tr>
          </table>
        </div>
      </div>
      <table style="width:100%;border-bottom:1px solid black;">
        <col width="15%">
        <col width="5%">
        <col width="15%">
        <col width="20%">
        <col width="15%">
        <col width="15%">
        <col width="15%">
        <tr>
          <th align="center">Date</th>
          <th align="center">Type</th>
          <th align="center">Ref.</th>
          <th align="center">Description</th>
          <th align="center">DR</th>
          <th align="center">CR</th>
          <th align="right" style="padding-right:10px">Balance</th>
        </tr>
        <tr>
            <td align="left"></td>
            <td align="left"></td>
            <td align="left"></td>
            <td align="left"></td>
            <td align="left"></td>
            <td align="left" style="font-style:italic;">
                @balance_in_word
            </td>
            <td align="right" style="padding-right:10px">
                <strong>
                  TOTAL: @balance
                </strong>
            </td>
        </tr>
      </table>
    </body>
</html>
';

$statement['default']  = '
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3pro.css">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <style> 
          .outline { outline: thin solid; text-align: right; padding-right: 10px; }
          p{ line-height: 0.1; } 
          h2,h3,h4{ line-height: 0.4; margin:0px; }
          hr{ height:0px; }
          * {
            box-sizing: border-box;
          }
          .column {
            float: left;
            width: 50%;
            padding: 10px;
          }
          .row:after {
            content: "";
            display: table;
            clear: both;
          }
          table {
            border-collapse: collapse;
          }
          th {
            border-top: 1px solid black;
            border-bottom: 1px solid black;
          }
          td{
              height:14px;
              font-size:14px;
          }
          .postdated{
            border:0px;font-size:12px;
            border-bottom:2px solid black;
          }
          .Row {
              display: table;
              width: 100%; /*Optional*/
              border-spacing: 1px; /*Optional*/
          }
          .Column {
              display: table-cell;
              border:1px solid grey;
              min-width:100px;
              word-wrap: break-word;
          }
          hr.monthlyDivider { 
            margin: 0em;
            border-width: 2px;
          } 
        </style>
    </head>
    <body style="padding:10px">
      <div style="text-align:center;">
          <h3>
            @client_name
          </h3>
          <p style="font-size:15px;line-height:10px;">
             @client_sub_name
          </p>
          <p style="font-size:15px;line-height:17px;">
            @client_addr
          </p>
          <p style="font-size:15px;line-height:18px;">
            <strong>Phone:</strong> 
                @client_phone
            <strong>Email:</strong> 
                @client_email
            <strong>Website:</strong> 
                @client_website
          </p>
          <hr>
          <h3 size="pixels">
            DEBTOR STATEMENT
          </h3>
      </div>
      <div class="row">
        <div class="column">
          <table style="width:100%">
            <tr>
              <td align="right">
                <strong></strong>
              </td>
              <td align="left">
                <strong>@debtor_code</strong>
              </td>
            </tr>
            <tr>
              <td align="right">
                <strong> </strong>
              </td>
              <td align="left" valing="center">
                  @debtor_name
              </td>
            </tr>
            <tr>
              <td align="right">
                <strong> </strong>
              </td>
              <td align="left" valing="center">
                  @debtor_addr
              </td>
            </tr>
          </table>
        </div>
        <div class="column">
          <table style="width:100%">
            <tr>
              <td align="right">
                <strong>Statement Date: </strong>
              </td>
              <td align="left">
                  @statement_date
              </td>
            </tr>
            <tr>
              <td align="right">
                <strong>Term: </strong>
              </td>
              <td align="left">
                  @debtor_term
              </td>
            </tr>
          </table>
        </div>
      </div>
      <table style="width:100%;border-bottom:1px solid black;">
        <col width="15%">
        <col width="5%">
        <col width="15%">
        <col width="20%">
        <col width="15%">
        <col width="15%">
        <col width="15%">
        <tr>
          <th align="center">Date</th>
          <th align="center">Type</th>
          <th align="center">Ref.</th>
          <th align="center">Description</th>
          <th align="center">DR</th>
          <th align="center">CR</th>
          <th align="right" style="padding-right:10px">Balance</th>
        </tr>
        
        @document_row

      </table>

      <div style="margin-top:10px">
        <table style="width:100%">
            <tr>
                <td align="left" style="width:70%;font-style:italic">
                    @balance_in_word
                </td>
                <td align="right" style="padding-right:10px">
                    <strong>
                        TOTAL: @balance
                    </strong>
                </td>
            </tr>
        </table>
      </div>

      @postdated
    
      <div style="margin-top:50px">
        @monthlyAging
      </div>
      
    </body>
</html>
';

$statement['monthly'] = '
<table width="100%">
    <col width="12.2%">
    <col width="12.2%">
    <col width="12.2%">
    <col width="12.2%">
    <col width="12.2%">
    <col width="12.2%">
    <col width="12.2%">
    <col width="12.2%">
    <tr class="outline">
        <td class="outline">Total</td>
        <td class="outline">1 Month</td>
        <td class="outline">2 Months</td>
        <td class="outline">3 Months</td>
        <td class="outline">4 Months</td>
        <td class="outline">5 Months</td>
        <td class="outline">6 Months</td>
        <td class="outline">7 Months & Abv</td>
    </tr>
    <tr class="outline">
        <td class="outline">
          @current_total
        </td>
        <td class="outline">
          @0_month
        </td>
        <td class="outline">
          @1_month
        </td>
        <td class="outline">
          @2_month
        </td>
        <td class="outline">
          @3_month
        </td>
        <td class="outline">
          @4_month
        </td>
        <td class="outline">
          @5_month
        </td>
        <td class="outline">
          @rest_month
        </td>
    </tr>
</table>';

$settings                       = parse_ini_file('../config.ini',true);

$custCode                       = '';
$client                         = '';
$connection                     = NULL;

if(isset($_GET['custCode']) && isset($_GET['client'])){
    $custCode                   = $_GET['custCode'];
    $client                     = $_GET['client'];
    $dateFrom                   = $_GET['dateFrom'];
    $dateTo                     = $_GET['dateTo'];

    $html                       = $statement['default'];
    if(isset($statement[$client])){
      $html = $statement[$client];
    }
    $doc_row                    = '';
    $doc_ko_row                 = '';
    if($statement_row[$client]){
      $doc_row                  = $statement_row[$client];
      $doc_ko_row               = $statement_ko_row[$client];
    }
    $cheque_row                 = '';
    if($statement_cheque_row[$client]){
      $cheque_row               = $statement_cheque_row[$client];
    }

    $users_SQLAccounting        = $settings['SQL']['sql_client'];
    $users_abswin               = $settings['ABSWIN']['abswin_client'];
    $users_Greenplus            = $settings['GREENPLUS']['greenplus_client'];
    $users_SmartSQL             = $settings['SMARTSQL']['smartsql_client'];

    $db                         = new DB();

    $bad_calc_customer          = $settings['Bad_Customer']['bad_calc_module'];

    $bad_calc_customer          = in_array($client,$bad_calc_customer);

    $settings                   = $settings[$client];

    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
    $db_name                    = $settings['db'];
    $db_host                    = $settings['host'] ? $settings['host'] : 'easysales.asia';

    $connection                 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);

    $table_query = 'CREATE TABLE `cms_customer_ageing_ko` (
        `id` int(10) NOT NULL AUTO_INCREMENT,
        `doc_code` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
        `doc_ko_ref` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
        `doc_ko_type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
        `doc_amount` double DEFAULT NULL,
        `active_status` int(10) NOT NULL DEFAULT 1,
        `doc_type` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
        `updated_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unq` (`doc_code`,`doc_ko_ref`,`doc_ko_type`)
      ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
    
    if(!$connection){
        echo "0";//unauthorized access
    }else{

        $knockOffJoinArr = array();

        $checkTable = "SELECT count(*) as isexists FROM information_schema.TABLES WHERE TABLE_NAME = 'cms_customer_ageing_ko' 
        AND TABLE_SCHEMA in (SELECT DATABASE());";
        $db->query($checkTable);
        if($db->get_num_rows() == 0){
          $db->query($table_query);
        }else{
          while($result = $db->fetch_array()){
            $isExists = $result['isexists'];
            $needToCreate = intval($isExists) <= 0;
          }
          if($needToCreate){
            $db->query($table_query);
          }else{
            $db->query("ALTER TABLE `cms_customer_ageing_ko` CHANGE `from_doc` `doc_type` VARCHAR(100) CHARSET utf8 COLLATE utf8_unicode_ci NULL;");
          }
        }

        $dbClone = clone $db;

        $monthlyAging           = array();

        $is_SQLAccounting       = in_array($client,$users_SQLAccounting);
        $is_Abswin              = in_array($client,$users_abswin);
        $is_Greenplus           = in_array($client,$users_Greenplus);
        $is_SmartSQL            = in_array($client,$users_SmartSQL);

        $is_AutoCount = $is_Abswin === false && $is_Greenplus === false && $is_SQLAccounting === false && $is_SmartSQL === false;

        $docMerged              = array();

        $custCode               = mysql_real_escape_string($custCode);
        $dateFrom               = mysql_real_escape_string($dateFrom);
        $dateTo                 = mysql_real_escape_string($dateTo);


        $rcpKnockOff = "SELECT 'r' AS type, doc_ko_ref, receipt_amount AS 'CR', receipt_knockoff_amount,DATE(receipt_date) AS 'Date','Credit' AS 'Type', receipt_code AS 'Ref', IF(CONCAT(IFNULL(receipt_desc,''),IFNULL(cheque_no,'')) = '','RECEIPT',CONCAT(IFNULL(receipt_desc,''),IF(cheque_no = '','','\nCHEQUE NO:'),IFNULL(cheque_no,''))) AS description, '' AS 'DR',(receipt_amount-receipt_knockoff_amount) AS 'OUTSTANDING', ko.doc_amount FROM cms_customer_ageing_ko AS ko LEFT JOIN cms_receipt r ON r.receipt_code = ko.doc_code WHERE doc_type = 'OR' AND cancelled = 'F' AND cust_code = '{$custCode}' AND doc_date >= '{$dateFrom}'";

        $cnKnockOff = "SELECT 'c' AS type, doc_ko_ref, cn_amount AS 'CR', cn_knockoff_amount,DATE(cn_date) AS 'Date','Credit' AS 'Type',cn_code AS 'Ref','CREDITNOTE' AS description, '' AS 'DR', (cn_amount-cn_knockoff_amount) AS 'OUTSTANDING', ko.doc_amount FROM cms_customer_ageing_ko AS ko LEFT JOIN cms_creditnote c ON c.cn_code = ko.doc_code WHERE doc_type = 'CN' AND cancelled = 'F' AND cust_code = '{$custCode}' AND doc_date >= '{$dateFrom}'";

        $dnKnockOff = "SELECT 'c' AS type, doc_ko_ref, dn_amount AS 'CR', outstanding_amount,DATE(dn_date) AS 'Date','Debit' AS 'Type',dn_code AS 'Ref','CREDITNOTE' AS description, '' AS 'DR', (dn_amount-outstanding_amount) AS 'OUTSTANDING', ko.doc_amount FROM cms_customer_ageing_ko AS ko LEFT JOIN cms_debitnote c ON c.dn_code = ko.doc_ko_ref WHERE doc_type = 'DN' AND cancelled = 'F' AND cust_code = '{$custCode}' AND doc_date >= '{$dateFrom}'";


        $cfKnockOff =  "SELECT 'cf' AS type, doc_ko_ref, cf_amount AS 'CR', cf_knockoff_amount,DATE(cf_date) AS 'Date','Debit' AS 'Type', cf_code AS 'Ref', 'CUSTOMER REFUND 'AS description, '' AS 'DR',(cf_amount-cf_knockoff_amount) AS 'OUTSTANDING', ko.doc_amount FROM cms_customer_ageing_ko AS ko LEFT JOIN cms_customer_refund r ON r.cf_code = ko.doc_code WHERE doc_type = 'CF' AND cancelled = 'F' AND cust_code = '{$custCode}' AND doc_date >= '{$dateFrom}'";

        $allKo = array();
        $koRefCodes = array();

        $dbClone->query($rcpKnockOff);

        if($dbClone->get_num_rows() != 0){
          while($rcpRes = $dbClone->fetch_array()){
            $ko_ref = $rcpRes['doc_ko_ref'];
            $doc_code = $rcpRes['Ref'];
            $allKo[$ko_ref][] = $rcpRes;

            if($koRefCodes){
              if(!in_array($ko_ref, $koRefCodes)){
                $koRefCodes[] = $ko_ref;
              }
            }else{
              $koRefCodes[] = $ko_ref;
            }
          }
        }

        $dbClone->query($cnKnockOff);

        if($dbClone->get_num_rows() != 0){
          while($rcpRes = $dbClone->fetch_array()){
            $ko_ref = $rcpRes['doc_ko_ref'];
            $doc_code = $rcpRes['Ref'];
            $allKo[$ko_ref][] = $rcpRes;

            if($koRefCodes){
              if(!in_array($ko_ref, $koRefCodes)){
                $koRefCodes[] = $ko_ref;
              }
            }else{
              $koRefCodes[] = $ko_ref;
            }
          }
        }

        $dbClone->query($cfKnockOff);

        if($dbClone->get_num_rows() != 0){
          while($rcpRes = $dbClone->fetch_array()){
            $ko_ref = $rcpRes['doc_ko_ref'];
            $doc_code = $rcpRes['Ref'];
            $allKo[$ko_ref][] = $rcpRes;

            if($koRefCodes){
              if(!in_array($ko_ref, $koRefCodes)){
                $koRefCodes[] = $ko_ref;
              }
            }else{
              $koRefCodes[] = $ko_ref;
            }
          }
        }

        echo json_encode($allKo).'<br><br>';
        // echo json_encode($koRefCodes).'<br><br>';

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

        $inv_date_cond = " AND invoice_date <= '{$dateTo}'";
        if($dateFrom == $dateTo){
          $inv_date_cond = '';
        }

      $inv_query = "SELECT invoice_amount AS 'DR',outstanding_amount,DATE(invoice_date) AS 'Date','IN' AS 'Type',invoice_code AS 'Ref','INVOICE' AS description, (invoice_amount-outstanding_amount) AS 'OUTSTANDING','' AS 'CR' FROM cms_invoice AS inv WHERE cust_code = '{$custCode}' AND cancelled = 'F'  AND outstanding_amount <> 0 {$inv_date_cond}";
        
      if($is_SQLAccounting){
        $inv_query = "SELECT invoice_amount AS 'DR',outstanding_amount,DATE(invoice_date) AS 'Date','IN' AS 'Type',invoice_code AS 'Ref','INVOICE' AS description, outstanding_amount AS 'OUTSTANDING','' AS 'CR' FROM cms_invoice AS inv WHERE cust_code = '{$custCode}' AND cancelled = 'F' AND (outstanding_amount <> 0 {$inv_date_cond} OR invoice_code IN ('".implode("','",$koRefCodes)."'))";
      }

      $dn_date_cond = " AND dn_date <='{$dateTo}'";
      if((($dateFrom == $dateTo) && $is_AutoCount) || (($dateFrom == $dateTo) && $is_SmartSQL) || (($dateFrom == $dateTo) && $is_SQLAccounting)){
        $dn_date_cond = '';
        if($is_SmartSQL){
          $dn_date_cond = ' AND outstanding_amount = 0 ';
        }
      }
      $dn_query = "SELECT dn_amount  AS 'DR',outstanding_amount,DATE(dn_date) AS 'Date','DN' AS 'Type',dn_code AS 'Ref','DEBITNOTE' AS description, dn_amount AS 'OUTSTANDING','' AS 'CR' FROM cms_debitnote AS dn WHERE cust_code = '{$custCode}' AND cancelled = 'F' {$dn_date_cond} AND (outstanding_amount > 0 OR dn_code IN ('".implode("','",$koRefCodes)."'))";

      $cn_date_cond = "AND cn_date <= '{$dateTo}'";
      if($dateFrom == $dateTo){
        if($is_AutoCount){
          $cn_date_cond = '';
        }else{
          $cn_date_cond = " AND (cn_amount - cn_knockoff_amount) <> 0";
        }
        if($is_SmartSQL){
          $cn_date_cond = " AND cn_knockoff_amount = 0 ";
        }
      }

      $cn_query = "SELECT cn_amount AS 'CR',cn_knockoff_amount,DATE(cn_date) AS 'Date','CN' AS 'Type',cn_code AS 'Ref','CREDITNOTE' AS description, '' AS 'DR',(cn_amount-cn_knockoff_amount) AS 'OUTSTANDING' FROM cms_creditnote AS cn WHERE cust_code = '{$custCode}' AND cancelled = 'F' {$cn_date_cond}  AND ((cn_amount - cn_knockoff_amount) <> 0 OR cn_code IN ('".implode("','",$koRefCodes)."'))";
      
        if($bad_calc_customer){
          $cn_query = "SELECT cn_amount AS 'CR',cn_knockoff_amount,DATE(cn_date) AS 'Date','CN' AS 'Type',cn_code AS 'Ref','CREDITNOTE' AS description, '' AS 'DR',(cn_amount-cn_knockoff_amount) AS 'OUTSTANDING' FROM cms_creditnote AS cn WHERE cust_code = '{$custCode}' AND cancelled = 'F'";
        }

        $or_date_cond = " AND receipt_date <= '{$dateTo}'";
        if(($dateFrom == $dateTo)){
          if($is_AutoCount){
            $or_date_cond = '';
          }
          if($is_SQLAccounting){
            $or_date_cond = ' AND (receipt_amount-receipt_knockoff_amount) <> 0 ';
          }
          if($is_SmartSQL){
            $or_date_cond = " AND receipt_knockoff_amount =0 ";
          }
        }

        $or_query = "SELECT receipt_amount AS 'CR',receipt_knockoff_amount,DATE(receipt_date) AS 'Date','OR' AS 'Type', receipt_code AS 'Ref',
        IF(CONCAT(IFNULL(receipt_desc,''),IFNULL(cheque_no,'')) = '','RECEIPT',CONCAT(IFNULL(receipt_desc,''),IF(cheque_no = '','','\nCHEQUE NO:'),IFNULL(cheque_no,''))) AS description,'' AS 'DR',(receipt_amount-receipt_knockoff_amount) AS 'OUTSTANDING' FROM cms_receipt WHERE cust_code = '{$custCode}' AND cancelled = 'F' {$or_date_cond} AND ((receipt_amount-receipt_knockoff_amount) <> 0 OR receipt_code IN ('".implode("','",$koRefCodes)."'))";

        // if($bad_calc_customer){
        //   $or_query               = "SELECT receipt_amount AS 'CR',receipt_knockoff_amount,DATE(receipt_date) AS 'Date','OR' AS 'Type', receipt_code AS 'Ref',
        // IF(CONCAT(IFNULL(receipt_desc,''),IFNULL(cheque_no,'')) = '','RECEIPT',CONCAT(IFNULL(receipt_desc,''),IF(cheque_no = '','','\nCHEQUE NO:'),IFNULL(cheque_no,''))) AS description,'' AS 'DR',(receipt_amount-receipt_knockoff_amount) AS 'OUTSTANDING' FROM cms_receipt WHERE cust_code = '{$custCode}' AND cancelled = 'F'";
        // }

        // $postdatedQuery         = "SELECT * FROM cms_receipt WHERE cust_code = '{$custCode}' AND cancelled = 'F' AND DATE(receipt_date) > CURRENT_DATE() ORDER BY receipt_date DESC";


        $cf_date_cond = " AND invoice_date <= '{$dateTo}'";
        if($dateFrom == $dateTo){
          $cf_date_cond = '';
        }

      $cf_query = "SELECT cf_amount AS 'DR',cf_outstanding_amount,DATE(invoice_date) AS 'Date','IN' AS 'Type',invoice_code AS 'Ref','INVOICE' AS description, (invoice_amount-cf_outstanding_amount) AS 'OUTSTANDING','' AS 'CR' FROM cms_invoice AS inv WHERE cust_code = '{$custCode}' AND cancelled = 'F'  AND outstanding_amount <> 0 {$cf_date_cond}";
        
      echo $inv_query;
      
        $db->query($inv_query);

        $distinctKoList = array();

        $out_array = array();

        if($db->get_num_rows() != 0){
            $total = 0;
            while($result = $db->fetch_array()){

              $doc_ref_ko = mysql_real_escape_string($result['Ref']);

              $knockOffDtl = isset($allKo[$doc_ref_ko]) ? $allKo[$doc_ref_ko] : array();
              $rcp_arr = array();
              $cn_arr = array();
              for ($i=0; $i < count($knockOffDtl); $i++) { 
                $knockOff = $knockOffDtl[$i];
                
                // $knockOffJoinArr[] = $knockOff['Ref'];
                // $dtl = array(
                //     "Date"          =>  $knockOff['Date'],
                //     "Type"          =>  $knockOff['Type'],
                //     "Ref"           =>  $knockOff['Ref'],
                //     "Description"   =>  $knockOff['description'],
                //     "DR"            =>  $knockOff['DR'],
                //     "CR"            =>  $knockOff['CR'],
                //     "Outstanding"   =>  $knockOff['OUTSTANDING'],
                //     "Due"           =>  $knockOff['receipt_knockoff_amount'],
                //     "key"           =>  monthAndYear($knockOff['Date'])
                // );
                // if($knockOff['type'] == 'r'){
                //   array_push($rcp_arr,$dtl);
                // }else{
                //   array_push($cn_arr,$dtl);
                // }
                $distinctKoList [] = $knockOff['Ref'];
                $doc_amount = $knockOff['doc_amount'];
                if($doc_amount > 0){
                  $result['outstanding_amount'] += $doc_amount;
                }
              }

              $credit_count = count(array_unique($distinctKoList));

              // array_push($docMerged,array(
              //     "Date"          =>  $result['Date'],
              //     "Type"          =>  $result['Type'],
              //     "Ref"           =>  $result['Ref'],
              //     "Description"   =>  $result['description'],
              //     "DR"            =>  $result['DR'],
              //     "CR"            =>  $result['CR'],
              //     "Outstanding"   =>  $result['OUTSTANDING'],
              //     "Due"           =>  $result['outstanding_amount'],
              //     "key"           =>  monthAndYear($result['Date']),
              //     "receipt"       =>  $rcp_arr,
              //     "credit"        =>  $cn_arr
              // ));

              array_push($out_array,array(
                "Date"          =>  $result['Date'],
                "Ref"           =>  $result['Ref'],
                "Description"   =>  $result['description'],
                "Due"           =>  $result['outstanding_amount'],
                "Type"          => 'Debit'
              ));

              $debit_count++;
            }
        }

        $db->query($dn_query);

        if($db->get_num_rows() != 0){
            while($result = $db->fetch_array()){
                // array_push($docMerged,array(
                //     "Date"          =>  $result['Date'],
                //     "Type"          =>  $result['Type'],
                //     "Ref"           =>  $result['Ref'],
                //     "Description"   =>  $result['description'],
                //     "DR"            =>  $result['DR'],
                //     "CR"            =>  $result['CR'],
                //     "Outstanding"   =>  $result['OUTSTANDING'],
                //     "Due"           =>  $result['outstanding_amount'],
                //     "key"           =>  monthAndYear($result['Date'])
                // ));
                $doc_ref_ko = mysql_real_escape_string($result['Ref']);

                $knockOffDtl = isset($allKo[$doc_ref_ko]) ? $allKo[$doc_ref_ko] : array();
                $rcp_arr = array();
                $cn_arr = array();
                for ($i=0; $i < count($knockOffDtl); $i++) { 
                  $knockOff = $knockOffDtl[$i];

                  $distinctKoList [] = $knockOff['Ref'];
                  $doc_amount = $knockOff['doc_amount'];
                  if($doc_amount > 0){
                    $result['outstanding_amount'] += $doc_amount;
                  }
                }

                array_push($out_array,array(
                  "Date"          =>  $result['Date'],
                  "Ref"           =>  $result['Ref'],
                  "Description"   =>  $result['description'],
                  "Due"           =>  $result['outstanding_amount'],
                  "Type"          => 'Debit'
                ));
              $debit_count++;
            }
        }

        $db->query($cn_query);

        if($db->get_num_rows() != 0){

            while($result = $db->fetch_array()){


              $doc_ref_ko = mysql_real_escape_string($result['Ref']);

              $knockOffDtl = isset($allKo[$doc_ref_ko]) ? $allKo[$doc_ref_ko] : array();
              $rcp_arr = array();
              $cn_arr = array();
              for ($i=0; $i < count($knockOffDtl); $i++) { 
                $knockOff = $knockOffDtl[$i];
                $distinctKoList [] = $knockOff['Ref'];
                $doc_amount = $knockOff['doc_amount'];
                if($doc_amount > 0){

                  $result['OUTSTANDING'] += $doc_amount;
                }
              }

              // if(!in_array($result['Ref'],$knockOffJoinArr)){
              //   array_push($docMerged,array(
              //       "Date"          =>  $result['Date'],
              //       "Type"          =>  $result['Type'],
              //       "Ref"           =>  $result['Ref'],
              //       "Description"   =>  $result['description'],
              //       "DR"            =>  $result['DR'],
              //       "CR"            =>  $result['CR'],
              //       "Outstanding"   =>  $result['OUTSTANDING'],
              //       "Due"           =>  $result['receipt_knockoff_amount'],
              //       "key"           =>  monthAndYear($result['Date'])
              //   ));
              // } 
              array_push($out_array,array(
                "Date"          =>  $result['Date'],
                "Ref"           =>  $result['Ref'],
                "Description"   =>  $result['description'],
                "Due"           =>  $result['OUTSTANDING'],
                "Type"          => 'Credit'
              ));
                $credit_count++;
            }
        }

        $db->query($or_query);

        if($db->get_num_rows() != 0){
          $total = 0;
            while($result = $db->fetch_array()){


              $doc_ref_ko = mysql_real_escape_string($result['Ref']);

              $knockOffDtl = isset($allKo[$doc_ref_ko]) ? $allKo[$doc_ref_ko] : array();
              $rcp_arr = array();
              $cn_arr = array();
              for ($i=0; $i < count($knockOffDtl); $i++) { 
                $knockOff = $knockOffDtl[$i];
                $distinctKoList [] = $knockOff['Ref'];
                $doc_amount = $knockOff['doc_amount'];
                if($doc_amount > 0){
                  $result['OUTSTANDING'] += $doc_amount;
                }
              }

              // if(!in_array($result['Ref'],$knockOffJoinArr)){
              //   array_push($docMerged,array(
              //       "Date"          =>  $result['Date'],
              //       "Type"          =>  $result['Type'],
              //       "Ref"           =>  $result['Ref'],
              //       "Description"   =>  $result['description'],
              //       "DR"            =>  $result['DR'],
              //       "CR"            =>  $result['CR'],
              //       "Outstanding"   =>  $result['OUTSTANDING'],
              //       "Due"           =>  $result['receipt_knockoff_amount'],
              //       "key"           =>  monthAndYear($result['Date'])
              //   ));
              // } 
              array_push($out_array,array(
                "Date"          =>  $result['Date'],
                "Ref"           =>  $result['Ref'],
                "Description"   =>  $result['description'],
                "Due"           =>  $result['OUTSTANDING'],
                "Type"          => 'Credit'
              ));
              $credit_count++;
            }
        }

      

        foreach ($allKo as $key => $value) {
          
          for ($i=0; $i < count($value); $i++) { 
            $result = $value[$i];

            $no_found = 1;
            for ($j=0; $j < count($out_array); $j++) { 
              $row = $out_array[$j];
  
              if($row['Ref'] == $result['Ref'] && $row['Type'] == $result['Type']){
  
                
  
                $row['Due'] += $result['doc_amount'];
                $no_found = 0;
                $out_array[$j]['Due'] = $row['Due'];
                break;
              }
            }
  
            if($no_found){
              array_push($out_array,array(
                "Date"          =>  $result['Date'],
                "Ref"           =>  $result['Ref'],
                "Description"   =>  $result['description'],
                "Due"           =>  $result['doc_amount'],
                "Type"          =>  $result['Type']
              ));
            }
          }
        }

        usort($out_array, build_sorter("Date", "ASC"));

        // echo json_encode($out_array);


        echo '<table><tr><th>DATE</th><th>REF</th><th>DESC</th><th>DEBIT</th><th>CREDIT</th><th>AMOUNT</th></tr>';
        $amount = 0;
        for ($i=0; $i < count($out_array); $i++) { 
          $row = $out_array[$i];

          if(strtotime($row['Date']) <= strtotime($dateTo)){
            if($row['Type'] == 'Debit'){
              $amount += $row['Due'];
  
              echo '<tr>
                      <td>'.$row['Date'].'</td>
                      <td>'.$row['Ref'].'</td>
                      <td>'.$row['Description'].'</td>
                      <td>'.$row['Due'].'</td>
                      <td></td>
                      <td>'.$amount.'</td>
                    </tr>';
            }else{
              $amount -= $row['Due'];
  
              echo '<tr>
                      <td>'.$row['Date'].'</td>
                      <td>'.$row['Ref'].'</td>
                      <td>'.$row['Description'].'</td>
                      <td></td>
                      <td>'.$row['Due'].'</td>
                      <td>'.$amount.'</td>
                    </tr>';
            }
          }

        }
        echo '</table>';

        return;

        usort($docMerged, build_sorter("Date", "ASC"));

        $client_name = '';
        $client_info = '';

        $db->query("SELECT * FROM cms_mobile_module WHERE module = 'app_client' OR module = 'app_client_info'");
        if($db->get_num_rows() != 0){
            while($result = $db->fetch_array()){
                if($result['module'] == 'app_client'){
                    $client_name = $result['status'];
                    $client_name = explode('@n',$client_name)[0];
                }
                if($result['module'] == 'app_client_info'){
                    $client_info = $result['status'];
                }
            }
        }
        if(!empty($client_info)){
            $client_info = json_decode($client_info,true);
        }

        $row_view = '';

        $balance = 0;
        for ($i=0; $i < count($docMerged); $i++) { 
            $each = $docMerged[$i];
            $receipt = $each['receipt'];
            $credit = $each['credit'];

            if($is_Greenplus && $each['Type'] != 'IN'){
              continue;
            }
            
            if($is_SQLAccounting){
              if($each['Type'] == 'IN' || $each['Type'] == 'DN'){
                $balance += floatval($each['Outstanding']);
                $debit_sum += floatval($each['Outstanding']);
              }
              if($each['Type'] == 'CN' || $each['Type'] == 'OR'){
                $balance -= floatval($each['Outstanding']);
                $credit_sum += floatval($each['Outstanding']);
              }
            }else if($is_SmartSQL){
              if($each['Type'] == 'IN'){
                $balance += floatval($each['Due']);
              }
              if($each['Type'] == 'CN' || $each['Type'] == 'OR'){
                $balance -= floatval($each['CR']);
              }
              if($each['Type'] == 'DN'){
                $balance -= floatval($each['DR']);
              }
            }else{
              $balance += floatval($each['DR']);
              if(count($receipt) == 0 && count($credit) == 0){
                $balance -= floatval($each['Outstanding']);
              }
            }

            $rowStyle = '';
            if(count($receipt) == 0 && count($credit) == 0){
              $rowStyle = 'border-bottom:1px solid lightgrey;';
            }
            if($doc_row){
              $temp_row = str_replace(array('@row_date','@row_reference','@row_description','@row_debit','@row_credit','@row_balance'),array(date("d/m/Y",strtotime($each['Date'])),$each['Ref'],$each['Description'],money($each['DR']),money($each['CR']),money($balance,true)),$doc_row);
              $row_view .= $temp_row;
            }else{
                $row_view .= '
                  <tr style="'.$rowStyle.'">
                      <td align="center">'.date("d/m/Y",strtotime($each['Date'])).'</td>
                      <td align="center">'.$each['Type'].'</td>
                      <td align="center">'.$each['Ref'].'</td>
                      <td align="center">'.$each['Description'].'</td>
                      <td align="center">'.money($each['DR']).'</td>
                      <td align="center">'.money($each['CR']).'</td>
                      <td align="right" style="padding-right:10px">'.money($balance,true).'</td>
                  </tr>
              ';
            }
            for ($j=0; $j < count($receipt); $j++) { 
               $rcp = $receipt[$j];
               if($is_SQLAccounting){
                if($rcp['Type'] == 'CN'){
                  $balance -= floatval($rcp['Outstanding']);
                }
              }else{
                if($client == 'abaro'){
                  $balance += floatval($rcp['DR']);
                  $balance -= floatval($rcp['Outstanding']);
                  $balance -= floatval($rcp['CR']);
                }
              }
              $rowStyle = '';
              if(count($credit) == 0 && $j == (count($receipt)-1)){
                $rowStyle = 'border-bottom:1px solid lightgrey';
              }
              if($doc_row){
                $temp_row = $temp_row = str_replace(array('@row_date','@row_reference','@row_description','@row_debit','@row_credit','@row_balance'),array(date("d/m/Y",strtotime($rcp['Date'])),$rcp['Ref'],$rcp['Description'],money($rcp['DR']),money($rcp['CR']),money($balance,true)),$doc_ko_row);
                $row_view .= $temp_row;
              }else{
                $row_view .= '
                    <tr style="font-style:italic;color:grey;'.$rowStyle.'">
                        <td align="center">'.date("d/m/Y",strtotime($rcp['Date'])).'</td>
                        <td align="center">'.$rcp['Type'].'</td>
                        <td align="center">'.$rcp['Ref'].'</td>
                        <td align="center">'.$rcp['Description'].'</td>
                        <td align="center">'.money($rcp['DR']).'</td>
                        <td align="center">'.money($rcp['CR']).'</td>
                        <td align="right" style="padding-right:10px">'.money($balance,true).'</td>
                    </tr>
                ';
              }
            }
            for ($j=0; $j < count($credit); $j++) { 
              $cn = $credit[$j];
              if($is_SQLAccounting){
               if($cn['Type'] == 'CN'){
                 $balance -= floatval($cn['Outstanding']);
               }
             }else{
               if($client == 'abaro'){
                $balance += floatval($cn['DR']);
                $balance -= floatval($cn['Outstanding']);
                $balance -= floatval($cn['CR']);
               }
             }
             $rowStyle = '';
              if($j == (count($credit)-1)){
                $rowStyle = 'border-bottom:1px solid lightgrey';
              }
             $row_view .= '
                       <tr style="font-style:italic;color:grey;'.$rowStyle.'">
                           <td align="center">'.date("d/m/Y",strtotime($cn['Date'])).'</td>
                           <td align="center">'.$cn['Type'].'</td>
                           <td align="center">'.$cn['Ref'].'</td>
                           <td align="center">'.$cn['Description'].'</td>
                           <td align="center">'.money($cn['DR']).'</td>
                           <td align="center">'.money($cn['CR']).'</td>
                           <td align="right" style="padding-right:10px">'.money($balance,true).'</td>
                       </tr>
                   ';
           }
        }

        $groupedDoc = group_by_key($docMerged);

        foreach ($groupedDoc as $date=>$data){
            $monthlyTotal = 0;
            foreach ($data as $key=>$each){
                if($is_SQLAccounting){
                    if($each['Type'] == 'IN'){
                        $monthlyTotal += floatval($each['Outstanding']);
                    }
                    if($each['Type'] == 'CN'){
                        $monthlyTotal -= floatval($each['Outstanding']);
                    }
                }else{
                    $monthlyTotal += floatval($each['DR']);
                    $monthlyTotal -= floatval($each['Outstanding']);
                }
            }
            array_push($monthlyAging,array(
                "key"   => keyName($date),
                "value" => money($monthlyTotal,true),
                "dateMonth"=>$date
            ));
        }

        $client_addr = $client_info['address'].', '.$client_info['city'].($client_info['zipcode']?', ':'').$client_info['zipcode'].($client_info['state'] ? ', ':'').$client_info['state'];

        $inWord = moneyInWord($balance);
        $inWord = strtoupper($inWord);

        $html = str_replace('@balance_in_word',$inWord,$html);
        $html = str_replace('@balance',money($balance,true),$html);
        $html = str_replace('@document_row',$row_view,$html);
        $html = str_replace('@debtor_term',$debtor_term,$html);
        $html = str_replace('@debtor_tel',$debtor_tel,$html);
        $html = str_replace('@statement_date',date("d/m/Y h:i A"),$html);
        $html = str_replace('@debtor_addr',$debtor_addr,$html);
        $html = str_replace('@debtor_name',$debtor_name,$html);
        $html = str_replace('@debtor_code',$debtor_code,$html);
        $html = str_replace('@attention',$debtor_incharge,$html);
        $html = str_replace('@client_website',$client_info['website'],$html);
        $html = str_replace('@client_phone',str_replace('@nTel No:','',$client_info['phone']),$html);
        $html = str_replace('@client_email',$client_info['email'],$html);
        $html = str_replace('@client_addr',$client_addr,$html);
        $html = str_replace('@client_sub_name',$client_info['sub_name'],$html);
        $html = str_replace('@client_name',$client_name,$html);
        $html = str_replace('@to_date',date_format(date_create($dateTo),'d/m/Y'),$html);
        $html = str_replace('@debit_count',$debit_count,$html);
        $html = str_replace('@debit_sum',$debit_sum,$html);
        $html = str_replace('@credit_count',$credit_count,$html);
        $html = str_replace('@credit_sum',$credit_sum,$html);
        $ifNegativeBalance = $balance < 0 ? $balance * -1 : $balance;
        $html = str_replace('@closing_balance',$balance > 0 ? $balance : "({$ifNegativeBalance})",$html);

        $postDated = '
          <div style="margin-top:10px">
            <table style="width:80%">
                <col width="15%">
                <col width="15%">
                <col width="15%">
                <col width="15%">
                <col width="15%">
                <tr>
                    <th class="postdated" align="center">Payment Date</th>
                    <th class="postdated" align="center">Cheque No</th>
                    <th class="postdated" align="center">OR No</th>
                    <th class="postdated" align="center">Knockoff</th>
                    <th class="postdated" align="center">Amount</th>
                </tr>
                @postedRow
            </table>
            <div style="margin-top:10px">
              <table style="width:80%">
                  <tr>
                      <td align="left">
                          Total Post Dated Cheque
                      </td>
                      <td align="right" style="padding-right:10px">
                          <strong>
                              @total_postdated
                          </strong>
                      </td>
                  </tr>
              </table>
            </div>
          </div>
        ';

        $result = $db->query("SHOW COLUMNS FROM `cms_receipt` LIKE 'cheque_no'");
        $exists = (mysql_num_rows($result)) ? TRUE : FALSE;
        if($exists){
          $postdatedRow = "";

          $db->query($postdatedQuery);

          $totalPostdatedAmt = 0;

          if($db->get_num_rows() != 0){
              while($result = $db->fetch_array()){
                  $postdatedRow .= '
                    <tr style="border-bottom:1px solid lightgrey">
                        <td align="center">'.$result['receipt_date'].'</td>
                        <td align="center">'.$result['cheque_no'].'</td>
                        <td align="center">'.$result['receipt_code'].'</td>
                        <td align="center">'.money($result['receipt_knockoff_amount']).'</td>
                        <td align="right" style="padding-right:10px">'.money($result['receipt_amount'],true).'</td>
                    </tr>
                  ';

                  $totalPostdatedAmt += floatval($result['receipt_amount']);
              }
          }

          if(strlen($postdatedRow) > 0){
              $postDated = str_replace('@postedRow',$postdatedRow,$postDated);
              $postDated = str_replace('@total_postdated',money($totalPostdatedAmt,true),$postDated);
              $html = str_replace('@postdated',$postDated,$html);
          }else{
              $html = str_replace('@postdated',"",$html);
          }
        }else{
          $html = str_replace('@postdated',"",$html);
        }

        $monthlyAging = array_reverse($monthlyAging);
        
        /* $monthlyAgingHtml = '<div class="Row">';

        $monthlyAgingHtml .= '
            <div class="Column">
                <p style="padding:2px;line-height:0px;">TOTAL</p>
                <hr id="monthlyDivider"/>
                <p style="padding:2px;line-height:0px;font-size:13px;">'.money($balance,true).'</p>
            </div>
        '; */
        $monthlyAgingHtml = $statement['monthly'];
        $monthlyAgingHtml = str_replace('@current_total',money($balance,true),$monthlyAgingHtml);

        for ($i=0; $i < 6; $i++) { 
            if(isset($monthlyAging[$i])){
              $key = '@'.$i.'_month';
              $each = $monthlyAging[$i];
                /* $each = $monthlyAging[$i];

                $monthlyAgingHtml .= '
                    
                        <div class="Column">
                            <p style="padding:0px;line-height:0px;">'.$each['key'].'</p>
                            <hr id="monthlyDivider"/>
                            <p style="padding:0px;line-height:0px;font-size:13px;">'.$each['value'].'</p>
                        </div>
                    
                '; */
              $monthlyAgingHtml = str_replace($key,$each['value'],$monthlyAgingHtml);
            }else{
              $key = '@'.$i.'_month';
              $monthlyAgingHtml = str_replace($key,'',$monthlyAgingHtml);
                /* $monthlyAgingHtml .= '
                    
                        <div style="border:1px solid transparent" class="Column">
                            <p></p>
                            <hr style="border:1px solid transparent"/>
                            <p></p>
                        </div>
                    
                '; */
            }
        }
        
        $monthlyAgingCount = count($monthlyAging);
        if($monthlyAgingCount > 6){
          
            $restTotal = 0;
            for ($i=6; $i < $monthlyAgingCount; $i++){
              $each = $monthlyAging[$i];
              $restTotal += floatMoney($each['value']);
            }
            
            /* $monthlyAgingHtml .= '
                    
              <div class="Column">
                  <p style="padding:2px;line-height:0px;font-size:13px;">6Mnths Above</p>
                  <hr id="monthlyDivider"/>
                  <p style="padding:2px;line-height:0px;font-size:13px;">'.money($restTotal,true).'</p>
              </div>
        
            '; */
            $monthlyAgingHtml = str_replace('@rest_month',money($restTotal,true),$monthlyAgingHtml);
        }

        $monthlyAgingHtml .= '</div>';

        $html = str_replace('@monthlyAging',$monthlyAgingHtml,$html);
        //$html = str_replace('@monthlyAging','',$html);

        if(count($docMerged) == 0){
          $zero_statement = $statement['zero'];
          $zero_statement = str_replace('@balance_in_word',$inWord,$zero_statement);
          $zero_statement = str_replace('@balance',money($balance,true),$zero_statement);
          $zero_statement = str_replace('@debtor_term',$debtor_term,$zero_statement);
          $zero_statement = str_replace('@statement_date',date("d/m/Y h:i A"),$zero_statement);
          $zero_statement = str_replace('@debtor_addr',$debtor_addr,$zero_statement);
          $zero_statement = str_replace('@debtor_name',$debtor_name,$zero_statement);
          $zero_statement = str_replace('@debtor_code',$debtor_code,$zero_statement);
          $zero_statement = str_replace('@client_website',$client_info['website'],$zero_statement);
          $zero_statement = str_replace('@client_phone',$client_info['phone'],$zero_statement);
          $zero_statement = str_replace('@client_email',$client_info['email'],$zero_statement);
          $zero_statement = str_replace('@client_addr',$client_addr,$zero_statement);
          $zero_statement = str_replace('@client_sub_name',$client_info['sub_name'],$zero_statement);
          $zero_statement = str_replace('@client_name',$client_name,$zero_statement);
          echo json_encode(array('statement'=>base64_encode($zero_statement)));
          return;
        }
        echo $html;
        // echo json_encode(array('statement'=>base64_encode($html)));
    }
}

function group_by_key($array, $key = 'key') {
    $return                     = array();
    foreach($array as $val) {
        $return[$val[$key]][]   = $val;
    }
    return $return;
}
function keyName($date){
    $year                       = explode("-",$date)[0];
    $month                      = explode("-",$date)[1];
    $month                      = intval($month);
    $month_names                = ['Jan', 'Febr', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec'];
    return $month_names[$month-1].", ".$year;
}
function build_sorter($key, $dir) {
    return function ($a, $b) use ($key, $dir) {
        $t1 = strtotime(is_array($a) ? $a[$key] : $a->$key);
        $t2 = strtotime(is_array($b) ? $b[$key] : $b->$key);
        if ($t1 == $t2) return 0;
        return (strtoupper($dir) == 'ASC' ? ($t1 < $t2) : ($t1 > $t2)) ? -1 : 1;
    };
}
function money($amount, $force = false){
    $amount = floatval($amount);
    if($force && !$amount) return 'RM0.00';
    if(!$amount) return '';
    return 'RM'.number_format($amount, 2, '.', ',');
}
function floatMoney($amount){
  $amount = str_replace(array("RM",","),"",$amount);
  return floatval($amount);
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

    return ($ringgit ? 'RINGGIT MALAYSIA '.$ringgit : '') . $cents ;
}
function monthAndYear($date){
  return substr(dateOnly($date),0,-3);
}
function dateOnly($date){
  return empty($date) ? "" : explode(" ",$date)[0];
}
?>
