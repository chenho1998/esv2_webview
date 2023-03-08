<?php 
require_once('./model/MySQL.php');
require_once('./MoneyToWord.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header('Content-Type: text/html; charset=utf-8');
header("Pragma: no-cache");
date_default_timezone_set('Asia/Kuala_Lumpur');

function getdetails(){
    return '
    <tr class="spaceUnder">
        <td>@counter.</td>
        <td>@code.</td>
        <td style="max-width: 30%;"><div style="width:300px;white-space:pre-line;line-height:0.9em;margin-top:-5px">@name</div></td>
        <td>@quantity</td>
        <td style="text-align: center;">@price</td>
        <td style="text-align: center;">@discount</td>
        <td style="text-align: right;">@total</td>
    </tr>
    ';
}
function getmain(){
    return '
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml">
       <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3pro.css">
            <meta http-equiv="X-UA-Compatible" content="ie=edge">
            <title>EasyTech</title>
            <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">

          <style>* {font-weight:500;} h2 { font-weight:bold; } table { page-break-inside:auto } tr { page-break-inside:avoid; page-break-after:auto;text-align:left } thead { display:table-header-group } tfoot { display:table-footer-group } body {  font-size: 14px;  line-height: 0.2em;}table td, table td * { vertical-align: top;white-space:pre;} tr.spaceUnder>td {  padding-bottom: 0.8em;}.small-text{ font-size: 12px; font-weight:500;margin-left: 5px;}.small-text-womargin{ font-size: 14px; font-weight:500; line-height: 0.1em; margin-left:-5px;}.main-div{  padding: 10px;}.title{ font-size: 15px;  line-height: 0.1em;  font-weight: bold;  color: black;margin-left:-5px;}.footer {  position: fixed;  left: 0;  bottom: 0;  width: 98%;  border-top: 1px solid black;  border-bottom: 1px solid black;  height: 100px;  margin-bottom: 10px;  align-self: center;  margin-left: 1%;} .right-text{ display:block;text-align:right } .left-text{ display:block;text-align:left } .center-text { display:block;text-align:center } p-wo-margin{line-height: 0.1em;margin:0px;} hr{
            height: 1px;
            background-color: black;
            border: none;
        } .neg-top { margin-top:-5px }</style>
       </head>
       <body>
          <table style="width:100%;height:80px">
             <col width="20%">
             <col width="80%">
             <tr>
                <td style="align-items:left;height:80px;"> <img src="data:image/png;base64,@base64" width="120px" /> 
                </td>
                <td style="align-items:left;height:80px;">
                   <h4 style="line-height:18px;font-weight:bold;">@client<label class="small-text" style="margin-top:5px;">@sub_client</label></h4><p class="p-wo-margin" style="line-height:0.9em;margin-top:-10px">@address @city @state @zipcode</p><p class="p-wo-margin neg-top">Tel: @phone Fax: @fax</p><p class="p-wo-margin">E-mail: @email Website: @website</p>
                </td>
             </tr>
          </table>
          <div class="main-div">
             <h2>@doctype_caps</h2>
             <hr>
             <p class="small-text-womargin" style="margin-left:1px">Billing Address</p>
             <p class="title" style="margin-left:1px;">@cust_name</p>
             <p style="line-height:1em;">@cust_address</p>  
             <p>Attn: @person_incharge</p>
             <p>Tel: @cust_tel</p>
             <p>Fax: @cust_fax</p>
             <hr>
             <table width="100%">
                <col width="15%">
                <col width="15%">
                <col width="10%">
                <col width="10%">
                <col width="20%">
                <col width="30%">
                <tr>
                   <td>
                      <label class="small-text-womargin left-text"> Account </label>  
                      <p class="title left-text"> @cust_code  </p>
                   </td>
                   <td>
                      <label class="small-text-womargin left-text"> Currency </label>  
                      <p class="title"> RM  </p>
                   </td>
                   <td></td>
                   <td></td>
                   <td>
                      <label class="small-text-womargin left-text"> Agent </label>  
                      <p class="title"> @staff_code  </p>
                   </td>
                   <td>
                      <label class="small-text-womargin left-text"> @doc_type No. </label>  
                      <p class="title right-text"> @doc_code  </p>
                   </td>
                   <td style="text-align:right;">
                        <label class="small-text-womargin right-text"> Date </label>  
                      <p class="right-text" style="font-weight:bold;width:100px;margin-right:-5px;"> @doc_date  </p>
                   </td>
                </tr>
             </table>
             <hr style="margin-top: -7px;">
             <table width="100%">
                <col width="2%">
                <col width="20%">
                <col width="43%">
                <col width="5%">
                <col width="10%">
                <col width="5%">
                <col width="10%">
                <tr class="spaceUnder">
                   <td class="small-text-womargin">No</td>
                   <td class="small-text-womargin">Item Code</td>
                   <td class="small-text-womargin">Description</td>
                   <td class="small-text-womargin">Qty</td>
                   <td class="small-text-womargin" style="text-align: center;">Price</td>
                   <td class="small-text-womargin" style="text-align: center;">Discount</td>
                   <td class="small-text-womargin" style="text-align: right;">Total</td>
                </tr>
                <tr class="spaceUnder">
                   <td class="small-text-womargin"></td>
                   <td class="small-text-womargin"></td>
                   <td class="small-text-womargin"></td>
                   <td class="small-text-womargin"></td>
                   <td class="small-text-womargin" style="text-align: center;">/Unit</td>
                   <td class="small-text-womargin" style="text-align: center;"></td>
                   <td class="small-text-womargin" style="text-align: right;">Price</td>
                </tr>
                <tr>
                    <td class="small-text-womargin"></td>
                   <td class="small-text-womargin"></td>
                   <td class="small-text-womargin"></td>
                   <td class="small-text-womargin"></td>
                   <td class="small-text-womargin" style="text-align: center;"></td>
                   <td class="small-text-womargin" style="text-align: center;"></td>
                   <td class="small-text-womargin" style="text-align: right;"></td>
                </tr>
                <tr>
                    <td class="small-text-womargin"></td>
                   <td class="small-text-womargin"></td>
                   <td class="small-text-womargin"></td>
                   <td class="small-text-womargin"></td>
                   <td class="small-text-womargin" style="text-align: center;"></td>
                   <td class="small-text-womargin" style="text-align: center;"></td>
                   <td class="small-text-womargin" style="text-align: right;"></td>
                </tr>
                @product_row 
                <tr class="spaceUnder">
                    <td class="small-text-womargin"></td>
                   <td class="small-text-womargin"></td>
                   <td class="small-text-womargin"></td>
                   <td style="border-top:1px solid black;padding-top:10px">@t_quantity</td>
                   <td class="small-text-womargin" style="text-align: center;"></td>
                   <td class="small-text-womargin" style="text-align: center;"></td>
                   <td class="small-text-womargin" style="text-align: right;"></td>
                </tr>
                <tr>
                    <td class="small-text-womargin"></td>
                   <td class="small-text-womargin"></td>
                   <td class="small-text-womargin"></td>
                   <td class="small-text-womargin"></td>
                   <td class="small-text-womargin" style="text-align: center;"></td>
                   <td class="small-text-womargin" style="text-align: center;"></td>
                   <td class="small-text-womargin" style="text-align: right;"></td>
                </tr>
             </table>
          </div>
          <div>
             <table width="100%">
                <col width="60%">
                <col width="40%">
                <tr>
                   <td>
                      <p style="line-height:1em;" class="neg-top">@in_word</p>
                   </td>
                   <td style="text-align: right;">
                      <p class="small-text-womargin">Total Payable</p>
                      <p class="title">@total</p>
                   </td>
                </tr>
                <tr>
                   <td>
                      <p class="small-text-womargin" style="margin-left:1px">Payment Terms:</p>
                      <p>@term</p>
                   </td>
                   <td style="text-align: right;">
                      <p class="small-text-womargin">Authorized Signature</p>
                      <p style="line-height:1em;" class="neg-top">@client</p>
                   </td>
                </tr>
             </table>
          </div>
       </body>
    </html>
    ';
}

$doc_type_name = array(
    'debit'=>'Debit Note',
    'credit'=>'Credit Note',
    'invoice'=>'Invoice',
    'receipt'=>'Receipt'
);
$settings = parse_ini_file('../config.ini',true);

if(
    isset($_GET['client']) &&
    isset($_GET['doc_type']) && 
    isset($_GET['doc_id']) &&
    isset($_GET['salesperson_id'])
){
    $client = $_GET['client'];
    $doc_type = $_GET['doc_type'];
    $doc_id = $_GET['doc_id'];
    $salesperson_id = $_GET['salesperson_id'];
    $cust_code = $_GET['cust_code'];

    $overly_customized = in_array($client,array('nmayang','nmayang_eng','agrox'));
    $overly_customized_area = array('Agreement','AGREEMENT');
    $overly_customized_area_desc = array('RP-WS.','RP-SITE');
    
    $mysql_config = $settings[$client];
    $mysql = new MySQL($mysql_config);

    $zero_items_hide_clients = $settings['Hide_Zero_Qty']['client_list'];
    $hide_zero_qty = in_array($client,$zero_items_hide_clients);

    $main = array();
    $details = array();

    /**
     * Migration
     */
    $check = $mysql->Execute("SHOW COLUMNS FROM `cms_invoice` LIKE 'salesperson_id'");
    if(count($check) == 0){
        $mysql->Execute("ALTER TABLE `cms_invoice` ADD COLUMN `salesperson_id` int(10) DEFAULT 0;");
    }
    $check = $mysql->Execute("SHOW COLUMNS FROM `cms_invoice` LIKE 'inv_udf'");
    if(count($check) == 0){
        $mysql->Execute("ALTER TABLE `cms_invoice` ADD COLUMN `inv_udf` LONGBLOB NOT NULL AFTER `salesperson_id`;");
    }
    $check = $mysql->Execute("SHOW COLUMNS FROM `cms_creditnote` LIKE 'cn_udf'");
    if(count($check) == 0){
        $mysql->Execute("ALTER TABLE `cms_creditnote` ADD COLUMN `cn_udf` LONGBLOB NOT NULL AFTER `cn_date`;");
    }
    $check = $mysql->Execute("SHOW COLUMNS FROM `cms_creditnote` LIKE 'salesperson_id'");
    if(count($check) == 0){
        $mysql->Execute("ALTER TABLE `cms_creditnote` ADD COLUMN `salesperson_id` int(2) AFTER `cn_date`;");
    }
    $check = $mysql->Execute("SHOW COLUMNS FROM `cms_receipt` LIKE 'salesperson_id'");
    if(count($check) == 0){
        $mysql->Execute("ALTER TABLE `cms_receipt` ADD COLUMN `salesperson_id` int(2) AFTER `receipt_date`;");
    }
    $check = $mysql->Execute("SHOW COLUMNS FROM `cms_receipt` LIKE 'receipt_udf'");
    if(count($check) == 0){
        $mysql->Execute("ALTER TABLE `cms_receipt` ADD COLUMN `receipt_udf` LONGBLOB NOT NULL AFTER `salesperson_id`;");
    }
    $check = $mysql->Execute("SHOW COLUMNS FROM `cms_invoice_details` LIKE 'active_status'");
    if(count($check) == 0){
        $mysql->Execute("ALTER TABLE `cms_invoice_details` ADD COLUMN `active_status` int(2) DEFAULT 1 NOT NULL AFTER `quantity`;");
    }
    $check = $mysql->Execute("SHOW COLUMNS FROM `cms_invoice_details` LIKE 'inv_dtl_udf'");
    if(count($check) == 0){
        $mysql->Execute("ALTER TABLE `cms_invoice_details` ADD COLUMN `inv_dtl_udf` LONGBLOB NOT NULL AFTER `active_status`;");
    }
    $check = $mysql->Execute("SHOW COLUMNS FROM `cms_invoice_details` LIKE 'sequence_no'");
    if(count($check) == 0){
        $mysql->Execute("ALTER TABLE `cms_invoice_details` ADD COLUMN `sequence_no` int(10) DEFAULT 0 NOT NULL AFTER `active_status`;");
    }

    $check = $mysql->Execute("SHOW COLUMNS FROM `cms_creditnote_details` LIKE 'active_status'");
    if(count($check) == 0){
        $mysql->Execute("ALTER TABLE `cms_creditnote_details` ADD COLUMN `active_status` int(2) DEFAULT 1 NOT NULL AFTER `quantity`;");
    }
    $check = $mysql->Execute("SHOW COLUMNS FROM `cms_creditnote_details` LIKE 'cn_dtl_udf'");
    if(count($check) == 0){
        $mysql->Execute("ALTER TABLE `cms_creditnote_details` ADD COLUMN `cn_dtl_udf` LONGBLOB NOT NULL AFTER `active_status`;");
    }
    $check = $mysql->Execute("SHOW COLUMNS FROM `cms_creditnote_details` LIKE 'sequence_no'");
    if(count($check) == 0){
        $mysql->Execute("ALTER TABLE `cms_creditnote_details` ADD COLUMN `sequence_no` int(10) DEFAULT 0 NOT NULL AFTER `active_status`;");
    }
    $check = $mysql->Execute("SHOW TABLES LIKE 'cms_pdf_image';");
    if(count($check) == 0){
        $mysql->Execute("create table cms_pdf_image
        (
            id             int auto_increment primary key,
            pdf_header     varchar(200)                not null,
            pdf_footer     varchar(200)                not null,
            pdf_content    blob,
            pdf_content_row 
                           blob,
            salesperson_id int         default 0       not null,
            cust_code      varchar(50) default ''      not null,
            doc_type       varchar(10) default 'sales' null,
            active_status  int         default 1       null
        )
            charset = utf8;");
    }
    $check = $mysql->Execute("SHOW COLUMNS FROM `cms_pdf_image` LIKE 'pdf_content'");
    if(count($check) == 0){
        $mysql->Execute("ALTER TABLE `cms_pdf_image` ADD COLUMN `pdf_content` BLOB;");
    }
    $check = $mysql->Execute("SHOW COLUMNS FROM `cms_pdf_image` LIKE 'pdf_content_row'");
    if(count($check) == 0){
        $mysql->Execute("ALTER TABLE `cms_pdf_image` ADD COLUMN `pdf_content_row` BLOB;");
    }
    $check = $mysql->Execute("SHOW COLUMNS FROM `cms_pdf_image` LIKE 'show_in_app'");
    if(count($check) == 0){
        $mysql->Execute("ALTER TABLE `cms_pdf_image` ADD COLUMN `show_in_app` int(2) default 0;");
    }

    $client_info = $mysql->Execute("select * from cms_mobile_module where module = 'app_client_info';");
    $client_name = $mysql->Execute("select * from cms_mobile_module where module = 'app_client'");
    $config = $mysql->Execute("select * from cms_setting;");

    if(count($client_info) > 0){
        $client_info = $client_info[0];
        $client_info = json_decode($client_info['status'],true);
    }
    if(count($client_name) > 0){
        $client_name = $client_name[0];
        $client_name = $client_name['status'];
    }
    if(count($config) > 0){
        $config = $config[0];
    }

    $header_image = base64_encode(file_get_contents($config['PO_header_Url']));
    $footer_image = base64_encode(file_get_contents($config['PO_footer_Url']));
    $logo_image = base64_encode(file_get_contents($config['logo_Url']));

    if($doc_type == 'debit'){
        $main = $mysql->Execute("select dn_code as doc_code, cust_code, date_format(date(dn_date),'%d/%m/%y') as doc_date, dn_amount as doc_amount, outstanding_amount as doc_outstanding where dn_code = '{$doc_id}'");
        if(count($main) > 0){
            $main = $main[0];
        }
    }
    if($doc_type == 'invoice'){
        $main = $mysql->Execute("select invoice_code as doc_code, cust_code, date_format(date(invoice_date),'%d/%m/%y') as doc_date, invoice_amount as doc_amount, outstanding_amount as doc_outstanding, inv_udf as doc_udf, salesperson_id from cms_invoice where invoice_code = '{$doc_id}'");
        if(count($main) > 0){
            $main = $main[0];
        }
        $details = $mysql->Execute("select *, inv_dtl_udf as dtl_udf from cms_invoice_details where invoice_code = '{$doc_id}' and active_status = 1 order by  sequence_no;");
    }
    if($doc_type == 'credit'){
        $main = $mysql->Execute("select cn_code as doc_code, cust_code, date_format(date(cn_date),'%d/%m/%y') as doc_date, cn_amount as doc_amount, cn_knockoff_amount as doc_outstanding, cn_udf as doc_udf, salesperson_id from cms_creditnote where cn_code = '{$doc_id}'");
        if(count($main) > 0){
            $main = $main[0];
        }
        $details = $mysql->Execute("select *, cn_dtl_udf as dtl_udf from cms_creditnote_details where cn_code = '{$doc_id}' and active_status = 1 order by sequence_no;");
    }
    if($doc_type == 'receipt'){
        $main = $mysql->Execute("select receipt_code as doc_code, cust_code, date_format(date(receipt_date),'%d/%m/%Y') as doc_date, receipt_amount as doc_amount, (receipt_amount-receipt_knockoff_amount) as doc_outstanding, receipt_desc, cheque_no,receipt_udf as doc_udf,salesperson_id from cms_receipt where receipt_code = '{$doc_id}'");
        if(count($main) > 0){
            $main = $main[0];
        }
        
        $details = $mysql->Execute("select k.doc_amount as total_price,k.doc_ko_ref as item_code,'' as quantity,'' as uom, '' as discount,inv.invoice_amount as item_price, inv.outstanding_amount, date_format(date(inv.invoice_date),'%d/%m/%Y') as item_name from cms_customer_ageing_ko as k
        left join cms_invoice inv on inv.invoice_code = k.doc_ko_ref where doc_code = '{$doc_id}' and doc_ko_type = 'IV'");

        $dn = $mysql->Execute("select k.doc_amount as total_price,k.doc_ko_ref as item_code,'' as quantity,'' as uom, '' as discount,dn.dn_amount as item_price, dn.outstanding_amount, date_format(date(dn.dn_date),'%d/%m/%Y') as item_name from cms_customer_ageing_ko as k
        left join cms_debitnote dn on dn.dn_code = k.doc_ko_ref where doc_code = '{$doc_id}' and doc_ko_type = 'DN';");

        $details = array_merge($details, $dn);
    }

    $customer = $mysql->Execute("select * from cms_customer where cust_code = '{$cust_code}'");
    $customer = $customer[0];

    if(isset($main['salesperson_id'])){
        $salesperson_id = $main['salesperson_id'];
    }
    $login = $mysql->Execute("select * from cms_login where login_id = '{$salesperson_id}'");
    $login = $login[0];

    $htmlContent = get_html($doc_type,$mysql);

    if($htmlContent['header']){
        $header_image = base64_encode(file_get_contents($htmlContent['header']));
    }
    if($htmlContent['footer']){
        $footer_image = base64_encode(file_get_contents($htmlContent['footer']));
    }
    
    $show_2nd_desc = false;
    $hide_area = false;
    $html = $htmlContent['main'];
    $main['doc_udf'] = json_decode($main['doc_udf'],true);
    
    if($main['doc_udf']){
        if($overly_customized){
            foreach ($main['doc_udf'] as $key => $value){
                if($key == 'area' && !in_array($value,$overly_customized_area)){
                    $hide_area = true;
                }
                if($key == 'area' && in_array($value,$overly_customized_area_desc)){
                    $show_2nd_desc = true;
                }
            }
        }
        foreach ($main['doc_udf'] as $key => $value) {
            $hkey = '@'.$key;
            if($hide_area && $key=='agreementno'){
                $html = str_replace($hkey,'',$html);
            }else{
                $html = str_replace($hkey,sanitize($value),$html);
            }
        }
    }
    $tax_amount = 0;
    $htmlRow = $htmlContent['details'];//get_template($client,$doc_type,$htmlRow);//isset($htmlRow[$client]) ? $htmlRow[$client] : $htmlRow['default'];

    if(count($details) > 0 && $doc_type == 'receipt'){
        $html = str_replace('ko hidden','',$html);
    }
    
    if($overly_customized && $hide_area){
        $udf = $details[0]['dtl_udf'];
        $udf = json_decode($udf,true);
        if($udf && empty($udf['remark1'])){
            $html = str_replace('@0.total_price','',$html);
            $html = str_replace('@0.remark1','',$html);
        }
    }
    $table_row = '';
    $total_quantity = 0;
    for ($i=0; $i < count($details); $i++) { 
        $row = $details[$i];
        if(intval($row['quantity']) < 1 && $hide_zero_qty && $doc_type != 'receipt'){
            continue;
        }
        $udf = json_decode($row['dtl_udf'],true);
        if($overly_customized && $show_2nd_desc && $udf){
            foreach ($udf as $key => $value) {
                if($key == 'description2'){
                    $row['item_name'] = $value;
                }
            }
        }
        $tmp_row = replace(
            array(
                '@counter',
                '@code',
                '@name',
                '@quantity',
                '@price',
                '@discount',
                '@total',
                '@uom'
            ),
            array(
                ($i+1),
                str_replace("'","",$row['item_code']),
                sanitize($row['item_name']),
                $row['quantity'],
                money($row['item_price']),
                $row['discount'],
                money($row['total_price']),
                str_replace("'","",$row['uom'])
            ),
            $htmlRow
        );
        $total_quantity += $row['quantity'];
        if($udf){
            foreach ($udf as $key => $value) {
                $mkey = '@'.$i.'.'.$key;
                $dkey = '@'.$key;
                if($key == 'localtaxamt'){
                    $tax_amount += floatval($value);
                }
                if($key == 'item_price' || 
                    $key == 'total_price' || 
                    $key == 'excltax' || 
                    $key == 'localtaxamt' || 
                    $key == 'incltax' ||
                    $key == 'suomqty'
                ){
                    $value = number_format($value,2);
                }
                if($key == 'description3'){
                    if(trim($value) == '@n' || empty($value)){
                        $value = '';
                    }else{
                        $value = str_replace('@n','<br>',$value);
                        $tmp_row = str_replace('hidden','',$tmp_row);
                    }
                }
                if(string_contains($key,'date')){
                    $value = my_date($value);
                }
                $value = is_numeric($value) && $value == 0 ? '' : $value;
                $value = sanitize($value);
                $html = str_replace($mkey,$value,$html);
                $tmp_row = str_replace($dkey,$value,$tmp_row);
            }
        }
        $keys = array_keys($row);
        for ($j=0; $j < count($keys); $j++) { 
            $mkey = '@'.$i.'.'.$keys[$j];
            $dkey = '@'.$keys[$j];
            $value = $row[$keys[$j]];
            if($keys[$j] == 'item_price' || $keys[$j] == 'total_price'){
                $value = number_format($value,2);
            }
            if(string_contains($keys[$j],'date')){
                $value = my_date($value);
            }
            $value = sanitize($value);
            $html = str_replace($mkey,$value,$html);
            $tmp_row = str_replace($dkey,$value,$tmp_row);
        }
        $table_row .= $tmp_row;
    }
    
    $without_tax = floatval($main['doc_amount']) - $tax_amount;
    $main_amount = floatval($main['doc_amount']) ;
    $main_amount_rounded = make_round($main['doc_amount']);
    $rounded_amount = rounding_amount($main_amount,$main_amount_rounded);
    $tax_included = floatval($main_amount) /* - $tax_amount */;
    
    if($overly_customized){
        $customer['billing_address4'] = '';
    }
    
    $html = replace(
        array(
            '@client',
            '@sub_client',
            '@address',
            '@city',
            '@state',
            '@zipcode',
            '@phone',
            '@fax',
            '@email',
            '@website',
            '@doc_type',
            '@doctype_caps',
            '@cust_name',
            '@cust_address',
            '@person_incharge',
            '@cust_tel',
            '@cust_fax',
            '@cust_code',
            '@staff_code',
            '@doc_code',
            '@doc_date',
            '@term',
            '@total',
            '@in_word',
            '@base64',
            '@product_row',
            '@doc_amount',
            '@doc_tax',
            '@rounding',
            '@doc_excl_tax',
            '@doc_incl_tax',
            '@cheque_no',
            '@doc_outstanding',
            '@receipt_desc',
            '@header_image',
            '@footer_image',
            '@t_quantity'
        ),
        array(
            $client_name,
            $client_info['sub_name'],
            $client_info['address'],
            $client_info['city'],
            $client_info['state'],
            $client_info['zipcode'],
            $client_info['phone'],
            $client_info['fax'],
            $client_info['email'],
            $client_info['website'],
            $doc_type_name[$doc_type],
            strtoupper($doc_type_name[$doc_type]),
            $customer['cust_company_name'],
            $customer['billing_address1'].'<br>'.$customer['billing_address2'].'<br>'.$customer['billing_address3'].'<br>'.$customer['billing_address4'],
            $customer['cust_incharge_person'],
            $customer['cust_tel'],
            $customer['cust_fax'],
            $customer['cust_code'],
            $login['staff_code'],
            $main['doc_code'],
            $main['doc_date'],
            $customer['termcode'],
            number_format($main['doc_amount'],2),
            moneyInWord($main['doc_amount']),
            $logo_image,
            $table_row,
            number_format($without_tax,2),
            number_format($tax_amount,2),
            number_format($rounded_amount,2),
            number_format($without_tax,2),
            number_format($tax_included,2),
            isOK($main['cheque_no']),
            isOK($main['doc_outstanding']) ? number_format(floatval(isOK($main['doc_outstanding'])),2) : '0.00',
            isOK($main['receipt_desc']),
            $header_image,
            $footer_image,
            $total_quantity
        ),
        $html
    );
    
    if ($client == "hpoint"){
        file_put_contents("hpoint.log",$html);
    }
    echo $html;
    // echo json_encode(array('html'=>base64_encode($html)),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
}
function isOK($val){
    return isset($val) ? $val : '';
}
function sanitize($input){
    return str_replace(array('#'),'',$input);
}
function replace($find,$replace,$html){
    $countFind = count($find);
    $countReplace = count($replace);
    if($countFind != $countReplace){
        die("Error in replace function. Count of find array and replace array is not same. Expected {$countFind} but found {$countReplace}");
    }
    $final = $html;
    for ($i=0; $i < count($find); $i++) { 
        $_a = $find[$i];
        $_b = $replace[$i];
        $final = str_replace($_a,$_b,$final);
    }
    return $final;
}
function moneyInWord($number){
    // $o = new MoneyToWord($number);
    // return 'RINGGIT MALAYSIA : '.$o->get();
    return currency_to_cheque($number);
}
function money($number){
    $number = floatval($number);
    if($number == 0){
        return '';
    }
    return number_format($number,2);
}
function is_decimal($val){
    return is_numeric($val) && floor($val) != $val;
}
function make_round($val){
    return is_decimal($val) ? floor(floatval($val)) + 1 : $val;
}
function rounding_amount($small, $big){
    return $big - $small;
}
function get_html ($_doc_type, $mysql){
    $doc_type = 'doc_'.$_doc_type;
    $pdf = $mysql->Execute("select * from cms_pdf_image where doc_type = '{$doc_type}';");
    if(count($pdf) > 0 && !empty($pdf[0]['pdf_content'])){
        return array(
            'main'=>base64_decode($pdf[0]['pdf_content']),
            'details'=>base64_decode($pdf[0]['pdf_content_row']),
            'header'=>$pdf[0]['pdf_header'],
            'footer'=>$pdf[0]['pdf_footer']
        );
    }
    return array(
        'main'=>getmain(),
        'details'=>getdetails()
    );
}
function get_template($client,$doc_type,$html){
    if(isset($html[$client])){
        return $html[$client];
    }
    if(isset($html[$client.'_'.$doc_type])){
        return $html[$client.'_'.$doc_type];
    }
    return $html['default'];
}
function string_contains($str, $needle){
    $str = trim(strtolower($str));
    $needle = trim(strtolower($needle));
    return (strpos($str, $needle) !== false);
}
function my_date($str){
    return date('d-m-Y',strtotime($str));
}
?>