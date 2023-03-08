<?php

session_start();
error_reporting(0);
date_default_timezone_set('Asia/Kuala_Lumpur');

$userId ='';
$roleId ='';
$client = '';

if(isset($_GET['userId']) && isset($_GET['roleId']) && isset($_GET['client']))
{
  $userId = $_GET['userId'];
  $roleId = $_GET['roleId'];
  $client = $_GET['client'];
}
else
{
  header("location : Erorpage.php");
}

if(isset($_SESSION['noStockDateFrom']))
{
  $dateFrom =  $_SESSION['noStockDateFrom'];
}
else
{
  $dateFrom = '';
}

if(isset($_SESSION['noStockDateTo']))
{
  $dateTo = $_SESSION['noStockDateTo'];
}
else
{
  $dateTo = '';
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>EasySales - Back Office System</title>

<script type="text/javascript" language="javascript" src="js/ajaxFunctions.js?random=<?php echo uniqid(); ?>"></script>
<script type="text/javascript" language="javascript" src="js/consignment.js?random=<?php echo uniqid(); ?>"></script>
<script type="text/javascript" language="javascript" src="js/manish.js?random=<?php echo uniqid(); ?>"></script>
<script src="js/lib/spin.js"></script>
<script src="js/lib/LogoResize/resizeLogo.js"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<link rel="stylesheet" type="text/css" media="screen" href="css/style.css"  />
<link rel="stylesheet" type="text/css" media="screen" href="css/jun.css"  />
<link rel="stylesheet" type="text/css" media="screen" href="css/orderpage.css"  />
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
  var visited = false;
  $( function() {
    $( "#dateFrom" ).datepicker({
        dateFormat: 'yy-mm-dd',
        onSelect: function (date) {
          var dateTo = $('#dateTo');
          var startDate = $(this).datepicker('getDate');

          dateTo.datepicker('option', 'minDate', startDate);
        }
    });
    $( "#dateTo" ).datepicker({ dateFormat: 'yy-mm-dd' });
    $('div.ui-datepicker').css({
       "font-size":"15px"
    });
    $('.dateClear').on('click',function(){
      $(this).parent().find('.hasDatepicker').datepicker('setDate', null);
    });
  } );

//below is the new date picker
  $(function() {
  $('input[name="daterange"]').daterangepicker({
    opens: 'right',
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

  });
});

</script>


</head>

<?php
    echo "<body id=\"loading\" onload=\"loadStockNotFound('$roleId','$userId','$dateFrom','$dateTo','$client')\" style='background-color:#ffffff;'>";
?>

<div  align="center">

<div id="sessionTimeoutWarning" style="display: none"></div>

    <table width="100%">
      <tr>
        <td colspan="3" align="center">
          <p class="order-type-label" align="justify" style="width: 90%"><strong>List of not found stock</strong><br> Click on checkbox after you have informed the customer</p>
        </td>
      </tr>
      <tr style="display:none;">
        <td hidden>
            <div class="btn-group" >
                  <input type="text" id="dateFrom" readonly="true" style="height: 0%;width: 150px;"  />
                  <span class="glyphicon glyphicon-remove-circle dateClear"></span>
                </div>
        </td>
        <td>&nbsp;&nbsp;&nbsp;</td>
        <td hidden>
            <div class="btn-group" >
                  <input type="text" id="dateTo" readonly="true" style="height: 0%;width: 150px;" >
                  <span class="glyphicon glyphicon-remove-circle dateClear"></span>
                </div>
        </td>
      </tr>
      <tr>
        <td colspan="3" style="padding-left: 5%">
          Consignment Date From - Consignment Date To
        </td>
      </tr>
      <tr>
        <td colspan="3" align="center">
          <input type="text" name="daterange" id="curdate" value="" readonly="true" style="height: 50px;width: 92%;font-size: 20px; text-align: center;border-radius: 10px;border: 1px solid #337AB7; "/>
          <br>

        </td>
      </tr>
      <tr>
        <td colspan="3"><br></td>
      </tr>
      <tr width="90%">
        <td align="center" width="49%">
                    <a href="#" <?php echo"onclick=\"window.location.reload(true);\""; ?> >
                      <button type="button" class="btn btn-primary" style="width:80%;">Refresh</button>
                    </a>


        </td>
        <td width="2%"></td>
        <td align="center" width="49%">
                  <a href="#" <?php echo"onclick=\"searchStockNotFound('$roleId', '$userId','$client')\""; ?> >
                      <button type="button" class="btn btn-primary" style="width:80%;"><span class="glyphicon glyphicon-search" aria-hidden="true"></span>   Search</button>
                  </a>


        </td>
      </tr>
    </table>




</div>
<br>
<div class="container" id="test">


</div>
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
  if(sessionStorage.getItem("visited")!=""|| sessionStorage.getItem("visited")!=null){

    document.getElementById("curdate").value =sessionStorage.getItem("visited") ;
  }
  else{
    document.getElementById("curdate").value = ful;
  }


</script>
</body>

</html>
