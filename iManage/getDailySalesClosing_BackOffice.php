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
          @media print {
            .break_page{
              size: A4 landscape;
              font-size:13px;
            }
          }
          .pixels{ font-weight:bold; padding:0px; margin:0px; }
          p{ line-height: 0.1; } 
          h2,h3,h4{ line-height: 0.4; margin:0px; }
          hr{ height:0px; }
          * {
            box-sizing: border-box;
          }
          body{
            font-family: sans-serif;
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
            page-break-inside:avoid;
            margin-top:10px;
          }
          .header {
            border: 3px double black;
            font-size:14px;
            text-align:center;
            padding:5px;
          }
          td{
              height:14px;
              page-break-inside:avoid;
          }
          .small-font{
              font-size:13px;
              line-height:13px;
          }
          .order_date{
              font-weight:bold;
              text-decoration:underline;
              text-align:left;
              padding-top:10px;
          }
          hr.monthlyDivider { 
            margin: 0em;
            border-width: 2px;
          } 
          .last_row{
            border-top:1px solid black;
            border-bottom: 1px solid black;
          }
          .number{
            text-align:right;
          }
          .sub_row{
            border:1px solid black;
          }
          .sub_title{
            float:left;
            font-weight:bold;

          }
          .sub_amount{
            float:right;
          }
          .top {
            vertical-align:top;
          }
          .space {
            padding:10px;
          }
          .break_page{
            page-break-after:auto; 
            size: A4 landscape;
          }
          .min_width{
            min-width:150px;
          }
          tr    { page-break-inside:avoid; page-break-after:auto }
        </style>
    </head>
    <body style="padding:10px">
      <div style="text-align:center;">
          <h3 size="pixels" class="pixels">
            DAILY SALES
          </h3>
          <p>On Date: @selected_date</p>
          <p style="text-align:left;"></p>
          @data_view
      </div>
    </body>
</html>';

$template['table'] = '
<div class="break_page">
  <table style="width:100%;">
    <thead>
      <tr>
        <th class="header">
          No
        </th>
        <th class="header">
          Lorry
        </th>
        <th class="header">
          Driver
        </th>
        <th class="header">
          Cash Sales
        </th>
        <th class="header">
          Credit Sales
        </th>
        <th class="header">
          Total
        </th>
        @category_row
      </tr>
    </thead>
    <tbody> 
      @details
    </tbody>
  </table>
  <table width:100%;>
      <thead>
        <tr>
          <th>
            TOTAL CASH SALES
          </th>
          @sub_category_row
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="top space sub_row">
            <b class="sub_title">CASH</b><b class="sub_amount">@cash_amount</b><br>
            <b class="sub_title">TNG</b><b class="sub_amount">@tng_amount</b><br>
            <b class="sub_title" >ONLINE</b><b class="sub_amount" >@online_amount</b><br>
            <b class="sub_title">E-WALLET</b><b class="sub_amount">@wallet_amount</b><br>
            <b class="sub_title">CCS</b><b class="sub_amount">@ccs_amount</b><br>
          </td>
          @sub_item_row
        </tr>
        <tr>
          <td class="sub_row space">
            <b class="sub_title">TOTAL</b><b class="sub_amount">@total_amount</b><br>
          </td>
          @sub_total_row
        </tr>
      </tbody>
  </table>
</div>
';

$template['row'] = '
<tr>
  <td>@no</td>
  <td>@salesperson</td>
  <td>@driver</td>
  <td class="number">@cash_amount</td>
  <td class="number">@credit_amount</td>
  <td class="number">@total_amount</td>
  @category_amount_row
</tr>';

$template['last_row'] = '
<tr>
  <td></td>
  <td></td>
  <td></td>
  <td class="last_row number">@cash_total_amount</td>
  <td class="last_row number">@credit_total_amount</td>
  <td class="last_row number">@all_total_amount</td>
  @last_category_amount_row
</tr>';

$template['sub_row'] = '
  <b class="sub_title" style="width:60%;text-align:left;">@item_name</b><b class="sub_amount" style="width:40%;">@item_amount</b><br>
';

$template['sub_last_row'] = '
  <td class="sub_row space min_width">
    <b class="sub_title">TOTAL</b><b class="sub_amount">@total_amount</b>
  </td>
';



if(
    isset($_GET['client']) && 
    isset($_GET['date_from']) &&
    isset($_GET['date_to'])
){
    $settings = parse_ini_file('../config.ini',true);

    $date_from = $_GET['date_from'];
    $date_to = $_GET['date_to'];
    $client = $_GET['client'];
    $salesperson = $_GET['salesperson'];
    $product_code = $_GET['items'] ? $_GET['items'] : '';
    $product_code_array = $product_code ? explode(",",$product_code) : array();

    $settings = $settings[$client];
    $mysql = new MySQL($settings);

    $result_set = array();
    
    $query = "SELECT * FROM cms_login";
    if($salesperson != ''){
        $query .= ' WHERE login_id IN (' . $salesperson .')';
        $co_sp = ' AND co.salesperson_id IN (' . $salesperson .')';
        $sp = ' AND salesperson_id IN (' . $salesperson .')';
    }else{
      $co_sp = '';
      $sp = '';
    }
    $agent_info = $mysql->Execute($query);
    $agent_array = array();

    for ($i=0; $i < count($agent_info); $i++) { 
      $agent_array[$agent_info[$i]['login_id']] = $agent_info[$i]['staff_code'];
    }

    $product_array = array();
    $product = $mysql->Execute("SELECT product_code, product_remark FROM cms_product WHERE product_status = 1");
    for ($i=0; $i < count($product); $i++) { 
      
      $product_array[$product[$i]['product_code']] = $product[$i]['product_remark'];
    }

    if($date_to != $date_from){
      $selected_date = $date_from . ' TO ' . $date_to;
    }else{
      $selected_date = $date_from;
    }

    $date_different = strtotime($date_to) - strtotime($date_from);
    $dateFrom=date_create($date_to);
    $dateTo=date_create($date_from);
    $diff=date_diff($dateFrom,$dateTo)->format("%a");

    $date = $selected_date;//$date_from.'-'.$date_to;//date("Y-m-d" , strtotime("+".$k." day", strtotime($date_from)));

      $documents = array();
      $item_array = array();
      $sp_item_array = array();
      $category_array = array();
      $sum_array = array();

      $order_item_query = "SELECT product_code, product_name, ROUND(SUM(quantity),2) AS total, salesperson_id, driver_name FROM cms_order co LEFT JOIN cms_order_item coi ON coi.order_id = co.order_id WHERE delivery_date BETWEEN '".$date_from." 00:00:00' AND '".$date_to." 23:59:59' ".$co_sp." AND co.cancel_status = 0 AND coi.cancel_status = 0";   

      $order_item_query .= $product_code_array ? " AND product_code IN ('".implode("','",$product_code_array)."')" : '';

      $order_item_query .= " GROUP BY salesperson_id, product_code";

      $order_item = $mysql->Execute($order_item_query);
      for ($l=0; $l < count($order_item); $l++) { 
        $each = $order_item[$l];
        $category_name = $product_array[$each['product_code']];
        $staff_code = $agent_array[$each['salesperson_id']];
        $array = array(
          "name" => $each['product_name'],
          "code" => $each['product_code'],
          "total" => $each['total']
        );
        if(!in_array($category_name,$category_array)){
          $category_array[] = $category_name;
        }
        $sp_item_array[$category_name][$staff_code] += $each['total'];

        if($item_array[$category_name][$each['product_code']]){
          $item_array[$category_name][$each['product_code']]['total'] += $each['total'];
        }else{
          $item_array[$category_name][$each['product_code']] = $array;
        }

        $sum_array[$category_name] += $each['total'];
      }

      $category_set[$date] = $category_array;
      $sp_item_array_set[$date] = $sp_item_array;
      $item_array_set[$date] = $item_array;
      $sum_array_set[$date] = $sum_array;

      $payment_query = "SELECT payment_method, cpd.payment_amount, payment_by, order_id, grand_total FROM cms_order co LEFT JOIN cms_payment cp ON description = order_id AND cp.cancel_status = 0 LEFT JOIN cms_payment_detail cpd on cp.payment_id = cpd.payment_id AND cpd.cancel_status= 0 WHERE delivery_date BETWEEN '".$date_from." 00:00:00' AND '".$date_to." 23:59:59' AND co.cancel_status = 0 AND co.doc_type = 'cash'";
      $payment = $mysql->Execute($payment_query);

      $payment_array = array();
      $payment_array['tng'] = 0;$payment_array['wallet'] = 0;$payment_array['online'] = 0;$payment_array['cash'] = 0;$payment_array['ccs'] = 0;$payment_array['total']=0;
      for ($l=0; $l < count($payment); $l++) { 
        
        $payment_array['total'] += $payment[$l]['grand_total'];

        if($payment[$l]['payment_method'] == 'Wire payment'){
          if($payment[$l]['payment_by'] == '327-000-TNG'){
            $payment_array['tng'] += $payment[$l]['grand_total'];
          }else if($payment[$l]['payment_by'] == '300-001-ONLINE'){
            $payment_array['online'] += $payment[$l]['grand_total'];
          }else if($payment[$l]['payment_by'] == '328-000-WALLET APP'){
            $payment_array['wallet'] += $payment[$l]['grand_total'];
          }else if($payment[$l]['payment_by'] == '300-008-CCS'){
            $payment_array['ccs'] += $payment[$l]['grand_total'];
          }
        }else{
          $payment_array['cash'] += $payment[$l]['grand_total']; 
        }
      }

      $payment_array_set[$date] = $payment_array;


      $q = "SELECT ROUND(SUM(grand_total),2) AS total , driver_name, salesperson_id FROM cms_order WHERE delivery_date BETWEEN '".$date." 00:00:00' AND '".$date." 23:59:59' ".$sp." AND cancel_status = 0 AND doc_type ='cash' GROUP BY salesperson_id, driver_name";

      $cash_documents = $mysql->Execute($q);
      for ($j=0; $j < count($cash_documents); $j++) { 
        $each = $cash_documents[$j];
        $staff_code = $agent_array[$each['salesperson_id']];

        if($documents[$staff_code]){

          if($each['driver_name'] && strpos($documents[$staff_code]['driver'],$each['driver_name']) === false){

            $documents[$staff_code]['driver'] .= $documents[$staff_code]['driver'] ? ','.$each['driver_name'] : $each['driver_name'];
          }
          $documents[$staff_code]['cash_total'] += $each['total'];
        }else{
          $documents[$staff_code] = array(
            "staff" => $staff_code,
            "driver"=> $each['driver_name'],
            "cash_total" => $each['total']
          );
        }
      }

      $q = "SELECT ROUND(SUM(grand_total),2) AS total , driver_name, salesperson_id FROM cms_order WHERE delivery_date BETWEEN '".$date." 00:00:00' AND '".$date." 23:59:59' ".$sp." AND cancel_status = 0 AND doc_type !='cash' GROUP BY salesperson_id, driver_name";
      $other_documents = $mysql->Execute($q);
      for ($j=0; $j < count($other_documents); $j++) { 
        $each = $other_documents[$j];
        $staff_code = $agent_array[$each['salesperson_id']];

        if($documents[$staff_code]){
          if($each['driver_name'] && strpos($documents[$staff_code]['driver'],$each['driver_name']) === false){
            $documents[$staff_code]['driver'] .= $documents[$staff_code]['driver'] ? ','.$each['driver_name'] : $each['driver_name'];
          }
          $documents[$staff_code]['credit_total'] += $each['total'];
        }else{
          $documents[$staff_code] = array(
            "staff" => $staff_code,
            "driver"=> $each['driver_name'],
            "credit_total" => $each['total']
          );
        }
      }

      $result_set[$date] = $documents;

  // for ($k=0; $k <= $diff; $k++) { 
  //   $date = $selected_date;//$date_from.'-'.$date_to;//date("Y-m-d" , strtotime("+".$k." day", strtotime($date_from)));

  //   $documents = array();
  //   $item_array = array();
  //   $sp_item_array = array();
  //   $category_array = array();
  //   $sum_array = array();

  //   $order_item_query = "SELECT product_code, product_name, ROUND(SUM(quantity),2) AS total, salesperson_id, driver_name FROM cms_order co LEFT JOIN cms_order_item coi ON coi.order_id = co.order_id WHERE delivery_date BETWEEN '".$date." 00:00:00' AND '".$date." 23:59:59' ".$co_sp." AND co.cancel_status = 0 AND coi.cancel_status = 0";   
  //   $order_item_query .= $product_code_array ? " AND product_code IN ('".implode("','",$product_code_array)."')" : '';

  //   $order_item_query .= " GROUP BY salesperson_id, product_code";

  //   $order_item = $mysql->Execute($order_item_query);
  //   for ($l=0; $l < count($order_item); $l++) { 
  //     $each = $order_item[$l];
  //     $category_name = $product_array[$each['product_code']];
  //     $staff_code = $agent_array[$each['salesperson_id']];
  //     $array = array(
  //       "name" => $each['product_name'],
  //       "code" => $each['product_code'],
  //       "total" => $each['total']
  //     );
  //     if(!in_array($category_name,$category_array)){
  //       $category_array[] = $category_name;
  //     }
  //     $sp_item_array[$category_name][$staff_code] += $each['total'];

  //     if($item_array[$category_name][$each['product_code']]){
  //       $item_array[$category_name][$each['product_code']]['total'] += $each['total'];
  //     }else{
  //       $item_array[$category_name][$each['product_code']] = $array;
  //     }

  //     $sum_array[$category_name] += $each['total'];
  //   }

  //   $category_set[$date] = $category_array;
  //   $sp_item_array_set[$date] = $sp_item_array;
  //   $item_array_set[$date] = $item_array;
  //   $sum_array_set[$date] = $sum_array;

  //   $payment_query = "SELECT payment_method, cpd.payment_amount, payment_by, order_id, grand_total FROM cms_order co LEFT JOIN cms_payment cp ON description = order_id AND cp.cancel_status = 0 LEFT JOIN cms_payment_detail cpd on cp.payment_id = cpd.payment_id AND cpd.cancel_status= 0 WHERE delivery_date BETWEEN '".$date." 00:00:00' AND '".$date." 23:59:59' AND co.cancel_status = 0 AND co.doc_type = 'cash'";
  //   $payment = $mysql->Execute($payment_query);

  //   $payment_array = array();
  //   $payment_array['tng'] = 0;$payment_array['wallet'] = 0;$payment_array['online'] = 0;$payment_array['cash'] = 0;$payment_array['ccs'] = 0;$payment_array['total']=0;
  //   for ($l=0; $l < count($payment); $l++) { 
      
  //     $payment_array['total'] += $payment[$l]['grand_total'];

  //     if($payment[$l]['payment_method'] == 'Wire payment'){
  //       if($payment[$l]['payment_by'] == '327-000-TNG'){
  //         $payment_array['tng'] += $payment[$l]['grand_total'];
  //       }else if($payment[$l]['payment_by'] == '300-001-ONLINE'){
  //         $payment_array['online'] += $payment[$l]['grand_total'];
  //       }else if($payment[$l]['payment_by'] == '328-000-WALLET APP'){
  //         $payment_array['wallet'] += $payment[$l]['grand_total'];
  //       }else if($payment[$l]['payment_by'] == '300-008-CCS'){
  //         $payment_array['ccs'] += $payment[$l]['grand_total'];
  //       }
  //     }else{
  //       $payment_array['cash'] += $payment[$l]['grand_total']; 
  //     }
  //   }

  //   $payment_array_set[$date] = $payment_array;

  //   $q = "SELECT ROUND(SUM(grand_total),2) AS total , driver_name, salesperson_id FROM cms_order WHERE delivery_date BETWEEN '".$date." 00:00:00' AND '".$date." 23:59:59' ".$sp." AND cancel_status = 0 AND doc_type ='cash' AND grand_total > 0 GROUP BY salesperson_id, driver_name";
  //   $cash_documents = $mysql->Execute($q);
  //   for ($j=0; $j < count($cash_documents); $j++) { 
  //     $each = $cash_documents[$j];
  //     $staff_code = $agent_array[$each['salesperson_id']];

  //     if($documents[$staff_code]){

  //       if($each['driver_name'] && strpos($documents[$staff_code]['driver'],$each['driver_name']) === false){

  //         $documents[$staff_code]['driver'] .= $documents[$staff_code]['driver'] ? ','.$each['driver_name'] : $each['driver_name'];
  //       }
  //       $documents[$staff_code]['cash_total'] += $each['total'];
  //     }else{
  //       $documents[$staff_code] = array(
  //         "staff" => $staff_code,
  //         "driver"=> $each['driver_name'],
  //         "cash_total" => $each['total']
  //       );
  //     }
  //   }

  //   $q = "SELECT ROUND(SUM(grand_total),2) AS total , driver_name, salesperson_id FROM cms_order WHERE delivery_date BETWEEN '".$date." 00:00:00' AND '".$date." 23:59:59' ".$sp." AND cancel_status = 0 AND doc_type !='cash' AND grand_total > 0 GROUP BY salesperson_id, driver_name";
  //   $other_documents = $mysql->Execute($q);
  //   for ($j=0; $j < count($other_documents); $j++) { 
  //     $each = $other_documents[$j];
  //     $staff_code = $agent_array[$each['salesperson_id']];

  //     if($documents[$staff_code]){
  //       if($each['driver_name'] && strpos($documents[$staff_code]['driver'],$each['driver_name']) === false){
  //         $documents[$staff_code]['driver'] .= $documents[$staff_code]['driver'] ? ','.$each['driver_name'] : $each['driver_name'];
  //       }
  //       $documents[$staff_code]['credit_total'] += $each['total'];
  //     }else{
  //       $documents[$staff_code] = array(
  //         "staff" => $staff_code,
  //         "driver"=> $each['driver_name'],
  //         "credit_total" => $each['total']
  //       );
  //     }
  //   }

  //   $result_set[$date] = $documents;
  // }

  $html = $template['default'];
  $innerHTML = '';

  foreach($result_set as $order_date=>$data){

    $html_row = $template['row'];
    $html_last_row = $template['last_row'];
    $html_table = $template['table'];

    $sub_row = $template['sub_row'];
    $sub_last_row = $template['sub_last_row'];

    $category_html = '';
    $sub_category_html = '';
    $category_array = $category_set[$order_date];
    $sp_item_array = $sp_item_array_set[$order_date];
    $item_array = $item_array_set[$order_date];
    $sum_array = $sum_array_set[$order_date];
    $payment_array = $payment_array_set[$order_date];

    $sub_item_html = '';
    $sub_total_html = '';
    for ($i=0; $i < count($category_array); $i++) { 
      $category_html .= '<th class="header">'.$category_array[$i].'</th>'; 
      $sub_category_html .= '<th>'.$category_array[$i].'</th>'; 
      $category_item_array = $item_array[$category_array[$i]]; 
      $item_total_array = $sum_array[$category_array[$i]];

      $sub_item_html .= '<td class="top space sub_row min_width">';
      foreach ($category_item_array as $key => $value) {
        $product_name = $value['name'];
        $product_code = $value['code'];
        $product_quantity = $value['total'];
        $sub_item_html .= str_replace(
          array(
            '@item_name',
            '@item_amount'
          ),
          array(
            $product_code,
            $product_quantity
          ),
          $sub_row
        );
      }

      $sub_item_html .= '</td>';

      $sub_total_html .= str_replace('@total_amount',$item_total_array,$sub_last_row);
    }
    $html_table = str_replace(
      array(
      '@category_row',
      '@sub_item_row',
      '@sub_total_row',
      '@cash_amount',
      '@tng_amount',
      '@online_amount',
      '@wallet_amount',
      '@ccs_amount',
      '@total_amount',
      '@sub_category_row'
      ),array(
      $category_html,
      $sub_item_html,
      $sub_total_html,
      number_format($payment_array['cash'],2),
      number_format($payment_array['tng'],2),
      number_format($payment_array['online'],2),
      number_format($payment_array['wallet'],2),
      number_format($payment_array['ccs'],2),
      number_format($payment_array['total'],2),
      $sub_category_html
      ),$html_table);



    if(count($data) > 0){
        $item_row = '';
        $total_credit_amount = 0;
        $total_cash_amount = 0;
        $i = 1;
        foreach ($data as $key => $item) {

            $category_amount_html = '';
            for ($l=0; $l < count($category_array); $l++) { 
              $amount = $sp_item_array[$category_array[$l]][$item['staff'].$item['driver']] ? $sp_item_array[$category_array[$l]][$item['staff'].$item['driver']] : '';
              $category_amount_html .= "<td>".$amount."</td>";
            }

            $item['cash_total'] = $item['cash_total'] ? floatval($item['cash_total']) : 0;
            $item['credit_total'] = $item['credit_total'] ? floatval($item['credit_total']) : 0;
            $total = $item['cash_total'] + $item['credit_total'];
            $total_credit_amount += $item['credit_total'];
            $total_cash_amount += $item['cash_total'];

            $item_row .= str_replace(
                array(
                    '@no',
                    '@salesperson',
                    '@driver',
                    // '@reference',
                    '@cash_amount',
                    '@credit_amount',
                    '@total_amount',
                    '@category_amount_row'
                ),
                array(
                    $i,
                    $item['staff'],
                    $item['driver'],
                    // '',
                    number_format($item['cash_total'],2),
                    number_format($item['credit_total'],2),
                    number_format($total,2),
                    $category_amount_html
                ),
                $html_row
            );
            $i++;
        }

        $last_category_amount_html = '';
        for ($l=0; $l < count($category_array); $l++) { 
          $amount = $sum_array[$category_array[$l]] ? $sum_array[$category_array[$l]] : 0;
          $last_category_amount_html .= "<td class='last_row'>".$amount."</td>";


        }

        $item_row .= str_replace(
          array(
              '@cash_total_amount',
              '@credit_total_amount',
              '@all_total_amount',
              '@last_category_amount_row'
          ),
          array(
            number_format($total_cash_amount,2),
            number_format($total_credit_amount,2),
            number_format($total_cash_amount + $total_credit_amount,2),
            $last_category_amount_html
          ),
          $html_last_row
        );



        $innerHTML .= str_replace(
            array(
                '@details',
                '@date'
            ),
            array(
                $item_row,
                $order_date
            ),
            $html_table
        );
    }

  }

  $html = str_replace(
      array(
          '@data_view',
          '@selected_date'
      ),
      array(
          $innerHTML,
          $selected_date
      ),
      $html
  );

  echo $html;
    //echo json_encode(array('data'=>base64_encode($html)));
}

?>