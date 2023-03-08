<?php
require_once('./model/MySQL.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
date_default_timezone_set('Asia/Kuala_Lumpur');

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
            box-sizing: border-box;
          }
          body{
            font-family: sans-serif;
            font-size:14px; line-height:16px;
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
            page-break-after:auto;
            page-break-inside:avoid;
          }
          th {
            border-top: 1px solid black;
            padding-top:3px;
            border-bottom: 1px solid black;
            font-size:12px;
          }
          td{
              height:14px;
              page-break-inside:avoid;
              page-break-after:auto 
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
          tr    { page-break-inside:avoid; page-break-after:auto }
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
            SALES ITEM SUMMARY
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
        @data_view
      </div>
      @page_break
      @item_list_data_view
    </body>
</html>';

$template['doc_type'] = '
<h3 size="pixels" class="pixels" style="margin-bottom:10px;margin-top:10px;">
    @doc_type
</h3>';

$template['group_by'] = '
<h5 size="pixels" class="pixels" style="margin-bottom:10px;margin-top:10px;text-align:center;">
    @group_by
</h5>';

$template['first_row'] = '
<table style="width:100%;border:0px solid white;">
    <col width="55%">
    <col width="15%">
    <col width="15%">
    <col width="15%">
    <tr>
        <th align="left">Item List Summary</th>
        <th align="right" style="padding-right:10px;">Unit Price</th>
        <th align="right" style="padding-right:10px;">Quantity</th>
        <th align="right">Amount</th>
    </tr>
    @document_row
</table>';

$template['row'] = '
<tr style="background-color:white">
    <td align="left">@first</td>
    <td align="right" style="padding-right:10px;">@second</td>
    <td align="right" style="padding-right:10px;">@third</td>
    <td align="right">@forth</td>
</tr>';

$template['last_row'] = '
<tr style="background-color:white">
    <td align="left" style="border-top:1px solid black;">@first</td>
    <td align="right" style="padding-right:10px;border-top:1px solid black;">@second</td>
    <td align="right" style="padding-right:10px;border-top:1px solid black;">@third</td>
    <td align="right" style="border-top:1px solid black;">@forth</td>
</tr>';

$doc_type_names = array(
    'sales'=>'SALES ORDER',
    'invoice'=>'INVOICE',
    'cash'=>'CASH SALES',
    'credit'=>'CREDIT NOTE'
);

if(
    isset($_GET['salesperson_id']) && 
    isset($_GET['client']) && 
    isset($_GET['date_from']) &&
    isset($_GET['date_to'])
){
    $settings = parse_ini_file('../config.ini',true);

    $salesperson_id = $_GET['salesperson_id'];
    $date_from = $_GET['date_from'];
    $date_to = $_GET['date_to'];
    $client = $_GET['client'];
    $printType = $_GET['print_type'];
    $warehouseCodes = isset($_GET['wh_code']) ? explode(',',$_GET['wh_code']) : array();
    $doc_type = $_GET['doc_type'] ? strtolower($_GET['doc_type']) : '';

    $stock_balance = isset($_GET['stockbalance']) ? intval($_GET['stockbalance']) : 0;
    $item_transaction_record = $settings['Item_Transaction_Record'];
    $show_item_transaction_record = in_array($client,$item_transaction_record['item_transaction_record']);

    $group_by_price = $settings['Group_By_Price'];
    $show_group_by_price = in_array($client,$group_by_price['group_by_price']);
    
    $summary_group_by	= $settings['SUMMARY_GROUP_BY'];
    $billing_state_group_by = in_array($client,$summary_group_by['billing_state']);


    file_put_contents('get.log',json_encode($_GET));

    $settings = $settings[$client];
    $mysql = new MySQL($settings);


    if(!$doc_type){
      $doc_types = array(
          'sales',
          'cash',
          'invoice',
          'credit'
      );
    }else{
      $doc_types = array(
          $doc_type
      );
    }

    $result_set = array();
    $stock_balance_arr = array();

    $summary_group_by_query = $billing_state_group_by ? ',billing_state' : '';

    for ($i=0; $i < count($doc_types); $i++) { 
        $doc = $doc_types[$i];
        $group_by_price_query = $show_group_by_price ? ', coi.unit_price' : '';
        $q = "SELECT cp.category_id,coi.product_name,coi.product_code, sum(quantity) as quantity, coi.unit_price, coi.disc_1, coi.disc_2, coi.disc_3, coi.discount_amount, unit_uom, SUM(sub_total) AS sub_total {$summary_group_by_query} FROM cms_order o LEFT JOIN cms_order_item coi ON o.order_id = coi.order_id AND coi.cancel_status = 0 LEFT JOIN cms_product cp on coi.product_code = cp.product_code WHERE doc_type IN ('{$doc}') AND order_status > 0 AND salesperson_id = '{$salesperson_id}' AND o.cancel_status = 0 AND DATE(order_date) BETWEEN '{$date_from}' AND '{$date_to}' GROUP BY coi.product_code, unit_uom ".$group_by_price_query."{$summary_group_by_query} ORDER BY product_code DESC";
        file_put_contents('summary.log',$q);
        $documents = $mysql->Execute($q);
        // $total_quantity = 0;
        // $total_amount = 0;
        $special_group_array = array();
        for ($j=0; $j < count($documents); $j++) { 

          // $total_quantity += $documents[$j]['quantity'];
          // $total_amount += $documents[$j]['sub_total'];

          $documents[$j]['quantity'] = $documents[$j]['quantity'].' '.$documents[$j]['unit_uom'];
          $documents[$j]['unit_price'] = number_format($documents[$j]['unit_price'],2);
          $documents[$j]['format_sub_total'] = number_format($documents[$j]['sub_total'],2);

          $disc = '';
          $disc .= $documents[$j]['disc_1'] ? $documents[$j]['disc_1'].'%' : '';
          $disc .= $documents[$j]['disc_2'] ? $documents[$j]['disc_2'].'%' : '';
          $disc .= $documents[$j]['disc_3'] ? $documents[$j]['disc_3'].'%' : '';

          $documents[$j]['discount'] = $item['discount_amount'] ? $item['disc_2'] == 0 && $item['disc_1'] == $item['discount_amount'] ? ' (RM'.number_format($item['discount_amount'],2).')' : ' ('.$disc.')' : '';

          if($billing_state_group_by){
            $special_group_array[$documents[$j]['billing_state']][] = $documents[$j];
          }

        }
        $result_set[$doc] = $billing_state_group_by ? $special_group_array : $documents;
    }

    if($show_item_transaction_record){
      $date_cond = " DATE(doc_date) BETWEEN '{$date_from}' AND '{$date_to}' AND ";
      if($stock_balance){
        $custom_date = 'CURRENT_DATE()-INTERVAL 1 YEAR';
        $date_cond = " DATE(doc_date) BETWEEN {$custom_date} AND CURRENT_DATE()+INTERVAL 1 MONTH AND ";
      }
      if(count($warehouseCodes)>0){
        $item_summary_list_view = '';
        for ($i=0; $i < count($warehouseCodes); $i++) { 
          $wh_code = $warehouseCodes[$i];

          $in_stock_query = "SELECT SUM(quantity) AS quantity, product_code, DATE(doc_date) AS date FROM cms_stock_card
          WHERE {$date_cond} quantity > 0 AND location = '".$wh_code."' AND cms_stock_card.cancelled = 'F' GROUP BY DATE(doc_date), product_code ORDER BY product_code, DATE(doc_date)";
           file_put_contents('bal_in.log',$in_stock_query);
          $in_item = $mysql->Execute($in_stock_query);
          $in_item_array = array();
          for ($j=0; $j < count($in_item); $j++) { 
            $result = $in_item[$j];
            $in_item_array[$result['date'].'|'.$result['product_code']] = $result['quantity'];
          }

          $out_stock_query = "SELECT SUM(quantity) AS quantity, product_code, DATE(doc_date) AS date FROM cms_stock_card
          WHERE {$date_cond} quantity < 0 AND location = '".$wh_code."' AND cms_stock_card.cancelled = 'F' GROUP BY DATE(doc_date), product_code ORDER BY product_code, DATE(doc_date)";
          file_put_contents('bal_out.log',$out_stock_query);
          $out_item = $mysql->Execute($out_stock_query);
          $out_item_array = array();
          for ($k=0; $k < count($out_item); $k++) { 
            $result = $out_item[$k];
            $out_item_array[$result['date'].'|'.$result['product_code']] = $result['quantity'];
          }

          $item_summary_array = array();
          $query = "SELECT product_name, cms_stock_card.product_code, unit_uom, DATE(doc_date) AS date FROM cms_stock_card
          JOIN cms_product ON cms_product.product_code = cms_stock_card.product_code
          WHERE {$date_cond} location = '".$wh_code."' AND cms_stock_card.cancelled = 'F'
          GROUP BY DATE(doc_date), cms_stock_card.product_code ORDER BY cms_stock_card.product_code, DATE(doc_date) ASC";
          file_put_contents('bal_app.log',$query);
          $items = $mysql->Execute($query);
          
          for ($l=0; $l < count($items); $l++) { 
                $product_name = $items[$l]['product_name'];
                $product_code = $items[$l]['product_code'];
                $order_date = $items[$l]['date'];

                $quantity = $in_item_array[$order_date.'|'.$product_code] ? $in_item_array[$order_date.'|'.$product_code] : 0;
                $out_quantity = $out_item_array[$order_date.'|'.$product_code] ? $out_item_array[$order_date.'|'.$product_code] : 0;
                $app_quantity = $app_item_array[$order_date.'|'.$product_code] ? $app_item_array[$order_date.'|'.$product_code] : 0;
              $_date = date_format(date_create($order_date),"d/m/Y");
                $summary = array(
                  "product_name"=>$product_name,
                  "product_code"=>$product_code,
                  "quantity"=>$quantity,
                  "order_date"=>$_date,
                  "out_quantity"=>$out_quantity,
                  "app_quantity"=>$app_quantity,
                  "unit_uom"=>$items[$l]['unit_uom']
                );

              $item_summary_array[$product_name.' ('.$product_code.')'][] = $summary;
              
          }
          foreach($item_summary_array as $key => $value){
            usort($f, function($a, $b)
            {
              return (($a["app_quantity"] < $b["app_quantity"]) ? -1 : 1);
            });	
          }
  
          $page_break = '<p style="page-break-before: always"></p>';
          $item_summary_list_view .= '<p style="text-align:center;display:inline-block;width:100%;font-weight:bold;font-size:19px;">Stock Card ('.$wh_code.')</p>';
          
          foreach ($item_summary_array as $key => $value) {
            $newObj = array();
            $product_name = $key;
            $item_summary_list_view .= '
            <table style="width:100%;">
              <tr style="border-bottom:3px double black;">
                <th align="left" style="width:40%">'.$product_name.'</th>
                <th align="center" style="width:20%">IN</th>
                <th align="center" style="width:20%">OUT</th>
              </tr>
            ';
  
            $total = 0;
            $total_after_app = 0;
            for ($m=0; $m < count($value); $m++) { 
              $order_date = $value[$m]['order_date'];
              $name = $value[$m]['product_name'];
              $in_quantity = $value[$m]['quantity'];
              $out_quantity = floatval($value[$m]['out_quantity']) < 0 ? $value[$m]['out_quantity'] * -1 : $value[$m]['out_quantity'];
              $app_quantity = $value[$m]['app_quantity'] ? $value[$m]['app_quantity'] : 0;
              $app_quantity = floatval($app_quantity) < 0 ? $app_quantity * -1 : $app_quantity;
              $total += $in_quantity - $out_quantity;
              $total_after_app += $in_quantity - $out_quantity - $value[$m]['app_quantity'];
              
              $item_summary_list_view .='
                <tr>
                  <td>'.$order_date.'</td>
                  <td style="text-align:center;">'.number_format($in_quantity,2).'</td>
                  <td style="text-align:center;">'.number_format($out_quantity,2).'</td>
                </tr>
              ';

              $newObj['product_code'] = $value[$m]['product_code'];
              $newObj['product_name'] = $value[$m]['product_name'];
              $newObj['unit_uom'] = $value[$m]['unit_uom'];
            }
            $item_summary_list_view .= '
              <tr style="border-top:1px solid black;">
                <td></td>
                <td style="text-align:center;font-weight:bold;">Balance</td>
                <td style="text-align:center;">'.number_format($total,2).'</td>
              </tr>
              </table><hr>
            ';
            $newObj['balance'] = $total;
            if(floatval($newObj['balance']) != 0){
              $stock_balance_arr[] = $newObj;
            }
          }
          file_put_contents('bal_res.log',json_encode($stock_balance_arr));
        }
      }else{

        $in_stock_query = "SELECT SUM(quantity) AS quantity, product_code, DATE(doc_date) AS date FROM cms_stock_card
        JOIN cms_login ON cms_login.staff_code = cms_stock_card.location WHERE DATE(doc_date) BETWEEN '{$date_from}' AND '{$date_to}' AND quantity > 0 AND login_id = '{$salesperson_id}' AND cms_stock_card.cancelled = 'F' GROUP BY DATE(doc_date), product_code ORDER BY product_code, DATE(doc_date)";

        $in_item = $mysql->Execute($in_stock_query);
        $in_item_array = array();
        for ($i=0; $i < count($in_item); $i++) { 
          $result = $in_item[$i];
          $in_item_array[$result['date'].'|'.$result['product_code']] = $result['quantity'];
        }

        $out_stock_query = "SELECT SUM(quantity * -1) AS quantity, product_code, DATE(doc_date) AS date FROM cms_stock_card JOIN cms_login ON cms_login.staff_code = cms_stock_card.location WHERE DATE(doc_date) BETWEEN '{$date_from}' AND '{$date_to}' AND quantity < 0 AND login_id = '{$salesperson_id}'  AND cms_stock_card.cancelled = 'F' GROUP BY DATE(doc_date), product_code ORDER BY product_code, DATE(doc_date)";

        $out_item = $mysql->Execute($out_stock_query);
        $out_item_array = array();
        for ($i=0; $i < count($out_item); $i++) { 
          $result = $out_item[$i];
          $out_item_array[$result['date'].'|'.$result['product_code']] = $result['quantity'];
        }

        $app_item = $mysql->Execute("SELECT SUM(quantity) AS quantity, product_code, DATE(order_date) AS date FROM cms_order_item
        LEFT JOIN cms_order ON cms_order.order_id = cms_order_item.order_id WHERE DATE(order_date) BETWEEN '{$date_from}' AND '{$date_to}' AND salesperson_id = '{$salesperson_id}' AND order_status = 1 AND cms_order.cancel_status = '0' AND cms_order_item.cancel_status = 0
        GROUP BY DATE(order_date), product_code ORDER BY product_code, DATE(order_date)");
        $app_item_array = array();
        for ($i=0; $i < count($app_item); $i++) { 
          $result = $app_item[$i];
          $app_item_array[$result['date'].'|'.$result['product_code']] = $result['quantity'];
        }
      
        $item_summary_array = array();
        $order_item_query = "SELECT SUM(quantity) AS quantity, product_name, product_code, DATE(order_date) AS date FROM cms_order_item LEFT JOIN cms_order ON cms_order.order_id = cms_order_item.order_id WHERE DATE(order_date) BETWEEN '{$date_from}' AND '{$date_to}' AND salesperson_id = '{$salesperson_id}' AND order_status = 1 AND cms_order.cancel_status = '0' AND cms_order_item.cancel_status = 0 GROUP BY DATE(order_date), product_code ORDER BY product_code, DATE(order_date)";
  
        $order_item = $mysql->Execute($order_item_query);
        for($i=0; $i < count($order_item); $i++){ 
          $product_name = $order_item[$i]['product_name'];
          $product_code = $order_item[$i]['product_code'];
          $order_date = $order_item[$i]['date'];
          $app_quantity = $app_item_array[$order_date.'|'.$product_code] ? $app_item_array[$order_date.'|'.$product_code] : 0;
  
          $summary = array(
            "product_name"=>$product_name,
            "product_code"=>$product_code,
            "order_date"=>date_format(date_create($order_date),"d/m/Y"),
            "app_quantity"=>$app_quantity
          );
        }

        $query = "SELECT product_name, cms_stock_card.product_code, unit_uom, DATE(doc_date) AS date FROM cms_stock_card
        JOIN cms_product ON cms_product.product_code = cms_stock_card.product_code
        JOIN cms_login ON cms_login.staff_code = cms_stock_card.location
        WHERE DATE(doc_date) BETWEEN '{$date_from}' AND '{$date_to}' AND login_id = '{$salesperson_id}' AND cms_stock_card.cancelled = 'F'
        GROUP BY DATE(doc_date), cms_stock_card.product_code ORDER BY cms_stock_card.product_code, DATE(doc_date) ASC";

        $items = $mysql->Execute($query);

        for ($k=0; $k < count($items); $k++) { 
              $product_name = $items[$k]['product_name'];
              $product_code = $items[$k]['product_code'];
              $order_date = $items[$k]['date'];

              $quantity = $in_item_array[$order_date.'|'.$product_code] ? $in_item_array[$order_date.'|'.$product_code] : 0;
              $out_quantity = $out_item_array[$order_date.'|'.$product_code] ? $out_item_array[$order_date.'|'.$product_code] : 0;
              $app_quantity = $app_item_array[$order_date.'|'.$product_code] ? $app_item_array[$order_date.'|'.$product_code] : 0;

              $summary = array(
                "product_name"=>$product_name,
                "product_code"=>$product_code,
                "quantity"=>$quantity,
                "order_date"=>date_format(date_create($order_date),"d/m/Y"),
                "out_quantity"=>$out_quantity,
                "app_quantity"=>$app_quantity
              );
    
            $item_summary_array[$product_name.' ('.$product_code.')'][] = $summary;
        }

        foreach($item_summary_array as $key => $value){	
          usort($f, function($a, $b)
          {
            return (($a["app_quantity"] < $b["app_quantity"]) ? -1 : 1);
          });	
        }

        $page_break = '<p style="page-break-before: always"></p>';
        $item_summary_list_view = '<p style="text-align:center;display:inline-block;width:100%;font-weight:bold;font-size:19px;">Stock Card</p>';

        foreach ($item_summary_array as $key => $value) {
          $product_name = $key;
          $item_summary_list_view .= '
          <table style="width:100%;">
            <tr style="border-bottom:3px double black;">
              <th align="left" style="width:40%">'.$product_name.'</th>
              <th align="center" style="width:20%">IN</th>
              <th align="center" style="width:20%">OUT</th>
            </tr>
          ';

          $total = 0;
          $total_after_app = 0;
          for ($i=0; $i < count($value); $i++) { 
            $order_date = $value[$i]['order_date'];
            $name = $value[$i]['product_name'];
            $in_quantity = $value[$i]['quantity'];
            $out_quantity = $value[$i]['out_quantity'];
            $app_quantity = $value[$i]['app_quantity'] ? $value[$i]['app_quantity'] : '';
            $total += $in_quantity - $out_quantity;
            $total_after_app += $in_quantity - $out_quantity - $value[$i]['app_quantity'];

            $item_summary_list_view .='
              <tr>
                <td>'.$order_date.'</td>
                <td style="text-align:center;">'.number_format($in_quantity,2).'</td>
                <td style="text-align:center;">'.number_format($out_quantity,2).'</td>
              </tr>
            ';
          }
          $item_summary_list_view .= '
            <tr style="border-top:1px solid black;">
              <td></td>
              <td style="text-align:center;font-weight:bold;">Balance</td>
              <td style="text-align:center;">'.number_format($total,2).'</td>
            </tr>
            </table><hr>
          ';
        }
      }
      file_put_contents('bal_in.log',$in_stock_query);
    }

    if($printType == 'bluetooth'){
        // foreach ($result_set as $key => $value) {
        //   for ($i=0; $i < count($value); $i++) { 
        //     $value[$i]['quantity'] = $value[$i]['quantity'].$value[$i]['unit_uom'];
        //   }
        // }
        //file_put_contents('q.log',json_encode($result_set));

        echo json_encode(array('data'=>$stock_balance ? $stock_balance_arr : $result_set));
        return;
    }else{
        $html = $template['default'];
        $html_doc_type = $template['doc_type'];
        $html_first_row = $template['first_row'];
        $html_row = $template['row'];
        $html_last_row = $template['last_row'];
        $html_group_by = $template['group_by'];

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
        $html = str_replace('@client_phone',stripUnnecessary($client_details['phone']),$html);
        $html = str_replace('@client_email',stripUnnecessary($client_details['email']),$html);
        $html = str_replace('@client_addr',$client_addr,$html);
        $html = str_replace('@client_sub_name',$client_details['sub_name'],$html);
        $html = str_replace('@client_name',$client_name,$html);
        $html = str_replace('@agent_name',$agent_name,$html);
        $html = str_replace('@summary_date',$display_date,$html);

        $innerHTML = '';

        file_put_contents('summary.log',json_encode($result_set));
        foreach($result_set as $doc_type=>$data){
          if(!strpos($doc_type, 'total')){

            $kkk = 0;
            if($billing_state_group_by){
              foreach ($data as $key => $value) {

                $innerHTML .= $kkk === 0 ? str_replace('@doc_type', $doc_type_names[$doc_type], $html_doc_type) : '';

                if(count($value) > 0){
                  $item_row = '';
                  $html_header = '';
                  $sum_quantity = 0;
                  $total_amount = 0;

                  $html_header = str_replace('@group_by',$key,$html_group_by);
                  $html_header .= $html_first_row;

                  for ($i=0; $i < count($value); $i++) { 
                      $j = $i - 1;
                      $item = $value[$i];
                      $old_item = $j >= 0? $value[$j] : '';
                  
                      $display_product = htmlSafe($item['product_code'].' - '.$item['product_name']);
                      if($old_item){
                        if($old_item['product_code'] == $item['product_code']){
                          $display_product = '';
                        }
                      }
      
                      $disc = '';
                      $disc .= $item['disc_1'] ? $item['disc_1'].'%' : '';
                      $disc .= $item['disc_2'] ? $item['disc_2'].'%' : '';
                      $disc .= $item['disc_3'] ? $item['disc_3'].'%' : '';
      
                      $discount_view = $item['discount_amount'] ? $item['disc_2'] == 0 && $item['disc_1'] == $item['discount_amount'] ? ' (RM'.number_format($item['discount_amount'],2).')' : ' ('.$disc.')' : '';
      
                      $sum_quantity += floatval($item['quantity']);
                      $sum_amount = floatval($item['quantity']) * $item['unit_price'];
                      $total_amount += $sum_amount;
      
                      $item_row .= str_replace(
                          array(
                              '@first',
                              '@second',
                              '@third',
                              '@forth'
                          ),
                          array(
                              $display_product,
                              'RM'.number_format($item['unit_price'],2).$discount_view,
                              $item['quantity'],
                              'RM'.number_format($sum_amount,2)
                          ),
                          $html_row
                      );
                  }
      
                  $item_row .= str_replace(
                    array(
                        '@first',
                        '@second',
                        '@third',
                        '@forth'
                    ),
                    array(
                        'Total',
                        '',
                        $sum_quantity,
                        'RM'.number_format($total_amount,2)
                    ),
                    $html_last_row
                  );
      
                  $innerHTML .= str_replace(
                      array(
                          ' @document_row'
                      ),
                      array(
                          $item_row
                      ),
                      $html_header
                  );
                }

                $kkk ++;
              }
            }else{
              $each = '';
              $each .= $html_doc_type . $html_first_row;
              if(count($data) > 0){

                $item_row = '';
                $sum_quantity = 0;
                $total_amount = 0;
                for ($i=0; $i < count($data); $i++) { 
                    $j = $i - 1;
                    $item = $data[$i];
                    $old_item = $j >= 0? $data[$j] : '';
                
                    $display_product = htmlSafe($item['product_code'].' - '.$item['product_name']);
                    if($old_item){
                      if($old_item['product_code'] == $item['product_code']){
                        $display_product = '';
                      }
                    }
    
                    $disc = '';
                    $disc .= $item['disc_1'] ? $item['disc_1'].'%' : '';
                    $disc .= $item['disc_2'] ? $item['disc_2'].'%' : '';
                    $disc .= $item['disc_3'] ? $item['disc_3'].'%' : '';
    
                    $discount_view = $item['discount_amount'] ? $item['disc_2'] == 0 && $item['disc_1'] == $item['discount_amount'] ? ' (RM'.number_format($item['discount_amount'],2).')' : ' ('.$disc.')' : '';
    
                    $sum_quantity += floatval($item['quantity']);
                    $sum_amount = floatval($item['quantity']) * $item['unit_price'];
                    $total_amount += $sum_amount;
    
                    $item_row .= str_replace(
                        array(
                            '@first',
                            '@second',
                            '@third',
                            '@forth'
                        ),
                        array(
                            $display_product,
                            'RM'.number_format($item['unit_price'],2).$discount_view,
                            $item['quantity'],
                            'RM'.number_format($sum_amount,2)
                        ),
                        $html_row
                    );
                }
    
                $item_row .= str_replace(
                  array(
                      '@first',
                      '@second',
                      '@third',
                      '@forth'
                  ),
                  array(
                      'Total',
                      '',
                      $sum_quantity,
                      'RM'.number_format($total_amount,2)
                  ),
                  $html_last_row
                );
    
                $innerHTML .= str_replace(
                    array(
                        '@doc_type',
                        ' @document_row'
                    ),
                    array(
                        $doc_type_names[$doc_type],
                        $item_row
                    ),
                    $each
                );
              }
            }

          }
        }
        $html = str_replace('@data_view',$innerHTML,$html);

        if($innerHTML && $item_summary_list_view){
          $html = str_replace('@page_break',$page_break,$html);
        }else{
          $html = str_replace('@page_break','',$html);
        }

        if(!$show_item_transaction_record && count($item_summary_array) <= 0){
          $html = str_replace('@item_list_data_view','',$html);
        }else{
          $html = str_replace('@item_list_data_view',$item_summary_list_view,$html);
        }
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

function cmp($a, $b) {
  return strcmp($a->quantity, $b->quantity);
}

?>