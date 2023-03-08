<?php
require_once('./model/MySQL.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
date_default_timezone_set('Asia/Kuala_Lumpur');

ini_set('memory_limit', '1024M');
ini_set('max_execution_time', 10800);

if(isset($_GET['client']) && isset($_GET['query'])){

    $isDebug = isset($_GET['debug']);
    $time_start = microtime(true);
    $empty_arr = json_encode(array());
    if(empty($_GET['query'])){
        echo $empty_arr.'<br><br>';
        return;
    }
    
    $preset_wh = isset($_GET['preset_wh']) ? $_GET['preset_wh'] : '';
    $salesperson_id = $_GET['salesperson_id'];
    $client = $_GET['client'];
    $query = $_GET['query'];
    $queryMirror = isset($_GET['mirror']) ? $_GET['mirror'] : '';
    $custCode = isset($_GET['cust_code']) ? $_GET['cust_code'] : '';
    $isQrCodeSearching = isset($_GET['qr_code']) ? true : false;


    $hide_zero_qty_price_tag = $client == 'ssf';
    $search_dtp = $client == 'moss';
    $twinsbee = $client == "twinbee";
    $mesah = $client == "mesah";
    $cost_price = in_array($client,array("weylite"));
    $change_uom = in_array($client,array("nikkata"));


    $search_filter_columns = array(
        "pbauto"=>array(
            "product_code"=>"code",
            "product_name"=>"desc",
            "QR_code"=>"barcode"
        ),
        "aek"=>array(
            "product_code"=>"code",
            "product_name"=>"desc",
            "QR_code"=>"barcode"
        )
    );

    $show_outso_qty = in_array($client,array('wls'));
    $hide_min_price = in_array($client,array('yhjewelry','shunyi'));
    $hide_std_price = in_array($client,array('yhjewelry','shunyi'));
    $visible_qr_codes = in_array($client,array('mohheng'));
    $merge_remark_qr_codes = in_array($client,array('amc'));
    $has_attributes = in_array($client, array('lewtaihing'));
    $has_serial_no = in_array($client, array("urbanhygienist"));
    $honest_price = in_array($client,array('bigbathxil'));
    $hide_other_uom = in_array($client,array('yewlen'));
    $no_category = in_array($client,array(/* 'mesah' */));
    $show_ready_qty = in_array($client,array("mohheng"));

    $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

    $is_new_server = parse_ini_file('../../config.ini',true);
    $new_server_clients = $is_new_server['new_servers']['client_list'];
    $is_new_server = in_array($client,$new_server_clients);

    $settings = parse_ini_file('../config.ini',true);

    $product_code_qrcode = $settings['QR_CODE']['product_code_qrcode'];
	$product_code_qrcode = in_array($client,$product_code_qrcode);

    $display_uom = $hide_other_uom ? $settings['Display_UOM'][$client] : [];

    $bind_items = $settings['BIND_ITEMS']['cms_stock_tmplt_bind'];
    $bind_items = in_array($client,$bind_items);

    $settings = $settings[$client];
    $mysql = new MySQL($settings);

    $has_attribute = $mysql->Execute("select * from information_schema.TABLES where TABLE_NAME = 'cms_product_attribute'");
    $has_attribute = count($has_attribute) > 0;
    if($has_attribute){
        $attr = $mysql->Execute("select * from cms_product_attribute limit 1");
        $has_attribute = count($attr) > 0;
    }

    $has_batch = $mysql->Execute("select * from information_schema.TABLES where TABLE_NAME = 'cms_product_batch'");
    $has_batch = count($has_batch) > 0;
    
    $role_id = 2;
    $res = $mysql->Execute("select role_id,proj_no from cms_login where login_id = '{$salesperson_id}'");
    if($res && count($res) > 0){
        $role_id = intval($res[0]['role_id']);
        if(empty($preset_wh)){
            $preset_wh = $res[0]['proj_no'];
        }
    }

    if($isDebug){
        echo json_encode($_GET).'<br><br>';
        echo "select role_id from cms_login where login_id = '{$salesperson_id}'".'<br><br>';
        echo $role_id.'<br><br>';
    }

    $hide_price =  $client != "sunchuan" && $role_id == 9;

    /** Restrict Items - Start */
    $bind_catergories = array();
    $bind_products = array();
    if($bind_items && $custCode){
        $check = $mysql->Execute("select d.*,c.category_id from cms_stock_tmplt_bind b left join cms_stock_tmplt_dtl d
        on d.tmplt_id = b.tmplt_id and d.active_status = 1 and b.active_status = 1
        left join cms_product_category c on c.categoryIdentifierId = d.dtl_code and d.dtl_type = 'CATEGORY'
        where b.cust_code = '{$custCode}';");
        if(count($check) > 0){
            for ($i=0; $i < count($check); $i++) { 
                $obj = $check[$i];
                if($obj['dtl_type'] == 'ITEMS'){
                    $bind_products[] = $obj['dtl_code'];
                }
                if($obj['dtl_type'] == 'CATEGORY'){
                    $bind_catergories[] = $obj['category_id'];
                }
            }
        }
    }
    if($bind_items && empty($custCode)){
        $check = $mysql->Execute("select distinct d.dtl_code, d.dtl_name,c.category_id
        from cms_stock_tmplt_bind b
        left join cms_stock_tmplt_dtl d on d.tmplt_id = b.tmplt_id and d.active_status = 1 and d.dtl_type = 'CATEGORY'
        join cms_product_category c on c.categoryIdentifierId = d.dtl_code
        join cms_stock_tmplt t on t.id = d.tmplt_id and t.active_status = 1
        where b.salesperson_id = {$salesperson_id} and b.active_status = 1 and c.category_status = 1 order by d.dtl_name");
        if(count($check) > 0){
            for ($i=0; $i < count($check); $i++) { 
                $obj = $check[$i];
                $bind_catergories[] = $obj['category_id'];
            }
        }

        $check = $mysql->Execute("select distinct d.dtl_code, d.dtl_name,c.category_id
        from cms_stock_tmplt_bind b
        left join cms_stock_tmplt_dtl d on d.tmplt_id = b.tmplt_id and d.active_status = 1 and d.dtl_type = 'ITEMS'
        join cms_product c on c.product_code = d.dtl_code
        join cms_stock_tmplt t on t.id = d.tmplt_id and t.active_status = 1
        where b.salesperson_id = {$salesperson_id} and b.active_status = 1 and c.product_status = 1 order by d.dtl_name");
        if(count($check) > 0){
            for ($i=0; $i < count($check); $i++) { 
                $obj = $check[$i];
                $bind_products[] = $obj['dtl_code'];
            }
        }
    }

    if($isDebug){
        echo 'BIND PRODUCT';
        echo json_encode($bind_products).'<br><br>';
        echo 'BIND CATEGORY';
        echo json_encode($bind_catergories).'<br><br>';
    }
    /** Restrict Items - End */

    if($hide_price || $honest_price){
        $sql = "SELECT * FROM cms_product WHERE (product_status = 1 OR product_status = 0) ";
    }else{
        $sql = "SELECT * FROM cms_product WHERE product_status = 1 ";
    }

    if($category_id){
        $sql .= " AND category_id = '{$category_id}' ";
    }

    $decodeQuery = json_decode($query,true);
    $decodeMirror = json_decode($queryMirror,true);
    
    $where = "";
    $search_filters = null;
    if (isset($search_filter_columns[$client])){
        $search_filters = $search_filter_columns[$client];
    }

    if($decodeQuery && is_array($decodeQuery)){
        $where = udf($decodeQuery, $decodeMirror, $is_new_server,$search_filters);
    }else{
        $where = normal($query);
    }
    if ($isQrCodeSearching){
        if ($product_code_qrcode){
            $where = " product_code = '".MySQL::sanitize($query)."' ";
        } else {
            $where = " QR_code = '".MySQL::sanitize($query)."' ";
        }
    }
    if($where){
        $sql .= " AND ({$where})";
    }

    $order_by_query = " ORDER BY product_name LIMIT 150;";
    /* if (isset($_GET['offset']) || $_GET['offset'] == 0){
        $order_by_query = " ORDER BY product_name LIMIT ".$_GET['offset'].",150;";
    } */

    if ($client == "gseries" || $client == "nmayang_eng" || $client == "dtcopier"){
        $order_by_query = " ORDER BY product_name;";
    }

    if ($decodeQuery){
        $order_by_query = " LIMIT 150; ";
    }

    $sql .= $order_by_query;

    if ($isDebug){
        echo $sql."<br><br>";
    }

    $result = $mysql->Execute($sql);
    if ($isDebug){
        echo "Result Count::" . count($result) . "---".json_encode($query);
    }
    
    if($result == false || count($result) == 0){
        if ($has_serial_no){
            $qr_code_query = MySQL::sanitize($query);

            $serial_no = $mysql->Execute("select * from cms_serial_no where (product_code like '%{$qr_code_query}%' or batch_no like '%{$qr_code_query}%' or serial_no like '%{$qr_code_query}%') and active_status = 1;");

            if($serial_no == false || count($serial_no) == 0){
                echo $empty_arr;
                return;
            } else {
                $result = array();
                for ($i = 0; $i < count($serial_no); $i++){
                    $srl_no = $serial_no[$i];
                    $product_code_from_serial = MySQL::sanitize($srl_no['product_code']);
                    $obj = $mysql->Execute("select * from cms_product where product_code = '{$product_code_from_serial}' AND product_status = 1;");
                    if (count($obj) > 0){
                        $result[] = $obj[0];
                    }
                }
                if(count($result) == 0){
                    echo $empty_arr;
                    return;
                }
            }
        } else {
            echo $empty_arr;
            if($isDebug == false){
                return;
            }else{
                echo $sql.'<br><br>';
            }
        }
    }

    $final = array();

    for ($i=0,$len = count($result); $i < $len; $i++) { 
        $each = safe($result[$i]);
        $product = $each;

        if($isDebug){
            echo json_encode($each).'<br><br>';
        }

        $_check_product_code = $product['product_code'];
        $_check_category_id = $product['category_id'];

        $skip = 0;
        if(count($bind_catergories) > 0 || count($bind_products) > 0){
            $skip = 1;
            if(in_array($_check_category_id,$bind_catergories)){
                $skip = 0;
            }

            if(in_array($_check_product_code,$bind_products)){
                $skip = 0;
            }
        }

        if($skip){
            continue;
        }

        // if((count($bind_catergories) > 0 && !in_array($_check_category_id,$bind_catergories)) && (count($bind_products) > 0 && !in_array($_check_product_code,$bind_products))){
        //     continue;
        // }
        
        $product['product_desc'] = strip_tags($product['product_desc']);
        if ($has_attributes){
            $product['product_desc'] = "";
        }
        $product['search_free_text'] = $each['search_filter'] ? $each['search_filter'] : array();
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
        // $product['sst_amount'] = $product['sst_code'] == 'ST' ? 10 : 0;
        if ($no_category){
            $product['category_id'] = 0;
        }
        $product['updated_at'] = '';
        
        if($visible_qr_codes){
            $product['QR_code'] = trim(str_replace(".","",$product['QR_code']));
            $product['product_desc'] = $product['QR_code'];
        }
        if($product_code_qrcode){
            $product['QR_code'] = $product['product_code'];
        }
        if ($merge_remark_qr_codes){
            $product['product_remark'] = $product['product_remark']." - ".$product['QR_code'];
        }

        $idToSearch = $each['product_id'];
        $codeToSearch = MySQL::sanitize($each['product_code']);

        $dtpMinPrice = 0;
        if($search_dtp){
            $dtpRes = $mysql->Execute("SELECT * FROM cms_product_price_v2 WHERE product_code = '{$codeToSearch}' AND active_status = 1 AND price_cat = 'DTP'");
            
            $dtpRes = $dtpRes[0];
            $product['search_min_price'] = $dtpRes['product_price'];
            $dtpMinPrice = $dtpRes['product_price'];
        }
        $uom_price = "SELECT * FROM cms_product_uom_price_v2 WHERE product_code = '{$codeToSearch}' ORDER BY active_status DESC, product_default_price DESC";
        if($isDebug){
            echo $uom_price.'<br><br>';
        }
        $priceArr = $mysql->Execute($uom_price);
        if($priceArr && count($priceArr) > 0){
            for ($iii=0; $iii < count($priceArr); $iii++) { 
                $objPrice = safe($priceArr[$iii]);
                $objPrice['QR_code'] = isset($objPrice['QR_Code']) ? $objPrice['QR_Code'] : '';
                if ($honest_price){
                    $objPrice['product_std_price'] = $objPrice['product_min_price'];
                    $product['search_price'] = $objPrice['product_min_price'];
                }
                if(intval($objPrice['product_default_price']) == 1 && intval($objPrice['active_status']==1)){
                    $product['search_price'] = $hide_price ? 0 : $objPrice['product_std_price'];

                    if($change_uom){
                        $objPrice['product_uom'] = $objPrice['product_uom'] == 'FTS' ? 'PCS' : $objPrice['product_uom'];
                    }

                    $product['search_uom'] = $objPrice['product_uom'];
                    $product['search_min_price'] = $search_dtp ? $dtpMinPrice : $objPrice['product_min_price'];

                    $product['search_min_price'] = $hide_price ? 0 : $product['search_min_price'];

                    if($hide_min_price){
                        $product['search_min_price'] = 0;
                    }
                    if($hide_std_price){
                        $product['search_price'] = 0;
                    }
                }
                
                if($search_dtp){
                    $dtpRes = $mysql->Execute("SELECT * FROM cms_product_price_v2 WHERE product_code = '{$codeToSearch}' AND active_status = 1 AND price_cat = 'DTP' AND product_uom = '{$objPrice['product_uom']}'");
                    if(count($dtpRes) > 0){
                        $dtpRes = $dtpRes[0];
                        $objPrice['product_min_price'] = $dtpRes['product_price'];
                    }
                }
                if($hide_price){
                    $objPrice['product_std_price'] = 0;
                    $objPrice['product_min_price'] = 0;
                }
                if($hide_min_price){
                    $objPrice['product_min_price'] = 0;
                }
                if($hide_std_price){
                    $objPrice['product_std_price'] = 0;
                }

                if($display_uom){
                    if(in_array($objPrice['product_uom'],$display_uom)){
                        $product['uom_price'][] = $objPrice;
                    }
                }else{
                    $product['uom_price'][] = $objPrice;
                }
            }
        }

        $product['item_images'] = array();
        $imgArr = $mysql->Execute("SELECT * FROM cms_product_image WHERE product_id = '{$idToSearch}' order by product_image_id desc;");
        if($imgArr && count($imgArr) > 0){
            for ($im=0; $im < count($imgArr); $im++) { 
                $imgobj = $imgArr[$im];
                $imgobj['image_url'] = str_replace('http:','https:',$imgobj['image_url']);
                if(intval($imgobj['active_status']) == 1 && intval($imgobj['product_default_image']) == 1 && empty($product['search_def_image_web'])){
                    $product['search_def_image_web'] = str_replace('http:','https:',$imgobj['image_url']);
                    $product['search_def_image'] = $product['search_def_image_web'];
                }
                $product['item_images'][] = $imgobj;
            }
        }

        $price_v2 = "SELECT * FROM cms_product_price_v2 WHERE product_code = '{$codeToSearch}'";
        if($isDebug){
            echo $price_v2.'<br><br>';
        }
        $sPriceArr = $mysql->Execute($price_v2);
        if($sPriceArr && count($sPriceArr) > 0){
            for ($j=0,$pLen = count($sPriceArr); $j < $pLen; $j++) { 
                $pEach = safe($sPriceArr[$j]);
                $pEach['date_from'] = isDate($pEach['date_from']);
                $pEach['date_to'] = isDate($pEach['date_to']);
                if (strlen($pEach['date_from']) > 0){
                    $pEach['date_to'] = "2030-01-01 23:59:59";
                }
                if ($client != "agri"){
                    $_is_active = __checkCustomerPrice($pEach['active_status'],$pEach['date_from'],$pEach['date_to']);
                    $pEach['active_status'] = $_is_active ? 1 : 0;
                } else {
                    $_is_active = __checkCustomerPrice($pEach['active_status'],$pEach['date_from'],$pEach['date_to']);
                    if ($_is_active == false && $pEach['active_status'] == 1){
                        $product_code = $pEach['product_code'];
                        if (isset($product_inactive_counter[$product_code])){
                            $counter = intval($product_inactive_counter[$product_code]);
                            if ($counter > 29){
                                $pEach['active_status'] = 0;
                            } else {
                                $product_inactive_counter[$product_code] = $counter+1;
                            }
                        }
                    }
                }
                $pEach['mix_discount'] = $pEach['mix_disc'];

                $discount_label = "";
                if ($pEach['disc_1']){
                    // $discount_label = " Discount:".$pEach['disc_1'];
                }
                $pEach['price_remark'] = $pEach['price_remark'] . $discount_label;

                if($hide_zero_qty_price_tag){
                    if($pEach['quantity'] == 0){
                        $pEach['active_status'] = 0;
                    }
                }
                if (empty($pEach['quantity'])){
                    $pEach['quantity'] = 0;
                }

                if($change_uom){
                    $pEach['product_uom'] == 'FTS' ? 'PCS' : $pEach['product_uom'];
                }
                
                if($display_uom){
                    if(in_array($pEach['product_uom'],$display_uom)){
                        $product['price_tags'][] = $pEach;
                    }
                }else{
                    $product['price_tags'][] = $pEach;
                }
            }
        }

        $product_attr = "SELECT * FROM cms_product_attribute WHERE product_code = '{$codeToSearch}'";
        if($isDebug){
            echo $product_attr.'<br><br>';
        }
        /* $product['attributes'] = array();
        $productAttributes = $mysql->Execute($product_attr);
        if($productAttributes && count($productAttributes) > 0){
            for($j = 0; $j < count($productAttributes); $j++){
                $pEach = safe($productAttributes[$j]);
                $pEach['unique_id'] = $pEach['product_attribute_id'];
                $product['attributes'][] = $pEach;
            }
        } */
        $uom_name = MySQL::sanitize($product['search_uom']);
        $wh_query = "";
        if($preset_wh && $hide_price){
            $wh_query = "SELECT wq.*,w.wh_name FROM cms_warehouse_stock AS wq LEFT JOIN cms_warehouse w ON w.wh_code = wq.wh_code WHERE product_code = '{$codeToSearch}' AND active_status = 1 AND w.wh_code = '{$preset_wh}' AND wq.uom_name = '{$uom_name}'";

            $whStockArr = $mysql->Execute($wh_query);
        }else if($preset_wh){
            $wh_query = "SELECT wq.*,w.wh_name FROM cms_warehouse_stock AS wq LEFT JOIN cms_warehouse w ON w.wh_code = wq.wh_code WHERE product_code = '{$codeToSearch}' AND active_status = 1 AND w.wh_status = 1 AND wq.uom_name = '{$uom_name}'";

            $whStockArr = $mysql->Execute($wh_query);
        }else{
            $wh_query = "SELECT wq.*,w.wh_name FROM cms_warehouse_stock AS wq LEFT JOIN cms_warehouse w ON w.wh_code = wq.wh_code WHERE product_code = '{$codeToSearch}' AND active_status = 1 AND w.wh_status = 1 AND wq.uom_name = '{$uom_name}'";

            $whStockArr = $mysql->Execute($wh_query);
        }

        if($isDebug){
            echo $wh_query.'<br><br>';
        }

        if($whStockArr && count($whStockArr) > 0){
            for ($j=0,$jLen = count($whStockArr); $j < $jLen; $j++) { 
                $wEach = safe($whStockArr[$j]);

                $unique = "{$wEach['product_code']}{$wEach['wh_code']}{$wEach['active_status']}";
                $unique = str_replace(" ","",$unique);
                $unique = strtoupper($unique);

                $wEach['uniqueCode'] = $unique;
                
                $wEach['ready_st_qty'] = $show_ready_qty ? $wEach['ready_st_qty'] : $wEach['available_st_qty'];

                // $wEach['uniqueCode'] = $wEach['id'];
                $wEach['mine_qty'] = 0;
                $product['wh_stock'][] = $wEach;
            }
        }

        if($hide_price){
            $product['product_status'] = 1;
        }

        $product['batches'] = array();
        if($has_batch){
            $product['batches'] = $mysql->Execute("select * from cms_product_batch WHERE product_code = '{$codeToSearch}';");
        }

        if($show_outso_qty){
            $outso = $mysql->Execute("select sum(so_out_qty) as out_qty from cms_outstanding_so where so_product_code = '{$codeToSearch}' and active_status = 1 group by so_product_code;");
            if(count($outso) > 0){
                $outso = $outso[0];
                $product['product_available_quantity'] = $outso['out_qty'];
            }else{
                $product['product_available_quantity'] = 0;
            }
        }

        if($twinsbee){
            $product['product_promo'] = 'COST: RM'.number_format($product['product_cost_price'],2).' '.$product['product_promo'];
            $product['product_available_quantity'] = 0;
        }
        if($mesah){
            $product['product_udf'] = $salesperson_id == 113 ? 'COST PRICE:RM'. number_format($product['product_cost_price'],2) .' '. $product['product_udf']  : '';
        }
        if ($cost_price){
            $product['product_promo'] = 'RM'.number_format($product['product_cost_price'],2);
        }
        if (empty($product['product_available_quantity'])){
            $product['product_available_quantity'] = 0;
        }

        if (isset($search_filter_columns[$client])){
            $filters = $search_filter_columns[$client];
            foreach ($filters as $mysql_column_name =>$json_prop_name) {
                if (isset($product[$mysql_column_name])){
                    $mysql_column_value = $product[$mysql_column_name];
                    $product['search_filter'][$json_prop_name] = $mysql_column_value;
                }
            }
            $product['search_filter'] = json_encode($product['search_filter']);
            $product['search_free_text'] = $product['search_filter'];
        }
        if ($honest_price && $product['product_status'] == 0){
            $product['product_status'] = 1;
        }
        $product['serial_no'] = array();
        if ($has_serial_no){
            $product['serial_no'] = $mysql->Execute("SELECT * FROM cms_serial_no WHERE product_code = '{$codeToSearch}' and active_status = 1;");
        }
        $product['attributes'] = array();
        if ($has_attributes){
            $attributes = $mysql->Execute("SELECT * FROM cms_product_attribute_v2 WHERE product_code = '{$codeToSearch}' and available_qty > 0;");
            if (count($attributes) == 0 || $attributes === false){
                $product['product_status'] = 0;
            } else {
                for($j = 0; $j < count($attributes); $j++){
                    $pEach = safe($attributes[$j]);
                    $pEach['unique_id'] = $pEach['product_attribute_id'];
                    $product['attributes'][] = $pEach;
                }
                // $product['attributes'] = $attributes;
            }
            if ($product['product_status'] == 1){
                $final[] = $product;
            }
        } else {
            $final[] = $product;
        }
    }
    
    echo json_encode($final);

    $mysql->Close();

    $time_end = microtime(true);
    $execution_time = ($time_end - $time_start);//60;
    if(isset($_GET['debug'])){
        echo "Total Execution Time: ".number_format((float) $execution_time, 10);
    }
}
function normal($query){
    $fields = array(
        'product_code',
        'product_name',
        'product_desc',
        'QR_code'
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
            $where .= ($column == 'product_code'?" REPLACE(REPLACE(`{$column}`, ' ', ''),'-','') LIKE '%{$str}%'" : " REPLACE(`{$column}`,'-','') LIKE '%{$str}%'");
            if($i != ($len - 1)){
                $where .= " AND ";
            }
        }
        $where .= ")";
    }

    return $where;
}
function udf($queryJson, $queryMirror, $is_new_server,$search_filters){
    $where = "";

    $json_cond = " json_extract(search_filter,'$.@field') LIKE '%@value%' ";
    if ($is_new_server){
        $json_cond = " instr(lower(search_filter->>'$.@field'),'@value') > 0 ";
    }
    if ($search_filters){
        $json_cond = " @field LIKE '%@value%' ";

    }

    $numOfKeys = count(array_keys((array)$queryJson));
    $i = 0;
    foreach($queryJson as $key => $value){
        if(is_array($value)){
            $where .= count($value) > 1 ? " ( " : "";
            for($j = 0, $len = count($value); $j < $len; $j++){
                if($j == 0){
                    $where .= " ( ";
                }else{
                    $where .= " AND ( ";
                }
                $each = strtolower($value[$j]);
                if ($search_filters){
                    $where .= str_replace(array('@field','@value'),array(__no_name_im_busy($search_filters, $key),$each),$json_cond);
                } else {
                    $where .= str_replace(array('@field','@value'),array(__no_name_im_busy($search_filters, $key),$each),$json_cond);
                }
                
                $where .= ")";
            }
            $where .= count($value) > 1 ? " ) " : "";
        }else{
            $value = strtolower($value);
            if(isset($queryMirror[$key])){
                $mirrors = $queryMirror[$key];
                if(count($mirrors) > 0){
                    $where .= " ( ";

                    if ($search_filters){
                        $where .= str_replace(array('@field','@value'),array(__no_name_im_busy($search_filters,$key),$value),$json_cond);
                    } else {
                        $where .= str_replace(array('@field','@value'),array($key,$value),$json_cond);
                    }
                    
                    $where .= " OR  ";
                    $mm = $mirrors[0];

                    if ($search_filters){
                        $where .= str_replace(array('@field','@value'),array(__no_name_im_busy($search_filters,$mm),$value),$json_cond);
                    } else {
                        $where .= str_replace(array('@field','@value'),array($mm,$value),$json_cond);
                    }
                    
                    $where .= " ) ";
                }
            }else{
                if ($search_filters){
                    $where .= str_replace(array('@field','@value'),array(__no_name_im_busy($search_filters,$key),$value),$json_cond);
                } else {
                    $where .= str_replace(array('@field','@value'),array($key,$value),$json_cond);
                }
            }
        }
        if($i != ($numOfKeys-1)){
            $where .= " AND ";
        }
        $i++;
    }
    return $where;
}
function __no_name_im_busy($filters, $code){
    foreach ($filters as $key => $value) {
        if ($value == $code){
            return $key;
        }
    }
}
function safe($obj){
    foreach ($obj as $key => $value) {
        if($value == null){
            $obj[$key] = '';
        }else{
            // $obj[$key] = trim($value);
        }
    }
    return $obj;
}
function __checkCustomerPrice($active_status, $date_from, $date_to){
    $isDateFrom = isDate($date_from);
    $isDateTo = isDate($date_to);
    if ($isDateFrom && empty($isDateTo)){
        $isDateTo = "2030-01-01 23:59:59";
    }
    if($isDateFrom && $isDateTo){
        $current = strtotime(date("Y-m-d"));
        $date_from = strtotime(explode(" ",$date_from)[0]);
        $date_to = strtotime(explode(" ",$date_to)[0]);
        $in_between = $date_from <= $current && $current <= $date_to;
        return $active_status && $in_between ? true : false;
    }
    return $active_status != '0';
}
function isDate($val){
    $zero = '0000-00-00 00:00:00';
    if($val == $zero){
        return '';
    }
    return $val;
}
?>