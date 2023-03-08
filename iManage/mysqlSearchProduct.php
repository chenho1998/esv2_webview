<?php
require_once('./model/MySQL.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
date_default_timezone_set('Asia/Kuala_Lumpur');

if(isset($_GET['client']) && isset($_GET['query'])){
    $isDebug = isset($_GET['debug']);
    $time_start = microtime(true);
    $empty_arr = json_encode(array());
    if(empty($_GET['query'])){
        echo $empty_arr;
        return;
    }
    $client = $_GET['client'];
    $query = $_GET['query'];
    $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

    $settings = parse_ini_file('../config.ini',true);

    $settings = $settings[$client];
    $mysql = new MySQL($settings);

    $sql = "SELECT * FROM cms_product WHERE product_status = 1 ";

    if($category_id){
        $sql .= " AND category_id = '{$category_id}' ";
    }

    $fields = array(
        'product_code',
        'product_name',
        'product_desc',
        'product_remark'
    );
    
    $where = "";
    
    $splitted = explode(" ",$query);
    $keywords = array();
    for ($i=0,$len = count($splitted); $i < $len; $i++){
        $str = strtolower(trim($splitted[$i]));
        if($str){
            $keywords[] = MySQL::sanitize(str_replace(array("-"," "),"",$str));
        }
    }
    for ($j=0; $j < count($fields); $j++) { 
        $column = $fields[$j];
        if($j == 0){
            $where = "(";
        }else{
            $where .= " OR (";
        }
        for ($i=0,$len = count($keywords); $i < $len; $i++) { 
            $str = $keywords[$i];
            $where .= " REPLACE(REPLACE(`{$column}`, ' ', ''),'-','') LIKE '%{$str}%'";
            if($i != ($len - 1)){
                $where .= " AND ";
            }
        }
        $where .= ")";
    }

    if($where){
        $sql .= " AND ({$where})";
    }

    $sql .= " LIMIT 200;";
    
    $result = $mysql->Execute($sql);
    if($result == false || count($result) == 0){
        echo $empty_arr;
        if($isDebug == false){
            return;
        }
    }

    $final = array();

    for ($i=0,$len = count($result); $i < $len; $i++) { 
        $each = safe($result[$i]);
        $product = $each;
        $product['product_desc'] = strip_tags($product['product_desc']);
        $product['search_free_text'] = $each['search_filter'];
        $product['search_price'] = 0;
        $product['search_uom'] = 0;
        $product['search_min_price'] = 0;
        $product['search_def_image'] = '';
        $product['search_def_image_web'] = '';
        $product['wh_stock'] = array();
        $product['price_tags'] = array();
        $product['customer_qty'] = array();
        $product['uom_price'] = array();
        $product['is_replacement'] = 0;
        $product['sst_amount'] = 0;
        $product['updated_at'] = '';

        $idToSearch = $each['product_id'];
        $codeToSearch = MySQL::sanitize($each['product_code']);
        
        /* $priceArr = $mysql->Execute("SELECT * FROM cms_product_uom_price_v2 WHERE product_code = '{$codeToSearch}' AND active_status = 1 AND product_default_price = 1");
        if($priceArr && count($priceArr) > 0){
            $priceArr = $priceArr[0];

            $product['search_price'] = $priceArr['product_std_price'];
            $product['search_uom'] = $priceArr['product_uom'];
            $product['search_min_price'] = $priceArr['product_min_price'];
        } */

        $priceArr = $mysql->Execute("SELECT * FROM cms_product_uom_price_v2 WHERE product_code = '{$codeToSearch}' AND active_status = 1");// AND product_default_price = 1
        if($priceArr && count($priceArr) > 0){
            for ($iii=0; $iii < count($priceArr); $iii++) { 
                $objPrice = $priceArr[$iii];
                if(intval($objPrice['product_default_price']) > 0){
                    $product['search_price'] = $objPrice['product_std_price'];
                    $product['search_uom'] = $objPrice['product_uom'];
                    $product['search_min_price'] = $objPrice['product_min_price'];
                }
                $product['uom_price'][] = $objPrice;
            }
        }

        $imgArr = $mysql->Execute("SELECT * FROM cms_product_image WHERE product_id = '{$idToSearch}' AND active_status = 1 AND product_default_image = 1;");
        if($imgArr && count($imgArr) > 0){
            $imgArr = $imgArr[0];

            $product['search_def_image_web'] = str_replace('http:','https:',$imgArr['image_url']);
        }

        $sPriceArr = $mysql->Execute("SELECT * FROM cms_product_price_v2 WHERE product_code = '{$codeToSearch}' AND active_status = 1");
        if($sPriceArr && count($sPriceArr) > 0){
            for ($j=0,$pLen = count($sPriceArr); $j < $pLen; $j++) { 
                $pEach = safe($sPriceArr[$j]);
                $pEach['date_from'] = '';
                $pEach['date_to'] = '';
                $product['price_tags'][] = $pEach;
            }
        }

        $whStockArr = $mysql->Execute("SELECT wq.*,w.wh_name FROM cms_warehouse_stock AS wq LEFT JOIN cms_warehouse w ON w.wh_code = wq.wh_code WHERE product_code = '{$codeToSearch}' AND active_status = 1");
        if($whStockArr && count($whStockArr) > 0){
            for ($j=0,$jLen = count($whStockArr); $j < $jLen; $j++) { 
                $wEach = safe($whStockArr[$j]);

                $unique = "{$wEach['product_code']}{$wEach['wh_code']}{$wEach['active_status']}";
                $unique = str_replace(" ","",$unique);
                $unique = strtoupper($unique);

                $wEach['uniqueCode'] = $unique;
                $wEach['ready_st_qty'] = $wEach['available_st_qty'];

                // $wEach['uniqueCode'] = $wEach['id'];
                $wEach['mine_qty'] = 0;
                $product['wh_stock'][] = $wEach;
            }
        }

        $final[] = $product;
    }

    echo json_encode($final);

    $mysql->Close();

    $time_end = microtime(true);
    $execution_time = ($time_end - $time_start);//60;
    if(isset($_GET['debug'])){
        echo $sql;
        echo "Total Execution Time: ".number_format((float) $execution_time, 10);
    }
}
function safe($obj){
    foreach ($obj as $key => $value) {
        if($value == null){
            $obj[$key] = '';
        }
    }
    return $obj;
}
?>