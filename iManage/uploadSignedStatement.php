<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
header('Content-Type: text/plain; charset="UTF-8"');
require_once('./model/DB_class.php');

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['file'])){
    $db = new DB();
    $db->connect_db_with_db_config();

    $json_data = array(
        'code'=>'200',
        'status'=>'0',
        'message'=>'Script is loaded successfully',
        'result'=>'null'
    );

    $salesperson_id = $_POST['salesperson_id'];
    $cust_code      = $_POST['cust_code'];
    $client         = $_POST['client'];
    $content        = $_POST['file'];
    $date_from      = $_POST['date_from'];
    $date_to        = $_POST['date_to'];

    $title          = date_format(date_create($date_from),"d/m/Y").' TO '.date_format(date_create($date_to),"d/m/Y");
    
    $config = parse_ini_file('../config.ini',true);
	$settings                   = $config[$client];

	$db_user                    = $settings['user'];
	$db_pass                    = $settings['password'];
	$db_name                    = $settings['db'];
    $db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

	$db = new DB();
    $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);

    $file_name                  = $client.'-'.time().'.pdf';
    $file_path = 'https://easysales.asia/signed_statements/'.$file_name;

    if(base64_to_pdf($content,$file_name)){

        $sql = "INSERT INTO cms_statement_signature (title,salesperson_id, cust_code, statement_path, updated_at, date_from, date_to) VALUES ('{$title}','{$salesperson_id}','{$cust_code}','{$file_path}',NOW(),'{$date_from}','{$date_to}')";

        if($db->query($sql)){
            $json_data['result'] = 1;
            $json_data['status'] = 1;
        }else{
            $json_data['result'] = 0;
            $json_data['status'] = 0;
        }
    }else{
        $json_data['result'] = 0;
        $json_data['status'] = 0;
        $json_data['message'] = "Image saving error";
    }

    echo json_encode($json_data);
}
function base64_to_pdf($base64_string, $output_file) {
    $ifp = fopen( '../../../../public_html/signed_statements/'.$output_file, 'wb' ); 
    if(fwrite( $ifp, base64_decode($base64_string))){
        fclose( $ifp ); 
        return $output_file; 
    }else{
        return null;
    }
}

?>