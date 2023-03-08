<?php
// header('Content-Type: text/plain; charset="UTF-8"');
require_once('model/DB_class.php');


function getDoJobStatus($client)
{  
    $settings                   = parse_ini_file('../config.ini',true);

	$db = new DB();

    $settings                   = $settings[$client];

    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
    $db_name                    = $settings['db'];
    $db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

    $connection                 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);

    if(!$connection){
        echo 'db error';
        die();
    }
    $db2 = clone $db;

    $job_status = array();
    $db->query("select distinct do_type from cms_do_job order by do_type;");
    while($result = $db->fetch_array()){
        $do_type = $result['do_type'];
        $db2->query("select distinct job_status from cms_do_job where do_type = '{$do_type}' order by job_status;");
        while($result2 = $db2->fetch_array()){
            $job_status[$do_type][] = $result2['job_status'];
        }
    }

    return $job_status;
}