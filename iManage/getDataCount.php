<?php
require_once('./model/DB_class.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$settings                       = parse_ini_file('../config.ini',true);

if(isset($_GET['client']) && isset($_GET['salesperson_id'])){
    $client                     = $_GET['client'];
    $salesperson_id             = $_GET['salesperson_id'];

    $db                         = new DB();

    $settings                   = $settings[$client];

    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
    $db_name                    = $settings['db'];
    $db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

    $connection                 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);

    $custCount = 0;
    $productCount = 0;
    $categoryCount = 0;
    $priceTagCount = 0;
    $priceCount = 0;
    $imageCount = 0;
    $outsoCount = 0;
    $purchasePriceCount = 0;

    $query = "select count(*) as total from cms_customer_salesperson as sp join cms_customer cs on cs.cust_id = sp.customer_id where sp.salesperson_id = '{$salesperson_id}';";
    $db->query($query);
    while($result = $db->fetch_array()){
        $custCount = $result['total'];
    }

    $query = "select count(*) as total from cms_product;";
    $db->query($query);
    while($result = $db->fetch_array()){
        $productCount = $result['total'];
    }

    $query = "select count(*) as total from cms_product_category;";
    $db->query($query);
    while($result = $db->fetch_array()){
        $categoryCount = $result['total'];
    }
    
    $query = "select count(*) as total from cms_product_price_v2 where active_status = 1;";
    $db->query($query);
    while($result = $db->fetch_array()){
        $priceTagCount = $result['total'];
    }

    $query = "select count(*) as total from cms_product_uom_price_v2 where active_status = 1;";
    $db->query($query);
    while($result = $db->fetch_array()){
        $priceCount = $result['total'];
    }

    $query = "select count(*) as total from cms_product_image;";
    $db->query($query);
    while($result = $db->fetch_array()){
        $imageCount = $result['total'];
    }

    $query = "SELECT count(*) as total FROM cms_outstanding_so where so_salesperson_id = '{$salesperson_id}';";
    $db->query($query);
    while($result = $db->fetch_array()){
        $outsoCount = $result['total'];
    }

    $query = "SELECT count(*) as total FROM cms_outstanding_so where so_salesperson_id = '{$salesperson_id}';";
    $db->query($query);
    while($result = $db->fetch_array()){
        $outsoCount = $result['total'];
    }

    $check = "SHOW TABLES LIKE 'cms_product_purchase_price';";
    $db->query($check);
    $countCheck = 0;
    while($result = $db->fetch_array()){
        $countCheck++;
    }
    if($countCheck){
        $query = "SELECT count(*) as total FROM cms_product_purchase_price;";
        $db->query($query);
        while($result = $db->fetch_array()){
            $purchasePriceCount = $result['total'];
        }
    }

    echo json_encode(array(
        "custInCloud"=>$custCount,
        "productInCloud"=>$productCount,
        "defPriceInCloud"=>$priceCount,
        "priceTagInCloud"=>$priceTagCount,
        "productImageInCloud"=>$imageCount,
        "categoryInCloud"=>$categoryCount,
        "outSoInCloud"=>$outsoCount,
        "purchasePriceInCloud"=>$purchasePriceCount
    ));
}
?>