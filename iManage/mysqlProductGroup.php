<?php
require_once('./model/MySQL.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
date_default_timezone_set('Asia/Kuala_Lumpur');

if(isset($_GET['client']) && isset($_GET['group_id'])){
    $client = $_GET['client'];
    $group_id = intval($_GET['group_id']);

    $settings = parse_ini_file('../config.ini',true);
    $settings = $settings[$client];
    $mysql = new MySQL($settings);

    $search_dtp = $client == 'moss';

    if($group_id == 0){
        $product_group = array();

        $master_query = masterquery($client);
        $group = $mysql->Execute($master_query);

        for ($i=0; $i < count($group); $i++) { 
            $obj = $group[$i];

            $group_id = $obj['id'];
            $group_remark = '';
            $detailquery = detailquery($client,$group_id);
            $details = $mysql->Execute($detailquery);
            for ($j=0; $j < count($details); $j++) { 
                $dtl = $details[$j];
                $group_remark = $dtl['filter'];
            }
            $product_group[]=array(
                'grp_id'=>$obj['id'],
                'grp_name'=>$obj['name'],
                'grp_description'=>$obj['description'],
                'grp_product_code'=>$obj['product_code'],
                'grp_date_created'=>$obj['date_created'],
                'grp_image'=> $obj['image_url'],
                'grp_filter'=>$group_remark,
                'grp_status'=>$obj['active_status']
            );
        }
        $mysql->Close();
        echo json_encode(array('group'=>$product_group),JSON_UNESCAPED_UNICODE);
    }else{
        $stocks = $mysql->Execute("select * from cms_product where product_group_id = '{$group_id}';");
        $productList = array();

        for ($i=0; $i < count($stocks); $i++) { 

            $product = safe($stocks[$i]);
    
            $product['product_desc'] = strip_tags($product['product_desc']);
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
            $product['sst_amount'] = isset($product['sst_code']) ? 10 : 0;
            $product['updated_at'] = $product['updated_at'];
    
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
                    if(intval($objPrice['product_default_price']) > 0){
                        $product['search_price'] = $objPrice['product_std_price'];
                        $product['search_uom'] = $objPrice['product_uom'];
                        $product['search_min_price'] = $search_dtp ? $dtpMinPrice : $objPrice['product_min_price'];
                    }
                    if($search_dtp){
                        $dtpRes = $mysql->Execute("SELECT * FROM cms_product_price_v2 WHERE product_code = '{$codeToSearch}' AND active_status = 1 AND price_cat = 'DTP' AND product_uom = '{$objPrice['product_uom']}'");
                        if(count($dtpRes) > 0){
                            $dtpRes = $dtpRes[0];
                            $objPrice['product_min_price'] = $dtpRes['product_price'];
                        }
                    }
                    $product['uom_price'][] = $objPrice;
                }
            }
            $product['item_images'] = array();
            $imgArr = $mysql->Execute("SELECT * FROM cms_product_image WHERE product_id = '{$idToSearch}';");
            /* if($imgArr && count($imgArr) > 0){
                for ($im=0; $im < count($imgArr); $im++) { 
                    $imgobj = $imgArr[$im];
                    if(intval($imgobj['active_status']) == 1 && intval($imgobj['product_default_image']) == 1){
                        $product['search_def_image_web'] = str_replace('http:','https:',$imgobj['image_url']);
                    }
                    $product['item_images'][] = $imgobj;
                }
            } */
            if($imgArr && count($imgArr) > 0){
                for ($im=0; $im < count($imgArr); $im++) { 
                    $imgobj = $imgArr[$im];
                    if(intval($imgobj['active_status']) == 1 && intval($imgobj['product_default_image']) == 1 && empty($product['search_def_image_web'])){
                        $product['search_def_image_web'] = str_replace('http:','https:',$imgobj['image_url']);
                    }
                    $product['item_images'][] = $imgobj;
                }
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
    
            $whStockArr = $mysql->Execute("SELECT wq.*,w.wh_name FROM cms_warehouse_stock AS wq LEFT JOIN cms_warehouse w ON w.wh_code = wq.wh_code WHERE product_code = '{$codeToSearch}' AND active_status = 1 AND w.wh_status = 1");
            if($whStockArr && count($whStockArr) > 0){
                for ($j=0,$jLen = count($whStockArr); $j < $jLen; $j++) { 
                    $wEach = safe($whStockArr[$j]);
    
                    $unique = "{$wEach['product_code']}{$wEach['wh_code']}{$wEach['active_status']}";
                    $unique = str_replace(" ","",$unique);
                    $unique = strtoupper($unique);
    
                    $wEach['uniqueCode'] = $unique;
                    $wEach['ready_st_qty'] = $wEach['available_st_qty'];
    
                    $wEach['mine_qty'] = 0;
                    $product['wh_stock'][] = $wEach;
                }
            }
    
            $productList[] = $product;
        }
        echo json_encode(
            array(
                'category'=>array(),
                'stocks'=>$productList
            )
        );
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

function masterquery($name){
    $group_queries['megadis'] = "select g.*, p.product_id, cpi.image_url from cms_product_group g left join cms_product p
    on p.product_code = g.product_code left join cms_product_image cpi on p.product_id = cpi.product_id
    and cpi.product_default_image = 1 and cpi.active_status = 1 order by g.active_status desc;";

    $group_queries['kimlee'] = $group_queries['megadis'];

    $group_queries['twagrohw'] = $group_queries['megadis'];

    $group_queries['abaro'] = $group_queries['megadis'];

    return $group_queries[$name];
}
function detailquery($name,$id){
    $group_queries['megadis'] = "select group_concat(distinct product_remark separator '') as filter from cms_product where product_group_id = '{$id}' group by product_group_id";

    $group_queries['kimlee'] = $group_queries['megadis'];

    $group_queries['twagrohw'] = $group_queries['megadis'];

    $group_queries['abaro'] = $group_queries['megadis'];

    return $group_queries[$name];
}
?>