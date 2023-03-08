<?php
function clearSessionDelivery()
{
    session_start();
    $_SESSION['d_cust_code'] = "";
    $_SESSION['d_status'] = "";
    $_SESSION['d_date_from'] = "";
    $_SESSION['d_date_to'] = "";
}
function clearSession()
{
    session_start();
    $_SESSION["_odate_from"] 	= "";
    $_SESSION["_odate_to"] 		= "";
    $_SESSION["_ddate_from"] 	= "";
    $_SESSION["_ddate_to"] 		= "";
    $_SESSION["_order_status"] 	= "";
    $_SESSION["_view_cancel"] 	= "";
    $_SESSION["_cust_id"] 		= "";
    $_SESSION["_salesperson_id"]= "";
    $_SESSION['_customer_name'] = "";
    $_SESSION['_case_name']     = "";
    $_SESSION['_status_name']   = "";
    $_SESSION['_title_name']    = "";
    $_SESSION['_ship_via']    = "";

    return "SESSION > ".$_SESSION["_odate_from"].$_SESSION["_odate_to"].$_SESSION["_ddate_from"].$_SESSION["_ddate_to"].$_SESSION["_order_status"].$_SESSION["_view_cancel"].$_SESSION["_cust_id"];
}
function clearSessionPayment(){
    $_SESSION["_cdate_from"] 	= "";
    $_SESSION["_cdate_to"] 		= "";
}
function clearSessionStock(){
    session_start();
    $_SESSION["_sdate_from"] 	= "";
    $_SESSION["_sdate_to"] 		= "";

    return "SESSION > ".$_SESSION["_sdate_from"].$_SESSION["_sdate_to"];
}
function clearSessionRollingCash(){
    session_start();
    $_SESSION["_cdate_from"] 	= "";
    $_SESSION["_cdate_to"] 		= "";

    return "SESSION > ".$_SESSION["_sdate_from"].$_SESSION["_sdate_to"];
}
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    //clearSession
    if($_GET['action'] == 'clearSession'){
        echo clearSession(); 
    }
    if($_GET['action'] == 'clearSessionStock'){
        echo clearSessionStock(); 
    }
    if($_GET['action'] == 'clearSessionPayment'){
        echo clearSessionPayment(); 
    }
    if($_GET['action'] == 'clearSessionDelivery'){
        echo clearSessionDelivery(); 
    }
    if($_GET['action'] == 'clearSessionRC'){
        echo clearSessionRollingCash(); 
    }
}
?>