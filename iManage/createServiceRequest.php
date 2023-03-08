<?php 
require_once('./model/MySQL.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
date_default_timezone_set('Asia/Kuala_Lumpur');

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $client = $_POST['client'];

    $settings = parse_ini_file('../config.ini',true);
    $settings = $settings[$client];
    $mysql = new MySQL($settings);
    $salesperson_id = $_POST['salesperson_id'];

    if(isset($_POST['log']) && $_POST['log'] == '1'){
        $id = $_POST['id'];
        $seen_by = $_POST['seen_by'];
        $sced_note = MySQL::sanitize($_POST['sched_note']);
        $query = "insert into cms_customer_visit_sched_log(sched_id,sched_seen_by,sched_note) values ('{$id}','{$seen_by}','{$sced_note}')";
        if(empty($sced_note)){
            $check = $mysql->Execute("select * from cms_customer_visit_sched_log where sched_seen_by = '{$seen_by}' and sched_id = '{$id}' and (sched_note is null or sched_note ='');");
            if(count($check) == 0){
                $mysql->Execute($query);
            }
        }else{
            $mysql->Execute($query);
        }
        return json_encode(array("res"=>"E"));
    }

    if(isset($_POST['delete']) && $_POST['delete'] == '1'){
        $id = $_POST['id'];
        $mysql->Execute("update cms_customer_visit_sched set active_status = 0 where id = '{$id}'");
        if($mysql->AffectedRows() > 0){
            $sched = $mysql->Execute("select * from cms_customer_visit_sched where id = '{$id}'");
            $sched = $sched[0];
            if($salesperson_id == $sched['salesperson_id'] && !empty($sched['tech_assignee'])){
                alertUser($sched['tech_assignee'],$mysql);
            }
            if($salesperson_id == $sched['tech_assignee'] && !empty($sched['tech_assigned'])){
                alertUser($sched['tech_assigned'],$mysql);
            }
            $mysql->Execute("update cms_customer set updated_at = now() where cust_code = '{$cust_code}'");
            echo json_encode(array('result'=>'1'));
        }else{
            echo json_encode(array('result'=>'0','query'=>"update cms_customer_visit_sched set active_status = 0 where id = '{$id}'"));
        }
        return json_encode(array("res"=>"E"));
    }

    $cust_code = $_POST['cust_code'];
    $tech_assignee = $_POST['tech_assignee'];
    $tech_assigned = $_POST['tech_assigned'];
    $created_by = $_POST['created_by'];
    $updated_by = $_POST['updated_by'];
    $sched_datetime = $_POST['sched_datetime'];
    $sched_note = MySQL::sanitize($_POST['sched_note']);
    $site_in_charge = MySQL::sanitize($_POST['site_in_charge']);
    $site_in_charge_contact = MySQL::sanitize($_POST['site_in_charge_contact']);
    $site_location = MySQL::sanitize($_POST['site_location']);
    $image_list = $_POST['image_list'];

    if(is_string($image_list)){
        $image_list = json_decode($image_list,true);
    }

    $query = '';

    if(isset($_POST['save']) && $_POST['save'] == '1'){
        $query = "INSERT INTO cms_customer_visit_sched (cust_code, salesperson_id, sched_datetime, sched_note, tech_assignee, tech_assigned, created_by,updated_by,site_in_charge,site_in_charge_contact,site_location) VALUES ('{$cust_code}','{$salesperson_id}','{$sched_datetime}','{$sched_note}','{$tech_assignee}','{$tech_assigned}','{$created_by}','{$created_by}','{$site_in_charge}','{$site_in_charge_contact}','{$site_location}')";

        $mysql->Execute($query);
        $last_id = $mysql->ConnectionLastInsertId();
        for ($m=0; $m < count($image_list); $m++) { 
            $obj = $image_list[$m];
            $res = uploadImages($obj,$last_id,$mysql);
        }
    }
    if(isset($_POST['update']) && $_POST['update'] == '1'){
        $active_status = $_POST['active_status'];
        $id = $_POST['id'];
        $query = "UPDATE cms_customer_visit_sched SET sched_datetime = '{$sched_datetime}',sched_note='{$sched_note}',tech_assigned = '{$tech_assigned}',tech_assignee = '{$tech_assignee}' ,updated_by = '{$updated_by}',site_in_charge='{$site_in_charge}',site_in_charge_contact='{$site_in_charge_contact}',site_location='{$site_location}' WHERE id = '{$id}'";

        $mysql->Execute($query);
    }
    if($mysql->AffectedRows() > 0){
        $sched = $mysql->Execute("select * from cms_customer_visit_sched where id = '{$id}'");
        $sched = $sched[0];
        if($salesperson_id == $sched['salesperson_id'] && !empty($sched['tech_assignee'])){
            alertUser($sched['tech_assignee'],$mysql);
        }
        if($salesperson_id == $sched['tech_assignee'] && !empty($sched['tech_assigned'])){
            alertUser($sched['tech_assigned'],$mysql);
        }
        $mysql->Execute("update cms_customer set updated_at = now() where cust_code = '{$cust_code}'");
        echo json_encode(array('result'=>'1'));
    }else{
        echo json_encode(array('result'=>'0','query'=>$query));
    }
}

function alertUser($salesperson_id,$mysql){
        $salesperson_device = $mysql->Execute("select device_token from cms_salesperson_device where login_id = '{$salesperson_id}' and device_token <> login_id and device_token <> '';");

        $device_token = array();
        for ($i=0; $i < count($salesperson_device); $i++) { 
            $obj = $salesperson_device[$i];
            if($obj['device_token']){
                $device_token[] = $obj['device_token'];
            }
        }

        $content = array(
            "en" => "📆 A new schedule has been updated. 📱 Please go to Service Request for details. Thank you 🙏🏻"
        );
        $title = "ESv2 Check-in Schedule";
        
        $newTitle = array(
            "en"=>$title
        );
    
        $fields = array(
            'app_id' => "aae0f2d5-28b8-4ccd-ae18-a18eb092d1ee",
            'include_player_ids' => $device_token,
            'data' => array("foo" => "bar"),
            'contents' => $content
        );
        if($title){
            $fields['headings'] = $newTitle;
        }
    
        $fields = json_encode($fields);
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
        'Authorization: Basic ZTIyMzE4YTYtMmE4Mi00NDhlLWJiNDQtMmQwNGRkYzVhZGYw'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    
        curl_exec($ch);
        curl_close($ch);
}
function uploadImages($data,$bind_id,$mysql){
    $json_data = array(
        'code'=>'200',
        'status'=>'0',
        'message'=>'Script is loaded successfully',
        'result'=>'null'
    );

    $salesperson_id = $data['salesperson_id'];
    $filename = $data['filename'];
    $base64data = $data['base64data'];
    $taken_time = $data['taken_time'];
    $location = $data['location'];
    $remark = $data['remark'];
    $type_name = $data['type_name'];
    $bind_type = $data['bind_type'];
    //file_put_contents('image.log','SENDING-'.$base64data.PHP_EOL,FILE_APPEND);
    $saved = base64_to_image($base64data,$filename);
    //file_put_contents('image.log',$filename.'SAVED-'.$saved.PHP_EOL,FILE_APPEND);
    if($saved){
        $image_path = siteURL().'/salesperson_uploads/'.$filename;

        $sql = "insert into cms_salesperson_uploads (upload_image, upload_type_name, upload_remark, upload_salesperson_id, upload_location, upload_bind_id,
                upload_bind_type, taken_date) values ('".MySQL::sanitize($image_path)."','".MySQL::sanitize($type_name)."',
                '".MySQL::sanitize($remark)."',".$salesperson_id.",'".MySQL::sanitize($location)."','".MySQL::sanitize($bind_id)."',
                '".MySQL::sanitize($bind_type)."','".MySQL::sanitize($taken_time)."')";
        //file_put_contents('image.log','SQL-'.$sql.PHP_EOL,FILE_APPEND);
            if($mysql->Execute($sql)){
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

    return $json_data['result'];
}
function base64_to_image($base64_string, $output_file) {
    $data = explode( ',', $base64_string );
    $ifp = fopen( '../../../salesperson_uploads/'.$output_file, 'wb' ); 
    if(fwrite( $ifp, base64_decode($data[1]))){
        fclose( $ifp ); 
        return $output_file; 
    }else{
        return null;
    }
}

function siteURL(){
    $protocol = "https://";
    $domainName = $_SERVER['HTTP_HOST'];
    return $protocol.$domainName;
}
?>