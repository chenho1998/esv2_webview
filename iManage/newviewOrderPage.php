<?php 

session_start(); 
/*$userId=$_SESSION["userId"];
$roleId=$_SESSION["roleId"];*/
$userId=$_POST['userId'];;
$roleId=$_POST['roleId'];;
$Client=$_POST['client'];
if($userId!=null && $roleId!=null && $Client!=null){

}
$config = parse_ini_file(dirname(__FILE__).'/../config.ini');
$order_page_active = $config['order_page_active'];
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>EasySales - Back Office System</title>

<!--<script type="text/javascript" language="javascript" src="js/ajaxFunctions.js"></script>-->
<script type="text/javascript" language="javascript" src="js/ajaxFunctions.js?random=<?php echo uniqid(); ?>"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="js/lib/spin.js"></script>
<script src="js/lib/LogoResize/resizeLogo.js"></script>
<script type="text/javascript" language="javascript" src="js/periodpoll.js"></script>
<script type="text/javascript" language="javascript" src="js/manish.js?random=<?php echo uniqid(); ?>"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

<script src="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">

<link rel="stylesheet" type="text/css" media="screen" href="css/style.css"  />
<link rel="stylesheet" type="text/css" media="screen" href="css/jun.css"  />
<link rel="stylesheet" type="text/css" media="screen" href="css/orderpage.css"  />

<script>
function goBack() {
    window.history.back();
}
</script>
</head>

<?php
 	
 	$orderId = $_POST['orderId'];
	$currency = $_POST['currency'];
 
    echo "<body id=\"loading\" onload=\"loadViewOrderPage2('$roleId','$orderId','$userId','$currency','$Client')\" style='background-color:#ffffff;'>";
?>

<div  width="100%" height="100%">


<td width="80%" height="100%" style="padding-top:10px; vertical-align: top;">
   
  
 <div class="container">
  <table  border="0" width="100%" height="32px" cellpadding="0" cellspacing="0">
    <tr>
      <td colspan="2" height="10px"></td>
    </tr>
    <tr id="functionHeader">
      
        <!-- <td  height="30px" class="mainheadfont" style="background-color:#FFFFFF; color:#000000;"></td> -->
        <td class="pull-right" width="100%"><button class="btn btn-primary" onclick="goBack()" style="width: 100%;">Go Back</button></td>
      
    </tr>
    <tr>
      <td colspan="2" height="10px"></td>
    </tr>
 </table>
  <div class="list-group">
   <a  class="list-group-item" style="background-color:#337AB7">
   	<h2 class="list-group-item-heading"style="color:white">
   		<span id="custname"></span>
        <span hidden id="orderData"></span>
   		<span hidden id="billingaddr"></span>
   		<span hidden id="deliveryDate"></span>
   		<span hidden id="totalOrderItems"></span>
   		<span hidden id="orderItem"></span>
   	</h2>
   </a>
    <a class="list-group-item" id="orderDetailTablenew">
                    
    </a>
  </div>
  <!-- <h4 id="custname" align="center" ></h4> -->
 </div>
<table  style="border:1px solid #ccc;" width="100%" height="100%" cellpadding="0" cellspacing="0" background="images/bg_all.png" hidden>
    
    <tr>
    <td style="vertical-align: top; text-align: left; ">
        <div id="contentDiv" class="contentfontsize" width="100%" height="100%" style="margin-top: 5px; padding-left:15px;">
          
          	
           
           <table style="background-color: #FFFFFF; width:975px;">
           		<tr>
           			<td style="height: 25px; padding-left: 15px;">Order</td>
           		</tr>
           </table>
           <!---<table  width:975px;">-->
           <table style="width:975px;">
              <tr>
                <td style="height: 25px; padding-left: 15px;">Sales Order: <span id="salesOrderId"></span></td>
              </tr>
              <tr>
                <td style="height: 25px; padding-left: 15px;">Sales Agent: <span id="salesAgent"></span></td>
              </tr>
              <tr>
                <td style="height: 25px; padding-left: 15px;">Created Date: <span id="createdDate"></span></td>
              </tr>
              <tr>
                <td style="height: 25px; padding-left: 15px;">Delivery Note: <b><span id="deliveryNote" style="background-color:yellow;"></span></b></td>
              </tr>
              <tr>
                <td style="height: 25px; padding-left: 15px;">Current Status:
                  <?php if ($order_page_active == 0): ?>
                    <div style="display:none;">
                  <?php endif ?>
                  <span id="reserved">Reserved</span><span> / </span>
                  <?php if ($order_page_active == 0): ?>
                    </div>
                  <?php endif ?>
                  <span id="confirmed">Confirmed</span><span> / </span>
                  <span id="toQNE">Transfered to Accounting software</span>
                </td>
              </tr>
           </table>
           
   			<table border="0" id="orderDetailTable" style="width:975px; border-collapse:collapse;">
          <tr class="orderDetailTableHeader">
            <td style="text-align:left; padding-left:5px;">No</td>
            <td style="text-align:left; padding-left:5px;">Item</td>
            <td style="text-align:left; padding-left:5px;">Item Price</td>
            <td style="text-align:left; padding-left:5px;">Quantity</td>
            <td style="padding-left:10px; text-align:left">Sub Total</td>
            <td style="padding-left:10px; text-align:left">Discount</td>
            <td style="text-align:left; padding-left:5px;">Picking Status </td>
            <td style="padding-left:0px;text-align:left">Agent Status </td>
          </tr>
        </table>
   			
   			<!-- <table border="0" id="newOrderDetailTable"></table> -->
           
           <!-- <table style="background-color: #FFFFFF; width:975px">
           		<tr>
           			<td style="height: 25px; padding-left: 15px;">Status</td>
           		</tr>
           </table> -->
           
   <!--         <table border="0" id="paymentDetailTable">
           	
           </table>
            -->
            <br>
            <table border="0" id="searchTableID">
            <tr>
              <td style="padding-left: 15px;"><b>Customer Info</b></td>
            </tr>
            <tr><td></td></tr>
            </table>

           <div style="height: 463px; overflow: auto; width:975px" >
                 
                  <table border="0" style="width: 975px; height: 463px; border-collapse:collapse;" id="viewOrderCustInfoTable" class="viewOrderCustInfoTable" background="images/base_addproduct.png" >
                  
                  </table>
           </div>
            <table border="0" style="width:350px; margin-left: 350px">
                            <tr>
                                <td style="padding:5px;border:none ; width:100% ;">
                                    <div>
                                    	<a href="#" onclick="goBack()"><img src="images/btn_back.png" border="0" /></a> 
                                    		
                                        <a href="#" <?php echo"onclick=\"saveOrder('$userId', '$orderId')\""; ?> ><img src="images/btn_savechanges.png" border="0" /></a> 
                                        
                                        <form action="ordersPage.php" method="POST" id="backForm">
                                        </form>
                                        
                                       
                                    </div>
                                </td>
                            </tr>
             </table>
           
        </div>
    </td>
    </tr>
</table>
<!--</td>
</tr>
</table>-->
</td>

</tr>
</table>
</div>
<div id="sessionTimeoutWarning" style="display: none"></div>
</body>

</html>
