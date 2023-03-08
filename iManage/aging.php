<?php
/**
 * Created by PhpStorm.
 * User: julfikar
 * Date: 2019-01-15
 * Time: 14:05
 */

////http://easysales.asia/julfitest/webview/iManage/invoice_details.php?client=toysworldtest&custCode=301-M004

require_once('./model/DB_class.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header('Content-Type: text/html; charset=utf-8');
header("Pragma: no-cache");

define("INVOICE","Invoice");
define("CREDIT","Credit");
define("RECEIPT","Receipt");
define("DEBIT","Debit");
define("TYPE","type");
define("KEY","key");

date_default_timezone_set('Asia/Kuala_Lumpur');

$settings                       = parse_ini_file('../config.ini',true);

$custCode                       = '';
$client                         = '';
$connection                     = NULL;

if(isset($_GET['custCode']) && isset($_GET['client'])){

    $custCode                   = $_GET['custCode'];
    $client                     = $_GET['client'];

    $users_SQLAccounting        = $settings['SQL']['sql_client'];
    $users_abswin               = $settings['ABSWIN']['abswin_client'];

    $db                         = new DB();

    $settings                   = $settings[$client];

    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
    $db_name                    = $settings['db'];
    $db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

    $connection                 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);
    if(!$connection){
        echo "0";//unauthorized access
        $string = $db_user." <> ".$db_pass." <> ".$db_name." <> ".$custCode." <> ".$client;
        file_put_contents("./aging.log", date('Y-m-d H:i:s')." <FAIL> ".$string.PHP_EOL, FILE_APPEND);
    }else{
        // do all data manipulation
        $string = $db_user." <> ".$db_pass." <> ".$db_name." <> ".$custCode." <> ".$client;
        file_put_contents("./aging.log", date('Y-m-d H:i:s')." <SUCCESS> ".$string.PHP_EOL, FILE_APPEND);

        $is_SQLAccounting       = in_array($client,$users_SQLAccounting);
        $is_Abswin              = in_array($client,$users_abswin);

        $positive               = 0;
        $negative               = 0;

        $sql_positive           = 0;
        $sql_negative           = 0;

        $abswin_calc            = 0;

        $total_outstanding      = 0;

        $store                  = array();
        $final                  = array();

        $sql                    = "";

        $sql                    = "SELECT * FROM cms_invoice WHERE cust_code = '".mysql_real_escape_string($custCode)."'
                                    AND cancelled = 'F' ORDER BY invoice_date DESC";

        $db->query($sql);

        if($db->get_num_rows() != 0){
            while($result = $db->fetch_array()){
                array_push($store,array(
                    TYPE                => INVOICE,
                    "identifier"        => $result['invoice_id'],
                    "code"              => $result['invoice_code'],
                    "date"              => dateOnly($result['invoice_date']),
                    "amount"            => $result['invoice_amount'],
                    "balance"           => $is_SQLAccounting ? (($result['outstanding_amount'] - $result['invoice_amount']) * -1) : $result['outstanding_amount'],
                    "cancelled"         => $result['cancelled'],
                    KEY                 => monthAndYear($result['invoice_date'])
                ));
                $sql_positive += floatval($result['invoice_amount']);
                $sql_negative += floatval($result['outstanding_amount']);

                $abswin_calc  += floatval($result['outstanding_amount']);

                $positive += floatval($result['outstanding_amount']);
            }
        }

        $sql                    = "SELECT * FROM cms_debitnote WHERE cust_code = '".mysql_real_escape_string($custCode)."'
                                    AND cancelled = 'F' ORDER BY dn_date DESC";

        $db->query($sql);
        if($db->get_num_rows() != 0){
            while ($result = $db->fetch_array()){
                array_push($store,array(
                    TYPE                => DEBIT,
                    "identifier"        => $result['dn_id'],
                    "code"              => $result['dn_code'],
                    "date"              => dateOnly($result['dn_date']),
                    "amount"            => $result['dn_amount'],
                    "balance"           => $result['outstanding_amount'],
                    "cancelled"         => $result['cancelled'],
                    KEY                 => monthAndYear($result['dn_date'])
                ));

                $positive += floatval($result['outstanding_amount']);
            }
        }

        $sql                    = "SELECT * FROM cms_creditnote WHERE cust_code = '".mysql_real_escape_string($custCode)."' AND cancelled = 'F' ORDER BY cn_date DESC";

        $db->query($sql);
        if($db->get_num_rows() != 0){
            while ($result = $db->fetch_array()){
                array_push($store,array(
                    TYPE                 => CREDIT,
                    "identifier"         => $result['cn_id'],
                    "code"               => $result['cn_code'],
                    "date"               => dateOnly($result['cn_date']),
                    "balance"            => $result['cn_knockoff_amount'],
                    "amount"             => $result['cn_amount'],
                    "cancelled"          => $result['cancelled'],
                    KEY                  => monthAndYear($result['cn_date'])
                ));
                $negative += floatval($result['cn_amount']) - floatval($result['cn_knockoff_amount']);
            }
        }

        $sql                    = "SELECT * FROM cms_receipt WHERE cust_code = '".mysql_real_escape_string($custCode)."' AND cancelled = 'F' ORDER BY receipt_date DESC";

        $db->query($sql);
        if($db->get_num_rows() != 0){
            while ($result = $db->fetch_array()){
                array_push($store,array(
                    TYPE                 => RECEIPT,
                    "identifier"         => $result['receipt_id'],
                    "code"               => $result['receipt_code'],
                    "date"               => dateOnly($result['receipt_date']),
                    "balance"            => $result['receipt_knockoff_amount'],
                    "amount"             => $result['receipt_amount'],
                    "cancelled"          => $result['cancelled'],
                    KEY                  => monthAndYear($result['receipt_date'])
                ));
                $negative += floatval($result['receipt_amount']) - floatval($result['receipt_knockoff_amount']);
            }
        }

        if(count($store) === 0){
            file_put_contents("./aging.log", date('Y-m-d H:i:s')." <NO RESULT> ".$custCode.PHP_EOL, FILE_APPEND);
            echo "0";
            return false;
        }

        $ageing                          = array();

        $store                           = group_by_key($store);

        $amount                          = 0;

        $carry_amount                    = 0;

        ksort($store);

        // here we go
        if($is_SQLAccounting){
            $total_outstanding               = ($sql_positive-$sql_negative);

            foreach ($store as $date=>$data){
                $temp                        = array();

                //one month calculation

                $invoice_amt                 = 0;
                $invoice_outstanding         = 0;

                $monthlyAmount               = 0;
                $balance                     = 0;

                foreach ($data as $key=>$value){
                    // each note
                    $afterKnockOff           = 0;

                    if($value[TYPE] === INVOICE){
                        $monthlyAmount          += floatval($value['amount']);
                        $amount                 += floatval($value['amount']);
                        $invoice_amt            += floatval($value['amount']);
                        $outstanding            = (floatval($value['balance']) + floatval($value['amount']));
                        $invoice_outstanding    += $outstanding;

                        if(floatval($value['balance']) > 0){
                            $ageing[]           = $value;
                        }
                    }
                    $value['afterKnockOff']     = (floatval($value['amount']) - floatval($value['balance']));

                    array_push($temp,$value);
                }
                $balance                        = $invoice_amt - $invoice_outstanding;

                $carry_amount                   += $balance;

                array_push($final,array(
                        "month"                 =>name($date),
                        "key"                   =>$date,
                        "amount"                =>money($monthlyAmount),
                        "outstanding"           =>money($balance * -1),
                        "accm_amount"           =>money($amount),
                        "accm_outstanding"      =>money($carry_amount * -1),
                        "data"                  =>$temp
                    )
                );
            }
        } else if($is_Abswin) {
            $total_outstanding               = $abswin_calc;

            foreach ($store as $date=>$data){
                $temp                        = array();

                //one month calculation

                $invoice_amt                 = 0;
                $invoice_outstanding         = 0;

                $monthlyAmount               = 0;
                $balance                     = 0;

                foreach ($data as $key=>$value){
                    // each note
                    $afterKnockOff           = 0;

                    if($value[TYPE] === INVOICE){
                        $monthlyAmount          += floatval($value['amount']);
                        $amount                 += floatval($value['amount']);
                        $invoice_amt            += floatval($value['amount']);
                        $invoice_outstanding    += floatval($value['balance']);
                    }
                    $value['afterKnockOff']      = floatval($value['balance']);

                    array_push($temp,$value);
                }
                $balance                        = $invoice_outstanding;

                $carry_amount                   += $balance;

                array_push($final,array(
                        "month"                 =>name($date),
                        "key"                   =>$date,
                        "amount"                =>money($monthlyAmount),
                        "outstanding"           =>money($balance),
                        "accm_amount"           =>money($amount),
                        "accm_outstanding"      =>money($carry_amount),
                        "data"                  =>$temp
                    )
                );
            }
        } else{
            $total_outstanding               = ($positive-$negative);

            foreach ($store as $date=>$data){

                $temp                        = array();

                //one month calculation

                $invoice                     = 0;
                $debit                       = 0;
                $credit                      = 0;
                $receipt                     = 0;

                $monthlyAmount               = 0;
                $balance                     = 0;

                foreach ($data as $key=>$value){

                    // each note

                    $afterKnockOff           = 0;

                    $shouldAdd = false;

                    if($value[TYPE] === INVOICE){
                        $monthlyAmount          += floatval($value['amount']);
                        $amount                 += floatval($value['amount']);
                        $invoice                += floatval($value['balance']);
                        if(floatval($value['balance']) > 0){
                            $shouldAdd = true;
                        }
                    }
                    if ($value[TYPE] === DEBIT){
                        $monthlyAmount          += floatval($value['amount']);
                        $amount                 += floatval($value['amount']);
                        $debit                  += floatval($value['balance']);
                        if(floatval($value['balance']) > 0){
                            $shouldAdd = true;
                        }
                    }
                    if($value[TYPE] === CREDIT){
                        $monthlyAmount          -= floatval($value['amount']);
                        $afterKnockOff          = floatval($value['amount']) - floatval($value['balance']);
                        $credit                 += $afterKnockOff;
                    }
                    if($value[TYPE] === RECEIPT){
                        $afterKnockOff          = floatval($value['amount']) - floatval($value['balance']);
                        $receipt                += $afterKnockOff;
                    }

                    $value['afterKnockOff']     = $afterKnockOff;

                    if($shouldAdd || $afterKnockOff != 0){
                        $ageing[] = $value;
                    }

                    array_push($temp,$value);

                }

                $balance                        = ($invoice+$debit) - ($credit+$receipt);

                $carry_amount                   += $balance;

                array_push($final,array(
                        "month"                 =>name($date),
                        "key"                   =>$date,
                        "amount"                =>money($monthlyAmount),
                        "outstanding"           =>money($balance),
                        "accm_amount"           =>money($amount),
                        "accm_outstanding"      =>money($carry_amount),
                        "data"                  =>$temp
                    )
                );
            }
        }


        $accm = 0;
        for($i = 0, $len = count($ageing); $i < $len; $i++){
            $value = $ageing[$i];
            if($value[TYPE] == INVOICE || $value[TYPE] == DEBIT){
                $accm += $value['balance'];
            }else{
                $accm -= $value['afterKnockOff'];
            }
            $value['accm'] = $accm;
            $ageing[$i] = $value;
        }

        $ageing = array_reverse($ageing);

        echo json_encode(array("total_outstanding" => money($total_outstanding), "statistics" => $final, "ageing" => $ageing));
    }
}else{
    echo "0";//unauthorized access
}

function nonnull($val,$type='string'){
    return empty($val) ? $type === 'string' ? "N/A" : 0 : $val;
}
function monthAndYear($date){
    return substr(dateOnly($date),0,-3);
}
function dateOnly($date){
    return empty($date) ? "" : explode(" ",$date)[0];
}
function group_by_key($array, $key = 'key') {
    $return                     = array();
    foreach($array as $val) {
        $return[$val[$key]][]   = $val;
    }
    return $return;
}
function name($date){
    $year                       = explode("-",$date)[0];
    $month                      = explode("-",$date)[1];
    $month                      = intval($month);
    $month_names                = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    return $month_names[$month-1].", ".$year;
}
function money($amount){
    return $amount;
    $amount = floatval($amount);
    return number_format($amount, 2, '.', ',');
}
?>

