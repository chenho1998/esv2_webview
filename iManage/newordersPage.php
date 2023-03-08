<?php 
session_start(); 

date_default_timezone_set('Asia/Kuala_Lumpur');

$userId ='';
$roleId ='';
$client = '';
if(isset($_GET['userId']) && isset($_GET['roleId']) && isset($_GET['client'])){
  $userId = $_GET['userId'];
  $roleId = $_GET['roleId'];
  $client = $_GET['client'];
}else{
  header('location : Erorpage.php');
}

if(!isset($_SESSION['dateFrom'])){
  $_SESSION['dateFrom'] = date('Y-m-d');
}

if(!isset($_SESSION['dateTo'])){
  $_SESSION['dateTo'] = '';
}

if(!isset($_SESSION['order-select'])){
  $_SESSION['order-select'] = "";
}

if(!isset($_SESSION['customer-select'])){
  $_SESSION['customer-select'] = "";
}

if(!isset($_SESSION['deliveryDateFrom'])){
  $_SESSION['deliveryDateFrom'] = "";
}

if(!isset($_SESSION['deliveryDateTo'])){
  $_SESSION['deliveryDateTo'] = "";
}

$dateFrom = $_SESSION['dateFrom'];
$dateTo = $_SESSION['dateTo'];
$customer = $_SESSION['customer-select'];
$orderStatus = $_SESSION['order-select'];
$deliveryDateFrom = $_SESSION['deliveryDateFrom'];
$deliveryDateTo = $_SESSION['deliveryDateTo'];

$config = parse_ini_file(dirname(__FILE__).'/../config.ini');
$order_page_selection = $config['order_page_selection'];
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
<script type="text/javascript" language="javascript" src="js/manish.js?random=<?php echo uniqid(); ?>"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script src="js/lib/spin.js"></script>
<script src="js/lib/LogoResize/resizeLogo.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script> -->
<!-- <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script> -->
<!-- <link rel="stylesheet" type="text/css" href="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css"> -->
<link rel="stylesheet" type="text/css" media="screen" href="css/style.css"  />
<link rel="stylesheet" type="text/css" media="screen" href="css/jun.css"  />
<link rel="stylesheet" type="text/css" media="screen" href="css/orderpage.css?random=<?php echo uniqid(); ?>"  />
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
  $(document).ready(function(){
    $('#order-cancel-check').on('change',function() {
      $('#order-page-search a').trigger("click");
    });

     displaySelectedOrderPage( $("#order-page-selections-value").val(), $("#order-page-select-" + $("#order-page-selections-value").val()))

    $(".order-page-selections").on("click", function() {
      // debugger; 
      var data = encodeURIComponent('{ "orderSelect":"' + $(this).data("select") + '"}');
      // debugger
      jQuery.ajax({
        type: "POST",
        url: "api.php",
        data: "data=" + data + "&action=changeOrderSelection",  
            success: function(msg){
              // debugger;
            }
      })     
      $("#order-page-selections-value").val( $(this).data("select"));
      displaySelectedOrderPage( $("#order-page-selections-value").val(), $(this))
    });

    $( "#dateFrom" ).datepicker({ 
        dateFormat: 'yy-mm-dd',
        onSelect: function (date) {
          var dateTo = $('#dateTo');
          var startDate = $(this).datepicker('getDate');
          // startDate.setDate(startDate.getDate());
          dateTo.datepicker('option', 'minDate', startDate);
        }
    });
  
    $( "#dateTo" ).datepicker({ dateFormat: 'yy-mm-dd' });
  
  $( "#deliveryDateFrom" ).datepicker({ dateFormat: 'yy-mm-dd' });
  
  $( "#deliveryDateTo" ).datepicker({ dateFormat: 'yy-mm-dd' });
    
  $('div.ui-datepicker').css({
       "font-size":"25px"
    });
  
    $('.dateClear').on('click',function(){
      $(this).parent().find('.hasDatepicker').datepicker('setDate', null);
    });

    $('.custClear').on('click',function(){
      $("#customerList").val('');
    });
  });

  function displaySelectedOrderPage(data, element){
    $(".order-page-selections").removeClass("btn-primary").addClass("btn-default");
    element.removeClass("btn-default").addClass("btn-primary");
    if(data == "all"){
      $('.order-page-reserved').show();
      $('.order-page-confirmed').show();
      $('.order-page-transfered').show();
    }
    else if (data == "reserved"){
      $('.order-page-reserved').show();
      $('.order-page-confirmed').hide();
      $('.order-page-transfered').hide();
    }
    else if (data == "confirmed"){
      $('.order-page-reserved').hide();
      $('.order-page-confirmed').show();
      $('.order-page-transfered').hide();
    }
    else if (data == "transfered"){
      $('.order-page-reserved').hide();
      $('.order-page-confirmed').hide();
      $('.order-page-transfered').show();
    }
  }
  
//below is the new date picker
  $(function() {
  $('input[name="daterange1"]').daterangepicker({
    opens: 'center',
    autoApply: true
  }, function(start, end, label) {
    var sdate=start.format('YYYY-MM-DD');
    var edate=end.format('YYYY-MM-DD');
    console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
    console.log(sdate+'-'+edate);
    document.getElementById('dateFrom').value=sdate;
    document.getElementById('dateTo').value=edate;
    $('#dateFrom').value= sdate;
    $('#dateTo').value=edate;
    document.getElementById("curdate").style.color = "black";
  });
});
  $(function() {
  $('input[name="daterange2"]').daterangepicker({
    opens: 'center',
    autoApply: true
  }, function(start, end, label) {
    var sdate=start.format('YYYY-MM-DD');
    var edate=end.format('YYYY-MM-DD');
    console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
    console.log(sdate+'-'+edate);
    document.getElementById('deliveryDateFrom').value=sdate;
    document.getElementById('deliveryDateTo').value=edate;
    $('#deliveryDateFrom').value= sdate;
    $('#deliveryDateTo').value=edate;
    document.getElementById("curdate2").style.color = "black";
   
  });
});

</script>
</head>

<?php
   echo "<body id=\"loading\" onload=\"loadOrdersPage2('$client','$roleId','$userId','$dateFrom','$dateTo','$customer', '$orderStatus', '$deliveryDateFrom', '$deliveryDateTo')\" style='background-color:#ffffff;'>";
?>

<div  width="100%" height="100%">

</td>

<td width="80%" height="100%" style="padding-top:10px; vertical-align: top;">
   
 <!-- <table  border="0" width="100%" height="32px" cellpadding="0" cellspacing="0">
    <tr id="functionHeader">
        <td  height="30px" class="mainheadfont" style="background-color:#FFFFFF; color:#000000;">Orders</td>
    </tr>
 </table> --> 
 <div class="container"> 
<table  style="border:0px solid #ccc;" width="100%" height="100%" cellpadding="0" cellspacing="0" >
  
  <tr>
        <td colspan="3" align="center" style="padding-left: 8px;">
          Order Date From - Order Date To
        </td>
      </tr>
     <tr>
        <td colspan="3" align="center">
          <input type="text" name="daterange1" id="curdate" value="" readonly="true" style="height: 50px;width: 100%;font-size: 20px; text-align: center;border-radius: 10px;border: 1px solid #337AB7 "/>
        </td>
    </tr>
    
    <tr>
        <td colspan="3" align="center" style="padding-left: 8px;">
          Delivery Date From - Delivery Date To
        </td>
      </tr>
     <tr>
        <td colspan="3" align="center">
          <input type="text" name="daterange2" id="curdate2" value="" readonly="true" style="height: 50px;width: 100%;font-size: 20px; text-align: center;border-radius: 10px;border: 1px solid #337AB7 ;"/>
        </td>
        
        
    </tr>
    <tr>
        <td colspan="3" align="center"><br>
            
                    <input id="customerList" type="text" style="height: 50px;width:100%;border-radius: 10px;border: 1px solid #337AB7 ;" class="form-control" placeholder="Search Customer">
                    <input id="customerListID" name="customerID" type="hidden" value="" >
                    
            
        </td>
    </tr>
    <tr hidden>
      <td colspan="3" align="center">
        <br>
        <select id="cancelList" style="height: 50px;width:100%;border: 1px solid #337AB7 ;border-radius: 10px;background-color: white;">
      </td>
    </tr>
    <tr>
      <td colspan="3" align="center">
        <br>
        <div class="input-group input-group-lg" align="center" style="width: 100%;height:50px">
                  <span class="input-group-addon" style="border: 1px solid #337AB7;">
                    <input type="checkbox" id="order-cancel-check" >
                  </span>
                  <label class="form-control" style="z-index:0;border: 1px solid #337AB7;height:50px;padding-top:14px;" >
                    View Cancelled Order
                  </label>
        </div>
      </td>
    </tr>
    <tr>
      <td align="right" width="48%">
        <br>
        <a href="#" <?php echo"onclick=\"searchOrder2('$roleId', '$userId','$client')\""; ?> >
                    <!--   <img src="images/btn_search_1.png" border="0" /> -->
                      <button type="button" class="btn btn-primary" style="width:100%;"><span class="glyphicon glyphicon-search" aria-hidden="true"></span>   SEARCH</button>
        </a>
        </td>
        <td width="2%">
          &nbsp;
        </td>
        <td align="left" width="48%">
          <br>
        <a href="#" <?php echo"onclick=\"clearboxes()\""; ?> >
                    <!--   <img src="images/btn_search_1.png" border="0" /> -->
                      <button type="button" class="btn btn-danger" style="width:100%;"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span>   CLEAR</button>
        </a>
        </td>
      
    </tr>
    <tr>
    <td colspan="3" style="vertical-align: top; text-align: left; ">
        <div id="contentDiv" class="contentfontsize" width="100%" height="100%" style="margin-top: 5px; padding-left:15px;">
          
          <table border="0" id="searchTableID" style="height: 5vw;width:1005px;padding:0.4em;background-color:#ccc;" hidden>
            
            <tr>
              <!-- <td>Created Date :</td>
              <td><select id="yearList" style="width:130px;">
                   
                </select></td>
                <td><select id="monthList" style="width:130px;">
                   
              </select></td>
                <td><select id="dayList" style="width:130px;">
                   
                </select></td> -->
                <td>Date From:</td>
                <td>
                  <div class="btn-group">
                    <input type="text" id="dateFrom" readonly="true" style="height: 4vw;width: 150px;" class="form-control" />
                    <span class="glyphicon glyphicon-remove-circle dateClear"></span>
                  </div>
                </td>
                <td>Date To:</td>
                <td>
                  <div class="btn-group">
                    <input type="text" id="dateTo" readonly="true" style="height: 4vw;width: 150px;" class="form-control">
                    <span class="glyphicon glyphicon-remove-circle dateClear"></span>
                  </div>
                </td>
                <td>
                  <div class="btn-group">
                    <input id="customerListold" type="text" style="height: 4vw;width:270px;" class="form-control" placeholder="Search Customer">
                    <input id="customerListIDold" name="customerIDold" type="hidden" value="" >
                    <span class="glyphicon glyphicon-remove-circle custClear"></span>
                  </div>
                   
              </input></td>
                <td><select id="cancelListold" style="height: 4vw;width:160px;">
                   
              </select></td>
              
              
              <td>
                  <div class="functional-buttom" id="order-page-search">
                    <a href="#" <?php echo"onclick=\"searchOrder2('$roleId', '$userId','$client')\""; ?> >
                      <img src="images/btn_search_1.png" border="0" />
                    </a>
                    </div>
               </td>
            </tr>
      
      <tr>
        <td>Delivery Date From:</td>
        <td>
          <div class="btn-group">
            <input type="text" id="deliveryDateFrom" readonly="true" style="height: 4vw;width: 150px;" class="form-control" />
            <span class="glyphicon glyphicon-remove-circle dateClear"></span>
          </div>
        </td>
        
        <td>Delivery Date To:</td>
        <td>
          <div class="btn-group">
            <input type="text" id="deliveryDateTo" readonly="true" style="height: 4vw;width: 150px;" class="form-control" />
            <span class="glyphicon glyphicon-remove-circle dateClear"></span>
          </div>
        </td>
      </tr>

            </table>
          <br />

          <div class="container-fluid">
            
            
                <?php if ($order_page_selection == 0): ?>
                  <div style="display:none;">
                <?php endif ?>
                <div class="btn-group btn-group-lg" role="group" aria-label="...">
                  <button type="button" class="btn btn-default order-page-selections" id="order-page-select-all" data-select="all">All</button>
                  <button type="button" class="btn btn-default order-page-selections" id="order-page-select-reserved" data-select="reserved">Reserved</button>
                  <button type="button" class="btn btn-default order-page-selections" id="order-page-select-confirmed" data-select="confirmed">Confirmed</button>
                  <button type="button" class="btn btn-default order-page-selections" id="order-page-select-transfered" data-select="transfered">Transfered</button>
                  <input type="hidden" id="order-page-selections-value" name="selection-value" value="<?php echo $_SESSION['order-select']; ?>" />
                </div>
                <?php if ($order_page_selection == 0): ?>
                  </div>
                <?php endif ?>
              
                 <div class="input-group input-group-lg" align="center" style="display: none;">
                  <span class="input-group-addon">
                    <input type="checkbox" id="order-cancel-check">
                  </span>
                  <label class="form-control" style="z-index:0;">
                    View Cancelled Order
                  </label>
                </div>

              
          </div>

          <br>
          <!-- <?php//if ($order_page_active == 0): ?>
            <div style="display:none;">
          <?php //endif ?>
          <p class="order-type-label order-page-reserved"><strong> Orders in sales active cart</strong></p>    
           <table border="0" id="ordersTableId" class="ordersTableId order-page-reserved">
               
           </table> 
           <br class="order-page-reserved">
           <?php //if ($order_page_active == 0): ?>
            </div>
           <?php //endif ?> -->
           <!-- <p class="order-type-label order-page-confirmed"><strong> Confirmed Orders</strong></p>    
           <table border="0" id="ordersTableComfirmedId" class="ordersTableId order-page-confirmed" >
               
           </table>
           <br class="order-page-confirmed"> -->
           <!-- <p class="order-type-label order-page-transfered"><strong> Transfered Orders to Accounting software</strong></p>    
           <table border="0" id="ordersTableQNEId" class="ordersTableId order-page-transfered">
               
           </table>  -->
            <input type="hidden" id="currency" name="currency" />
        </div>
    </td>
    </tr>
</table>
</div>
<!--</td>
</tr>
</table>-->
</td>

</tr>
</table>
</div>
<div class="container">
  <div class="list-group">
          <a  class="list-group-item" style="background-color:#337AB7"><h3 class="list-group-item-heading"style="color:white"><span>Orders in sales active cart</span></h3></a>
                  <a class="list-group-item" id="ordersTableId">
                    
                  </a>
    </div>
 
  <div class="list-group">
          <a  class="list-group-item" style="background-color:#337AB7"><h3 class="list-group-item-heading"style="color:white"><span>Confirmed Orders</span></h3></a>
                  <a class="list-group-item" id="ordersTableComfirmedId">
                    
                  </a>
    </div>
    
    <div class="list-group">
          <a  class="list-group-item" style="background-color:#337AB7"><h3 class="list-group-item-heading"style="color:white"><span>Transferred Orders to Accounting system</span></h3></a>
                  <a class="list-group-item" id="ordersTableQNEId">
                    
                  </a>
    </div>
</div>   
<div class="container" id="table">
  
</div>

<div id="sessionTimeoutWarning" style="display: none"></div>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script type="text/javascript">
  var d= new Date();
  var datenewp = document.getElementById('curdate').value;
  var tes = d.getMonth() + 1 + "/" +d.getDate() + "/" +  d.getFullYear();
    var ful= tes+" - "+tes;
    console.log(datenewp);
    console.log("session after this");

    console.log(sessionStorage.getItem("visited"));
    console.log(sessionStorage.getItem("deliverys"));
  if(sessionStorage.getItem("visited")){
    
    document.getElementById("curdate").value =sessionStorage.getItem("visited") ;
    
  }
  else{
    document.getElementById("curdate").value = "";
    document.getElementById("curdate").style.color = "white";

  }
  
  //var datedeli = document.getElementById('curdate2').value;
  if(sessionStorage.getItem("deliverys")){
    
    document.getElementById("curdate2").value =sessionStorage.getItem("deliverys") ;
  }
  else {
    
    document.getElementById("curdate2").value="";
    document.getElementById("curdate2").style.color = "white";
    console.log('if null');

  }
  
  var te=sessionStorage.getItem("deliverys");
  console.log(te);
function clearboxes(){

  document.getElementById("customerList").value="";
  document.getElementById("curdate").value="";
  document.getElementById("curdate2").value="";
  document.getElementById('dateFrom').value="";
  document.getElementById('dateTo').value="";
  document.getElementById('deliveryDateFrom').value="";
  document.getElementById('deliveryDateTo').value="";
  sessionStorage.removeItem("deliverys");
  sessionStorage.removeItem("visited");

}
</script>
</body>

</html>