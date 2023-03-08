<?php
require_once('./model/DB_class.php');
require_once('./MoneyToWord.php');
header('Content-Type: text/html; charset=utf-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
//https://easysales.asia/esv2/webview/iManage/testAgingView.php?client=moss&cust_code=300-X00066&date_from=2021-10-31&date_to=2021-10-31
date_default_timezone_set('Asia/Kuala_Lumpur');

$settings                       = parse_ini_file('../config.ini',true);

$custCode                       = '';
$client                         = '';
$connection                     = NULL;

$config_ini = parse_ini_file('../config.ini',true);
$customization_nm = array('nmayang','nmayang_eng','agrox');

$_cust_code = get('cust_code');
$_client = get('client');
$_salesperson_id = get('salesperson_id');
$_date_to =  get('date_to');
$_date_from =  get('date_from');

if(!$_cust_code || !$_client){
    die('Not enough param');
}
$customization_nm           = in_array($_client,$customization_nm);
$db_settings                = $config_ini[$_client];
$db_user                    = $db_settings['user'];
$db_pass                    = $db_settings['password'];
$db_name                    = $db_settings['db'];
$db_host                    = isset($db_settings['host']) ? $db_settings['host'] : 'easysales.asia';

$db = new DB();

$con_1 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);
if(!$con_1){
    echo "DB Error";
    die();
}

if(!$_salesperson_id){
    $db->query("select salesperson_id from cms_customer c left join cms_customer_salesperson sp on sp.customer_id = c.cust_id and sp.active_status = 1
    where cust_code = '".$_cust_code."'");
    while($result = $db->fetch_array()){
        $_salesperson_id = $result['salesperson_id'];
    }
}

$proj_no = '';
$db->query("select proj_no from cms_login where login_id = '{$_salesperson_id}'");
while($result = $db->fetch_array()){
    $proj_no = $result['proj_no'];
}

$custCode               = mysql_real_escape_string($_cust_code);
$dateFrom               = mysql_real_escape_string($_date_from);
$dateTo                 = mysql_real_escape_string($_date_to);


$db->query("SELECT status FROM cms_mobile_module WHERE module = 'app_client'");
while($result = $db->fetch_array()){
    $company_name = $result['status'];
}
$db->query("SELECT status FROM cms_mobile_module WHERE module = 'app_client_info'");
while($result = $db->fetch_array()){
    $cust_info = json_decode($result['status'],true);
    $sub_name = $cust_info['sub_name'];
    $address = $cust_info['address'];
    $city = $cust_info['city'];
    $state = $cust_info['state'];
    $zipcode = $cust_info['zipcode'];
    $phone = $cust_info['phone'];
    $fax = $cust_info['fax'];
    $email = isset($cust_info['acc_email']) ? $cust_info['acc_email'] : $cust_info['email'];
    $website = $cust_info['website'];
}
if(!empty($proj_no)){
    $db->query("SELECT status FROM cms_mobile_module WHERE module = 'app_statement_header'");
    while($result = $db->fetch_array()){
        $status = json_decode($result['status'],true);
        if(isset($status[$proj_no])){
            $cust_info = $status[$proj_no];

            $sub_name = $cust_info['sub_name'];
            $address = $cust_info['address'];
            $city = $cust_info['city'];
            $state = $cust_info['state'];
            $zipcode = $cust_info['zipcode'];
            $phone = $cust_info['phone'];
            $fax = $cust_info['fax'];
            $email = isset($cust_info['acc_email']) ? $cust_info['acc_email'] : $cust_info['email'];
            $website = $cust_info['website'];
        }
    }
}

$db->query("SELECT name FROM cms_login WHERE login_id = '".$_salesperson_id."'");
while($result = $db->fetch_array()){
    $salesperson_name = $customization_nm ? explode('(',$result['name'])[0] : $result['name'];
}

$db->query("SELECT cust_company_name, cust_incharge_person, cust_tel, cust_fax, billing_address1, billing_address2, billing_address3, billing_address4, termcode FROM cms_customer WHERE cust_code = '".$custCode."'");
while($result = $db->fetch_array()){
    $cust_company_name = $result['cust_company_name'];
    $cust_incharge_person = $result['cust_incharge_person'];
    $cust_tel = $result['cust_tel'];
    $cust_fax = $result['cust_fax'];
    $billing_address1 = $result['billing_address1'];
    $billing_address2 = $result['billing_address2'];
    $billing_address3 = $result['billing_address3'];
    $billing_address4 = $customization_nm ? '' : $result['billing_address4'];
    $termcode = $result['termcode'];
}

$branch_data = null;
if($customization_nm){
    $db->query("SELECT * FROM cms_customer_branch WHERE cust_code = '".$custCode."' order by branch_id limit 1;");
    while($result = $db->fetch_array()){
        $branch_data = $result;
    }
    $cust_incharge_person = $branch_data['branch_attn'];
}


$rcpKnockOff = "SELECT doc_ko_ref, receipt_amount AS 'CR', receipt_knockoff_amount,DATE(receipt_date) AS 'Date','Credit' AS 'Type', receipt_code AS 'Ref', IF(CONCAT(IFNULL(receipt_desc,''),IFNULL(cheque_no,'')) = '','RECEIPT',CONCAT(IFNULL(receipt_desc,''),IF(cheque_no = '','','\nCHEQUE NO:'),IFNULL(cheque_no,''))) AS description, '' AS 'DR',(receipt_amount-receipt_knockoff_amount) AS 'OUTSTANDING', ko.doc_amount FROM cms_customer_ageing_ko AS ko LEFT JOIN cms_receipt r ON r.receipt_code = ko.doc_code WHERE doc_type = 'OR' AND cancelled = 'F' AND cust_code = '{$custCode}' AND doc_date >= '{$dateFrom}' AND active_status = 1";
$cnKnockOff = "SELECT doc_ko_ref, cn_amount AS 'CR', cn_knockoff_amount,DATE(cn_date) AS 'Date','Credit' AS 'Type',cn_code AS 'Ref','CREDITNOTE' AS description, '' AS 'DR', (cn_amount-cn_knockoff_amount) AS 'OUTSTANDING', ko.doc_amount FROM cms_customer_ageing_ko AS ko LEFT JOIN cms_creditnote c ON c.cn_code = ko.doc_code WHERE doc_type = 'CN' AND cancelled = 'F' AND cust_code = '{$custCode}' AND doc_date >= '{$dateFrom}' AND active_status = 1";
$cfKnockOff =  "SELECT doc_ko_ref, cf_amount AS 'CR', cf_knockoff_amount,DATE(cf_date) AS 'Date','Debit' AS 'Type', cf_code AS 'Ref', 'CUSTOMER REFUND 'AS description, '' AS 'DR',(cf_amount-cf_knockoff_amount) AS 'OUTSTANDING', ko.doc_amount FROM cms_customer_ageing_ko AS ko LEFT JOIN cms_customer_refund r ON r.cf_code = ko.doc_code WHERE doc_type = 'CF' AND cancelled = 'F' AND cust_code = '{$custCode}' AND doc_date >= '{$dateFrom}' AND active_status = 1";

$allKo = array();
$koRefCodes = array();
$dbClone = clone $db;

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



$debit_count = 0; $debit_sum = 0; $credit_count = 0; $credit_sum = 0;

$inv_query = "SELECT invoice_amount AS 'DR',outstanding_amount,DATE(invoice_date) AS 'Date','IN' AS 'Type',invoice_code AS 'Ref','INVOICE' AS description, outstanding_amount AS 'OUTSTANDING','' AS 'CR' FROM cms_invoice AS inv WHERE cust_code = '{$custCode}' AND cancelled = 'F' AND (outstanding_amount <> 0  AND invoice_date <= '{$dateTo}' OR invoice_code IN ('".implode("','",$koRefCodes)."'))";

$dn_query = "SELECT dn_amount  AS 'DR',outstanding_amount,DATE(dn_date) AS 'Date','DN' AS 'Type',dn_code AS 'Ref','DEBITNOTE' AS description, dn_amount AS 'OUTSTANDING','' AS 'CR' FROM cms_debitnote AS dn WHERE cust_code = '{$custCode}' AND cancelled = 'F'  AND dn_date <='{$dateTo}' AND (outstanding_amount > 0 OR dn_code IN ('".implode("','",$koRefCodes)."'))";

$cn_query = "SELECT cn_amount AS 'CR',cn_knockoff_amount,DATE(cn_date) AS 'Date','CN' AS 'Type',cn_code AS 'Ref','CREDITNOTE' AS description, '' AS 'DR',(cn_amount-cn_knockoff_amount) AS 'OUTSTANDING' FROM cms_creditnote AS cn WHERE cust_code = '{$custCode}' AND cancelled = 'F' AND cn_date <= '{$dateTo}' AND ((cn_amount - cn_knockoff_amount) <> 0 OR cn_code IN ('".implode("','",$koRefCodes)."'))";

$or_query = "SELECT receipt_amount AS 'CR',receipt_knockoff_amount,DATE(receipt_date) AS 'Date','OR' AS 'Type', receipt_code AS 'Ref',
IF(CONCAT(IFNULL(receipt_desc,''),IFNULL(cheque_no,'')) = '','RECEIPT',CONCAT(IFNULL(receipt_desc,''),IF(cheque_no = '','','\nCHEQUE NO:'),IFNULL(cheque_no,''))) AS description,'' AS 'DR',(receipt_amount-receipt_knockoff_amount) AS 'OUTSTANDING' FROM cms_receipt WHERE cust_code = '{$custCode}' AND cancelled = 'F'  AND receipt_date <= '{$dateTo}' AND ((receipt_amount-receipt_knockoff_amount) <> 0 OR receipt_code IN ('".implode("','",$koRefCodes)."'))";

$db->query($inv_query);

$distinctKoList = array();

$out_array = array();

if($db->get_num_rows() != 0){
  $total = 0;
  while($result = $db->fetch_array()){

    $doc_ref_ko = mysql_real_escape_string($result['Ref']);

    $knockOffDtl = isset($allKo[$doc_ref_ko]) ? $allKo[$doc_ref_ko] : array();
    
    $aestric= '';
    if($result['outstanding_amount'] == 0){
      $aestric= '*';
    }else if($result['outstanding_amount'] != $result['DR']){
      $aestric= 'P';
    }else{
      $aestric= '';
    }

    for ($i=0; $i < count($knockOffDtl); $i++) { 
      $knockOff = $knockOffDtl[$i];

      $doc_amount = $knockOff['doc_amount'];
      if($doc_amount > 0){
        $result['outstanding_amount'] += $doc_amount;
      }
    }

    $credit_count = count(array_unique($distinctKoList));

    array_push($out_array,array(
      "Date"          =>  $result['Date'],
      "Ref"           =>  $result['Ref'],
      "Description"   =>  $result['description'],
      "Due"           =>  $result['outstanding_amount'],
      "Type"          => 'Debit',
      "Aestric"       => $aestric
    ));

    $debit_count++;
  }
}

$db->query($dn_query);

if($db->get_num_rows() != 0){
  while($result = $db->fetch_array()){
    $doc_ref_ko = mysql_real_escape_string($result['Ref']);
    $knockOffDtl = isset($allKo[$doc_ref_ko]) ? $allKo[$doc_ref_ko] : array();

    $aestric= '';
    // if($result['outstanding_amount'] == 0){
    //   $aestric= '*';
    // }else if($result['outstanding_amount'] != $result['DR']){
    //   $aestric= 'P';
    // }else{
    //   $aestric= '';
    // }

    for ($i=0; $i < count($knockOffDtl); $i++) { 
      $knockOff = $knockOffDtl[$i];

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
      "Type"          => 'Debit',
      "Aestric"       => $aestric
    ));

  }
}

$db->query($cn_query);

if($db->get_num_rows() != 0){

  while($result = $db->fetch_array()){

    $doc_ref_ko = mysql_real_escape_string($result['Ref']);

    $knockOffDtl = isset($allKo[$doc_ref_ko]) ? $allKo[$doc_ref_ko] : array();

    $aestric= '';
    // if($result['cn_knockoff_amount'] == 0){
    //   $aestric= '*';
    // }else if($result['cn_knockoff_amount'] != $result['DR']){
    //   $aestric= 'P';
    // }else{
    //   $aestric= '';
    // }

    for ($i=0; $i < count($knockOffDtl); $i++) { 
      $knockOff = $knockOffDtl[$i];
      $doc_amount = $knockOff['doc_amount'];
      if($doc_amount > 0){

        $result['OUTSTANDING'] += $doc_amount;
      }
    }

    array_push($out_array,array(
      "Date"          =>  $result['Date'],
      "Ref"           =>  $result['Ref'],
      "Description"   =>  $result['description'],
      "Due"           =>  $result['OUTSTANDING'],
      "Type"          => 'Credit',
      "Aestric"       => $aestric
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

    $aestric= '';
    // if($result['receipt_knockoff_amount'] == 0){
    //   $aestric= '*';
    // }else if($result['receipt_knockoff_amount'] != $result['DR']){
    //   $aestric= 'P';
    // }else{
    //   $aestric= '';
    // }
    
    for ($i=0; $i < count($knockOffDtl); $i++) { 
      $knockOff = $knockOffDtl[$i];

      $doc_amount = $knockOff['doc_amount'];
      if($doc_amount > 0){
        $result['OUTSTANDING'] += $doc_amount;
      }
    }

    array_push($out_array,array(
      "Date"          =>  $result['Date'],
      "Ref"           =>  $result['Ref'],
      "Description"   =>  $result['description'],
      "Due"           =>  $result['OUTSTANDING'],
      "Type"          => 'Credit',
      "Aestric"       => $aestric
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

//echo json_encode($out_array);return;

usort($out_array, build_sorter("Date", "ASC"));

$_to_date_and_1 = date('Y-m-d',strtotime($dateTo.' +1 day'));
$post_dated_q = " (date(receipt_date) between '{$_to_date_and_1}' and current_date()) and ";

$post_dated_q = "select *, date_format(receipt_date,'%d/%m/%Y') as formatted_date from cms_receipt where {$post_dated_q} cust_code = '{$custCode}' and cancelled = 'F'";

$db->query($post_dated_q);

$receipt_codes = array();

while($result = $db->fetch_array()){
  $post_dated_rcp[] = array(
      "type" => "RP",
      "desc" => $result['receipt_desc'],
      "date" => $result['formatted_date'],
      "amount" => $result['receipt_amount'],
      "id" => $result['receipt_code'],
      "cheque" => $result['cheque_no'],
      "balance" => $result['receipt_amount'] - $result['receipt_knockoff_amount'],
      "unique"=>"RP".$result['receipt_code']
  );
  $receipt_codes[] = "'{$result['receipt_code']}'";
}

// echo json_encode($out_array);


// echo '<table><tr><th>DATE</th><th>REF</th><th>DESC</th><th>DEBIT</th><th>CREDIT</th><th>AMOUNT</th></tr>';
$cumulative_total = 0;
$debit_count = 0;$total_debit = 0;$credit_count = 0;$total_credit = 0;

$table = '';
for ($i=0; $i < count($out_array); $i++) { 
  $row = $out_array[$i];

  $paid = '';
  if(!empty($row['Aestric'])){
    $paid = '<label style="font-size:9px">'.$row['Aestric'].'</label>';
  }
  $debit = '';
  $credit = '';
  if(strtotime($row['Date']) <= strtotime($dateTo)){
    if($row['Type'] == 'Debit'){
      $debit_count ++;
      $total_debit += $row['Due'];
      $cumulative_total += $row['Due'];
      $debit = number_format($row['Due'],2);
      // echo '<tr>
      //         <td>'.$row['Date'].'</td>
      //         <td>'.$row['Ref'].'</td>
      //         <td>'.$row['Description'].'</td>
      //         <td>'.$row['Due'].'</td>
      //         <td></td>
      //         <td>'.$cumulative_total.'</td>
      //       </tr>';
    }else{
      $credit_count ++;
      $total_credit += $row['Due'];
      $cumulative_total -= $row['Due'];
      $credit = number_format($row['Due'],2);
      // echo '<tr>
      //         <td>'.$row['Date'].'</td>
      //         <td>'.$row['Ref'].'</td>
      //         <td>'.$row['Description'].'</td>
      //         <td></td>
      //         <td>'.$row['Due'].'</td>
      //         <td>'.$cumulative_total.'</td>
      //       </tr>';
    }

    $table .=  '<tr>
    <td style="text-align:center;font-size:12px;">'. date('d/m/Y',strtotime($row['Date'])).'</td>
    <td></td>
    <td style="font-size:12px;">
        <p style="font-size:10px;margin:0px;font-weight:500;text-align:center;">'.$row['Ref'].'</p>';
    if($row['cheque']){
        $table .=  '<p style="font-size:10px;margin:0px;font-weight:500;text-align:center;">'.$row['cheque'].'</p>';
    }

    $table .=     '</td>
    
            <td style="text-align:center;font-size:12px;white-space:pre-line;" colspan="3">'.$row['Description'].'</td>
            
            <td style="text-align:center;font-size:12px;">'.$debit.'</td>
            
            <td style="text-align:center;font-size:12px;">'.$credit.'</td>
            
            <td style="text-align:right;font-size:12px;">'.isPositive($cumulative_total).$paid.'</td>
        </tr>';
  }
}
$balance = $total_debit - $total_credit;


$view[] =
    '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3pro.css">
            <meta http-equiv="X-UA-Compatible" content="ie=edge">
            <title>EasyTech</title>
            <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
            <style type="text/css">
            * {
              font-weight:500;
            }
            @media print {
                table{
                    border-collapse:collapse;
                }
                tr td{
                    page-break-inside: avoid;
                    white-space: nowrap;
                }
            }
            .break {
                page-break-above: avoid !important;
            }
            </style>
        </head>
        <body style="padding:5px; width:100%;">';

$view[] =  '<div style="text-align:center;width:100%;">
        <p style="font-size:20px;margin:0px;font-weight:bold;">'.$company_name.'<br><label style="font-size:10px;font-weight:500">'.$sub_name.'</label></p>
        <p style="font-size:12px;margin:0px;">'.$address.'</p>
        <p style="font-size:12px;margin:0px;">'.$zipcode.' '.$city.' '.$state.'</p>
        <p style="font-size:12px;margin:0px;">Tel.: '.$phone.' Fax: '.$fax.'</p>
        <p style="font-size:12px;margin:0px;">Email: '.$email.'</p>
        <hr style="border-top:1px solid black;margin:0px;">
    </div>';

$branch_html = '';
if($branch_data){
    $_branch_code = $branch_data['branch_code'];
    $_branch_name = $branch_data['branch_name'];

    $branch_name = str_replace("({$_branch_code})",'',$_branch_name);
    $branch_name = trim($branch_name);
    if($branch_name != 'BILLING'){
        $branch_html = '<p style="font-size:12px;margin:0px;font-weight:bold;">SITE - '.$branch_name.'</p>';
    }
}

$view[] =  '<div style="padding:5px;width:100%;">
        <div style="width:60%;float:left;">
            <p style="font-size:10px;margin:0px;">Customer</p>
            <p style="font-size:12px;margin:0px;font-weight:bold;">'.$cust_company_name.'</p>
            <p style="font-size:12px;margin:0px;">'.$billing_address1.'</p>
            <p style="font-size:12px;margin:0px;">'.$billing_address2.'</p>
            <p style="font-size:12px;margin:0px;">'.$billing_address3.'</p>
            <p style="font-size:12px;margin:0px;">'.$billing_address4.'</p>
            <p style="font-size:12px;margin:0px;">Tel '.$cust_tel.'</p>
            <p style="font-size:12px;margin:0px;">Fax '.$cust_fax.'</p>
            '.$branch_html.'
        </div>
        <div style="width:39%;float:left;border:1px solid black;">
            <p style="font-size:14pt;margin:0px;border-bottom:1px solid black;text-align:center;">Statement of Account</p>
            <br>
            <p style="font-size:12px;margin:0px;float:left;margin-left:10px;">Total Debit ('.number_format($debit_count).')</p>
            <p style="font-size:12px;margin:0px;float:right;margin-right:10px;">'.number_format($total_debit,2).'</p>
            <br>
            <p style="font-size:12px;margin:0px;float:left;margin-left:10px;">Total Credit ('.number_format($credit_count).')</p>
            <p style="font-size:12px;margin:0px;float:right;margin-right:10px;">'.number_format($total_credit,2).'</p>
            <br>
            <hr style="border-top:2px solid black;margin:0px;">
            <p style="font-size:12px;margin:10px;float:left;">Closing Balance</p>
            <p style="font-size:12px;margin:10px;float:right;">'.number_format($balance,2).'</p>
        </div>
    </div>';


$view[] =  '<table style="width:99%;table-layout: fixed;">
        <tr>
          <td style="width:10%"></td>
          <td style="width:10%"></td>
          <td style="width:15%"></td>
          <td style="width:10%"></td>
          <td style="width:15%"></td>
          <td style="width:10%"></td>
          <td style="width:10%"></td>
          <td style="width:10%"></td>
          <td style="width:10%"></td>
        </tr>
        <tr>
            <td colspan=9></td>
        </tr>
        <tr>
            <td colspan=9 style="border-top:1px solid black;"></td>
        </tr>
        <tr>
            <th colspan=2>
                <p style="font-size:12px;margin:0px;font-weight:500;">Attention</p>
                <p style="font-size:10px;margin:0px;">'.ellipse($cust_incharge_person).'</p>
            </th>
            <th colspan=2>
                <p style="font-size:12px;margin:0px;font-weight:500;">Customer Account</p>
                <p style="font-size:12px;margin:0px;">'.$custCode.'</p>
            </th>
            <th colspan=2>
                <p style="font-size:12px;margin:0px;font-weight:500;">Sales Executive</p>
                <p style="font-size:'.(strlen($salesperson_name) > 18 ? 10 : 12) .'px;margin:0px;text-align:center;">'.$salesperson_name.'</p>
            </th>
            <th>
                <p style="font-size:12px;margin:0px;font-weight:500;">Currency</p>
                <p style="font-size:12px;margin:0px;">RM</p>
            </th>
            <th colspan=2 style="border:1px solid black;">
                <p style="font-size:12px;margin:0px;float:left;font-weight:500;">Term</p>
                <p style="font-size:12px;margin:0px;float:right;font-weight:500;">Date</p><br>
                <p style="font-size:12px;margin:0px;float:left;">'.$termcode.'</p>
                <p style="font-size:12px;margin:0px;float:right;">'.($dateTo ? date('d/m/Y',strtotime($dateTo)) : date("d/m/Y")).'</p>
            </th>
        </tr>
        <tr>
            <td colspan=9 style="border-bottom:1px solid black;"></td>
        </tr>';
        
$view[] =  '<tr>
        <td style="text-align:center;font-size:10px;font-weight:bold">Date</td>
        <td></td>
        <td style="text-align:center;font-size:10px;font-weight:bold">References</td>
        
        <td style="text-align:center;font-size:10px;font-weight:bold" colspan="3">Description</td>
        
        <td style="text-align:center;font-size:10px;font-weight:bold">Debit</td>
        
        <td style="text-align:center;font-size:10px;font-weight:bold">Credit</td>
        
        <td style="text-align:right;font-size:10px;font-weight:bold">Balance</td>
    </tr>';

$view[] = $table;

if(count($post_dated_rcp) > 0){
  $rows = '
      <tr>
          <td style="border-bottom:1px solid black;width:20%;text-align:left;font-size:10px;">Date</td>
          <td style="border-bottom:1px solid black;width:20%;text-align:left;font-size:10px;">Cheque Num.</td>
          <td style="border-bottom:1px solid black;width:20%;text-align:left;font-size:10px;">OR No.</td>
          <td style="border-bottom:1px solid black;width:20%;padding:2px;font-size:10px;">Amount</td>
          <td style="border-bottom:1px solid black;width:20%;padding:2px;font-size:10px;">Balance</td>
      </tr>
  ';
  usort($post_dated_rcp, function ($a, $b) {
      $dateA = DateTime::createFromFormat('d/m/Y', $a['date']);
      $dateB = DateTime::createFromFormat('d/m/Y', $b['date']);
      return $dateA >= $dateB;
  });
  $pch_balance = 0;
  for ($p=0; $p < count($post_dated_rcp); $p++) {
      $pcheque = $post_dated_rcp[$p];
      $pch_balance += floatval($pcheque['amount']);
      $rows .= '
          <tr>
              <td style="width:20%;padding:0px;font-size:12px;text-align:left;">'.$pcheque['date'].'</td>
              <td style="width:20%;padding:0px;font-size:12px;text-align:left;">'.$pcheque['cheque'].'</td>
              <td style="width:20%;padding:0px;font-size:12px;text-align:left;">'.$pcheque['id'].'</td>
              <td style="width:20%;padding:0px;font-size:12px;">'.number_format($pcheque['amount'],2).'</td>
              <td style="width:20%;padding:0px;font-size:12px;">'.number_format($pch_balance,2).'</td>
          </tr>
      ';
  }
  $view[] = '
          <tr>
              <td colspan=9></td>
          </tr>
          <tr>
              <td colspan=9></td>
          </tr>
          <tr><td colspan=9>
              <div style="width:80%;margin:auto;font-size:12px;color:black;font-weight:bold">Post Dated Cheque Received</div>
              <table style="border-collapse: collapse;width:80%;text-align:right;margin:auto;">'.$rows.'</table>
          </td></tr>
          <tr>
              <td colspan=9></td>
          </tr>
          <tr>
              <td colspan=9></td>
          </tr>
  ';
}

$view[] =  '<tr><td colspan=9 style="border-top:1px solid black;"></td></tr>
<tr><td colspan=7 style="font-size:12px;"><div style="width:100%;white-space:pre-line">'.moneyInWord($balance).'</div></td><td style="text-align:center;">RM :</td><td style="text-align:right;font-weight:bold">'.isPositive($balance).'</td></table><br>';

$loop_balance_array = array();

$curr_mth = $dateTo ? date('Y-m-01',strtotime($dateTo)) : date('Y-m-01');
for($k = 0; $k < 6; $k ++){
    $loop_iv_amount = 0;
    $loop_cn_amount = 0;
    $loop_dn_amount = 0;
    $loop_rp_amount = 0;

    $decided_month = date('Y-m-d',strtotime($curr_mth.'-'.$k.' months'));
    $start = $k == 5 ? '2012-01-01 23:59:59' : date('Y-m-01 00:00:00',strtotime($decided_month));
    $end = date('Y-m-t 23:59:59',strtotime($decided_month));
    // if($k == 0){
    //     if($dont_show_ko){
    //         $loop_inv_query = "SELECT SUM(outstanding_amount) as amount FROM cms_invoice WHERE (invoice_date BETWEEN '{$start}' AND '{$end}') AND cust_code = '".$custCode."' AND cancelled = 'F'";
    //     }else{
    //         $loop_inv_query = "select SUM(outstanding_amount) as amount from (
    //             SELECT if(date(doc_date) > '{$dateTo}',outstanding_amount+doc_amount,outstanding_amount) as outstanding_amount FROM cms_invoice i
    //     LEFT JOIN cms_customer_ageing_ko k ON k.doc_ko_ref = i.invoice_code
    //     where (invoice_date BETWEEN '{$start}' AND '{$end}') and cancelled = 'F'
    //     and cust_code = '{$custCode}' order by invoice_date
    //                 )d where outstanding_amount <> 0;";
    //     }
    // }else{
    //     $loop_inv_query = "SELECT SUM(outstanding_amount) as amount FROM cms_invoice WHERE (invoice_date BETWEEN '{$start}' AND '{$end}') AND cust_code = '".$custCode."' AND cancelled = 'F'";
    // }

    if($k == 0){
      $loop_inv_query = "select SUM(outstanding_amount) as amount from (
        SELECT if(date(doc_date) > '{$dateTo}',outstanding_amount+doc_amount,outstanding_amount) as outstanding_amount FROM cms_invoice i
      LEFT JOIN cms_customer_ageing_ko k ON k.doc_ko_ref = i.invoice_code
      where (invoice_date BETWEEN '{$start}' AND '{$dateTo}') and cancelled = 'F'
      and cust_code = '{$custCode}' GROUP BY invoice_code ORDER BY invoice_date
                )d where outstanding_amount <> 0;";
    }else{
      $loop_inv_query = "select SUM(outstanding_amount) as amount from (
        SELECT if(date(doc_date) > '{$dateTo}',outstanding_amount+doc_amount,outstanding_amount) as outstanding_amount FROM cms_invoice i
      LEFT JOIN cms_customer_ageing_ko k ON k.doc_ko_ref = i.invoice_code
      where (invoice_date BETWEEN '{$start}' AND '{$end}') and cancelled = 'F'
      and cust_code = '{$custCode}' GROUP BY invoice_code ORDER BY invoice_date
                )d where outstanding_amount <> 0;";
    }
 

    $loop_dn_query = "SELECT SUM(outstanding_amount) as amount FROM cms_debitnote WHERE (dn_date BETWEEN '{$start}' AND '{$end}') AND cust_code = '".$custCode."' AND cancelled = 'F'";

    $loop_cn_query = "SELECT SUM(cn_amount-cn_knockoff_amount) as amount FROM cms_creditnote WHERE (cn_date BETWEEN '{$start}' AND '{$end}') AND cust_code = '".$custCode."' AND cancelled = 'F'";
    //and cn_code not in (select doc_code from cms_customer_ageing_ko where active_status = 1)";

    $loop_rp_query = "SELECT SUM(receipt_amount-receipt_knockoff_amount) as amount FROM cms_receipt WHERE (receipt_date BETWEEN '{$start}' AND '{$end}') AND cust_code = '".$custCode."' AND cancelled = 'F'";

    $db->query($loop_inv_query);
    while($result = $db->fetch_array()){
        $loop_iv_amount = $result['amount'];
    }

    $db->query($loop_dn_query);
    while($result = $db->fetch_array()){
        $loop_dn_amount = $result['amount'];
    }
    
    $db->query($loop_cn_query);
    while($result = $db->fetch_array()){
        $loop_cn_amount = $result['amount'];
    }
    
    $db->query($loop_rp_query);
    while($result = $db->fetch_array()){
        $loop_rp_amount = $result['amount'];
    }

    $loop_balance = $loop_iv_amount + $loop_dn_amount - $loop_cn_amount - $loop_rp_amount;

    if($k == 0){
        $status = 'Current Mth';
    }else if( $k == 5){
        $status = '5 Mths & Above';
    }else if($k == 1){
        $status = $k . ' Month';
    }else{
        $status = $k . ' Months';
    }

    $loop = array(
        "status" => $status,
        "balance" => $loop_balance == 0 ? '-' : $loop_balance
    );

    $loop_balance_array[] = $loop;
}

for ($i=0; $i < count($loop_balance_array); $i++) {
    $first_row .= '<td style="border:1px solid black;width:16.67%;padding:2px;font-size:10px;"><div class="break">'.$loop_balance_array[$i]['status'].'</div></td>';
    $second_row .= '<td style="border:1px solid black;width:16.67%;padding:2px;font-size:12px;"><div class="break">'.isPositive($loop_balance_array[$i]['balance']).'</div></td>';
}
$view[] =  '<table style="border:1px solid black;border-collapse: collapse;width:80%;text-align:right;margin:auto;"><tr>'.$first_row.'</tr><tr>'.$second_row.'</tr></table>';
$view[] = '<div style="color:grey;margin-top:5px;font-size:8px;font-style:italic">This copy of statement is for reference only</div>';
$view[] = '<div style="color:grey;margin-top:10px;font-size:7px">Statement Date From: '.$dateFrom.' Date To: '.$dateTo.' Generated At: '.date('d/m/Y h:i:s').'</div>';

$html = implode('',$view) . "</body>
</html>";

echo json_encode(array('statement'=>base64_encode($html)));


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
  return currency_to_cheque(abs($number));
}
function monthAndYear($date){
  return substr(dateOnly($date),0,-3);
}
function dateOnly($date){
  return empty($date) ? "" : explode(" ",$date)[0];
}
function ellipse($str){
  return strlen($str) > 18 ? substr($str,0,18).'...' : $str;
}

function isPositive($amount){
  if($amount == '-'){
      return $amount;
  }
  if($amount < 0){
      $amount = $amount * -1;
      $amount = number_format($amount,2);
      return '('.$amount.')';
  }else{
      $amount = number_format($amount,2);
      return $amount;
  }
}

function get($name){
  return isset($_GET[$name]) ? $_GET[$name] : '';
}
?>
