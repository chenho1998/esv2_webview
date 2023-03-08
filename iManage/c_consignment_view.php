<!DOCTYPE html>
<html lang="en">
<?php
session_start();
$client = '';
$userId = '';
if(isset($_GET['userId']) && isset($_GET['client'])){
    require_once('getCustomerList.php');

    $client         = $_GET['client'];
    $userId         = $_GET['userId'];
    $salespersonid  = $userId; 

    $param = array(
        "client"        =>$client,
        "salespersonid" =>$userId
    );

    $customer_data = getCustomerList($param);
    $customer_data = $customer_data["data"];
    $customer_len  = count($customer_data);
    
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
            <div class="divider"></div>
            <input placeholder="Delivery Date From - Delivery Date To" id="rangeDateTo" type="button" class="button_add radius text" id="deliveryDate"/>
        </form>
        </td>
    </tr>

    <?php
        $customerName="Choose Customer";

        if($_SESSION['_cust_id']){
            for($i = 0; $i < $customer_len; $i++){
                if ($customer_data[$i]['value'] == $_SESSION['_cust_id'])
                    $customerName= $customer_data[$i]['label'];
                if (strlen($customerName) > 30)
                $customerName = substr($customerName, 0, 29) . '...';
            }
        }
    ?>
    <tr style="max-width:100%;overflow: hidden;text-overflow: ellipsis; ">
        <td>
            
            <?php
                if($customer_len > 0):
            ?>
           
            <input style="max-width:100%;" id="custSelection" value="<?php echo $customerName ?>" type="button" class="customer_selection_button radius text_white" onclick="toggle_cust_selection()"/>

            <div id="customerDropDwn" style="display:none;" class="dropdown-content radius">

                <input type="text" placeholder="Search.." id="customerSeachInp" onkeyup="filter_cust()">

                <?php for($i = 0; $i < $customer_len; $i++):?>
                    <a class="choosenCustomer" data-value="<?php echo $customer_data[$i]['value']?>" data-name="<?php $str = $customer_data[$i]['label'];
                        if (strlen($str) > 30)
                            $str = substr($str, 0, 29) . '...';
                        echo $str;?>">

                        <?php echo $customer_data[$i]['label']?>
                    </a>
                <?php endfor;?>

            </div>
            <?php endif;?>
            <?php
                if(count($customer_data) === 0):
            ?>
            <input value="Error Loading Customer" type="button" class="customer_selection_button radius text_white"/>
            <?php endif;?>
        </td>
    </tr>


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

<div id="ordersDiv">

</div>

<link href='https://fonts.googleapis.com/css?family=Open Sans' rel='stylesheet'>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.2.3/flatpickr.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.2.3/themes/airbnb.css">
<script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.3.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.2.3/flatpickr.js"></script>
<script src="//cdn.jsdelivr.net/npm/jquery.marquee@1.5.0/jquery.marquee.min.js" type="text/javascript"></script>


<script>

    class Order{
        constructor(
            userId,
            client,
            orderFrom,
            orderTo,
            deliverFrom,
            deliverTo,
            customer_id,
            backlink
        ){
            this.userId = userId;
            this.client = client;
            this.orderFrom = orderFrom;
            this.orderTo = orderTo;
            this.deliverFrom = deliverFrom;
            this.deliverTo = deliverTo;
            this.customerId = customer_id;
            this.backlink   = backlink;
        }
    }

    function exists (data, property, datatype = "string") {
        var isNotValid = false;
        const has = Object.prototype.hasOwnProperty;
        if(typeof(obj) === "object" && obj != null){
            if(has.call(obj,property)){
                return obj[property] ? obj[property] : datatype === "string" ? "" : 0;
            }else{
                isNotValid = true;
            }
        }else{
            isNotValid = true;
        }
        if(isNotValid){
            if(datatype === "string"){
                return "";
            }else{
                return 0;
            }
        }
    }

    function showMessage(msg){
        alert(msg);
    }

    $(document).ready(function(){

        $(window).scroll(function () { 
            localStorage.setItem("consignment_scrollTo", $(window).scrollTop());
        })

        if(localStorage.getItem("consignment_scrollTo")){
            
            $("html, body").animate({ scrollTop: localStorage.getItem("consignment_scrollTo") });
        }

        var backlink             = window.location.href;



        $("#customerDropDwn").removeAttr("style");

        // today's orders

        var customer_name = "", customer_id = "", orderDatePicker_val = "", deliveryDatePicker_val = "",temp_si='';
        
        var today               = new Date().toJSON().slice(0,10);

        var userId              = '<?php echo $userId; ?>';
        var client              = '<?php echo $client; ?>';

        let orderDate_arr       = orderDatePicker_val.split("to");
        let deliveryDate_arr    = deliveryDatePicker_val.split("to");

        var orderFrom           = phpSessionExists('_odate_from');
        var orderTo             = phpSessionExists('_odate_to');

        var deliverFrom         = phpSessionExists('_ddate_from');
        var deliverTo           = phpSessionExists('_ddate_to');
        var customer_id         = phpSessionExists('_cust_id');
        var view_cancel         = phpSessionExists('_view_cancel');      

        if(view_cancel){
            $('#showCancelBox').prop('checked', true);
        }

        loadOrders(new Order
            (userId,client,orderFrom,orderTo,deliverFrom,deliverTo,customer_id,backlink)
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
            }
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
            onChange: function(selectedDates, dateStr, instance) {
                deliveryDatePicker_val = dateStr;
             },
             onReady: function(dObj, dStr, fp, dayElem){
                deliveryDatePicker_val = dStr;
             }

        });

        let deliveryClicks = 0;
        $("#rangeDateTo").click(function(){
            deliveryClicks += 1;
            if(deliveryClicks%2==0 && deliveryClicks>0){
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
            customer_id   = $(this).attr("data-value");

            $("#custSelection").val(customer_name);
            toggle_cust_selection();
        });

        $("#btnSearch").click(function() {

            var userId              = '<?php echo $userId; ?>';
            var client              = '<?php echo $client; ?>';

            let orderDate_arr       = orderDatePicker_val.split("to");
            let deliveryDate_arr    = deliveryDatePicker_val.split("to");

            var orderFrom           = orderDate_arr[0];
            var orderTo             = orderDate_arr[1];

            var deliverFrom         = deliveryDate_arr[0];
            var deliverTo           = deliveryDate_arr[1];

            loadOrders(new Order
                (userId,client,orderFrom,orderTo,deliverFrom,deliverTo,customer_id,backlink)
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
            let deliveryDate_arr    = deliveryDatePicker_val.split("to");

            var orderFrom           = orderDate_arr[0];
            var orderTo             = orderDate_arr[1];

            var deliverFrom         = deliveryDate_arr[0];
            var deliverTo           = deliveryDate_arr[1];

            loadOrders(new Order (
                    userId,client,orderFrom,orderTo,deliverFrom,deliverTo,customer_id
                )
            );
        });
    });

    function loadOrders(_, btnSearchClicked = false) {
        //console.log(JSON.stringify(_));
        var cancel      = $("#showCancelBox").is(':checked');

        cancel          = String((cancel | 0));

        var orderType   =''; // $('#orderType').val();

        var data = encodeURIComponent('{"searchConsignments_data" :{ "salespersonId":"' + _.userId + '","showCancel":"' + cancel
		+ '","dateFrom":"' + _.orderFrom + '","dateTo":"' + _.orderTo + '","order_status":"'
		+ orderType+ '","customer_status":"' + _.customerId
        + '","deliveryDateFrom":"' + _.deliverFrom + '","deliveryDateTo":"' + _.deliverTo + '","client":"' + _.client + '","backlink":"'+_.backlink+'"}}');


        jQuery.ajax({
            type: "POST",
            url: "./route.php",
            data: "data=" + data + "&action=getConsignmentWithView",
            success: function (msg) {
                //console.warn(msg);
                $('#ordersDiv').empty();

                msg = isJSONparsable(msg);
                if(msg){
                    //console.log(msg.query);
                    let views = msg.data;

                    if(typeof views == "undefined"){
                        // cause views can be array as well with no data
                        views = "";
                    }

                    if ((views.length == 0) && btnSearchClicked){
                         
                        $("#ordersDiv").append("<p class='no_orders'>No orders available</p>");
                    }else if( views.length == 0 ){
                        $("#ordersDiv").append("<p class='no_orders'>No orders today</p>");
                    }else{
                        if(views instanceof Array){
                            for(let i = 0, len = views.length; i < len; i++){
                                let view = views[i];
                                //$("#ordersDiv").append(msg.query);
                                $("#ordersDiv").append(view);
                                
                            }
                        }
                    }
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

    function filter_cust() {
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
    }

    function filter_salesperson() {
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
    }

    function phpSessionExists(key){
        let temp='';

        if(key=='_odate_from'){
            temp= "<?php echo $_SESSION['_odate_from'] ?>";
            if (temp==='') {
                temp= new Date().toJSON().slice(0,10);
            }
        }
        if(key=='_odate_to'){
            temp= "<?php echo $_SESSION['_odate_to'] ?>";
            if (temp==='') {
                temp= new Date().toJSON().slice(0,10);
            }
        }
        if(key=='_ddate_from'){
            temp= "<?php echo $_SESSION['_ddate_from'] ?>";
        }
        if(key=='_ddate_to'){
            temp= "<?php echo $_SESSION['_ddate_to'] ?>";
        }
        if(key=='_view_cancel'){
            temp= "<?php echo $_SESSION['_view_cancel'] ?>";
        }
        if(key=='_cust_id'){
            temp= "<?php echo $_SESSION['_cust_id'] ?>";
        }
        
        return temp;
    }
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
    font-size:13;
    color:grey;
    position: absolute;
    top: 50%;
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
</style>
</body>
</html>
