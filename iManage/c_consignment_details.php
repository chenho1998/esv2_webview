<!DOCTYPE html>
<html lang="en">
<?php
session_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL ^ E_DEPRECATED); 
require_once('getConsignmentViewDetails.php');


$client = '';
$consignment_id = '';

if(isset($_GET['consignmentId']) && isset($_GET['client'])){

	$client = $_GET['client'];
	$consignment_id = $_GET['consignmentId'];

	// $object = new stdClass();
	// $object->client = $client;
	// $object->consignment_id = $consignment_id;

	$param = array(
        "client"  => $client,
        "consignment_id" => $consignment_id,
    );


	$consignment_details =  getConsignmentViewDetails($param);
    $order_len           =  count($consignment_details);
    
    $goback_link = $_SESSION['_consign_backlink'];
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
<body id="loading">
<div class="site-wrapper">
   <main class="site-content">
        <?php echo($consignment_details['views']); ?>
   </main>
   
</div>
<div hidden id="orderData">
	<?php echo json_encode($consignment_details['order_data']);?>
</div>
</body>

            <!-- <div href="##" onclick="history.go(-1);" style="padding:5px"> -->

            <div href="##" onClick="location.replace('<?php 
            
            echo $goback_link? $goback_link: history.go(-1);
            

            
            ?>');" style="padding:5px">
                <p style="cursor: pointer; text-align: center; padding:8px;
                            position: fixed;bottom: 2%;width:95%" 
                            class="dropdown-div radius text_white back-button shadow"> Go Back </p>
            </div>


<link href='https://fonts.googleapis.com/css?family=Open Sans' rel='stylesheet'>
<script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.3.1.min.js"></script>

<style>
.column {
  float: left;
  width: 38%;
  padding-top: 5px;
  padding-bottom: 5px;
  height: 100%;
  border-style: solid;
  border-width: 1px;
  border-color:lightgrey;
  border-radius:1px;
}

.row {

    height: auto;
    width:auto;
}
tr {
    border-collapse:collapse;
    border-spacing:0px;
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
   min-height: 100vh;
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

table {
    border-radius: .5em;
    overflow: hidden;
    width: 100%;
    height: 100%;
    border-collapse:separate;
    padding:5px;
    border-spacing:0px;
    
}


/* top-left border-radius */
table tr:first-child th:first-child {
  border-top-left-radius: 6px;
}

/* top-right border-radius */
table tr:first-child th:last-child {
  border-top-right-radius: 6px;
}

/* bottom-left border-radius */
table tr:last-child td:first-child {
  border-bottom-left-radius: 6px;
}

/* bottom-right border-radius */
table tr:last-child td:last-child {
  border-bottom-right-radius: 6px;
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
</style>

</html>
