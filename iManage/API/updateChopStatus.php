<?php
header('Content-Type: text/plain; charset="UTF-8"');
require_once('model/DB_class.php');

function updateChopStatus($data)
{

    $config                     = parse_ini_file('../config.ini',true);
    $client                     = $data['client'];
    $settings                   = $config[$client];
    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
    $db_name                    = $settings['db'];
    $db_host                    = $settings['host'] ?  $settings['host'] : 'easysales.asia';


    $db     = new DB();
    $con_1  = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);
    
    $json_data = array(
			'result'=> ""
			,'action'=>"updateChopStatus"
			,'data'=>array()
			,"message"=> ""
			,"message_code"=> ""
	);
	
	$db->query("UPDATE `cms_do_job` SET `chop_status` = 1, `chop_remark` = '". $data['chop_remark']."' WHERE `job_id` = '". $data['chop_id'] ."'");
	if($db->get_affected_rows() == 0)
	{
		$json_data['result'] = '0';
        $json_data['message'] = 'Failed update.';
	}
	else
	{
		$json_data['result'] = '1';
		$json_data['message'] = 'Updated successfully.';

	}
	
	return json_encode($json_data);
}

?>