<html>

<body>

  <h1>iManage Web Service</h1>

  <style type="text/css">
    #txt1 {
      height: 175px;
      width: 385px;
    }

    #txt2 {
      height: 175px;
      width: 385px;
    }
  </style>

  <!-- start of function -->

  <b>
    <strike>1(a). User Login:</b>
  </strike>
  <br>
  <strike>GET['action'] = "getUserLogin"</strike>
  <br>
  <strike>POST['data'] = { "getUserLogin_data": { "userID":"test", "password":"12" } }
  </strike>
  <br />
  <?php 

echo 
	
'<form action="./api.php?action=getUserLogin" method="post" name="form1">
<textarea name="data" id="txt1" type="text" value=""></textarea><br>
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->

  <!-- start of function -->

  <b>1(b). Test Connection (v2):</b>
  <br> GET['action'] = "testConnection_v2"
  <br> POST['data'] = { "testConnectionv2_data": { "login_id":"67", "device_token":"asdsda" } }
  <br />
  <?php 

echo 
  
'<form action="./api.php?action=testConnection_v2" method="post" name="form1">
<textarea name="data" id="txt1" type="text" value=""></textarea><br>
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->

  <!-- start of function -->

  <b>2. Login:</b>
  <br> GET['action'] = "getSalespersonCredential"
  <br>

  <?php 

echo 
	
'<form action="./api.php?action=getSalespersonCredential" method="post" name="form1">
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->

  <!-- start of function -->

  <b>3. Product Details:</b>
  <br> GET['action'] = "getStockQuantity"
  <br> POST['data'] = { "getStockQuantity_data": { "ProductID":123 } }

  <br />

  <?php 

echo 
	
'<form action="./api.php?action=getStockQuantity" method="post" name="form1">
<textarea name="data" id="txt1" type="text" value=""></textarea><br>
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->

  <!-- start of function -->
  <b>4. Order (OLD):</b>
  <br> GET['action'] = "transferOrder"
  <br> POST['data'] = { "transferOrder_data": { "order_id":"order001", "order_date":"2014-04-02", "transfer_received_date":"2014-04-02",
  "total_discount":"1", "discount_method":"percent", "tax":"100.00", "shippingfee":"10", "grand_total":"20", "cust_id":"12",
  "cust_code":"1212", "cust_company_name":"michael", "cust_incharge_person":"johnathan", "cust_reference":"cusref0909", "cust_email":"some1@gmail.com",
  "cust_tel":"05-190909090", "cust_fax":"05-190909092", "billing_address1":"billing address 1", "billing_address2":"billing
  address 2", "billing_address3":"billing address 3", "billing_city":"petaling jaya", "billing_state":"persekutuan", "billing_zipcode":"22302",
  "billing_country":"malaysia", "shipping_address1":"shipping address 1", "shipping_address2":"shipping address 2", "shipping_address3":"shipping
  address 3", "shipping_city":"labuan", "shipping_state":"labuan", "shipping_zipcode":"12120", "shipping_country":"malaysia",
  "salesperson_id":"12", "order_status":"1", "payment_received_date":"2014-04-02", "order_remark":"this is remark", "Payment_received_by":"oracle",
  "OrderitemList":[{"order_id":"ref1234", "product_id":"12", "salesperson_remark":"new order", "quantity":"23", "unit_price":"12.50",
  "unit_uom":"meter", "uom_id":"1", "attribute_remark":"no remark", "optional_remark": "no remark", "discount_method":"no
  discount", "discount_amount":"0", "sub_total":"200", "sequence_no":"1"}, {"order_id":"ref2234", "product_id":"19", "salesperson_remark":"new
  order 2", "quantity":"23", "unit_price":"18.00", "unit_uom":"meter", "uom_id":"1", "attribute_remark":"no remark", "optional_remark":
  "no remark", "discount_method":"no discount", "discount_amount":"0", "sub_total":"200", "sequence_no":"2"}] } }

  <br />

  <?php 

echo 
  
'<form action="./api.php?action=transferOrder" method="post" name="form1">
<textarea name="data" id="txt1" type="text" value=""></textarea><br>
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />


  <b>4. Order (New):</b>
  <br> !!!! 6 September update: added cancel_status in transferOrder_data and OrderItemList !!!!
  <br> !!!! cancel_status = 0 => active, cancel_status = 1 => deleted/removed !!!!
  <br> GET['action'] = "transferOrder_v2"
  <br> POST['data'] = { "transferOrder_data": { "order_id":"order001", "order_date":"2017-08-09", "transfer_received_date":"2017-08-09",
  <span style=" background-color: yellow;">"delivery_date":"2018-01-01"</span>, "total_discount":"0", "discount_method":" ", "tax":"0", "shippingfee":"0", "grand_total":"20","gst_amount":"20",
  "cust_id":"1", "cust_code":"SHDB", "cust_company_name":"michael", "cust_incharge_person":"johnathan", "cust_reference":"cusref0909",
  "cust_email":"some1@gmail.com", "cust_tel":"05-190909090", "cust_fax":"05-190909092", "billing_address1":"billing address
  1", "billing_address2":"billing address 2", "billing_address3":"billing address 3", "billing_address4":"billing address
  4", "billing_city":"petaling jaya", "billing_state":"persekutuan", "billing_zipcode":"22302", "billing_country":"malaysia",
  "shipping_address1":"shipping address 1", "shipping_address2":"shipping address 2", "shipping_address3":"shipping address
  3","shipping_address4":"shipping address 4", "shipping_city":"labuan", "shipping_state":"labuan", "shipping_zipcode":"12120",
  "shipping_country":"malaysia","termcode":"CASH", "salesperson_id":"16", "order_status":"0", "order_remark":"this is remark",
  "order_delivery_note":"this is delivery note", "order_status_last_update_date":"2017-08-09","order_status_last_update_by":"salesagent",
  "cancel_status":"0","OrderitemList":[{"order_id":"order001", "product_id":"10","order_item_id":"1","product_code":"L 1100
  ( A )","product_name":"NKRV 1100 LCD ( A )", "salesperson_remark":"new order", "quantity":"20", "unit_price":"12",
  <span style=" background-color: yellow;">"disc_1":"40", "disc_2" : "10", "disc_3" : "20" </span>, "unit_uom":"meter", "uom_id":"1", "attribute_remark":"no remark",
  "optional_remark": "no remark", "discount_method":"no discount", "discount_amount":"0", "sub_total":"240", "sequence_no":"1",
  "cancel_status":"0"}, {"order_id":"order001", "product_id":"11","order_item_id":"2","product_code":"L 1100 ( O )","product_name":"NKRV
  1100 LCD ( O )", "salesperson_remark":"new order 2", "quantity":"22", "unit_price":"18.00",
  <span style=" background-color: yellow;">"disc_1":"40", "disc_2" : "50", "disc_3" : "50" </span>, "unit_uom":"meter", "uom_id":"1", "attribute_remark":"no remark",
  "optional_remark": "no remark", "discount_method":"no discount", "discount_amount":"0", "sub_total":"396", "sequence_no":"2","cancel_status":"0"},{"order_id":"order001",
  "product_id":"12","order_item_id":"3","product_code":"L 6260 ( O )","product_name":"NKRV 6260 LCD ( O )", "salesperson_remark":"new
  order 2", "quantity":"10", "unit_price":"10.00",
  <span style=" background-color: yellow;">"disc_1":"50", "disc_2" : "50", "disc_3" : "50" </span>, "unit_uom":"meter", "uom_id":"1", "attribute_remark":"no remark",
  "optional_remark": "no remark", "discount_method":"no discount", "discount_amount":"0", "sub_total":"100", "sequence_no":"3",
  "cancel_status":"0"},{"order_id":"order001", "product_id":"13","order_item_id":"4","product_code":"L 1100F ( A )","product_name":"NKRV
  1100 F/SET ", "salesperson_remark":"new order 2", "quantity":"10", "unit_price":"10.00",
  <span style=" background-color: yellow;">"disc_1":"40", "disc_2" : "10", "disc_3" : "20" </span>, "unit_uom":"meter", "uom_id":"1", "attribute_remark":"no remark",
  "optional_remark": "no remark", "discount_method":"no discount", "discount_amount":"0", "sub_total":"100", "sequence_no":"4",
  "cancel_status":"0"}] } }

  <br />

  <?php 

echo 
	
'<form action="./api.php?action=transferOrder_v2" method="post" name="form1">
<textarea name="data" id="txt1" type="text" value=""></textarea><br>
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->


  <!-- start of function -->

  <b>5. Order (Transferred): When salesperson search for order </b>
  <br> GET['action'] = "searchTransferredOrder"
  <br> POST['data'] = { "searchTransferredOrder_data": { "salespersonid":"123", "created_start_date":"2014-04-02 00:00:00", "created_end_date":"2014-04-02
  00:00:00", "customer_name":"nani", "order_status":"1" } }

  <br />

  <?php 

echo 
	
'<form action="./api.php?action=searchTransferredOrder" method="post" name="form1">
<textarea name="data" id="txt1" type="text" value=""></textarea><br>
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->

  <!-- start of function -->

  <b>
    <strike>6. Order (Transferred): When salesperson click on "Pay" button </strike>
  </b>
  <br>
  <strike>GET['action'] = "editOrderPaymentDetails"</strike>
  <br>
  <strike>POST['data'] = { "editOrderPaymentDetails_data": { "salespersonid":"123", "orderId":"90" } }
  </strike>

  <br />

  <?php 

echo 
	
'<form action="./api.php?action=editOrderPaymentDetails" method="post" name="form1">
<textarea name="data" id="txt1" type="text" value=""></textarea><br>
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->


  <!-- start of function -->

  <b>7. Sync:</b>
  <br> GET['action'] = "getLastUpdateDate"
  <br> POST['data'] = { "getLastUpdateDate_data": { "salespersonid":"123" } }

  <?php 

echo 
	
'<form action="./api.php?action=getLastUpdateDate" method="post" name="form1">
<textarea name="data" id="txt1" type="text" value=""></textarea><br>
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->

  <!-- start of function -->

  <b>8. Sync (Customer): </b>
  <br> GET['action'] = "getCustomerList"
  <br> POST['data'] = { "getCustomerList_data": { "salespersonid":"123", "date":"2017-08-08" } }

  <br />

  <?php 

echo 
	
'<form action="./api.php?action=getCustomerList" method="post" name="form1">
<textarea name="data" id="txt1" type="text" value=""></textarea><br>
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->


  <!-- start of function -->

  <b>9. Sync (Product):</b>
  <br> GET['action'] = "getProductList"
  <br>

  <?php 

echo 
	
'<form action="./api.php?action=getProductList" method="post" name="form1">
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->


  <!-- start of function -->

  <b>10. Sync (Back up selling price):</b>
  <br> GET['action'] = "insertSellingPrice"
  <br> POST['data'] = { "insertSellingPrice_data": { "lastSellingPriceList":[{"salesperson_id":"12", "product_id":"100", "product_unit_price":"20",
  <span style=" background-color: yellow;">"product_disc_1":"40", "product_disc_2" : "10", "product_disc_3" : "20" </span>, "uom_id":"5", "cust_id":"10","quantity":"5",
  "discount":"10", "discount_method":"percenttype","last_selling_date":"2017-10-14"}, {"salesperson_id":"19", "product_id":"10",
  "product_unit_price":"90",
  <span style=" background-color: yellow;">"product_disc_1":"50", "product_disc_2" : "50", "product_disc_3" : "50" </span>, "uom_id":"3", "cust_id":"10", "quantity":"6","discount":"15",
  "discount_method":"percenttype","last_selling_date":"2017-10-14"}] } }

  <?php 

echo 
	
'<form action="./api.php?action=insertSellingPrice" method="post" name="form1">
<textarea name="data" id="txt1" type="text" value=""></textarea><br>
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->


  <!-- start of function -->

  <b>11. Sync (Restore selling price): </b>
  <br> GET['action'] = "getSellingPrice"
  <br> POST['data'] = { "getSellingPrice_data": { "salespersonid":"123" } }

  <br />

  <?php 

echo 
	
'<form action="./api.php?action=getSellingPrice" method="post" name="form1">
<textarea name="data" id="txt1" type="text" value=""></textarea><br>
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->


  <!-- start of function -->

  <b>12. Sync (General Info):</b>
  <br> GET['action'] = "getGeneralInfo"
  <br>

  <?php 

echo 
	
'<form action="./api.php?action=getGeneralInfo" method="post" name="form1">
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->


  <!-- start of function -->

  <b>13. Sync (SalesPersonCustomer):</b>
  <br> GET['action'] = "getSalesPersonCustomerlist"
  <br> POST['data'] = { "getSalesPersonCustomerlist_data": { "salespersonid":"123", "date":"2017-08-08" } }

  <?php 

echo 
	
'<form action="./api.php?action=getSalesPersonCustomerlist" method="post" name="form1">
<textarea name="data" id="txt1" type="text" value=""></textarea><br>
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->

  <!-- start of function -->

  <b>14. Get All Category:</b>
  <br> GET['action'] = "getAllCategory"
  <br> POST['data'] = { "date":"2017-08-08" }

  <?php 

echo 
	
'<form action="./api.php?action=getAllCategory" method="post" name="form1">
<textarea name="data" id="txt1" type="text" value=""></textarea><br>
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->

  <!-- start of function -->

  <b>15. Get All Product:</b>
  <br> GET['action'] = "getAllProduct"
  <br> POST['data'] = { "date":"2017-08-08" }

  <?php 

echo 
	
'<form action="./api.php?action=getAllProduct" method="post" name="form1">
<textarea name="data" id="txt1" type="text" value=""></textarea><br>
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->


  <!-- start of function -->

  <b>16. Get All Product Attribute:</b>
  <br> GET['action'] = "getAllProductAttribute"
  <br>

  <?php 

echo 
	
'<form action="./api.php?action=getAllProductAttribute" method="post" name="form1">
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->

  <!-- start of function -->

  <b>17. Get All Product Image:</b>
  <br> GET['action'] = "getAllProductImage"
  <br> POST['data'] = { "date":"2017-08-08" }

  <?php 

echo 
	
'<form action="./api.php?action=getAllProductImage" method="post" name="form1">
<textarea name="data" id="txt1" type="text" value=""></textarea><br>
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->

  <!-- start of function -->

  <b>18. Get All Product Optional Item:</b>
  <br> GET['action'] = "getAllProductOptionalItem"
  <br>

  <?php 

echo 
	
'<form action="./api.php?action=getAllProductOptionalItem" method="post" name="form1">
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->

  <!-- start of function -->

  <b>19. Get All Product UOM Price:</b>
  <br> GET['action'] = "getAllProductUOMPrice"
  <br> POST['data'] = { "date":"2017-08-08" }

  <?php 

echo 
	
'<form action="./api.php?action=getAllProductUOMPrice" method="post" name="form1">
<textarea name="data" id="txt1" type="text" value=""></textarea><br>
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->

  <!-- start of function -->

  <b>20. Get All UOM:</b>
  <br> GET['action'] = "getAllUOM"
  <br> POST['data'] = { "date":"2017-08-08" }

  <?php 

echo 
	
'<form action="./api.php?action=getAllUOM" method="post" name="form1">
<textarea name="data" id="txt1" type="text" value=""></textarea><br>
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->

  <!-- start of function -->

  <b>20b. Get All Special Price:</b>
  <br> GET['action'] = "getAllSpecialPrice"
  <br> POST['data'] = { "date":"2017-08-08" }

  <?php 

echo 
  
'<form action="./api.php?action=getAllSpecialPrice" method="post" name="form1">
<textarea name="data" id="txt1" type="text" value=""></textarea><br>
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->

  <!-- start of function -->

  <b>21. Get All Optional Item:</b>
  <br> GET['action'] = "getAllOptionalItem"
  <br>

  <?php 

echo 
	
'<form action="./api.php?action=getAllOptionalItem" method="post" name="form1">
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->

  <!-- start of function -->

  <b>22. Get All Document:</b>
  <br> GET['action'] = "getAllDocument"
  <br>

  <?php 

echo 
	
'<form action="./api.php?action=getAllDocument" method="post" name="form22">
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->

  <!-- start of function -->

  <b>23. Get All Video:</b>
  <br> GET['action'] = "getAllVideo"
  <br>

  <?php 

echo 
	
'<form action="./api.php?action=getAllVideo" method="post" name="form23">
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->

  <!-- start of function -->

  <b>24. Get All Order Status:</b>
  <br> GET['action'] = "getAllOrderStatus"
  <br>

  <?php 

echo 
	
'<form action="./api.php?action=getAllOrderStatus" method="post" name="form24">
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->

  <!-- start of function -->

  <b>25. Change order status</b>
  <br> GET['action'] = "changeOrderStatus"
  <br> POST['data'] = { "changeOrderStatus_data": { "salespersonid":"123", "order_status":"1", "others_order_status":"sample
  text here", "orderId":"90" } }

  <br />

  <?php 

echo 
	
'<form action="./api.php?action=changeOrderStatus" method="post" name="form25">
<textarea name="data" id="txt1" type="text" value=""></textarea><br>
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->

  <!-- start of function -->

  <b>26. Get All Journal:</b>
  <br> GET['action'] = "getAllJournal"
  <br> POST['data'] = { "date":"2017-08-08", "salespersonId":"67" }
  <?php 

echo 
  
'<form action="./api.php?action=getAllJournal" method="post" name="form24">
<textarea name="data" id="txt1" type="text" value=""></textarea><br>
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->

  <!-- start of function -->

  <b>27. Get All Receipt:</b>
  <br> GET['action'] = "getAllReceipt"
  <br> POST['data'] = { "date":"2017-08-08", "salespersonId":"67" }
  <?php 

echo 
  
'<form action="./api.php?action=getAllReceipt" method="post" name="form24">
<textarea name="data" id="txt1" type="text" value=""></textarea><br>
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->

  <!-- start of function -->

  <b>28(a). Get All Invoice:</b>
  <br> GET['action'] = "getAllInvoice"
  <br> POST['data'] = { "date":"2017-08-08", "salespersonId":"67" }
  <?php 

echo 
  
'<form action="./api.php?action=getAllInvoice" method="post" name="form24">
<textarea name="data" id="txt1" type="text" value=""></textarea><br>
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->

  <!-- start of function -->

  <b>28(b). Get All Credit Note:</b>
  <br> GET['action'] = "getAllCreditNote"
  <br> POST['data'] = { "date":"2017-08-08", "salespersonId":"67" }
  <?php 

echo 
  
'<form action="./api.php?action=getAllCreditNote" method="post" name="form24">
<textarea name="data" id="txt1" type="text" value=""></textarea><br>
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->

  <!-- start of function -->

  <b>28(c). Get All Debit Note:</b>
  <br> GET['action'] = "getAllDebitNote"
  <br> POST['data'] = { "date":"2017-08-08", "salespersonId":"67" }
  <?php 

echo 
  
'<form action="./api.php?action=getAllDebitNote" method="post" name="form24">
<textarea name="data" id="txt1" type="text" value=""></textarea><br>
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->

  <!-- start of function -->

  <b>29. Get Orderpage by ios:</b>
  <br> GET['action'] = "iosOrderPage"
  <br> POST['data'] = { "iosOrderPage_data": { "salespersonid":"123" } }

  <?php 

echo 
  
'<form action="./api.php?action=iosOrderPage" method="post" name="form24">
<textarea name="data" id="txt1" type="text" value=""></textarea><br>
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->

  <!-- start of function -->

  <b> 30.Send request to boss for lower price:</b>
  <br> GET['action'] = "requestBossPrice"
  <br> POST['data'] = { "requestBossPrice_data": { "salesperson_id":"16", "customer_id":"1", "device_token":"hmng", "product_code":"LCD",
  "min_price":"6.00", "asking_price":"5.00" } }

  <?php 

echo 
  
'<form action="./api.php?action=requestBossPrice" method="post" name="form24">
<textarea name="data" id="txt1" type="text" value=""></textarea><br>
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->

  <!-- start of function -->

  <!-- <b> 29.Check password for request min price:</b><br>
GET['action'] = "checkRequestPassword"<br>
POST['data'] = 
{
  "checkRequestPassword_data":
  {
    "salesperson_code":"Chong",
    "device_token":"hmng",
    "product_code":"LCD",
    "min_price":"6.00",
    "asking_price":"4.00",
    "password":"3954"
  }
}

<?php 

// echo 
  
// '<form action="./api.php?action=checkRequestPassword" method="post" name="form24">
// <textarea name="data" id="txt1" type="text" value=""></textarea><br>
// <input name="submit" type="submit" value="GO">
// </form>';
?>

<hr /> -->

  <!-- end of function -->

  <!-- start of function -->

  <b>31. Upload File:</b>
  <br> GET['action'] = "uploadExcel"
  <br>

  <?php 

echo 
  
'<form id="myForm" action="http://eztech.com.my/easysales/iManage/api.php?action=uploadExcel" method="post" enctype="multipart/form-data">

                   <table>
                        <tr>
                <td>Product Import File</td>  
                <td>:</td>
                <td><input type="text" name="excelFileName" id="excelFileName" disabled style="width: 135%" /></td>
                                <td><label class="cabinet"><input type="file" name="dataFile[]" id="dataFile" multiple="true"  accept="application/zip" onchange="return getUploadFileName2()" class="uploadControl"/></label></td>
              </tr> 
                                  <tr><td style="height: 5px;"></td><td></td><td></td><td></td></tr>
                        <tr>
                <td>Zip image File</td> 
                <td>:</td>
                <td><input type="text" name="fileName" id="fileName" disabled style="width: 135%" /></td>
                                <td><label class="cabinet"><input type="file" name="dataFile[]" id="dataFile1" multiple="true"  accept="application/zip" onchange="return getUploadFileName()" class="uploadControl"/></label></td>
              </tr> 

                     


                   
                   </table>
                   <table>
                        <tr>
                            <td>
                                <label style="padding-left: 130px; width: 270px;color: #7F7F7F">You are required to upload product images with zip [extension .zip]</label>

                            </td>
                            
                        </tr>
                        <tr>
                            <td>
                                <label style="padding-left: 130px; width: 270px;color: #7F7F7F">Please note the product images must be allocated at root folder of the zip which means it shouldn\'t include any subfolder in the zip.</label>

                            </td>
                            
                        </tr>
                       <tr>
                            <td>
                                <label style="padding-left: 130px; width: 270px;color: #7F7F7F">Maximum allowed product image width is 1000.</label>

                            </td>
                            
                        </tr>
                       <tr>
                            <td>
                                <label style="padding-left: 130px; width: 270px;color: #7F7F7F">Maximum allowed product image height is 1300.</label>

                            </td>
                            
                        </tr>
                   </table>

                   

                   

                   <div class="uploadDiv" style="padding-top: 20px; padding-left: 175px">
                            <a href="#" onclick="goBack()" ><img src="images/btn_cancel.png"/></a>
                            <button type="submit" >submit</button>
                          </div>
                    

                   
                   <input type="hidden" value="<?php echo $postId; ?>" name="hiddenPostID" id="hiddenPostID">
  <input type="hidden" name="existingPDF" id="existingPDF">
  </form>'; ?>

  <hr />

  <!-- end of function -->

  <!-- start of function -->

  <b>32. request mobile setting:</b>
  <br> GET['action'] = "requestMobileSetting"
  <br>

  <?php 

echo 
  
'<form action="./api.php?action=requestMobileSetting" method="post" name="form1">
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->

  <!-- start of function -->

  <b>33. Get no stock page by ios:</b>
  <br> GET['action'] = "iosNoStockPage"
  <br> POST['data'] = { "iosNoStockPage_data": { "salespersonid":"123" } }

  <?php 

echo 
  
'<form action="./api.php?action=iosNoStockPage" method="post" name="form24">
<textarea name="data" id="txt1" type="text" value=""></textarea><br>
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->

  <!-- start of function -->

  <b>34. Get no stock notification by ios:</b>
  <br> GET['action'] = "getNoStockNotification"
  <br> POST['data'] = { "getNoStockNotification_data": { "salespersonid":"123" } }

  <?php 

echo 
  
'<form action="./api.php?action=getNoStockNotification" method="post" name="form24">
<textarea name="data" id="txt1" type="text" value=""></textarea><br>
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->

  <!-- start of function -->

  <b>35. Get owed quantity:</b>
  <br> GET['action'] = "getOwedQuantitylist"
  <br> POST['data'] = { "getOwedQuantitylist_data": { "salespersonId":"123" } }

  <?php 

echo 
  
'<form action="./api.php?action=getOwedQuantitylist" method="post" name="form24">
<textarea name="data" id="txt1" type="text" value=""></textarea><br>
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->
  <!-- start of function -->

  <b>36. ConfirmTransferMultipleOrder:</b>
  <br> GET['action'] = "ConfirmTransferMultipleOrder"
  <br> POST['data'] = { "ConfirmTransferMultipleOrder_data": { "OrderList":[{"order_id":"order1001"}, {"order_id":"order1002"}]
  } }

  <br />

  <?php 

echo 
	
'<form action="./api.php?action=ConfirmTransferMultipleOrder" method="post" name="form1">
<textarea name="data" id="txt1" type="text" value=""></textarea><br>
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->

  <!-- start of function getProductCurrentCount -->

  <b>37. Get Product Current Count:</b>
  GET['action'] = "getProductCurrentCount"<br>
  POST['data'] = 
{
  "getProductCurrentCount_data":
  {
    "productCodeList":[{
      "code":"P-SKI-FZ-DFP-12.5X12.5-PCS"
    },{
      "code":"P-1000-IQF-100/200-0.8"
    }]
  }
}
  <br />

  <?php 

echo 
'<form action="./api.php?action=getProductCurrentCount" method="post" name="form1">
<textarea name="data" id="txt1" type="text" value=""></textarea><br>
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />

  <!-- end of function -->
  
  <!-- start of function getProductCurrentCount -->

  <b>38. Get Product Current Count:</b>
  GET['action'] = "getProductGroup"<br>
  POST['data'] = { "date":"2017-08-08" }
  <br />

  <?php 

echo 
'<form action="./api.php?action=getProductGroup" method="post" name="form1">
<textarea name="data" id="txt1" type="text" value=""></textarea><br>
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />
  
  <!-- end of function -->

  
  <!-- end of function -->
  
  <!-- start of function getProductCurrentCount -->

  <b>39. Get Product Current Count:</b>
  GET['action'] = "getProductGroupAdv"<br>
  POST['data'] = { "date":"2017-08-08" }
  <br />

  <?php 

echo 
'<form action="./api.php?action=getProductGroupAdv" method="post" name="form1">
<textarea name="data" id="txt1" type="text" value=""></textarea><br>
<input name="submit" type="submit" value="GO">
</form>';
?>

  <hr />
  
  <!-- end of function -->


</body>

</html>