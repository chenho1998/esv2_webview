<?php 
ini_set('max_execution_time',180000);
ini_set('post_max_size',204800);
ini_set('memory_limit',1000000);
ini_set('upload_max_filesize',100000);
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $data = file_get_contents('php://input');
    $data = json_decode($data,true);

    file_put_contents('bigbath.log',json_encode($data));

    $mysql = mysqli_connect('117.53.154.68','easyicec_bigbathlef','easyicec_bigbathlef123@','easyicec_bigbathlef');

    $sql_product_image = array();
    $product_codes = array();
    $sql_customer_salesperson = array();
    $customer_codes = array();

    $sql_order_salesperson = array();

    $cms_login = "INSERT INTO cms_login (staff_code,login,password,login_status) VALUES ";
    $cms_login_update = " ON DUPLICATE KEY UPDATE password = VALUES(password);";

    $cms_customer = "INSERT INTO cms_customer (cust_code,cust_company_name,created_date,cust_incharge_person,cust_reference,cust_tel,cust_fax,billing_address1,shipping_address1,billing_city,shipping_city,billing_state,shipping_state,billing_country,shipping_country, cust_tel, cust_fax) VALUES ";
    $cms_customer_update = " ON DUPLICATE KEY UPDATE cust_company_name = VALUES(cust_company_name), cust_reference = VALUES(cust_reference), cust_tel = VALUES(cust_tel), cust_fax = VALUES(cust_fax);";

    $cms_customer_salesperson = "INSERT INTO cms_customer_salesperson (salesperson_id, customer_id) VALUES ";
    $cms_customer_salesperson_update = " ON DUPLICATE KEY UPDATE salesperson_id = VALUES(salesperson_id)";

    $cms_product = "INSERT INTO cms_product (product_code,product_name,product_remark,product_current_quantity) VALUES ";
    $cms_product_update = " ON DUPLICATE KEY UPDATE product_name = VALUES(product_name),product_remark=VALUES(product_remark);";

    $cms_product_uom_price = "INSERT INTO cms_product_uom_price_v2 (product_code, product_uom, active_status, product_std_price, product_default_price) VALUES ";
    $cms_product_uom_price_update = " ON DUPLICATE KEY UPDATE product_std_price = VALUES(product_std_price);";

    $cms_order = "INSERT INTO cms_order (order_id,cust_code,cust_company_name,cust_reference,cust_tel,cust_fax,delivery_date,order_reference,order_delivery_note,billing_address1,shipping_address1,billing_city,shipping_city,billing_state,shipping_state,billing_country,shipping_country,order_date,internal_updated_at,cancel_status,packing_status,order_status,order_remark) VALUES ";
    $cms_order_update = " ON DUPLICATE KEY UPDATE cancel_status = VALUES(cancel_status), delivery_date = VALUES(delivery_date),order_status=VALUES(order_status), cust_reference = VALUES(cust_reference), cust_tel = VALUES(cust_tel), cust_fax = VALUES(cust_fax),order_remark=VALUES(order_remark),packing_status = IF(cms_order.packing_status = -1,cms_order.packing_status,VALUES(packing_status));";

    $cms_order_item = "INSERT INTO cms_order_item (ipad_item_id,order_id,product_code,product_name,attribute_remark,optional_remark,quantity,unit_uom,unit_price,salesperson_remark, editted_quantity, isParent, parent_code,packing_status, warehouse_code, doc_entry, sequence_no) VALUES ";
    $cms_order_item_update = " ON DUPLICATE KEY UPDATE editted_quantity = VALUES(editted_quantity), quantity = VALUES(quantity), isParent = VALUES(isParent), parent_code = VALUES(parent_code),packing_status=VALUES(packing_status),attribute_remark=VALUES(attribute_remark), warehouse_code = VALUES(warehouse_code), doc_entry = VALUES(doc_entry), sequence_no = VALUES(sequence_no);";

    $cms_warehouse = "INSERT INTO cms_warehouse (wh_name, wh_code, wh_status) VALUES ";
    $cms_warehouse_update = " ON DUPLICATE KEY UPDATE wh_name = VALUES(wh_name);";

    $cms_warehouse_qty = "INSERT INTO cms_warehouse_stock (product_code, wh_code, ready_st_qty, available_st_qty, uom_name, active_status) VALUES ";
    $cms_warehouse_qty_update = " ON DUPLICATE KEY UPDATE ready_st_qty = VALUES(ready_st_qty), available_st_qty = VALUES(available_st_qty)";

    $cms_product_image = "INSERT INTO cms_product_image (product_id, image_url, product_default_image, active_status, product_image_created_date) VALUES ";
    $cms_product_image_update = " ON DUPLICATE KEY UPDATE image_url = VALUES(image_url)";

    $cms_order_salesperson = "INSERT INTO cms_order (order_id, salesperson_id) VALUES ";
    $cms_order_salesperson_update = " ON DUPLICATE KEY UPDATE salesperson_id = VALUES(salesperson_id)";

    $arr_login = array();
    $arr_customer = array();
    $arr_product = array();
    $arr_order = array();
    $arr_order_item = array();
    $arr_warehouse = array();
    $arr_product_price = array();
    $arr_warehouse_info = array();

    $arr_customer_salesperson = array();
    $arr_product_image = array();
    $arr_order_salesperson = array();

    for ($i=0; $i < count($data); $i++) { 
        $order = $data[$i];

        $order_items = $order['items'];
        $current_date = date('Y-m-d h:i:s');

        $order['staff_ID'] = mysqli_real_escape_string($mysql,$order['staff_ID']);
        $order['customer_Code'] = mysqli_real_escape_string($mysql,$order['customer_Code']);
        $order['customr_Name'] = mysqli_real_escape_string($mysql,$order['customr_Name']);
        $order['shipp_Address'] = mysqli_real_escape_string($mysql,strip($order['shipp_Address']));
        $order['salesOrder_reference'] = mysqli_real_escape_string($mysql,$order['salesOrder_reference']);
        $order['salesOrder_Remark'] = mysqli_real_escape_string($mysql,strip($order['salesOrder_Remark']));
        $order['attn'] = mysqli_real_escape_string($mysql,$order['attn']);
        $order['tel'] = mysqli_real_escape_string($mysql,$order['tel']);
        $order['fax'] = mysqli_real_escape_string($mysql,$order['fax']);
        $order['end_Cust_Name'] = mysqli_real_escape_string($mysql,$order['end_Cust_Name']);
        $order['end_Cust_Tel'] = mysqli_real_escape_string($mysql,$order['end_Cust_Tel']);
        $order['city'] = mysqli_real_escape_string($mysql,$order['city']);
        $order['state'] = mysqli_real_escape_string($mysql,$order['state']);
        $order['country'] = mysqli_real_escape_string($mysql,$order['country']);

        $order['end_Cust_Name'] = explode('|',$order['end_Cust_Name'])[0];

        $order_remark = $order['end_Cust_Name'] . ' | ' . $order['end_Cust_Tel'];
        $order_remark = mysqli_real_escape_string($mysql,$order_remark);

        $sql_order_salesperson[$order['salesOrder_id']] = $order['staff_ID'];

        $customer_codes[] = "'".$order['customer_Code']."'";

        $order_cancel = $order['isWriteoff'] == 'N' ? 0 : 1;

        $arr_login[] = "('{$order['staff_ID']}','{$order['staff_ID']}','bblf123',1)";

        $arr_customer[] = "('{$order['customer_Code']}','{$order['customr_Name']}',NOW(),'{$order['staff_ID']}','{$order['attn']}','{$order['tel']}','{$order['fax']}','{$order['shipp_Address']}','{$order['shipp_Address']}','{$order['city']}','{$order['city']}','{$order['state']}','{$order['state']}','{$order['country']}','{$order['country']}')";

        $sql_customer_salesperson[$order['customer_Code']] = $order['staff_ID'];

        $pickedCounter = 0;

        for ($j=0; $j < count($order_items); $j++) { 

            $item = $order_items[$j];
            $item['itemId'] = mysqli_real_escape_string($mysql,$item['itemId']);
            $item['item_Name'] = mysqli_real_escape_string($mysql,$item['item_Name']);
            $item['item_Reference'] = mysqli_real_escape_string($mysql,$item['item_Reference']);
            $item['uoM'] = mysqli_real_escape_string($mysql,$item['uoM']);
            $item['item_Remark'] = mysqli_real_escape_string($mysql,$item['item_Remark']);
            $item['additional_Description'] = mysqli_real_escape_string($mysql,$item['additional_Description']);
            $item['warehouse_Code'] = mysqli_real_escape_string($mysql,$item['warehouse_Code']);

            if($item['item_Image_url']){
                $product_codes[] = "'".$item['itemId']."'";
            }

            $item['item_Image_url'] = mysqli_real_escape_string($mysql,basename($item['item_Image_url']));
            $item['item_Image_url'] = "https://easyecosystem.com/client_image/bigbathlef/".$item['item_Image_url'];

            $sql_product_image[$item['itemId']] = $item['item_Image_url'];
            
            $arr_product [] = "('{$item['itemId']}','{$item['item_Name']}','{$item['item_Reference']}','{$item['avail_Qty']}')";
            
            $arr_product_price[] = "('{$item['itemId']}','{$item['uoM']}',1,'{$item['unit_Price']}',1)";
            
            $warehouse = $item['onhand_by_Warehouse'];
            for ($w=0; $w < count($warehouse); $w++) { 
                $wh = $warehouse[$w];
                $arr_warehouse[] = "('{$item['itemId']}','{$wh['warehouse_Code']}','{$wh['onhand_quantity']}','{$wh['onhand_quantity']}','{$item['uoM']}',1)";
                $arr_warehouse_info[] = "('{$wh['warehouse_Code']}','{$wh['warehouse_Code']}',1)";
            }

            $ipad_item_id = floatval($item['docEntry'].''.$item['salesOrder_LineNum']);

            $packing_status = 0;
            if($item['shipped_Qty'] == $item['quantity']){
                $packing_status = 1;
            }
            if($packing_status == 1){
                $pickedCounter++;
            }
            
            $isParent = $item['isPackage'] == 'Y' ? 1 : 0;
            $parent_code = $isParent ? 'PACKAGE' : '';
            //warehouse_code, doc_entry, sequence_no
            $arr_order_item[] = "('{$ipad_item_id}','{$item['salesOrder_id']}','{$item['itemId']}','{$item['item_Name']}','{$item['additional_Description']}','{$item['item_Reference']}','{$item['quantity']}','{$item['uoM']}','{$item['unit_Price']}','{$item['item_Remark']}','{$item['shipped_Qty']}','{$isParent}','{$parent_code}','{$packing_status}','{$item['warehouse_Code']}','{$item['docEntry']}','{$item['salesOrder_LineNum']}')";

            if($item['isPackage'] == 'Y'){
                $packages = $item['packages'];
                for ($p=0; $p < count($packages); $p++) { 
                    $pkg_item = $packages[$p];
                    $pkg_ipad_item_id = $ipad_item_id . '' . ($p + 1);
                    $arr_order_item[] = "('{$pkg_ipad_item_id}','{$item['salesOrder_id']}','{$pkg_item['package_Code']}','{$pkg_item['package_Name']}','','','{$pkg_item['base_Qty']}','','0','','0','0','{$item['itemId']}','{$packing_status}','{$item['warehouse_Code']}','0','0')";
                }
            }
        }
        
        $order_packing_status_otherwise = $pickedCounter > 0 ? -1 : 0;
        $order_packing_status = count($order_items) == $pickedCounter ? 1 : $order_packing_status_otherwise;
        $order_status = $order_packing_status == 1 ? 2 : 1;

        $arr_order[] = "('{$order['salesOrder_id']}','{$order['customer_Code']}','{$order['customr_Name']}','{$order['attn']}','{$order['tel']}','{$order['fax']}','{$order['target_Delivery_date']}','{$order['salesOrder_reference']}','{$order['salesOrder_Remark']}','{$order['shipp_Address']}','{$order['shipp_Address']}','{$order['city']}','{$order['city']}','{$order['state']}','{$order['state']}','{$order['country']}','{$order['country']}','{$order['created_Date']}','{$order['modified_Date']}','{$order_cancel}','{$order_packing_status}','{$order_status}','{$order_remark}')";
    }

    $query = $cms_login . implode(',',$arr_login) . $cms_login_update;
        mysqli_query($mysql,$query);

    file_put_contents('cms_login.log',$query);
    
    $query = $cms_customer . implode(',',$arr_customer) . $cms_customer_update;
        mysqli_query($mysql,$query);

    file_put_contents('cms_customer.log',$query);
    
    $query = $cms_product . implode(',',$arr_product) . $cms_product_update;
        mysqli_query($mysql,$query);
    
    file_put_contents('cms_product.log',$query);

    $query = $cms_product_uom_price . implode(',',$arr_product_price) . $cms_product_uom_price_update;
        mysqli_query($mysql,$query);

    file_put_contents('cms_product_uom_price.log',$query);

    $query = $cms_order . implode(',',$arr_order) . $cms_order_update;
        mysqli_query($mysql,$query);

    file_put_contents('cms_order.log',$query);
    
    $query = $cms_order_item . implode(',',$arr_order_item) . $cms_order_item_update;
        mysqli_query($mysql,$query);

    file_put_contents('cms_order_item.log',$query.';');

    $query = $cms_warehouse_qty . implode(',',$arr_warehouse) . $cms_warehouse_qty_update;
        mysqli_query($mysql,$query);

    file_put_contents('cms_warehouse_qty.log',$query);

    $query = $cms_warehouse . implode(',',$arr_warehouse_info) . $cms_warehouse_update;
        mysqli_query($mysql,$query);

    file_put_contents('cms_warehouse.log',$query);

    $product_codes = "(" . implode(",",$product_codes) . ")";
    $customer_codes = "(" . implode(",",$customer_codes) . ")";

    $cms_login_res = array();
    $login = mysqli_query($mysql,"SELECT login_id, staff_code FROM cms_login");
    while($row = mysqli_fetch_array($login)){
        $cms_login_res[$row['staff_code']] = $row['login_id']; 
    }

    $_products = mysqli_query($mysql,"SELECT product_code, product_id FROM cms_product WHERE product_code IN {$product_codes}");
    while($row = mysqli_fetch_array($_products)){
        $__product_code = $row['product_code'];
        $product_id = $row['product_id'];
        if(isset($sql_product_image[$__product_code])){
            $image_url = $sql_product_image[$__product_code];
            $arr_product_image[] = "('{$product_id}','{$image_url}','1','1',NOW())";
        }
    }
    
    $_customers = mysqli_query($mysql,"SELECT cust_code, cust_id FROM cms_customer WHERE cust_code IN {$customer_codes}");
    while($row = mysqli_fetch_array($_customers)){
        $_custcode = $row['cust_code'];
        if(isset($sql_customer_salesperson[$_custcode])){
            $staff_code = $sql_customer_salesperson[$_custcode];
            $login_id = $cms_login_res[$staff_code];
            $arr_customer_salesperson[] = "('{$login_id}','{$row['cust_id']}')";

            $order_id = $sql_order_salesperson[$staff_code];

            $arr_order_salesperson[] = "('{$order_id}','{$login_id}')";
        }
    }

    $query = $cms_product_image . implode(',',$arr_product_image) . $cms_product_image_update;
        mysqli_query($mysql,$query);
    file_put_contents('cms_product_image.log',$query);

    $query = $cms_order_salesperson . implode(',',$arr_order_salesperson) . $cms_order_salesperson_update;
        mysqli_query($mysql,"insert into cms_order (order_id, salesperson_id)
        select order_id, sp.salesperson_id from cms_order o
        left join cms_customer c on c.cust_code = o.cust_code
        left join cms_customer_salesperson sp on sp.customer_id = c.cust_id
        on duplicate key update salesperson_id = values(salesperson_id);");

    file_put_contents('cms_order_salesperson.log',$query);
    
    $query = $cms_customer_salesperson . implode(',',$arr_customer_salesperson) . $cms_customer_salesperson_update;
        mysqli_query($mysql,$query);

    file_put_contents('cms_customer_salesperson.log',$query);

    mysqli_query($mysql,"UPDATE cms_order SET order_status = 2 AND packing_status = 1 WHERE order_id IN (SELECT order_id FROM (
        SELECT COUNT(*) total, SUM(IF(quantity = editted_quantity,1,0)) picked,order_id FROM cms_order_item WHERE isParent = 1 GROUP BY order_id
        ) d WHERE d.total = d.picked;)");
}
function strip($str){
    return str_ireplace(array("\r","\n",'\r','\n'),'', $str);
}
?>