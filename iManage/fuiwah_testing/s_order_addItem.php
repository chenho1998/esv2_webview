
<!DOCTYPE html>
<html lang="en">
<?php
session_start();

$client = '';
$orderId = '';

if(isset($_GET['orderId']) && isset($_GET['client'])){
    $client = $_GET['client'];
    $orderId = $_GET['orderId'];
    $role_id = $_SESSION['role_id'];
    $user_id = $_SESSION['user_id'];

    $settings = $_SESSION['settings'];
    $param = array(
        "client"  => $client,
        "orderId" => $orderId,
        "settings"=>$settings,
    );
    $goback_link = "https://easysales.asia/esv2/webview/iManage/fuiwah/s_order_details.php?orderId=".$orderId."&client=".$client;
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
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
</head>

<body id="loading">

<!-- <div id="loadingSpinner" style="margin-left:-35px" class=" no_orders loader"></div> -->

<div class="site-wrapper">
    <main class="site-content">
        <tr style="width:100%;overflow: hidden;text-overflow: ellipsis; ">
            <td>

                <input style="width:75%;border:1px solid #147efb;line-height:30px;color:#147efb;padding-left:5px;margin:5px;" id="productSelection" type="text" class="radius"  placeholder="Product Code / Product Name..."/>
                
            </td>
            <td>
                <button class="radius non_important_text buttons text_white" id="btnSearch" onclick="searchProduct()"> Search </button>
            </td>
        </tr>
        <table id="table" style="width:100%;"></table>
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


</body>
<link href='https://fonts.googleapis.com/css?family=Open Sans' rel='stylesheet'>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.2.3/themes/airbnb.css">
<script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.3.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.2.3/flatpickr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery.redirect@1.1.4/jquery.redirect.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8.5.0/dist/sweetalert2.all.min.js"></script>
<script>

function searchProduct(){

    client = "<?php echo $client; ?>";
    orderId = "<?php echo $orderId ?>";
    text = document.getElementById("productSelection").value;

    var data = encodeURIComponent('{"searchProduct_data" :{  "client":"' + client + '","orderId":"' + orderId + '","text":"' + text +'"}}');

    jQuery.ajax({
   		type: "POST",
   		url: "./route.php",
   		data: "data=" + data + "&action=searchProduct",
   		success: function(msg){
        	debugger;
            document.getElementById("productSelection").value = '';
            $( "#table" ).empty();
     		var decodedJson = JSON.parse(msg);
     		var view = decodedJson.view;

     		if(view != false && view != undefined)
     		{
                $( "#table" ).append(view);
     		}
     		else
     		{
         		alert("The product name/code is not found.Please try again.");
     		}
   		},
      	error: function (xhr, ajaxOptions, thrownError)
      	{
			// error
      	}
		});
}

function addProduct(productCode){
    client = "<?php echo $client; ?>";
    orderId = "<?php echo $orderId ?>";


    var data = encodeURIComponent('{"addProduct_data" :{  "client":"' + client + '","orderId":"' + orderId + '","productCode":"' + productCode +'"}}');

    jQuery.ajax({
        type: "POST",
   		url: "./route.php",
   		data: "data=" + data + "&action=addProduct",
   		success: function(msg){
        	debugger;
            
     		var decodedJson = JSON.parse(msg);
     		var success = decodedJson.success;
            var message =  decodedJson.message;
     		if(success != false && success != undefined)
     		{
                alert(message);
                window.location="<?php echo $goback_link; ?>";
     		}
     		else
     		{
                alert(message);
     		}
   		},
      	error: function (xhr, ajaxOptions, thrownError)
      	{
			// error
      	}
		});
}

</script>
</html>
<style>
*{
    cursor:pointer;
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

#productSeachInp {
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

#productSeachInp:focus {outline: 3px solid #ddd;}

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

.radius {
    border-radius:3px;
}

.text_white {
    color:#fff;
    text-align:left;
}

.shadow{
    box-shadow: 0 4px 8px 0 rgba(0,0,0,0.5);
    transition: 0.3s;
}

.buttons {
    color:#e8ebef;
    border: 1px solid #147efb;
    background: #147efb;
    width: 20%;
    display: inline;
    text-align:center;
    padding: 5px;
    box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
    transition: 0.3s;
    height:35px;
}

.column {
    float: left;
    width: 50%;
    padding-top: 5px;
    padding-bottom: 5px;
    height: 25px;
    border-style: solid;
    border-width: 1px 1px 1px 1px;
    border-color:lightgrey;
    border-radius:1px;
}
.row:after {
    display: table;
    clear: both;
}

table {
    border-radius: .5em;
    overflow: hidden;
    width: 100%;
    padding:5px;
}
td {
    padding: 0px;
    /* border: 1px solid black; */
    width: 100%;
    /* box-shadow: 0 4px 8px 0 rgba(0,0,0,0.1); */
    border-radius:3px;
}

p{
    margin-block-start: 0em;
    margin-block-end: 0em;
}

</style>
