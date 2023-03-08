<!DOCTYPE html>
<html lang="en">
<?php
session_start();
$client = '';
$userId = '';
$cust_code = '';
$orderStatus = '';
$isWarehouse = isset($_GET['isWarehouse']) ? $_GET['isWarehouse'] : 0;
$wh_code = isset($_GET['wh_code']) ? $_GET['wh_code'] : '';
$testing = isset($_GET['test']) ? isset($_GET['test']) : 0;

if (isset($_SESSION['_order_status'])){
    $orderStatus=$_SESSION['_order_status'];
}

if(isset($_GET['userId']) && isset($_GET['client'])){

    $client         = $_GET['client'];
    
    $_SESSION['settings'] = $_GET['settings'];

    $isCustomerView = false;
    if(isset($_GET['cust_code'])){
        $isCustomerView = true;
        $cust_code = $_GET['cust_code'];
    } 

    if($isCustomerView){
         
    }else{
        require_once('getSalespersonList.php');    
        $userId         = $_GET['userId'];
        $salespersonid  = $userId;

        $param = array(
            "client"        =>$client,
            "salespersonid" =>$userId
        );
      
        $salesperson_param = array(
            "client"         =>  $client,
            "salespersonid"  =>  $salespersonid
        );


        // $customer_data = getCustomerList($param);
        // $customer_data = $customer_data["data"];
        // $customer_len  = count($customer_data);
        $salesperson_data = getSalespersonList($salesperson_param);
        $_SESSION['role_id'] = $salesperson_data['role_id'];
        $_SESSION['user_id'] = $userId;
        $salespersonid = $salesperson_data['role_id'] == 10 ? 0 : $salesperson_data['salesperson_id'];
        $salesperson_data = $salesperson_data["data"];
        $salesperson_len  = count($salesperson_data);
    }
    

}else{
    header('location:Errorpage.php');
}

?>


<head>
    <meta charset="UTF-8">
    <meta name="viewport" 
      content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
      <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3pro.css">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>EasyTech</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
   <style>
     * { max-height: 5000em; }
   </style>
</head>
<body id="bodym">

<table  style="border:0px solid #ccc; width:100%;">
    <tr>
        <td style="align:left" colspan="3">
            <form id="input-form">

                <?php if($client != 'bigbathlef' || !$isWarehouse): ?>
                <input placeholder="Order Date From - Order Date To" id="rangeDateFrom" type="button" class="button_add radius text" id="orderDate"/>
                <?php endif; ?>
                

                <?php if($client != 'eurosteel' && $client != 'ideamaker'): ?>
                    <div class="divider"></div>
                    <input placeholder="Delivery Date From - Delivery Date To" id="rangeDateTo" type="button" class="button_add radius text" id="deliveryDate"/>
                <?php endif; ?>
            </form>
        </td>
    </tr>




    <?php

    if(!$isCustomerView && ($client != 'bigbathlef' && !$isWarehouse)){
        if($_SESSION['_salesperson_id'] && is_numeric($_SESSION['_salesperson_id'])){
            for($i = 0; $i < $salesperson_len; $i++){
                if ($salesperson_data[$i]['value'] == $_SESSION['_salesperson_id'])
                    $salespersonName= $salesperson_data[$i]['label'];
                if (strlen($salespersonName) > 30)
                    $salespersonName= substr($salespersonName, 0, 29) . '...';
            }
        }else {
            $salespersonName="Choose Salesperson";
    
        }
    }
    ?>
    <?php if(!$isCustomerView && ($client != 'bigbathlef' || !$isWarehouse)):?>
    <tr style="max-width:100%;overflow: hidden;text-overflow: ellipsis; ">
        <td>

            <?php
            if($salespersonid == '0' || !is_numeric($salespersonid) || $_SESSION['role_id'] == 10):
                ?>
                <div class="divider"></div>
                <input style="background: #147efb url('./images/customer.png') no-repeat 5px center; max-width:100%;" id="salespersonSelection" value="<?php
                if(is_numeric($_SESSION['_salesperson_id']) ){
                    echo $salespersonName;
                }else{
                    echo "Choose Salesperson";


                }
                ?>" type="button" class="customer_selection_button radius text_white""/>

                <div id="salespersonDropDwn" style="display:none;" class="dropdown-content radius">

                    <input type="text" placeholder="Search ..." id="salespersonSeachInp" onkeyup="filter_salesperson()">

                    <?php for($i = 0; $i < $salesperson_len; $i++):?>
                        <a class="choosenSalesperson" data-value="<?php echo $salesperson_data[$i]['value']?>" data-name="<?php $str = $salesperson_data[$i]['label'];
                        if (strlen($str) > 30)
                            $str = substr($str, 0, 29) . '...';
                        echo $str;?>">
                            <?php echo $salesperson_data[$i]['label']?>

                        </a>
                    <?php endfor;?>

                </div>
            <?php endif;?>
            <?php
            if(count($salesperson_data) === 0 && !$isCustomerView ):
                ?>
                <input value="Error Loading Salesperson" type="button" class="customer_selection_button radius text_white"/>
                <div class="divider"></div>

            <?php endif;?>
        </td>
    </tr>
    <?php endif;?>






    <?php

    if(!$isCustomerView && ($client != 'bigbathlef' || !$isWarehouse)){
        if ($_SESSION['_customer_name'] ){
            $customerName=$_SESSION['_customer_name'];
            if (strlen($customerName) > 30){
                $customerName = substr($customerName, 0, 29) . '...';
            }

        }else{
            $customerName="Choose Customer";
        }
    }

    ?>

    <?php if(!$isCustomerView && ($client != 'bigbathlef' || !$isWarehouse)): ?>

    <tr style="max-width:100%;overflow: hidden;text-overflow: ellipsis; ">
        <td>



            <input style="max-width:100%;" id="custSelection" value="<?php echo $customerName ?>" type="button" class="customer_selection_button radius text_white"/>

            <div id="customerDropDwn" style="display:none;" class="dropdown-content radius">

                <input type="text" placeholder="Search.." id="customerSeachInp" onkeyup="filter_cust()">




            </div>

        </td>
    </tr>
    
    <?php endif;?>
    <?php if(!$isCustomerView && ($client != 'bigbathlef' || !$isWarehouse)): ?>
    <tr style="max-width:100%;overflow: hidden;text-overflow: ellipsis; ">
        <td>
            <select name="orderSelection" id="orderSelection" style="line-height:25px;" class="customer_selection_button radius text_white">
                <option <?php if ($orderStatus == '') echo 'selected' ; ?> value="">All Orders</option>
                <option <?php if ($orderStatus == '0') echo 'selected' ; ?> value="0">Active Orders</option>
                <option <?php if ($orderStatus == '1') echo 'selected' ; ?> value="1">Confirmed Orders</option>
                <option <?php if ($orderStatus == '2') echo 'selected' ; ?> value="2">Transferred Orders</option>
                <option <?php if ($orderStatus == '3') echo 'selected' ; ?> value="3">Posted Orders</option>
            </select>
        </td>
    </tr>
    <?php endif;?>
    
    <?php

    if ($_SESSION['_case_name'] ){
        $caseName=$_SESSION['_case_name'];
    }else{
        $caseName='';
    }

    if ($_SESSION['_status_name'] ){
        $statusName=$_SESSION['_status_name'];
    }else{
        $statusName='PENDING';
    }

    if ($_SESSION['_title_name'] ){
        $titleName=$_SESSION['_title_name'];
    }else{
        $titleName='';
    }

    if ($_SESSION['_ship_via'] ){
        $shipVia=$_SESSION['_ship_via'];
    }else{
        $shipVia='';
    }

    ?>

    <?php if($client == 'eurosteel'): ?>

    <tr style="max-width:100%;overflow: hidden;text-overflow: ellipsis; ">
        <td>
            <select name="shipViaSelection" id="shipViaSelection" style="line-height:25px;" class=" radius text_white">
                <option <?php if ($shipVia == '') echo 'selected' ; ?> value="">All Ship Via</option>
                <option <?php if ($shipVia == 'BW') echo 'selected' ; ?> value="BW">BW</option>
                <option <?php if ($shipVia == 'JB') echo 'selected' ; ?> value="JB">JB</option>
                <option <?php if ($shipVia == 'NONE') echo 'selected' ; ?> value="NONE">None</option>
            </select>
        </td>
    </tr>

    <tr style="max-width:100%;overflow: hidden;text-overflow: ellipsis; ">
        <td>
            <select name="titleSelection" id="titleSelection" style="line-height:25px;" class=" radius text_white">
                <option <?php if ($titleName == '') echo 'selected' ; ?> value="">All Job Type</option>
                <option <?php if ($titleName == 'CUTTING') echo 'selected' ; ?> value="CUTTING">Cutting</option>
                <option <?php if ($titleName == 'MACHINING') echo 'selected' ; ?> value="MACHINING">Machining</option>
                <!-- <option <?php if ($titleName == 'SEND TO TRANSPORT') echo 'selected' ; ?> value="SEND TO TRANSPORT">Send To Transport</option>
                <option <?php if ($titleName == 'READY FOR DELIVERY') echo 'selected' ; ?> value="READY FOR DELIVERY">Ready For Delivery</option> -->
            </select>
        </td>
    </tr>

    <tr style="max-width:100%;overflow: hidden;text-overflow: ellipsis; ">
        <td>
            <select name="statusSelection" id="statusSelection" style="line-height:25px;" class=" radius text_white">
                <option <?php if ($statusName == 'ALL') echo 'selected' ; ?> value="ALL">All Status</option>
                <option <?php if ($statusName == 'PENDING') echo 'selected' ; ?> value="PENDING">Pending</option>
                <option <?php if ($statusName == 'COMPLETED') echo 'selected' ; ?> value="COMPLETED">Completed</option>
                <option <?php if ($statusName == 'SEND TO TRANSPORT') echo 'selected' ; ?> value="SEND TO TRANSPORT">Send To Transport</option>
            </select>
        </td>
    </tr>
    <?php endif;?>

    <?php if($client == 'eurosteel' || ($client == 'bigbathlef' && $isWarehouse)): ?>
    <tr style="max-width:100%;overflow: hidden;text-overflow: ellipsis; ">
        <td>
            <input style="min-width:100%;border:1px solid #147efb;line-height:30px;color:#147efb;padding-left:5px;" id="caseSelection" name="caseSelection" type="text" value ="<?php echo $caseName ?>" class="radius"  placeholder="Search.."/>
        </td>
    </tr>
    <?php endif;?>

    <tr>
        <td align="middle">
            <div class="row">
                <div class="column" style="line-height:25px;padding-right:5px">
                    <div class="check">
                        <label class="checkbox_rounded">
                            <input type="checkbox" id="showCancelBox">
                            <div class="checkbox_hover"></div>
                        </label>
                        <p class="non_important_text" style="margin-left:5px;" >View CXL</p>
                    </div>
                </div>

                <div class="column" style="padding-right:5px">
                    <button class="radius non_important_text red_button text_white" id="btnClear"> Clear </button>
                </div>
                <div class="column">
                    <button class="radius non_important_text buttons text_white" id="btnSearch"> Search </button>
                </div>
            </div>
        </td>
    </tr>

</table>

<div class="divider"></div>
<div class="divider"></div>

<div class="divider"></div>
<div class="divider"></div>

<div id="ordersDiv" style="margin-bottom:100px">

</div>
<div id="showApprovement" class="approvement">

</div>

<div id="wh_modal" class="wh-modal">
    <div class="wh-modal-content">
        <span id="wh_close">&times;</span>
        <div class="wh-content"></div>
    </div>
</div>



<link href='https://fonts.googleapis.com/css?family=Open Sans' rel='stylesheet'>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.2.3/themes/airbnb.css">
<script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.3.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.2.3/flatpickr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery.redirect@1.1.4/jquery.redirect.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8.5.0/dist/sweetalert2.all.min.js"></script>
<!-- <link href='css/magnific-popup.css' rel='stylesheet'>
<script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.3.1.min.js"></script>
<script type="text/javascript" src="js/jquery.magnific-popup.min.js"></script> -->
<script>

    var filter_cust,filter_salesperson,custSelection,updateOrderApprovalStatus;

    class Order{
        constructor(
            userId,
            client,
            orderFrom,
            orderTo,
            deliverFrom,
            deliverTo,
            customer_id,
            backlink,
            salesperson_id,
            customer_name,
            customer_code,
            case_name,
            status_name,
            title_name,
            isCustomerView,
            isWarehouse,
            ship_via,
            order_status,
            wh_code,
            testing
        ){
            this.userId = userId;
            this.client = client;
            this.orderFrom = orderFrom;
            this.orderTo = orderTo;
            this.deliverFrom = deliverFrom;
            this.deliverTo = deliverTo;
            this.customerId = customer_id;
            this.backlink   = backlink;
            this.salesperson_id = salesperson_id;
            this.customer_name  = customer_name;
            this.customer_code  = customer_code;
            this.case_name  = case_name;
            this.status_name  = status_name;
            this.title_name  = title_name;
            this.isCustomerView  = isCustomerView;
            this.isWarehouse = isWarehouse;
            this.ship_via = ship_via;
            this.order_status = order_status;
            this.wh_code = wh_code;
            this.testing = testing;
        }
    }

    function showMessage(msg){
        alert(msg);
    }

    $(document).ready(function(){

        $('#bodym').nextAll().css('font-size','17px');

        var tisenoAPI = 'http://13.229.126.174/Delivery/api.php?action=submitInformation';

        $("#customerDropDwn").removeAttr("style");

        $(document).on('click', '.create-button', function() {
            let orderData     = JSON.parse($(this).attr('data-json'));
            $.redirect(tisenoAPI, orderData);
        });

        $(document).on('click', '.view-button', function() {
            let orderData     = JSON.parse($(this).attr('data-json'));
            $.redirect(tisenoAPI, orderData);
        });

        $(document).on('click', '.approve-button', function() {
            // approve here
            var order_id = $(this).attr('data-orderid');
            var salesperson_id = $(this).attr('data-userid');
            var client = $(this).attr('data-client');

            var data = encodeURIComponent('{"client":"' + client + '","salesperson_id":"' + salesperson_id +'","order_id":"' + order_id +'","action":"approve","comment":""}');
            Swal.fire({
                title: 'Approving Document',
                html: 'Please wait...',
                onBeforeOpen: () => {
                    Swal.showLoading()
                },
                onClose: () => {
                    
                }
            })
            updateOrderApprovalStatus(data);
        });

        var myarray = [];
        $(document).on('click', '.checkedInvoice', function() {
            $('#showApprovement').empty();
            var order_id = $(this).attr('data-orderid');
            var salesperson_id = $(this).attr('data-userid');
            var client = $(this).attr('data-client');

            if(this.checked){
                myarray.push(order_id);
                $(".display").addClass("display-none");
            }else{
                myarray.splice(myarray.findIndex(x => x === order_id), 1);
            }
            if(myarray.length == 0){
                $(".display").removeClass("display-none");
            }
            if(myarray.length > 0){
                $('#showApprovement').append('<button data-client="'+client+'" data-userid="'+salesperson_id+'"  data-orderid="'+myarray+'" class="radius non_important_text approve-button-st approve-button" style={width:25%;font-width: 10vw;}> Approve </button><button data-client="'+client+'" data-userid="'+salesperson_id+'" data-orderid="'+myarray+'" class="radius non_important_text reject-button-st reject-button" style={width:25%;font-width: 10vw;}> Reject </button>');
            }
        });

        $(document).on('click', '.approve-button-comment', async function() {
            // reject here
            var order_id = $(this).attr('data-orderid');
            var salesperson_id = $(this).attr('data-userid');
            var client = $(this).attr('data-client');
            
            const {value: formValues} = await Swal.fire({
                html:
                    '<div data-orderid="'+order_id+'" data-userid="'+salesperson_id+'" data-client="'+client+'" class="container" id="swal-container">' +
                    '    <div class="row">' +
                    '        <div class="col">' +
                    '            <div>' +
                    '                <div class="card-body">' +
                    '                        <div class="form-group">' +
                    '                            <input id="approve_comment" class="swal2-input" autofocus placeholder="Comment">' +
                    '                        </div>' +
                    '                        <div class="mx-auto">' +
                    '                        <button type="submit" class="approve-button-st" id="btn_approve">Approve</button></div>'+
                    '                </div>' +
                    '            </div>' +
                    '        </div>'+
                    '    </div>' +
                    '</div>',
                focusConfirm: true,
                background:'white',
                showConfirmButton:false,
                showCloseButton:true,
                preConfirm: () => {
                    return [
                        document.getElementById('en_remark').value
                    ]
                },
                onOpen: () => {
                    
                }
            });
        });

        $(document).on('click', '.reject-button', async function() {
            // reject here
            var order_id = $(this).attr('data-orderid');
            var salesperson_id = $(this).attr('data-userid');
            var client = $(this).attr('data-client');
            
            const {value: formValues} = await Swal.fire({
                html:
                    '<div data-orderid="'+order_id+'" data-userid="'+salesperson_id+'" data-client="'+client+'" class="container" id="swal-container">' +
                    '    <div class="row">' +
                    '        <div class="col">' +
                    '            <div>' +
                    '                <div class="card-body">' +
                    '                        <div class="form-group">' +
                    '                            <input id="reject_comment" class="swal2-input" autofocus placeholder="Reason to reject">' +
                    '                        </div>' +
                    '                        <div class="mx-auto">' +
                    '                        <button type="submit" class="reject-button-st" id="btn_reject">Reject</button></div>'+
                    '                </div>' +
                    '            </div>' +
                    '        </div>'+
                    '    </div>' +
                    '</div>',
                focusConfirm: true,
                background:'white',
                showConfirmButton:false,
                showCloseButton:true,
                preConfirm: () => {
                    return [
                        document.getElementById('en_remark').value
                    ]
                },
                onOpen: () => {
                    
                }
            });
        });


        $('body').on('click', '#btn_approve', function() {
            Swal.showLoading();

            var order_id = $("#swal-container").attr('data-orderid');
            var salesperson_id = $("#swal-container").attr('data-userid');
            var client = $("#swal-container").attr('data-client');
            var comment = $("#approve_comment").val();

            var data = encodeURIComponent('{"client":"' + client + '","salesperson_id":"' + salesperson_id +'","order_id":"' + order_id +'","action":"approve","comment":"'+comment+'"}');

            updateOrderApprovalStatus(data);
        });

        $('body').on('click', '#btn_reject', function() {
            Swal.showLoading();

            var order_id = $("#swal-container").attr('data-orderid');
            var salesperson_id = $("#swal-container").attr('data-userid');
            var client = $("#swal-container").attr('data-client');
            var comment = $("#reject_comment").val();

            var data = encodeURIComponent('{"client":"' + client + '","salesperson_id":"' + salesperson_id +'","order_id":"' + order_id +'","action":"reject","comment":"'+comment+'"}');

            updateOrderApprovalStatus(data);
        });

        // today's orders

        $(window).scroll(function () {
            localStorage.setItem("transfer_order_scrollTo", $(window).scrollTop());
        });

        var backlink             = window.location.href;

        // today's orders

        var customer_name = "", customer_id = "", orderDatePicker_val = "", deliveryDatePicker_val = "";


        var userId                  = '<?php echo $userId; ?>';
        var client                  = '<?php echo $client; ?>';
        var salesperson_id          = '<?php echo $salespersonid; ?>';
        var customer_code           = '<?php echo $cust_code; ?>';
        var case_name               = '<?php echo $caseName; ?>';
        var status_name             = '<?php echo $statusName; ?>';
        var title_name              = '<?php echo $titleName; ?>';
        var ship_via                = '<?php echo $shipVia; ?>';
        var isWarehouse             = '<?php echo $isWarehouse; ?>';
        var order_status            = '<?php echo $orderStatus; ?>';
        var wh_code                 = '<?php echo $wh_code ?>';
        var testing                 = '<?php echo $testing ?>';

        var first_day_this_month    = '<?php if($client == 'urbanhygienist'){ echo date('Y-m-01'); }else{ echo '';}?>';
        var last_day_this_month     = '<?php if($client == 'urbanhygienist'){ echo date('Y-m-t'); }else{ echo '';}?>';

        var isSalesPersonView = userId>0;
        var isCustomerView    = '<?php echo $isCustomerView; ?>';

        if(isSalesPersonView){
            loadCustomers (client,salesperson_id);
        }

        var orderFrom           = phpSessionExists('_odate_from');
        var orderTo             = phpSessionExists('_odate_to');

        var deliverFrom         = phpSessionExists('_ddate_from');
        var deliverTo           = phpSessionExists('_ddate_to');
        customer_id             = phpSessionExists('_cust_id');
        var view_cancel         = phpSessionExists('_view_cancel');


        if(!isCustomerView){
            if(phpSessionExists('_salesperson_id')){
            salesperson_id      = phpSessionExists('_salesperson_id');
            $("#salespersonDropDwn").data('clicked', true);
            loadCustomers (client,salesperson_id);

        }
        // if(!customer_name){
            customer_name           = phpSessionExists('_customer_name');
        // }
        }

        if(view_cancel){
            $('#showCancelBox').prop('checked', true);
        }

        loadOrders(new Order
            (userId,client,orderFrom,orderTo,deliverFrom,deliverTo,customer_id,backlink,salesperson_id,customer_name,customer_code,case_name,status_name,title_name,isCustomerView,isWarehouse,ship_via,order_status,wh_code,testing)
        );

        const orderDatePicker = flatpickr("#rangeDateFrom",{
            mode: 'range',
            dateFormat: "Y-m-d",
            disableMobile: "false",
            defaultDate:[orderFrom,orderTo],
            onChange: function(selectedDates, dateStr, instance) {
                orderDatePicker_val = dateStr;
            },
            onReady: function(dObj, dStr, fp, dayElem){
                orderDatePicker_val = dStr;
            },
            maxDate: last_day_this_month,
            minDate: first_day_this_month
        });
        let clicks = 0;
        $("#rangeDateFrom").click(function(){
            clicks += 1;
            if(clicks%2==0 && clicks>0){
                orderDatePicker.close();
            }
        });

        const deliveryDatePicker = flatpickr("#rangeDateTo",{
            mode: 'range',
            dateFormat: "Y-m-d",
            disableMobile: "false",
            defaultDate:[deliverFrom,deliverTo],
            onChange: function(selectedDates, dateStr, instance) {
                deliveryDatePicker_val = dateStr;
            },
            onReady: function(dObj, dStr, fp, dayElem){
                deliveryDatePicker_val = dStr;
            },
            maxDate: last_day_this_month,
            minDate: first_day_this_month
        });

        let deliveryClicks = 0, salesperson_name;

        $("#rangeDateTo").click(function(){
            deliveryClicks += 1;
            if(deliveryClicks%2===0 && deliveryClicks>0){
                deliveryDatePicker.close();
            }
        });

        $("#rangeDateTo").blur(function(){
            if(deliveryDatePicker_val){
                deliveryDatePicker.toggle();
            }
        });

        $(".choosenCustomer").click(function() {

            customer_name = $(this).attr("data-name");
            customer_code   = $(this).attr("data-value");

            $("#custSelection").val(customer_name);
            toggle_cust_selection();
        });

        $("#custSelection").click(function() {
            if(!$("#salespersonDropDwn").data('clicked') && !isSalesPersonView )
            {
                alert("please select a salesperson first");
                return;
            }
            toggle_cust_selection();
        });

        $("#salespersonSelection").click(function() {
            $("#customerDropDwn").hide();
            $("#salespersonDropDwn").toggle();
        });

        $(".choosenSalesperson").click(function() {

            $("#salespersonDropDwn").data('clicked', true);

            customer_code='';
            $("#custSelection").val("Choose Customer");

            salesperson_name = $(this).attr("data-name");
            salesperson_id = $(this).attr("data-value");

            $("#salespersonSelection").val(salesperson_name);

            loadCustomers (client,salesperson_id);
            toggle_sp_selection();
        });

        $('#titleSelection').on('change', function() {
            title_name = $(this).val();
        });

        $('#shipViaSelection').on('change', function() {
            ship_via = $(this).val();
        });
        
        $('#statusSelection').on('change', function() {
            status_name = $(this).val();
        });

        $('#caseSelection').on('change', function() {
            case_name = $(this).val();
        });

        $('#orderSelection').on('change', function() {
            order_status = $(this).val();
        });

        $("#btnSearch").click(function() {

            var userId              = '<?php echo $userId; ?>';
            var client              = '<?php echo $client; ?>';
            var isWarehouse         = '<?php echo $isWarehouse; ?>';
            var wh_code             = '<?php echo $wh_code ?>';
            var testing             = '<?php echo $testing ?>';

            let orderDate_arr       = orderDatePicker_val.split("to");
            let deliveryDate_arr    = deliveryDatePicker_val.split("to");

            var orderFrom           = orderDate_arr[0];
            var orderTo             = orderDate_arr[1];

            var deliverFrom         = deliveryDate_arr[0];
            var deliverTo           = deliveryDate_arr[1];

            loadOrders(new Order
                (userId,client,orderFrom,orderTo,deliverFrom,deliverTo,customer_id,backlink,salesperson_id,customer_name,customer_code,case_name,status_name,title_name,isCustomerView,isWarehouse,ship_via,order_status,wh_code,testing)
                ,true);
        });

        $("#btnClear").click(function() {

            $('#showCancelBox').prop('checked', false);
            $('#input-form')[0].reset();
            $('#custSelection').val("Choose Customer");
            $("#rangeDateFrom").attr("placeholder", "Order Date From - Order Date To");

            $.ajax({
                url: "clearSession.php?action=clearSession",
                type: "POST",
                contentType: false,
                cache: false,
                processData:false,
                success: function(response){
                    console.warn("session cleared"+response);

                    location.reload();
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.warn(xhr);
                }
            });

        });

        /* $('select').on('change', function() {
            var userId              = '<?php echo $userId; ?>';
            var client              = '<?php echo $client; ?>';

            let orderDate_arr       = orderDatePicker_val.split("to");
            let deliveryDate_arr    = deliveryDatePicker_val.split("to");

            var orderFrom           = orderDate_arr[0];
            var orderTo             = orderDate_arr[1];

            var deliverFrom         = deliveryDate_arr[0];
            var deliverTo           = deliveryDate_arr[1];

            loadOrders(new Order (
                userId,client,orderFrom,orderTo,deliverFrom,deliverTo,customer_id,salesperson_id,customer_name,customer_code
                )
            );
        }); */

        custSelection = function custSelection(value,name){
            customer_name = name;
            customer_id   = value;

            let tmp_cust_name = customer_name.length<29?customer_name:customer_name.substr(0,29)+' ...';
            $("#custSelection").val(tmp_cust_name);
            toggle_cust_selection();
        };

        updateOrderApprovalStatus = function updateOrderApprovalStatus (data){
            jQuery.ajax({
                type: "POST",
                url: "./route.php",
                data: "data=" + data + "&action=updateOrderApprovalStatus",
                success: function (msg) {
                    console.warn(JSON.stringify(msg));
                    debugger;
                    window.location.reload();
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.warn(xhr);
                }

            });
        }

        function loadCustomers (client,userId){
            var data = encodeURIComponent('{"client":"' + client + '","salesperson_id":"' + userId +'"}');
            jQuery.ajax({
                type: "POST",
                url: "./route.php",
                data: "data=" + data + "&action=loadCustomers",

                success: function (msg) {

                    msg = isJSONparsable(msg);


                    if(msg){
                        customerList=msg.data;
                        cust_len=customerList.length;
                        $(".choosenCustomer").remove();
                        for(let i=0; i<cust_len; i++){

                            $("#customerDropDwn").append(`<a class="choosenCustomer" onclick="custSelection('${customerList[i].value}','${customerList[i].label.replace("'", "\\'")}')">${customerList[i].label}</a>`);
                        }
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.warn(xhr);
                }

            });
        }

        function warn(msg){
            console.warn(JSON.stringify(msg));
        }

        function loadOrders(_, btnSearchClicked = false) {
            $('#ordersDiv').empty();
            $('#ordersDiv').append('<div style="margin-left:-35px" class=" no_orders loader"></div>');

            var cancel      = $("#showCancelBox").is(':checked');

            cancel          = String((cancel | 0));
            var orderType = '';
            if(_.isWarehouse == 1){
                <?php if($client == 'bigbathlef'): ?>
                orderType = 2;
                _.orderFrom = '';
                _.orderTo = '';
                <?php endif; ?>
            }else{
                orderType   = _.order_status;
            }
            var doc_type    = '<?php echo $_GET["doc"]?>';
            console.warn(JSON.stringify(_));
            var data = encodeURIComponent('{"searchOrders_data" :{ "salespersonId":"' + _.userId + '","showCancel":"' + cancel
                + '","dateFrom":"' + _.orderFrom + '","dateTo":"' + _.orderTo + '","order_status":"'
                + orderType+ '","customer_status":"' + _.customerId
                + '","deliveryDateFrom":"' + _.deliverFrom + '","deliveryDateTo":"' + _.deliverTo + '","client":"' + _.client + '","salesperson_id":"' + _.salesperson_id + '","doc":"' + doc_type + '","backlink":"'+_.backlink+'","customer_name":"'+_.customer_name+'","customer_code":"'+_.customer_code+'","case_name":"'+_.case_name+'","status_name":"'+_.status_name+'","title_name":"'+_.title_name+'","isCustomerView":"'+_.isCustomerView+'","ship_via":"'+_.ship_via+'","wh_code":"'+_.wh_code+'","isWarehouse":"'+_.isWarehouse+'","testing":"'+_.testing+'"}}');

            jQuery.ajax({
                type: "POST",
                url: "./route.php",
                data: "data=" + data + "&action=getOrderWithView",
                success: function (msg) {
                    $('#ordersDiv').empty();

                    msg = isJSONparsable(msg);
                    
                    if(msg){

                        let views = msg.data;

                        if(typeof views == "undefined"){
                            // cause views can be array as well with no data
                            views = "";
                        }

                        if ((views.length == 0) && btnSearchClicked){
                            // $("#ordersDiv").append(msg.query);
                            $("#ordersDiv").append("<p class='no_orders'>No orders available</p>");
                        }else if( views.length == 0 ){
                            $("#ordersDiv").append("<p class='no_orders'>No orders today</p>");
                        }else{
                            if(views instanceof Array){
                                for(let i = 0, len = views.length; i < len; i++){
                                    let view = views[i];
                                    //$("#ordersDiv").append(msg.query);
                                    $("#ordersDiv").append(view);
                                    
                                    if(localStorage.getItem("transfer_order_scrollTo")){
                                        $("html, body").animate({ scrollTop: localStorage.getItem("transfer_order_scrollTo") });
                                    }

                                }
                            }
                        }
                    }

                    // $('body').on('click', '#checkedInvoice', function() {
                    //     console.log("click");
                    // }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.warn(xhr);
                    $("#ordersDiv").append("<p class='no_orders'>Technical error. Please contact support</p>");
                }
            });
        }


        function isJSONparsable(value){
            if (typeof(value) !== "string"){
                return false;
            }
            try{
                return JSON.parse(value);
            }catch (error){
                return false;
            }
        }

        function toggle_cust_selection() {
            $("#salespersonDropDwn").hide();
            $("#customerDropDwn").toggle();
            //document.getElementById("customerDropDwn").classList.toggle("show");

        }
        function toggle_sp_selection() {
            $("#customerDropDwn").hide();
            $("#salespersonDropDwn").toggle();
            //document.getElementById("salespersonDropDwn").classList.toggle("show");
        }

        filter_cust = function filter_cust() {
            var input, filter, ul, li, a, i;
            input   = document.getElementById("customerSeachInp");
            filter  = input.value.toUpperCase();
            div     = document.getElementById("customerDropDwn");
            a       = div.getElementsByTagName("a");
            for (i = 0, len = a.length; i < len; i++) {
                txtValue    = a[i].textContent || a[i].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    a[i].style.display = "";
                } else {
                    a[i].style.display = "none";
                }
            }
        };

        filter_salesperson = function filter_salesperson() {
            var input, filter, ul, li, a, i;
            input   = document.getElementById("salespersonSeachInp");
            filter  = input.value.toUpperCase();
            div     = document.getElementById("salespersonDropDwn");
            a       = div.getElementsByTagName("a");
            for (i = 0, len = a.length; i < len; i++) {
                txtValue    = a[i].textContent || a[i].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    a[i].style.display = "";
                } else {
                    a[i].style.display = "none";
                }
            }
        };

        function phpSessionExists(key){
            let temp='';

            if(key === '_odate_from'){
                temp= "<?php echo $_SESSION['_odate_from'] ?>";
                if (temp==='') {
                    <?php if($client == 'ideamaker'): ?>
                    var date = new Date();
                    temp = new Date(date.getFullYear(), date.getMonth(), 2).toJSON().slice(0,10);
                    <?php else: ?>
                    temp= new Date().toJSON().slice(0,10);
                    <?php endif; ?>

                }
            }
            if(key === '_odate_to'){
                temp= "<?php echo $_SESSION['_odate_to'] ?>";
                if (temp==='') {
                    <?php if($client == 'ideamaker'): ?>
                    var date = new Date();
                    temp = new Date(date.getFullYear(), date.getMonth() + 1, 1).toJSON().slice(0,10);
                    <?php else: ?>
                    temp= new Date().toJSON().slice(0,10);
                    <?php endif; ?>
                }
            }
            if(key === '_ddate_from'){
                temp= "<?php echo $_SESSION['_ddate_from'] ?>";
                <?php if($client == 'bigbathlef' && $isWarehouse): ?>
                temp= new Date().toJSON().slice(0,10);
                <?php endif; ?>
            }
            if(key === '_ddate_to'){
                temp= "<?php echo $_SESSION['_ddate_to'] ?>";
                <?php if($client == 'bigbathlef' && $isWarehouse): ?>
                temp= new Date().toJSON().slice(0,10);
                <?php endif; ?>
            }
            if(key === '_view_cancel'){
                temp= "<?php echo $_SESSION['_view_cancel'] ?>";
            }
            if(key === '_salesperson_id'){
                temp= "<?php echo $_SESSION['_salesperson_id'] ?>";
            }
            if(key === '_cust_id'){
                temp= "<?php echo $_SESSION['_cust_id'] ?>";
            }
            if(key === '_customer_name'){
                temp= "<?php echo $_SESSION['_customer_name'] ?>";
            }

            return temp;
        }
    });

    var wh_modal = document.getElementById("wh_modal");
    var wh_span = document.getElementById("wh_close");
    wh_span.onclick = function() {
        wh_modal.style.display = "none";
    }

    function openWHModal(order_id){
        //debugger
        var modal = document.getElementById("wh_modal");
        let client = "<?php echo $client; ?>";
        var data = encodeURIComponent('{"openWHModal_data" :{ "client":"' + client + '"}}');

        $.ajax({
            type: "POST",
            url: "./route.php",
            data: "data=" + data + "&action=openWHModal",
            success: function(msg)
            {
                //debugger;
                var decodedJson = JSON.parse(msg);
                var data = decodedJson.data;

                if(data){
                    $(".wh-content").empty();
                    var view = "<p style='font-weight:bold;text-align:center;'>SELECT WAREHOUSE</p>";

                    var select = "<div style='width:100%;'>";
                    for(var i = 0; i < data.length; i++){
                        var wh_code = data[i].wh_code;
                        var wh_name = data[i].wh_name;
                        select += "<button class='btn-warehouse' style='margin:5px;width:100%;' onclick=\"updateWarehouse('"+order_id+"','"+ wh_code +"')\" >" + wh_name + "</button>";
                    }
                    select += "</div>";
                    if(select != ''){
                        view += select;
                    } 
                    $(".wh-content").append(view);
                    modal.style.display = "block";

                }
            },
            error: function (xhr, ajaxOptions, thrownError)
            {
                alert("Please contact support");
            }
        });
    }

    function updateWarehouse(order_id,wh_code){

        let client = "<?php echo $client; ?>";
        var json = { 
            updateWarehouse_data:{
                client,
                order_id,
                wh_code
            }
        };
        /* var data = encodeURIComponent('{"updateOrderItem_data" :{ "ipad_item_id":"' + ipad_item_id + '","client":"' + client + '","status":"' + status + '","price":"' + price + '","quantity":"' + quantity + '","disc_1":"' + disc_1 + '","disc_2":"' + disc_2 + '","disc_3":"' + disc_3 + '","remark":"' + remark + '","uom":"' + uom + '","discount_method":"' + discount_method + '","order_id":"' + <?php echo $_GET['orderId'];?> + '"}}'); */

        var data = encodeURIComponent(JSON.stringify(json));

        $.ajax({
            type: "POST",
            url: "./route.php",
            data: "data=" + data + "&action=updateWarehouse",
            success: function(msg)
            {
                
                var decodedJson = JSON.parse(msg);
                var status = decodedJson.msg;
                // debugger
                if(status.msg){
                    alert(status.msg);
                    location.reload();
                }
            },
            error: function (xhr, ajaxOptions, thrownError)
            {
                alert("Please contact support");
            }
        });
    }

    function openODModal(order_id, current_date){

        var modal = document.getElementById("wh_modal");
        let client = "<?php echo $client; ?>";
        $(".wh-content").empty();
        var view = "<p style='font-weight:bold;text-align:center;'>SELECT ORDER DATE</p>";

        var select = "<div style='width:100%;'>";
        select += '<input value="'+current_date+'" type="button" class="button_add radius text" id="newOrderDate"/>';
        select += "</div>";

        select += "<button class='btn-warehouse' style='margin-top:5px;width:100%;' onclick=\"updateOrderDate('"+order_id+"')\" >Update</button>";

        if(select != ''){
            view += select;
        } 
        $(".wh-content").append(view);
        modal.style.display = "block";
        debugger
        const orderDatePicker = flatpickr("#newOrderDate",{
            dateFormat: "Y-m-d",
            disableMobile: "false",
            enableTime: false
        });
    }

    function updateOrderDate(order_id){

        let client = "<?php echo $client; ?>";
        let order_date = document.getElementById('newOrderDate').value;
        debugger;
        var json = { 
            updateOrderDate_data:{
                client,
                order_id,
                order_date
            }
        };

        var data = encodeURIComponent(JSON.stringify(json));

        $.ajax({
            type: "POST",
            url: "./route.php",
            data: "data=" + data + "&action=updateOrderDate",
            success: function(msg)
            {
                
                var decodedJson = JSON.parse(msg);
                var status = decodedJson.msg;
                // debugger
                if(status.msg){
                    alert(status.msg);
                    location.reload();
                }
            },
            error: function (xhr, ajaxOptions, thrownError)
            {
                alert("Please contact support");
            }
        });
    }
</script>

<!-- <script type="text/javascript">
	$(document).ready(function() {
        $('.order_img').magnificPopup({
            delegate: 'a',
            type: 'image',
            closeOnContentClick: false,
            closeBtnInside: false,
            mainClass: 'mfp-with-zoom mfp-img-mobile',
            image: {
                verticalFit: true,
                titleSrc: function(item) {
                    return item.el.attr('title') + ' &middot; <a class="image-source-link" href="'+item.el.attr('data-source')+'" target="_blank">image source</a>';
                }
            },
            gallery: {
                enabled: true
            },
            zoom: {
                enabled: true,
                duration: 300, // don't foget to change the duration also in CSS
                opener: function(element) {
                    return element.find('img');
                }
            }
            
        });
    });
</script> -->
<style>

    #customerSeachInp {
        border-box: box-sizing;
        background-image: url('./images/search.png');
        background-position: 14px 12px;
        background-repeat: no-repeat;
        font-size: 16px;
        padding: 12px 15px 12px 35px;
        border: none;
        width: 100%;
        border-bottom: 1px solid #ddd;
    }

    #customerSeachInp:focus {outline: 3px solid #ddd;}

    #salespersonSeachInp {
        border-box: box-sizing;
        background-image: url('./images/search.png');
        background-position: 14px 12px;
        background-repeat: no-repeat;
        font-size: 16px;
        padding: 12px 15px 12px 35px;
        border: none;
        width: 100%;
        border-bottom: 1px solid #ddd;
    }

    #salespersonSeachInp:focus {outline: 3px solid #ddd;}

    #caseSelection:focus {outline: none;}

    #statusSelection Option {background-color: rgb(239, 239, 239);color:black;}

    #titleSelection Option {background-color: rgb(239, 239, 239);color:black;}

    #shipViaSelection Option {background-color: rgb(239, 239, 239);color:black;}

    .dropdown {
        position: relative;
        display: inline-block;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        background-color: #f6f6f6;
        min-width: 97%;
        max-width: 97%;
        overflow: auto;
        border: 1px solid #ddd;
        z-index: 1;
        height: 50%;
    }

    .dropdown-content a {
        color: black;
        padding: 12px 16px;
        text-decoration: none;
        display: block;
    }

    .dropdown a:hover {background-color: #ddd;}

    .show {display: block;}


    ::placeholder { /* Chrome, Firefox, Opera, Safari 10.1+ */
        color: #147efb;
        opacity: 1; /* Firefox */
    }

    :-ms-input-placeholder { /* Internet Explorer 10-11 */
        color: #147efb;
    }

    ::-ms-input-placeholder { /* Microsoft Edge */
        color: #147efb;
    }
    body {
        font-family: 'Open Sans';
        font-size: 16px;
        line-height:1;
        user-select: none;
        -webkit-user-select: none;
        -ms-user-select: none;
        -webkit-touch-callout: none;
        -o-user-select: none;
        -moz-user-select: none;
        padding:5px;
        overflow: scroll;
    }
    input.button_add {
        line-height: 35px;
        height: 35px;
        padding-left: 25px;
        border: 1px solid #147efb;
        background: #fff url('./images/calendar.png') no-repeat 5px center;
        cursor: pointer;
        width:100%;
    }
    input.dropdown_add {
        height: 35px;
        padding-left: 25px;
        border: 1px solid #147efb;
        background: #fff url('./images/building.png') no-repeat 5px center;
        cursor: pointer;
        width:100%;
    }
    .text {
        color:#147efb;
        text-align:left;
    }
    .text_white {
        color:#fff;
        text-align:left;
    }
    .non_important_text {
        font-size: 14px;
    }
    .radius {
        border-radius:3px;
    }
    .divider {
        height:5px
    }
    .buttons {
        color:#e8ebef;
        border: 1px solid #147efb;
        background: #147efb;
        width: 100%;
        display: inline;
        text-align:center;
        padding: 5px;
        box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
        transition: 0.3s;
        z-index: 1;
        height:35px;
    }

    .title {
        color:#147efb;
        text-align:left;
        margin-block-start: 0.5em;
        margin-block-end: 0em;
        font-weight: bold;
    }
    .dates {
        text-align:left;
    }
    .message {
        color: grey;
        text-align:left;
        font-style: italic;
    }
    p {
        display: block;
        margin-block-start: 0.5em;
        margin-block-end: 0.5em;
        margin-inline-start: 0.5px;
        margin-inline-end: 0px;
        margin-top: 3px;
        padding-top: 3px;
        margin-bottom: 3px;
        padding-bottom: 3px;
    }
    }

    .delivery_status {
        display: inline;
    }

    hr {
        display: block;
        unicode-bidi: isolate;
        margin-block-start: 0em;
        margin-block-end: 0em;
        margin-inline-start: auto;
        margin-inline-end: auto;
        margin: 3px;
        padding: 3px;
    }
    .delivery-buttons {
        margin-inline-end: 5px;
        color:#e8ebef;
        border: 1px solid #147efb;
        background: #147efb;
        width: 32%;
        display: inline;
        text-align:center;
        padding: 5px;
        box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
        transition: 0.3s;
    }
    .reject-button-st {
        margin-inline-end: 5px;
        color:#e8ebef;
        border: 1px solid rgb(255,85,45);
        background: rgb(255,85,45);
        width: 32%;
        display: inline;
        text-align:center;
        padding: 5px;
        box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
        transition: 0.3s;
    }
    .approve-button-st {
        margin-inline-end: 5px;
        color:#e8ebef;
        border: 1px solid #3CB371;
        background: #3CB371;
        width: 32%;
        display: inline;
        text-align:center;
        padding: 5px;
        box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
        transition: 0.3s;
    }
    .search_field {
        border: 1px solid #147efb;
        font-size: 100%;
        width:100%;
    }

    .customer_selection_button {
        box-sizing: border-box;
        -moz-box-sizing: border-box;
        -webkit-box-sizing: border-box;
        height: 35px;
        padding-left: 25px;
        border: 1px solid #147efb;
        background: #147efb url('./images/customer.png') no-repeat 5px center;
        cursor: pointer;
        width:100%;
        box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
        transition: 0.3s;
    }

    .red_button {
        color:#e8ebef;
        border: 1px solid rgb(255,85,45);
        width: 100%;
        display: inline;
        text-align:center;
        padding: 5px;
        box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
        transition: 0.3s;
        background: rgb(255,85,45);
        z-index: 1;
        height:35px;
    }
    .no_orders {
        font-size:14px;
        color:grey;
        position: absolute;
        top: 65%;
        left: 50%;
        -moz-transform: translateX(-50%) translateY(-50%);
        -webkit-transform: translateX(-50%) translateY(-50%);
        transform: translateX(-50%) translateY(-50%);
    }

    .dropdown-div{
        margin-left: -5px;
        margin-right: -5px;
        box-sizing: border-box;
        -moz-box-sizing: border-box;
        -webkit-box-sizing: border-box;
        height: 30px;
        color:gray;
        font-weight: bold;
        font-size:14px;
        background-color: #f4f5f7;

    }

    .total-sales{

        box-sizing: border-box;
        -moz-box-sizing: border-box;
        -webkit-box-sizing: border-box;
        height: 30px;
        line-height: 30px;
        color:#147efb;
        font-weight: bold;
        font-size:14px;
        border: 1px solid #147efb;
        text-align:center;

    }
    .center-text{
        position: relative;
        float: left;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
    select{
        -moz-appearance: none;
        -webkit-appearance: none;
        appearance: none;
        height: 100%;
        width: 100%;
        padding: 5px;
        border: 1px solid #147efb;
        color:#fff;
        background: url('./images/dropdown.png') no-repeat right #147efb;
        background-position-x : 99%;
    }

    .check {
        display: flex;
        justify-content: center;
        align-items: center;
        width:120px;
    }

    .checkbox_rounded {
        position: relative;
        cursor: pointer;
        display: flex;
        justify-content: center;
        align-items: center;
        width: 25px;
        height: 25px;
    }

    .checkbox_rounded input {
        opacity: 1;
    }

    .checkbox_rounded .checkbox_hover {
        position: absolute;
        width: 100%;
        height: 100%;
        background: white;
        border-radius: 50%;
        border: 4px solid #d6d6d6;
        transition: all 0.5s;
    }

    .checkbox_rounded input:checked + .checkbox_hover {
        box-shadow: inset 0 0 0 8px #147efb;
        border: none;
    }

    * {
        box-sizing: border-box;
    }

    .column {
        float: left;
        width: 33.33%;
        padding-top: 10px;
        padding-bottom: 10px;
        height: 35px;
    }

    .row:after {
        content: "";
        display: table;
        clear: both;
    }
    .no-message {
        color:grey;
        border: 1px solid lightgrey;
        background: lightgrey;
        width: 32%;
        display: inline;
        text-align:center;
        padding: 5px;

    }
    .loader {
    border: 6px solid #f3f3f3;
    border-radius: 50%;
    border-top: 6px solid #147efb;
    width: 60px;
    height: 60px;
    -webkit-animation: spin 2s linear infinite; /* Safari */
    animation: spin 2s linear infinite;
    }

    /* Safari */
    @-webkit-keyframes spin {
        0% { -webkit-transform: rotate(0deg); }
        100% { -webkit-transform: rotate(360deg); }
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .child_div{
        float:left;
        margin-right:5px;
        font-size:14px;
        color:grey
    }
    .load-wrapp {
    width:200px;
        height:20px;
        float:left;
    display: inline-block;
    }
    .line {
        display: inline-block;
        width: 7px;
        height: 7px;
        border-radius: 7px;
        background-color: #147efb;
    }

    .load-2 .line:nth-last-child(1) {animation: loadingB 1.5s 1s infinite;}
    .load-2 .line:nth-last-child(2) {animation: loadingB 1.5s .5s infinite;}
    .load-2 .line:nth-last-child(3) {animation: loadingB 1.5s 0s infinite;}

    /* Safari */
    @-webkit-keyframes loadingB {
            0% { -webkit-transform: rotate(0deg); }
            100% { -webkit-transform: rotate(360deg); }
        }
    @keyframes loadingB {
        0% {width: 7px;}
        50% {width: 15px;}
        100% {width: 7px;}
    }

    .order_img {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 1; /* Sit on top */
    padding: 10px; /* Location of the box */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 80%; /* Full height */
    overflow-x: auto; /* Enable scroll if needed */
    background-color: rgb(0,0,0); /* Fallback color */
    background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
    flex-direction: row;
    }

    .close {
    color: black;
    float: right;
    font-size: 28px;
    font-weight: bold;
    }

    .img_button{
    color: #e8ebef;
    border: 1px solid #147efb;
    background: #147efb;
    width: 32%;
    display: inline-block;
    text-align: center;
    padding: 5px;
    border-radius: 3px;
    font-size: 14px;
    }

    .attachment {
        display:flex;
		text-align: center;
		padding: 0px 5px;
		border: 1px solid #ddd;
		max-width: 100%;
        max-height: 100%;
		margin: 2vw;
		border-radius: 3px;
        padding:5px;
	}
	.attachment img {
		max-width: 100%;
		max-height: 100%;
	}

    .wh-modal {
        display: none; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: 1; /* Sit on top */
        padding-top: 100px; /* Location of the box */
        left: 0;
        top: 0;
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        overflow: auto; /* Enable scroll if needed */
        background-color: rgb(0,0,0); /* Fallback color */
        background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
    }

    /* Modal Content */
    .wh-modal-content {
        background-color: #fefefe;
        margin: auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
    }

    .btn-warehouse {
        background-color: #147efb; 
        border: 1px solid #147efb; 
        color: white; 
        padding: 8px 24px; 
        cursor: pointer; 
        border-radius: 3px;
        height: 40px;
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)
    }

    .approvement {
        width: 100%;
        position: fixed;
        bottom: 0;
        background-color: white;
        padding: 10px;
        display: flex;
        justify-content: center;
    }

    .display-none {
        display: none;
    }
</style>
</body>
</html>
