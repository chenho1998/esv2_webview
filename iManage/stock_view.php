<!DOCTYPE html>
<html lang="en">
<?php
session_start();
$client = '';
$userId = '';
if(isset($_GET['userId']) && isset($_GET['client'])){

    $client         = $_GET['client'];

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

        $salesperson_data = getSalespersonList($salesperson_param);
        $salesperson_data = $salesperson_data["data"];
        $salesperson_len  = count($salesperson_data);
    }

    

}else{
    header('location:Errorpage.php');
}

?>


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3pro.css">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>EasyTech</title>
</head>
<body>

<table  style="border:0px solid #ccc; width:100%;">
    <tr>
        <td style="align:left" colspan="3">
            <form id="input-form">
                <input placeholder="Order Date From - Order Date To" id="rangeDateFrom" type="button" class="button_add radius text" id="orderDate"/>
            </form>
        </td>
    </tr>




    <?php

    if(!$isCustomerView){
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
    <?php if(!$isCustomerView):?>
    <tr style="max-width:100%;overflow: hidden;text-overflow: ellipsis; ">
        <td>

            <?php
            if($salespersonid == '0' || !is_numeric($salespersonid)):
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

    if(!$isCustomerView){
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

    <?php if(!$isCustomerView ): ?>

    <tr style="max-width:100%;overflow: hidden;text-overflow: ellipsis; ">
        <td>
            <input style="max-width:100%;" id="custSelection" value="<?php echo $customerName ?>" type="button" class="customer_selection_button radius text_white"/>

            <div id="customerDropDwn" style="display:none;" class="dropdown-content radius">

                <input type="text" placeholder="Search.." id="customerSeachInp" onkeyup="filter_cust()">

            </div>

        </td>
    </tr>
    
    <?php endif;?>
    <?php $link =""; $class = "column"; if($userId == 0){ $link = "onclick='window.open(\"http://easysales.asia/".$client."/easysales/cms/stockTakeExcel.php?userId=0\")'"; $class="four_column";}?>
    <tr>
        <td align="middle">
            <div class="row">
            
                <div class=<?php echo $class; ?> style="line-height:25px;padding-right:5px">
                    <div class="check">
                        <label class="checkbox_rounded">
                            <input type="checkbox" id="showCancelBox">
                            <div class="checkbox_hover"></div>
                        </label>
                        <p class="non_important_text" style="margin-left:5px;" >View CXL</p>
                    </div>
                </div>

                <div class=<?php echo $class; ?> style="padding-right:5px">
                    <button class="radius non_important_text red_button text_white" id="btnClear"> Clear </button>
                </div>
                <div class=<?php echo $class; ?>>
                    <button class="radius non_important_text buttons text_white" id="btnSearch"> Search </button>
                </div>
                <?php if($_GET['userId'] == 0){
                    echo '<div class='.$class.' style="padding-left:5px">
                    <button class="radius non_important_text excel_buttons text_white" '.$link.' id="btnExcel"> Export Excel </button>
                </div>';
                } ?>
                
            </div>
        </td>
    </tr>

</table>

<div class="divider"></div>
<div class="divider"></div>

<div class="divider"></div>
<div class="divider"></div>

<div id="ordersDiv">

</div>

<!-- The Modal -->
<div id="myModal" class="modal">

  <!-- Modal content -->
  <div class="modal-content">
    <span class="close">&times;</span>
    <div style="margin-top:30px" id="content"></div>
  </div>

</div>

<link href='https://fonts.googleapis.com/css?family=Open Sans' rel='stylesheet'>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.2.3/themes/airbnb.css">
<script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.3.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.2.3/flatpickr.min.js"></script>


<script>

    var filter_cust,filter_salesperson,custSelection,offset = 0;

    var modal = document.getElementById("myModal");
    
    var span = document.getElementsByClassName("close")[0];

    function showModal(param){
        modal.style.display = "block";
        var view = '';
        for (let i = 0; i < param.length; i++) {
            const element = param[i];
            view += `<div class="box">
                        <div class="row" style="padding:0px;height:20px;">
                            <div class="column-box" style="border:0px">
                                <p style="color:grey;font-size:13px;margin:0px;">${element.product_code}</p>
                            </div>
                            <div class="column-box" style="border:0px">
                                <p style="color:#147efb;margin:0px;font-weight:bold;font-size:13px;text-align:right">${element.unit_uom}</p>
                            </div>
                        </div>
                        <p style="color:#000;font-size:13px;font-weight:bold;margin:0px;line-height:0.8em;">${element.product_name}</p>
                        `;
                
                    if(element.remark){
                        view += `<p style="font-size:13px;color:grey;margin:0px;font-weight:bold;">${element.remark}</p>`;
                    }  
                    
                    if(element.shelf_location){
                        view += `<p style="font-size:13px;color:#147efb;margin:0px;text-decoration:underline;font-weight:bold;">${element.shelf_location}</p>`;
                    }
                        
            

            if(element.expiry_date){
                view += `<p style="font-size:13px;color:#000;color:grey;margin:0px;font-weight:bold;">Exp Date: <label style="color:red">${element.expiry_date}</label></p>`;
            }

            if(element.foc_qty > 0){
                view += `<p style="font-size:13px;color:#147efb;margin:0px;font-weight:bold;">FOC QTY: ${element.foc_qty}</p>`;
            }
            
            if(element.markdown_price){
                view += `<p style="font-size:13px;color:#000;margin:0px;font-weight:bold;text-decoration:underline;">Markdown price: ${element.markdown_price}</p>`;
            }

            view += `<div class="row" style="display:inline-block;width:100%;">
                        <center style="font-size:13px;color:black;font-weight:bold;float:left;width:50%;">Sug. Transfer ${element.suggest_return}</center>
                        <center style="font-size:13px;color:black;font-weight:bold;float:left;width:50%;">Sug. Return ${element.suggest_transfer}</center>
                    </div>`;

            view += `<div class="row" style="margin-top:5px">
                            <div class="column-box" style="border-left:0px">
                                <center style="color:black;font-weight:bold;"><label style="font-size:12px;color:grey">Cur.</label> ${element.current_quantity}</center>
                            </div>
                            <div class="column-box" style="border-right:0px">
                                <center style="color:#147efb;font-weight:bold;"><label style="font-size:12px;color:grey">Sug.</label> ${element.suggested_quantity}</center>
                            </div>
                        </div>
                    </div>`;
        }
        document.getElementById('content').innerHTML = view;
    }
    
    span.onclick = function() {
        modal.style.display = "none";
    }
    
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
    class Order{
        constructor(
            userId,
            client,
            orderFrom,
            orderTo,
            customer_id,
            backlink,
            salesperson_id,
            customer_name,
            customer_code,
            offset

        ){
            this.userId = userId;
            this.client = client;
            this.orderFrom = orderFrom;
            this.orderTo = orderTo;
            this.customerId = customer_id;
            this.backlink   = backlink;
            this.salesperson_id = salesperson_id;
            this.customer_name  = customer_name;
            this.customer_code  = customer_code;
            this.offset = offset;
        }
    }

    function showMessage(msg){
        alert(msg);
    }

    $(document).ready(function(){

        $("#customerDropDwn").removeAttr("style");

        // today's orders

        var backlink             = window.location.href;

        // today's orders

        var customer_name = "", customer_id = "", orderDatePicker_val = "", deliveryDatePicker_val = "";


        var userId                  = '<?php echo $userId; ?>';
        var client                  = '<?php echo $client; ?>';
        var salesperson_id          = '<?php echo $salespersonid; ?>';
        var customer_code           = '<?php echo $cust_code; ?>';

        var isSalesPersonView = userId>0;
        var isCustomerView    = '<?php echo $isCustomerView; ?>';

        if(isSalesPersonView){
            loadCustomers (client,salesperson_id);
        }

        var orderFrom           = phpSessionExists('_odate_from');
        var orderTo             = phpSessionExists('_odate_to');

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
            (userId,client,orderFrom,orderTo,customer_id,backlink,salesperson_id,customer_name,customer_code,0)
        );

        $(window).scroll(function () {
            localStorage.setItem("transfer_order_scrollTo", $(window).scrollTop());
            if ($(document).height() <= $(window).scrollTop() + $(window).height()){
                var userId              = '<?php echo $userId; ?>';
                var client              = '<?php echo $client; ?>';

                let orderDate_arr       = orderDatePicker_val.split("to");

                var orderFrom           = orderDate_arr[0];
                var orderTo             = orderDate_arr[1];

                    offset              += 20;

                loadOrders(new Order
                    (userId,client,orderFrom,orderTo,customer_id,backlink,salesperson_id,customer_name,customer_code,offset)
                    ,true);
            }
        });

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
            }
        });
        let clicks = 0;
        $("#rangeDateFrom").click(function(){
            clicks += 1;
            if(clicks%2==0 && clicks>0){
                orderDatePicker.close();
            }
        });

        let salesperson_name;

        $(".choosenCustomer").click(function() {
            customer_name = $(this).attr("data-name");
            customer_id   = $(this).attr("data-value");

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

            customer_id='';
            $("#custSelection").val("Choose Customer");

            salesperson_name = $(this).attr("data-name");
            salesperson_id = $(this).attr("data-value");

            $("#salespersonSelection").val(salesperson_name);

            loadCustomers (client,salesperson_id);
            toggle_sp_selection();
        });

        $("#btnSearch").click(function() {

            var userId              = '<?php echo $userId; ?>';
            var client              = '<?php echo $client; ?>';

            let orderDate_arr       = orderDatePicker_val.split("to");

            var orderFrom           = orderDate_arr[0];
            var orderTo             = orderDate_arr[1];

            loadOrders(new Order
                (userId,client,orderFrom,orderTo,customer_id,backlink,salesperson_id,customer_name,customer_code,0)
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

        $('select').on('change', function() {
            var userId              = '<?php echo $userId; ?>';
            var client              = '<?php echo $client; ?>';

            let orderDate_arr       = orderDatePicker_val.split("to");

            var orderFrom           = orderDate_arr[0];
            var orderTo             = orderDate_arr[1];

            loadOrders(new Order (
                userId,client,orderFrom,orderTo,customer_id,salesperson_id,customer_name,customer_code,0)
            );
        });

        custSelection = function custSelection(value,name){
            customer_name = name;
            customer_id   = value;

            let tmp_cust_name = customer_name.length<29?customer_name:customer_name.substr(0,29)+' ...';
            $("#custSelection").val(tmp_cust_name);
            toggle_cust_selection();
        };

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

                            $("#customerDropDwn").append(`<a class="choosenCustomer" onclick="custSelection('${customerList[i].value}','${customerList[i].label}')">${customerList[i].label}</a>`);
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

            cancel          = String(+cancel);

            var orderType   =''; // $('#orderType').val();

            var doc_type    = '<?php echo $_GET["doc"]?>';
            
            var data = encodeURIComponent('{"searchStock_data" :{ "salespersonId":"' + _.userId + '","showCancel":"' + cancel
                + '","dateFrom":"' + _.orderFrom + '","dateTo":"' + _.orderTo + '","customer_status":"' + _.customerId
                + '","client":"' + _.client + '","salesperson_id":"' + _.salesperson_id + '","doc":"' + doc_type + '","offset":"' + offset + '","backlink":"'+_.backlink+'","customer_name":"'+_.customer_name+'","customer_code":"'+_.customer_code+'"}}');
            localStorage.setItem("stockTakeData", data);
            
            jQuery.ajax({
                type: "POST",
                url: "./route.php",
                data: "data=" + data + "&action=getStockWithView",
                success: function (msg) {

                    $('#ordersDiv').empty();

                    //msg = isJSONparsable(msg);
                    //warn(msg);
                    let views = msg;

                    if(typeof views == "undefined"){
                        // cause views can be array as well with no data
                        views = "";
                    }

                    if ((views.length == 0) && btnSearchClicked){
                        // $("#ordersDiv").append(msg.query);
                        $("#ordersDiv").append("<p class='no_orders'>No record available</p>");
                    }else if( views.length == 0 ){
                        $("#ordersDiv").append("<p class='no_orders'>No record today</p>");
                    }else{
                        $("#ordersDiv").append(views);
                    }
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
                    temp= new Date().toJSON().slice(0,10);
                }
            }
            if(key === '_odate_to'){
                temp= "<?php echo $_SESSION['_odate_to'] ?>";
                if (temp==='') {
                    temp= new Date().toJSON().slice(0,10);
                }
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
</script>


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

    .excel_buttons {
        color:#e8ebef;
        border: 1px solid #ffa500;
        background: #ffa500;
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
        font-size: 13px;
        color:rgba(0,0,0,0.4);
    }
    .message {
        color: black;
        text-align:left;
        font-size: 14px;
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

    .four_column {
        float: left;
        width: 25%;
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
    /* The Modal (background) */
    .modal {
        display: none; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: 1; /* Sit on top */
        padding-top: 10px; /* Location of the box */
        left: 0;
        top: 0;
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        overflow: auto; /* Enable scroll if needed */
        background-color: rgb(0,0,0); /* Fallback color */
        background-color: rgba(0,0,0,0.4); /* Black w/ opacity */;
        border-radius:3px;
    }

    /* Modal Content */
    .modal-content {
        background-color: #fefefe;
        margin: auto;
        padding: 5px;
        border-radius:3px;
        width: 95%;
    }

    /* The Close Button */
    .close {
        color: #aaaaaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: #000;
        text-decoration: none;
        cursor: pointer;
    }

    p {
        display: block;
        margin-block-start: 0.25em;
        margin-block-end: 0.25em;
        margin-inline-start: 0.5px;
        margin-inline-end: 0.5px;
        margin: 3px;
        padding: 2px;
    }
    .column-box {
        float: left;
        width: 50%;
        padding-top: 5px;
        padding-bottom: 5px;
        border-style: solid;
        border-width: 1px 1px 0px 1px;
        border-color:lightgrey;
        border-radius:1px;
    }
    .packing-status {
        font-size: 14px;
        text-color:grey;
    }
    .box{
    	border-style: solid;
        border-width: 1px 1px 1px 1px;
        border-color:lightgrey;
        border-radius:1px;
        padding:0px;
        margin-top: 5px;
        border-radius:3px;
    }
</style>
</body>
</html>
