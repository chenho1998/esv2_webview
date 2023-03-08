<?php
session_start();
require_once('./model/DB_class.php');
require_once('./MoneyToWord.php');
//https://easysales.asia/esv2/webview/iManage/custStatement_openItem.php?client=moss&date_from=2021-05-01&date_to=2021-05-31&cust_code=300-X042&salesperson_id=3830
//https://easysales.asia/esv2/webview/iManage/custStatement_openItem.php?client=moss&date_from=2021-06-24&date_to=2021-06-24&cust_code=300-X042&salesperson_id=3830
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

?>
<?php
    $post_dated_rcp = array();
    $view = array();
    $transaction = array();
    $balance = 0;
    $credit_count = 0;
    $debit_count = 0;
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

    $db->query("SELECT cust_company_name, cust_incharge_person, cust_tel, cust_fax, billing_address1, billing_address2, billing_address3, billing_address4, termcode FROM cms_customer WHERE cust_code = '".$_cust_code."'");
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
        $db->query("SELECT * FROM cms_customer_branch WHERE cust_code = '".$_cust_code."' order by branch_id limit 1;");
        while($result = $db->fetch_array()){
            $branch_data = $result;
        }
        $cust_incharge_person = $branch_data['branch_attn'];
    }

    $debugger = array();
/**Loginstarthere */
    $current_date = date('Y-m-d');
    $same_date = $_date_from == $_date_to;
    $dont_show_ko = $_date_from == $_date_to && $current_date == $_date_from;
    if($dont_show_ko){
        $or_date = "";
        $cn_date = "";

        $inv_out_date = "";
        $dn_out_date = "";
        $rcp_out_date = "";
        $cn_out_date = "";
    }else{
        $cn_date = " cn_date >= '{$_date_from} 00:00:00' AND cn_date <= '{$_date_to} 23:59:59' and ";
        $or_date = " receipt_date >= '{$_date_from} 00:00:00' AND receipt_date <= '{$_date_to} 23:59:59' and ";

        $inv_out_date = " (invoice_date between (current_date()-interval 10 year) and '{$_date_to} 23:59:59') and ";
        $dn_out_date = " (dn_date between (current_date()-interval 10 year) and '{$_date_to} 23:59:59') and ";
        $rcp_out_date = " (receipt_date between (current_date()-interval 10 year) and '{$_date_to} 23:59:59') and ";
        $cn_out_date = " (cn_date between (current_date()-interval 10 year) and '{$_date_to} 23:59:59') and ";
    }

    if($dont_show_ko){
        $invoice_out_q = "SELECT i.*,date(k.doc_date) as doc_date, if(k.doc_code is null,'','*') as aestric,k.doc_code,k.doc_type,DATE_FORMAT(invoice_date,'%d/%m/%Y') AS formatted_date,doc_amount FROM cms_invoice i
        LEFT JOIN cms_customer_ageing_ko k ON k.doc_ko_ref = i.invoice_code where (invoice_date between (current_date()-interval 10 year) and '{$_date_to} 23:59:59') and cancelled = 'F' and outstanding_amount <> 0 and cust_code = '{$_cust_code}';";
    }else{
        $invoice_out_q = "select * from (
            SELECT date(k.doc_date) as doc_date, k.doc_code,if(k.doc_code is null,'','*') as aestric,k.doc_type, if(date(doc_date) >= '{$_date_to}',outstanding_amount+doc_amount,outstanding_amount) as outstanding_amount,
    invoice_code,cust_code,invoice_date,invoice_due_date,invoice_amount,i.salesperson_id,inv_udf,cancelled,
                   DATE_FORMAT(invoice_date,'%d/%m/%Y') AS formatted_date,doc_amount FROM cms_invoice i
    LEFT JOIN cms_customer_ageing_ko k ON k.doc_ko_ref = i.invoice_code
    where {$inv_out_date} cancelled = 'F'
    and cust_code = '{$_cust_code}' order by invoice_date
                )d where outstanding_amount <> 0;";
    }

    $debitnote_out_q = "select *, date_format(dn_date,'%d/%m/%Y') as formatted_date from cms_debitnote where {$dn_out_date} cancelled = 'F' and outstanding_amount <> 0 and cust_code = '{$_cust_code}';";
    
    $creditnote_out_q = "select *, date_format(cn_date,'%d/%m/%Y') as formatted_date from cms_creditnote where {$cn_out_date} cancelled = 'F' and cn_amount <> cn_knockoff_amount and cust_code = '{$_cust_code}';";
    
    $receipt_out_q = "select *, date_format(receipt_date,'%d/%m/%Y') as formatted_date from cms_receipt where {$rcp_out_date} cancelled = 'F' and receipt_amount <> receipt_knockoff_amount and cust_code = '{$_cust_code}';";
    
    /* $calculated_cn = array();
    $calculated_rcp = array(); */

    $debugger[] = $invoice_out_q;

    $db->query($invoice_out_q);
    while($result = $db->fetch_array()){
        $inRange = check_in_range($_date_from,$_date_to,$result['invoice_date']);
        $show_outstanding = $same_date ? true : !$inRange;
        /**Messeduphere**/
        if(!empty($result['doc_date']) && !$same_date){
            $month_start = date($same_date?'Y-m-d':'Y-m-01',strtotime($_date_from));
            $month_end = date('Y-m-d',strtotime($_date_to));
            $inRange = check_in_range($month_start,$month_end,$result['doc_date']);
            $show_outstanding = true;
        }
        $inv_amount = $show_outstanding ? $result['outstanding_amount'] : $result['invoice_amount'];
        $aestric = $result['aestric'];
        $ko_date = strtotime($result['doc_date']);
        $chosen_date_to = strtotime($_date_to);
        $chosen_date_from = strtotime($_date_from);

        if(!empty($aestric) && $result['doc_type'] == 'CN'){
            $aestric = 'P';
            if($dont_show_ko == false){
                $inv_amount = $result['outstanding_amount'];
            }
            // if($ko_date < $chosen_date_from){
            //     $inv_amount = $result['invoice_amount'] - $result['doc_amount'];
            // }
        }
        if(!empty($aestric) && $result['doc_type'] == 'OR'){
            $month_year_ko = date('Y-m',$ko_date);
            $month_year_chsn = date('Y-m',$chosen_date_to);
            if(($dont_show_ko == false && $ko_date > $chosen_date_to) || ($month_year_ko == $month_year_chsn /* && $dont_show_ko */)){
                $inv_amount = $result['outstanding_amount'];
            }

            if($ko_date > $chosen_date_from && $ko_date < $chosen_date_to){
                $inv_amount = $result['outstanding_amount'] + $result['doc_amount'];
            }
        }
        /**Messeduphere**/
        $transaction[] = array(
            "type" => "IV",
            "desc" => "Sales",
            "date" => $result['formatted_date'],
            "amount" => $inv_amount,
            "id" => $result['invoice_code'],
            "balance" => $result['outstanding_amount'],
            "ko"=>$result['doc_code'],
            "aestric"=>$aestric,
            "unique"=>"IV".$result['invoice_code']
        );

        /* if($result['doc_ko_type'] == 'OR'){
            $calculated_rcp[] = $result['doc_code'];
        }
        if($result['doc_ko_type'] == 'CN'){
            $calculated_cn[] = $result['doc_code'];
        } */
    }
    $transaction = unqGroupBy($transaction); 

    $debugger[] = $debitnote_out_q;

    $db->query($debitnote_out_q);
    while($result = $db->fetch_array()){
        $show_outstanding = !check_in_range($_date_from,$_date_to,$result['dn_date']);
        $transaction[] = array(
            "type" => "DN",
            "desc" => "Debit Note",
            "date" => $result['formatted_date'],
            "amount" => $show_outstanding ? $result['outstanding_amount'] : $result['dn_amount'],
            "id"=> $result['dn_code'],
            "balance" => $result['outstanding_amount'],
            "unique"=>"DN".$result['dn_code']
        );
    }

    $debugger[] = $creditnote_out_q;

    $db->query($creditnote_out_q);
    while($result = $db->fetch_array()){
        $deduct = !check_in_range($_date_from,$_date_to,$result['cn_date']);
        $transaction[] = array(
            "type" => "CN",
            "desc" => "Credit Note",
            "date" => $result['formatted_date'],
            "amount" => floatval($result['cn_amount']) - ($deduct ? floatval($result['cn_knockoff_amount']) : 0),
            "id" => $result['cn_code'],
            "balance" => $result['cn_knockoff_amount'],
            "unique"=>"CN".$result['cn_code']
        );
    }

    $debugger[] = $receipt_out_q;

    $db->query($receipt_out_q);
    while($result = $db->fetch_array()){
        $deduct = !check_in_range($_date_from,$_date_to,$result['receipt_date']);
        $transaction[] = array(
            "type" => "RP",
            "desc" => $result['receipt_desc'],
            "date" => $result['formatted_date'],
            "amount" => floatval($result['receipt_amount']) - ($deduct ? floatval($result['receipt_knockoff_amount']) : 0),
            "id" => $result['receipt_code'],
            "cheque" => $result['cheque_no'],
            "balance" => $result['receipt_knockoff_amount'],
            "unique"=>"RP".$result['receipt_code']
        );
    }

    $post_dated_q = " date(receipt_date) > current_date() and ";
    $transaction2 = array();

    if($dont_show_ko == false){
        $_to_date_and_1 = date('Y-m-d',strtotime($_date_to.' +1 day'));
        $post_dated_q = " (date(receipt_date) between '{$_to_date_and_1}' and current_date()) and ";

        $receipt_codes = array();
        $receipt = "select *, date_format(receipt_date,'%d/%m/%Y') as formatted_date from cms_receipt where {$or_date} cancelled = 'F' and cust_code = '{$_cust_code}';";

        $debugger[] = $receipt;

        $db->query($receipt);
        while($result = $db->fetch_array()){
            $transaction[] = array(
                "type" => "RP",
                "desc" => $result['receipt_desc'],
                "date" => $result['formatted_date'],
                "amount" => $result['receipt_amount'],
                "id" => $result['receipt_code'],
                "cheque" => $result['cheque_no'],
                "balance" => $result['receipt_knockoff_amount'],
                "unique"=>"RP".$result['receipt_code']
            );
            $receipt_codes[] = "'{$result['receipt_code']}'";
        }
        $receipt_codes = implode(',',$receipt_codes);

        $inv_ko = "select inv.*, date_format(invoice_date,'%d/%m/%Y') as formatted_date, sum(ko.doc_amount) as ko_amount, ko.doc_code from cms_customer_ageing_ko ko left join cms_invoice inv on inv.invoice_code = ko.doc_ko_ref and ko.doc_ko_type = 'IV' where ko.active_status = 1 and inv.cancelled = 'F' and ko.doc_code in ({$receipt_codes}) and date(invoice_date) <= '{$_date_to}' group by invoice_code;";

        $debugger[] = $inv_ko;
        
        $db->query($inv_ko);
        while($result = $db->fetch_array()){
            $transaction2[] = array(
                "type" => "IV",
                "desc" => "Sales",
                "date" => $result['formatted_date'],
                "amount" => $result['ko_amount'],
                "id" => $result['invoice_code'],
                "balance" => $result['outstanding_amount'],
                "ko" => $result['doc_code'],
                "unique"=>"IV".$result['invoice_code']
            );
        }
        $dn_ko = "select dn.*, date_format(dn_date,'%d/%m/%Y') as formatted_date, sum(ko.doc_amount) as ko_amount from cms_customer_ageing_ko ko left join cms_debitnote dn on dn.dn_code = ko.doc_ko_ref and ko.doc_ko_type = 'IV' where ko.active_status = 1 and dn.cancelled = 'F' and ko.doc_code in ({$receipt_codes}) and date(dn_date) <= '{$_date_to}' group by dn_code;";

        $debugger[] = $dn_ko;

        $db->query($dn_ko);
        while($result = $db->fetch_array()){
            $transaction2[] = array(
                "type" => "DN",
                "desc" => "Debit Note",
                "date" => $result['formatted_date'],
                "amount" => $result['ko_amount'],
                "id"=> $result['dn_code'],
                "balance" => $result['outstanding_amount'],
                "ko" => $result['doc_code'],
                "unique"=>"DN".$result['dn_code']
            );
        }

        $cn_codes = array();
        $creditnote = "select *, date_format(cn_date,'%d/%m/%Y') as formatted_date from cms_creditnote where {$cn_date} cancelled = 'F' and cust_code = '{$_cust_code}';";

        $debugger[] = $creditnote;

        $db->query($creditnote);
        while($result = $db->fetch_array()){
            $transaction[] = array(
                "type" => "CN",
                "desc" => "Credit Note",
                "date" => $result['formatted_date'],
                "amount" => $result['cn_amount'],
                "id" => $result['cn_code'],
                "balance" => $result['cn_knockoff_amount'],
                "unique"=>"CN".$result['cn_code']
            );
            $cn_codes[] = "'{$result['cn_code']}'";
        }
        $cn_codes = implode(',',$cn_codes);

        $inv_ko = "select inv.*, date_format(invoice_date,'%d/%m/%Y') as formatted_date, sum(ko.doc_amount) as ko_amount, ko.doc_code from cms_customer_ageing_ko ko left join cms_invoice inv on inv.invoice_code = ko.doc_ko_ref and ko.doc_ko_type = 'IV' where ko.active_status = 1 and inv.cancelled = 'F' and ko.doc_code in ({$cn_codes}) and date(invoice_date) <= '{$_date_to}' group by invoice_code;";

        $debugger[] = $inv_ko;

        $db->query($inv_ko);
        while($result = $db->fetch_array()){
            $transaction2[] = array(
                "type" => "IV",
                "desc" => "Sales",
                "date" => $result['formatted_date'],
                "amount" => $result['ko_amount'],
                "id" => $result['invoice_code'],
                "balance" => $result['outstanding_amount'],
                "ko" => $result['doc_code'],
                "unique"=>"IV".$result['invoice_code']
            );
        }
        $dn_ko = "select dn.*, date_format(dn_date,'%d/%m/%Y') as formatted_date, sum(ko.doc_amount) as ko_amount from cms_customer_ageing_ko ko left join cms_debitnote dn on dn.dn_code = ko.doc_ko_ref and ko.doc_ko_type = 'IV' where ko.active_status = 1 and dn.cancelled = 'F' and ko.doc_code in ({$cn_codes}) and date(dn_date) <= '{$_date_to}'  group by dn_code;";
        
        $debugger[] = $dn_ko;

        $db->query($dn_ko);
        while($result = $db->fetch_array()){
            $transaction2[] = array(
                "type" => "DN",
                "desc" => "Debit Note",
                "date" => $result['formatted_date'],
                "amount" => $result['ko_amount'],
                "id"=> $result['dn_code'],
                "balance" => $result['outstanding_amount'],
                "ko" => $result['doc_code'],
                "unique"=>"DN".$result['dn_code']
            );
        }
    }

    $transaction2 = unqGroupBy($transaction2);
    for ($t=0; $t < count($transaction2); $t++) { 
        $obj = $transaction2[$t];
        $transaction[] = $obj;
    }

    $post_dated_q = "select *, date_format(receipt_date,'%d/%m/%Y') as formatted_date from cms_receipt where {$post_dated_q} cust_code = '{$_cust_code}' and cancelled = 'F'";

    $debugger[] = $post_dated_q;
    
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

    if(count($receipt_codes) > 0){

        $receipt_codes = implode(',',$receipt_codes);
               
        $date_cond_inv = " and date(invoice_date) < CURRENT_DATE() ";
        $date_cond_dn = " and date(dn_date) < CURRENT_DATE() ";
        if($dont_show_ko == false){
            $date_cond_inv = " and date(invoice_date) < '{$_date_to}' ";
            $date_cond_dn = " and date(dn_date) < '{$_date_to}' ";
        }

        $inv_ko = "select inv.*, date_format(invoice_date,'%d/%m/%Y') as formatted_date, sum(ko.doc_amount) as ko_amount, ko.doc_code from cms_customer_ageing_ko ko left join cms_invoice inv on inv.invoice_code = ko.doc_ko_ref and ko.doc_ko_type = 'IV' where ko.active_status = 1 and inv.cancelled = 'F' and ko.doc_code in ({$receipt_codes}) {$date_cond_inv} group by invoice_code;";
       
        $debugger[] = $inv_ko;

        $db->query($inv_ko);
        while($result = $db->fetch_array()){
            $transaction[] = array(
                "type" => "IV",
                "desc" => "Sales",
                "date" => $result['formatted_date'],
                "amount" => $result['ko_amount'],
                "id" => $result['invoice_code'],
                "balance" => $result['outstanding_amount'],
                "ko" => $result['doc_code'],
                "unique"=>"IV".$result['invoice_code']
            );
        }
        $dn_ko = "select dn.*, date_format(dn_date,'%d/%m/%Y') as formatted_date, sum(ko.doc_amount) as ko_amount from cms_customer_ageing_ko ko left join cms_debitnote dn on dn.dn_code = ko.doc_ko_ref and ko.doc_ko_type = 'IV' where ko.active_status = 1 and dn.cancelled = 'F' and ko.doc_code in ({$receipt_codes}) {$date_cond_dn} group by dn_code;";
        
        $debugger[] = $dn_ko;
        
        $db->query($dn_ko);
        while($result = $db->fetch_array()){
            $transaction[] = array(
                "type" => "DN",
                "desc" => "Debit Note",
                "date" => $result['formatted_date'],
                "amount" => $result['ko_amount'],
                "id"=> $result['dn_code'],
                "balance" => $result['outstanding_amount'],
                "unique"=>"DN".$result['dn_code']
            );
        }
    }
    
    $debugger = implode(";\n\n\n",$debugger);
    file_put_contents('agsql.log',$debugger);
    file_put_contents('agarr.log',json_encode($transaction));
    $transaction = unqByObjProp($transaction,'unique');
    usort($transaction, function ($a, $b) {
        $dateA = DateTime::createFromFormat('d/m/Y', $a['date']);
        $dateB = DateTime::createFromFormat('d/m/Y', $b['date']);
        return $dateA >= $dateB;
    });
    
    /* usort($transaction, function($a, $b)
    {
        $t1 = DateTime::createFromFormat('d/m/Y', $a['date']);
        $t2 = DateTime::createFromFormat('d/m/Y', $b['date']);
        $res =  $dateA >= $dateB ? 1 : -1;
        if($res > 0){
            return strcmp($a['id'], $b['id']);
        }
    }); */

    for($j = 0; $j < count($transaction); $j++){
        if($transaction[$j]['type'] == 'IV' || $transaction[$j]['type'] == 'DN'){
            $debit_count++;
            $total_debit += floatval($transaction[$j]['amount']);
        }else{
            $credit_count++;
            $total_credit += floatval($transaction[$j]['amount']);
        }
    }
    /**Loginendhere */
    
    $total_debit = $total_debit;
    $balance = $total_debit - $total_credit;
    $debit_count = $debit_count;

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
                    font-weight: bold;
                    font-family: sans-serif;
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
            <p style="font-size:20px;margin:0px;font-weight:bold;">'.$company_name.'<br><label style="font-size:10px;font-weight:normal">'.$sub_name.'</label></p>
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
                <td style="width:10%"></td>
                <td style="width:10%"></td>
                <td style="width:10%"></td>
                <td style="width:10%"></td>
                <td style="width:15%"></td>
            </tr>
            <tr>
                <td colspan=9></td>
            </tr>
            <tr>
                <td colspan=9 style="border-top:1px solid black;"></td>
            </tr>
            <tr>
                <th colspan=2>
                    <p style="font-size:12px;margin:0px;font-weight:normal;">Attention</p>
                    <p style="font-size:10px;margin:0px;">'.ellipse($cust_incharge_person).'</p>
                </th>
                <th colspan=2>
                    <p style="font-size:12px;margin:0px;font-weight:normal;">Customer Account</p>
                    <p style="font-size:12px;margin:0px;">'.$_cust_code.'</p>
                </th>
                <th colspan=2>
                    <p style="font-size:12px;margin:0px;font-weight:normal;">Sales Executive</p>
                    <p style="font-size:'.(strlen($salesperson_name) > 18 ? 10 : 12) .'px;margin:0px;text-align:center;">'.$salesperson_name.'</p>
                </th>
                <th>
                    <p style="font-size:12px;margin:0px;font-weight:normal;">Currency</p>
                    <p style="font-size:12px;margin:0px;">RM</p>
                </th>
                <th colspan=2 style="border:1px solid black;">
                    <p style="font-size:12px;margin:0px;float:left;font-weight:normal;">Term</p>
                    <p style="font-size:12px;margin:0px;float:right;font-weight:normal;">Date</p><br>
                    <p style="font-size:12px;margin:0px;float:left;">'.$termcode.'</p>
                    <p style="font-size:12px;margin:0px;float:right;">'.($_date_to ? date('d/m/Y',strtotime($_date_to)) : date("d/m/Y")).'</p>
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
    
    $cumulative_total = 0;
    for($j = 0; $j < count($transaction); $j++){
        $debit = '';
        $credit = '';
        $paid = '&nbsp;';
        if($transaction[$j]['type'] == 'IV' || $transaction[$j]['type'] == 'DN'){
            $cumulative_total += $transaction[$j]['amount'];
            $debit = number_format($transaction[$j]['amount'],2);
            if(!empty($transaction[$j]['aestric'])){
                $paid = '<label style="font-size:9px">'.$transaction[$j]['aestric'].'</label>';
            }
        }else{
            $cumulative_total -= $transaction[$j]['amount'];
            $credit = number_format($transaction[$j]['amount'],2);
        }



        $view[] =  '<tr>
                <td style="text-align:center;font-size:12px;">'.$transaction[$j]['date'].'</td>
                <td></td>
                <td style="font-size:12px;">
                    <p style="font-size:10px;margin:0px;font-weight:normal;text-align:center;">'.$transaction[$j]['id'].'</p>';
        if($transaction[$j]['cheque']){
            $view[] =  '<p style="font-size:10px;margin:0px;font-weight:normal;text-align:center;">'.$transaction[$j]['cheque'].'</p>';
        }

        $view[] =     '</td>
        
                <td style="text-align:center;font-size:12px;white-space:pre-line;" colspan="3">'.$transaction[$j]['desc'].'</td>
                
                <td style="text-align:center;font-size:12px;">'.$debit.'</td>
                
                <td style="text-align:center;font-size:12px;">'.$credit.'</td>
                
                <td style="text-align:right;font-size:12px;">'.isPositive($cumulative_total).$paid.'</td>
            </tr>';
    }

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
    file_put_contents('ag.log','');
    $curr_mth = $_date_to ? date('Y-m-01',strtotime($_date_to)) : date('Y-m-01');
    for($k = 0; $k < 6; $k ++){
        $loop_iv_amount = 0;
        $loop_cn_amount = 0;
        $loop_dn_amount = 0;
        $loop_rp_amount = 0;

        $decided_month = date('Y-m-d',strtotime($curr_mth.'-'.$k.' months'));
        $start = $k == 5 ? '2012-01-01 23:59:59' : date('Y-m-01 00:00:00',strtotime($decided_month));
        $end = date('Y-m-t 23:59:59',strtotime($decided_month));
        if($k == 0){
            if($dont_show_ko){
                $loop_inv_query = "SELECT SUM(outstanding_amount) as amount FROM cms_invoice WHERE (invoice_date BETWEEN '{$start}' AND '{$end}') AND cust_code = '".$_cust_code."' AND cancelled = 'F'";
            }else{
                $loop_inv_query = "select SUM(outstanding_amount) as amount from (
                    SELECT if(date(doc_date) > '{$_date_to}',outstanding_amount+doc_amount,outstanding_amount) as outstanding_amount FROM cms_invoice i
            LEFT JOIN cms_customer_ageing_ko k ON k.doc_ko_ref = i.invoice_code
            where (invoice_date BETWEEN '{$start}' AND '{$end}') and cancelled = 'F'
            and cust_code = '{$_cust_code}' order by invoice_date
                        )d where outstanding_amount <> 0;";
            }
        }else{
            $loop_inv_query = "SELECT SUM(outstanding_amount) as amount FROM cms_invoice WHERE (invoice_date BETWEEN '{$start}' AND '{$end}') AND cust_code = '".$_cust_code."' AND cancelled = 'F'";
        }
        $loop_inv_query = "select SUM(outstanding_amount) as amount from (
            SELECT if(date(doc_date) > '{$_date_to}',outstanding_amount+doc_amount,outstanding_amount) as outstanding_amount FROM cms_invoice i
        LEFT JOIN cms_customer_ageing_ko k ON k.doc_ko_ref = i.invoice_code
        where (invoice_date BETWEEN '{$start}' AND '{$end}') and cancelled = 'F'
        and cust_code = '{$_cust_code}' GROUP BY invoice_code ORDER BY invoice_date
                    )d where outstanding_amount <> 0;";
        }
        
        $loop_cn_query = "SELECT SUM(cn_amount-cn_knockoff_amount) as amount FROM cms_creditnote WHERE (cn_date BETWEEN '{$start}' AND '{$end}') AND cust_code = '".$_cust_code."' AND cancelled = 'F' and cn_code not in (select doc_code from cms_customer_ageing_ko where active_status = 1)";
        $loop_rp_query = "SELECT SUM(receipt_amount-receipt_knockoff_amount) as amount FROM cms_receipt WHERE (receipt_date BETWEEN '{$start}' AND '{$end}') AND cust_code = '".$_cust_code."' AND cancelled = 'F'";

        file_put_contents('ag.log',$loop_inv_query,FILE_APPEND);
        file_put_contents('ag.log',$loop_cn_query,FILE_APPEND);
        file_put_contents('ag.log',$loop_rp_query,FILE_APPEND);
        
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
        $first_row .= '<td style="border:1px solid black;width:16.67%;padding:2px;font-size:10px;"><div class="break">'.$loop_balance_array[$i]['status'].'</div></td>';
        $second_row .= '<td style="border:1px solid black;width:16.67%;padding:2px;font-size:12px;"><div class="break">'.isPositive($loop_balance_array[$i]['balance']).'</div></td>';
    }
    $view[] =  '<table style="border:1px solid black;border-collapse: collapse;width:80%;text-align:right;margin:auto;"><tr>'.$first_row.'</tr><tr>'.$second_row.'</tr></table>';
    $view[] = '<div style="color:grey;margin-top:5px;font-size:8px;font-style:italic">This copy of statement is for reference only</div>';
    $view[] = '<div style="color:grey;margin-top:10px;font-size:7px">Statement Date From: '.$_date_from.' Date To: '.$_date_to.' Generated At: '.date('d/m/Y h:i:s').'</div>';

    $html = implode('',$view) . "</body>
    </html>";
    // echo $html;
    file_put_contents('agview.log',base64_encode($html));
    echo json_encode(array('statement'=>base64_encode($html)));
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
    return currency_to_cheque(abs($number));
}
function unqByObjProp($array,$property) {
    $tempArray = array_unique(array_column($array, $property));
    $moreUniqueArray = array_values(array_intersect_key($array, $tempArray));
    return $moreUniqueArray;
}
function unqGroupBy($array){
    $dist = array();
    for ($i=0; $i < count($array); $i++) {
        $obj = $array[$i];
        $id = $obj['id'].$obj['type'];
        $arr_amount = $obj['amount'];
        $ko = isset($obj['ko']) ? $obj['ko'] : '';
        $cond = isset($dist[$id]) && !empty($ko) && $ko != $dist[$id]['ko'] && $obj['balance'] == 0;
        if($cond){
            $arr_amount += $dist[$id]['amount'];
            $array[$dist[$id]['index']] = null;
        }
        $dist[$id] = array('amount'=>$arr_amount,'index'=>$i,'ko'=>$ko);
        $obj['amount'] = $arr_amount;
        $array[$i] = $obj;
    }
    $cp = array();
    for ($i=0; $i < count($array); $i++) {
        $obj = $array[$i];
        if($obj){
            $cp[] = $obj;
        }
    }
    return $cp;
}
function check_in_range($start_date, $end_date, $doc_date){
  $start_ts = strtotime($start_date);
  $end_ts = strtotime($end_date);
  $user_ts = strtotime($doc_date);
  
  return (($user_ts >= $start_ts) && ($user_ts <= $end_ts));
}
function ellipse($str){
    return strlen($str) > 18 ? substr($str,0,18).'...' : $str;
}
?>
