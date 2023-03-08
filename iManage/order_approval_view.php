<!DOCTYPE html>
<html lang="en">
<?php
session_start();
$client = '';
$userId = '';
if(isset($_GET['userId']) && isset($_GET['client'])){

   require_once('getOrderApproval.php');

   $client         = $_GET['client'];
   $userId         = $_GET['userId'];


   $param = array(
       "client"        =>$client,
       "salespersonid" =>$userId
   );
   
    
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
<body>



<div class="divider"></div>
<div class="divider"></div>



<div id="ordersDiv">
    <?php getOrderApproval($param); ?>
</div>




<link href='https://fonts.googleapis.com/css?family=Open Sans' rel='stylesheet'>
<script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-3.3.1.min.js"></script>
















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
  width: 33.33%;
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
