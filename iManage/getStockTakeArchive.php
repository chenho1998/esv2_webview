<?php 
require_once('./model/MySQL.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
date_default_timezone_set('Asia/Kuala_Lumpur');

if(
    isset($_GET['cust_code']) && 
    isset($_GET['client']) && 
    isset($_GET['salesperson_id'])
){
    $settings = parse_ini_file('../config.ini',true);

    $cust_code = $_GET['cust_code'];
    $client = $_GET['client'];
    $salesperson_id = $_GET['salesperson_id'];

    $settings = $settings[$client];
    $mysql = new MySQL($settings);

    // Stock take and per invoice basis formula. requested by maxios
    $stock_take_record = $mysql->Execute(
        "SELECT a.*,ifnull(b.current_quantity,0) as current_quantity,b.created_date,b.st_id FROM (
            WITH ranked_items AS (
                SELECT cid.id,up.product_uom,i.invoice_date, cid.quantity,cid.invoice_code,p.*, ROW_NUMBER() OVER (PARTITION BY item_code ORDER BY id DESC) AS ranking
                FROM cms_invoice i
                LEFT JOIN cms_invoice_details cid ON i.invoice_code = cid.invoice_code
                JOIN cms_product p on p.product_code = cid.item_code AND p.product_status = 1
                JOIN cms_product_uom_price_v2 up ON p.product_code = up.product_code AND up.product_default_price = 1 AND up.active_status = 1
                WHERE cust_code = '{$cust_code}'
            )
            SELECT * FROM ranked_items WHERE ranking = 1
        ) a
        LEFT JOIN (
            WITH ranked_items AS (
                SELECT cstd.*,stk.created_date, ROW_NUMBER() OVER (PARTITION BY product_code ORDER BY cstd.id DESC) AS ranking
                FROM cms_stock_take stk LEFT JOIN cms_stock_take_dtl cstd on stk.st_id = cstd.st_id
                WHERE cust_code = '{$cust_code}'
            ) 
            SELECT * FROM ranked_items WHERE ranking = 1
        ) b ON a.product_code = b.product_code ORDER BY invoice_date DESC;"
    );

    if(count($stock_take_record) == 0){
        $stock_take_record = $mysql->Execute("WITH ranked_items AS (
            SELECT cstd.id,0 as quantity,up.product_uom,'' as invoice_date,cstd.suggested_quantity,cstd.current_quantity,stk.created_date, p.*,ROW_NUMBER() OVER (PARTITION BY cstd.product_code ORDER BY cstd.id DESC) AS ranking
            FROM cms_stock_take stk LEFT JOIN cms_stock_take_dtl cstd on stk.st_id = cstd.st_id
            JOIN cms_product p on p.product_code = cstd.product_code AND p.product_status = 1
            JOIN cms_product_uom_price_v2 up ON p.product_code = up.product_code AND up.product_default_price = 1 AND up.active_status = 1
            WHERE cust_code = '{$cust_code}'
        ) 
        SELECT * FROM ranked_items WHERE ranking = 1");
    }

    echo json_encode($stock_take_record);
}
?>