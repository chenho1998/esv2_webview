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
	
	<link href='https://fonts.googleapis.com/css?family=Open Sans' rel='stylesheet'>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.2.3/flatpickr.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.2.3/themes/airbnb.css">
	<script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.3.1.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.2.3/flatpickr.js"></script>
	
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
	
	#customerSeachInp:focus {outline: 3px solid #ddd;}
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
	  width: 49.99%;
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
</head>

<body>
	
	<table  style="border:0px solid #ccc; width:100%;">
		<tr>
			<td style="align:left" colspan="3">
			<form id="input-form">
				<input placeholder="Payment Date From - Payment Date To" id="rangeDateFrom" type="button" class="button_add radius text" id="orderDate"/>
				<div class="divider"></div>
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

		<tr>
			<td align="middle">
				<div class="row">
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
	
	<div id="paymentsDiv"></div>
	
	<?php /* Script for the Check In List Data */ ?>
	<script type="text/javascript">
	function checkedPayment(paymentId, client, userId){
		var data = encodeURIComponent('{"payment_data" :{ "payment_id":"' + paymentId + '","client":"' + client + '","salesperson_id":"' + userId +'"}}');

		jQuery.ajax({
			type: "POST",
			url: "./route.php",
			data: "data=" + data + "&action=checkedPayment",
			success: function (msg)
			{		
				//debugger
				msg = isJSONparsable(msg);
				if(msg) {
					console.log(msg.data);
				}
			},
			error: function (xhr, ajaxOptions, thrownError) {
				console.warn(xhr);
				$("#paymentsDiv").append("<p class='no_orders'>Technical error. Please contact support</p>");
			}
		});
	}

	function uncheckedPayment(paymentId, client, userId){
		var data = encodeURIComponent('{"payment_data" :{ "payment_id":"' + paymentId + '","client":"' + client + '","salesperson_id":"' + userId +'"}}');

		jQuery.ajax({
			type: "POST",
			url: "./route.php",
			data: "data=" + data + "&action=uncheckedPayment",
			success: function (msg)
			{
				//debugger
				msg = isJSONparsable(msg);
				if(msg) {
					console.log(msg.data);
				}
			},
			error: function (xhr, ajaxOptions, thrownError) {
				console.warn(xhr);
				$("#paymentsDiv").append("<p class='no_orders'>Technical error. Please contact support</p>");
			}
		});
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
        };

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
	};
	
	function phpSessionExists(key){
		let temp='';

		if(key=='_date_from'){
			temp= "<?php echo $_SESSION['_date_from'] ?>";
			if (temp==='') {
				temp= new Date().toJSON().slice(0,10);
			}
		}
		if(key=='_date_to'){
			temp= "<?php echo $_SESSION['_date_to'] ?>";
			if (temp==='') {
				temp= new Date().toJSON().slice(0,10);
			}
		}
		
		if(key=='_cust_id'){
			temp= "<?php echo $_SESSION['_cust_id'] ?>";
		}
		
		if(key=='_salesperson_id'){
			temp= "<?php echo $_SESSION['_salesperson_id'] ?>";
		}

		return temp;
	}
	
	// Alert The Message
	function showMessage(msg){
		alert(msg);
	}
	// Make it Global
	var customer_name = "", customer_id = "", paymentDatePicker_val = "", temp_si='', salesperson="",custSelection,isJSONparsable;

	$(document).ready(function(){
		$("#customerDropDwn").removeAttr("style");
		
		// Today Check In
		
		var today = new Date().toJSON().slice(0,10);
		
		var userId = '<?php echo $userId; ?>';
		var client = '<?php echo $client; ?>';
		var salesperson_id = '<?php echo $salespersonid; ?>';
		let paymentDate_arr = paymentDatePicker_val.split("to");
		
		var paymentFrom = phpSessionExists('_date_from');
		var paymentTo = phpSessionExists('_date_to');
		var customer_code = phpSessionExists('_cust_id');
		//salesperson_id = phpSessionExists('_salesperson_id');
        var isSalesPersonView = userId>0;
		if(isSalesPersonView){
            loadCustomers (client,salesperson_id);
        }

		loadPayments(new Payment(userId,client,paymentFrom,paymentTo,customer_code,salesperson_id));
		
		const paymentDatePicker = flatpickr("#rangeDateFrom",{
			mode: 'range',
			dateFormat: "Y-m-d",
			disableMobile: "false",
			defaultDate:[paymentFrom,paymentTo],
			onChange: function(selectedDates, dateStr, instance) {
				paymentDatePicker_val = dateStr;
			},
			onReady: function(dObj, dStr, fp, dayElem){
				paymentDatePicker_val = dStr;
			}
		});
		
		let clicks = 0;
		$("#rangeDateFrom").click(function(){
			clicks += 1;
			if(clicks%2==0 && clicks>0){
				paymentDatePicker.close();
			}
		});

		$(".choosenCustomer").click(function() {
			customer_name = $(this).attr("data-name");
			// customer_code   = $(this).attr("data-code");

			$("#custSelection").val(customer_name);
			toggle_cust_selection();
		});

		$("#custSelection").click(function() {
			if(!isSalesPersonView){
				var selectedSalesperson = document.getElementById('salespersonSelection').value;

				if(selectedSalesperson =='Choose Salesperson'){
					alert('Please select a salesperson');
					return;
				}
				toggle_cust_selection();
			}else{
				toggle_cust_selection();
			}
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
			reloadOrderBaseOnChanges(customer_code,salesperson_id,true);
		});
		
		$("#btnClear").click(function() {
			$('#input-form')[0].reset();
			$('#custSelection').val("Choose Customer");
			$('#salespersonSelection').val("Choose Salesperson");
			$("#rangeDateFrom").attr("placeholder", "Payment Date From - Payment Date To");
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
			reloadOrderBaseOnChanges(customer_code,salesperson_id,true);
		});
		
		// Check Data Exists
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
	
	isJSONparsable  = function isJSONparsable(value){
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
	

    custSelection = function custSelection(value,name){
            customer_name = name;
            customer_code   = value;

            let tmp_cust_name = customer_name.length<29?customer_name:customer_name.substr(0,29)+' ...';
            $("#custSelection").val(tmp_cust_name);
			$("#custSelection").attr('data-code',value);
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
	});
	
	class Payment{
		constructor(
			userId,
			client,
			paymentFrom,
			paymentTo,
			customer_code,
			salesperson_id
		){
			this.userId = userId;
			this.client = client;
			this.paymentFrom = paymentFrom;
			this.paymentTo = paymentTo;
			this.customer_code = customer_code;
			this.salesperson_id = salesperson_id;
		}
	}
	
	function reloadOrderBaseOnChanges(customer_code,salesperson_id,btnSearchClicked=false) {
		var userId = '<?php echo $userId; ?>';
		var client = '<?php echo $client; ?>';

		let paymentDate_arr = paymentDatePicker_val.split("to");

		var paymentFrom = paymentDate_arr[0];
		var paymentTo = paymentDate_arr[1];
		loadPayments(new Payment
			(userId,client,paymentFrom,paymentTo,customer_code,salesperson_id)
		,btnSearchClicked);
	}
	
	function loadPayments(_, btnSearchClicked = false) {
		console.log(JSON.stringify(_));
		var showCancel = 2;
		var paymentStatus = 1;
		var data = encodeURIComponent('{"searchPayments_data" :{ "salespersonId":"' + _.salesperson_id + '","showCancel":"' + showCancel
		+ '","dateFrom":"' + _.paymentFrom + '","dateTo":"' + _.paymentTo + '","payment_status":"'
		+ paymentStatus + '","customer_code":"' + _.customer_code
		+ '","client":"' + _.client + '","userId":"' + _.userId + '"}}');

		jQuery.ajax({
			type: "POST",
			url: "./route.php",
			data: "data=" + data + "&action=getPaymentWithView",
			success: function (msg)
			{

				$("#paymentsDiv").empty();
				msg = isJSONparsable(msg);
				// console.log(msg.sql);
				// console.log(msg.data);
				if(msg) {
					let views = msg.data;
					if(typeof views == "undefined"){
						// cause views can be array as well with no data
						views = "";
					}
					
					if ((views.length == 0) && btnSearchClicked){
						// $("#checkInsDiv").append(msg.query);
						$("#paymentsDiv").append("<p class='no_orders'>No payment available</p>");
					}else if( views.length == 0 ){
						$("#paymentsDiv").append("<p class='no_orders'>No payment today</p>");
					}else{
						if(views instanceof Array){
							for(let i = 0, len = views.length; i < len; i++){
								let view = views[i];
								$("#paymentsDiv").append(view);
							}
						}
					}
					
				}
			},
			error: function (xhr, ajaxOptions, thrownError) {
				console.warn(xhr);
				$("#paymentsDiv").append("<p class='no_orders'>Technical error. Please contact support</p>");
			}
		});
	}
	
	</script>
</body>
</html>
