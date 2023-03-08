<?php
require_once('./model/MySQL.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
date_default_timezone_set('Asia/Kuala_Lumpur');

$doc_type_names = array(
    'sales'=>'SALES ORDER',
    'invoice'=>'INVOICE',
    'cash'=>'CASH SALES',
    'payment'=>'PAYMENT',
    'deliveryorder'=>'DELIVERY ORDER'
);

$template['default'] = '<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3pro.css">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <style> 
            .pixels{ font-weight:bold; padding:0px; margin:0px; }
          p{ line-height: 0.1; } 
          h2,h3,h4{ line-height: 0.4; margin:0px; }
          hr{ height:0px; }
          * {
            font-weight:500;
            box-sizing: border-box;
          }
          body{
            font-family: sans-serif;
            font-size:10px; line-height:1.2em;
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
            padding-top:3px;
            border-bottom: 1px solid black;
            font-size:12px;
          }
          td{
              height:14px;
          }
          .small-font{
              font-size:13px;
              line-height:13px;
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
          <p style="font-size:15px;line-height:15px;">
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
          <h3 size="pixels" class="pixels">
            @doc_header_title
          </h3>
      </div>
      <div class="row">
        <div class="column">
          <table style="width:100%">
            <tr>
              <td align="right">
                <strong>AGENT: </strong>
              </td>
              <td align="left">
                @agent_name
              </td>
            </tr>
          </table>
        </div>
        <div class="column">
          <table style="width:100%">
            <tr>
              <td align="right">
                <strong>DATE: </strong>
              </td>
              <td align="left">
                  @summary_date
              </td>
            </tr>
          </table>
        </div>
      </div>
      <div style="margin-top:10px">
        @document_row
      </div>
    </body>
</html>';

if(!$_GET['doc_type']){
  $template['doc_total_amount'] = '
  <hr>
      <table>
        <tr>
          <td align="right" style="font-weight:bold;">SALES ORDER: </td>
          <td>RM @sales_amount</td>
        </tr>
        <tr>
          <td align="right" style="font-weight:bold;">CASH SALES: </td>
          <td>RM @cash_amount</td>
        </tr>
        @cash_payment
        <tr>
          <td align="right" style="font-weight:bold;">INVOICE: </td>
          <td>RM @invoice_amount</td>
        </tr>
      </table>
  <hr>';
}else{

  if($_GET['doc_type'] == 'Sales'){
    $document_total_view =
      '<tr>
        <td align="right" style="font-weight:bold;">SALES ORDER: </td>
        <td>RM @sales_amount</td>
      </tr>';
  }else if($_GET['doc_type'] == 'Invoice'){
    $document_total_view =
      '<tr>
        <td align="right" style="font-weight:bold;">INVOICE: </td>
        <td>RM @invoice_amount</td>
      </tr>';
  }else if($_GET['doc_type'] == 'Cash'){
    $document_total_view =
      '<tr>
        <td align="right" style="font-weight:bold;">CASH SALES: </td>
        <td>RM @cash_amount</td>
      </tr>@cash_payment';
  }

  $template['doc_total_amount'] = '
  <hr>
      <table>
        '.$document_total_view.'
      </table>
  <hr>';
}

$template['doc_total_amount_payment'] = '
<hr>
    <table>
      <tr>
        <td align="right" style="font-weight:bold;">CASH PAYMENT</td>
        <td>: RM @payment_cash_amount</td>
      </tr>
      <tr>
        <td align="right" style="font-weight:bold;">WIRE PAYMENT</td>
        <td>: RM @payment_other_amount</td>
      </tr>
      <tr>
          <td style="border-bottom:1px solid black;"></td>
          <td style="border-bottom:1px solid black;"></td>
      </tr>
      <tr>
        <td align="right" style="font-weight:bold;">TOTAL</td>
        <td>: RM @payment_amount</td>
      </tr>
      <tr>
          <td style="border-top:1px solid black;"></td>
          <td style="border-top:1px solid black;"></td>
      </tr>
      <tr>
        <td align="right" style="font-weight:bold;">AMOUNT BANK IN (1ST)</td>
        <td>: RM</td>
      </tr>
      <tr>
        <td align="right" style="font-weight:bold;">AMOUNT BANK IN (2ND)</td>
        <td>: RM</td>
      </tr>
    </table>
<hr>';

$template['doc_type'] = '
<h3 size="pixels" class="pixels" style="margin-bottom:10px">
    @doc_type
</h3>';

$template['group_by'] = '
<h5 size="pixels" class="pixels" style="margin-bottom:10px;text-align:center">
    @group_by
</h5>';

$template['cust_row'] = '
<table style="width:100%;border:0px solid white;">
    <col width="33%">
    <col width="34%">
    <col width="33%">
    <tr>
        <th align="left">@customer_name</th>
        <th align="left">@doc_id</th>
        <th align="left">@doc_date</th>
    </tr>
    @item_row
</table>';

$template['cust_row_driver'] = '
<table style="width:100%;border:0px solid white;">
    <col width="33%">
    <col width="34%">
    <col width="33%">
    <tr>
        <th align="left">@customer_name</th>
        <th align="left">@doc_id  <br>@driver</th>
        <th align="left">@doc_date  <br>@assistant</th>
    </tr>
    @item_row
</table>';


$template['cust_row_payment'] = '
<table style="width:100%;border:0px solid white;">
    <col width="33%">
    <col width="34%">
    <col width="33%">
    <tr>
        <th align="left">@customer_name @doc_remark</th>
        <th align="left">@doc_id</th>
        <th align="left">@doc_date</th>
    </tr>
    @item_row
</table>';

$template['row'] = '
<tr style="background-color:smokewhite">
    <td align="right" style="padding-right:20px">@first</td>
    <td align="left">@second</td>
    <td align="left">@third</td>
</tr>';

$template['last_row'] = '
<tr>
    <td align="left"></td>
    <td align="right" style="font-size:12px;padding-top:3px;border-top:1px solid black;padding-right:15px">
          <strong>
          TOTAL:
          </strong>
    </td>
    <td align="left" style="font-size:12px;padding-top:3px;border-top:1px solid black;">

        <strong>
            RM @grand_total
        </strong>
    </td>
</tr>';
$template['last_row_sst'] = '
<tr>
    <td align="left"></td>
    <td align="right" style="font-size:12px;padding-top:3px;border-top:1px solid black;padding-right:15px">
          <strong>
          TOTAL(SST):
          </strong>
    </td>
    <td align="left" style="padding-top:3px;border-top:1px solid black;">
        <strong>
            RM@grand_total
        </strong>
    </td>
</tr>';

$settings = parse_ini_file('../config.ini',true);



if(
    isset($_GET['salesperson_id']) && 
    isset($_GET['client']) && 
    isset($_GET['date_from']) &&
    isset($_GET['date_to'])
){
    $salesperson_id = $_GET['salesperson_id'];
    $date_from = $_GET['date_from'];
    $date_to = $_GET['date_to'];
    $client = $_GET['client'];
    $printType = $_GET['print_type'];
    $doc_type = $_GET['doc_type'] ? strtolower($_GET['doc_type']) : '';

    $hasSst = $client == 'pluto';

    $sst_exclude	= $settings['SST_EXCLUDE'];
    $allowToShowSST = !in_array($client,$sst_exclude['sst_exclude']);

    $summary_delivery	= $settings['SUMMARY_DELIVERY'];
    $summaryDelivery = in_array($client,$summary_delivery['summary_delivery']);

    $display_cash_payment = $settings['PAYMENT_DISPLAY'];
    $displayCashPayment = in_array($client,$display_cash_payment['payment_display']);

    $summary_group_by	= $settings['SUMMARY_GROUP_BY'];
    $billing_state_group_by = in_array($client,$summary_group_by['billing_state']);

    $show_driver_assistant	= $settings['SHOW_DRIVER_ASSISTANT'];
    $show_driver_assistant_setting = in_array($client, $show_driver_assistant['show_driver_assistant']);

    $settings = $settings[$client];
    $mysql = new MySQL($settings);

    $isPayment = isset($_GET['payment']) ? $_GET['payment'] == 1 : false;

    $result_set = array();

    if(!$doc_type){
      $doc_types = array(
          'sales',
          'cash',
          'invoice',
          'deliveryorder'
      );
    }else{
      $doc_types = array(
          $doc_type
      );
    }


    $doc_type_total = array();

    if($isPayment == false){
      $summary_group_by_query = $billing_state_group_by ? ',billing_state' : '';
      $driver_query = $show_driver_assistant_setting ? ',driver_name, assistant_name' : '';
      for ($i=0; $i < count($doc_types); $i++) { 
          $type = $doc_types[$i];
          
          $query = "SELECT order_date,order_id, cust_code, cust_company_name, grand_total,gst_amount,tax {$summary_group_by_query} {$driver_query} FROM cms_order WHERE doc_type = '{$type}' AND order_status > 0 AND salesperson_id = '{$salesperson_id}' AND cancel_status = 0";
          
          $query .= !$summaryDelivery ? " AND DATE(order_date) BETWEEN '{$date_from}' AND '{$date_to} 23:59:59';" : " AND DATE(delivery_date) BETWEEN '{$date_from}' AND '{$date_to}  23:59:59';";
          $documents = $mysql->Execute($query);

          $acc_total = 0;
          $special_group_array = array();
          for ($j=0; $j < count($documents); $j++) { 
              $doc_id = $documents[$j]['order_id'];
              $acc_total += $allowToShowSST ? floatval($documents[$j]['gst_amount']): floatval($documents[$j]['grand_total']);
              $document_items = $mysql->Execute("SELECT * FROM cms_order_item WHERE order_id = '{$doc_id}' AND cancel_status = 0;");

              $documents[$j]['payment_method'] = '';
              if($type == 'cash' && $displayCashPayment){
                $payment_details = $mysql->Execute("SELECT payment_by FROM cms_payment p JOIN cms_payment_detail pd ON p.payment_id = pd.payment_id WHERE p.description = '{$doc_id}' AND p.cancel_status = 0");
                if(count($payment_details) > 0 ){
                  $documents[$j]['payment_method'] =  $payment_details[0]['payment_by'] ? ' | '.$payment_details[0]['payment_by'] : '';
                }
              }

              $documents[$j]['order_items'] = $document_items;

              if($billing_state_group_by){
                $special_group_array[$documents[$j]['billing_state']][] = $documents[$j];
              }
          }
          $doc_type_total[$type] = number_format($acc_total,2);
          $result_set[$type] = $special_group_array ? $special_group_array: $documents;
      }
    }else{
      $cashTotal = 0;
      $otherTotal = 0;
      $acc_total = 0;

      $documents = $mysql->Execute("select payment_date as order_date,payment_id as order_id, p.cust_code, cc.cust_company_name, payment_amount as grand_total,
      0 as gst_amount, 0 as tax, description as payment_remark
  from cms_payment p left join cms_customer cc on p.cust_code = cc.cust_code
  where payment_status > 0 AND salesperson_id = '{$salesperson_id}' AND cancel_status = 0 AND DATE(payment_date) BETWEEN '{$date_from}' AND '{$date_to}'");

      for ($j=0; $j < count($documents); $j++) { 
        $doc_id = $documents[$j]['order_id'];
        $acc_total += floatval($documents[$j]['grand_total']);
        $document_items = $mysql->Execute("select if(payment_method = 'Cash','',payment_method) as product_code,
        if(payment_method <> 'Cash',payment_detail_remark,payment_method)
  as product_name, payment_amount from cms_payment_detail
  where payment_id = '{$doc_id}'");

        $document_items[0]['product_name'] = str_replace(" "," | ", trim($document_items[0]['product_name']));
        $documents[$j]['order_items'] = $document_items;
        for ($ll=0; $ll < count($document_items); $ll++) { 
          $_obj = $document_items[$ll];
          if($_obj['product_code'] == ''){
            $cashTotal += floatval($documents[$j]['grand_total']);
          }else{
            $otherTotal += floatval($documents[$j]['grand_total']);
          }
        }
      }
      $doc_type_total['cash'] = number_format($cashTotal,2);
      $doc_type_total['other'] = number_format($otherTotal,2);
      $doc_type_total['payment'] = number_format($acc_total,2);
      $result_set['payment'] = $documents;
    }


    if($printType == 'bluetooth'){
        $dataToSend = array();
        foreach($result_set as $doc_type=>$data){
          $doc_details = array();
          $doc_total_amount = 0;
          $doc_total_gst = 0;
          for ($i=0; $i < count($data); $i++) { 
            $doc_items = $data[$i]['order_items'];
            $new_doc_items = array();
            for ($j=0; $j < count($doc_items); $j++) { 
              $item = $doc_items[$j];
              $new_doc_items[] = array(
                'product_code'=>$item['product_code'],
                'product_name'=>$item['product_name'],
                'product_price'=>$item['quantity'].' '.$item['unit_uom'].' RM'.number_format($item['unit_price'],2)
              );
            }
            $total_amount = floatval($data[$i]['grand_total']);
            $total_amount_gst = floatval($data[$i]['gst_amount']);
            $sst_amount = floatval($data[$i]['tax']);

            if($show_driver_assistant_setting){
              $data[$i]['assistant_name'] = 'NO ASSISTANT' ? '-' : $data[$i]['assistant_name'];
              $doc_details[] = array(
                'cust_name'=>$data[$i]['cust_company_name'],
                'doc_id'=>$data[$i]['order_id'],
                'doc_date'=>malaysianDate($data[$i]['order_date']),
                'doc_amount'=>'RM'.number_format($total_amount,2),
                'doc_amount_sst'=>$hasSst ? 'RM'.number_format($total_amount_gst,2) : "RM0.00",
                'doc_tax'=>$hasSst ? 'RM'.number_format($sst_amount,2) : "RM0.00",
                'doc_items'=>$new_doc_items,
                'driver_name'=>'DRIVER: '.$data[$i]['driver_name'],
                'assistant_name'=>'ASSISTANT: '.$data[$i]['assistant_name']
              );
            }else{
              $doc_details[] = array(
                'cust_name'=>$data[$i]['cust_company_name'],
                'doc_id'=>$data[$i]['order_id'],
                'doc_date'=>malaysianDate($data[$i]['order_date']),
                'doc_amount'=>'RM'.number_format($total_amount,2),
                'doc_amount_sst'=>$hasSst ? 'RM'.number_format($total_amount_gst,2) : "RM0.00",
                'doc_tax'=>$hasSst ? 'RM'.number_format($sst_amount,2) : "RM0.00",
                'doc_items'=>$new_doc_items
              );
            }
            $doc_total_amount += floatval($data[$i]['grand_total']);
            $doc_total_gst += ($hasSst ? floatval($data[$i]['gst_amount']) : 0); 
          }
          $dataToSend[] = array(
            'doc_type'=>$doc_type,
            'doc_details'=>$doc_details,
            'num_of_doc'=>count($data),
            'sum_of_doc'=>'RM'.number_format($doc_total_amount,2),
            'sum_of_doc_gst'=>'RM'.number_format($doc_total_gst,2),
            'sum'=>$doc_total_amount
          );
        } 
        $total_sales = 0;
        for($i = 0; $i < count($dataToSend); $i++){
          $total_sales += floatval($dataToSend[$i]['sum']);
        }
        echo json_encode(array('data'=>$dataToSend,'total'=>'RM'.number_format($total_sales,2)));
        return;
    }else{
        $html = $template['default'];
        $html_doc_total_amount = $isPayment ? $template['doc_total_amount_payment'] : $template['doc_total_amount'];
        $html_doc_type = $template['doc_type'];

        if($isPayment){
          $html_cust_row = $template['cust_row_payment'];
        }else if($show_driver_assistant_setting){
          $html_cust_row = $template['cust_row_driver'];
        }else{
          $html_cust_row = $template['cust_row_driver'];
        }

        // $html_cust_row = $isPayment ? $template['cust_row_payment'] : $show_driver_assistant_setting ? $template['cust_row_driver']: $template['cust_row_driver'];
        $html_row = $template['row'];
        $html_last_row = $template['last_row'];
        $html_last_row_sst = $template['last_row_sst'];
        $html_group_by = $template['group_by'];
        file_put_contents('summary.log',$html_cust_row);
        if($isPayment){
          $html = str_replace('@doc_header_title','PAYMENT SUMMARY',$html);
        }else{
          $html = str_replace('@doc_header_title','SALES SUMMARY',$html);
        }

        $agent_info = $mysql->Execute("SELECT * FROM cms_login WHERE login_id = '{$salesperson_id}'");
        $agent_name = "";
        if(count($agent_info) > 0){
            $agent_name = $agent_info[0]['name'];
        }

        $client_info = $mysql->Execute("SELECT * FROM cms_mobile_module WHERE LENGTH(status) < 400 AND (module = 'app_client' OR module = 'app_client_info') ORDER BY module;");
        
        $client_name = "";
        $client_details = array();
        if(count($client_info) > 0){
            $client_name = $client_info[0]['status'];
            $client_name = explode('@n',$client_name)[0];
            $client_details = $client_info[1]['status'];
            if(!empty($client_details)){
                $client_details = json_decode($client_details,true);
            }
        }
        
        $client_addr = stripUnnecessary($client_details['address']).'<br>'.
                       stripUnnecessary($client_details['city']).' '.
                       stripUnnecessary($client_details['zipcode']).'<br>'.
                       stripUnnecessary($client_details['state']);

        $date_from = malaysianDate($date_from);
        $date_to = malaysianDate($date_to);
        $display_date = $date_from != $date_to ? "{$date_from} - {$date_to}" : $date_from;

        $html = str_replace('@client_website',stripUnnecessary($client_details['website']),$html);
        $html = str_replace('@client_phone',stripUnnecessary(str_replace('Tel No:','',$client_details['phone'])),$html);
        $html = str_replace('@client_email',stripUnnecessary($client_details['email']),$html);
        $html = str_replace('@client_addr',$client_addr,$html);
        $html = str_replace('@client_sub_name',$client_details['sub_name'],$html);
        $html = str_replace('@client_name',$client_name,$html);
        $html = str_replace('@agent_name',$agent_name,$html);
        $html = str_replace('@summary_date',$display_date,$html);

        if($isPayment == false){
          for ($i=0; $i < count($doc_types); $i++){
            $type = $doc_types[$i];
            $html_doc_total_amount = str_replace('@'.$type.'_amount',$doc_type_total[$type],$html_doc_total_amount);
          }
        }
        $html_doc_total_amount = str_replace('@payment_amount',$doc_type_total['payment'],$html_doc_total_amount);
        $html_doc_total_amount = str_replace('@payment_cash_amount',$doc_type_total['cash'],$html_doc_total_amount);
        $html_doc_total_amount = str_replace('@payment_other_amount',$doc_type_total['other'],$html_doc_total_amount);

        $innerHTML = '';
        $innerHTML .= $html_doc_total_amount;

        $cash_payment = array();
        $cash_payment_view = '';
        file_put_contents('summaryd.log',json_encode($result_set));
        foreach($result_set as $doc_type=>$data){
          $kkk = 0;
          if($billing_state_group_by){
            foreach ($data as $key => $value) {
              $numofRecord = count($value);
              if($isPayment && $doc_type != 'payment'){
                continue;
              }

              if($numofRecord > 0){
                $innerHTML .= $kkk === 0 ? str_replace('@doc_type',$doc_type_names[$doc_type],$html_doc_type) : '';
                $innerHTML .= str_replace('@group_by',$key." ({$numofRecord})",$html_group_by);
  
                for ($i=0; $i < $numofRecord; $i++) { 
                    $doc = $value[$i];
                    $doc_items = $doc['order_items'];
                    $item_row = '';
                    
                    $discount_view = '';
  
                    for ($j=0; $j < count($doc_items); $j++) { 
                        $disc = '';
                        $item = $doc_items[$j];
                        $disc .= $item['disc_1'] ? $item['disc_1'].'%' : '';
                        $disc .= $item['disc_2'] ? $item['disc_2'].'%' : '';
                        $disc .= $item['disc_3'] ? $item['disc_3'].'%' : '';
  
                        $discount_view = $item['discount_amount'] ? $item['disc_2'] == 0 && $item['disc_1'] == $item['discount_amount'] ? ' (RM'.number_format($item['discount_amount'],2).')' : ' ('.$disc.')' : '';
                        
  
                        $item_row .= str_replace(
                            array(
                                '@first',
                                '@second',
                                '@third'
                            ),
                            array(
                                htmlSafe($item['product_code']),
                                htmlSafe($item['product_name']),
                                isset($item['unit_uom']) ? 
                                ($item['quantity'].' '.$item['unit_uom'].' RM'.number_format($item['unit_price'],2).$discount_view) :
                                  ' RM'.number_format($item['payment_amount'],2)
                            ),
                            $html_row
                        );
                    }
                    $item_row .= str_replace('@grand_total',number_format($doc['grand_total'],2),$html_last_row);
                    if($allowToShowSST){
                      if($doc['gst_amount']){
                        $item_row .= str_replace('@grand_total',number_format($doc['gst_amount'],2),$html_last_row_sst);
                      }
                    }
  
                    if($doc_type == 'cash'){
                      if($cash_payment){
                        $found_method = 0;
                        for ($n=0; $n < count($cash_payment); $n++) { 
                          if($cash_payment[$n]['method'] == $doc['payment_method']){
                            $cash_payment[$n]['amount'] += $doc['grand_total'];
                            $found_method = 1;
                            break;
                          }
                        }
    
                        if($found_method == 0){
                          $cash_payment[] = array(
                            "method"=>$doc['payment_method'],
                            "amount"=>$doc['grand_total']
                          );
                        }
                      }else{
                        $cash_payment[] = array(
                          "method"=>$doc['payment_method'],
                          "amount"=>$doc['grand_total']
                        );
                      }
                    }
                    
  
                    if($show_driver_assistant_setting){
                      $innerHTML .= str_replace(
                          array(
                              '@customer_name',
                              '@doc_id',
                              '@doc_date',
                              '@item_row',
                              '@doc_remark',
                              '@driver',
                              '@assistant'
                          ),
                          array(
                              '['.$doc['cust_code'].']  '.$doc['cust_company_name'],
                              $doc['order_id'] . $doc['payment_method'],
                              malaysianDate($doc['order_date']),
                              $item_row,
                              isset($doc['payment_remark']) ?  
                              '<br>
                                <span style="color:grey;font-size:14px;font-weight:normal">'.
                                  ucfirst($doc['payment_remark']).
                              '</span>'
                              : '',
                              $doc['driver_name'],
                              $doc['asssitant_name']
                          ),
                          $html_cust_row
                      );
                    }else{
                      $innerHTML .= str_replace(
                          array(
                              '@customer_name',
                              '@doc_id',
                              '@doc_date',
                              '@item_row',
                              '@doc_remark'
                          ),
                          array(
                              '['.$doc['cust_code'].']  '.$doc['cust_company_name'],
                              $doc['order_id'] . $doc['payment_method'],
                              malaysianDate($doc['order_date']),
                              $item_row,
                              isset($doc['payment_remark']) ?  
                              '<br>
                                <span style="color:grey;font-size:14px;font-weight:normal">'.
                                  ucfirst($doc['payment_remark']).
                              '</span>'
                              : ''
                          ),
                          $html_cust_row
                      );
                    }

                    $kkk ++;
                }
              }
              
              if($doc_type == 'cash'){
                if($displayCashPayment){
                  for ($n=0; $n < count($cash_payment); $n++) { 
                    $cash_payment[$n]['method'] = $cash_payment[$n]['method'] ? $cash_payment[$n]['method'] : 'CASH';
                    $cash_payment_view .= '<tr><td align="right">'.$cash_payment[$n]['method'].': </td><td style="font-weight:bold;">RM '.number_format($cash_payment[$n]['amount'],2).'</td></tr>';
                  }
                }
              }
            }
          }else{
            $numofRecord = count($data);
            if($isPayment && $doc_type != 'payment'){
              continue;
            }

            if($numofRecord > 0){
              $innerHTML .= str_replace('@doc_type',$doc_type_names[$doc_type]." ({$numofRecord})",$html_doc_type);
              $innerHTML .= str_replace('@group_by',$key,$html_group_by);

              for ($i=0; $i < $numofRecord; $i++) { 
                  $doc = $data[$i];
                  $doc_items = $doc['order_items'];
                  $item_row = '';
                  
                  $discount_view = '';

                  for ($j=0; $j < count($doc_items); $j++) { 
                      $disc = '';
                      $item = $doc_items[$j];
                      $disc .= $item['disc_1'] ? $item['disc_1'].'%' : '';
                      $disc .= $item['disc_2'] ? $item['disc_2'].'%' : '';
                      $disc .= $item['disc_3'] ? $item['disc_3'].'%' : '';

                      $discount_view = $item['discount_amount'] ? $item['disc_2'] == 0 && $item['disc_1'] == $item['discount_amount'] ? ' (RM'.number_format($item['discount_amount'],2).')' : ' ('.$disc.')' : '';
                      

                      $item_row .= str_replace(
                          array(
                              '@first',
                              '@second',
                              '@third'
                          ),
                          array(
                              htmlSafe($item['product_code']),
                              htmlSafe($item['product_name']),
                              isset($item['unit_uom']) ? 
                              ($item['quantity'].' '.$item['unit_uom'].' RM'.number_format($item['unit_price'],2).$discount_view) :
                                ' RM'.number_format($item['payment_amount'],2)
                          ),
                          $html_row
                      );
                  }
                  $item_row .= str_replace('@grand_total',number_format($doc['grand_total'],2),$html_last_row);
                  if($allowToShowSST){
                    if($doc['gst_amount']){
                      $item_row .= str_replace('@grand_total',number_format($doc['gst_amount'],2),$html_last_row_sst);
                    }
                  }

                  if($doc_type == 'cash'){
                    if($cash_payment){
                      $found_method = 0;
                      for ($n=0; $n < count($cash_payment); $n++) { 
                        if($cash_payment[$n]['method'] == $doc['payment_method']){
                          $cash_payment[$n]['amount'] += $doc['grand_total'];
                          $found_method = 1;
                          break;
                        }
                      }
  
                      if($found_method == 0){
                        $cash_payment[] = array(
                          "method"=>$doc['payment_method'],
                          "amount"=>$doc['grand_total']
                        );
                      }
                    }else{
                      $cash_payment[] = array(
                        "method"=>$doc['payment_method'],
                        "amount"=>$doc['grand_total']
                      );
                    }
                  }
                  

                  if($show_driver_assistant_setting){
                    $innerHTML .= str_replace(
                        array(
                            '@customer_name',
                            '@doc_id',
                            '@doc_date',
                            '@item_row',
                            '@doc_remark',
                            '@driver',
                            '@assistant'
                        ),
                        array(
                            '['.$doc['cust_code'].']  '.$doc['cust_company_name'],
                            $doc['order_id'] . $doc['payment_method'],
                            malaysianDate($doc['order_date']),
                            $item_row,
                            isset($doc['payment_remark']) ?  
                            '<br>
                              <span style="color:grey;font-size:14px;font-weight:normal">'.
                                ucfirst($doc['payment_remark']).
                            '</span>'
                            : '',
                            $doc['driver_name'],
                            $doc['assistant_name']
                        ),
                        $html_cust_row
                    );
                  }else{
                    $innerHTML .= str_replace(
                        array(
                            '@customer_name',
                            '@doc_id',
                            '@doc_date',
                            '@item_row',
                            '@doc_remark'
                        ),
                        array(
                            '['.$doc['cust_code'].']  '.$doc['cust_company_name'],
                            $doc['order_id'] . $doc['payment_method'],
                            malaysianDate($doc['order_date']),
                            $item_row,
                            isset($doc['payment_remark']) ?  
                            '<br>
                              <span style="color:grey;font-size:14px;font-weight:normal">'.
                                ucfirst($doc['payment_remark']).
                            '</span>'
                            : ''
                        ),
                        $html_cust_row
                    );
                  }
              }
            }
            
            if($doc_type == 'cash'){
              if($displayCashPayment){
                for ($n=0; $n < count($cash_payment); $n++) { 
                  $cash_payment[$n]['method'] = $cash_payment[$n]['method'] ? $cash_payment[$n]['method'] : 'CASH';
                  $cash_payment_view .= '<tr><td align="right">'.$cash_payment[$n]['method'].': </td><td style="font-weight:bold;">RM '.number_format($cash_payment[$n]['amount'],2).'</td></tr>';
                }
              }
            }
          }
        }
        $innerHTML = str_replace('@cash_payment',$cash_payment_view, $innerHTML);
        $html = str_replace('@document_row',$innerHTML,$html);
        echo json_encode(array('data'=>base64_encode($html)));
    }
}
function stripUnnecessary($line){
    $newline = str_replace('@n','',$line);
    if(empty($newline)){
        return '-';
    }else{
        return $newline;
    }
}
function malaysianDate($date, $withTime = false){
    $format = 'd/m/Y';
    if($withTime){
        $format = "d/m/Y H:i";
    }
    return date_format(date_create($date),$format);
}
function htmlSafe($var){
  return str_replace(array("\\\\","#")," ",$var);
}
?>