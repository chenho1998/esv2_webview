<?php
session_start();
require_once('./model/DB_class.php');
require_once('./MoneyToWord.php');
//https://easysales.asia/esv2/webview/iManage/custStatement.php?client=moss&date_from=2021-05-01&date_to=2021-05-31&cust_code=300-F00024&salesperson_id=3830
$config_ini = parse_ini_file('../config.ini',true);


$_cust_code = get('cust_code');
$_client = get('client');
$_salesperson_id = get('salesperson_id');
$_date_to =  get('date_to');
$_date_from =  get('date_from');

if(!$_cust_code || !$_client){
    die('Not enough param');
}

$db_settings                  = $config_ini[$_client];
$db_user                    = $db_settings['user'];
$db_pass                    = $db_settings['password'];
$db_name                    = $db_settings['db'];
$db_host                    = isset($db_settings['host']) ? $db_settings['host'] : 'easysales.asia';

$db = new DB();

$con_1 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);

if(!$_salesperson_id){
    $db->query("select salesperson_id from cms_customer c left join cms_customer_salesperson sp on sp.customer_id = c.cust_id and sp.active_status = 1 
    where cust_code = '".$_cust_code."'");
    while($result = $db->fetch_array()){
        $_salesperson_id = $result['salesperson_id'];
    }
}

?>
<?php
    $view = array();
    $transaction = array();
    $balance = 0;
    $cr_count = 0;
    $db_count = 0;
    $total_credit = 0;
    $total_debit = 0;

    $db->query("SELECT CCSA.COLLATION_NAME FROM information_schema.`TABLES` T,
                    information_schema.`COLLATION_CHARACTER_SET_APPLICABILITY` CCSA
                WHERE CCSA.collation_name = T.table_collation
                AND T.table_schema = (select database())
                AND T.table_name = 'cms_customer_ageing_ko';");
    while($result = $db->fetch_array()){
        if($result['COLLATION_NAME'] != 'utf8_general_ci'){
            $db->query("ALTER TABLE cms_customer_ageing_ko CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;");
        }
    }
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
        $email = $cust_info['email'];
        $website = $cust_info['website'];
    }

    $db->query("SELECT name FROM cms_login WHERE login_id = '".$_salesperson_id."'");
    while($result = $db->fetch_array()){
        $salesperson_name = $result['name'];
    }

    $db->query("SELECT cust_company_name, cust_incharge_person, cust_tel, cust_fax, billing_address1, billing_address2, billing_address3, billing_address4, termcode FROM cms_customer WHERE cust_code = '".$_cust_code."'");
    while($result = $db->fetch_array()){
        $cust_company_name = $result['cust_company_name'];
        $cust_incharge_person = $result['cust_incharge_person'];
        $cust_tel = $result['cust_tel'];
        $cust_fax = $result['cust_fax'];
        $billing_address1 = $result['billing_address1'];
        $billing_address2 = $result['billing_address2'];
        $billing_address3 = $result['billing_address3'];
        $billing_address4 = $result['billing_address4'];
        $termcode = $result['termcode'];
    }

    $invoice_query = "SELECT * FROM cms_invoice WHERE cust_code = '".$_cust_code."' AND cancelled = 'F'";

    $invoice_query .= $_date_from ? " AND invoice_date >='".$_date_from." 00:00:00'" : "";
    $invoice_query .= $_date_to ? " AND invoice_date <='".$_date_to." 23:59:59'" : "";
    $invoice_query .= " ORDER BY invoice_date ASC ";
    
    $db->query($invoice_query);
    while($result = $db->fetch_array()){

        $result["invoice_date"] = date_create($result["invoice_date"]);
		$result["invoice_date"] = date_format($result["invoice_date"],"d/m/Y");

        $invoice = array(
            "type" => "IV",
            "desc" => "Sales",
            "date" => $result['invoice_date'],
            "amount" => $result['invoice_amount'],
            "id" => $result['invoice_code'],
            "balance" => $result['outstanding_amount']
        );
        $total_debit += $result['invoice_amount'];
        $db_count ++;
        $transaction[] = $invoice;
    }

    $cn_query = "SELECT * FROM cms_creditnote WHERE cust_code = '".$_cust_code."' AND cancelled = 'F'";

    $cn_query .= $_date_from ? " AND cn_date >='".$_date_from." 00:00:00'" : "";
    $cn_query .= $_date_to ? " AND cn_date <='".$_date_to." 23:59:59'" : "";
    $cn_query .= " ORDER BY cn_date ASC ";

    $db->query($cn_query);
    while($result = $db->fetch_array()){

        $result["cn_date"] = date_create($result["cn_date"]);
		$result["cn_date"] = date_format($result["cn_date"],"d/m/Y");

        $cn = array(
            "type" => "CN",
            "desc" => "Credit Note",
            "date" => $result['cn_date'],
            "amount" => $result['cn_amount'],
            "id" => $result['cn_code'],
            "balance" => $result['cn_knockoff_amount']
        );
        $total_credit += $result['cn_amount'];
        $cr_count ++;
        $transaction[] = $cn;
    }

    $dn_query = "SELECT * FROM cms_debitnote WHERE cust_code = '".$_cust_code."' AND cancelled = 'F'";

    $dn_query .= $_date_from ? " AND dn_date >='".$_date_from." 00:00:00'" : "";
    $dn_query .= $_date_to ? " AND dn_date <='".$_date_to." 23:59:59'" : "";
    $dn_query .= " ORDER BY dn_date ASC ";

    $db->query($dn_query);
    while($result = $db->fetch_array()){

        $result["dn_date"] = date_create($result["dn_date"]);
		$result["dn_date"] = date_format($result["dn_date"],"d/m/Y");

        $dn = array(
            "type" => "DN",
            "desc" => "Debit Note",
            "date" => $result['dn_date'],
            "amount" => $result['dn_amount'],
            "id"=> $result['dn_code'],
            "balance" => $result['outstanding_amount']
        );
        $total_debit += $result['dn_amount'];
        $db_count ++;
        $transaction[] = $dn;
    }

    $receipt_query = "SELECT * FROM cms_receipt WHERE cust_code = '".$_cust_code."' AND cancelled = 'F'";

    $receipt_query .= $_date_from ? " AND receipt_date >='".$_date_from." 00:00:00'" : "";
    $receipt_query .= $_date_to ? " AND receipt_date <='".$_date_to." 23:59:59'" : "";
    $receipt_query .= " ORDER BY receipt_date ASC ";

    $db->query($receipt_query);
    while($result = $db->fetch_array()){

        $result["receipt_date"] = date_create($result["receipt_date"]);
		$result["receipt_date"] = date_format($result["receipt_date"],"d/m/Y");

        $rp = array(
            "type" => "RP",
            "desc" => $result['receipt_desc'],
            "date" => $result['receipt_date'],
            "amount" => $result['receipt_amount'],
            "id" => $result['receipt_code'],
            "cheque" => $result['cheque_no'],
            "balance" => $result['receipt_knockoff_amount']
        );
        $total_credit += $result['receipt_amount'];
        $cr_count ++;
        $transaction[] = $rp;
    }
    
    usort($transaction, function ($a, $b) {
        $dateA = DateTime::createFromFormat('d/m/Y', $a['date']);
        $dateB = DateTime::createFromFormat('d/m/Y', $b['date']);
        return $dateA >= $dateB;
    });

    $old_amount = 0;
    if($_date_from && $_date_to){
        $old_invoice_query = "SELECT SUM(invoice_amount) AS amount FROM cms_invoice WHERE cust_code = '".$_cust_code."' AND cancelled = 'F' AND invoice_date <'".$_date_from." 00:00:00'";
        $db->query($old_invoice_query);
        while($result = $db->fetch_array()){
            $old_inv_amount = $result['amount'];
        }


        $old_cn_query = "SELECT SUM(cn_amount) AS amount FROM cms_creditnote WHERE cust_code = '".$_cust_code."' AND cancelled = 'F' AND cn_date <'".$_date_from." 00:00:00'";

        $db->query($old_cn_query);
        while($result = $db->fetch_array()){
            $old_cn_amount = $result['amount'];
        }


        $old_dn_query = "SELECT SUM(dn_amount) AS amount FROM cms_debitnote WHERE cust_code = '".$_cust_code."' AND cancelled = 'F' AND dn_date <'".$_date_from." 00:00:00'";

        $db->query($old_dn_query);
        while($result = $db->fetch_array()){
            $old_dn_amount = $result['amount'];
        }


        $old_receipt_query = "SELECT SUM(receipt_amount) AS amount FROM cms_receipt WHERE cust_code = '".$_cust_code."' AND cancelled = 'F' AND receipt_date <'".$_date_from." 00:00:00'";

        $db->query($old_receipt_query);
        while($result = $db->fetch_array()){
            $old_rp_amount = $result['amount'];
        }

        $old_amount = $old_inv_amount + $old_dn_amount - $old_cn_amount - $old_rp_amount;

    }
    $total_debit = $total_debit + $old_amount;
    $balance = $total_debit - $total_credit;
    $db_count = $db_count + ($old_amount > 0 ? 1 : 0);

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
                @media print {
                    * {
                        font-weight:bold;
                    }
                    table{
                        border-collapse:collapse;
                    }
                    tr td{
                        page-break-inside: avoid;
                        white-space: nowrap;
                    }
                }
                </style>
            </head>
            <body style="padding:5px; width:100%;">';

    $view[] =  '<div style="text-align:center;width:100%;">
            <p style="font-size:20px;margin:0px;font-weight:bold;">'.$company_name.'<label style="font-size:10px;font-weight:bold">('.$sub_name.')</label></p>
            <p style="font-size:12px;margin:0px;">'.$address.'</p>
            <p style="font-size:12px;margin:0px;">'.$zipcode.' '.$city.' '.$state.'</p>
            <p style="font-size:12px;margin:0px;">Tel. : '.$phone.'</p>
            <p style="font-size:12px;margin:0px;">Email: '.$email.'</p>
            <hr style="border-top:1px solid black;margin:0px;">
        </div>';

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
            </div>
            <div style="width:40%;float:left;border:1px solid black;">
                <p style="font-size:14pt;margin:0px;border-bottom:1px solid black;text-align:center;">Statement of Account</p>
                <br>
                <p style="font-size:12px;margin:0px;float:left;margin-left:10px;">Total Debit ('.number_format($db_count).')</p>
                <p style="font-size:12px;margin:0px;float:right;margin-right:10px;">'.number_format($total_debit,2).'</p>
                <br>
                <p style="font-size:12px;margin:0px;float:left;margin-left:10px;">Total Credit ('.number_format($cr_count).')</p>
                <p style="font-size:12px;margin:0px;float:right;margin-right:10px;">'.number_format($total_credit,2).'</p>
                <br>
                <hr style="border-top:2px solid black;margin:0px;">
                <p style="font-size:12px;margin:10px;float:left;">Closing Balance</p>
                <p style="font-size:12px;margin:10px;float:right;">'.number_format($balance,2).'</p>
            </div>
        </div>';

    // while($i == 45){

    // }

    $view[] =  '<table style="width:100%;">
            <tr>
                <td colspan=9></td>
            </tr>
            <tr>
                <td colspan=9></td>
            </tr>
            <tr>
                <td colspan=9 style="border-top:1px solid black;"></td>
            </tr>
            <tr>
                <th>
                    <p style="font-size:12px;margin:0px;font-weight:bold;">Attention</p>
                    <p style="font-size:12px;margin:0px;">'.$cust_incharge_person.'</p>
                </th>
                <th colspan=2>
                    <p style="font-size:12px;margin:0px;font-weight:bold;">Customer Account</p>
                    <p style="font-size:12px;margin:0px;">'.$_cust_code.'</p>
                </th>
                <th>
                    <p style="font-size:12px;margin:0px;font-weight:bold;">Sales Executive</p>
                    <p style="font-size:12px;margin:0px;">'.$salesperson_name.'</p>
                </th>
                <th>
                    <p style="font-size:12px;margin:0px;font-weight:bold;">Currency</p>
                    <p style="font-size:12px;margin:0px;">RM</p>
                </th>
                <th colspan=4 style="border:1px solid black;">
                    <p style="font-size:12px;margin:0px;float:left;font-weight:bold;">Term</p>
                    <p style="font-size:12px;margin:0px;float:right;font-weight:bold;">Date</p><br>
                    <p style="font-size:12px;margin:0px;float:left;">'.$termcode.'</p>
                    <p style="font-size:12px;margin:0px;float:right;">'.($_date_to ? date('d/m/Y',strtotime($_date_to)) : date("d/m/Y")).'</p>
                </th>
            </tr>
            <tr>
                <td colspan=9 style="border-bottom:1px solid black;"></td>
            </tr>';

    $view[] =  '<tr>
            <td style="text-align:center;font-size:10px;font-weight:bold">Date</td>
            <td style="text-align:center;font-size:10px;font-weight:bold">References</td>
            <td style="text-align:center;font-size:10px;font-weight:bold">Transaction Description</td>
            <td></td>
            <td style="text-align:center;font-size:10px;font-weight:bold">Debit</td>
            <td></td>
            <td style="text-align:center;font-size:10px;font-weight:bold">Credit</td>
            <td></td>
            <td style="text-align:center;font-size:10px;font-weight:bold">Balance</td>
        </tr>';
    
    if($old_amount){
        $old_credit = '';
        $old_debit = '';

        if($old_amount > 0){
            $old_credit = number_format($old_amount,2);
        }else{
            $old_debit = number_format($old_amount,2);
        }

        $view[] =  '<tr>
                <td style="text-align:center;font-size:12px;"></td>
                <td style="text-align:center;font-size:12px;"></td>
                <td style="text-align:center;font-size:12px;">Balance b/f</td>
                <td></td>
                <td style="text-align:center;font-size:12px;">'.$old_credit.'</td>
                <td></td>
                <td style="text-align:center;font-size:12px;">'.$old_debit.'</td>
                <td></td>
                <td style="text-align:right;font-size:12px;">'.isPositive($old_amount).'&nbsp;</td>
            </tr>';
    }

    $cumulative_total += $old_amount;  
    for($j = 0; $j < count($transaction); $j++){
        $debit = '';
        $credit = '';
        $paid = '&nbsp;';
        if($transaction[$j]['type'] == 'IV' || $transaction[$j]['type'] == 'DN'){
            $cumulative_total += $transaction[$j]['amount'];
            $debit = number_format($transaction[$j]['amount'],2);
            if(floatval($transaction[$j]['balance']) == 0){
                $paid = '*';
            }
        }else{
            $cumulative_total -= $transaction[$j]['amount'];
            $credit = number_format($transaction[$j]['amount'],2);
        }



        $view[] =  '<tr>
                <td style="text-align:center;font-size:12px;">'.$transaction[$j]['date'].'</td>
                <td style="text-align:center;font-size:12px;">
                    <p style="font-size:12px;margin:0px;font-weight:bold;">'.$transaction[$j]['id'].'</p>';
        if($transaction[$j]['cheque']){
            $view[] =  '<p style="font-size:12px;margin:0px;font-weight:bold;">'.$transaction[$j]['cheque'].'</p>';
        }

        $view[] =     '</td>
                <td style="text-align:center;font-size:12px;">'.$transaction[$j]['desc'].'</td>
                <td></td>
                <td style="text-align:center;font-size:12px;">'.$debit.'</td>
                <td></td>
                <td style="text-align:center;font-size:12px;">'.$credit.'</td>
                <td></td>
                <td style="text-align:right;font-size:12px;">'.isPositive($cumulative_total).$paid.'</td>
            </tr>';
    }


    $view[] =  '<tr><td colspan=9 style="border-top:1px solid black;"></td></tr>
        <tr><td colspan=7 style="font-size:12px;"><div style="width:450px;white-space:pre-line">'.moneyInWord($balance).'</div></td><td style="text-align:center;">RM :</td><td style="text-align:right;font-weight:bold">'.isPositive($balance).'</td></table><br>';

    $loop_balance_array = array();
    
    $curr_mth = $_date_to ? date('Y-m-01',strtotime($_date_to)) : date('Y-m-01');
    for($k = 0; $k < 6; $k ++){
        $loop_iv_amount = 0;
        $loop_cn_amount = 0;
        $loop_dn_amount = 0;
        $loop_rp_amount = 0;

        $decided_month = date('Y-m-d',strtotime($curr_mth.'-'.$k.' months'));
        $start = date('Y-m-01 00:00:00',strtotime($decided_month));
        $end = date('Y-m-t 23:59:59',strtotime($decided_month));

        $loop_inv_query = "SELECT SUM(outstanding_amount) as amount FROM cms_invoice WHERE (invoice_date BETWEEN '{$start}' AND '{$end}') AND cust_code = '".$_cust_code."' AND cancelled = 'F'";
        $loop_cn_query = "SELECT SUM(cn_amount) as amount FROM cms_creditnote WHERE (cn_date BETWEEN '{$start}' AND '{$end}') AND cust_code = '".$_cust_code."' AND cancelled = 'F' and cn_code not in (select doc_code from cms_customer_ageing_ko where active_status = 1)";
        $loop_rp_query = "SELECT SUM(receipt_amount-receipt_knockoff_amount) as amount FROM cms_receipt WHERE (receipt_date BETWEEN '{$start}' AND '{$end}') AND cust_code = '".$_cust_code."' AND cancelled = 'F'";
        
        $db->query($loop_inv_query);
        while($result = $db->fetch_array()){
            $loop_iv_amount = $result['amount'];
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
        $first_row .= '<td style="border:1px solid black;width:16.67%;padding:2px;font-size:10px;">'.$loop_balance_array[$i]['status'].'</td>';
        $second_row .= '<td style="border:1px solid black;width:16.67%;padding:2px;font-size:12px;">'.isPositive($loop_balance_array[$i]['balance']).'</td>';
    }
    $view[] =  '<table style="border:1px solid black;border-collapse: collapse;width:80%;text-align:right;margin:auto;"><tr>'.$first_row.'</tr><tr>'.$second_row.'</tr></table>';

    // echo implode('',$view);
    $html = implode('',$view) . "</body>
    </html>";
    echo $html;
    // echo json_encode(array('statement'=>base64_encode($html)));
?>
<?php 
function get($name){
    return isset($_GET[$name]) ? $_GET[$name] : '';
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

function moneyInWord($number){
    return currency_to_cheque($number);
}
?>