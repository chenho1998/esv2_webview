
<!DOCTYPE html>
<html lang="en">
<?php
session_start();
require_once('getOrderViewDetails.php');
$client = '';
$orderId = '';

if(isset($_GET['orderId']) && isset($_GET['client'])){
    $client = $_GET['client'];
    $orderId = $_GET['orderId'];
    $role_id = $_SESSION['role_id'];
    $user_id = $_SESSION['user_id'];
    $cancelCuttingDate = isset($_GET['cancelled']) ? $_GET['cancelled'] : '1';

    $object = new stdClass();
    $object->client = $client;
    $object->orderId = $orderId;
    $object->cancelled_cutting_date = $cancelCuttingDate;

    $settings = $_SESSION['settings'];
    $param = array(
        "client"  => $client,
        "orderId" => $orderId,
        "cancelled_cutting_date"=>$cancelCuttingDate,
        "settings"=>$settings
    );
    $order_details = getOrderViewDetails($param);
    $order_len=count($order_details);
    $udf_fields = $order_details['udf_fields'];
    $order_cutting_date = $order_details['order_cutting_date'];
    $order_cutting_exists = gettype($order_cutting_date)=='array'?true:false;
    $goback_link   = $_SESSION['_backlink'];
    

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
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link href='https://fonts.googleapis.com/css?family=Open Sans' rel='stylesheet'>
    <script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.3.1.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <?php 
        if($order_cutting_exists){
            echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8.5.0/dist/sweetalert2.all.min.js">
            </script><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.2.3/flatpickr.css">
            <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.2.3/flatpickr.js"></script>';
        }
    ?>

    <style>
    .modal {
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
    .modal-content {
    background-color: #fefefe;
    margin: auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
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

    .btn-delete {
        background-color: #ff552d; 
        border: 1px solid #ff552d; 
        color: white; 
        /* padding: 8px 24px;  */
        height: 30px;
        cursor: pointer; 
        border-radius: 3px;
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)
    }

    .btn-update {
        background-color: #3CB371; 
        border: 1px solid #3CB371; 
        color: white; 
        /* padding: 8px 24px;  */
        cursor: pointer; 
        border-radius: 3px;
        height: 30px;
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)
    }

    </style>

</head>
<body id="loading">

<div id="loadingSpinner" style="margin-left:-35px" class=" no_orders loader"></div>

<div class="site-wrapper">
    <main class="site-content">
        <?php
        if($order_details['views']){
            echo($order_details['views']);
        }
        ?>
        <?php if ($order_cutting_exists): ?>
            <?php if (empty($order_details['views'])): ?>
                <?php echo
                    '<div class="divider"></div>
                        <div class="limit-text-length text_white company-title" style="max-width:100%;height:25px;">
                            <center style="height:25px;margin-left:5px;margin-right:5px;"> '.$order_cutting_date[0]["cust_company_name"].' </center>
                        </div>
                    <div class="divider"></div>';
                ?>
            <?php else: ?>
        <?php endif; ?>
<div>

<?php if($role_id == 10): ?>
<div class="btn-group" style="width:100%">
    <button style="width:65%;" onclick="showModalOnClickEdit({
                    id:'0',
                    title:'',
                    remark:'',
                    task:'',
                    status:'',
                    active:'1'
                });">Add new</button>
    <button style="width:35%;background:rgb(255,85,45);border:rgb(255,85,45)" onclick="toggleCancelHideButton();">
        <?php echo $cancelCuttingDate == '1' ? 'View CXL' : 'Hide CXL' ?>
    </button>
</div>
<?php endif; ?>
<?php foreach($order_cutting_date as $row): ?>
    <div style="padding:5px;border-radius:3px;border:1px solid whitesmoke;margin:2px;opacity: <?php echo $row['active_status'] == '0' ? '0.4' : '1' ?>;">
        <div class="btn-group" style="width:100%">
            <label style="font-weight:bold;width:85%;color:<?php echo $row['active_status'] == '0' ? 'red' : '#147efb' ?>;font-size:14px">
                <?php 
                    echo $row['cutting_title'];
                ?>
            </label>
            <?php if($role_id == 10): ?>
            <button 
                onclick="showModalOnClickEdit({
                    id:'<?php echo $row['id'];?>',
                    title:'<?php echo $row['cutting_title'];?>',
                    remark:'<?php echo $row['cutting_remark'];?>',
                    task:'<?php echo $row['task_date'];?>',
                    status:'<?php echo $row['cutting_status'];?>',
                    active:'<?php echo $row['active_status'];?>'
                });"
                class="edit-doc" style="width:15%;background:transparent;color:#009688;padding:0px;border:0px">Edit</button>
            <?php endif; ?>
        </div>
        <label style="font-size:14px"><?php echo $row['cutting_remark']?></label>
        <br/>
        <label style="font-size:12px">Task Date: <?php echo $row['task_date']?></label>
        <br/>
        <label style="font-size:12px;color:grey;">Create Date: <?php if($row['create_login_id']== '0'){ $row['create_name']='ADMIN';} echo $row['cutting_date'].' <b>'.$row['create_name']."</b>"?></label>
        <br/>
        <label style="font-size:12px;color:grey">Edit Date: <?php if($row['edit_login_id']== '0'){ $row['edit_name']='ADMIN';} echo $row['cutting_edit_date']; echo $row['edit_name']?' <b>'.$row['edit_name']."</b>":' <b>'.$row['create_name']."</b>"?></label>
        <br/>
        <label style="font-size:14px;color:black;margin-bottom:0;font-weight:bold"><?php echo $row['cutting_status']?></label>
    </div>
<?php endforeach; ?>
<!-- <table id="order-table" class="table table-hover" style="padding:3px;border-radius:0px; vertical-align:middle">
    <thead>
    <tr>
        <th> <center>Cutting Remarks</center> </th>
        <th> <center>Date</center>  </th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($order_cutting_date as $row): ?>
        <tr>
            <td style="width:60%; box-shadow: 0 0px 0px 0 rgba(0,0,0,0.1);border-right:0px; vertical-align:middle" data-id="<?php echo $row['id'] ?>">
                <?php echo $row['cutting_remark']; ?>
            </td>
            <td style="width:60%; box-shadow: 0 0px 0px 0 rgba(0,0,0,0.1);border-left:0px;border-right:0px; vertical-align:middle">
                <?php echo $row['cutting_date']=='0000-00-00'?'Not Specified':$row['cutting_date']; ?>
            </td>
            <td id="removeDate" style="width:60%; box-shadow: 0 0px 0px 0 rgba(0,0,0,0.1);border-left:0px; vertical-align:middle">
                <a class="btn" style="width: 40px;font-size:20px;margin-right:-1px; float:right; padding:3px 0px 3px 3px; color:rgb(255,85,45)"><i class="fa fa-trash"></i></a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table> -->
</div>

<!-- <a id="addDate"  style="width:10%;padding:0px;font-size:24px;margin:5px 0px 0px 0px; float:right; color:#147efb;"> <i class="fa fa-plus" aria-hidden="true"></i> </a>
<input style="width:60%; margin-left:5px" name="remarks" id ="remarks" placeholder="Remarks" type="text">
<input style="width:25%;" class="button-add" id="date" placeholder="Date"  type="text">

<div style="margin-left:5px">
    
</div> -->

<?php endif; ?>
</main>
</div>
<div style="height:60px"></div>

<div href="#" onClick="<?php
                            echo empty($goback_link)? 'history.back(-1)': "location.replace('".$goback_link."')";
                        ?>" style="padding:5px">
    <p style="cursor: pointer; text-align: center; padding:8px;
                            position: fixed;bottom: 2%;width:95%"
       class="dropdown-div radius text_white back-button shadow"> Go Back </p>
</div>

<?php if($order_details['order_data'][0]['orderItemArr']):?>
<div hidden id="orderData">
	<?php echo json_encode($order_details['order_data']);?>
</div>
    <?php endif;?>
    <div id="myModal" class="modal">
    
    <div class="modal-content">
    <span class="close">&times;</span>
        <div class="content"></div>
    </div>
</div>
</body>
<script>

    var taskDateVal = '', showModalOnClickEdit, toggleCancelHideButton;

    var udf_fields = '<?php echo json_encode($udf_fields);?>';

    $(document).ready(function(){

        var today               = new Date().toJSON().slice(0,10);
        let DatePicker_val      = today ;

        $('#loadingSpinner').hide();
        
        <?php if($order_cutting_exists):?>

            const DatePicker = flatpickr("#date",{
                dateFormat: "Y-m-d",
                disableMobile: true,
                onChange: function(selectedDates, dateStr, instance) {
                    DatePicker_val = dateStr;
                },
                onReady: function(dObj, dStr, fp, dayElem){
                    DatePicker_val = dStr;
                }
            });

        showModalOnClickEdit = async function showModalOnClickEdit(rowVal){

            var { title, remark, task, status, active } = rowVal;

            var active_status = 'checked';

            if(active == '1'){
                active_status = '';
            }

            var tmpUdf_fields = JSON.parse(udf_fields);

            var title_values = tmpUdf_fields.title;
            var status_values = tmpUdf_fields.status;

            var selectedIndex = 0;
            
            var title_options = [];
            for (let i = 0; i < title_values.length; i++) {
                const each_title = title_values[i];
                title_options.push(`<option value="${each_title.value}">${each_title.name}</option>`);
                if(each_title.name == title){
                    selectedIndex = i;
                }
            }

            var firstValue = title_options[0];

            title_options[0] = title_options[selectedIndex];

            title_options[selectedIndex] = firstValue;

            title_options = title_options.join(" ");

            selectedIndex = 0;

            var status_options = [];

            for (let i = 0; i < status_values.length; i++) {
                const each_status = status_values[i];
                status_options.push(`<option value="${each_status.value}">${each_status.name}</option>`);
                if(each_status.name == status){
                    selectedIndex = i;
                }
            }

            selectedIndex = 0;

            firstValue = status_options[0];

            status_options[0] = status_options[selectedIndex];

            status_options[selectedIndex] = firstValue;

            status_options = status_options.join(" ");

            var titleDropDown = `
                <div class="form-group" style="margin-bottom:0px;">
                    <select id="ed_title" data-id="${rowVal.id}" style="width:100%;border:1px solid lightgrey;border-radius:3px;height:35px;color:gray;padding-left:7px" class="form-group" name="title">
                        ${title_options}
                    </select>
                </div>
            `; 

            var statusDropDown = `
                <div class="form-group" style="margin-bottom:0px;">
                    <select id="ed_status" style="width:100%;border:1px solid lightgrey;border-radius:3px;height:35px;color:gray;padding-left:7px" class="form-group" name="status">
                       ${status_options}
                    </select>
                </div>
            `;

            if(!task){
                const date = new Date();
                const month = date.toLocaleString('default', { month: 'short' });
                task = `${date.getDate()} ${month} ${date.getFullYear()}`;
            }

            const {value: formValues} = await Swal.fire({
                html:
                    '<div class="container">' +
                    '    <div class="row">' +
                    '        <div class="col">' +
                    '            <div>' +
                    '                <div class="card-body">' +
                    '                    <form>' + 
                                             titleDropDown   + statusDropDown +
                    '                        <div class="form-group">' +
                    '                            <input value="'+task+'" type="tel" class="form-control" id="ed_date" aria-describedby="emailHelp" placeholder="Task date">' +
                    '                        </div>' +
                    '                        <div class="form-group">' +
                    '                            <textarea id="ed_remark" class="form-control" id="ed_remark" rows="6" placeholder="Remark">'+remark+'</textarea>' +
                    '                        </div>' +
                    '                        <div style="margin-bottom:10px;margin-left:-25px" class="check form-group"><label class="checkbox_rounded">'+
                    '                           <input ' + active_status + ' type="checkbox" id="hideRecord"><div class="checkbox_hover"></div>'+
                    '                           </label><p class="non_important_text" style="margin-left:5px;margin-top:-5px">Hide</p>'+
                    '                        </div>' +
                    '                        <div class="mx-auto">' +
                    '                        <button style="background-color: #147efb;" type="button" class="btn btn-primary text-center btn-block" id="btn_edit">Submit</button></div>'+
                    '                    </form>' +
                    '                </div>' +
                    '            </div>' +
                    '        </div>'+
                    '    </div>' +
                    '</div>',
                focusConfirm: false,
                background:'white',
                showConfirmButton:false,
                showCloseButton:true,
                preConfirm: () => {
                    return [
                        document.getElementById('ed_date').value,
                        document.getElementById('en_remark').value
                    ]
                },
                onOpen: () => {
                    const TaskDatePicker = flatpickr("#ed_date",{
                        dateFormat: "d M Y",
                        disableMobile: true,
                        onChange: function(selectedDates, dateStr, instance) {
                            taskDateVal = dateStr;
                            console.warn(taskDateVal);
                            
                        },
                        onReady: function(dObj, dStr, fp, dayElem){
                            taskDateVal = dStr;
                        }
                    });
                }
            });
        }
        $('body').on('click', '#btn_edit', function() {

            var hideRecord = $("#hideRecord").is(':checked');
                hideRecord = +(!hideRecord);

            var title = $("#ed_title").val();
            var status = $("#ed_status").val();
            var date = $("#ed_date").val();
            var remark = $("#ed_remark").val();
            remark = remark.replace(/\n/g," ");
            var userId = '<?php echo $user_id; ?>';

            var rec_id = $("#ed_title").attr('data-id');

            var action = rec_id == 0 ? 'insert' : 'update';

            $("#btn_edit").attr("disabled", true);
            Swal.showLoading();
            
            var taskDate = Date.parse(date);
                taskDate = new Date(taskDate);
                taskDate = dateToYMD(taskDate);

            let order_id = "<?php echo $orderId; ?>";
            let client = "<?php echo $client; ?>";

            var tmpUdf_fields = JSON.parse(udf_fields);

            var title_values = tmpUdf_fields.title;
            var status_values = tmpUdf_fields.status;

            for (let i = 0; i < title_values.length; i++) {
                const each_title = title_values[i];
                if(each_title.value == title){
                    title = each_title.name;
                }
            }

            for (let i = 0; i < status_values.length; i++) {
                const each_status = status_values[i];
                if(each_status.value == status){
                    status = each_status.name;
                }
            }

            var data = encodeURIComponent('{ "rec_id":"' +rec_id+ '","title":"' +title+ '","active":"' +hideRecord+ '","status":"' +status+ '","order_id":"' + order_id + '","date":"' + taskDate + '","remark":"' + remark + '","client":"' + client + '","action":"'+action+'","userId":"' + userId + '"}');

            jQuery.ajax({
                type: "POST",
                url: "./route.php",
                data: "data=" + data + "&action=updateCuttingDate",
                success: function (msg) {
                    debugger
                    msg = isJSONparsable(msg);
                    let order_id = msg['order_id'];
                    
                    if(order_id){
                        window.location = window.location.href;
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.warn(xhr);
                }
            });
        });
        function dateToYMD(date) {
            var d = date.getDate();
            var m = date.getMonth() + 1;
            var y = date.getFullYear();
            return '' + y + '-' + (m<=9 ? '0' + m : m) + '-' + (d <= 9 ? '0' + d : d);
        }
        function replaceUrlParam(url, paramName, paramValue){
            if (paramValue == null) {
                paramValue = '';
            }
            var pattern = new RegExp('\\b('+paramName+'=).*?(&|#|$)');
            if (url.search(pattern)>=0) {
                return url.replace(pattern,'$1' + paramValue + '$2');
            }
            url = url.replace(/[?#]$/,'');
            return url + (url.indexOf('?')>0 ? '&' : '?') + paramName + '=' + paramValue;
        }
        toggleCancelHideButton = function toggleCancelHideButton(){

            $('#loadingSpinner').show();

            var cancelled = '<?php echo $cancelCuttingDate;?>';
                cancelled = parseInt(cancelled);
                cancelled = +!cancelled;

            var url = window.location.href;
                url = replaceUrlParam(url,'cancelled',cancelled);

            window.location = url;
        }
        <?php endif;?>
    });
    $(document).on('click', '#removeDate', function() {
        let sure = confirm("Are you sure you want to delete the item?");
        if(sure){
            let cutting_order_id = $(this).parent().find("td").attr("data-id");
            let client           = "<?php echo $client; ?>";

            $(this).parent().remove();

            var data = encodeURIComponent('{ "id":"' + cutting_order_id + '","client":"' + client + '","action":"' + 'delete'+ '"}');
            jQuery.ajax({
                type: "POST",
                url: "./route.php",
                data: "data=" + data + "&action=updateCuttingDate",
                success: function (msg) {
                    console.log(msg);
                    if(msg){
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.warn(xhr);
                }
            });
        }
    });
    $(document).on('click', '#addDate', function() {
        let date = $('#date').val();
        let remarks =  $('#remarks').val();
        if(date || remarks){
            date = date?date:'-';
            remarks = remarks?remarks:'-';

            let order_id         = "<?php echo $orderId; ?>";
            let client           = "<?php echo $client; ?>";
            $('#remarks').val('') ;
            $('#date').val('') ;
            $('#order-table').append('<tr><td style="width:60%; box-shadow: 0 0px 0px 0 rgba(0,0,0,0.1);border-right:0px"> '+remarks+'</td><td style="width:60%; box-shadow: 0 0px 0px 0 rgba(0,0,0,0.1);border-left:0px;border-right:0px">'+date+'</td><td id="removeDate" style="width:60%; box-shadow: 0 0px 0px 0 ;border-left:0px; vertical-align:middle"><a class="btn" style="width: 40px;font-size:20px;margin-right:0px; float:right; margin-top:-15px; color:red"><i class="fa fa-trash"></i></a></td></tr>');
            var data = encodeURIComponent('{ "id":"' + order_id + '","date":"' + date + '","remarks":"' + remarks + '","client":"' + client + '","action":"' + 'insert'+ '","userId":"' + userId + '"}');
            jQuery.ajax({
                type: "POST",
                url: "./route.php",
                data: "data=" + data + "&action=updateCuttingDate",
                success: function (msg) {
                    debugger
                    msg = isJSONparsable(msg);
                    let order_id = msg['order_id'];
                    console.log(order_id);
                    if(order_id){
                        $('#order-table tr:last').find("td").attr("data-id",order_id);
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.warn(xhr);
                }
            });
        }else{
            alert('Please set a remark or date')
        }
    });
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

    var modal = document.getElementById("myModal");
    var span = document.getElementsByClassName("close")[0];
    span.onclick = function() {
        modal.style.display = "none";
    }

    function openModal(ipad_item_id, product_code, unit_uom, quantity, unit_price, disc_1, disc_2, disc_3, salesperson_remark,discount_method,product_name){
        debugger
        var modal = document.getElementById("myModal");
        let client = "<?php echo $client; ?>";
        disc_1 = disc_1 == 0 ? '' : disc_1;
        disc_2 = disc_2 == 0 ? '' : disc_2;
        disc_3 = disc_3 == 0 ? '' : disc_3;
        modal.style.display = "block";
        var data = encodeURIComponent('{"openModal_data" :{ "product_code":"' + product_code + '","client":"' + client + '"}}');

        $.ajax({
            type: "POST",
            url: "./route.php",
            data: "data=" + data + "&action=openModal",
            success: function(msg)
            {
                debugger;
                var decodedJson = JSON.parse(msg);
                var data = decodedJson.data;
                var single_discount = decodedJson.discount_method;
                var multi_discount = decodedJson.app_multi_discount;
                if(data){
                    $(".content").empty();
                    var view ="<p style='font-weight:bold;'>"+product_name+"</p><input class='numberic' style='width:32.6%;line-height:35px;margin-right:1%;' type='text' id='price' value="+unit_price+" placeholder='Price'><input class='numberic' style='width:32.6%;line-height:35px;margin-right:1%;' type='text' id='quantity' value="+quantity+" placeholder='Quantity'>";

                    var select = "<select style='width:32.6%;height:35px;border-radius:3px;border:1px solid whitesmoke;' id='unit_uom'>";
                    for(var i = 0; i < data.length; i++){
                        if(data[i].product_uom == unit_uom){
                            select +="<option value='"+data[i].product_uom+"' selected>"+data[i].product_uom+"</option>";
                        }else{
                            select +="<option value='"+data[i].product_uom+"'>"+data[i].product_uom+"</option>";
                        }
                    }
                    select += "</select>";
                    if(select != ''){
                        view += select;
                    } 

                    if(single_discount == 1 && multi_discount == 0){
                        var discount = "<div style='margin-top:1%;'><button id='btn_m' style='width:24.5%;line-height:30px;border-radius:3px;' onclick='addDiscountMethod(1)'>RM</button ><button id='btn_p' style='width:24.5%;line-height:30px;border-radius:3px;' onclick='addDiscountMethod(2)'>%</button><input style='width:50%;line-height:35px;margin-left:1%;' class='numberic' type='text' id='disc_1' value='"+disc_1+"' placeholder='%'></div>";
                    }else if(single_discount == 0 && multi_discount == 1){
                        var discount = "<div style='margin-top:1%;'><input style='width:32.6%;line-height:35px;margin-right:1%' class='numberic' type='text' id='disc_1' value='"+disc_1+"' placeholder='%'><input style='width:32.6%;line-height:35px;margin-right:1%' class='numberic' type='text' id='disc_2' value='"+disc_2+"' placeholder='%'><input style='width:32.6%;line-height:35px;' class='numberic' type='text' id='disc_3' value='"+disc_3+"' placeholder='%'></div>";
                    }else{
                        var discount = '';
                    }
                    
                   
                    view += "<br>"+discount+"<input type='text' id='salesperson_remark' value='"+salesperson_remark+"' placeholder='Remark' style='width:100%;line-height:35px;margin-top:1%;'><input type='text' class='numberic' id='discount_method' value='"+discount_method+"' style='width:100%;visibility: hidden;'><div style='width:100%;margin-top:5px;line-height:20px;'><button class='btn-delete' onclick='updateOrderItem("+ipad_item_id+",1)' style='width:49.5%;float:left;margin-right:1%;'>DELETE</button><button class='btn-update' style='width:49.5%;float:left;' onclick='updateOrderItem("+ipad_item_id+",0)' >UPDATE</button></div>";
                    $(".content").append(view);

                    if(multi_discount == 0){
                        if(discount_method == 'MoneyDiscountType'){
                            addDiscountMethod(1);
                        }else{
                            addDiscountMethod(2);
                        }
                    }
                }
            },
            error: function (xhr, ajaxOptions, thrownError)
            {
                alert("Please contact support");
            }
        });
    }

    function updateOrderItem(ipad_item_id,status){

        let client = "<?php echo $client; ?>";

        var price = document.getElementById('price').value;
        var quantity = document.getElementById('quantity').value;

        var disc_1 = '';
        if(document.getElementById('disc_1')){
            disc_1 = document.getElementById('disc_1').value;
        }

        var disc_2 = '';
        if(document.getElementById('disc_2')){
            disc_2 = document.getElementById('disc_2').value;
        }

        var disc_3 = '';
        if(document.getElementById('disc_3')){
            disc_3 = document.getElementById('disc_3').value;
        }

        var remark = document.getElementById('salesperson_remark').value;
        var uom = document.getElementById('unit_uom').value;
        var discount_method = document.getElementById('discount_method').value;
        debugger

        var data = encodeURIComponent('{"updateOrderItem_data" :{ "ipad_item_id":"' + ipad_item_id + '","client":"' + client + '","status":"' + status + '","price":"' + price + '","quantity":"' + quantity + '","disc_1":"' + disc_1 + '","disc_2":"' + disc_2 + '","disc_3":"' + disc_3 + '","remark":"' + remark + '","uom":"' + uom + '","discount_method":"' + discount_method + '"}}');

        $.ajax({
            type: "POST",
            url: "./route.php",
            data: "data=" + data + "&action=updateOrderItem",
            success: function(msg)
            {
                
                var decodedJson = JSON.parse(msg);
                var status = decodedJson.msg;
                debugger
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

    function addDiscountMethod(i){
        var btn_m = document.getElementById('btn_m');
        var btn_p = document.getElementById('btn_p');

        if(i == 1){
            var discount_method = 'MoneyDiscountType';
            btn_m.style.backgroundColor = '#147efb';
            btn_m.style.color = '#fff';
            btn_m.style.borderColor = '#147efb';

            btn_p.style.backgroundColor = '#fff';
            btn_p.style.color = 'black';
            btn_p.style.borderColor = 'whitesmoke';

        }else{
            var discount_method = 'PercentDiscountType';
            btn_p.style.backgroundColor = '#147efb';
            btn_p.style.color = '#fff';
            btn_p.style.borderColor = '#147efb';

            btn_m.style.backgroundColor = '#fff';
            btn_m.style.color = 'black';
            btn_m.style.borderColor = 'whitesmoke';
        }
        document.getElementById('discount_method').value = discount_method;
    }

    $(document).on('input','.numberic',function(event) {
        this.value = this.value.replace(/[^0-9.]/g, '');
    });

</script>
</html>
<style>
*{
    cursor:pointer;
}
.column {
    float: left;
    width: 38%;
    padding-top: 5px;
    padding-bottom: 5px;
    height: 25px;
    border-style: solid;
    border-width: 1px 1px 0px 1px;
    border-color:lightgrey;
    border-radius:1px;
}
.row:after {
    display: table;
    clear: both;
}
header, footer{
    height: 60px;
}
.site-wrapper{
    display: flex;
    flex-direction: column;
    /* min-height: 100vh; */
}
.site-content{
    flex: 1
}
body {
    font-family: 'Open Sans';
    font-size: 15px;
    user-select: none;
    -webkit-user-select: none;
    -ms-user-select: none;
    -webkit-touch-callout: none;
    -o-user-select: none;
    -moz-user-select: none;
    line-height: 1;
    padding: 0;
    margin: 0;
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
    height:5px;
}
.company-title{
    box-sizing: border-box;
    -moz-box-sizing: border-box;
    -webkit-box-sizing: border-box;
    height: 25px;
    color:white;
    font-weight: bold;
    background-color: rgba(128,128,128,0.7);
    padding: 0px;
    font-size:14px;
    box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
}
.title {
    color:#147efb;
    text-align:left;
    margin-block-start: 0.1em;
    margin-block-end: 0.1em;
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
    margin-block-start: 0.25em;
    margin-block-end: 0.25em;
    margin-inline-start: 0.5px;
    margin-inline-end: 0.5px;
    margin: 3px;
    padding: 2px;
}
.delivery_status {
    display: inline;
}
.dropdown-div{
    padding-left: 5px;
    padding-right: 5px;
    box-sizing: border-box;
    -moz-box-sizing: border-box;
    -webkit-box-sizing: border-box;
    background-color: #147efb;
    height: 35px;
    border: 1px solid #147efb;
}
.center-text{
    position: relative;
    float: left;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}
.limit-text-length {
    overflow: hidden;
    text-overflow: ellipsis;
    max-height: 25px;
    line-height: 25px;
    vertical-align: middle;
    text-align: center;
    height: 25px;
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
table {
    border-radius: .5em;
    overflow: hidden;
    width: 100%;
    padding:5px;
}
td {
    padding: 0px;
    border: 1px solid #ddd;
    width: 100%;
    box-shadow: 0 4px 8px 0 rgba(0,0,0,0.1);
    border-radius:3px;
}
.shadow{
    box-shadow: 0 4px 8px 0 rgba(0,0,0,0.5);
    transition: 0.3s;
}
.packing-status {
    font-size: 14px;
    text-color:grey;
}
.title-box {
    padding-top: 20px 0px 5px;
    text-align: center;
}
.btn-nueva {
    margin: 2px 0 14px 0;
    float:right;
}
.input-box {
    border-width: 0.5px;
    border-radius: 3px;
    padding: 3px 8px;
    height:25px;
    width:25%;
    border-color: #00000026;
}
input {
    border: 2px solid whitesmoke;
    border-radius: 5px;
    padding: 5px;
    text-align: left;
    height:35px;
    background-color:white;
}
.btn-group button {
  background-color: #147efb; 
  border: 1px solid #147efb; 
  color: white; 
  padding: 8px 24px; 
  cursor: pointer; 
  float: center; 
}
.btn-group:after {
  content: "";
  clear: both;
  display: table;
}

.btn-group button:not(:last-child) {
  border-right: none; 
}
.left-grid {
  width: 65%;
  float: left;
  box-sizing: border-box;
}

.right-grid {
  width: 35%;
  float: left;
  box-sizing: border-box;
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
</style>
