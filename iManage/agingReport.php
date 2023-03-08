<?php
require_once('./model/DB_class.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Content-Type: text/html; charset=utf-8');
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
        @media print  
        {
           div{
               page-break-inside: avoid;
            }
        }
          .outline { outline: thin solid; text-align: right; padding-right: 5px; }
          p{ line-height: 0.1; } 
          h2,h3,h4{ line-height: 0.4; margin:0px; }
          hr{ height:0px; }
          * {
            box-sizing: border-box;
            font-weight:bold;
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
          h4{
            font-size:14px;
            font-weight:bold;
            line-height:13px;
          }
          th {
            border-top: 1px solid black;
            border-bottom: 1px solid black;
            font-size:12px;
          }
          td{
              height:14px;
              font-size:12px;
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
         <div class="bodyPadding topMargin">
         <table style="width: 100%; margin-left:-1px;">
            <tr>
               <td style="padding-bottom:50px">
                  <img style="width:200px;height:100px" src="@company_logo" alt="header"> 
               </td>
               <td class="fifty-percent">
                  <div>
                  <h4>
                    @client_name
                  </h4>
                  <p style="font-size:12px;line-height:1em;margin:0px;padding-bottom:5px;">
                     @client_sub_name
                  </p>
                  <div style="font-size:13px;line-height:1em;margin:0px;padding-bottom:5px;">
                    @client_addr
                  </div>
                  <div style="font-size:13px;line-height:1em;margin:0px;padding-bottom:5px;">
                        <strong>Phone:</strong> 
                            @client_phone
                        <strong>Email:</strong> 
                            @client_email<br>
                        <strong>Website:</strong> 
                            @client_website
                      </div>
                      @extra_header
                      <hr>
                      <h4 size="pixels">
                        Debtor Statement
                      </h4>
                  </div>
               </td>
            </tr>
         </table>
      </div>
      <div class="row">
        <div class="column">
          <table style="width:100%">
            <tr>
              <td align="right">
                <strong></strong>
              </td>
              <td align="left">
                @debtor_code
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
                <strong>Payment Term: </strong>
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
          <hr/>
      <div style="margin-top:10px">
        <table style="width:100%">
            <tr>
                <td align="left" style="width:70%;font-style:italic">
                @balance_in_word
                </td>
                <td align="right" style="padding-right:10px">
                    <p><strong>
                    TOTAL: @balance
                </strong></p>
                </td>
            </tr>
        </table>
      </div>

      @postdated
    
      <div style="margin-top:50px">
      @monthlyAging
      </div>
      @extra_footer
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
        @media print  
        {
           div{
               page-break-inside: avoid;
            }
        }
          .outline { outline: thin solid; text-align: right; padding-right: 5px; }
          p{ line-height: 0.1; } 
          h2,h3,h4{ line-height: 0.4; margin:0px; }
          hr{ height:0px; }
          * {
            box-sizing: border-box;
            font-weight:bold;
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
          h4{
            font-size:14px;
            font-weight:bold;
            line-height:13px;
          }
          th {
            border-top: 1px solid black;
            border-bottom: 1px solid black;
            font-size:12px;
          }
          td{
              height:14px;
              font-size:12px;
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
      <div class="bodyPadding topMargin">
          <table style="width: 100%; margin-left:-1px;">
            <tr>
               <td style="padding-bottom:50px">
                  <img style="width:200px;height:100px" src="@company_logo" alt="header"> 
               </td>
               <td class="fifty-percent">
                  <div>
                  <h4>
                    @client_name
                  </h4>
                  <p style="font-size:12px;line-height:1em;margin:0px;padding-bottom:5px;">
                     @client_sub_name
                  </p>
                  <div style="font-size:13px;line-height:1em;margin:0px;padding-bottom:5px;">
                    @client_addr
                  </div>
                  <div style="font-size:13px;line-height:1em;margin:0px;padding-bottom:5px;">
                        <strong>Phone:</strong> 
                            @client_phone
                        <strong>Email:</strong> 
                            @client_email<br>
                        <strong>Website:</strong> 
                            @client_website
                      </div>
                      @extra_header
                      <hr>
                      <h4 size="pixels">
                        Debtor Statement
                      </h4>
                  </div>
               </td>
            </tr>
         </table>
      </div>
      <div class="row">
        <div class="column">
          <table style="width:100%">
            <tr>
              <td align="right">
                <strong></strong>
              </td>
              <td align="left">
                @debtor_code
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
                <strong>Payment Term: </strong>
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
          <hr/>
      <div style="margin-top:10px">
        <table style="width:100%">
            <tr>
                <td align="left" style="width:70%;font-style:italic">
                @balance_in_word
                </td>
                <td align="right" style="padding-right:10px">
                    <p><strong>
                    TOTAL: @balance
                </strong></p>
                </td>
            </tr>
        </table>
      </div>

      @postdated
    
      <div style="margin-top:50px">
      @monthlyAging
      </div>
      @extra_footer
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
        <td class="outline">Current Month</td>
        <td class="outline">1 Month</td>
        <td class="outline">2 Months</td>
        <td class="outline">3 Months</td>
        <td class="outline">4 Months</td>
        <td class="outline">5 Months</td>
        <td class="outline">6 Mths&Abv</td>
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
    $users_autoCount            = $settings['AUTOCOUNT']['autoCount_client'];
    $users_QNE                  = $settings['QNE']['qne_client'];

    $statement_type             = $settings['Statement'];
    $isOpenItemStatement        = in_array($client,$statement_type['open_item_statement']);
    $isBringforwardStatement    = in_array($client,$statement_type['bringforward_statement']);

    $is_SQLAccounting       = in_array($client,$users_SQLAccounting);
    $is_Abswin              = in_array($client,$users_abswin);
    $is_Greenplus           = in_array($client,$users_Greenplus);
    $is_SmartSQL            = in_array($client,$users_SmartSQL);
    $is_AutoCountKO         = in_array($client,$users_autoCount);
    $is_QNE                 = in_array($client,$users_QNE);

    if($is_SQLAccounting && ($isOpenItemStatement || $isBringforwardStatement)){
      $file = 'custStatement.php';
      if($isOpenItemStatement){
        $file = 'customerStatement.php';
      }

      $link = "https://easysales.asia/esv2/webview/iManage/{$file}?date_from={$dateFrom}&date_to={$dateTo}&client={$client}&cust_code={$custCode}";
      
      header('Location: '.$link);
      die();
    }

    $db                         = new DB();

    $bad_calc_customer          = $settings['Bad_Customer']['bad_calc_module'];

    $bad_calc_customer          = in_array($client,$bad_calc_customer);

    $settings                   = $settings[$client];

    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
    $db_name                    = $settings['db'];
    $db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

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

        // query to get company logo url from cms_settings and then replace @company_logo with the url
        $settingTable = "SELECT * FROM cms_setting";
        $db->query($settingTable);
        if($db->get_num_rows() > 0){
          while($result = $db->fetch_array()){
            $logo_url = $result['logo_Url'];
          }
        }else{
          $logo_url = '';
        }

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

        // file_put_contents('ageing.log',json_encode($is_AutoCountKO).'--'.json_encode($users_autoCount));

        $is_AutoCount = $is_Abswin === false && $is_Greenplus === false && $is_SQLAccounting === false && $is_SmartSQL === false;

        $docMerged              = array();

        $custCode               = mysql_real_escape_string($custCode);
        $dateFrom               = mysql_real_escape_string($dateFrom);
        $dateTo                 = mysql_real_escape_string($dateTo);


        $rcpKnockOff = "SELECT 'r' AS type,doc_ko_ref, doc_amount AS 'CR',receipt_knockoff_amount,DATE(receipt_date) AS 'Date','OR' AS 'Type', receipt_code AS 'Ref',
        IF(CONCAT(IFNULL(receipt_desc,''),IFNULL(cheque_no,'')) = '','RECEIPT',CONCAT(IFNULL(receipt_desc,''),IF(cheque_no = '','','\nCHEQUE NO:'),IFNULL(cheque_no,''))) AS description,
        '' AS 'DR',(receipt_amount-receipt_knockoff_amount) AS 'OUTSTANDING',DATE(invoice_date) as invoice_date, invoice_amount, outstanding_amount as inv_out_amount FROM cms_customer_ageing_ko AS ko
            LEFT JOIN cms_receipt r ON r.receipt_code = ko.doc_code
        LEFT JOIN cms_invoice i ON i.invoice_code = ko.doc_ko_ref
WHERE doc_type = 'OR' AND r.cancelled = 'F' AND r.cust_code = '{$custCode}' AND ko.active_status = 1";


        $cnKnockOff = "SELECT 'c' AS type,doc_ko_ref, doc_amount AS 'CR',cn_knockoff_amount,DATE(cn_date) AS 'Date','CN' AS 'Type',cn_code AS 'Ref','CREDITNOTE' AS description, '' AS 'DR',
                (cn_amount-cn_knockoff_amount) AS 'OUTSTANDING' FROM cms_customer_ageing_ko AS ko LEFT JOIN cms_creditnote c ON c.cn_code = ko.doc_code WHERE doc_type = 'CN' AND cancelled = 'F' AND cust_code = '{$custCode}'  AND ko.active_status = 1";

        $rcpCnKnockOff = array();
        $rcpCnKnockOffReverse = array();

        $dbClone->query($rcpKnockOff);

        if($dbClone->get_num_rows() != 0){
          while($rcpRes = $dbClone->fetch_array()){
            $ko_ref = $rcpRes['doc_ko_ref'];
            $rcpCnKnockOff[$ko_ref][] = $rcpRes;
            $ref = $rcpRes['Ref'];
            $rcpCnKnockOffReverse[$ref][] = $rcpRes;
          }
        }

        $dbClone->query($cnKnockOff);

        if($dbClone->get_num_rows() != 0){
          while($rcpRes = $dbClone->fetch_array()){
            $ko_ref = $rcpRes['doc_ko_ref'];
            $rcpCnKnockOff[$ko_ref][] = $rcpRes;
            $ref = $rcpRes['Ref'];
            $rcpCnKnockOffReverse[$ref][] = $rcpRes;
          }
        }


        $debtor_code = ''; $debtor_name = ''; $debtor_addr = ''; $debtor_term = ''; $debtor_tel = ''; $debtor_incharge = ''; 

        $cust_query             = "SELECT c.*, CONCAT(IFNULL(c.billing_address1,''),' ',IFNULL(c.billing_address2,''),' ',IFNULL(c.billing_address3,''),' ',IFNULL(c.billing_address4,''),' ',IFNULL(c.billing_city,''),' ',IFNULL(c.billing_state,'')) AS address FROM cms_customer AS c WHERE cust_code = '{$custCode}' LIMIT 1";

        $db->query($cust_query);

        if($db->get_num_rows() != 0){
            while($result = $db->fetch_array()){
                $debtor_code    = $result['cust_code'];
                $debtor_name    = $result['cust_company_name'];
                $debtor_addr    = str_replace('#','',$result['address']);
                $debtor_term    = $result['termcode'];
                $debtor_tel     = $result['cust_tel'];
                $debtor_incharge= $result['cust_incharge_person'];
            }
        }

        $debit_count = 0; $debit_sum = 0; $credit_count = 0; $credit_sum = 0;

        $inv_date_cond = " AND invoice_date BETWEEN '{$dateFrom}' AND '{$dateTo}'";
        if($dateFrom == $dateTo){
          $inv_date_cond = '';
        }else{
          if($is_AutoCountKO){
            $inv_date_cond = '';
          }
        }

      $inv_query              = "SELECT outstanding_amount AS 'DR',outstanding_amount,DATE(invoice_date) AS 'Date','IN' AS 'Type',invoice_code AS 'Ref','INVOICE' AS description, (invoice_amount-outstanding_amount) AS 'OUTSTANDING','' AS 'CR' FROM cms_invoice AS inv WHERE cust_code = '{$custCode}' AND cancelled = 'F'  AND outstanding_amount <> 0 {$inv_date_cond}";

      
        
        if($is_SQLAccounting){
          $inv_query              = "SELECT outstanding_amount AS 'DR',outstanding_amount,DATE(invoice_date) AS 'Date','IN' AS 'Type',invoice_code AS 'Ref','INVOICE' AS description, outstanding_amount AS 'OUTSTANDING','' AS 'CR' FROM cms_invoice AS inv WHERE cust_code = '{$custCode}' AND cancelled = 'F' AND outstanding_amount <> 0 {$inv_date_cond}";
        }

        if($is_QNE){
          $inv_query              = "SELECT outstanding_amount AS 'DR',outstanding_amount,DATE(invoice_date) AS 'Date','IN' AS 'Type',invoice_code AS 'Ref','INVOICE' AS description, outstanding_amount AS 'OUTSTANDING','' AS 'CR' FROM cms_invoice AS inv WHERE cust_code = '{$custCode}' AND cancelled = 'F' AND outstanding_amount <> 0 {$inv_date_cond}";
        }

        $dn_date_cond = " AND dn_date BETWEEN '{$dateFrom}' AND '{$dateTo}'";
        if($dateFrom == $dateTo){
          $dn_date_cond = '';
          if($is_SmartSQL){
            $dn_date_cond = ' AND outstanding_amount = 0 ';
          }else{
            $dn_date_cond = ' AND outstanding_amount <> 0 ';
          }
        }
        
      $dn_query               = "SELECT dn_amount  AS 'DR',outstanding_amount,DATE(dn_date) AS 'Date','DN' AS 'Type',dn_code AS 'Ref','DEBITNOTE' AS description, outstanding_amount AS 'OUTSTANDING','' AS 'CR' FROM cms_debitnote AS dn WHERE cust_code = '{$custCode}' AND cancelled = 'F' {$dn_date_cond}";

      if($is_QNE){
        $dn_query = "SELECT outstanding_amount  AS 'DR',outstanding_amount,DATE(dn_date) AS 'Date','DN' AS 'Type',dn_code AS 'Ref','DEBITNOTE' AS description, outstanding_amount AS 'OUTSTANDING','' AS 'CR' FROM cms_debitnote AS dn WHERE cust_code = '{$custCode}' AND cancelled = 'F' {$dn_date_cond}";
      }

      $cn_date_cond = "AND cn_date BETWEEN '{$dateFrom}' AND '{$dateTo}'";
      if($dateFrom == $dateTo){
        $cn_date_cond = " AND (cn_amount - cn_knockoff_amount) <> 0";
        if($is_SmartSQL){
          $cn_date_cond = " AND cn_knockoff_amount = 0 ";
        }
      }

      $cf_date_cond = "AND cf_date BETWEEN '{$dateFrom}' AND '{$dateTo}'";
      if($dateFrom == $dateTo){
        $cf_date_cond = " AND (cf_amount - cf_knockoff_amount) <> 0";
        if($is_SmartSQL){
          $cf_date_cond = " AND (cf_amount - cf_knockoff_amount)  <> 0 ";
        }
      }

      $cn_query = "SELECT cn_amount AS 'CR',cn_knockoff_amount,DATE(cn_date) AS 'Date','CN' AS 'Type',cn_code AS 'Ref','CREDITNOTE' AS description, '' AS 'DR',(cn_amount-cn_knockoff_amount) AS 'OUTSTANDING' FROM cms_creditnote AS cn WHERE cust_code = '{$custCode}' AND cancelled = 'F' {$cn_date_cond}";
        
      if($bad_calc_customer){
        $cn_query = "SELECT cn_amount AS 'CR',cn_knockoff_amount,DATE(cn_date) AS 'Date','CN' AS 'Type',cn_code AS 'Ref','CREDITNOTE' AS description, '' AS 'DR',(cn_amount-cn_knockoff_amount) AS 'OUTSTANDING' FROM cms_creditnote AS cn WHERE cust_code = '{$custCode}' AND cancelled = 'F'";
      }

      if($is_QNE){
        $cn_query = "SELECT (cn_amount-cn_knockoff_amount) AS 'CR',cn_knockoff_amount,DATE(cn_date) AS 'Date','CN' AS 'Type',cn_code AS 'Ref','CREDITNOTE' AS description, '' AS 'DR',(cn_amount-cn_knockoff_amount) AS 'OUTSTANDING' FROM cms_creditnote AS cn WHERE cust_code = '{$custCode}' AND cancelled = 'F' {$cn_date_cond}";
      }

        file_put_contents('ageing.log',$inv_query. ' | ' .$dn_query. ' | ' .$cn_query);

        $or_date_cond = " AND receipt_date BETWEEN '{$dateFrom}' AND '{$dateTo}'";
        if(($dateFrom == $dateTo)){
          $or_date_cond = ' AND (receipt_amount-receipt_knockoff_amount) <> 0 ';

          if($is_SQLAccounting || $is_QNE){
            $or_date_cond = ' AND (receipt_amount-receipt_knockoff_amount) <> 0 ';
          }
          if($is_SmartSQL){
            $or_date_cond = " AND receipt_knockoff_amount =0 ";
          }
        }

        $or_query               = "SELECT receipt_amount AS 'CR',receipt_knockoff_amount,DATE(receipt_date) AS 'Date','OR' AS 'Type', receipt_code AS 'Ref',
        IF(CONCAT(IFNULL(receipt_desc,''),IFNULL(cheque_no,'')) = '','RECEIPT',CONCAT(IFNULL(receipt_desc,''),IF(cheque_no = '','','\nCHEQUE NO:'),IFNULL(cheque_no,''))) AS description,'' AS 'DR',(receipt_amount-receipt_knockoff_amount) AS 'OUTSTANDING' FROM cms_receipt WHERE cust_code = '{$custCode}' AND cancelled = 'F' {$or_date_cond}";

        if($bad_calc_customer){
          $or_query               = "SELECT receipt_amount AS 'CR',receipt_knockoff_amount,DATE(receipt_date) AS 'Date','OR' AS 'Type', receipt_code AS 'Ref',
        IF(CONCAT(IFNULL(receipt_desc,''),IFNULL(cheque_no,'')) = '','RECEIPT',CONCAT(IFNULL(receipt_desc,''),IF(cheque_no = '','','\nCHEQUE NO:'),IFNULL(cheque_no,''))) AS description,'' AS 'DR',(receipt_amount-receipt_knockoff_amount) AS 'OUTSTANDING' FROM cms_receipt WHERE cust_code = '{$custCode}' AND cancelled = 'F'";
        }

        if($is_QNE){
          $or_query               = "SELECT (receipt_amount-receipt_knockoff_amount) AS 'CR',receipt_knockoff_amount,DATE(receipt_date) AS 'Date','OR' AS 'Type', receipt_code AS 'Ref',
          IF(CONCAT(IFNULL(receipt_desc,''),IFNULL(cheque_no,'')) = '','RECEIPT',CONCAT(IFNULL(receipt_desc,''),IF(cheque_no = '','','\nCHEQUE NO:'),IFNULL(cheque_no,''))) AS description,'' AS 'DR',(receipt_amount-receipt_knockoff_amount) AS 'OUTSTANDING' FROM cms_receipt WHERE cust_code = '{$custCode}' AND cancelled = 'F' {$or_date_cond}";
        }

        $cf_query = "SELECT (cf_amount - cf_knockoff_amount) AS 'DR',cf_knockoff_amount,DATE(cf_date) AS 'Date','CF' AS 'Type',cf_code AS 'Ref','CUSTOMER REFUND' AS description, '' AS 'CR',cf_knockoff_amount AS 'OUTSTANDING' FROM cms_customer_refund AS cf WHERE cust_code = '{$custCode}' AND cancelled = 'F' {$cf_date_cond}";
        
        if($bad_calc_customer){
          $cf_query = "SELECT (cf_amount - cf_knockoff_amount) AS 'DR',cf_knockoff_amount,DATE(cf_date) AS 'Date','CF' AS 'Type',cf_code AS 'Ref','CUSTOMER REFUND' AS description, '' AS 'CR', cf_knockoff_amount AS 'OUTSTANDING' FROM cms_customer_refund AS cf WHERE cust_code = '{$custCode}' AND cancelled = 'F'";
        }
  
        if($is_QNE){
          $cf_query = "SELECT (cf_amount - cf_knockoff_amount) AS 'DR',cf_knockoff_amount,DATE(cf_date) AS 'Date','CF' AS 'Type',cf_code AS 'Ref','CUSTOMER REFUND' AS description, '' AS 'CR', cf_knockoff_amount AS 'OUTSTANDING' FROM cms_customer_refund AS cf WHERE cust_code = '{$custCode}' AND cancelled = 'F' {$cf_date_cond}";
        }

        $postdatedQuery         = "SELECT * FROM cms_receipt WHERE cust_code = '{$custCode}' AND cancelled = 'F' AND DATE(receipt_date) > CURRENT_DATE() ORDER BY receipt_date DESC";


        $db->query($inv_query);

        $distinctKoList = array();

        if($db->get_num_rows() != 0){

            while($result = $db->fetch_array()){

                $doc_ref_ko = mysql_real_escape_string($result['Ref']);

                $knockOffDtl = isset($rcpCnKnockOff[$doc_ref_ko]) ? $rcpCnKnockOff[$doc_ref_ko] : array();
                $rcp_arr = array();
                $cn_arr = array();
                for ($i=0; $i < count($knockOffDtl); $i++) { 
                  $knockOff = $knockOffDtl[$i];
                  $knockOffJoinArr[] = $knockOff['Ref'];
                  $dtl = array(
                      "Date"          =>  $knockOff['Date'],
                      "Type"          =>  $knockOff['Type'],
                      "Ref"           =>  $knockOff['Ref'],
                      "Description"   =>  $knockOff['description'],
                      "DR"            =>  $knockOff['DR'],
                      "CR"            =>  $knockOff['CR'],
                      "Outstanding"   =>  $knockOff['OUTSTANDING'],
                      "Due"           =>  $knockOff['receipt_knockoff_amount'],
                      "key"           =>  monthAndYear($knockOff['Date'])
                  );
                  if($knockOff['type'] == 'r'){
                    array_push($rcp_arr,$dtl);
                  }else{
                    array_push($cn_arr,$dtl);
                  }
                  $distinctKoList [] = $knockOff['Ref'];
                }

                $credit_count = count(array_unique($distinctKoList));

                array_push($docMerged,array(
                    "Date"          =>  $result['Date'],
                    "Type"          =>  $result['Type'],
                    "Ref"           =>  $result['Ref'],
                    "Description"   =>  $result['description'],
                    "DR"            =>  $result['DR'],
                    "CR"            =>  $result['CR'],
                    "Outstanding"   =>  $result['OUTSTANDING'],
                    "Due"           =>  $result['outstanding_amount'],
                    "key"           =>  monthAndYear($result['Date']),
                    "receipt"       =>  $rcp_arr,
                    "credit"        =>  $cn_arr
                ));

                $debit_count++;
            }
        }

        $db->query($dn_query);

        if($db->get_num_rows() != 0){
            while($result = $db->fetch_array()){
                array_push($docMerged,array(
                    "Date"          =>  $result['Date'],
                    "Type"          =>  $result['Type'],
                    "Ref"           =>  $result['Ref'],
                    "Description"   =>  $result['description'],
                    "DR"            =>  $result['DR'],
                    "CR"            =>  $result['CR'],
                    "Outstanding"   =>  $result['OUTSTANDING'],
                    "Due"           =>  $result['outstanding_amount'],
                    "key"           =>  monthAndYear($result['Date'])
                ));
              $debit_count++;
            }
        }

        $db->query($cn_query);

        if($db->get_num_rows() != 0){
            while($result = $db->fetch_array()){
                if(!in_array($result['Ref'],$knockOffJoinArr)){
                  array_push($docMerged,array(
                      "Date"          =>  $result['Date'],
                      "Type"          =>  $result['Type'],
                      "Ref"           =>  $result['Ref'],
                      "Description"   =>  $result['description'],
                      "DR"            =>  $result['DR'],
                      "CR"            =>  $result['CR'],
                      "Outstanding"   =>  $result['OUTSTANDING'],
                      "Due"           =>  $result['cn_knockoff_amount'],
                      "key"           =>  monthAndYear($result['Date'])
                  ));
                }
                $credit_count++;
            }
        }

        if($client == 'hpoint'){
          file_put_contents('cf.log',$dn_query);
          $db->query($cf_query);

          if($db->get_num_rows() != 0){
              while($result = $db->fetch_array()){
                  array_push($docMerged,array(
                    "Date"          =>  $result['Date'],
                    "Type"          =>  $result['Type'],
                    "Ref"           =>  $result['Ref'],
                    "Description"   =>  $result['description'],
                    "DR"            =>  $result['DR'],
                    "CR"            =>  $result['CR'],
                    "Outstanding"   =>  $result['OUTSTANDING'],
                    "Due"           =>  $result['cf_knockoff_amount'],
                    "key"           =>  monthAndYear($result['Date'])
                ));
                  $debit_count++;
              }
          }
        }


        $db->query($or_query);

        if($db->get_num_rows() != 0){
            while($result = $db->fetch_array()){


              if($dateFrom == $dateTo){
                if(!in_array($result['Ref'],$knockOffJoinArr)){
                  array_push($docMerged,array(
                      "Date"          =>  $result['Date'],
                      "Type"          =>  $result['Type'],
                      "Ref"           =>  $result['Ref'],
                      "Description"   =>  $result['description'],
                      "DR"            =>  $result['DR'],
                      "CR"            =>  $result['CR'],
                      "Outstanding"   =>  $result['OUTSTANDING'],
                      "Due"           =>  $result['receipt_knockoff_amount'],
                      "key"           =>  monthAndYear($result['Date'])
                  ));
                }
              }else{
                if(!in_array($result['Ref'],$knockOffJoinArr)){
                  $invoices = array();
                  if(!in_array($result['Ref'],$distinctKoList)){
                    $knockOffArr = isset($rcpCnKnockOffReverse[$result['Ref']]) ? $rcpCnKnockOffReverse[$result['Ref']] : array();
                    for ($kk=0; $kk < count($knockOffArr); $kk++) { 
                      $knockOff = $knockOffArr[$kk];
                      $invoices[] = array(
                        "Date"          =>  $knockOff['invoice_date'],
                        "Type"          =>  $knockOff['RKOIN'],
                        "Ref"           =>  $knockOff['doc_ko_ref'],
                        "Description"   =>  'PAID',
                        "DR"            =>  $knockOff['invoice_amount'],
                        "CR"            =>  '0',
                        "Outstanding"   =>  '0',
                        "Due"           =>  '0',
                        "key"           =>  monthAndYear($knockOff['Date'])
                    ); 
                    }
                  }
                  array_push($docMerged,array(
                      "Date"          =>  $result['Date'],
                      "Type"          =>  $result['Type'],
                      "Ref"           =>  $result['Ref'],
                      "Description"   =>  $result['description'],
                      "DR"            =>  $result['DR'],
                      "CR"            =>  $result['CR'],
                      "Outstanding"   =>  $result['OUTSTANDING'],
                      "Due"           =>  $result['receipt_knockoff_amount'],
                      "key"           =>  monthAndYear($result['Date']),
                      "receipt"       =>  $invoices
                  ));
                  $credit_count++;
                }
              }
            }
        }

        // usort($docMerged, build_sorter("Date", "ASC"));
        if($client == 'gseries'){
          /* usort($docMerged, function($a, $b)
          {
              $t1 = strtotime($a['Date']);
              $t2 = strtotime($b['Date']);
              $res =  ($t1 < $t2) ? -1 : 1;
              if($res > 0){
                return strcmp($a['Ref'], $b['Ref']);
              }
          });
          file_put_contents("ageingReport.log",json_encode($docMerged)); */
          $json = $docMerged;//json_decode($json,true);
          usort($json, function($a, $b)
          {
              $t1 = strtotime($a['Date']);
              $t2 = strtotime($b['Date']);
              $res =  ($t1 < $t2) ? -1 : 1;
              return $res;
          });
          $spbm = array();
          $prev_month = 0; $prev_index = 0;
          foreach($json as $key=>$element){
            $new_month = intval(date("m",strtotime($element['Date'])));
            if ($new_month > $prev_month){
              $prev_month = $new_month;
              $prev_index = count($spbm);
            }
            $spbm[$prev_index][] = $element;
          }

          $docMerged = array();
          for($si = 0; $si < count($spbm); $si++){
            $document_arr = $spbm[$si];
            usort($document_arr, function($a, $b)
            {   
                if ($a['Type'] == $b['Type']){
                  return strcmp($a['Ref'],$b['Ref']);
                }
                $t1 = strtotime($a['Date']);
                $t2 = strtotime($b['Date']);
                $res =  ($t1 < $t2) ? -1 : 1;
                return $res;
            });
            for($dm = 0; $dm < count($document_arr); $dm++){
              $docMerged[] = $document_arr[$dm];
            }
          }
        }else{
          usort($docMerged, build_sorter("Date", "ASC"));
        }

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

        $extra_header = '';
        $extra_footer = '';

        $db->query("SELECT * FROM cms_mobile_module WHERE module = 'pdf_extra_header' OR module = 'pdf_extra_footer'");
        if($db->get_num_rows() != 0){
            while($result = $db->fetch_array()){
                if($result['module'] == 'pdf_extra_header'){
                    $extra_header = $result['status'];
                }
                if($result['module'] == 'pdf_extra_footer'){
                    $extra_footer = $result['status'];
                }
            }
        }

        $row_view = '';

        $balance = 0;
        for ($i=0; $i < count($docMerged); $i++) { 
            $each = $docMerged[$i];
            $receipt = $each['receipt'];
            $credit = $each['credit'];

            if($client == 'chillm_jb' && $each['Type'] == 'IN'){
              $db->query("select inv_udf from cms_invoice where invoice_code = '{$each['Ref']}'");
              if($db->get_num_rows() != 0){
                while($result = $db->fetch_array()){
                  $ref = json_decode($result['inv_udf'],true);
                }
              }
              file_put_contents('ag.log',$ref.PHP_EOL,FILE_APPEND);
              if($ref){
                $each['Description'] = ('Alt. ID '.$ref['refdocno']);
              }
            }

            if($is_Greenplus && $each['Type'] != 'IN'){
              continue;
            }
            
            if($is_SQLAccounting || $is_QNE){
              file_put_contents('AGING.log',1);
              if($each['Type'] == 'IN' || $each['Type'] == 'DN'){
                $balance += floatval($each['Outstanding']);
                $debit_sum += floatval($each['Outstanding']);
              }
              if($each['Type'] == 'CN' || $each['Type'] == 'OR'){
                $balance -= floatval($each['Outstanding']);
                $credit_sum += floatval($each['Outstanding']);
              }
            }else if($is_SmartSQL){
              file_put_contents('AGING.log',2);
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
              $each['Type'] == 'CF' ? file_put_contents('AGING.log',json_encode($each)) : '';
              $balance += floatval($each['DR']);
              // if($each['Type'] == 'IN' && count($credit) == 0){
              //   $balance -= floatval($each['Outstanding']);
              // }

              if($each['Type'] == 'OR' || $each['Type'] == 'CN'){
                $balance -= floatval($each['Outstanding']);
              }
            }

            $rowStyle = '';
            if(count($receipt) == 0 && count($credit) == 0){
              $rowStyle = 'border-bottom:1px solid lightgrey;';
            }
            if($doc_row){
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
                    date("d/m/Y",strtotime($each['Date'])),
                    $each['Ref'],
                    $each['Description'],
                    money($each['DR']),
                    money($each['CR']),
                    money($balance,
                    true
                  )
                ),$doc_row);

              $row_view .= $temp_row;
            }else{
                $row_view .= '
                  <tr style="'.$rowStyle.'">
                      <td align="center">'.date("d/m/Y",strtotime($each['Date'])).'</td>
                      <td align="center">'.$each['Type'].'</td>
                      <td align="center">'.$each['Ref'].'</td>
                      <td align="center" style="font-size:12px">'.$each['Description'].'</td>
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
                if($is_AutoCountKO && $each['Type'] != 'OR'){
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
                        <td align="center" style="font-size:12px">'.$rcp['Description'].'</td>
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
               if($is_AutoCountKO){
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
        /** TOTAL VIEW **/
        // $row_view .= '
        //     <tr style="font-style:italic;color:grey;'.$rowStyle.'">
        //         <td align="center"></td>
        //         <td align="center"></td>
        //         <td align="center"></td>
        //         <td align="center"></td>
        //         <td align="center">'.money($debit_sum).'</td>
        //         <td align="center">'.money($credit_sum).'</td>
        //         <td align="right" style="padding-right:10px"></td>
        //     </tr>
        // ';

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
        $html = str_replace('@company_logo',$logo_url,$html);
        $html = str_replace('@to_date',date_format(date_create($dateTo),'d/m/Y'),$html);
        $html = str_replace('@debit_count',$debit_count,$html);
        $html = str_replace('@debit_sum',$debit_sum,$html);
        $html = str_replace('@credit_count',$credit_count,$html);
        $html = str_replace('@credit_sum',$credit_sum,$html);
        $ifNegativeBalance = $balance < 0 ? $balance * -1 : $balance;
        $html = str_replace('@closing_balance',$balance > 0 ? $balance : "({$ifNegativeBalance})",$html);
        $html = str_replace('@extra_header',$extra_header,$html);
        $html = str_replace('@extra_footer',$extra_footer,$html);
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

        /* for ($i=0; $i < 6; $i++) { 
            if(isset($monthlyAging[$i])){
              $key = '@'.$i.'_month';
              $each = $monthlyAging[$i];
              $monthlyAgingHtml = str_replace($key,$each['value'],$monthlyAgingHtml);
            }else{
              $key = '@'.$i.'_month';
              $monthlyAgingHtml = str_replace($key,'',$monthlyAgingHtml);
            }
        } */
        $keyToRemove = array();
        $monthCounter = 0;
        for($i = 0; $i < 6; $i++){
          $monthCounter += 1;
          $key = '@'.$i.'_month';
          $expectedKey = keyName(date('Y-m-d',strtotime("{$i} months ago")));
          $keyToRemove[] = $expectedKey;
          $monthlyAgingHtml = str_replace($key,$expectedKey,$monthlyAgingHtml);
        }
        for($i = 0; $i < count($monthlyAging); $i++){
          $each = $monthlyAging[$i];
          $monthlyAgingHtml = str_replace($each['key'],$each['value'],$monthlyAgingHtml);
        }
        for($i = 0; $i < count($keyToRemove); $i++){
          $key = $keyToRemove[$i];
          $monthlyAgingHtml = str_replace($key,'',$monthlyAgingHtml);
        }
        
        $monthlyAgingCount = count($monthlyAging);
        if($monthlyAgingCount > 5){
          
            $restTotal = 0;
            for ($i=5; $i < $monthlyAgingCount; $i++){
              $each = $monthlyAging[$i];
              $restTotal += floatMoney($each['value']);
            }
            
            // $monthlyAgingHtml .= '
                    
            //   <div class="Column">
            //       <p style="padding:2px;line-height:0px;font-size:13px;">6Mnths Above</p>
            //       <hr id="monthlyDivider"/>
            //       <p style="padding:2px;line-height:0px;font-size:13px;">'.money($restTotal,true).'</p>
            //   </div>
        
            // ';
            $monthlyAgingHtml = str_replace('@rest_month',money($restTotal,true),$monthlyAgingHtml);
        }else{
          $monthlyAgingHtml = str_replace('@rest_month','',$monthlyAgingHtml);
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
          $zero_statement = str_replace('@company_logo',$logo_url,$zero_statement);
          $zero_statement = str_replace('@client_website',$client_info['website'],$zero_statement);
          $zero_statement = str_replace('@client_phone',$client_info['phone'],$zero_statement);
          $zero_statement = str_replace('@client_email',$client_info['email'],$zero_statement);
          $zero_statement = str_replace('@client_addr',$client_addr,$zero_statement);
          $zero_statement = str_replace('@client_sub_name',$client_info['sub_name'],$zero_statement);
          $zero_statement = str_replace('@client_name',$client_name,$zero_statement);
          echo json_encode(array('statement'=>base64_encode($zero_statement)));
          return;
        }

        if($_GET['debug'] == 1){
          echo json_encode($groupedDoc);
          echo json_encode($monthlyAging);
          echo $html;
          return;
        }
        file_put_contents('html.log',base64_encode($html));
        echo json_encode(array('statement'=>base64_encode($html)));
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
    $month_names                = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec'];
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
    if($force && !$amount) return '-';
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
