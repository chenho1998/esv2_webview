<!DOCTYPE html>
<html lang="en">
<?php
session_start();
$client = '';
$userId = '';
if(isset($_GET['userId']) && isset($_GET['client'])){
    $client         = $_GET['client'];
    $userId         = $_GET['userId'];
}else{
    //header('location:Errorpage.php');
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
    <tr>
        <td align="middle">
            <div class="row">
                <div class="column" style="padding-right:5px">
                    <button onclick="clearSession()" class="radius non_important_text red_button text_white" id="btnClear"> Clear </button>
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

<div id="ordersDiv">

</div>

<link href='https://fonts.googleapis.com/css?family=Open Sans' rel='stylesheet'>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.2.3/flatpickr.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.2.3/themes/airbnb.css">
<script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.3.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.2.3/flatpickr.js"></script>

<script>
$(document).ready(function(){
    class Stock{
        constructor(
            date_from,
            date_to,
            client,
            user_id
        ){
            this.date_from = date_from;
            this.date_to = date_to;
            this.client = client;
            this.user_id = user_id;
        }
    }

    var orderFrom           = phpSessionExists('_sdate_from');
    var orderTo             = phpSessionExists('_sdate_to');

    var userId              = '<?php echo $userId; ?>';
    var client              = '<?php echo $client; ?>';

    var orderDatePicker_val = '';

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
    // let orderDate_arr       = orderDatePicker_val.split("to");

    // var orderFrom           = orderDate_arr[0];
    // var orderTo             = orderDate_arr[1];


    loadItems(new Stock(orderFrom,orderTo,client,userId));

    $( "#btnSearch" ).click(function() {
        let orderDate_arr       = orderDatePicker_val.split("to");

        var orderFrom           = orderDate_arr[0];
        var orderTo             = orderDate_arr[1];
        loadItems(new Stock(orderFrom,orderTo,client,userId));
    });

});

function loadItems(_) {
    var data = encodeURIComponent('{"searchStock_data" :{ "salespersonId":"' + _.user_id + '","client":"' + _.client + '","dateFrom":"' + _.date_from + '","dateTo":"' + _.date_to + '"}}');
    debugger
    jQuery.ajax({
        type: "POST",
        url: "./route.php",
        data: "data=" + data + "&action=getNoStockWithView",
        success: function (msg) {
           
           console.log(msg);
            $('#ordersDiv').empty();

            msg = isJSONparsable(msg);
            
            if(msg){
                for(let view in msg){
                    $("#ordersDiv").append(view);
                    let rows = msg[view];
                    for(let i = 0, len = rows.length; i < len; i++){
                        $("#ordersDiv").append(rows[i]);
                    }
                }
            }

            if(msg.length==0){
                
                $("#ordersDiv").append("<p class='no_orders'>No Alerts Available</p>");
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            console.warn(xhr);
            $("#ordersDiv").append("<p class='no_orders'>Technical error. Please contact support</p>");
        }
    });
}
function informedCustomer(custCode,userId,client){
    var data = encodeURIComponent('{"setAcknowledgeStatus_data" :{ "custCode":"' + custCode + '","userId":"'
        + userId + '","client":"' + client + '"}}');

        console.log('{"setAcknowledgeStatus_data" :{ "custCode":"' + custCode + '","userId":"'
            + userId + '","client":"' + client + '"}}');

    jQuery.ajax({
        type: "POST",
        url: "./route.php",
        data: "data=" + data + "&action=setAcknowledgeStatus",
        success: function (msg) {
            // console.log(msg);
            var decodedJson = JSON.parse(msg);

            var cust_code = decodedJson.custCode.split('/').join('---');

            if (decodedJson.result == 0) {
                location.reload(); 
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {

        }
    });
}
function clearSession(){
    $("#rangeDateFrom").attr("placeholder", "Order Date From - Order Date To");

    $.ajax({
        url: "clearSession.php?action=clearSessionStock",
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
function phpSessionExists(key){
        let temp='';

        if(key=='_sdate_from'){
            temp= "<?php echo $_SESSION['_sdate_from'] ?>";
            if (temp==='') {
                temp= new Date().toJSON().slice(0,10);
            }
        }
        if(key=='_sdate_to'){
            temp= "<?php echo $_SESSION['_sdate_to'] ?>";
            if (temp==='') {
                temp= new Date().toJSON().slice(0,10);
            }
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
.description {
    font-size: 12px;
    color:gray;
    text-align:left;
    margin-block-start: 0em;
    margin-block-end: 0.5em;

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
    min-height: 30px;
    padding-left: 5px;
    padding-right: 5px;
    color:white;
    font-weight: bold;
    font-size:14px;
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
  width: 50%;
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
