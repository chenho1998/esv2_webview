<?php
session_start();
header('Content-Type: text/plain; charset="UTF-8"');
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once('./model/DB_class.php');

function getStockWithView($data){

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

    $query = "SELECT cust_id, st.st_id,st.cust_company_name,st.created_date,st.sp_updated_at,st.cust_code FROM cms_stock_take as st
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
        $query .= " {$and} date(st.created_date) >= '{$data['dateFrom']}' ";
    }

    if(!empty($data['dateTo'])){
        $and = '';
        if($whereClause){
            $and = 'AND';
        }
        $query .= " {$and} date(st.created_date) <= '{$data['dateTo']}' ";
    }
    $data['showCancel'] = intval($data['showCancel']);
    if($data['showCancel'] == 0){
        $data['showCancel'] = 1;
    }else{
        $data['showCancel'] = 0;
    }
    $and = '';
    if($whereClause){
        $and = 'AND';
    }
    $query .= " {$and} st.active_status = '{$data['showCancel']}' ";

    if(!empty($customer_id)){
        $and = '';
        if($whereClause){
            $and = 'AND';
        }
        $query .= " {$and} c.cust_id = '{$customer_id}' ";
    }

    $db->query($query);

    $view = '';

    if($db->get_num_rows() != 0)
    {
        while($result = $db->fetch_array())
        {
            $dtl = array();

            $st_id = $result['st_id'];

            $queryDtl = "SELECT * FROM cms_stock_take_dtl WHERE st_id = '{$st_id}' AND active_status = 1";

            $db2->query($queryDtl);
            
            while($result2 = $db2->fetch_array()){
                array_push($dtl,array(
                    "product_code"      => $result2['product_code'],
                    "product_name"      => $result2['product_name'],
                    "current_quantity"  => $result2['current_quantity'],
                    "suggested_quantity"=> $result2['suggested_quantity'],
                    "unit_uom"          => $result2['unit_uom'],
                    "remark"            => $result2['sp_remark'] ? $result2['sp_remark'] : "",
                    "shelf_location"          => $result2['shelf_location'] ? "Location: " . $result2['shelf_location'] : '',
                    "suggest_return"          => $result2['suggest_return'] ? '<font style="font-size:12px;color:#28a745">✅</font>' : '<font style="font-size:12px;color:#dc3545!important">❌</font>',
                    "suggest_transfer"          => $result2['suggest_transfer'] ? '<font style="font-size:12px;color:#28a745">✅</font>' : '<font style="font-size:12px;color:#dc3545!important">❌</font>',
                    "expiry_date"          => $result2['expiry_date'] != '1970-02-01'? date("d M Y", strtotime($result2['expiry_date'])) : '',
                    "foc_qty"          => $result2['foc_qty'],
                    "markdown_price"          => $result2['req_markdown'] ? 'RM'.$result2['markdown_price'] : '',
                ));
            }

            $view .= '
                        <div onclick="showModal('.htmlspecialchars(json_encode($dtl)).')" data-id="'.$result['st_id'].'" style="cursor: pointer;border-bottom-width: 1px grey;">
                            <div id="'.$result['st_id'].'" hidden>'.json_encode($dtl).'</div>
                            <p class="title" > '.$result['cust_company_name'].' </p>
                            <p class="message" >'.count($dtl).' items</p>
                            <p class="dates" > Created date: '.displaydate($result['created_date']).'</p>
                            <p class="dates" > Last updated date: '.displaydate($result['sp_updated_at']).'</p>
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