<?php
session_start();
header('Content-Type: text/plain; charset="UTF-8"');
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once('./model/DB_class.php');

function getStockTransferWithView($data){

    $_SESSION["_odate_from"] 	= _isNull($data['dateFrom']);
    $_SESSION["_odate_to"] 		= _isNull($data['dateTo']);
    $_SESSION["_view_cancel"] 	= _isNull($data['showCancel']);
	$_SESSION["_cust_id"] 		= _isNull($data['customer_status']);
	$_SESSION["_backlink"]		= _isNull($data['backlink']);
	$_SESSION["_salesperson_id"]= _isNull($data['salesperson_id']);	
    $_SESSION['_customer_name']	= _isNull($data['customer_name']);
    
    $config = parse_ini_file('../config.ini',true);

	$client                     = $data['client'];

	$isAdmin                    = ($data['salespersonId'] == '0') || !is_numeric($data['salespersonId']);

    $customer_code              = $data['customer_code'];
    $customer_id                = $data['customer_status'];
    $offset                     = $data['offset'];

	$settings                   = $config[$client];
    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
    $db_name                    = $settings['db'];
    $db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

	$db                         = new DB();
	$con_1                      = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);

	$db2                        = new DB();
	$con_2                      = $db2->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);

    $query = "SELECT cust_id, st.id,c.cust_company_name,st.st_date,st.cust_code,st.st_status,st_code, st.cancel_status FROM cms_stock_transfer as st
    left join cms_customer c on c.cust_code = st.cust_code ";

    $whereClause = false;

    if(!$isAdmin){
        $query .= "WHERE st.salesperson_id = '{$data['salespersonId']}' "; 
        $whereClause = true;
    }
    

    if(!empty($data['dateFrom'])){
        $and = ' WHERE ';
        if($whereClause){
            $and = 'AND';
        }else{
            $whereClause = true;
        }
        $query .= " {$and} date(st.st_date) >= '{$data['dateFrom']}' ";
    }

    if(!empty($data['dateTo'])){
        $and = '';
        if($whereClause){
            $and = 'AND';
        }
        $query .= " {$and} date(st.st_date) <= '{$data['dateTo']}' ";
    }

    if(!empty($data['showCancel'])){
        $and = '';
        if($whereClause){
            $and = 'AND';
        }
        $query .= " {$and} st.cancel_status = '{$data['showCancel']}' ";
    }else{
        $query .= " {$and} st.cancel_status = '0'";
    }

    if(!empty($customer_id)){
        $and = '';
        if($whereClause){
            $and = 'AND';
        }
        $query .= " {$and} c.cust_id = '{$customer_id}' ";
    }

    $query .= " AND st.st_status > 0 ORDER BY st.st_status ASC";

    $db->query($query);

    $view = '';

    if($db->get_num_rows() != 0)
    {
        while($result = $db->fetch_array())
        {
            $dtl = array();

            $st_code = $result['st_code'];

            $queryDtl = "SELECT * FROM cms_stock_transfer_dtl WHERE st_code = '{$st_code}' AND cancel_status = 0";

            $db2->query($queryDtl);
            $json = $result;
            $json['details'] = array();
            while($result2 = $db2->fetch_array()){
                if (isset($result2['serial_no'])){
                    $result2_serial = json_decode($result2['serial_no'],true);
                    if ($result2_serial){
                        $result2['serial_no'] = $result2_serial['serial_no'];
                    } else {
                        $result2['serial_no'] = "";
                    }
                }
                $json['details'][] = $result2;
                array_push($dtl,array(
                    "id"                => $result2['id'],
                    "product_code"      => $result2['product_code'],
                    "product_name"      => $result2['product_name'],
                    "quantity"          => $result2['quantity'],
                    "unit_uom"          => $result2['unit_uom'],
                    "from_location"     => $result2['from_location'] ? "From Location : ".$result2['from_location'] : "",
                    "to_location"     => $result2['to_location'] ? "To Location : ".$result2['to_location'] : "",
                    "acknowledged"      => empty($result2['acknowledged']) ? 0 : $result2['acknowledged']
                ));
            }

            if($result['st_status'] == 1){
                $status = 'Confirmed';
            }else if($result['st_status'] == 2){
                $status = 'Approved By Admin';
            }else{
                $status = 'Transferred to Accounting Software';
            }

            if($result['cancel_status']){
                $status = '<font style="color:red;">Cancelled</font>';
            }

            $view .= '
                        <div onclick="showModal('.htmlspecialchars(json_encode($json)).')" data-id="'.$result['st_id'].'" style="cursor: pointer;border-bottom-width: 1px grey;">
                            <div id="json_data" hidden>'.json_encode($json).'</div>
                            <div id="'.$result['st_id'].'" hidden>'.json_encode($dtl).'</div>
                            <p class="title" > '.$result['st_code'].' </p>
                            <p class="message"><b>'.$result['cust_company_name'].'</b></p>
                            <p class="message"><i>'.count($dtl).' items</i></p>
                            <p class="dates" > Stock date: '.displaydate($result['st_date']).'</p>
                            <p class="message"> Stock status: '.$status.'</p>
                        </div><hr>
                    ';
        }
    }

    return $view;
}

function _isNull ($value){
	if($value == 'null' || $value == 'undefined' || !$value){
		return '';
	}
	return $value;
}

function displaydate($datetime){
    $datetime = strval($datetime);
    $month_names = array(
       'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'
    );
    
    $splitted = explode(' ',$datetime);
    
    $date = $splitted[0];
    $time = $splitted[1];
    
    $splitted = explode('-',$date);
    $year = $splitted[0];
    $month = intval($splitted[1])-1;
    $day = $splitted[2];
    
    $month = $month_names[$month];
  
    if($time != '00:00:00' && !empty($time)){
        $timeSplit = explode(':',$time);

        $hr          = intval($timeSplit[0]);
        $min         = intval($timeSplit[1]);
        $zz          = 'AM';

        if($min < 10){
            $min         = "0".$min;
        }

        if ($hr > 12) {
            $hr -= 12;
            $zz = 'PM';
        }
        $time = '';
        $time = ' at '. $hr . ':' . $min . $zz;
        }else{
           $time = '';
        }
    
    $display = $day." ".$month." ".$year.$time;
    
    return $display;
}
?>