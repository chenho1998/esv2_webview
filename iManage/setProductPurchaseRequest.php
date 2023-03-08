<?php
header('Content-Type: text/plain; charset="UTF-8"');
require_once('./model/MySQL.php');

if(isset($_GET['client'])){
    $config = parse_ini_file('../config.ini',true);

	$client                     = $_GET['client'];

	$settings                   = $config[$client];

    $mysql = new MySQL($settings);
    
    $table_query = 'CREATE TABLE `cms_product_purchase_request` (
        `id` INT(10) NOT NULL AUTO_INCREMENT,
        `product_code` VARCHAR(100) NOT NULL,
        `product_uom` VARCHAR(100) NOT NULL,
        `quantity` DOUBLE DEFAULT NULL,
        `salesperson_id` INT(10) NOT NULL,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unq` (`product_code`,`product_uom`,`salesperson_id`)
      ) ENGINE=INNODB DEFAULT CHARSET=utf8;';

    $checkTable = "SELECT count(*) as isexists FROM information_schema.TABLES WHERE TABLE_NAME = 'cms_product_purchase_request' 
    AND TABLE_SCHEMA in (SELECT DATABASE());";
    $select = $mysql->Execute($checkTable);

    $needToCreate = false;
    if($select){
        $isExists = $select[0]['isexists'];
        $needToCreate = intval($isExists) <= 0;
    }
    if($needToCreate){
        $mysql->Execute($table_query);
    }

    $data                   = json_decode($_POST['productInfo'],true);
    if($data){
        $product_code = MySQL::sanitize($data['product_code']);
        $product_uom = MySQL::sanitize($data['product_uom']);
        $quantity = floatval($data['quantity']);
        $salesperson_id = MySQL::sanitize($data['salesperson_id']);

        $query = "INSERT INTO cms_product_purchase_request (product_code, product_uom, quantity, salesperson_id, updated_at) VALUES ('{$product_code}','{$product_uom}','{$quantity}','{$salesperson_id}',NOW()) ON DUPLICATE KEY UPDATE quantity = quantity+VALUES(quantity), updated_at = VALUES(updated_at)";

        $mysql->Execute($query);

        if($mysql->AffectedRows()){
            echo json_encode(
                array(
                    "success"=>"1",
                    "query"=>$query
                )
            );
        }else{
            echo json_encode(
                array(
                    "success"=>"0",
                    "query"=>$query
                )
            );
        }
    }else{
        echo json_encode(
            array(
                "success"=>"0"
            )
        );
    }
}