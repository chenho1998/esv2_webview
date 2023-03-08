<?php 
$mysql = mysqli_connect('117.53.154.68','easyicec_bigbathlef','easyicec_bigbathlef123@','easyicec_bigbathlef');
$postData = array();

$ordersQuery = mysqli_query($mysql,"SELECT *, date(delivery_date) as deliveryDate FROM cms_order WHERE order_status = 1 AND pack_confirmed = 1");
while($row = mysqli_fetch_array($ordersQuery)){
    $orders = array();

    $order_id = $row['order_id'];
    
    $orderItemQuery = mysqli_query($mysql,"SELECT * FROM cms_order_item WHERE order_id = '{$order_id}' AND pack_confirmed_status = 1 AND editted_quantity < packed_qty AND cancel_status = 0 AND packed_qty <> 0;");

    $itemCount = mysqli_num_rows($orderItemQuery);

    if($itemCount > 0){
        $orders = array(
            'cardCode'=> $row['cust_code'],
            'deliveryDate' => $row['deliveryDate'],
            'remarks' => 'FROM ES',
            'order_id'=>$order_id,
            'items' => array()
        );
        while($itemrow = mysqli_fetch_array($orderItemQuery)){
            $delivery_count = intval($itemrow['packed_qty']);
            if($delivery_count)
            $orders['items'][] = array(
                'cardCode'=>$row['cust_code'],
                'itemCode'=>$itemrow['product_code'],
                'warehouse_Code'=>$itemrow['warehouse_code'],
                'docEntry'=>$itemrow['doc_entry'],
                'salesOrder_LineNum'=>$itemrow['sequence_no'],
                'deliver_Quantity'=>$itemrow['packed_qty']
            );
        }
        $postData[] = $orders;
    }
}

echo json_encode($postData);
?>