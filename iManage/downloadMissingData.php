<?php
require_once('./model/DB_class.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$zero_price = array(
    'cozbeau'
);

$settings = parse_ini_file('../config.ini',true);

if(isset($_GET['client'])){

    ini_set('memory_limit', '1024M');
    
    ini_set('max_execution_time', 10800);

    $salesperson_id             = $_GET['salesperson_id'];
    $client                     = $_GET['client'];

    $is_zeroPrice               = in_array($client,$zero_price);

    $db                         = new DB();

    $settings                   = $settings[$client];

    $db_user                    = $settings['user'];
    $db_pass                    = $settings['password'];
    $db_name                    = $settings['db'];
    $db_host                    = isset($settings['host']) ? $settings['host'] : 'easysales.asia';

    $connection                 = $db->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);

    $fixType                    = $_GET['fix'];
    $dataFromDevice             = json_decode($_POST['dataFromDevice'],true);
    $json_data                  = array();

    switch ($fixType) {
        case 'customer':{
            $customerArr = array();

            //check if it's leader
            $isLeader = false;
            $db->query("SELECT * FROM cms_mobile_module WHERE module = 'app_sp_group'");
            while($result = $db->fetch_array()){
                if($result['status'] != 0){
                    $json = json_decode($result['status'],true);
                    $isLeader = $json[$salesperson_id] == 1;
                }
            }

            $query = "select cs.* from cms_customer_salesperson as sp join cms_customer cs on cs.cust_id = sp.customer_id";
            if($isLeader === false){
                $query .= " WHERE sp.salesperson_id = '{$salesperson_id}'";
            }
            
            $db->query($query);

            while($result = $db->fetch_array()){
                $custCode = $result['cust_code'];
                if(!in_array($custCode,$dataFromDevice)){
                    $customerDetails = array(
                        'cust_id'                   =>$result['cust_id']
                        ,'created_date'             =>$result['created_date']
                        ,'cust_code'                =>$result['cust_code']
                        ,'cust_company_name'        =>$result['cust_company_name']
                        ,'cust_incharge_person'     =>$result['cust_incharge_person']
                        ,'cust_reference'           =>$result['cust_reference']
                        ,'cust_email'               =>$result['cust_email']
                        ,'cust_tel'                 =>$result['cust_tel']
                        ,'cust_fax'                 =>$result['cust_fax']
                        ,'cust_remark'              =>$result['cust_remark']
                        ,'billing_address1'         =>$result['billing_address1']
                        ,'billing_address2'         =>$result['billing_address2']
                        ,'billing_address3'         =>$result['billing_address3']
                        ,'billing_address4'         =>$result['billing_address4']
                        ,'billing_city'             =>$result['billing_city']
                        ,'billing_state'            =>$result['billing_state']
                        ,'billing_zipcode'          =>$result['billing_zipcode']
                        ,'billing_country'          =>$result['billing_country']
                        ,'shipping_address1'        =>$result['shipping_address1']
                        ,'shipping_address2'        =>$result['shipping_address2']
                        ,'shipping_address3'        =>$result['shipping_address3']
                        ,'shipping_address4'        =>$result['shipping_address4']
                        ,'shipping_city'            =>$result['shipping_city']
                        ,'shipping_state'           =>$result['shipping_state']
                        ,'shipping_zipcode'         =>$result['shipping_zipcode']
                        ,'shipping_country'         =>$result['shipping_country']
                        ,'selling_price_type'       =>$result['selling_price_type']
                        ,'customer_status'          =>$result['customer_status']
                        ,'termcode'                 =>$result['termcode']
                        ,'current_balance'          =>$result['current_balance']
                    );
                                    
                    $customerArr[] = $customerDetails;   
                }
            }
            $json_data['customerList'] = $customerArr;
            break;
        }
        case 'product':{
            $db2                        = new DB();
            $connection2                = $db2->connect_with_given_connection($db_user,$db_pass,$db_name,$db_host);

            $productArr = array();

            $check = "SHOW TABLES LIKE 'cms_warehouse_stock';";
            $db->query($check);
            $countCheck = 0;
            while($result = $db->fetch_array()){
                $countCheck++;
            }

            $query = "select * from cms_product;";
            $db->query($query);

            while($result = $db->fetch_array()){
                $productCode = $result['product_code'];

                if(!in_array($productCode,$dataFromDevice)){

                    $warehouse_stock = array();

                    if($countCheck){
                        $db2->query("SELECT ws.*,wh.wh_name FROM cms_warehouse_stock AS ws LEFT JOIN cms_warehouse wh ON wh.wh_code = ws.wh_code COLLATE utf8_unicode_ci WHERE product_code = '{$result['product_code']}'");
                    
                        while($res = $db2->fetch_array()){
                            $unique         = "{$res['product_code']}{$res['wh_code']}";
                            $unique         = str_replace(" ","",$unique);
                            $unique         = strtoupper($unique);
                            $res['uniqueCode']      = $unique;
                            $res['ready_st_qty']    = $res['available_st_qty'];
                            $warehouse_stock[]      = $res;
                        }
                    }

                    $search_price = 0;
                    $search_uom = '';
                    $price_tags = array();

                    if($is_zeroPrice){
                        $db2->query("SELECT * FROM cms_product_price_v2 WHERE product_code = '{$result['product_code']}' ORDER BY FIELD(price_cat,'RETAIL') DESC");
                        
                        while($res = $db2->fetch_array()){
                            if($res['price_cat'] == 'RETAIL'){
                                $search_price = floatval($res['product_price']);
                                $search_uom = $res['product_uom'];
                            }
                            $price_tags[] = $res;
                        }
                    }

                    if($result['product_code']){
                        $productDetail = array(
                            'product_id'                    =>$result['product_id']
                            ,'product_code'                 =>$result['product_code']
                            ,'product_name'                 =>$result['product_name']
                            ,'product_desc'                 =>$result['product_desc']
                            ,'product_remark'               =>$result['product_remark']
                            ,'sequence_no'                  =>$result['sequence_no']
                            ,'product_status'               =>$result['product_status']
                            ,'QR_code'                      =>$result['QR_code']
                            ,'product_current_quantity'     =>$result['product_current_quantity']
                            ,'product_available_quantity'   =>$result['so_qty']
                            ,'category_id'                  =>$result['category_id']
                            ,'product_group_id'             =>$result['product_group_id']
                            ,'product_price'                =>$is_zeroPrice ? $search_price : $result['product_price']
                            ,'product_min_price'            =>$is_zeroPrice ? 0 : $result['product_min_price']
                            ,'uom_name'                     =>isset($search_uom) ? $search_uom : $result['uom_name']
                            ,'product_brand'                =>$result['product_brand'] 
                            ,'image_url'                    =>''
                            ,'product_promo'                =>$result['product_promo']
                            ,'warehouse_stock'              =>$warehouse_stock
                            ,'search_filter'                =>isset($result['search_filter']) ? $result['search_filter'] : ''
                        );

                        if($is_zeroPrice){
                            $productDetail['price_tags'] = $price_tags;
                            $productDetail['search_filter'] = json_encode(
                                array(
                                    'name'=>$result['product_name'],
                                    'code'=>$result['product_code'],
                                    'subcategory'=>$result['product_remark'],
                                    'category'=>$result['category_name']
                                )
                            );
                        }
                        
                        $productArr[]       = $productDetail;
                    } 
                }
            }

            $db2->close();
            $json_data['productList']   = $productArr;

            break;
        }
        case 'price':{
            $productUOMPriceArr = array();

            $query = "select * from cms_product_uom_price_v2;";
            $db->query($query);

            while($result = $db->fetch_array()){
                $priceId = $result['product_uom_price_id'];

                if(!in_array($priceId,$dataFromDevice)){
		$ol = $client == 'knowledgewealth';
		if($ol){
			$check = strtolower(trim($result['product_uom'])) == 'ol';
            		if($check) continue;
		}
                    $productUOMPriceDetail = array(
                        'product_uom_price_id'      =>$result['product_uom_price_id']
                        ,'product_code'             =>$result['product_code']
                        ,'product_uom'              =>$result['product_uom']
                        ,'product_uom_rate'         =>$result['product_uom_rate']
                        ,'product_std_price'        =>$is_zeroPrice ? 0 : $result['product_std_price']
                        ,'product_min_price'        =>$is_zeroPrice ? 0 : $result['product_min_price']
                        ,'product_default_price'    =>$result['product_default_price']
                        ,'active_status'            =>$result['active_status']
                    );
                    $productUOMPriceArr[] = $productUOMPriceDetail;
                }
            }
            $json_data['productUOMProceList']   = $productUOMPriceArr;

            break;
        }
        case 'priceTag':{
            $productArr = array();

            if($client !== 'insidergroup'){
                $query = "select * from cms_product_price_v2;";
                $db->query($query);

                while($result = $db->fetch_array()){
                    $priceId = $result['product_price_id'];

                    if(!in_array($priceId,$dataFromDevice)){
                        $productDetail = array(
                            'product_price_id'      =>$result['product_price_id']
                            ,'product_code'         =>$result['product_code']
                            ,'price_cat'            =>$result['price_cat']
                            ,'cust_code'            =>$result['cust_code']
                            ,'product_price'        =>$result['product_price']
                            ,'disc_1'               =>$result['disc_1']
                            ,'disc_2'               =>$result['disc_2']
                            ,'disc_3'               =>$result['disc_3']
                            ,'date_from'            =>isDate($result['date_from'])
                            ,'date_to'              =>isDate($result['date_to'])
                            ,'product_uom'          =>$result['product_uom']
                            ,'quantity'             =>$result['quantity']
                            ,'price_remark'         =>$result['price_remark']
                            ,'active_status'        =>$result['active_status']
                        );
                        $productArr[] = $productDetail;
                    }
                }
            }
            
            $json_data['productPriceList']   = $productArr;

            break;
        }
        case 'image':{
            $productImageArr = array();

            $query = "SELECT `cms_product_image`.`product_image_id`, `cms_product_image`.`product_id`, 
                        `cms_product_image`.`image_url`, `cms_product_image`.`sequence_no`, 
                        `cms_product_image`.`product_default_image`, `cms_product_image`.`product_image_created_date`, 
                        `cms_product`.`product_id`, `cms_product`.`product_code`,`cms_product_image`.`active_status`				
                        FROM `cms_product_image`, `cms_product` 
                        WHERE `cms_product_image`.`product_id` = `cms_product`.`product_id`;";
            $db->query($query);

            while($result = $db->fetch_array()){
                $imageId = $result['product_image_id'];

                if(!in_array($imageId,$dataFromDevice)){
                    $productImageDetails = array(
                        'product_image_id'  =>$result['product_image_id']
                        ,'product_id'       =>$result['product_id']
                        ,'image_url'        =>str_replace('http','https',str_replace('https','http',$result['image_url']))
                        ,'sequence_no'      =>$result['sequence_no']
                        ,'product_default_image'        =>$result['product_default_image']
                        ,'active_status'    =>$result['active_status']
                        ,'product_image_created_date'   =>$result['product_image_created_date']
                        ,'product_code'     =>$result['product_code']
                    );
                    
                    $productImageArr[] = $productImageDetails; 
                }
            }
            $json_data['productImageList'] = $productImageArr;
            break;
        }
        case 'category':{
            $categoryArr = array();

            $query = "select * from cms_product_category;";
            if($client == "chiapsheng"){
                $query = "select * from cms_product_category where categoryIdentifierId = 'CONSIGN';";
            }
            $db->query($query);

            while($result = $db->fetch_array()){
                $categoryId = $result['category_id'];

                if(!in_array($categoryId,$dataFromDevice)){
                    $categoryDetail = array(
                        'category_id'           =>$result['category_id']
                        ,'category_name'        =>$result['category_name']
                        ,'parent_category_id'   =>$result['parent_category_id']
                        ,'sequence_no'          =>$result['sequence_no']
                        ,'category_status'      =>$result['category_status']
                        ,'category_image_url'   =>$result['category_image_url']
                    );
                    
                    $categoryArr[] = $categoryDetail; 
                }
            }
            $json_data['categoryList'] = $categoryArr;

            break;
        }
        case 'outso':{
            $outstanding = array();

            $query = "SELECT * FROM cms_outstanding_so WHERE so_salesperson_id = '{$salesperson_id}';";
            $db->query($query);
            
            while($result = $db->fetch_array()){
                $docNo = $result['so_docno'];
                if(!in_array($docNo,$dataFromDevice)){
                    $soDetail = array(
                        "so_docno"          => $result['so_docno'],
                        "so_dockey"         => $result['so_dockey'],
                        "so_product_code"   => $result['so_product_code'],
                        "so_ori_qty"        => $result['so_ori_qty'],
                        "so_out_qty"        => $result['so_out_qty'],
                        "so_trans_qty"      => $result['so_trans_qty'],
                        "so_doc_date"       => explode(" ",$result['so_doc_date'])[0],
                        "so_salesperson_id" => $result['so_salesperson_id'],
                        "so_cust_code"      => $result['so_cust_code']
                    );
                    $outstanding[] = $soDetail;
                }
            }
            $json_data['outstandingList'] =  $outstanding;
            break;
        }
        case 'purchasePrice':{
            $productArr = array();

            $query = "SELECT * FROM cms_product_purchase_price";
            $db->query($query);
            
            while($result = $db->fetch_array()){
                $prId = $result['pr_id'];
                if(!in_array($prId,$dataFromDevice)){
                    $productArr[] = $result;
                }
            }
            $json_data['productList']   = $productArr;
            break;
        }
        default:
            # code...
            break;
    }
    $db->close();
    echo json_encode($json_data,JSON_UNESCAPED_UNICODE);
}
function isDate($val){
    $zero = '0000-00-00 00:00:00';
    if($val == $zero){
        return '';
    }
    return $val;
}

?>