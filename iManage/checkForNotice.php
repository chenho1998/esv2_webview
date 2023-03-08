<?php
header('Content-Type: text/plain; charset="UTF-8"');
date_default_timezone_set('Asia/Kuala_Lumpur');
require_once('./model/DB_class.php');

if(isset($_GET['client']) && isset($_GET['salesperson_id']) && $_GET['salesperson_id'] != 0){
    $config = parse_ini_file('../config.ini',true);

    $client = $_GET['client'];
    
    if($client != 'huasen'){
        echo json_encode(
            array()
        );
    }

	$settings                   = $config[$client];
    
    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
    $db_name                    = $settings['db'];
    $db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

    $custCode                   = $_GET['cust_code'];

	$db = new DB();
    $con_1 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);

    $final = array();

    array_push($final,
        array(
            "noticeForSp"=> array(
                "title"=>"Announcement",
                "message"=>"1) Please ensure that all the stock take submitted before 31 March 2020, No more paper form accepted starts from this month. \n\n2) Monthly meeting : 21 March 2020, 9AM",
                "action"=>array(
                    
                ),
                "cancelable"=>"1",
                "image"=>"https://www.simplilearn.com/ice9/free_resources_article_thumb/COVER-IMAGE_Digital-Selling-Foundation-Program.jpg",
                "auto_close"=>array(
                    "enable"=>"0",
                    "interval"=>"3000"
                )
            )
        )
    );

    echo json_encode(
        $final
    );
}
if(isset($_GET['client']) && isset($_GET['cust_code']) && !empty($_GET['cust_code'])){
    $config = parse_ini_file('../config.ini',true);

	$client = $_GET['client'];

	$settings                   = $config[$client];
    
    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
    $db_name                    = $settings['db'];
    $db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

    $custCode                   = $_GET['cust_code'];

	$db = new DB();
    $con_1 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);

    if($client != 'weiwo'){
        $announcements = array();
        $sql = "SELECT * FROM cms_app_announcement WHERE active_status = 1 AND current_date() between date_from and date_to ORDER BY updated_at DESC LIMIT 1;";
        $db->query($sql);
    
        $counter = $db->get_num_rows();
        if($counter > 0){
            while($row = $db->fetch_array()){
                $action = json_decode(json_encode($row['alert_action']),true);
                $announcements[] = array(
                    '--announcement--'=> array(
                        "title"=>$row['alert_name'],
                        "message"=>$row['alert_content'],
                        "action"=>/* $action ? array($action) : */ array(),
                        "cancelable"=>"1",
                        "image"=>$row['alert_image']? $row['alert_image']:"https://easysales.asia/esv2/images/bell.png",
                        "auto_close"=>array(
                            "enable"=>"0",
                            "interval"=>"5000"
                        )
                    )
                );
            }
        }
        echo json_encode(
            $announcements
        );
        return;
    }

    $sql = "SELECT * FROM cms_invoice AS inv 
    WHERE cust_code = '".mysql_real_escape_string($custCode)."' AND cancelled = 'F' AND (
        DATE(invoice_date) >= '2020-07-20' AND DATE(invoice_date) <= LAST_DAY(CURRENT_DATE()-INTERVAL 2 MONTH)
        ) AND approved = 0 ORDER BY invoice_date DESC";

	$db->query($sql);
    
    $counter = $db->get_num_rows();

    $final = array();

    if($counter != 0){
        array_push($final,
            array(
                "pendingInvoice"=> array(
                    "title"=>"Pending Invoices",
                    "message"=>"You have {$counter} invoices waiting for approval. Please take a moment to approve",
                    "action"=>array(
                        array(
                            "name"=>"Approve Now",
                            "function"=>"this.setState({activeItem:'UserStatement'});this.props.navigation.navigate('UserStatement',{strCustCode:'{$_GET['cust_code']}'})"
                        )
                    ),
                    "cancelable"=>"0",
                    "image"=>"https://easysales.asia/esv2/images/bell.png",
                    "auto_close"=>array(
                        "enable"=>"0",
                        "interval"=>"5000"
                    )
                )
            )/* ,
            array(
                "advert"=> array(
                    "title"=>"",
                    "message"=>"",
                    "action"=>array(
                        
                    ),
                    "cancelable"=>"1",
                    "image"=>"https://easysales.asia/esv2/images/vivo.jpeg",
                    "auto_close"=>array(
                        "enable"=>"0",
                        "interval"=>"3000"
                    )
                )
            ) */
        );
    }

    echo json_encode(
        $final
    );
}
?>