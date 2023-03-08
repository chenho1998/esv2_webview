<?php 
session_start();
$client = '';
$userId = '';
require_once('getDoJobStatus.php');
if(isset($_GET['userId']) && isset($_GET['client'])){
    $client = $_GET['client'];
    $userId = $_GET['userId'];
}else{
    header('location:Errorpage.php');
}
$do_types = getDoJobStatus($client);
$sageClient = $client == 'sendoichi';
?>
<!DOCTYPE html>
<html lang="en">
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
                <input placeholder="Date From - Date To" id="rangeDateFrom" type="button" class="button_add radius text" id="delDate"/>
            </form>
        </td>
    </tr>
    <tr style="max-width:100%;overflow: hidden;text-overflow: ellipsis; ">
        <td>
            <input style="max-width:100%;" id="custSelection" value="Search by Customer" type="button" class="customer_selection_button radius text_white" data-value="0"/>
            <div id="customerDropDwn" style="display:none;" class="dropdown-content radius">
                <input type="text" placeholder="Search.." id="customerSeachInp" onkeyup="filter_cust()">
            </div>
        </td>
    </tr>
    <tr style="max-width:100%;overflow: hidden;text-overflow: ellipsis; ">
        <td>
        <?php if($sageClient):?>
            <input id="deliveryStatus" value="Delivery Status" type="button" class="customer_selection_button radius text_white" data-value="0"/>
            <div id="deliveryStatusDropDwn" class="dropdown-content radius" style="display:none;height:auto;">
                <?php foreach ($do_types as $type =>$arr): ?>
                    <label style="font-size:14px;color:grey;margin-left:10px;margin-top:10px"><?php echo $type?></label>
                    <?php for($i = 0; $i < count($arr); $i++): ?>
                        <a class="deliveryStatus" 
                            onclick="delStatusSelection('<?php echo $type.'-'.$arr[$i];?>','<?php echo $type.'-'.$arr[$i];?>')">
                            <?php echo $arr[$i];?>
                        </a>
                    <?php endfor;?>
                <?php endforeach;?>
            </div>
        <?php endif;?>
        <?php if(!$sageClient):?>
            <input style="max-width:100%;" id="deliveryStatus" value="Delivery Status" type="button" class="customer_selection_button radius text_white" data-value="0"/>
            <div id="deliveryStatusDropDwn" class="dropdown-content radius" style="display:none;height:auto;">
                <a class="deliveryStatus" 
                    onclick="delStatusSelection('self','Self Collect')">Self Collect</a>
                <a class="deliveryStatus" 
                    onclick="delStatusSelection('waiting','Waiting for arrangement')">Waiting for arrangement</a>
                <a class="deliveryStatus" 
                    onclick="delStatusSelection('pending','Pending delivery')">Pending delivery</a>
                <a class="deliveryStatus" 
                    onclick="delStatusSelection('progress','Delivery in progress')">Delivery in progress</a>
                <a class="deliveryStatus" 
                    onclick="delStatusSelection('completed','Completed delivery')">Completed delivery</a>
                <a class="deliveryStatus" 
                    onclick="delStatusSelection('cancelled','Cancelled delivery')">Cancelled delivery</a>
            </div>
        <?php endif;?>
        </td>
    </tr>
    <tr>
        <td align="middle" style="padding-top: 5px;">
            <div class="row">
                <div class="column-half" style="padding-right:5px">
                    <button disabled class="radius non_important_text buttons-disable text_white" id="btnClear"> Clear </button>
                </div>
                <div class="column-half">
                    <button onclick="loadDeliveryStatus()" disabled class="radius non_important_text buttons-disable text_white" id="btnSearch"> Search </button>
                </div>
            </div>
        </td>
    </tr>
</table>
<div class="divider"></div>
<div class="divider"></div>

<div class="divider"></div>
<div class="divider"></div>

<div id="chopModal" class="modal">
  <div class="modal-content">
    <span id="chopClose" class="close">&times;</span>
    <p>Update Chop Status</p><br>
    <input type="text" id="chop_id" style="display:none;" value=""/>
    <label for="chop_remark">Chop Remark:</label>
    <input type="text" id="chop_remark" name="chop_remark" placeholder="Remark..."><br><br>
    <button style="border-radius:5px;border:0px solid black; background-color:green; color:#FFF; padding:5px; font-size:14px;" onclick="updateChopModal()">Chopped</button>
    <button style="border-radius:5px;border:0px solid black; background-color:red; color:#FFF; padding:5px; font-size:14px;"  onclick="closeChopModal()">Cancel</button>
  </div>
</div>
<div id="documentDiv">

</div>

<link href='https://fonts.googleapis.com/css?family=Open Sans' rel='stylesheet'>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.2.3/themes/airbnb.css">
<script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.3.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.2.3/flatpickr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery.redirect@1.1.4/jquery.redirect.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8.5.0/dist/sweetalert2.all.min.js"></script>
<script>
var filter_cust, custSelection, loadDeliveryStatus, delDatePicker_val, validDate = false;
function redirectTo(link){
    var nLink = String(link);
    window.location.href = nLink;
}
$(document).ready(function(){
    $('#customerSeachInp').val("");
    var salesperson_id = '<?php echo $userId; ?>';
    var client = '<?php echo $client;?>';
    var delDateFrom = '<?php echo $_SESSION['d_date_from'];?>';
    var delDateTo = '<?php echo $_SESSION['d_date_to'];?>';

    loadCustomers(client, salesperson_id);

    const delDatePicker = flatpickr("#rangeDateFrom",{
        mode: 'range',
        dateFormat: "Y-m-d",
        disableMobile: "false",
        defaultDate:[delDateFrom,delDateTo],
        onChange: function(selectedDates, dateStr, instance) {
            delDatePicker_val = dateStr;
            if(String(dateStr).split("to").length == 2){
                enableSections();
                validDate = true;
            }
        },
        onReady: function(dObj, dStr, fp, dayElem){
            delDatePicker_val = dStr;
            if(String(dStr).split("to").length == 2){
                enableSections();
                validDate = true;
            }
        }
    });
    let clicks = 0;
    $("#rangeDateFrom").click(function(){
        clicks += 1;
        if(clicks%2==0 && clicks>0){
            delDatePicker.close();
        }
    });

    warn(delDatePicker_val);

    $("#custSelection").click(function() {
        toggle_cust_selection();
    });

    $("#deliveryStatus").click(function() {
        toggle_delivery_status_selection();
    });

    $("#btnClear").click(function() {
        $('#custSelection').val("Search by Customer");
        $('#deliveryStatus').val("Delivery Status");
        $('#customerSeachInp').val("");
        $("#custSelection").attr("data-value","0");
        $("#deliveryStatus").attr("data-value","0");
        $("#btnSearch").prop("disabled",true);
        $("#btnSearch").removeClass("buttons");
        $("#btnSearch").addClass("buttons-disable");
        $("#btnClear").prop("disabled",true);
        $("#btnClear").removeClass("red_button");
        $("#btnClear").addClass("buttons-disable");
        $('#documentDiv').empty();

        $.ajax({
            url: "clearSession.php?action=clearSessionDelivery",
            type: "POST",
            contentType: false,
            cache: false,
            processData:false,
            success: function(response){
                
                location.reload();
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.warn(xhr);
            }
        });
    });

    function loadCustomers (client,userId){
        var data = encodeURIComponent('{"client":"' + client + '","salesperson_id":"' + userId +'"}');
        jQuery.ajax({
            type: "POST",
            url: "./route.php",
            data: "data=" + data + "&action=loadCustomers_v2",
            success: function (msg) {
                msg = isJSONparsable(msg);
                var savedCustCode = '<?php echo $_SESSION['d_cust_code'];?>';
                var savedDelStatus = '<?php echo $_SESSION['d_status'];?>';
                debugger;
                if(msg){
                    customerList=msg.data;
                    cust_len=customerList.length;
                    $(".choosenCustomer").remove();

                    var selectedCust_code = '', selectedCust_name = '';
                    for(let i=0; i<cust_len; i++){
                        if(savedCustCode == customerList[i].value){
                            selectedCust_code = customerList[i].value;
                            selectedCust_name = customerList[i].label;
                        }
                        $("#customerDropDwn").append(`<a class="choosenCustomer" onclick="custSelection('${customerList[i].value}','${customerList[i].label}')">(<strong>${customerList[i].value}</strong>) ${customerList[i].label}</a>`);
                    }
                    var savedDelStatusName = '';
                    if(savedDelStatus == 'waiting'){
                        savedDelStatusName = 'Waiting for arrangement';
                    }
                    if(savedDelStatus == 'self'){
                        savedDelStatusName = 'Self Collect';
                    }
                    if(savedDelStatus == 'pending'){
                        savedDelStatusName = 'Pending delivery';
                    }
                    if(savedDelStatus == 'progress'){
                        savedDelStatusName = 'Delivery in progress';
                    }
                    if(savedDelStatus == 'completed'){
                        savedDelStatusName = 'Completed delivery';
                    }
                    if(savedDelStatus == 'cancelled'){
                        savedDelStatusName = 'Cancelled delivery';
                    }
                    var didnotTrigger = true;
                    if(selectedCust_code){
                        custSelection(selectedCust_code,selectedCust_name,false);
                        loadDeliveryStatus();

                        didnotTrigger = false;
                    }
                    if(savedDelStatusName){
                        delStatusSelection(savedDelStatus, savedDelStatusName,false);
                        if(didnotTrigger){
                            loadDeliveryStatus();
                            didnotTrigger = false;
                        }
                    }
                    if(didnotTrigger && validDate){
                        loadDeliveryStatus();
                    }
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.warn(xhr);
            }

        });
    }

    loadDeliveryStatus = function loadDeliveryStatus(){
        $('#documentDiv').empty();
        $('#documentDiv').append('<div style="margin-left:-35px" class=" no_orders loader"></div>');

        var cust_code = $("#custSelection").attr("data-value");
        var delivery_status = $("#deliveryStatus").attr("data-value");
        var client = "<?php echo $client; ?>";
        var userId = "<?php echo $userId; ?>";
        var date_split = String(delDatePicker_val).split('to');
        var date_from = '', date_to = '';
        if(date_split.length == 2){
            date_from = date_split[0];
            date_to = date_split[1];
        }
        var isSage = $("#showCancelBox") ? $("#showCancelBox").is(':checked') : 0;
        isSage          = String((isSage | 0));

        var data = encodeURIComponent('{"client":"' + client + '","salesperson_id":"' + userId +'","cust_code":"' + cust_code +'","delivery_status":"' + delivery_status +'","date_from":"' + date_from +'","date_to":"' + date_to +'","is_sage":"' + isSage +'"}');
        jQuery.ajax({
            type: "POST",
            url: "./route.php",
            data: "getDeliveryStatusView_data=" + data + "&action=getDeliveryStatusView",
            success: function (msg) {
                debugger;
                $(".loader").remove();
                msg = isJSONparsable(msg);
                if(msg){
                    let views = msg.data;

                    if(typeof views == "undefined"){
                        $("#documentDiv").append("<p class='no_orders'>No result found</p>");
                    }

                    if( !views ){
                        $("#documentDiv").append("<p class='no_orders'>No result found</p>");
                    }else{
                        $("#documentDiv").append(views);
                    }
                }else{
                    warn("Delivery api error");
                    $("#documentDiv").append("<p class='no_orders'>No result found</p>");
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.warn(xhr);
                $("#documentDiv").append("<p class='no_orders'>Technical error. Please contact support</p>");
            }

        });
    }
    custSelection = function custSelection(value,name, toggle = true){
        let tmp_cust_name = name.length<29?name:name.substr(0,29)+' ...';
        $("#custSelection").val(tmp_cust_name);
        $("#custSelection").attr("data-value",value);
        enableSections();
        if(toggle){
            toggle_cust_selection();
        }
    };
    delStatusSelection = function delStatusSelection(value,name, toggle = true){
        $("#deliveryStatus").val(name);
        $("#deliveryStatus").attr("data-value",value);
        enableSections();
        if(toggle){
            toggle_delivery_status_selection();
        }
    };

    function enableSections(){
        $("#btnSearch").prop("disabled",false);
        $("#btnSearch").addClass("buttons");
        $("#btnSearch").removeClass("buttons-disable");
        $("#btnClear").prop("disabled",false);
        $("#btnClear").addClass("red_button");
        $("#btnClear").removeClass("buttons-disable");
    }
    function warn(msg){
        console.warn(JSON.stringify(msg));
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
        $("#customerDropDwn").toggle();
    }
    function toggle_delivery_status_selection(){
        $("#deliveryStatusDropDwn").toggle();
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

    var chopModal = document.getElementById("chopModal");
    var chopClose = document.getElementById('chopClose');
    chopClose.onclick = function() {
        chopModal.style.display = "none";
    }
});

    function openChopModal(job_id){
        let chop_modal = document.getElementById("chopModal");
        chop_modal.style.display = "block";
        document.getElementById("chop_id").value = job_id;
    }

    function closeChopModal(){
        let chop_modal = document.getElementById("chopModal");
        chop_modal.style.display = "none";
        document.getElementById("chop_id").value = '';
    }

    function updateChopModal(){
        var salesperson_id = '<?php echo $userId; ?>';
        var client = '<?php echo $client;?>';
        var chop_id = $('#chop_id').val();
        var chop_remark = $('#chop_remark').val();

        var data = encodeURIComponent('{"updateChopStatus_data":{"client":"' + client + '","salesperson_id":"' + salesperson_id + '","chop_id":"' + chop_id +'","chop_remark":"' + chop_remark +'"}}');
        jQuery.ajax({
            type: "POST",
            url: "./route.php",
            data: "data=" + data + "&action=updateChopStatus",
            success: function (res) {
                debugger;
                res = JSON.parse(res);
                if(res){
                    alert(res['message']);
                    location.reload();
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.warn(xhr);
            }

        });
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
        height: auto;
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
        overflow: hidden;
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
    .buttons-disable {
        color: grey;
        border: 1px solid #e8ebef;
        background: #e8ebef;
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
    .job-row{
        height:auto;
        padding:5px;
        margin: 0px;
    }
    .job-row-text{
        color:gray;
        font-size: 13px;
        text-align:left;
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

    * {
        box-sizing: border-box;
    }

    .column {
        float: left;
        width: 33.3%;
        padding-top: 10px;
        padding-bottom: 10px;
        height: 35px;
    }
    .column-half{
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

    .footer {
        position: fixed;
        left: 0;
        bottom: 15px;
        width: 95%;
        background-color: white;
        border: 1px #147efb solid;
        border-radius: 5px;
        color: #147efb;
        font-weight: bold;
        text-align: center;
        margin-left: 10px;
    }
    .check {
        display: flex;
        justify-content: center;
        align-items: center;
        width:120px;
        margin-top: 5px;
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

    .modal {
        display: none; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: 1; /* Sit on top */
        left: 0;
        top: 0;
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        overflow: auto; /* Enable scroll if needed */
        background-color: rgb(0,0,0); /* Fallback color */
        background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
    }

    .modal-content {
        background-color: #fefefe;
        margin: 15% auto; /* 15% from the top and centered */
        padding: 20px;
        border: 1px solid #888;
        width: 80%; /* Could be more or less, depending on screen size */
    }
    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }
</style>
</body>
</html>