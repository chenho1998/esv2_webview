<?php
require_once('./model/DB_class.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0"); 
header("Cache-Control: post-check=0, pre-check=0", false); 
header("Pragma: no-cache");

date_default_timezone_set('Asia/Kuala_Lumpur');
// $custList                   = json_decode($_POST['custList'],true);

$invoiceCodes = array();
$client = '';
$connection = NULL;

$settings = parse_ini_file('../config.ini',true);

$db = new DB();

if(isset($_GET['client'])){

    $invoiceCodes = json_decode($_POST['invoiceCodes'],true);
    $client = $_GET['client'];
    $is_salesperson = isset($_GET['salesperson']) ? true : false;

    $settings = $settings[$client];

    $db_user  = $settings['user'];
    $db_pass  = $settings['password'];
    $db_name  = $settings['db'];
    $db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

    $connection = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);

    $final                      = array();

    $pdf                        = array();

    for ($i=0; $i < count($invoiceCodes); $i++) { 
        $obj = $invoiceCodes[$i];
        $code = $obj['code'];
        $type = $obj['type'];

        switch ($type) {
            case 'invoice':{
                $sql = "select * from cms_invoice_details where invoice_code = '".mysql_real_escape_string($code)."' AND active_status = 1";
                $main = "select invoice_code as code, 'invoice' as type,invoice_amount as amount, outstanding_amount as balance, invoice_due_date as overdueDate, invoice_date as date, approved as checked from cms_invoice where invoice_code = '".mysql_real_escape_string($code)."'";
                break;
            }
            case 'credit':{
                $sql = "select * from cms_creditnote_details where cn_code = '".mysql_real_escape_string($code)."'";
                $main = "select cn_code as code, cn_date as date, cn_amount as amount, cn_knockoff_amount as balance, approved as checked from cms_creditnote where cn_code = '".mysql_real_escape_string($code)."'";
                break;
            }
            case 'receipt':{
                $sql = "select k.*,inv.invoice_amount, inv.outstanding_amount, date_format(date(inv.invoice_date),'%d/%m/%y %h:%m %p') as invoice_date from cms_customer_ageing_ko as k
                left join cms_invoice inv on inv.invoice_code = k.doc_ko_ref where doc_code = '".mysql_real_escape_string($code)."'";
                $main = "select receipt_code as code,receipt_date as date,receipt_amount as amount,receipt_knockoff_amount as balance, approved as checked from cms_receipt where receipt_code = '".mysql_real_escape_string($code)."'";
                break;
            }
            default:
                # code...
                break;
        }
        
        $db->query($sql);

        $details = array();
	
        if($db->get_num_rows() != 0){
            while($result = $db->fetch_array()){
                $result['type'] = $type;
                $result['quantity'] = intval($result['quantity']);
                $result["item_code"] = str_replace(array('#','>',"'",),'',$result["item_code"]);
                $result["item_name"] = str_replace(array('#','>',"'",),'',$result["item_name"]);
                switch ($type) {
                    case 'invoice':{
                        $result['doc_code'] = $result['invoice_code'];
                        $result['total_price'] = number_format($result['total_price'],2);
                        $result['item_price'] = number_format($result['item_price'],2);
                        if(empty($result["item_code"])){
                            $result["item_code"] = '-';
                        }
                        if(!empty($result["item_code"])){
                            if(strpos($result['discount'], '%')  !== false){
                                $result['discount'] = $result['discount'];
                            }else{
                                $result['discount'] = floatval($result['discount']) ? floatval($result['discount']) : '';
                            }
                            $details[] = $result;
                        }
                        break;
                    }
                    case 'credit':{
                        if(empty($result["item_code"])){
                            $result["item_code"] = '-';
                        }
                        if(!empty($result["item_code"])){
                            $result['doc_code'] = $result['cn_code'];
                            
                            $details[] = $result;
                        }
                        break;
                    }
                    case 'receipt':{
                        $result['total_price'] = number_format($result['doc_amount'],2);
                        $result['item_name'] = $result['invoice_date'];
                        $result['item_code'] = $result['doc_ko_ref'];
                        $result['quantity'] = '';
                        $result['uom'] = '';
                        $result['discount'] = '';
                        $result['item_price'] = '';
                        
                        $details[] = $result;
                        break;
                    }
                    default:
                        # code...
                        break;
                }
            }
        }

        if($is_salesperson){
            $db->query($main);
            if($db->get_num_rows() != 0){
                $doc = array();
                while($result = $db->fetch_array()){
                    $doc[] = $result;
                }
            }
            $pdf[$code] = array(
                "doc"=>$doc,
                "details"=>$details
            );
        }else{
            $final[$code] = $details;
        }
    }
    if($is_salesperson){
        // file_put_contents('multiDocDetails.log',json_encode($pdf));
        echo json_encode($pdf);
    }else{
        if($final){
            echo json_encode($final);
        }else{
            echo $_POST['invoiceCodes']."<-invoice codes";
        }
    }
}
?>