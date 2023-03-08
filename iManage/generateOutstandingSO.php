<?php
require_once('./model/MySQL.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
date_default_timezone_set('Asia/Kuala_Lumpur');
//easysales.asia/esv2/webview/iManage/generateOutstandingSO.php?cust_code=300-T006&client=mdh&date_from=2022-07-01&date_to=2022-07-13&salesperson_id=25
if(
    isset($_GET['client']) && 
    isset($_GET['date_from']) && 
    isset($_GET['date_to']) && 
    isset($_GET['cust_code']) && 
    isset($_GET['salesperson_id'])
){
    $client = $_GET['client'];
    $date_from = $_GET['date_from'];
    $date_to = $_GET['date_to'];
    $cust_code = MySQL::sanitize($_GET['cust_code']);
    $salesperson_id = $_GET['salesperson_id'];

    $settings = parse_ini_file('../config.ini',true);
    $settings = $settings[$client];
    $mysql = new MySQL($settings);

    $app_client_info = array('client_name'=>'','client_sub'=>'','staff_code'=>'');
    $client_info = $mysql->Execute("select * from cms_mobile_module where module = 'app_client' or module = 'app_client_info';");
    for ($i=0; $i < count($client_info); $i++) { 
        $obj = $client_info[$i];
        if($obj['module'] == 'app_client'){
            $app_client_info['client_name'] = $obj['status'];
        }else{
            $info = json_decode($obj['status'],true);
            $app_client_info['client_sub'] = $info['status'];
        }
    }

    $login = $mysql->Execute("select * from cms_login where login_id = '{$salesperson_id}'");
    $login = $login[0];
    $app_client_info['staff_code'] = $login['staff_code'];

    $app_client_info['current'] = date('d-m-Y h:mA');

    $customer = $mysql->Execute("select * from cms_customer where cust_code = '{$cust_code}'");
    $customer = $customer[0];

    $outstanding = $mysql->Execute("select o.* , p.product_name, date_format(o.so_doc_date,'%d/%m/%Y') as formatted_date from cms_outstanding_so o left join cms_product p on p.product_code = o.so_product_code
    where o.so_cust_code = '{$cust_code}' and o.active_status = 1 and o.so_trans_qty <> o.so_ori_qty
      and date(o.so_doc_date) between '{$date_from}' and '{$date_to}';");

    $distinct = array();
    for ($i=0; $i < count($outstanding); $i++) { 
        $out = $outstanding[$i];
        $distinct[$out['so_dockey']][] = $out;
    }

    $html = html($app_client_info);
    foreach ($distinct as $doc_key => $arr) {
        $first = $arr[0];
        $obj = obj_merge($first,$customer);
        $html .= customer_html($obj);
        $items = '';
        
        for ($i=0; $i < count($arr); $i++) { 
            $items .= items_html(($i+1),$arr[$i]);
        }
        $html .= item_wrapper($items);
    }
    // echo $html;
    echo json_encode(array('html'=>$html,'base64'=>base64_encode($html)));
}

function html($obj){
    return '<html>
                <head>
                    <style>
                    *{
                        font-weight:bold;
                    }
                    </style>
                </head>
                <body>
                    <table style="width:100%;">
                        <tr>
                            <td style="width:50%;font-size: 1.5em;font-weight:bold;text-align:center;">
                                Outstanding Sales Order Listing
                            </td>
                            <td style="width:25%;vertical-align:bottom;font-size:10pt;">
                                Ref. No:
                            </td>
                            <td style="width:25%;right:0;font-size:10pt;text-align:right;">
                                Date : '.$obj['current'].'<br>
                                User ID : '.$obj['staff_code'].'
                            </td>
                        </tr>
                        <tr>
                            <td style="vertical-align:bottom;font-size:10pt;">
                                '.$obj['client_name'].' '.$obj['client_sub'].'
                            </td>
                            <td>
                                <div style="border:1px solid black; width:75px; height:75px;float:left;font-size:10pt;">

                                </div>
                            </td>
                            <td style="vertical-align:bottom;text-align:right;">
                                
                            </td>
                        </tr>
                    </table>
                    <table style="border-top:1px solid black; border-bottom:1px solid black;width:100%;font-size:10pt;">
                        <tr>
                            <td style="font-weight:bold;">
                                Group By
                            </td>
                            <td style="font-weight:bold;">
                                Group Description
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight:bold;width:12%;">
                                Doc Date
                            </td>
                            <td style="text-align:center;font-weight:bold;width:14%;">
                                Doc No.
                            </td>
                            <td style="font-weight:bold;width:14%;">
                                Debtor Code
                            </td>
                            <td style="width:50%;font-weight:bold;width:30%;">
                                Debtor Name
                            </td>
                            <td style="width:25%">

                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight:bold;">
                                No.
                            </td>
                            <td colspan="2" style="text-align:center;font-weight:bold;">
                                Item Description
                            </td>
                        </tr>
                    </table>
                </body>
            </html>';
}

function customer_html($obj){
    return '<table style="width:100%;margin-top:5px;">
                <tr>
                    <td style="font-weight:bold;font-size:10pt;width:12%;">
                        '.$obj['formatted_date'].'
                    </td>
                    <td style="text-align:center;font-weight:bold;font-size:10pt;width:18%;">
                        '.$obj['so_dockey'].'
                    </td>
                    <td style="font-weight:bold;font-size:10pt;width:10%;">
                        '.$obj['so_cust_code'].'
                    </td>
                    <td style="font-weight:bold;width:30%;font-size:11pt;">
                        '.$obj['cust_company_name'].'
                    </td>
                    <td style="font-style:italic;width:30%;font-size:10px;">
                        '.($obj['billing_address1'] .$obj['billing_address2'] .$obj['billing_address3'] .$obj['billing_address4'] .$obj['billing_city'] .$obj['billing_state']
                        .$obj['billing_zipcode'] .$obj['billing_country']).'
                    </td>
                </tr>
                <tr>
                    <td style="font-style:italic;font-weight:bold;text-align:center;font-size:10pt;"> 
                        Your P/O No:
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="font-style:italic;font-size:10pt;">
                        TEL: '.$obj['cust_tel'].'
                    </td>
                </tr>
            </table>';
}

function item_wrapper($item){
    return '<table style="width:100%;font-size:10pt;">
                <tr>
                    <td style="width:5%;"></td>
                    <td style="width:60%;"></td>
                    <td style="width:15%;">
                        HQ
                    </td>
                    <td style="width:20%;font-weight:bold;">
                        Pending Qty
                    </td>
                </tr>
                <tr>
                    <td colspan="4" style="border-top:1px solid black;"></td>
                </tr>
                '.$item.'
            </table>';
}

function items_html($counter, $obj){
    return '
            <tr>
                <td style="text-align:center;line-height:20px;">
                    '.$counter.'
                </td>
                <td colspan="2">
                    '.$obj['product_name'].'
                </td>
                <td>
                    <span style="font-weight:bold;"> '.$obj['so_out_qty'].' </span> '.$obj['uom'].'
                </td>
            </tr>';
}

function obj_merge($first, $second){
    $res = array();
    foreach ($first as $key => $value) {
        $res[$key] = $value;
    }
    foreach ($second as $key => $value) {
        $res[$key] = $value;
    }
    return $res;
}