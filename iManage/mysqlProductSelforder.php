<?php
require_once('./model/MySQL.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
date_default_timezone_set('Asia/Kuala_Lumpur');

if(isset($_GET['client']) && isset($_GET['categoryId'])){
    $queries = array();
    $client = $_GET['client'];
    $category_id = intval($_GET['categoryId']);
    $custCode = isset($_GET['cust_code']) ? $_GET['cust_code'] : '';
    $salesperson_id = $_GET['salesperson_id'];
    $debug = isset($_GET['debug']) ? 1 : 0;

    $empty_arr = json_encode(array());
    if(empty($client)){
        echo $empty_arr;
        return;
    }
    
    $settings = parse_ini_file('../config.ini',true);

    $product_code_qrcode = $settings['QR_CODE']['product_code_qrcode'];
	$product_code_qrcode = in_array($client,$product_code_qrcode);

    $bind_items = $settings['BIND_ITEMS']['cms_stock_tmplt_bind'];
    $bind_items = in_array($client,$bind_items);

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


    $settings = $settings[$client];
    $mysql = new MySQL($settings);

    $has_batch = $mysql->Execute("select * from information_schema.TABLES where TABLE_NAME = 'cms_project'");
    $has_batch = count($has_batch) > 0;

    $role_id = 2;
    $preset_wh = "";
    $res = $mysql->Execute("select * from cms_login where login_id = '{$salesperson_id}'");
    if($res && count($res) > 0){
        $role_id = intval($res[0]['role_id']);
        if (!empty($res[0]['proj_no'])){
            $expld = explode(",",$res[0]['proj_no']);
            $expld = "'".implode("','",$expld)."'";

            $preset_wh = " wq.wh_code in ({$expld}) AND ";
        }
    }

    $hide_price =  $client != "sunchuan" && $role_id == 9;


    $hide_zero_qty_price_tag = $client == 'ssf';
    $search_dtp = $client == 'moss';
    $twinsbee = $client == "twinbee";
    $mesah = $client == "mesah";
    $cost_price = in_array($client,array("weylite"));

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
    $change_uom = in_array($client,array("nikkata"));
    $show_ready_qty = in_array($client,array("mohheng"));

    $display_uom = $hide_other_uom ? $settings['Display_UOM'][$client] : [];

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
    $queries[] = array('$bind_items && empty($custCode)'=>$bind_items && empty($custCode));
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
    /** Restrict Items - End */

    $stocks = array();
    $categoryList = array();
    $productList = array();
    

    if($category_id){
        $query = "select * from cms_product where category_id = '{$category_id}' and product_code <> '';";
        $queries[] = $query;
        $stocks = $mysql->Execute($query);
    }else{
        $extraCond = '';
        if(count($bind_catergories) > 0){
            $extraCond = implode(',',$bind_catergories);
            $extraCond = " and category_id in ({$extraCond}) ";
        }
        $select = "*";
        if ($client == 'lewtaihing'){
            $select = "category_id, categoryIdentifierId, categoryIdentifierId as category_name, parent_category_id, sequence_no, category_status, category_image_url, updated_at, moderator";
        }
        $query = "select {$select} from cms_product_category where categoryIdentifierId <> '' {$extraCond} order by category_name;";
        $queries[] = $query;
        
        $categoryList = $mysql->Execute($query);

        if ($client == "rhymotor"){
            for($i = 0; $i < count($categoryList); $i++){
                $obj = $categoryList[$i];
                if (empty($obj['category_name'])){
                    $obj['category_name'] = $obj['categoryIdentifierId'];
                }
                $categoryList[$i] = $obj;
            }
        }
        if ($no_category){
            for($i = 0; $i < count($categoryList); $i++){
                $obj = $categoryList[$i];
                $obj['category_status'] = 0;
                $categoryList[$i] = $obj;
            }
        }
        if(!($bind_items && $custCode)){
            $query = "select * from cms_product where category_id = 0 and product_code <> '' limit 1000;";
            if ($no_category){
                $query = "select * from cms_product where product_code <> '' limit 1000;";
            }
            $queries[] = $query;
            $stocks = $mysql->Execute($query);
        }
    }
    $warehouse = '';
    if($client == 'bigbathxil'){
        $branch = $mysql->Execute("select * from cms_customer_branch where cust_code = '{$custCode}'");
        if(count($branch)>0){
            $warehouse = $branch[0]['branch_code'];
        }
    }

    for ($i=0; $i < count($stocks); $i++) { 

        $product = safe($stocks[$i]);

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
        $product['search_free_text'] = $product['search_filter'] ? $product['search_filter'] : array();
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
        $product['updated_at'] = $product['updated_at'];
        
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
        
        $idToSearch = $product['product_id'];
        $codeToSearch = MySQL::sanitize($product['product_code']);

        $dtpMinPrice = 0;
        if($search_dtp){
            $dtpRes = $mysql->Execute("SELECT * FROM cms_product_price_v2 WHERE product_code = '{$codeToSearch}' AND active_status = 1 AND price_cat = 'DTP'");
            
            $dtpRes = $dtpRes[0];
            $product['search_min_price'] = $dtpRes['product_price'];
            $dtpMinPrice = $dtpRes['product_price'];
        }
        
        $priceArr = $mysql->Execute("SELECT * FROM cms_product_uom_price_v2 WHERE product_code = '{$codeToSearch}' AND active_status = 1");
        if($priceArr && count($priceArr) > 0){
            for ($iii=0; $iii < count($priceArr); $iii++) { 
                $objPrice = $priceArr[$iii];
                $objPrice['QR_code'] = isset($objPrice['QR_Code']) ? $objPrice['QR_Code'] : '';
                $objPrice['QR_Code'] = isset($objPrice['QR_Code']) ? $objPrice['QR_Code'] : '';
                if ($honest_price){
                    $objPrice['product_std_price'] = $objPrice['product_min_price'];
                    $product['search_price'] = $objPrice['product_min_price'];
                }
                if(intval($objPrice['product_default_price']) == 1 && intval($objPrice['active_status']==1)){
                
                    if($change_uom){
                        $objPrice['product_uom'] = $objPrice['product_uom'] == 'FTS' ? 'PCS' : $objPrice['product_uom'];
                    }
                    $product['search_price'] = $hide_price ? 0 : $objPrice['product_std_price'];
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
        $imgArr = $mysql->Execute("SELECT * FROM cms_product_image WHERE product_id = '{$idToSearch}';");

        if($imgArr && count($imgArr) > 0){
            for ($im=0; $im < count($imgArr); $im++) { 
                $imgobj = $imgArr[$im];
                $imgobj['image_url'] = str_replace('http:','https:',$imgobj['image_url']);
                if(intval($imgobj['active_status']) == 1 && intval($imgobj['product_default_image']) == 1 && empty($product['search_def_image_web'])){
                    $product['search_def_image_web'] = str_replace('http:','https:',$imgobj['image_url']);
                }
                $product['item_images'][] = $imgobj;
            }
        }

        $sPriceArr = $mysql->Execute("SELECT * FROM cms_product_price_v2 WHERE product_code = '{$codeToSearch}' ");
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

        $product['wh_stock'] = array();

        if (!empty($preset_wh)){
            $whStockArr = $mysql->Execute("SELECT wq.*,w.wh_name FROM cms_warehouse_stock AS wq LEFT JOIN cms_warehouse w ON w.wh_code = wq.wh_code WHERE  {$preset_wh} product_code = '{$codeToSearch}' AND active_status = 1 AND w.wh_status = 1");

            file_put_contents("wh_log.log","SELECT wq.*,w.wh_name FROM cms_warehouse_stock AS wq LEFT JOIN cms_warehouse w ON w.wh_code = wq.wh_code WHERE  {$preset_wh} product_code = '{$codeToSearch}' AND active_status = 1 AND w.wh_status = 1",FILE_APPEND);
        }

        if(empty($warehouse) && empty($preset_wh)){
            $whStockArr = $mysql->Execute("SELECT wq.*,w.wh_name FROM cms_warehouse_stock AS wq LEFT JOIN cms_warehouse w ON w.wh_code = wq.wh_code WHERE product_code = '{$codeToSearch}' AND active_status = 1 AND w.wh_status = 1");
        }
        
        if (!empty($warehouse)){
            $whStockArr = $mysql->Execute("SELECT wq.*,w.wh_name FROM cms_warehouse_stock AS wq LEFT JOIN cms_warehouse w ON w.wh_code = wq.wh_code WHERE product_code = '{$codeToSearch}' AND wq.wh_code = '{$warehouse}' AND active_status = 1 AND w.wh_status = 1");
        }

        $available_qty = 0;

        if($whStockArr && count($whStockArr) > 0){
            for ($j=0,$jLen = count($whStockArr); $j < $jLen; $j++) { 
                $wEach = safe($whStockArr[$j]);

                $unique = "{$wEach['product_code']}{$wEach['wh_code']}{$wEach['active_status']}";
                $unique = str_replace(" ","",$unique);
                $unique = strtoupper($unique);

                $wEach['uniqueCode'] = $unique;
                $wEach['ready_st_qty'] = $show_ready_qty ? $wEach['ready_st_qty'] :  $wEach['available_st_qty'];

                $wEach['mine_qty'] = 0;
                $product['wh_stock'][] = $wEach;

                if(!empty($warehouse)){
                    $available_qty += floatval($wEach['available_st_qty']);
                }
            }
        }

        if(!empty($warehouse)){
            $product['product_current_quantity'] = $available_qty;
            $product['product_available_quantity'] = $available_qty;
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
        }
        $product['serial_no'] = array();
        if ($has_serial_no){
            $product['serial_no'] = $mysql->Execute("SELECT * FROM cms_serial_no WHERE product_code = '{$codeToSearch}' and active_status = 1;");
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
        
        $productList[] = $product;
    }
    $mysql->Close();

    if ($debug){
        echo json_encode($queries);
    }

    echo json_encode(
        array(
            'category'=>$categoryList,
            'stocks'=>$productList
        )
    );
}

function safe($obj){
    foreach ($obj as $key => $value) {
        if($value == null){
            $obj[$key] = '';
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