<?php
require_once('./model/MySQL.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
date_default_timezone_set('Asia/Kuala_Lumpur');

if(isset($_GET['statement_id'])){
    $settings = parse_ini_file('../config.ini',true);

    $statement_id = $_GET['statement_id'];
    $client = $_GET['client'];

    $settings = $settings[$client];
    $mysql = new MySQL($settings);

    $statements = $mysql->Execute("SELECT * FROM cms_statement_signature WHERE id = '{$statement_id}'");

    echo json_encode(
        array(
            "html" => isset($statements) && count($statements) > 0 ? $statements[0]['html_content'] : ''
        )
    );
}
?>